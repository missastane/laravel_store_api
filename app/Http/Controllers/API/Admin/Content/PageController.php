<?php

namespace App\Http\Controllers\API\Admin\Content;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Content\PageRequest;
use App\Models\Content\Page;
use App\Models\Tag;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class PageController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/admin/content/page",
     *     summary="Retrieve list of Pages",
     *     description="Retrieve list of all `Pages`",
     *     tags={"Page"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *     @OA\Response(
     *         response=200,
     *         description="A list of Pages with their Tags",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="array",
     *                 @OA\Items(
     *                     ref="#/components/schemas/Page"
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function index()
    {
        $pages = Page::with('tags:name,id')->orderBy('created_at', 'desc')->simplePaginate(15);
        $pages->getCollection()->each(function ($item) {
            $item->tags->makeHidden(['pivot']);
        });
        return response()->json([
            'data' => $pages
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/content/page/search",
     *     summary="Searchs among Pages by title",
     *     description="This endpoint allows users to search for `Pages` by title. The search is case-insensitive and returns results that contain the given keyword. The results are paginated for better performance",
     *     tags={"Page"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *   @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="type title of Page which you're searching for",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="A list of Pages with their Tags",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="array",
     *                 @OA\Items(
     *                     ref="#/components/schemas/Page"
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function search(Request $request)
    {
        $pages = Page::where('title', 'LIKE', "%" . $request->search . "%")->with('tags:name,id')->orderBy('title')->simplePaginate(15);
        $pages->getCollection()->each(function ($item) {
            $item->tags->makeHidden(['pivot']);
        });
        return response()->json([
            'data' => $pages
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/content/page/show/{page}",
     *     summary="Get details of a specific Page",
     *     description="Returns the `Page` details along with tags and provide details for edit method.",
     *     operationId="getPageDetails",
     *     tags={"Page", "Page/Form"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="path",
     *         description="ID of the Page to fetch",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully fetched Page details with tags for editing",
     *         @OA\JsonContent(ref="#/components/schemas/Page"),
     *     )
     * )
     */
    public function show(Page $page)
    {
        $page->load('tags:id,name');
        $page->tags->makeHidden(['pivot']);
        return response()->json([
            'data' => $page
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/admin/content/page/store",
     *     summary="create new Page",
     *     description="this method creates a new `Page` and stores its related tags.",
     *     tags={"Page"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *             @OA\Property(property="title", type="string", pattern="^[a-zA-Z\u0600-\u06FF0-9\s\-\.\?\!\_\,\،؟]+$", description="This field can only contain Persian and English letters, Persian and English numbers, and symbols (-.,!?_!؟.،). Any other characters will result in a validation error.", example="درباره ما"),
     *             @OA\Property(property="body", type="string", example="بدنه درباره ما"),
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
     *                 @OA\Items(type="string",pattern="^[a-zA-Z\u0600-\u06FF0-9\s\-\.\?]+$", example="درباره ما"),
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
     *         description="successful Page and tags creation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="true"),
     *             @OA\Property(property="message", type="string", example="صفحه x با موفقیت افزوده شد")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="invalid request",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="عنوان الزامی است")
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
    public function store(PageRequest $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            $page = Page::create($inputs);
            if ($request->has('tags')) {
                foreach ($request->tags as $tagName) {
                    $tag = Tag::firstOrCreate(['name' => $tagName]);
                    $page->tags()->attach($tag);
                }
            }
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'صفحه ' . $page->title . ' با موفقیت افزوده شد'
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
     *     path="/api/admin/content/page/status/{page}",
     *     summary="Change the status of a Page",
     *     description="This endpoint `toggles the status of a Page` (active/inactive)",
     *     operationId="updatePageStatus",
     *     security={{"bearerAuth": {}}},
     *     tags={"Page"},
     *     @OA\Parameter(
     *         name="page",
     *         in="path",
     *         description="Page id to change the status",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     security={ {"bearerAuth": {}} },
     *     @OA\Response(
     *         response=200,
     *         description="Page status updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="checked", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="وضعیت با موفقیت فعال شد")
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
    public function status(Page $page)
    {
        $page->status = $page->status == 1 ? 2 : 1;
        $result = $page->save();
        if ($result) {
            if ($page->status == 1) {
                return response()->json([
                    'status' => true,
                    'checked' => true,
                    'message' => 'وضعیت با موفقیت فعال شد'
                ]);
            } else {
                return response()->json([
                    'status' => true,
                    'checked' => false,
                    'message' => 'وضعیت با موفقیت غیرفعال شد'
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
     *     path="/api/admin/content/page/update/{page}",
     *     summary="Updates an existing Page",
     *     description="this method updates an existin `Page` and stores its related tags.",
     *     tags={"Page"},
     *     security={{"bearerAuth": {}}},
     *   @OA\Parameter(
     *         name="page",
     *         in="path",
     *         description="Page id to fetch",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *             @OA\Property(property="title", type="string", pattern="^[a-zA-Z\u0600-\u06FF0-9\s\-\.\?\!\_\,\،؟]+$", description="This field can only contain Persian and English letters, Persian and English numbers, and symbols (-.,!?_!؟.،). Any other characters will result in a validation error.", example="درباره ما"),
     *             @OA\Property(property="body", type="string", example="بدنه درباره ما"),
     *             @OA\Property(
     *                 property="status",
     *                 oneOf={
     *                     @OA\Schema(type="integer", example=1, description="1 = active"),
     *                     @OA\Schema(type="integer", example=2, description="2 = inactive")
     *                 }
     *             ),
     *             @OA\Property(
     *                 property="tags",
     *                 type="array",
     *                 @OA\Items(type="string",pattern="^[a-zA-Z\u0600-\u06FF0-9\s\-\.\?]+$", example="درباره ما"),
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
     *         response=200,
     *         description="successful Page and tags update",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="true"),
     *             @OA\Property(property="message", type="string", example="صفحه x با موفقیت بروزرسانی شد")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="invalid request",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="عنوان الزامی است")
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
    public function update(PageRequest $request, Page $page)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            $page->update($inputs);
            if ($request->has('tags')) {
                $tagIds = [];
                foreach ($request->tags as $tagName) {
                    $tag = Tag::firstOrCreate(['name' => $tagName]);
                     array_push($tagIds,$tag->id);
                }
                $page->tags()->sync($tagIds);
            }
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'صفحه ' . $page->title . ' با موفقیت بروزرسانی شد'
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
     *     path="/api/admin/content/page/destroy/{page}",
     *     summary="Delete a Page",
     *     description="This endpoint allows the user to `delete an existing Page`.",
     *     operationId="deletePage",
     *     tags={"Page"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="path",
     *         description="The ID of the Page to be deleted",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Page deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="صفحه Example با موفقیت حذف شد")
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
    public function destroy(Page $page)
    {
        try{
        $page->delete();
            return response()->json([
                'status' => true,
                'message' => 'صفحه ' . $page->title . ' با موفقیت حذف شد'
            ], 200);
        } catch(Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'عملیات با خطا مواجه شد. لطفا دوباره امتحان کنید'
            ], 500);

        }
    }
}
