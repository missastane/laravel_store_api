<?php

namespace App\Http\Controllers\API\Admin\Market;

use App\Http\Controllers\Controller;
use App\Models\Market\Product;
use App\Models\Market\ProductColor;
use Exception;
use Illuminate\Http\Request;

class ProductColorController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/admin/market/product/color/{product}",
     *     summary="Retrieve list of `Colors` with their product",
     *     description="Retrieve list of all `ProductColors` with their product",
     *  tags={"ProductColor"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *  @OA\Parameter(
     *         name="product",
     *         in="path",
     *         description="product id to fetch its Colors",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="A list of `Colors` with their product",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="array",
     *                 @OA\Items(
     *                     ref="#/components/schemas/ProductColor"
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function index(Product $product)
    {
        $colors = $product->colors()->with('product:name,id')->simplePaginate(15);
        $colors->getCollection()->each(function ($item) {
            $item->product->makeHidden(['status_value', 'related_products_value', 'marketable_value']);
        });
        return response()->json([
            'data' => $colors
        ], 200);
    }

     /**
     * @OA\Get(
     *     path="/api/admin/market/product/color/search/{product}",
     *     summary="Searches among ProductColor by name.",
     *     description="This endpoint allows users to search for `ProductColor` by name. The search is case-insensitive and returns results that contain the given keyword. The results are paginated for better performance",
     *  tags={"ProductColor"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *  @OA\Parameter(
     *         name="product",
     *         in="path",
     *         description="Id of product that you want search fo its ProductColor",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *   @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="type name of Color which you're searching for",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="A list of Color with their product",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="array",
     *                 @OA\Items(
     *                     ref="#/components/schemas/ProductColor"
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function search(Request $request, Product $product)
    {
        $colors = ProductColor::where('product_id', $product->id)->where('color_name', 'LIKE', "%" . $request->search . "%")->with('product:name,id')->orderBy('color_name')->simplePaginate(15);
        $colors->getCollection()->each(function ($item) {
            $item->product->makeHidden(['status_value', 'related_products_value', 'marketable_value']);
        });
        return response()->json([
            'data' => $colors
        ], 200);
    }


      /**
     * @OA\Get(
     *     path="/api/admin/market/product/color/show/{color}",
     *     summary="Returns ProductColor details for edit form",
     *     description="Returns `ProductColor` details with its product for edit form",
     *  tags={"ProductColor","ProductColor/Form"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *  @OA\Parameter(
     *         name="color",
     *         in="path",
     *         description="Id of color that you want showing",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="A ProductColor with its product",
     *        @OA\JsonContent(ref="#/components/schemas/ProductColor"),
     *     )
     * )
     */
    public function show(ProductColor $color)
    {
        $color->load('product:name,id');
        $color->product->makeHidden(['status_value', 'related_products_value', 'marketable_value']);
        return response()->json([
            'data' => $color
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/admin/market/product/color/{product}/store",
     *     summary="create new value for a ProductColor",
     *     description="this method creates a new `ProductColor` for the product and stores it.",
     *     tags={"ProductColor"},
     *     security={{"bearerAuth": {}}},
     *   @OA\Parameter(
     *         name="product",
     *         in="path",
     *         description="ID of the product to fetch",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *             @OA\Property(property="color_name", type="string", pattern="^[a-zA-Z\u0600-\u06FF0-9\s\-\.\?]+$", description="This field can only contain Persian and English letters, Persian and English numbers, and hyphens (-). Any other characters will result in a validation error.", example="2"),
     *             @OA\Property(property="color", type="string"),
     *             @OA\Property(property="price_increase", type="float", example=60000),
     *             )
     * )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="successful Guarantee creation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="true"),
     *             @OA\Property(property="message", type="string", example="رنگ با موفقیت افزوده شد"),
     *            
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="invalid request",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="نام الزامی است")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="خطای غیرمنتظره در سرور رخ داده است. لطفاً دوباره تلاش کنید.")
     *     )
     *)
     * )
     */
    public function store(Request $request, Product $product)
    {
        $validated = $request->validate([
            'color_name' => 'required|max:120|min:2|regex:/^[ا-یa-zA-Z0-9\-۰-۹ء-ي.,، ]+$/u',
            'color' => 'required|max:120',
            'price_increase' => 'required|numeric',
            // 'g-recaptcha-response' => 'recaptcha',
        ]);
        try{
        $inputs = $request->all();
        $inputs['product_id'] = $product->id;
        $color = ProductColor::create($inputs);
        
            return response()->json([
                'status' => true,
                'message' => 'رنگ با موفقیت افزوده شد'
            ], 201);
        } catch(Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'خطایی غیرمنتطره در سرور رخ داده است. لطفا دوباره تلاش کنید'
            ], 500);
        }
    }

     /**
     * @OA\Get(
     *     path="/api/admin/market/product/color/status/{color}",
     *     summary="Change the status of a color",
     *     description="This endpoint `toggles the status of a ProductColor` (active/inactive)",
     *     operationId="updateProductColorStatus",
     *     security={{"bearerAuth": {}}},
     *     tags={"ProductColor"},
     *     @OA\Parameter(
     *         name="color",
     *         in="path",
     *         description="Color id to change the status",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     security={ {"bearerAuth": {}} },
     *     @OA\Response(
     *         response=200,
     *         description="Color status updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="checked", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="وضعیت رنگ محصول x با موفقیت فعال شد")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="عملیات با خطا مواجه شد. دوباره امتحان کنید")
     *         )
     *     )
     * )
     */
    public function status(ProductColor $color)
    {
        $color->status = $color->status == 1 ? 2 : 1;
        $result = $color->save();
        if ($result) {
            if ($color->status == 1) {
                return response()->json([
                    'status' => true,
                    'checked' => true,
                    'message' => 'وضعیت ' . $color->color_name . ' با موفقیت فعال شد'
                ]);
            } else {
                return response()->json([
                    'status' => true,
                    'checked' => false,
                    'message' => 'وضعیت ' . $color->color_name . ' با موفقیت غیرفعال شد'
                ]);
            }
        } else {
            return response()->json([
                'status' => false,
                'message' => 'عملیات با خطا مواجه شد. دوباره امتحان کنید'
            ]);
        }
    }

     /**
     * @OA\Put(
     *     path="/api/admin/market/product/color/update/{color}",
     *     summary="update an existing ProductColor",
     *     description="this method updates an existing `ProductColor` for the product and stores it.",
     *     tags={"ProductColor"},
     *     security={{"bearerAuth": {}}},
     *   @OA\Parameter(
     *         name="color",
     *         in="path",
     *         description="ID of the ProductColor to fetch",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *             @OA\Property(property="color_name", type="string", pattern="^[a-zA-Z\u0600-\u06FF0-9\s\-\.\?]+$", description="This field can only contain Persian and English letters, Persian and English numbers, and hyphens (-). Any other characters will result in a validation error.", example="سفید"),
     *             @OA\Property(property="color", type="#111111"),
     *             @OA\Property(property="price_increase", type="float", example=600000),
     *             )
     * )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="successful ProductColor update",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="true"),
     *             @OA\Property(property="message", type="string", example="رنگ با موفقیت بروزرسانی شد"),
     *            
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="invalid request",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="نام الزامی است")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="خطای غیرمنتظره در سرور رخ داده است. لطفاً دوباره تلاش کنید.")
     *     )
     *)
     * )
     */
    public function update(Request $request, ProductColor $color)
    {
        $validated = $request->validate([
            'color_name' => 'required|max:120|min:2|regex:/^[ا-یa-zA-Z0-9\-۰-۹ء-ي.,، ]+$/u',
            'color' => 'required|max:120',
            'price_increase' => 'required|numeric',
            // 'g-recaptcha-response' => 'recaptcha',
        ]);
        try{
        $inputs = $request->all();
        $update = $color->update($inputs);
        
            return response()->json([
                'status' => true,
                'message' => 'رنگ با موفقیت ویرایش شد'
            ], 200);
        } catch(Exception $e) {
            return response()->json([
                'status' => false,
                'message'=> 'خطایی غیرمنتظره در سرور رخ داده است. لطفا دوباره تلاش کنید'
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/admin/market/product/color/destroy/{color}",
     *     summary="Delete a ProductColor",
     *     description="This endpoint allows the user to `delete an existing ProductColor`.",
     *     operationId="deleteProductColor",
     *     tags={"ProductColor"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="color",
     *         in="path",
     *         description="The ID of the ProductColor to be deleted",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="ProductColor deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="رنگ با موفقیت حذف شد")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="خطایی غیرمنتظره در سرور رخ داده است. لطفا دوباره تلاش کنید")
     *         )
     *     )
     * )
     */
    public function destroy(ProductColor $color)
    {
        $result = $color->delete();
        if ($result) {
            return response()->json([
                'status' => true,
                'message' => ' رنگ با موفقیت حذف شد'
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'خطایی غیرمنتظره در سرور رخ داده است. لطفا دوباره تلاش کنید'
            ], 500);
        }
    }
}
