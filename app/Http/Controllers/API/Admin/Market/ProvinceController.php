<?php

namespace App\Http\Controllers\API\Admin\Market;

use App\Http\Controllers\Controller;
use App\Models\User\Province;
use Exception;
use Illuminate\Foundation\Http\Middleware\Concerns\ExcludesPaths;
use Illuminate\Http\Request;

class ProvinceController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/admin/market/delivery/province",
     *     summary="Retrieve list of Provinces",
     *     description="Retrieve list of all `Provinces`",
     *     tags={"Province"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *     @OA\Response(
     *         response=200,
     *         description="A list of Provinces",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="array",
     *                 @OA\Items(
     *                     ref="#/components/schemas/Province"
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function index()
    {
        $provinces = Province::orderBy('created_at', 'desc')->simplePaginate(15);
        return response()->json([
            'data' => $provinces
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/market/delivery/province/search",
     *     summary="Searchs among Provinces by name",
     *     description="This endpoint allows users to search for `Province` by name. The search is case-insensitive and returns results that contain the given keyword. The results are paginated for better performance",
     *    tags={"Province"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *   @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="type name of Province which you're searching for",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="A list of Provinces",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="array",
     *                 @OA\Items(
     *                     ref="#/components/schemas/Province"
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function search(Request $request)
    {
        $provinces = Province::where('name', 'LIKE', "%" . $request->search . "%")->orderBy('name')->simplePaginate(15);
        return response()->json([
            'data' => $provinces
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/admin/market/delivery/province/store",
     *     summary="create new Province",
     *     description="this method creates a new `Province`.",
     *     tags={"Province"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *             @OA\Property(property="name", type="string", pattern="^[\u0600-\u06FF\s]+$", description="This field can only contain Persianh letters. Any other characters will result in a validation error.", example="بوشهر"),
     *            
     *                       )
     *             )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="successful Province creation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="true"),
     *             @OA\Property(property="message", type="string", example="استان x با موفقیت افزوده شد")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="invalid request",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="نام استان الزامی است")
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
            'name' => 'required|max:120|min:2|regex:/^[ا-یء-ي ]+$/u',
            // 'g-recaptcha-response' => 'recaptcha',
        ]);
        try {
            $inputs = $request->all();
            $province = Province::create($inputs);
            return response()->json([
                'status' => true,
                'message' => 'استان ' . $province->name . ' با موفقیت افزوده شد'
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'خطایی غیرمنتظره رخ داده است. لطفا دوباره تلاش کنید'
            ], 500);

        }
    }

    /**
     * @OA\Get(
     *     path="/api/admin/market/delivery/province/show/{province}",
     *     summary="Get details of a specific Province",
     *     description="Returns the `Province` details along with their cities and provide details for edit method.",
     *     operationId="getProvinceDetails",
     *     tags={"Province", "Province/Form"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="province",
     *         in="path",
     *         description="ID of the Province to fetch",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully fetched Province details with cities for editing",
     *         @OA\JsonContent(ref="#/components/schemas/Province"),
     *     )
     *   )
     * )
     */
    public function show(Province $province)
    {
        return response()->json([
            'data' => $province
        ], 200);
    }

    /**
     * @OA\Put(
     *     path="/api/admin/market/delivery/province/update/{province}",
     *     summary="updates an existing Province",
     *     description="this method updates an existing `Province`.",
     *     tags={"Province"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="province",
     *         in="path",
     *         description="The ID of the Province to be deleted",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *             @OA\Property(property="name", type="string", pattern="^[\u0600-\u06FF\s]+$", description="This field can only contain Persianh letters. Any other characters will result in a validation error.", example="بوشهر"),
     *                       )
     *             )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="successful Province update",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="true"),
     *             @OA\Property(property="message", type="string", example="استان x با موفقیت بروزرسانی شد")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="invalid request",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="نام استان الزامی است")
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
    public function update(Request $request, Province $province)
    {
        $request->validate([
            'name' => 'required|max:120|min:2|regex:/^[ا-یء-ي ]+$/u',
            // 'g-recaptcha-response' => 'recaptcha',
        ]);
        try {
            $inputs = $request->all();
            $province->update($inputs);
            return response()->json([
                'status' => true,
                'message' => 'استان ' . $province->name . ' با موفقیت بروزرسانی شد'
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
     *     path="/api/admin/market/delivery/province/destroy/{province}",
     *     summary="Delete a Province",
     *     description="This endpoint allows the user to `delete an existing Province`.",
     *     operationId="deleteProvince",
     *     tags={"Province"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="province",
     *         in="path",
     *         description="The ID of the Province to be deleted",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Province deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="استان x با موفقیت حذف شد")
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
    public function destroy(Province $province)
    {
        try{
        $result = $province->delete();
            return response()->json([
                'message' => 'استان ' . $province->name . ' با موفقیت حذف شد'
            ], 200);
        } catch(Exception $e) {
            return response()->json([
                'message' => 'خطایی غیرمنتظره در سرور رخ داده است. لطفا دوباره تلاش کنید'
            ], 500);

        }
    }
}
