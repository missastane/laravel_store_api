<?php

namespace App\Http\Controllers\API\Admin\Market;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Market\StoreRequest;
use App\Models\Market\Product;
use Exception;
use Illuminate\Http\Request;
use Log;

class StoreController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/admin/market/store/store/{product}",
     *     summary="create new value for a ProductStore",
     *     description="this method `increase ProductStore` for the product and stores it.",
     *     tags={"Store"},
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
     *            @OA\Property(property="receiver", type="string", pattern="^[a-zA-Z\u0600-\u06FF0-9\s\-\.\?]+$", description="This field can only contain Persian and English letters, Persian and English numbers, and hyphens (-). Any other characters will result in a validation error.", example="دریافت کننده"),
     *             @OA\Property(property="deliverer", type="string", pattern="^[a-zA-Z\u0600-\u06FF0-9\s\-\.\?]+$", description="This field can only contain Persian and English letters, Persian and English numbers, and hyphens (-). Any other characters will result in a validation error.", example="تحویل دهنده"),
     *             @OA\Property(property="description", type="string", pattern="^[a-zA-Z\u0600-\u06FF0-9\s\-\.\?]+$", description="This field can only contain Persian and English letters, Persian and English numbers, and hyphens (-). Any other characters will result in a validation error.", example="توضیحات"),
     *             @OA\Property(property="marketable_number", type="number", example=6),
     *             )
     * )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="successful ProductStore creation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="true"),
     *             @OA\Property(property="message", type="string", example="موجودی محصول x با موفقیت افزایش یافت"),
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
    public function store(StoreRequest $request, Product $product)
    {
        try {
            $product->marketable_number += $request->marketable_number;
            $product->save();
            Log::info("receiver => {$request->receiver}, deliverer => {$request->deliverer}, description => {$request->description}, add => {$request->marketable_number}");
            return response()->json([
                'status' => true,
                'message' => 'موجودی محصول ' . $product->name . ' با موفقیت افزایش یافت'
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'خطایی غیرمنتظره در سرور رخ داده است. لطفا دوباره تلاش کنید'
            ]);
        }
    }


    /**
     * @OA\Put(
     *     path="/api/admin/market/store/update/{product}",
     *     summary="update an existing Product Store",
     *     description="this method updates an existing `Store` for the product and stores it.",
     *  tags={"Store"},
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
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *             @OA\Property(property="marketable_number", type="number", example=6),
     *             @OA\Property(property="sold_number", type="number", example=6),
     *             @OA\Property(property="frozen_number", type="number", example=6),
     *             )
     * )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="successful Store update",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="true"),
     *             @OA\Property(property="message", type="string", example="موجودی محصول X با موفقیت بروزرسانی شد"),
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
    public function update(Product $product, Request $request)
    {
        $validated = $request->validate([
            'marketable_number' => 'required|numeric',
            'sold_number' => 'required|numeric',
            'frozen_number' => 'required|numeric',
            // 'g-recaptcha-response' => 'recaptcha',
        ]);
        try {
            $inputs = $request->all();
            $update = $product->update($inputs);
            return response()->json([
                'status' => true,
                'message' => 'موجودی محصول ' . $product->name . ' با موفقیت بروزرسانی شد'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'خطایی غیرمنتظره در سرور رخ داده است. لطفا دوباره تلاش کنید'
            ], 500);
        }
    }


}
