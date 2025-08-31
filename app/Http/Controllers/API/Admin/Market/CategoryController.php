<?php

namespace App\Http\Controllers\API\Admin\Market;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Market\CategoryRequest;
use App\Http\Services\Image\ImageService;
use App\Models\Market\Category;
use App\Models\Market\CategoryAttribute;
use App\Models\Tag;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/admin/market/category",
     *     summary="Retrieve list of categories",
     *     description="Retrieve list of all `Categories`",
     *     tags={"ProductCategory"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *     @OA\Response(
     *         response=200,
     *         description="A list of categories with their Parents and Tags",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="array",
     *                 @OA\Items(
     *                     ref="#/components/schemas/Category"
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function index()
    {
        $categories = Category::with('parent:name,id', 'tags:id,name')->orderBy('created_at', 'desc')->simplePaginate(15);
        $categories->getCollection()->each(function ($item) {
            if (isset($item->parent)) {
                $item->parent->makeHidden(['status_value', 'show_in_menu_value', 'parent']);
            }
            $item->tags->makeHidden(['pivot']);
        });
        return response()->json([
            'data' => $categories
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/market/category/search",
     *     summary="Searchs among Categories by name",
     *     description="This endpoint allows users to search for `Categories` by name. The search is case-insensitive and returns results that contain the given keyword. The results are paginated for better performance",
     *     tags={"ProductCategory"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *   @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="type name of Category which you're searching for",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="A list of Categories with their Parent and Tags",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="array",
     *                 @OA\Items(
     *                     ref="#/components/schemas/Category"
     *                 )
     *             )
     *         )
     *     )
     * )
     */

    public function search(Request $request)
    {
        $categories = Category::where('name', 'LIKE', "%" . $request->search . "%")->with('parent:name,id', 'tags:id,name')->orderBy('name')->simplePaginate(15);
        $categories->getCollection()->each(function ($item) {
            if (isset($item->parent)) {
                $item->parent->makeHidden(['status_value', 'show_in_menu_value', 'parent']);
            }
            $item->tags->makeHidden(['pivot']);
        });
        return response()->json([
            'data' => $categories
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/market/category/show/{category}",
     *     summary="Get details of a specific Category",
     *     description="Returns the `Category` details along with tags and provide details for edit method. also `productCategories` in this method is specially provided details for edit form",
     *     operationId="getCategoryDetails",
     *     tags={"ProductCategory", "ProductCategory/Form"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="category",
     *         in="path",
     *         description="ID of the Category to fetch",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully fetched Category details with tags for editing",
     *     @OA\JsonContent(
     *         @OA\Property(
     *             property="data",
     *             type="object",
     *             @OA\Property(
     *                 property="category",
     *                 ref="#/components/schemas/Category"  
     *             ),
     *             @OA\Property(
     *                 property="productCategories",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="کالای دیجیتال")
     *                 )
     *             ),
     *         )
     *     )
     *   )
     * )
     */

    public function show(Category $category)
    {
        // this productCategories will use for edit
        $productCategories = Category::where('id', '!=', $category->id)->select(['name', 'id'])->get()->makeHidden(['status_value', 'show_in_menu_value']);
        $category->load('parent:name,id', 'tags:id,name');
        if (isset($category->parent)) {
            $category->parent->makeHidden(['status_value', 'show_in_menu_value', 'parent']);
        }
        $category->tags->makeHidden(['pivot']);
        return response()->json([
            'data' => [
                'category' => $category,
                'productCategories' => $productCategories
            ]
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/market/category/options",
     *     summary="Get necessary options for ProductCategory forms",
     *     description="This endpoint returns all `productCategories` which can be used to create a new product",
     *     tags={"ProductCategory", "ProductCategory/Form"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successfully fetched product categories that you may need to make create form",
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
     *                         @OA\Property(property="name", type="string")
     *                     )
     *                 ),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *             @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="خطای غیرمنتظره در سرور رخ داده است. لطفاً دوباره تلاش کنید.")
     *           )
     *     )
     * )
     */
    public function options()
    {
        $productCategories = Category::select(['name', 'id'])->get()->makeHidden(['status_value', 'show_in_menu_value']);
        return response()->json([
            'data' => $productCategories
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/admin/market/category/store",
     *     summary="create new category",
     *     description="this method creates a new `ProductCategory` and stores its related tags.",
     *     tags={"ProductCategory"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(
     *                     property="image",
     *                     type="string",
     *                     format="binary"
     *         ),
     *             @OA\Property(property="name", type="string", pattern="^[a-zA-Z\u0600-\u06FF0-9\s\-\.\?]+$", description="This field can only contain Persian and English letters, Persian and English numbers, and hyphens (-). Any other characters will result in a validation error.", example="لوازم ورزشی"),
     *             @OA\Property(property="description", type="string", example="توضیح لوازم ورزشی"),
     *             @OA\Property(
     *                 property="status",
     *                 oneOf={
     *                     @OA\Schema(type="integer", example=1, description="1 = active"),
     *                     @OA\Schema(type="integer", example=2, description="2 = inactive")
     *                 }
     *             ),
     *             @OA\Property(
     *                 property="show_in_menu",
     *                 oneOf={
     *                     @OA\Schema(type="integer", example=1, description="1 = yes"),
     *                     @OA\Schema(type="integer", example=2, description="2 = no")
     *                 }
     *             ),
     *             @OA\Property(property="parent_id",description="ParentID.This field is optional when creating or updating the category.", type="integer", nullable="true", example=5),
     *             @OA\Property(
     *                 property="tags[]",
     *                 type="array",
     *                 @OA\Items(type="string",pattern="^[a-zA-Z\u0600-\u06FF0-9\s\-\.\?]+$", example="لوازم ورزشی"),
     *              description="This field can only contain Persian and English letters, Persian and English numbers, hyphens (-),question marks (?), and periods (.). Any other characters will result in a validation error.",
     *             ),
     *                       ),
     *             encoding={
     *                 "tags[]": {
     *                     "style": "form",
     *                     "explode": true
     *                 }
     *             }
     *             )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="successful category and tags creation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="true"),
     *             @OA\Property(property="message", type="string", example="دسته x با موفقیت افزوده شد")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="invalid request",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="نام دسته بندی الزامی است")
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
    public function store(ImageService $imageService, CategoryRequest $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            if ($request->hasFile('image')) {
                $imageService->setExclusiveDirectory('images' . DIRECTORY_SEPARATOR . 'market' . DIRECTORY_SEPARATOR . 'category');
                $result = $imageService->createIndexAndSave($request->file('image'));
                if ($result === false) {
                    return response()->json([
                        'status' => false,
                        'message' => 'بارگذاری عکس با خطا مواجه شد'
                    ], 422);

                }
                $inputs['image'] = $result;
            }

            $category = Category::create($inputs);
            if ($request->has('tags')) {
                foreach ($request->tags as $tagName) {
                    $tag = Tag::firstOrCreate(['name' => $tagName]);
                    $category->tags()->attach($tag);
                }
            }
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'دسته ' . $category->name . ' با موفقیت افزوده شد'
            ], 201);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'خطایی غیرمنتظره در سرور رخ داده است. لطفا دوباره تلاش کنید'
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/admin/market/category/status/{category}",
     *     summary="Change the status of a category",
     *     description="This endpoint `toggles the status of a ProductCategory` (active/inactive)",
     *     operationId="updateProductCategoryStatus",
     *     security={{"bearerAuth": {}}},
     *     tags={"ProductCategory"},
     *     @OA\Parameter(
     *         name="category",
     *         in="path",
     *         description="ProductCategory id to change the status",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     security={ {"bearerAuth": {}} },
     *     @OA\Response(
     *         response=200,
     *         description="ProductCategory status updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="checked", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="وضعیت دسته x با موفقیت فعال شد")
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
    public function status(Category $category)
    {
        $category->status = $category->status == 1 ? 2 : 1;
        $result = $category->save();
        if ($result) {
            if ($category->status == 1) {
                return response()->json([
                    'status' => true,
                    'checked' => true,
                    'message' => 'وضعیت ' . $category->name . ' با موفقیت فعال شد'
                ]);
            } else {
                return response()->json([
                    'status' => true,
                    'checked' => false,
                    'message' => 'وضعیت ' . $category->name . ' با موفقیت غیرفعال شد'
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
     * @OA\Get(
     *     path="/api/admin/market/category/show-in-menu/{category}",
     *     summary="Change the show-in-menu status of a ProductCategory",
     *     description="This endpoint `toggles the status of a ProductCategory to be Shown in menu` (active/inactive)",
     *     operationId="updateProductCategoryShowInMenuStatus",
     *     security={{"bearerAuth": {}}},
     *     tags={"ProductCategory"},
     *     @OA\Parameter(
     *         name="category",
     *         in="path",
     *         description="ProductCategory id to change the show-in-menu status",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     security={ {"bearerAuth": {}} },
     *     @OA\Response(
     *         response=200,
     *         description="ProductCategory show-in-menu status updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="checked", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="وضعیت نمایش در منوی دسته x با موفقیت فعال شد")
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
    public function showInMenu(Category $category)
    {
        $category->show_in_menu = $category->show_in_menu == 1 ? 2 : 1;
        $result = $category->save();
        if ($result) {
            if ($category->show_in_menu == 1) {
                return response()->json([
                    'status' => true,
                    'checked' => true,
                    'message' => 'امکان نمایش در منو برای ' . $category->name . ' با موفقیت فعال شد'
                ]);
            } else {
                return response()->json([
                    'status' => true,
                    'checked' => false,
                    'message' => 'امکان نمایش در منو برای ' . $category->name . ' با موفقیت غیرفعال شد'
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
     * @OA\Post(
     *     path="/api/admin/market/category/update/{category}",
     *     summary="update an existing category",
     *     description="this method updates an exisiting `ProductCategory` and stores its related tags.",
     *     tags={"ProductCategory"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="category",
     *         in="path",
     *         description="Category id to fetch",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *             @OA\Property(
     *                     property="image",
     *                     type="string",
     *                     format="binary"
     *         ),
     *             @OA\Property(property="name", type="string", pattern="^[a-zA-Z\u0600-\u06FF0-9\s\-\.\?]+$", description="This field can only contain Persian and English letters, Persian and English numbers, and hyphens (-). Any other characters will result in a validation error.", example="لوازم ورزشی"),
     *             @OA\Property(property="description", type="string", example="توضیح لوازم ورزشی"),
     *             @OA\Property(
     *                 property="status",
     *                 oneOf={
     *                     @OA\Schema(type="integer", example=1, description="1 = active"),
     *                     @OA\Schema(type="integer", example=2, description="2 = inactive")
     *                 }
     *             ),
     *             @OA\Property(
     *                 property="show_in_menu",
     *                 oneOf={
     *                     @OA\Schema(type="integer", example=1, description="1 = yes"),
     *                     @OA\Schema(type="integer", example=2, description="2 = no")
     *                 }
     *             ),
     *             @OA\Property(property="parent_id",description="ParentID.This field is optional when creating or updating the category.", type="integer", nullable="true", example=5),
     *             @OA\Property(
     *                 property="tags[]",
     *                 type="array",
     *                 @OA\Items(type="string",pattern="^[a-zA-Z\u0600-\u06FF0-9\s\-\.\?]+$", example="لوازم ورزشی"),
     *              description="This field can only contain Persian and English letters, Persian and English numbers, hyphens (-),question marks (?), and periods (.). Any other characters will result in a validation error.",
     *             ),
     *            @OA\Property(property="_method", type="string", example="PUT"),
     *                       ),
     *             encoding={
     *                 "tags[]": {
     *                     "style": "form",
     *                     "explode": true
     *                 }
     *             }
     *             )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="successful category and tags update",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="true"),
     *             @OA\Property(property="message", type="string", example="دسته x با موفقیت بروزرسانی شد")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="invalid request",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="نام دسته بندی الزامی است")
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
    public function update(Category $category, ImageService $imageService, CategoryRequest $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            if ($request->hasFile('image')) {
                if (!empty($category->image)) {
                    $imageService->deleteDirectoryAndFiles($category->image['directory']);
                }
                $imageService->setExclusiveDirectory('images' . DIRECTORY_SEPARATOR . 'market' . DIRECTORY_SEPARATOR . 'category');
                $result = $imageService->createIndexAndSave($request->file('image'));
                if ($result === false) {
                    return response()->json([
                        'status' => false,
                        'message' => 'بارگذاری عکس با خطا مواجه شد'
                    ], 422);

                }
                $inputs['image'] = $result;
            } else {
                $inputs['image'] = $category->image;
            }

            $update = $category->update($inputs);
            if ($request->has('tags')) {
                $tagIds = [];
                foreach ($request->tags as $tagName) {
                    $tag = Tag::firstOrCreate(['name' => $tagName]);
                    array_push($tagIds, $tag->id);
                }

                $category->tags()->sync($tagIds);
            }
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'دسته ' . $category->name . ' با موفقیت ویرایش شد'
            ], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'خطایی غیرمنتظره در سرور رخ داده است. لطفا دوباره تلاش کنید'
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/admin/market/category/destroy/{category}",
     *     summary="Delete a ProductCategory",
     *     description="This endpoint allows the user to `delete an existing ProductCategory`.",
     *     operationId="deleteProductCategory",
     *     tags={"ProductCategory"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="category",
     *         in="path",
     *         description="The ID of the ProductCategory to be deleted",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="ProductCategory deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="دسته Example category با موفقیت حذف شد")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="عملیات با خطا مواجه شد. لطفا دوباره امتحان کنید")
     *         )
     *     )
     * )
     */
    public function destroy(Category $category)
    {
        try {
            $category->delete();
            return response()->json([
                'status' => true,
                'message' => 'دسته ' . $category->name . ' با موفقیت حذف شد'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'عملیات با خطا مواجه شد. لطفا دوباره امتحان کنید'
            ], 500);
        }
    }
}
