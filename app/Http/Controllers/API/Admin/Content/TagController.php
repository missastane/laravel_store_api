<?php

namespace App\Http\Controllers\API\Admin\Content;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TagController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/admin/content/tag",
     *     summary="Retrieve list of Tags",
     *     description="Retrieve list of all `Tags`",
     *     tags={"Tag"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *     @OA\Response(
     *         response=200,
     *         description="A list of Tags",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="array",
     *                 @OA\Items(
     *                     ref="#/components/schemas/Tag"
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function index()
    {
        $tags = Tag::with('taggables')->orderBy('id', 'desc')->simplePaginate(15);
        return response()->json([
            'data' => $tags
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/content/tag/search",
     *     summary="Searchs among Tags by name",
     *     description="This endpoint allows users to search for `Tags` by name. The search is case-insensitive and returns results that contain the given keyword. The results are paginated for better performance",
     *     tags={"Tag"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *   @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Type name of Tag which you're searching for",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="A list of Tags with their Taggable",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="array",
     *                 @OA\Items(
     *                     ref="#/components/schemas/Tag"
     *                 )
     *             )
     *         )
     *     )
     * )
     */


    public function search(Request $request)
    {
        $tags = Tag::where('name', 'LIKE', "%" . $request->search . "%")->with('taggables')->orderBy('name')->get();
        return response()->json([
            'data' => $tags
        ], 200);
    }

     /**
     * @OA\Get(
     *     path="/api/admin/content/tag/show/{tag}",
     *     summary="Get details of a specific Tag",
     *     description="Returns the `Tag` details along with taggable and provide details for edit method.",
     *     operationId="getTagDetails",
     *     tags={"Tag", "Tag/Form"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="tag",
     *         in="path",
     *         description="ID of the Tag to fetch",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully fetched Tag details with taggable for editing",
     *         @OA\JsonContent(ref="#/components/schemas/Tag"),
     *     )
     * )
     */
    public function show(Tag $tag)
    {
        return response()->json([
            'data' => $tag->load(['taggables']),

        ], 200);
    }

     /**
     * @OA\Post(
     *     path="/api/admin/content/tag/store",
     *     summary="create new Tag",
     *     description="this method creates a new `Tag` and stores its related taggable.",
     *     tags={"Tag"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                
     *             @OA\Property(property="name", type="string", pattern="^[a-zA-Z\u0600-\u06FF0-9\s\-\،,]+$", description="This field can only contain Persian and English letters, Persian and English numbers, and symbols (,،). Any other characters will result in a validation error.", example="لوازم ورزشی"),
     *                       )
     *             )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="successful Tag creation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="true"),
     *             @OA\Property(property="message", type="string", example="تگ x با موفقیت افزوده شد")
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
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|max:120|min:2|unique:tags,name|regex:/^[ا-یa-zA-Z0-9\-۰-۹ء-ي.,، ]+$/u',
        ]);
        try {
            $inputs = $request->all();
            $tag = Tag::create($inputs);
            return response()->json([
                'status' => true,
                'message' => 'تگ ' . $tag->name . ' با موفقیت افزوده شد'
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'خطایی غیرمنتظره در سرور رخ داده است. لطفا دوباره تلاش کنید',
            ], 500);
        }
    }

 /**
     * @OA\Put(
     *     path="/api/admin/content/tag/update/{tag}",
     *     summary="Update an exisiting Tag",
     *     description="this method Update an exisiting`Tag` and stores it.",
     *     tags={"Tag"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="tag",
     *         in="path",
     *         description="Tag id to fetch",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                
     *             @OA\Property(property="name", type="string", pattern="^[a-zA-Z\u0600-\u06FF0-9\s\-\،,]+$", description="This field can only contain Persian and English letters, Persian and English numbers, and symbols (,،). Any other characters will result in a validation error.", example="لوازم ورزشی"),
     *                       )
     *             )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="successful Tag creation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="true"),
     *             @OA\Property(property="message", type="string", example="تگ x با موفقیت بروزرسانی شد")
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
    public function update(Tag $tag, Request $request)
    {
        $request->validate([
            'name' => ['required','max:120','min:2','regex:/^[ا-یa-zA-Z0-9\-۰-۹ء-ي.,، ]+$/u',Rule::unique('tags','name')->ignore($request->route('tag'))],
        ]);
        try {
            $inputs = $request->all();
            $tag->update($inputs);
            return response()->json([
                'status' => true,
                'message' => 'تگ ' . $tag->name . ' با موفقیت بروزرسانی شد'
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
     *     path="/api/admin/content/tag/destroy/{tag}",
     *     summary="Delete a Tag",
     *     description="This endpoint allows the user to `delete an existing Tag`.",
     *     operationId="deleteTag",
     *     tags={"Tag"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="tag",
     *         in="path",
     *         description="The ID of the Tag to be deleted",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tag deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="تگ Example با موفقیت حذف شد")
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
    public function destroy(Tag $tag)
    {
        try {
            $tag->delete();
            return response()->json([
                'status' => true,
                'message' => 'تگ ' . $tag->name . ' با موفقیت حذف شد'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'عملیات با خطا مواجه شد. لطفا دوباره امتحان کنید'
            ], 500);

        }
    }
}
