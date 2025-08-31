<?php

namespace App\Http\Controllers\API\Admin\Market;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Market\CategoryAttributeRequest;
use App\Models\Market\Category;
use App\Models\Market\CategoryAttribute;
use Exception;
use Illuminate\Http\Request;

class PropertyController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/admin/market/property",
     *     summary="Retrieve list of CategoryAttributes with their category",
     *     description="Retrieve list of all CategoryAttributes with their category",
     *  tags={"CategoryAttribute"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *     @OA\Response(
     *         response=200,
     *         description="A list of CategoryAttributes with their category",
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
    public function index()
    {
        $category_attributes = CategoryAttribute::with('category:name,id')->orderBy('created_at', 'desc')->simplePaginate(15);
        $category_attributes->getCollection()->each(function ($item) {
            $item->category->makeHidden(['status_value', 'show_in_menu_value']); 
        });
        return response()->json([
            'data' => $category_attributes
        ], 200);
    }


    /**
     * @OA\Get(
     *     path="/api/admin/market/property/search",
     *     summary="Searches among CategoryAttributes by name.",
     *     description="This endpoint allows users to search for `CategoryAttributes` by name. The search is case-insensitive and returns results that contain the given keyword. The results are paginated for better performance",
     *  tags={"CategoryAttribute"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *   @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="type name of CategoryAttribute which you're searching for",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="A list of CategoryAttributes with their category",
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
    public function search(Request $request)
    {
        $category_attributes = CategoryAttribute::where('name', 'LIKE', "%" . $request->search . "%")->with('category:name,id')->orderBy('name')->simplePaginate(15);
        $category_attributes->getCollection()->each(function ($item) {
            $item->category->makeHidden(['status_value', 'show_in_menu_value']); 
        });
        return response()->json([
            'data' => $category_attributes
        ], 200);
    }


    /**
     * @OA\Get(
     *     path="/api/admin/market/property/show/{attribute}",
     *     summary="Get details of a specific attribute",
     *     description="Returns the `attribute` details along with category and provide details for edit method",
     *     operationId="getAttributeDetails",
     *     tags={"CategoryAttribute", "CategoryAttribute/Form"},
     *   security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="attribute",
     *         in="path",
     *         description="ID of the attribute to fetch",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully fetched attribute details with category for editing",
     *         @OA\JsonContent(ref="#/components/schemas/CategoryAttribute"),
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
     *     path="/api/admin/market/property/store",
     *     summary="create new property",
     *     description="this method creates a new property and stores it.",
     *  tags={"CategoryAttribute"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *             @OA\Property(property="name", type="string", pattern="^[a-zA-Z\u0600-\u06FF0-9\s\-\.\?]+$", description="This field can only contain Persian and English letters, Persian and English numbers, and hyphens (-). Any other characters will result in a validation error.", example="پردازنده"),
     *             @OA\Property(property="unit", type="string", pattern="^[a-zA-Z\u0600-\u06FF0-9\s\-\.\?]+$", description="This field can only contain Persian and English letters, Persian and English numbers, and hyphens (-). Any other characters will result in a validation error.", example="هرتز"),
     *             @OA\Property(property="category_id",description="Category ID. This must be provided when creating or updating the product.", type="integer", example=5),
     *             )
     * )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="successful product attribute creation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="true"),
     *             @OA\Property(property="message", type="string", example="فرم propertyName با موفقیت ثبت شد")
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
     *   )
     * )
     */

    public function store(CategoryAttributeRequest $request)
    {
        try {
            $inputs = $request->all();
            $attribute = CategoryAttribute::create($inputs);
            return response()->json([
                'status' => true,
                'message' => 'فرم ' . $attribute->name . ' با موفقیت ثبت شد'
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'خطایی غیرمنتظره در سرور رخ داده است. لطفا دوباره تلاش کنید'
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/admin/market/property/options",
     *     summary="Get necessary options for property forms",
     *     description="This endpoint returns all `productCategories` which can be used to create a new property or edit method",
     *     tags={"CategoryAttribute", "CategoryAttribute/Form"},
     *  security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successfully fetched product categories that you may need to make edit,create,.. pages",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="productCategories",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer"),
     *                         @OA\Property(property="name", type="string"),
     *                         @OA\Property(property="status_value", type="string", example="فعال"),
     *                         @OA\Property(property="show_in_menu_value", type="string", example="فعال"),
     *                     )
     *                 )
     *             )
     *         )
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

    public function options()
    {
        $categories = Category::select('name', 'id')->get();
        return response()->json([
            'data' => $categories
        ], 200);
    }


    /**
     * @OA\Put(
     *     path="/api/admin/market/property/update/{attribute}",
     *     summary="update an existing property",
     *     description="this method updates an existing property and stores it.",
     *     tags={"CategoryAttribute"},
     *     security={{"bearerAuth": {}}},
     *  @OA\Parameter(
     *         name="attribute",
     *         in="path",
     *         description="The ID of the attribute to be updated",
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
     *             @OA\Property(property="category_id",description="Category ID. This must be provided when creating or updating the product.", type="integer", example=5),
     *             )
     * )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="successful product attribute update",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="true"),
     *             @OA\Property(property="message", type="string", example="فرم propertyName با موفقیت بروزرسانی شد")
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
     *   )
     * )
     */
    public function update(CategoryAttribute $attribute, CategoryAttributeRequest $request)
    {
        try {
            $inputs = $request->all();
            $attribute->update($inputs);
            return response()->json([
                'status' => true,
                'message' => 'فرم ' . $attribute->name . ' با موفقیت بروزرسانی شد'
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
     *     path="/api/admin/market/property/destroy/{attribute}",
     *     summary="Delete a product",
     *     description="This endpoint allows the user to delete an existing attribute.",
     *     operationId="deleteAttribute",
     *     tags={"CategoryAttribute"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="attribute",
     *         in="path",
     *         description="The ID of the attribute to be deleted",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Attribute deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="فرم Example attribute با موفقیت حذف شد")
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
    public function destroy(CategoryAttribute $attribute)
    {
        try {
            $result = $attribute->delete();
            return response()->json([
                'status' => true,
                'message' => 'فرم ' . $attribute->name . ' با موفقیت حذف شد'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'خطایی غیرمنتظره در سرور رخ داده است. لطفا دوباره تلاش کنید'
            ], 500);
        }

    }
}
