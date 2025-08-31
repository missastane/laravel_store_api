<?php

namespace App\Http\Controllers\API\Admin\Content;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Content\PostRequest;
use App\Http\Services\Image\ImageService;
use App\Models\Content\Post;
use App\Models\Content\PostCategory;
use App\Models\Tag;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class PostController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/admin/content/post",
     *     summary="Retrieve list of Posts",
     *     description="Retrieve list of all `Posts`",
     *     tags={"Post"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *     @OA\Response(
     *         response=200,
     *         description="A list of Posts with their Tags",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="array",
     *                 @OA\Items(
     *                     ref="#/components/schemas/Post"
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function index()
    {
        $posts = Post::with('postCategory:id,name', 'user:id,first_name,last_name', 'tags:id,name')->orderBy('id', 'desc')->simplePaginate(15);
        $posts->getCollection()->each(function ($item) {
            $item->postCategory->makeHidden(['status_value']);
            $item->user->makeHidden(['status_value', 'activation_value', 'user_type_value']);
            $item->tags->makeHidden(['pivot']);
        });
        return response()->json([
            'data' => $posts
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/content/post/search",
     *     summary="Searchs among Posts by title",
     *     description="This endpoint allows users to search for `Posts` by title. The search is case-insensitive and returns results that contain the given keyword. The results are paginated for better performance",
     *     tags={"Post"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *   @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="type title of Post which you're searching for",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="A list of Posts with their Tags",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="array",
     *                 @OA\Items(
     *                     ref="#/components/schemas/Post"
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function search(Request $request)
    {
        $posts = Post::where('title', 'LIKE', "%" . $request->search . "%")->with('postCategory:id,name', 'user:id,first_name,last_name', 'tags:id,name')->orderBy('title')->simplePaginate(15);
        $posts->getCollection()->each(function ($item) {
            $item->postCategory->makeHidden(['status_value']);
            $item->user->makeHidden(['status_value', 'activation_value', 'user_type_value']);
            $item->tags->makeHidden(['pivot']);
        });
        return response()->json([
            'data' => $posts
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/content/post/show/{post}",
     *     summary="Get details of a specific Post",
     *     description="Returns the `Post` details along with tags and provide details for edit method.",
     *     operationId="getPostDetails",
     *     tags={"Post", "Post/Form"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="post",
     *         in="path",
     *         description="ID of the Post to fetch",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully fetched Post details with tags for editing",
     *         @OA\JsonContent(ref="#/components/schemas/Post"),
     *     )
     * )
     */
    public function show(Post $post)
    {
        $post->load(['postCategory:id,name', 'user:id,first_name,last_name', 'tags:id,name']);
        $post->postCategory->makeHidden(['status_value']);
        $post->user->makeHidden(['status_value', 'activation_value', 'user_type_value']);
        $post->tags->makeHidden(['pivot']);
        return response()->json([
            'data' => $post
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/content/post/options",
     *     summary="Get necessary options for Post forms",
     *     description="This endpoint returns all `PostCategories` which can be used to create a new post",
     *     tags={"Post", "Post/Form"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successfully fetched post categories that you may need to make a post create form",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="postCategories",
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
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="An error occurred. Please try again.")
     *         )
     *     )
     * )
     */
    public function options()
    {
        $postCategories = PostCategory::select(['id','name'])->get();
        $postCategories->makeHidden('status_value');
        return response()->json([
            'data' => $postCategories
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/admin/content/post/store",
     *     summary="create new category",
     *     description="this method creates a new `Post` and stores its related tags.",
     *     tags={"Post"},
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
     *             @OA\Property(property="title", type="string", pattern="^[a-zA-Z\u0600-\u06FF0-9\s\-\.\?]+$", description="This field can only contain Persian and English letters, Persian and English numbers, and hyphens (-). Any other characters will result in a validation error.", example="تأثیر هوش مصنوعی بر دنیای دیجیتال"),
     *             @OA\Property(property="summary", type="string", example="خلاصه تأثیر هوش مصنوعی بر دنیای دیجیتال"),
     *             @OA\Property(property="body", type="string", example="توضیح تأثیر هوش مصنوعی بر دنیای دیجیتال"),
     *              @OA\Property(
     *                 property="commentable",
     *                 oneOf={
     *                     @OA\Schema(type="integer", example=1, description="1 = yes"),
     *                     @OA\Schema(type="integer", example=2, description="2 = no")
     *                 }
     *             ),
     *             @OA\Property(
     *                 property="status",
     *                 oneOf={
     *                     @OA\Schema(type="integer", example=1, description="1 = active"),
     *                     @OA\Schema(type="integer", example=2, description="2 = inactive")
     *                 }
     *             ),
     *            
     *             @OA\Property(property="post_category_id",description="ParentID.This field is optional when creating or updating the category.", type="integer", nullable="true", example=5),
     *                 @OA\Property(property="published_at", type="integer", example=1677030400),
     *             @OA\Property(
     *                 property="tags[]",
     *                 type="array",
     *                 @OA\Items(type="string",pattern="^[a-zA-Z\u0600-\u06FF0-9\s\-\.\?]+$", example="تازه های دیجیتال"),
     *              description="This field can only contain Persian and English letters, Persian and English numbers, hyphens (-),question marks (?), and periods (.). Any other characters will result in a validation error.",
     *             ),
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
     *         response=201,
     *         description="successful Post and tags creation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="true"),
     *             @OA\Property(property="message", type="string", example="پست x با موفقیت افزوده شد")
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
    public function store(PostRequest $request, ImageService $imageService)
    {
        try {
            DB::beginTransaction();
            date_default_timezone_set('Iran');
            $realTimestamp = substr($request['published_at'], 0, 10);
            $request['published_at'] = date("Y-m-d H:i:s", (int) $realTimestamp);
            $inputs = $request->all();
            if ($request->hasFile('image')) {
                $imageService->setExclusiveDirectory('images' . DIRECTORY_SEPARATOR . 'post');
                $result = $imageService->createIndexAndSave($request->file('image'));
                if ($result === false) {
                    return response()->json([
                        'status' => false,
                        'message' => 'بارگذاری عکس با خطا مواجه شد'
                    ], 422);

                }
                $inputs['image'] = $result;
            }
            $inputs['author_id'] = auth()->user()->id;

            $post = Post::create($inputs);
            if ($request->has('tags')) {
                foreach ($request->tags as $tagName) {
                    $tag = Tag::firstOrCreate(['name' => $tagName]);
                    $post->tags()->attach($tag);
                }
            }
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'پست ' . $post->title . ' با موفقیت افزوده شد'
            ], 201);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'خطایی غیرمنتظره در سرور رخ داده است. لطفا دوباره تلاش کنید',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/admin/content/post/status/{post}",
     *     summary="Change the status of a Post",
     *     description="This endpoint `toggles the status of a Post` (active/inactive)",
     *     operationId="updatePostStatus",
     *     security={{"bearerAuth": {}}},
     *     tags={"Post"},
     *     @OA\Parameter(
     *         name="post",
     *         in="path",
     *         description="Post id to change the status",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     security={ {"bearerAuth": {}} },
     *     @OA\Response(
     *         response=200,
     *         description="Post status updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="checked", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="وضعیت x با موفقیت فعال شد")
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
    public function status(Post $post)
    {
        $post->status = $post->status == 1 ? 2 : 1;
        $result = $post->save();
        if ($result) {
            if ($post->status == 1) {
                return response()->json([
                    'status' => true,
                    'checked' => true,
                    'message' => 'وضعیت ' . $post->title . ' با موفقیت فعال شد'
                ], 200);
            } else {
                return response()->json([
                    'status' => true,
                    'checked' => false,
                    'message' => 'وضعیت ' . $post->title . ' با موفقیت غیرفعال شد'
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
     * @OA\Get(
     *     path="/api/admin/content/post/commentable/{post}",
     *     summary="Change the status of a post commentable",
     *     description="This endpoint `toggles the commentable state of a Post` (active/inactive)",
     *     operationId="updatePostCommentableStatus",
     *     security={{"bearerAuth": {}}},
     *     tags={"Post"},
     *     @OA\Parameter(
     *         name="post",
     *         in="path",
     *         description="Post id to change the commentable status",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     security={ {"bearerAuth": {}} },
     *     @OA\Response(
     *         response=200,
     *         description="Post Commentable status updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="checked", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="امکان درج نظر x با موفقیت فعال شد")
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
    public function commentable(Post $post)
    {
        $post->commentable = $post->commentable == 1 ? 2 : 1;
        $result = $post->save();
        if ($result) {
            if ($post->commentable == 1) {
                return response()->json([
                    'status' => true,
                    'checked' => true,
                    'message' => 'امکان درج نظر  ' . $post->title . ' با موفقیت فعال شد'
                ], 200);
            } else {
                return response()->json([
                    'status' => true,
                    'checked' => false,
                    'message' => 'امکان درج نظر ' . $post->title . ' با موفقیت غیرفعال شد'
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
     *     path="/api/admin/content/post/update/{post}",
     *     summary="Update an existing post",
     *     description="this method updates an existing `Post` and stores its related tags.",
     *     tags={"Post"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="post",
     *         in="path",
     *         description="Post id to fetch",
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
     *             @OA\Property(property="title", type="string", pattern="^[a-zA-Z\u0600-\u06FF0-9\s\-\.\?]+$", description="This field can only contain Persian and English letters, Persian and English numbers, and hyphens (-). Any other characters will result in a validation error.", example="تأثیر هوش مصنوعی بر دنیای دیجیتال"),
     *             @OA\Property(property="summary", type="string", example="خلاصه تأثیر هوش مصنوعی بر دنیای دیجیتال"),
     *             @OA\Property(property="body", type="string", example="توضیح تأثیر هوش مصنوعی بر دنیای دیجیتال"),
     *              @OA\Property(
     *                 property="commentable",
     *                 oneOf={
     *                     @OA\Schema(type="integer", example=1, description="1 = yes"),
     *                     @OA\Schema(type="integer", example=2, description="2 = no")
     *                 }
     *             ),
     *             @OA\Property(
     *                 property="status",
     *                 oneOf={
     *                     @OA\Schema(type="integer", example=1, description="1 = active"),
     *                     @OA\Schema(type="integer", example=2, description="2 = inactive")
     *                 }
     *             ),
     *            
     *             @OA\Property(property="post_category_id",description="ParentID.This field is optional when creating or updating the category.", type="integer", nullable="true", example=5),
     *                 @OA\Property(property="published_at", type="integer", example=1677030400),
     *             @OA\Property(
     *                 property="tags[]",
     *                 type="array",
     *                 @OA\Items(type="string",pattern="^[a-zA-Z\u0600-\u06FF0-9\s\-\.\?]+$", example="تازه های دیجیتال"),
     *              description="This field can only contain Persian and English letters, Persian and English numbers, hyphens (-),question marks (?), and periods (.). Any other characters will result in a validation error.",
     *             ),
     *                 @OA\Property(property="_method", type="string", example="PUT"),
     *                       ),
     *            encoding={
     *                 "tags[]": {
     *                     "style": "form",
     *                     "explode": true
     *                 }
     *             }
     *             )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="successful Post and tags update",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="true"),
     *             @OA\Property(property="message", type="string", example="پست x با موفقیت بروزرسانی شد")
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
    public function update(Post $post, ImageService $imageService, PostRequest $request)
    {
        try {
            DB::beginTransaction();
            date_default_timezone_set('Iran');
            $realTimestamp = substr($request['published_at'], 0, 10);
            $request['published_at'] = date("Y-m-d H:i:s", (int) $realTimestamp);
            $inputs = $request->all();
            if ($request->hasFile('image')) {

                if (!empty($post->image)) {
                    $imageService->deleteDirectoryAndFiles($post->image['directory']);
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
               $inputs['image'] = $post->image;
            }

            $inputs['author_id'] = auth()->user()->id;

            $result = $post->update($inputs);
            if ($request->has('tags')) {
                $tagIds = [];
                foreach ($request->tags as $tagName) {
                    $tag = Tag::firstOrCreate(['name' => $tagName]);
                     array_push($tagIds,$tag->id);
                }

                $post->tags()->sync($tagIds);
            }
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'پست ' . $post->title . ' با موفقیت بروزرسانی شد'
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
     *     path="/api/admin/content/post/destroy/{post}",
     *     summary="Delete a Post",
     *     description="This endpoint allows the user to `delete an existing Post`.",
     *     operationId="deletePost",
     *     tags={"Post"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="post",
     *         in="path",
     *         description="The ID of the Post to be deleted",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Post deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="پست Example با موفقیت حذف شد")
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
    public function destroy(Post $post)
    {
        try{
        $post->delete();
            return response()->json([
                'status' => true,
                'message' => 'پست ' . $post->title . ' با موفقیت حذف شد'
            ], 200);
        } catch(Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'عملیات با خطا مواجه شد. لطفا دوباره امتحان کنید'
            ], 500);

        }
    }
}
