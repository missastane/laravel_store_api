<?php

namespace App\Http\Controllers\API\Admin\Market;

use App\Http\Controllers\Controller;
use App\Models\Market\CategoryAttribute;
use App\Models\Market\CategoryValue;
use App\Models\Market\Product;
use Exception;
use Illuminate\Http\Request;

class ProductPropertiesController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/admin/market/product/properties/{product}",
     *     summary="Retrieve list of product attributes",
     *     description="Retrieve list of all `categoryAtttributes` with their `category` which belongs to a product",
     *   operationId="getProductProperties",
     *     tags={"ProductProperty"},
     *     security={
     *         {"bearerAuth": {}}},
     *  @OA\Parameter(
     *         name="product",
     *         in="path",
     *         description="Id of Product that we want its attributes",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *      @OA\Response(
     *         response=200,
     *         description="list of productProperties with their category",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="array",
     *                 @OA\Items(
     *                     ref="#/components/schemas/CategoryAttribute"
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function properties(Product $product)
    {
        $category_attributes = CategoryAttribute::with('category:name,id')->where('category_id', $product->category_id)->orderBy('created_at', 'desc')->simplePaginate(15);
        $category_attributes->getCollection()->each(function ($item) {
            $item->category->makeHidden(['status_value', 'show_in_menu_value']); 
        });
        return response()->json([
            'data' => $category_attributes
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/market/product/properties/search/{product}",
     *     summary="Searches among productAttributes by name",
     *     description="This endpoint allows users to search for `categoryAttributes` by name.These attributes belongs to a `category` of product The search is case-insensitive and returns results that contain the given keyword. The results are paginated for better performance",
     *   operationId="searchProductProperties",
     *     tags={"ProductProperty"},
     *     security={
     *         {"bearerAuth": {}}},
     *  @OA\Parameter(
     *         name="product",
     *         in="path",
     *         description="Id of Product that we want its attributes",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *   @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="type name of property which you're searching for",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *      @OA\Response(
     *         response=200,
     *         description="list of productProperties with their category",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="array",
     *                 @OA\Items(
     *                     ref="#/components/schemas/CategoryAttribute"
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function search(Request $request, Product $product)
    {
        $category_attributes = CategoryAttribute::where('category_id', $product->category->id)->where('name', 'LIKE', "%" . $request->search . "%")->with('category:name,id')->orderBy('name')->simplePaginate(15);
        $category_attributes->getCollection()->each(function ($item) {
            $item->category->makeHidden(['status_value', 'show_in_menu_value']); 
        });
        return response()->json([
            'data' => $category_attributes
        ], 200);
    }


     /**
     * @OA\Get(
     *     path="/api/admin/market/product/properties/show/{attribute}",
     *     summary="Shows productAttribute by id",
     *     description="This endpoint provides the necessary data for editing or creating a ProductValue or editing productAttribute a and allows users to show `categoryAttribute` with its category",
     *     operationId="showProductProperty",
     *     tags={"ProductProperty", "ProductProperty/Form"},
     *     security={
     *         {"bearerAuth": {}}},
     *  @OA\Parameter(
     *         name="attribute",
     *         in="path",
     *         description="Id of Attribute that we want",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *      @OA\Response(
     *         response=200,
     *         description="`CategoryAttribute` object with its category",
     *        @OA\JsonContent(ref="#/components/schemas/CategoryAttribute"),
     *     )
     * )
     */
    public function show(CategoryAttribute $attribute)
    {
        $attribute->load('category:name,id');
        $attribute->category->makeHidden(['status_value', 'show_in_menu_value']);
        return response()->json([
            'data' => $attribute
        ], 200);
    }
    /**
     * @OA\Post(
     *     path="/api/admin/market/product/properties/{product}",
     *     summary="create new property for the product",
     *     description="this method creates a new property for the product and stores it.",
     *     tags={"ProductProperty"},
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
     *             @OA\Property(property="name", type="string", pattern="^[a-zA-Z\u0600-\u06FF0-9\s\-\.\?]+$", description="This field can only contain Persian and English letters, Persian and English numbers, and hyphens (-). Any other characters will result in a validation error.", example="پردازنده"),
     *             @OA\Property(property="unit", type="string", pattern="^[a-zA-Z\u0600-\u06FF0-9\s\-\.\?]+$", description="This field can only contain Persian and English letters, Persian and English numbers, and hyphens (-). Any other characters will result in a validation error.", example="هرتز"),
     *             )
     * )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="successful product attribute creation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="true"),
     *             @OA\Property(property="message", type="string", example="ویژگی با موفقیت افزوده شد")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="invalid request",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="نام ویژگی الزامی است")
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
    public function storeProperties(Product $product, Request $request)
    {
        $request->validate([
            'name' => 'required|max:120|min:1|regex:/^[ا-یa-zA-Z0-9\-۰-۹ء-ي.,، ]+$/u',
            'unit' => 'required|max:120|min:1|regex:/^[ا-یa-zA-Z0-9\-۰-۹ء-ي.,، ]+$/u',
            // 'g-recaptcha-response' => 'recaptcha',
        ]);
        try {
            $inputs = $request->all();
            $inputs['category_id'] = $product->category->id;
            CategoryAttribute::create($inputs);
            return response()->json([
                'status' => true,
                'message' => 'ویژگی با موفقیت افزوده شد',
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'خطای غیر منتظره در سرور رخ داده است. لطفا دوباره تلاش کنید',
            ], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/admin/market/product/properties/{attribute}",
     *     summary="update existing property of the product",
     *     description="this method updates exising property of the product and saves changes",
     *     tags={"ProductProperty"},
     *     security={{"bearerAuth": {}}},
     *   @OA\Parameter(
     *         name="attribute",
     *         in="path",
     *         description="ID of the attribute to update",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *             @OA\Property(property="name", type="string", pattern="^[a-zA-Z\u0600-\u06FF0-9\s\-\.\?]+$", description="This field can only contain Persian and English letters, Persian and English numbers, and hyphens (-). Any other characters will result in a validation error.", example="پردازنده"),
     *             @OA\Property(property="unit", type="string", pattern="^[a-zA-Z\u0600-\u06FF0-9\s\-\.\?]+$", description="This field can only contain Persian and English letters, Persian and English numbers, and hyphens (-). Any other characters will result in a validation error.", example="هرتز"),
     *             )
     * )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="successful product attribute update",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="true"),
     *             @OA\Property(property="message", type="string", example="ویژگی با موفقیت بروزرسانی شد")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="invalid request",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="نام ویژگی الزامی است")
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
    public function updateProperties(CategoryAttribute $attribute, Request $request)
    {
        $request->validate([
            'name' => 'required|max:120|min:1|regex:/^[ا-یa-zA-Z0-9\-۰-۹ء-ي.,، ]+$/u',
            'unit' => 'required|max:120|min:1|regex:/^[ا-یa-zA-Z0-9\-۰-۹ء-ي.,، ]+$/u',
            // 'g-recaptcha-response' => 'recaptcha',
        ]);
        try {
            $inputs = $request->all();
            $inputs['category_id'] = $attribute->category_id;
            $attribute->update($inputs);
            return response()->json([
                'status' => true,
                'message' => 'ویژگی با موفقیت بروزرسانی شد',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'خطای غیرمنتظره ای در سرور رخ داده است. لطفا مجددا تلاش کنید',
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/admin/market/product/property-values/{product}/{attribute}",
     *     summary="Retrieve list of values of a special attribute of especial product",
     *     description="Retrieve list of all `categoryValues` which belongs to a special productAttribute",
     *     operationId="getProductProperty Values",
     *     tags={"ProductProperty"},
     *     security={
     *         {"bearerAuth": {}}},
     *  @OA\Parameter(
     *         name="product",
     *         in="path",
     *         description="Id of Product that we want its attribute values",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *  @OA\Parameter(
     *         name="attribute",
     *         in="path",
     *         description="Id of attribute that we want its attribute values",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *      @OA\Response(
     *         response=200,
     *         description="list of productProperty values",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="array",
     *                 @OA\Items(
     *                     ref="#/components/schemas/CategoryValue"
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function propertyValues(Product $product, CategoryAttribute $attribute)
    {
        $productValues = $attribute->values()->where(['product_id' => $product->id])->get();
        return response()->json([
            'data' => $productValues
        ], 200);
    }



    /**
     * @OA\Post(
     *     path="/api/admin/market/product/property-values/store/{product}/{attribute}",
     *     summary="create new value for product attribute",
     *     description="this method creates a new `propertyValue` for the product and stores it.",
     *     tags={"ProductProperty"},
     *     security={{"bearerAuth": {}}},
     *   @OA\Parameter(
     *         name="product",
     *         in="path",
     *         description="ID of the product to fetch",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Parameter(
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
     *             @OA\Property(property="type", type="integer", enum={1, 2},  description="value type: 1 = multiple values select by customers (effects on price), 2 = simple", example=1)
     * 
     *             )
     * )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="successful product attributeValue creation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="true"),
     *             @OA\Property(property="message", type="string", example="مقدار با موفقیت افزوده شد"),
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
    public function storePropertyValue(Product $product, CategoryAttribute $attribute, Request $request)
    {
        $request->validate([
            'value' => 'required|max:120|min:1|regex:/^[ا-یa-zA-Z0-9\-۰-۹ء-ي.,،::. ]+$/u',
            'price_increase' => 'required|regex:/^[0-9\.]+$/u',
            'type' => 'required|numeric|in:1,2',
            // 'g-recaptcha-response' => 'recaptcha',
        ]);
        try {
            $inputs = $request->all();
            $inputs['product_id'] = $product->id;
            $inputs['value'] = json_encode(['value' => $request->value, 'price_increase' => $request->price_increase]);
            $inputs['category_attribute_id'] = $attribute->id;
            CategoryValue::create($inputs);
            return response()->json([
                'status' => true,
                'message' => 'مقدار با موفقیت افزوده شد',
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'خطای غیرمنتظره ای در سرور رخ داده است. لطفا مجددا تلاش کنید',
            ], 500);
        }
    }


    /**
     * @OA\Put(
     *     path="/api/admin/market/product/property-values/update/{value}",
     *     summary="update existing productAttribute value",
     *     description="this method updates existing `propertyValue` for the product and save changes.",
     *     tags={"ProductProperty"},
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
     *             @OA\Property(property="type", type="integer", enum={1, 2},  description="value type: 1 = multiple values select by customers (effects on price), 2 = simple", example=1)
     * 
     *             )
     * )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="successful product attributeValue update",
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
    public function updatePropertyValue(CategoryValue $value, Request $request)
    {
        $request->validate([
            'value' => 'required|max:120|min:1|regex:/^[ا-یa-zA-Z0-9\-۰-۹ء-ي.,،::. ]+$/u',
            'price_increase' => 'required|regex:/^[0-9\.]+$/u',
            'type' => 'required|numeric|in:1,2',
            // 'g-recaptcha-response' => 'recaptcha',
        ]);
        try {
            $inputs = $request->all();
            $inputs['product_id'] = $value->product_id;
            $inputs['category_attribute_id'] = $value->category_attribute_id;
            $inputs['value'] = ['value' => $request->value, 'price_increase' => $request->price_increase];
            $value->update($inputs);
            return response()->json([
                'status' => true,
                'message' => 'مقدار با موفقیت بروزرسانی شد',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'خطای غیرمنتظره ای در سرور رخ داده است. لطفا مجددا تلاش کنید',
            ], 500);
        }

    }
}
