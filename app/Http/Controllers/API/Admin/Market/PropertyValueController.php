<?php

namespace App\Http\Controllers\API\Admin\Market;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Market\CategoryValueRequest;
use App\Models\Market\CategoryAttribute;
use App\Models\Market\CategoryValue;
use App\Models\Market\Product;
use Exception;
use Illuminate\Http\Request;

class PropertyValueController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/admin/market/property/value/{attribute}",
     *     summary="Retrieve Values of special peoperty and products which have same category with attribute for edit or create form",
     *     description="Retrieve `CategoryValues` of special peoperty  and also get list of `products` which have same category with attribute for create or edit method",
     *     tags={"CategoryValue","CategoryValue/Form"},
     *     @OA\Parameter(
     *         name="attribute",
     *         in="path",
     *         description="attribute id to fetch its value",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *     @OA\Response(
     *         response=200,
     *         description="A list of values which belongs to a special Property",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="values",
     *                     type="array",
     *                     @OA\Items(
     *                       ref="#/components/schemas/CategoryValue"
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="products",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer"),
     *                         @OA\Property(property="name", type="string")        
     *                     )
     *                 )
     *             )
     *         )
     * )
     * )
     */
    public function index(CategoryAttribute $attribute)
    {
        $products = Product::where('category_id', $attribute->category_id)->select('id', 'name')->get()->makeHidden(['status_value', 'marketable_value', 'related_products_value']);
        $values = $attribute->values()->with(['attribute:id,name','product:id,name'])->simplePaginate(15);
        $values->getCollection()->each(function ($item) {
            $item->product->makeHidden(['status_value', 'marketable_value', 'related_products_value']);
        });
        return response()->json([
            'data' => [
                "value" => $values,
                "products" => $products
            ]
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/market/property/value/show/{value}",
     *     summary="Get details of a specific value",
     *     description="Returns the `CategoryValue` details along with attribute and product for edit form",
     *     operationId="getValueDetails",
     *     tags={"CategoryValue","CategoryValue/Form"},
     *   security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="value",
     *         in="path",
     *         description="ID of the value to fetch",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully fetched value details with product and attribute for editing",
     *        @OA\JsonContent(ref="#/components/schemas/CategoryValue"),
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="An error occurred. Please try again.")
     *         )
     *     )
     * )
     */
    public function show(CategoryValue $value)
    {   $value->load('attribute:id,name', 'product:id,name');
        $value->product->makeHidden(['status_value', 'marketable_value', 'related_products_value']);
        return response()->json([
            'data' => $value

        ], 200);
    }


    /**
     * @OA\Post(
     *     path="/api/admin/market/property/value/{attribute}/store",
     *     summary="create new value for an attribute",
     *     description="this method creates a new `CategoryValue` for the attribute and stores it.",
     *  tags={"CategoryValue"},
     *     security={{"bearerAuth": {}}},
     *   @OA\Parameter(
     *         name="attribute",
     *         in="path",
     *         description="ID of the attribute to fetch",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *             @OA\Property(property="value", type="string", pattern="^[a-zA-Z\u0600-\u06FF0-9\s\-\.\?]+$", description="This field can only contain Persian and English letters, Persian and English numbers, and hyphens (-). Any other characters will result in a validation error.", example="2"),
     *             @OA\Property(property="price_increase", type="float", example=60000),
     *             @OA\Property(property="type", type="integer", enum={1, 2},  description="value type: 1 = multiple values select by customers (effects on price), 2 = simple", example=1),
     *             @OA\Property(property="product_id",description="Product ID. This must be provided when creating or updating the CategoryValue.", type="integer", example=5),
     * 
     *             )
     * )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="successful CategoryValue creation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="true"),
     *             @OA\Property(property="message", type="string", example="مقدار جدید برای attributeName موفقیت ثبت شد"),
     *            
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="invalid request",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="مقدار ویژگی الزامی است")
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
    public function store(CategoryValueRequest $request, CategoryAttribute $attribute)
    {
        try {
            $inputs = $request->all();
            $inputs['value'] = ['value' => $request->value, 'price_increase' => $request->price_increase];
            $inputs['category_attribute_id'] = $attribute->id;
            CategoryValue::create($inputs);
            return response()->json([
                'status' => true,
                'message' => 'مقدار جدید برای ' . $attribute->name . ' با موفقیت ثبت شد'
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'خطایی غیرمنتظره در سرور رخ داده است. لطفا دوباره تلاش کنید'
            ], 500);
        }
    }


    /**
     * @OA\Put(
     *     path="/api/admin/market/property/value/update/{value}",
     *     summary="Update an existing value",
     *     description="this method updates `CategoryValue` and stores it.",
     *     tags={"CategoryValue"},
     *     security={{"bearerAuth": {}}},
     *   @OA\Parameter(
     *         name="value",
     *         in="path",
     *         description="ID of the value to fetch",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *             @OA\Property(property="value", type="string", pattern="^[a-zA-Z\u0600-\u06FF0-9\s\-\.\?]+$", description="This field can only contain Persian and English letters, Persian and English numbers, and hyphens (-). Any other characters will result in a validation error.", example="2"),
     *             @OA\Property(property="price_increase", type="float", example=60000),
     *             @OA\Property(property="type", type="integer", enum={1, 2},  description="value type: 1 = multiple values select by customers (effects on price), 2 = simple", example=1),
     *             @OA\Property(property="product_id",description="Product ID. This must be provided when creating or updating the CategoryValue.", type="integer", example=5),
     * 
     *             )
     * )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="successful CategoryValue update",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="true"),
     *             @OA\Property(property="message", type="string", example="مقدار با موفقیت بروزرسانی شد"),
     *            
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="invalid request",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="مقدار ویژگی الزامی است")
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
    public function update(CategoryValueRequest $request, CategoryValue $value)
    {
        try {
            $inputs = $request->all();
            $inputs['value'] = ['value' => $request->value, 'price_increase' => $request->price_increase];
            $inputs['category_attribute_id'] = $value->category_attribute_id;
            $update = $value->update($inputs);

            return response()->json([
                'status' => true,
                'message' => 'مقدار با موفقیت بروزرسانی شد'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'خطایی غیرمنتظره در سرور رخ داده است. لطفا دوباره تلاش کنید'
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/admin/market/property/value/destroy/{value}",
     *     summary="Delete a CtegoryValue",
     *     description="This endpoint allows the user to delete an existing `CtegoryValue` for a product.",
     *     operationId="deleteCategoryValu",
     *     tags={"CategoryValue"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="value",
     *         in="path",
     *         description="The ID of the value to be deleted",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="CategoryValue deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="مقدار با موفقیت حذف شد")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="مشکلی پیش آمده است. لطفا دوباره امتحان کنید")
     *         )
     *     )
     * )
     */
    public function destroy(CategoryValue $value)
    {
        try {
            $value->delete();
            return response()->json([
                'status' => true,
                'message' => 'مقدار با موفقیت حذف شد'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'مشکلی پیش آمده است. لطفا دوباره امتحان کنید'
            ], 500);
        }
    }

}
