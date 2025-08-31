<?php

namespace App\Http\Controllers\API\Admin\Market;

use App\Http\Controllers\Controller;
use App\Models\User\City;
use App\Models\User\Province;
use Exception;
use Illuminate\Http\Request;

class CityController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/admin/market/delivery/city/{province}",
     *     summary="Retrieve list of all `Cities` of a province",
     *     description="Retrieve list of all `Cities` of a province",
     *  tags={"City"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *  @OA\Parameter(
     *         name="province",
     *         in="path",
     *         description="province id to fetch its Cities",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="A list of `Cities` of a specisl province",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="array",
     *                 @OA\Items(
     *                     ref="#/components/schemas/City"
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function index(Province $province)
    {
        return response()->json([
            'data' => $province->cities()->with('province:name,id')->simplePaginate(15)
        ], 200);
    }


    /**
     * @OA\Get(
     *     path="/api/admin/market/delivery/city/search/{province}",
     *     summary="Searches among ProductColor by name.",
     *     description="This endpoint allows users to search for `Cities of A Province` by name. The search is case-insensitive and returns results that contain the given keyword. The results are paginated for better performance",
     *     tags={"City"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *  @OA\Parameter(
     *         name="province",
     *         in="path",
     *         description="Id of province that you want search fo its Cities",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *   @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="type name of City which you're searching for",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="A list of Cities of a Province",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="array",
     *                 @OA\Items(
     *                     ref="#/components/schemas/City"
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function search(Request $request, Province $province)
    {
        $cities = City::where('province_id', $province->id)->where('name', 'LIKE', "%" . $request->search . "%")->with('province:name,id')->orderBy('name')->get();
        return response()->json([
            'data' => $cities
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/market/delivery/city/show/{city}",
     *     summary="Returns City details for edit form",
     *     description="Returns `City` details with its province for edit form",
     *     tags={"City","City/Form"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *     @OA\Parameter(
     *         name="city",
     *         in="path",
     *         description="Id of city that you want showing",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="A City with its province",
     *       @OA\JsonContent(ref="#/components/schemas/City"),
     *     )
     * )
     */
    public function show(City $city)
    {
        return response()->json([
            'data' => $city->load('province:id,name')
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/admin/market/delivery/city/store/{province}",
     *     summary="create new city for a special province",
     *     description="this method creates a new `City` for the province and stores it.",
     *     tags={"City"},
     *     security={{"bearerAuth": {}}},
     *   @OA\Parameter(
     *         name="province",
     *         in="path",
     *         description="ID of the province to fetch",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *             @OA\Property(property="name", type="string", pattern="^[\u0600-\u06FF ]+$", description="This field can only contain Persian letters and space. Any other characters will result in a validation error.", example="2"),
     *             )
     * )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="successful City creation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="true"),
     *             @OA\Property(property="message", type="string", example="شهر x با موفقیت افزوده شد"),
     *            
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
    public function store(Request $request, Province $province)
    {
        $request->validate([
            'name' => 'required|max:120|min:2|regex:/^[ا-یء-ي ]+$/u',
            // 'g-recaptcha-response' => 'recaptcha',
        ]);
        try {
            $inputs = $request->all();
            $inputs['province_id'] = $province->id;
            $city = City::create($inputs);
            return response()->json([
                'status' => true,
                'message' => 'شهر ' . $city->name . ' با موفقیت افزوده شد'
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'خطایی غیرمنتظره در سرور رخ داده است. لطفا دوباره تلاش کنید'
            ], 500);

        }
    }

    /**
     * @OA\Put(
     *     path="/api/admin/market/delivery/city/update/{city}",
     *     summary="updates an exisiting `City",
     *     description="this method updates an exisiting `City` and saves changes.",
     *     tags={"City"},
     *     security={{"bearerAuth": {}}},
     *   @OA\Parameter(
     *         name="city",
     *         in="path",
     *         description="ID of the city to fetch",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *             @OA\Property(property="name", type="string", pattern="^[\u0600-\u06FF ]+$", description="This field can only contain Persian letters and space. Any other characters will result in a validation error.", example="2"),
     *             )
     * )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="successful City update",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="true"),
     *             @OA\Property(property="message", type="string", example="شهر x با موفقیت بروزرسانی شد"),
     *            
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
    public function update(Request $request, City $city)
    {
        $request->validate([
            'name' => 'required|max:120|min:2|regex:/^[ا-یء-ي ]+$/u',
            // 'g-recaptcha-response' => 'recaptcha',
        ]);
        try {
            $inputs['province_id'] = $city->province->id;
            $inputs = $request->all();
            $update = $city->update($inputs);

            return response()->json([
                'status' => true,
                'message' => 'شهر ' . $city->name . ' با موفقیت بروزرسانی شد'
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
     *     path="/api/admin/market/delivery/city/destroy/{city}",
     *     summary="Delete a ProductCategory",
     *     description="This endpoint allows the user to `delete an existing City`.",
     *     operationId="deleteCityy",
     *     tags={"City"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="city",
     *         in="path",
     *         description="The ID of the City to be deleted",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="City deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="شهر Example city با موفقیت حذف شد")
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
    public function destroy(City $city)
    {
        try {
            $result = $city->delete();
            return response()->json([
                'status' => true,
                'message' => 'شهر ' . $city->name . ' با موفقیت حذف شد'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'خطایی غیرمنتظره در سرور رخ داده است. لطفا دوباره تلاش کنید'
            ], 500);

        }
    }
}
