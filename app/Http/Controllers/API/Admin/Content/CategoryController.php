<?php

namespace App\Http\Controllers\API\Admin\Content;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Content\PostCategoryRequest;
use App\Http\Services\Image\ImageService;
use App\Models\Content\PostCategory;
use App\Models\Tag;
use Exception;
use GuzzleHttp\Psr7\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;


class CategoryController extends Controller
{
    function __construct()
    {
        // operators can only see edit page(method)
        // $this->middleware('role:admin')->only('index');
        // $this->middleware('role:operator')->only('edit');
        // $this->middleware('can:read-category')->only('index');
        // $this->authorizeResource(PostCategory::class, 'postCategory'); first model, second route model binding parameter

    }

    /**
     * @OA\Get(
     *     path="/api/admin/content/category",
     *     summary="Retrieve list of PostCategory",
     *     description="Retrieve list of all `PostCategory`",
     *     tags={"PostCategory"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *     @OA\Response(
     *         response=200,
     *         description="A list of PostCategory with their Tags",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="array",
     *                 @OA\Items(
     *                     ref="#/components/schemas/PostCategory"
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function index()
    {

        // $user = auth()->user();
        // if($request->user()->can('read-category'))
        // {
        $postCategories = PostCategory::with('tags:id,name')->orderBy('created_at', 'desc')->simplePaginate(15);
        $postCategories->getCollection()->each(function ($item) {
            $item->tags->makeHidden(['pivot']);
        });
        return response()->json([
            'data' => $postCategories
        ], 200);
        // }
        // else{
        //     abort(403,'you not allowed to access this page');
        // }

        // if($request->user()->cannot('read-category')){}


    }
    /**
     * @OA\Get(
     *     path="/api/admin/content/category/search",
     *     summary="Searchs among PostCategory by name",
     *     description="This endpoint allows users to search for `PostCategory` by name. The search is case-insensitive and returns results that contain the given keyword. The results are paginated for better performance",
     *     tags={"PostCategory"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *   @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="type name of PostCategory which you're searching for",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="A list of PostCategory with their Tags",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="array",
     *                 @OA\Items(
     *                     ref="#/components/schemas/PostCategory"
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function search(Request $request)
    {
        $postCategories = PostCategory::where('name', 'LIKE', "%" . $request->search . "%")->with('tags:id,name')->orderBy('name')->simplePaginate(15);
        $postCategories->getCollection()->each(function ($item) {
            $item->tags->makeHidden(['pivot']);
        });
        return response()->json([
            'data' => $postCategories
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/content/category/show/{category}",
     *     summary="Get details of a specific PostCategory",
     *     description="Returns the `PostCategory` details along with tags and provide details for edit method.",
     *     operationId="getPostCategoryDetails",
     *     tags={"PostCategory", "PostCategory/Form"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="category",
     *         in="path",
     *         description="ID of the PostCategory to fetch",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully fetched PostCategory details with tags for editing",
     *         @OA\JsonContent(ref="#/components/schemas/PostCategory"),
     *   )
     * )
     */
    public function show(PostCategory $postCategory)
    {
        $postCategory->load('tags:id,name');
        $postCategory->tags->makeHidden(['pivot']);
        return response()->json([
            'data' => $postCategory
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/admin/content/category/store",
     *     summary="create new PostCategory",
     *     description="this method creates a new `PostCategory` and stores its related tags.",
     *     tags={"PostCategory"},
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
     *                 property="tags[]",
     *                 type="array",
     *                 @OA\Items(type="string",pattern="^[a-zA-Z\u0600-\u06FF0-9\s\-\.\?]+$", example="لوازم ورزشی"),
     *              description="This field can only contain Persian and English letters, Persian and English numbers, hyphens (-),question marks (?), and periods (.). Any other characters will result in a validation error.",
     *             )
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
     *         description="successful PostCategory and tags creation",
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
     *       )
     *    )
     * )
     */
    public function store(PostCategoryRequest $request, ImageService $imageService)
    {
        try {
            DB::beginTransaction();
            // $this->authorize('create', PostCategory::class);
            $inputs = $request->all();
            if ($request->hasFile('image')) {
                $imageService->setExclusiveDirectory('images' . DIRECTORY_SEPARATOR . 'post-category');
                // $result = $imageService->save($request->file('image'));
                $result = $imageService->createIndexAndSave($request->file('image'));

            }
            if ($result === false) {
                return response()->json([
                    'status' => false,
                    'message' => 'بارگذاری عکس با خطا مواجه شد'
                ], 422);
                ;

            }
            $inputs['image'] = $result;

            $postCategory = PostCategory::create($inputs);
            if ($request->has('tags')) {
                foreach ($request->tags as $tagName) {
                    $tag = Tag::firstOrCreate(['name' => $tagName]);
                    $postCategory->tags()->attach($tag);
                }
            }
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'دسته ' . $postCategory->name . ' با موفقیت افزوده شد',
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
     *     path="/api/admin/content/category/status/{category}",
     *     summary="Change the status of a PostCategory",
     *     description="This endpoint `toggles the status of a PostCategory` (active/inactive)",
     *     operationId="updatePostCategoryStatus",
     *     security={{"bearerAuth": {}}},
     *     tags={"PostCategory"},
     *     @OA\Parameter(
     *         name="category",
     *         in="path",
     *         description="PostCategory id to change the status",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     security={ {"bearerAuth": {}} },
     *     @OA\Response(
     *         response=200,
     *         description="PostCategory status updated successfully",
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
    public function status(PostCategory $postCategory)
    {
        $postCategory->status = $postCategory->status == 1 ? 2 : 1;
        $result = $postCategory->save();
        if ($result) {
            if ($postCategory->status == 1) {
                return response()->json([
                    'status' => true,
                    'checked' => true,
                    'message' => 'وضعیت ' . $postCategory->name . ' با موفقیت فعال شد'
                ], 200);
            } else {
                return response()->json([
                    'status' => true,
                    'checked' => false,
                    'message' => 'وضعیت ' . $postCategory->name . ' با موفقیت غیرفعال شد'
                ], 200);
            }
        } else {
            return response()->json([
                'status' => false,
                'message' => 'عملیات با خطا مواجه شد. دوباره امتحان کنید'
            ], 500);
        }
    }

 /**
     * @OA\Post(
     *     path="/api/admin/content/category/update/{category}",
     *     summary="Update an existing PostCategory",
     *     description="this method update an existing `PostCategory` and stores its related tags.",
     *     tags={"PostCategory"},
     *     security={{"bearerAuth": {}}},
     *  @OA\Parameter(
     *         name="category",
     *         in="path",
     *         description="PostCategory id to change the status",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
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
     *                 property="tags[]",
     *                 type="array",
     *                 @OA\Items(type="string",pattern="^[a-zA-Z\u0600-\u06FF0-9\s\-\.\?]+$", example="لوازم ورزشی"),
     *              description="This field can only contain Persian and English letters, Persian and English numbers, hyphens (-),question marks (?), and periods (.). Any other characters will result in a validation error.",
     *             ),
     *             @OA\Property(property="_method", type="string", example="PUT"),
     *                       ),
     *              encoding={
     *                 "tags[]": {
     *                     "style": "form",
     *                     "explode": true
     *                 }
     *             }
     *             )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="successful PostCategory and tags Update",
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
     *       )
     *    )
     * )
     */
    public function update(PostCategoryRequest $request, PostCategory $postCategory, ImageService $imageService)
    {

        // $this->authorize('update', $category);
        // authorize return a http response + error code

        // if(!Gate::allows('update-postCategory', $postCategory)){
        // abort(403);
        // }
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            if ($request->hasFile('image')) {

                if (!empty($postCategory->image)) {
                    $imageService->deleteDirectoryAndFiles($postCategory->image['directory']);
                }

                $imageService->setExclusiveDirectory('images' . DIRECTORY_SEPARATOR . 'post-category');
                $result = $imageService->createIndexAndSave($request->file('image'));

                if ($result === false) {
                    return response()->json([
                        'status' => false,
                        'message' => 'بارگذاری عکس با خطا مواجه شد'
                    ], 422);

                }
                $inputs['image'] = $result;
            } else {
               $inputs['image'] = $postCategory->image;
            }

            $result = $postCategory->update($inputs);
            if ($request->has('tags')) {
                $tagIds = [];
                foreach ($request->tags as $tagName) {
                    $tag = Tag::firstOrCreate(['name' => $tagName]);
                     array_push($tagIds,$tag->id);
                }

                $postCategory->tags()->sync($tagIds);
                
            }
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'دسته ' . $postCategory->name . ' با موفقیت بروزرسانی شد'
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
     *     path="/api/admin/content/category/destroy/{category}",
     *     summary="Delete a PostCategory",
     *     description="This endpoint allows the user to `delete an existing PostCategory`.",
     *     operationId="deletePostCategory",
     *     tags={"PostCategory"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="category",
     *         in="path",
     *         description="The ID of the PostCategory to be deleted",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="PostCategory deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="دسته بندی با موفقیت حذف شد")
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

    public function destroy(PostCategory $postCategory, ImageService $imageService)
    {
        try{
        $result = $postCategory->delete();
            return response()->json([
                'status' => true,
                'messages' => 'دسته بندی با موفقیت حذف شد'
            ], 200);
        } catch(Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'عملیات با خطا مواجه شد. لطفا دوباره امتحان کنید'
            ], 500);
        }
    }
}
