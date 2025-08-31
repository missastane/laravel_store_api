<?php

namespace App\Http\Controllers\API\Admin\Market;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Market\BrandRequest;
use App\Http\Services\Image\ImageService;
use App\Models\Market\Brand;
use App\Models\Tag;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Log;
class BrandController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/admin/market/brand",
     *     summary="Retrieve list of brands",
     *     description="Retrieve list of all `Brands`",
     *  tags={"Brand"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *     @OA\Response(
     *         response=200,
     *         description="A list of brands with Tags",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="array",
     *                 @OA\Items(
     *                     ref="#/components/schemas/Brand"
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function index()
    {
        $brands = Brand::with('tags:id,name')->orderBy('created_at', 'desc')->simplePaginate(15);
        $brands->getCollection()->each(function ($item) {
            $item->tags->makeHidden(['pivot']);
        });
        return response()->json([
            'data' => $brands
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/market/brand/search",
     *     summary="Searchs among brands by name",
     *     description="This endpoint allows users to search for `Brands` by name. The search is case-insensitive and returns results that contain the given keyword. The results are paginated for better performance",
     *    tags={"Brand"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *   @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="type name of brand which you're searching for",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="A list of Brands with Tags",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="array",
     *                 @OA\Items(
     *                     ref="#/components/schemas/Brand"
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function search(Request $request)
    {
        $brands = Brand::where('persian_name', 'LIKE', "%" . $request->search . "%")->orWhere('original_name', 'LIKE', "%" . $request->search . "%")->with('tags:id,name')->orderBy('persian_name')->simplePaginate(15);
        $brands->getCollection()->each(function ($item) {
            $item->tags->makeHidden(['pivot']);
        });
        return response()->json([
            'data' => $brands
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/market/brand/show/{brand}",
     *     summary="Get details of a specific Brand",
     *     description="Returns the `Brand` details along with tags and provide details for edit method",
     *     operationId="getBrandDetails",
     *     tags={"Brand", "Brand/Form"},
     *   security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="brand",
     *         in="path",
     *         description="ID of the Brand to fetch",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully fetched Brand details with tags for editing",
     *            @OA\JsonContent(ref="#/components/schemas/Brand"),
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
    public function show(Brand $brand)
    {
        $brand->load('tags:id,name');
        $brand->tags->makeHidden(['pivot']);
        return response()->json([
            'data' => $brand
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/admin/market/brand/store",
     *     summary="create new brand",
     *     description="this method creates a new `Brand` and stores its related tags.",
     *  tags={"Brand"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(
     *                     property="logo",
     *                     type="string",
     *                     format="binary"
     *         ),
     *             @OA\Property(property="persian_name", type="string", pattern="^[\u0600-\u06FF0-9 ,.]+$", description="This field can only contain Persian letters and numbers, and hyphens (،.) and space. Any other characters will result in a validation error.", example="پاکشوما"),
     *             @OA\Property(property="original_name", type="string", pattern="^[a-zA-Z0-9 ,]+$", description="This field can only contain English letters and numbers, and space and comma. Any other characters will result in a validation error.", example="PakShooma"),
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
     *                 @OA\Items(type="string",pattern="^[a-zA-Z\u0600-\u06FF0-9\s\-\.\?]+$", example="آیا api خوب است؟"),
     *              description="This field can only contain Persian and English letters, Persian and English numbers, hyphens (-),question marks (?), and periods (.). Any other characters will result in a validation error.",
     *             ),
     *                       ),
     *              encoding={
     *                 "tags[]": {
     *                     "style": "form",
     *                     "explode": true
     *                 }
     *             }
     *             
     *             )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="successful brand and tags creation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="true"),
     *             @OA\Property(property="message", type="string", example="برند x با موفقیت افزوده شد")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="invalid request",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="نام برند الزامی است")
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
    public function store(BrandRequest $request, ImageService $imageService)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            Log::info($inputs);
            if ($request->hasFile('logo')) {
                $imageService->setExclusiveDirectory('images' . DIRECTORY_SEPARATOR . 'market' . DIRECTORY_SEPARATOR . 'brand');
                $result = $imageService->createIndexAndSave($request->file('logo'));
                if ($result === false) {
                    return response()->json([
                        'status' => false,
                        'message' => 'بارگذاری عکس با خطا مواجه شد'
                    ], 422);

                }
                $inputs['logo'] = $result;
            }
            $brand = Brand::create($inputs);

            if ($request->has('tags')) {
                foreach ($request->tags as $tagName) {
                    $tag = Tag::firstOrCreate(['name' => $tagName]);
                    $brand->tags()->attach($tag);
                }
            }
            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'برند ' . $brand->persian_name . ' با موفقیت افزوده شد'
            ], 201);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'خطایی غیرمنتظره در سرور رخ داد. لطفاً مجدداً تلاش کنید.',
                'error' => $e->getMessage()
            ], 500);
        }

    }

    /**
     * @OA\Get(
     *     path="/api/admin/market/brand/status/{brand}",
     *     summary="Change the status of a brand",
     *     description="This endpoint `toggles the status of a brand` (active/inactive)",
     *     operationId="updateBrandStatus",
     *     security={{"bearerAuth": {}}},
     *     tags={"Brand"},
     *     @OA\Parameter(
     *         name="brand",
     *         in="path",
     *         description="Brand id to change the status",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     security={ {"bearerAuth": {}} },
     *     @OA\Response(
     *         response=200,
     *         description="Brand status updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="checked", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="وضعیت برند x با موفقیت فعال شد")
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
    public function status(Brand $brand)
    {
        $brand->status = $brand->status == 1 ? 2 : 1;
        $result = $brand->save();
        if ($result) {
            if ($brand->status == 1) {
                return response()->json([
                    'status' => true,
                    'checked' => true,
                    'message' => 'وضعیت ' . $brand->persian_name . ' با موفقیت فعال شد'
                ]);
            } else {
                return response()->json([
                    'status' => true,
                    'checked' => false,
                    'message' => 'وضعیت ' . $brand->persian_name . ' با موفقیت غیرفعال شد'
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
     *     path="/api/admin/market/brand/update/{brand}",
     *     summary="create new brand",
     *     description="this method updates an existing `Brand` and its related tags.",
     *     tags={"Brand"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="brand",
     *         in="path",
     *         description="Brand id to change the status",
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
     *                     property="logo",
     *                     type="string",
     *                     format="binary"
     *         ),
     *             @OA\Property(property="persian_name", type="string", pattern="^[\u0600-\u06FF0-9 ,.]+$", description="This field can only contain Persian letters and numbers, and hyphens (،.) and space. Any other characters will result in a validation error.", example="پاکشوما"),
     *             @OA\Property(property="original_name", type="string", pattern="^[a-zA-Z0-9 ,]+$", description="This field can only contain English letters and numbers, and space and comma. Any other characters will result in a validation error.", example="PakShooma"),
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
     *                 @OA\Items(type="string",pattern="^[a-zA-Z\u0600-\u06FF0-9\s\-\.\?]+$", example="آیا api خوب است؟"),
     *              description="This field can only contain Persian and English letters, Persian and English numbers, hyphens (-),question marks (?), and periods (.). Any other characters will result in a validation error.",
     *             ),
     *            @OA\Property(property="_method", type="string", example="PUT"),
     * 
     *           ),
     *             encoding={
     *                 "tags[]": {
     *                     "style": "form",
     *                     "explode": true
     *                 }
     *             }
     *        )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="successful brand and tags update",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="true"),
     *             @OA\Property(property="message", type="string", example="برند x با موفقیت بروزرسانی شد")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="invalid request",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="نام برند الزامی است")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="خطای غیرمنتظره در سرور رخ داده است. لطفاً دوباره تلاش کنید.")
     *     )
     *  )
     * )
     */
    public function update(BrandRequest $request, Brand $brand, ImageService $imageService)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            if ($request->hasFile('logo')) {
                if (!empty($brand->logo)) {
                    $imageService->deleteDirectoryAndFiles($brand->logo['directory']);
                }
                $imageService->setExclusiveDirectory('images' . DIRECTORY_SEPARATOR . 'market' . DIRECTORY_SEPARATOR . 'brand');
                $result = $imageService->createIndexAndSave($request->file('logo'));
                if ($result === false) {
                    return response()->json([
                        'status' => false,
                        'message' => 'بارگذاری عکس با خطا مواجه شد'
                    ], 422);

                }
                $inputs['logo'] = $result;
            } else {
                $inputs['logo'] = $brand->logo;
            }
            $update = $brand->update($inputs);
            if ($request->has('tags')) {
                $tagIds = [];
                foreach ($request->tags as $tagName) {
                    $tag = Tag::firstOrCreate(['name' => $tagName]);
                    array_push($tagIds, $tag->id);
                }

                $brand->tags()->sync($tagIds);
            }
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'برند ' . $brand->persian_name . '  با موفقیت بروزرسانی شد'
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
     *     path="/api/admin/market/brand/destroy/{brand}",
     *     summary="Delete a Brand",
     *     description="This endpoint allows the user to `delete an existing Brand`.",
     *     operationId="deleteBrand",
     *     tags={"Brand"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="brand",
     *         in="path",
     *         description="The ID of the Brand to be deleted",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Brand deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="برند Example Brand با موفقیت حذف شد")
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
    public function destroy(Brand $brand)
    {
        try {
            $result = $brand->delete();

            return response()->json([
                'status' => true,
                'message' => 'برند ' . $brand->persian_name . ' با موفقیت حذف شد'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'مشکلی پیش آمده است. لطفا دوباره امتحان کنید'
            ], 500);
        }
    }
}
