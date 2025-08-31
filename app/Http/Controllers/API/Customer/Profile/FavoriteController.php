<?php

namespace App\Http\Controllers\API\Customer\Profile;

use App\Http\Controllers\Controller;
use App\Models\Market\Product;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{

    /**
     * @OA\Delete(
     *     path="/api/my-favorites/remove/{product}",
     *     summary="Remove a product from the favorites list",
     *     description="Removes a product from the authenticated user's favorites list.",
     *     operationId="removeFavorite",
     *     tags={"Favorite","Market"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="product",
     *         in="path",
     *         required=true,
     *         description="ID of the product to remove from favorites",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product successfully removed from favorites",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="محصول از لیست علاقه‌مندی پاک شد")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Unexpected server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="خطایی غیرمنتظره در سرور رخ داده است. لطفا دوباره تلاش کنید")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not Found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="محصول مورد نظر در لیست علاقمندی ها وجود ندارد")
     *         )
     *     )
     * )
     */
    public function remove(Product $product)
    {
        try {
            $user = auth()->user();
            if ($user->products()->where('product_id', $product->id)->exists()) {
                $user->products()->detach($product->id);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'محصول مورد نظر در لیست علاقمندی ها وجود ندارد'
                ], 404);
            }
            return response()->json([
                'status' => true,
                'message',
                'محصول از لیست علاقه مندی پاک شد'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'خطایی غیرمنتظره در سرور رخ داده است. لطفا دوباره تلاش کنید'
            ], 500);
        }
    }
}
