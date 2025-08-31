<?php

namespace App\Http\Controllers\API\Customer\SalesProcess;

use App\Http\Controllers\Controller;
use App\Models\Market\CartItem;
use App\Models\Market\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CartController extends Controller
{
     /**
     * @OA\Get(
     *     path="/api/cart/options",
     *     summary="Retrieve the authenticated user's cart items and related products",
     *     description="Returns a list of cart items along with related products that are not in the cart. The user must be authenticated.",
     *     operationId="getCartItems",
     *     tags={"Cart"},
     *     security={{"bearerAuth": {}}},
     * 
     *     @OA\Response(
     *         response=200,
     *         description="Successful response with cart items and related products",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="cartItems", type="array",
     *                     @OA\Items(ref="#/components/schemas/CartItem")
     *                 ),
     *                 @OA\Property(property="relatedProducts", type="array",
     *                     @OA\Items(ref="#/components/schemas/Product")
     *                 ),
     *                 @OA\Property(property="authUser", ref="#/components/schemas/User")
     *             )
     *         )
     *     ),
     * 
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - User must be authenticated",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthorized")
     *         )
     *     )
     * )
     */
    public function options()
    {
        $user = Auth::user();
        $cartItems = CartItem::where('user_id', $user->id)->with('product:id,name,image,slug,related_products', 'color:id,color_name', 'guarantee:id,name')->simplePaginate(15);
        $cartItems->getCollection()->each(function ($item) {
            $item->product->makeHidden(['status_value', 'marketable_value', 'related_products_value']);
            if ($item->color) {
                $item->color->makeHidden('status_value');
            }
            if ($item->guarantee) {
                $item->guarantee->makeHidden('status_value');
            }
        });
        $cartItemIds = $cartItems->pluck('product_id')->toArray();
        $relatedProductIds = $cartItems->flatMap(function ($item) {
            return $item->product->related_products ?
                explode(',', $item->product->related_products) : [];
        })->unique()->values()->toArray();
        $relatedProducts = Product::whereIn('id', $relatedProductIds)
            ->whereNotIn('id', $cartItemIds)->with('category:id,name', 'brand:id,persian_name', 'tags:id,name')->simplePaginate(15);
        $relatedProducts->getCollection()->each(function ($product) {
            $product->category->makeHidden(['status_value', 'show_in_menu_value']);
            if ($product->brand) {
                $product->brand->makeHidden('status_value');
            }
            if ($product->tags) {
                $product->tags->makeHidden('pivot');
            }
        });
        return response()->json([
            'data' => [
                'cartItems' => $cartItems,
                'relatedProducts' => $relatedProducts,
                'authUser' => $user
            ]
        ], 200);
    }

    /**
     * @OA\Put(
     *     path="/api/cart/update",
     *     summary="Update quantities of items in the cart",
     *     description="Updates the quantities of the user's cart items. The user must be authenticated.",
     *     operationId="updateCart",
     *     tags={"Cart"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="number", type="object", example={"1": 2, "3": 1})
     *         )
     *     ),
     * @OA\Response(
     *     response=400,
     *     description="Cart is empty",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="status", type="boolean", example=false),
     *         @OA\Property(property="message", type="string", example="سبد خرید شما خالی است و امکان بروزرسانی وجود ندارد")
     *     )
     * ),
     *     @OA\Response(
     *         response=200,
     *         description="Cart updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="سبد خرید با موفقیت بروزرسانی شد"),
     *             @OA\Property(property="meta", type="object",
     *                 @OA\Property(property="next_step", type="string", example="redirect_to_/choose-address-and-delivery")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="خطایی غیرمنتظره در سرور رخ داده است. لطفا دوباره تلاش کنید")
     *         )
     *     )
     * )
     */
    public function updateCart(Request $request)
    {
        $validated = $request->validate([
            'number.*' => 'required|numeric|min:1|max:5'
        ]);
        try {
            $inputs = $request->all();
            $cartItems = CartItem::where('user_id', Auth::user()->id)->get();
            if ($cartItems->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'سبد خرید شما خالی است و امکان بروزرسانی وجود ندارد'
                ], 400);
            }
            foreach ($cartItems as $cartItem) {
                if (isset($inputs['number'][$cartItem->id])) {
                    $cartItem->update(['number' => $inputs['number'][$cartItem->id]]);
                }
            }
            return response()->json([
                'status' => true,
                'message' => 'سبد خرید با موفقیت بروزرسانی شد',
                'meta' => [
                    'next_step' => 'redirect_to_/choose-address-and-delivery'
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'خطایی غیرمنتظره در سرور رخ داده است. لطفا دوباره تلاش کنید'
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/add-to-cart/{product:slug}",
     *     summary="Add a product to the cart",
     *     description="Adds a product to the user's cart. The user must be authenticated.",
     *     operationId="addToCart",
     *     tags={"Cart"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="product:slug",
     *         in="path",
     *         required=true,
     *         description="Product Slug",
     *         @OA\Schema(type="string", example="موبایل-سامسونگ-مدل-a71")
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="color", type="integer", nullable=true, example=4),
     *             @OA\Property(property="guarantee", type="integer", nullable=true, example=7),
     *             @OA\Property(property="number", type="integer", example=1)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Product added successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="محصول با موفقیت به سبد خرید افزوده شد")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=400,
     *         description="Product already exists in cart",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="محصول از قبل در سبد خرید شما وجود داشته است")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="خطایی غیرمنتظره در سرور رخ داده است. لطفا دوباره تلاش کنید")
     *         )
     *     )
     * )
     */
    public function addToCart(Product $product, Request $request)
    {
        $request->validate([
            'color' => ['nullable', Rule::exists('product_colors', 'id')->where('product_id', $product->id)],
            'guarantee' => ['nullable', Rule::exists('guarantees', 'id')->where('product_id', $product->id)],
            'number' => ['numeric', 'min:1', "max:$product->marketable_number"],
        ]);

        try {
            $cartItems = CartItem::where('product_id', $product->id)->where('user_id', auth()->user()->id)->get();
            if (!isset($request->color)) {
                $request->color = null;
            }
            if (!isset($request->guarantee)) {
                $request->guarantee = null;
            }

            foreach ($cartItems as $cartItem) {
                if ($cartItem->color_id == $request->color && $cartItem->guarantee_id == $request->guarantee) {
                    if ($cartItem->number != $request->number) {
                        $cartItem->update(['number' => $request->number]);
                    }
                    return response()->json([
                        'status' => false,
                        'message' => 'محصول از قبل در سبد خرید شما وجود داشته است'
                    ]);
                }
            }

            $inputs = [];
            $inputs['color_id'] = $request->color;
            $inputs['guarantee_id'] = $request->guarantee;
            $inputs['user_id'] = auth()->user()->id;
            $inputs['product_id'] = $product->id;
            $inputs['number'] = $request->number;
            $newCartItem = CartItem::create($inputs);

            return response()->json([
                'status' => true,
                'message' => 'محصول با موفقیت به سبد خرید افزوده شد'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'خطایی غیرمنتظره در سرور رخ داده است. لطفا دوباره تلاش کنید'
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/remove-from-cart/{cartItem}",
     *     summary="Remove an item from the cart",
     *     description="Removes a specific cart item. The user must be authenticated.",
     *     operationId="removeFromCart",
     *     tags={"Cart"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Parameter(
     *         name="cartItem",
     *         in="path",
     *         required=true,
     *         description="Cart item ID",
     *         @OA\Schema(type="integer", example=7)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Item removed successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="محصول از سبد خرید شما پاک شد")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="خطایی غیرمنتظره در سرور رخ داده است. لطفا دوباره تلاش کنید")
     *         )
     *     )
     * )
     */
    public function removeFromCart(CartItem $cartItem)
    {
        // if (!Auth::check()) {
        //     return response()->json([
        //         'status' => false,
        //         'message' => 'کاربر گرامی، جهت مشاهده سبد خرید ابتدا باید وارد حساب خود شوید'
        //     ], 401);
        // }
        try {
            if ($cartItem->user_id === Auth::user()->id) {
                $cartItem->delete();
            }
            return response()->json([
                'status' => true,
                'message' => $cartItem->product->name . ' از سبد خرید شما پاک شد'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'خطایی غیرمنتظره در سرور رخ داده است. لطفا دوباره تلاش کنید'
            ], 500);
        }
    }

}
