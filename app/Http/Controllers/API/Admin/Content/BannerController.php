<?php

namespace App\Http\Controllers\API\Admin\Content;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Content\BannerRequest;
use App\Http\Services\Image\ImageService;
use App\Models\Content\Banner;
use Exception;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/admin/content/banner",
     *     summary="Retrieve list of Banners",
     *     description="Retrieve list of all `Banners`",
     *     tags={"Banner"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *     @OA\Response(
     *         response=200,
     *         description="A list of Banners",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="array",
     *                 @OA\Items(
     *                     ref="#/components/schemas/Banner"
     *                 )
     *             )
     *         )
     *     )
     * )
     */

    public function index()
    {
        $banners = Banner::orderBy('created_at', 'desc')->simplePaginate(15);
        return response()->json([
            'data' => $banners
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/content/banner/search",
     *     summary="Searchs among Banners by title",
     *     description="This endpoint allows users to search for `Banners` by title. The search is case-insensitive and returns results that contain the given keyword. The results are paginated for better performance",
     *    tags={"Banner"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *   @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="type title of Banner which you're searching for",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="A list of Banners",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="array",
     *                 @OA\Items(
     *                     ref="#/components/schemas/Banner"
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function search(Request $request)
    {
        $banners = Banner::where('title', 'LIKE', "%" . $request->search . "%")->orderBy('title')->get();
        return response()->json([
            'data' => $banners
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/content/banner/show/{banner}",
     *     summary="Get details of a specific Banner",
     *     description="Returns the `Banner` details and provide details for edit method.",
     *     operationId="getBannerDetails",
     *     tags={"Banner", "Banner/Form"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="banner",
     *         in="path",
     *         description="ID of the Banner to fetch",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully fetched Banner details for editing",
     *         @OA\JsonContent(ref="#/components/schemas/Banner"),
     *    )
     * )
     */
    public function show(Banner $banner)
    {
        return response()->json([
            'data' => $banner
        ], 200);
    }

    // public function options()
    // {
    //     $positions = Banner::$positions;
    //     return response()->json([
    //         'data' => $positions
    //         ], 200);
    // }

    /**
     * @OA\Post(
     *     path="/api/admin/content/banner/store",
     *     summary="create new Banner",
     *     description="this method creates a new `Banner` and stores it.",
     *     tags={"Banner"},
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
     *             @OA\Property(property="title", type="string", pattern="^[a-zA-Z\u0600-\u06FF0-9\s\-\.\?]+$", description="This field can only contain Persian and English letters, Persian and English numbers, and hyphens (-). Any other characters will result in a validation error.", example="لوازم ورزشی"),
     *             @OA\Property(property="url", type="string",format="url", example="https://example.com"),
     *             @OA\Property(property="position", type="integer", description="Each number in the `position` field corresponds to a specific position on the page, determined by designer.For example 0 means above of the main page big slideshow", example=0),
     *             @OA\Property(
     *                 property="status",
     *                 oneOf={
     *                     @OA\Schema(type="integer", example=1, description="1 = active"),
     *                     @OA\Schema(type="integer", example=2, description="2 = inactive")
     *                 }
     *             ),
     *            
     *            
     *                       )
     *             )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="successful Banner creation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="true"),
     *             @OA\Property(property="message", type="string", example="بنر x با موفقیت افزوده شد")
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
    public function store(BannerRequest $request, ImageService $imageService)
    {
        try {
            $inputs = $request->all();
            if ($request->hasFile('image')) {
                $imageService->setExclusiveDirectory('images' . DIRECTORY_SEPARATOR . 'banner');
                $result = $imageService->save($request->file('image'));

            }
            if ($result === false) {
                return response()->json([
                    'status' => false,
                    'message' => 'بارگذاری عکس با خطا مواجه شد'
                ], 422);

            }
            $inputs['image'] = $result;

            $banner = Banner::create($inputs);
            return response()->json([
                'status' => true,
                'message' => 'بنر ' . $banner->title . ' با موفقیت افزوده شد'
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
     *     path="/api/admin/content/banner/status/{banner}",
     *     summary="Change the status of a Banner",
     *     description="This endpoint `toggles the status of a Banner` (active/inactive)",
     *     operationId="updateBannerStatus",
     *     security={{"bearerAuth": {}}},
     *     tags={"Banner"},
     *     @OA\Parameter(
     *         name="banner",
     *         in="path",
     *         description="Banner id to change the status",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     security={ {"bearerAuth": {}} },
     *     @OA\Response(
     *         response=200,
     *         description="Banner status updated successfully",
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
    public function status(Banner $banner)
    {
        $banner->status = $banner->status == 1 ? 2 : 1;
        $result = $banner->save();
        if ($result) {
            if ($banner->status == 1) {
                return response()->json([
                    'status' => true,
                    'checked' => true,
                    'message' => 'وضعیت ' . $banner->title . ' با موفقیت فعال شد'
                ]);
            } else {
                return response()->json([
                    'status' => true,
                    'checked' => false,
                    'message' => 'وضعیت ' . $banner->title . ' با موفقیت غیرفعال شد'
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
     *     path="/api/admin/content/banner/update/{banner}",
     *     summary="Update an existing Banner",
     *     description="this method Update an existing `Banner` and stores it.",
     *     tags={"Banner"},
     *     security={{"bearerAuth": {}}},
     *  @OA\Parameter(
     *         name="banner",
     *         in="path",
     *         description="Banner id to fetch",
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
     *             @OA\Property(property="title", type="string", pattern="^[a-zA-Z\u0600-\u06FF0-9\s\-\.\?]+$", description="This field can only contain Persian and English letters, Persian and English numbers, and hyphens (-). Any other characters will result in a validation error.", example="لوازم ورزشی"),
     *             @OA\Property(property="url", type="string",format="url", example="https://example.com"),
     *             @OA\Property(property="position", type="integer", description="Each number in the `position` field corresponds to a specific position on the page, determined by designer.For example 0 means above of the main page big slideshow", example=0),
     *             @OA\Property(
     *                 property="status",
     *                 oneOf={
     *                     @OA\Schema(type="integer", example=1, description="1 = active"),
     *                     @OA\Schema(type="integer", example=2, description="2 = inactive")
     *                 }
     *             ),
     *             @OA\Property(property="_method", type="string", example="PUT"),
     *            
     *                       )
     *             )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="successful Banner update",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="true"),
     *             @OA\Property(property="message", type="string", example="بنر x با موفقیت بروزرسانی شد")
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
    public function update(BannerRequest $request, Banner $banner, ImageService $imageService)
    {
        try{
        $inputs = $request->all();
        if ($request->hasFile('image')) {

            if (!empty($postCategory->image)) {
                $imageService->deleteImage($banner->image['directory']);
            }

            $imageService->setExclusiveDirectory('images' . DIRECTORY_SEPARATOR . 'banner');
            $result = $imageService->save($request->file('image'));

            if ($result === false) {
                return response()->json([
                    'status' => false,
                    'message' => 'بارگذاری عکس با خطا مواجه شد'
                ], 422);

            }
            $inputs['image'] = $result;
        } else {
            if (isset($inputs['currentImage']) && !empty($banner->image)) {
                $image = $banner->image;
                $image['currentImage'] = $inputs['currentImage'];
                $inputs['image'] = $image;
            }
        }

        $result = $banner->update($inputs);
            return response()->json([
                'status' => true,
                'message' => 'بنر ' . $banner->title . ' با موفقیت بروزرسانی شد'
            ], 200);
        } catch(Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'خطایی غیرمنتظره در سرور رخ داده است. لطفا دوباره تلاش کنید'
            ], 500);
        }

    }

     /**
     * @OA\Delete(
     *     path="/api/admin/content/banner/destroy/{banner}",
     *     summary="Delete a Banner",
     *     description="This endpoint allows the user to `delete an existing Banner`.",
     *     operationId="deleteBanner",
     *     tags={"Banner"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="banner",
     *         in="path",
     *         description="The ID of the Banner to be deleted",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Banner deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="بنر با موفقیت حذف شد")
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
    public function destroy(Banner $banner, ImageService $imageService)
    {
        $result = $banner->delete();
        if ($result) {
            return response()->json([
                'status' => true,
                'message' => 'بنر با موفقیت حذف شد'
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'عملیات با خطا مواجه شد. لطفا دوباره امتحان کنید'
            ], 500);
        }
    }
}
