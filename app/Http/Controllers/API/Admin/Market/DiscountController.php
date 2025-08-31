<?php

namespace App\Http\Controllers\API\Admin\Market;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Market\AmazingSaleRequest;
use App\Http\Requests\Admin\Market\CommonDiscountRequest;
use App\Http\Requests\Admin\Market\CopanRequest;
use App\Models\Market\AmazingSale;
use App\Models\Market\CommonDiscount;
use App\Models\Market\Copan;
use App\Models\Market\Product;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;

class DiscountController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/admin/market/discount/copan",
     *     summary="Retrieve list of Copans",
     *     description="Retrieve list of all `Copans`",
     *     tags={"Discount","Copan"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *     @OA\Response(
     *         response=200,
     *         description="A list of Copans",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="array",
     *                 @OA\Items(
     *                     ref="#/components/schemas/Copan"
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function copan()
    {
        $copans = Copan::orderBy('created_at', 'desc')->with('user:id,first_name,last_name')->simplePaginate(15);
        $copans->getCollection()->each(function ($item) {
            if (isset($item->user_id)) {
                $item->user->makeHidden(['status_value', 'activation_value', 'user_type_value']);
            }
        });
        return response()->json([
            'data' => $copans
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/market/discount/copan/search",
     *     summary="Searchs among Copans by code",
     *     description="This endpoint allows users to search for `Copans` by code. The search is case-insensitive and returns results that contain the given keyword. The results are paginated for better performance",
     *    tags={"Discount","Copan"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *   @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="type code of Copan which you're searching for",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="A list of Copans",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="array",
     *                 @OA\Items(
     *                     ref="#/components/schemas/Copan"
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function copanSearch(Request $request)
    {
        $copans = Copan::where('code', 'LIKE', "%" . $request->search . "%")->with('user:id,first_name,last_name')->orderBy('id')->simplePaginate(15);
        $copans->getCollection()->each(function ($item) {
            if (isset($item->user_id)) {
                $item->user->makeHidden(['status_value', 'activation_value', 'user_type_value']);
            }
        });
        return response()->json([
            'data' => $copans
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/market/discount/copan/status/{copan}",
     *     summary="Change the status of a copan",
     *     description="This endpoint `toggles the status of a Copan` (active/inactive)",
     *     operationId="updateCopanStatus",
     *     security={{"bearerAuth": {}}},
     *     tags={"Copan","Discount"},
     *     @OA\Parameter(
     *         name="copan",
     *         in="path",
     *         description="Copan id to change the status",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     security={ {"bearerAuth": {}} },
     *     @OA\Response(
     *         response=200,
     *         description="Copan status updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="checked", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="وضعیت کوپن x با موفقیت فعال شد")
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
    public function copanStatus(Copan $copan)
    {
        $copan->status = $copan->status == 1 ? 2 : 1;
        $result = $copan->save();
        if ($result) {
            if ($copan->status == 1) {
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
     * @OA\Get(
     *     path="/api/admin/market/discount/options",
     *     summary="Get necessary options for Discount forms",
     *     description="This endpoint returns all `Users which can be used to create or edit a copan` and all `Products for create or edit a AmazingSale`",
     *     tags={"Discount", "Discount/Form","Copan/Form","AmazingSale/Form"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successfully fetched Users and Products that you may need to make create or edit form",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="Users",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer"),
     *                         @OA\Property(property="first_name", type="string"),
     *                         @OA\Property(property="last_name", type="string"),
     *                     )
     *                 ),
     *                  @OA\Property(
     *                     property="Products",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer"),
     *                         @OA\Property(property="name", type="string"),
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
        $users = User::select('id', 'first_name', 'last_name')->get();
        $products = Product::select('id', 'name')->get();
        return response()->json([
            'data' => [
                'users' => $users,
                'products' => $products
            ]
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/market/discount/copan/show/{copan}",
     *     summary="Get details of a specific Copan",
     *     description="Returns the `Copan` details and provide details for edit method.",
     *     operationId="getCopanDetails",
     *     tags={"Copan","Copan", "Copan/Form","Discount","Discount/Form"},
     *   security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="copan",
     *         in="path",
     *         description="ID of the Copan to fetch",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully fetched Copan details for editing",
     *     @OA\JsonContent(ref="#/components/schemas/Copan"),
     *   )
     * )
     */
    public function copanShow(Copan $copan)
    {
        $copan->load('user:first_name,last_name,id');
        $copan->makeHidden(['status_value', 'activation_value', 'user_type_value']);
        return response()->json([
            'data' => $copan
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/admin/market/discount/copan/store",
     *     summary="create new copan",
     *     description="this method creates a new `Copan` and stores it.",
     *     tags={"Copan","Discount"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *             @OA\Property(property="code", type="string", pattern="^[a-zA-Z\u0600-\u06FF0-9\s\-\.\?]+$", description="This field can only contain Persian and English letters, Persian and English numbers, and symbols (-.,،.). Any other characters will result in a validation error.", example="takhfif"),
     *             @OA\Property(property="amount", type="float", example=50000),
     *             @OA\Property(property="discount_ceiling", type="float", example=45000),
     *             @OA\Property(
     *                 property="amount_type",
     *                 oneOf={
     *                     @OA\Schema(type="integer", example=1, description="1 = price unit"),
     *                     @OA\Schema(type="integer", example=2, description="2 = percentage")
     *                 }
     *             ),
     *             @OA\Property(
     *                 property="type",
     *                 oneOf={
     *                     @OA\Schema(type="integer", example=1, description="1 = spicially for one user"),
     *                     @OA\Schema(type="integer", example=2, description="2 = commoun=> every user can use it")
     *                 }
     *             ),
     *             @OA\Property(property="user_id",description="UserID.This field must be null when copan type=2 and it will be required when copan type=1", type="integer", nullable="true", example=5),
     *                 @OA\Property(property="start_date", type="integer", example=1677030400),
     *                 @OA\Property(property="end_date", type="integer", example=1677030400),
     *                       )
     *             )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="successful copan craetion",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="true"),
     *             @OA\Property(property="message", type="string", example="کوپن x با موفقیت افزوده شد")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="invalid request",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="نام  الزامی است")
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
    public function copanStore(CopanRequest $request)
    {
        try {
            date_default_timezone_set('Iran');
            $startDateTimestamp = substr($request['start_date'], 0, 10);
            $request['start_date'] = date("Y-m-d H:i:s", (int) $startDateTimestamp);
            $endDateTimestamp = substr($request['end_date'], 0, 10);
            $request['end_date'] = date("Y-m-d H:i:s", (int) $endDateTimestamp);
            $inputs = $request->all();
            if ($inputs['type'] == 0) {
                $inputs['user_id'] = null;
            }
            Copan::create($inputs);
            return response()->json([
                'status' => true,
                'message' => 'کوپن با موفقیت افزوده شد'
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
     *     path="/api/admin/market/discount/copan/update/{copan}",
     *     summary="Update an existing copan",
     *     description="this method updates an existing `Copan` and saves change",
     *     tags={"Copan","Discount"},
     *     security={{"bearerAuth": {}}},
     *  @OA\Parameter(
     *         name="copan",
     *         in="path",
     *         description="The ID of the Copan to be updated",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *             @OA\Property(property="code", type="string", pattern="^[a-zA-Z\u0600-\u06FF0-9\s\-\.\?]+$", description="This field can only contain Persian and English letters, Persian and English numbers, and symbols (-.,،.). Any other characters will result in a validation error.", example="takhfif"),
     *             @OA\Property(property="amount", type="float", example=50000),
     *             @OA\Property(property="discount_ceiling", type="float", example=45000),
     *             @OA\Property(
     *                 property="amount_type",
     *                 oneOf={
     *                     @OA\Schema(type="integer", example=1, description="1 = price unit"),
     *                     @OA\Schema(type="integer", example=2, description="2 = percentage")
     *                 }
     *             ),
     *             @OA\Property(
     *                 property="type",
     *                 oneOf={
     *                     @OA\Schema(type="integer", example=1, description="1 = spicially for one user"),
     *                     @OA\Schema(type="integer", example=2, description="2 = commoun=> every user can use it")
     *                 }
     *             ),
     *             @OA\Property(property="user_id",description="UserID.This field must be null when copan type=2 and it will be required when copan type=1", type="integer", nullable="true", example=5),
     *                 @OA\Property(property="start_date", type="integer", example=1677030400),
     *                 @OA\Property(property="end_date", type="integer", example=1677030400),
     *                       )
     *             )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="successful copan update",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="true"),
     *             @OA\Property(property="message", type="string", example="کوپن x با موفقیت بروزرسانی شد")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="invalid request",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="نام  الزامی است")
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

    public function copanUpdate(CopanRequest $request, Copan $copan)
    {
        try {
            date_default_timezone_set('Iran');
            $startDateTimestamp = substr($request['start_date'], 0, 10);
            $request['start_date'] = date("Y-m-d H:i:s", (int) $startDateTimestamp);
            $endDateTimestamp = substr($request['end_date'], 0, 10);
            $request['end_date'] = date("Y-m-d H:i:s", (int) $endDateTimestamp);
            $inputs = $request->all();
            if ($inputs['type'] == 0) {
                $inputs['user_id'] = null;
            }
            $copan->update($inputs);
            return response()->json([
                'status' => true,
                'message' => 'کوپن با موفقیت بروزرسانی شد'
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
     *     path="/api/admin/market/discount/copan/destroy/{copan}",
     *     summary="Delete a Copan",
     *     description="This endpoint allows the user to `delete an existing Copan`.",
     *     operationId="deleteCopan",
     *     tags={"Copan","Discount"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="copan",
     *         in="path",
     *         description="The ID of the Copan to be deleted",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Copan deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="کوپن Example با موفقیت حذف شد")
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
    public function copanDestroy(Copan $copan)
    {
        try {
            $copan->delete();
            return response()->json([
                'status' => true,
                'message' => 'کوپن با موفقیت حذف شد'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'مشکلی پیش آمده است. لطفا دوباره امتحان کنید'
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/admin/market/discount/common-discount",
     *     summary="Retrieve list of CommonDiscounts",
     *     description="Retrieve list of all `CommonDiscounts`",
     *     tags={"CommonDiscount","Discount"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *     @OA\Response(
     *         response=200,
     *         description="A list of CommonDiscounts",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="array",
     *                 @OA\Items(
     *                     ref="#/components/schemas/CommonDiscount"
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function commonDiscount()
    {
        $commonDiscounts = CommonDiscount::orderBy('created_at', 'desc')->simplePaginate(15);
        return response()->json([
            'data' => $commonDiscounts
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/market/discount/common-discount/search",
     *     summary="Searchs among CommonDiscounts by title",
     *     description="This endpoint allows users to search for `CommonDiscounts` by title. The search is case-insensitive and returns results that contain the given keyword. The results are paginated for better performance",
     *    tags={"CommonDiscount","Discount"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *   @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="type title of CommonDiscount which you're searching for",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="A list of CommonDiscounts",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="array",
     *                 @OA\Items(
     *                     ref="#/components/schemas/CommonDiscount"
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function commonDiscountSearch(Request $request)
    {
        $commonDiscounts = CommonDiscount::where('title', 'LIKE', "%" . $request->search . "%")->orderBy('title')->simplePaginate(15);
        return response()->json([
            'data' => $commonDiscounts
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/market/discount/common-discount/status/{commonDiscount}",
     *     summary="Change the status of a CommonDiscount",
     *     description="This endpoint `toggles the status of a CommonDiscount` (active/inactive)",
     *     operationId="updateCommonDiscountStatus",
     *     security={{"bearerAuth": {}}},
     *     tags={"CommonDiscount","Discount"},
     *     @OA\Parameter(
     *         name="commonDiscount",
     *         in="path",
     *         description="CommonDiscount id to change the status",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     security={ {"bearerAuth": {}} },
     *     @OA\Response(
     *         response=200,
     *         description="CommonDiscount status updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="checked", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="وضعیت تخفیف عمومی با موفقیت فعال شد")
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
    public function commonDiscountStatus(CommonDiscount $commonDiscount)
    {
        $commonDiscount->status = $commonDiscount->status == 1 ? 2 : 1;
        $result = $commonDiscount->save();
        if ($result) {
            if ($commonDiscount->status == 1) {
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
     * @OA\Get(
     *     path="/api/admin/market/discount/common-discount/show/{commonDiscount}",
     *     summary="Get details of a specific CommonDiscount",
     *     description="Returns the `CommonDiscount` details for edit method.",
     *     operationId="getCommonDiscountDetails",
     *     tags={"CommonDiscount", "CommonDiscount/Form","Discount","Discount/Form"},
     *   security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="commonDiscount",
     *         in="path",
     *         description="ID of the CommonDiscount to fetch",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully fetched CommonDiscount details for editing",
     *   @OA\JsonContent(ref="#/components/schemas/CommonDiscount"),
     *   )
     * )
     */
    public function commonDiscountShow(CommonDiscount $commonDiscount)
    {
        return response()->json([
            'data' => $commonDiscount
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/admin/market/discount/common-discount/store",
     *     summary="create new CommonDiscount",
     *     description="this method creates a new `CommonDiscount` and stores it",
     *     tags={"CommonDiscount","Discount"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *             @OA\Property(property="title", type="string", pattern="^[a-zA-Z\u0600-\u06FF0-9\s\-\.\?,.،]+$", description="This field can only contain Persian and English letters, Persian and English numbers, and hyphens (symobls (.,.،)). Any other characters will result in a validation error.", example="لوازم ورزشی"),
     *             @OA\Property(property="percentage", type="int", example=8),
     *             @OA\Property(property="discount_ceiling", type="float", example=50000),
     *             @OA\Property(property="minimal_order_amount", type="float", example=1000000),
     *             @OA\Property(
     *                 property="status",
     *                 oneOf={
     *                     @OA\Schema(type="integer", example=1, description="1 = active"),
     *                     @OA\Schema(type="integer", example=2, description="2 = inactive")
     *                 }
     *             ),
     *            @OA\Property(property="start_date", type="integer", example=1677030400),
     *            @OA\Property(property="end_date", type="integer", example=1677030400),
     *                       )
     *             )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="successful CommonDiscount creation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="true"),
     *             @OA\Property(property="message", type="string", example="تخفیف عمومی با موفقیت افزوده شد")
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
    public function commonDiscountStore(CommonDiscountRequest $request)
    {
        try {
            date_default_timezone_set('Iran');
            $startDateTimestamp = substr($request['start_date'], 0, 10);
            $request['start_date'] = date("Y-m-d H:i:s", (int) $startDateTimestamp);
            $endDateTimestamp = substr($request['end_date'], 0, 10);
            $request['end_date'] = date("Y-m-d H:i:s", (int) $endDateTimestamp);
            $inputs = $request->all();
            CommonDiscount::create($inputs);
            return response()->json([
                'status' => true,
                'message' => 'تخفیف عمومی با موفقیت افزوده شد'
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
     *     path="/api/admin/market/discount/common-discount/update/{commonDiscount}",
     *     summary="create new CommonDiscount",
     *     description="this method updates an existing `CommonDiscount` and stores it",
     *     tags={"CommonDiscount","Discount"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="commonDiscount",
     *         in="path",
     *         description="CommonDiscount id to be updated",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *             @OA\Property(property="title", type="string", pattern="^[a-zA-Z\u0600-\u06FF0-9\s\-\.\?]+$", description="This field can only contain Persian and English letters, Persian and English numbers, and symobls (.,.،). Any other characters will result in a validation error.", example="لوازم ورزشی"),
     *             @OA\Property(property="percentage", type="int", example=8),
     *             @OA\Property(property="discount_ceiling", type="float", example=50000),
     *             @OA\Property(property="minimal_order_amount", type="float", example=1000000),
     *             @OA\Property(
     *                 property="status",
     *                 oneOf={
     *                     @OA\Schema(type="integer", example=1, description="1 = active"),
     *                     @OA\Schema(type="integer", example=2, description="2 = inactive")
     *                 }
     *             ),
     *            @OA\Property(property="start_date", type="integer", example=1677030400),
     *            @OA\Property(property="end_date", type="integer", example=1677030400),
     *                       )
     *             )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="successful CommonDiscount update",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="true"),
     *             @OA\Property(property="message", type="string", example="تخفیف عمومی با موفقیت بروزرسانی شد")
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
    public function commonDiscountUpdate(CommonDiscountRequest $request, CommonDiscount $commonDiscount)
    {
        try {
            date_default_timezone_set('Iran');
            $startDateTimestamp = substr($request['start_date'], 0, 10);
            $request['start_date'] = date("Y-m-d H:i:s", (int) $startDateTimestamp);
            $endDateTimestamp = substr($request['end_date'], 0, 10);
            $request['end_date'] = date("Y-m-d H:i:s", (int) $endDateTimestamp);
            $inputs = $request->all();
            $update = $commonDiscount->update($inputs);
            return response()->json([
                'status' => true,
                'message' => 'تخفیف عمومی با موفقیت بروزرسانی شد'
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
     *     path="/api/admin/market/discount/common-discount/destroy/{commonDiscount}",
     *     summary="Delete a CommonDiscount",
     *     description="This endpoint allows the user to `delete an existing CommonDiscount`.",
     *     operationId="deletecommonDiscount",
     *     tags={"CommonDiscount","Discount"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="commonDiscount",
     *         in="path",
     *         description="The ID of the CommonDiscount to be deleted",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="CommonDiscount deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="تخفیف عمومی  با موفقیت حذف شد")
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
    public function commonDiscountDestroy(CommonDiscount $commonDiscount)
    {
        try {
            $result = $commonDiscount->delete();
            return response()->json([
                'status' => true,
                'message' => ' تخفیف عمومی با موفقیت حذف شد'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'مشکلی پیش آمده است. لطفا دوباره امتحان کنید'
            ], 500);
        }
    }
    /**
     * @OA\Get(
     *     path="/api/admin/market/discount/amazing-sale",
     *     summary="Retrieve list of categories",
     *     description="Retrieve list of all `AmazingSale`",
     *     tags={"AmazingSale","Discount"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *     @OA\Response(
     *         response=200,
     *         description="A list of AmazingSales",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="array",
     *                 @OA\Items(
     *                     ref="#/components/schemas/AmazingSale"
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function amazingSale()
    {
        $amazingSales = AmazingSale::orderBy('created_at', 'desc')->with('product:id,name')->simplePaginate(15);
        $amazingSales->getCollection()->each(function ($item) {
            $item->product->makeHidden(['status_value', 'related_products_value', 'marketable_value']);
        });
        return response()->json([
            'data' => $amazingSales
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/market/discount/amazing-sale/search",
     *     summary="Searchs among AmazingSales by name",
     *     description="This endpoint allows users to search for `AmazingSales` by name. The search is case-insensitive and returns results that contain the given keyword. The results are paginated for better performance",
     *    tags={"AmazingSale","Discount"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *   @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="type name of product which you're searching for its AmazingSale",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="A list of AmazingSales",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="array",
     *                 @OA\Items(
     *                     ref="#/components/schemas/AmazingSale"
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function amazingSaleSearch(Request $request)
    {
        $products = Product::where('name', 'LIKE', "%" . $request->search . "%")->get();
        $amazingSales = collect();
        foreach ($products as $product) {
            if ($product->amazingSales()->get()->toArray()) {
                $amazingSales->push($product->amazingSales);
            }
        }
        $first = $amazingSales->first()->load('product:id,name');
        $first[0]->product->setHidden(['status_value', 'marketable_value', 'related_products_value']);
        return response()->json([
            'data' => $first
        ], 200);
    }
    /**
     * @OA\Get(
     *     path="/api/admin/market/discount/amazing-sale/status/{amazingSale}",
     *     summary="Change the status of a AmazingSale",
     *     description="This endpoint `toggles the status of a AmazingSale` (active/inactive)",
     *     operationId="updateAmazingSaleStatus",
     *     security={{"bearerAuth": {}}},
     *     tags={"AmazingSale","Discount"},
     *     @OA\Parameter(
     *         name="amazingSale",
     *         in="path",
     *         description="AmazingSale id to change the status",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     security={ {"bearerAuth": {}} },
     *     @OA\Response(
     *         response=200,
     *         description="AmazingSale status updated successfully",
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
    public function amazingSaleStatus(AmazingSale $amazingSale)
    {
        $amazingSale->status = $amazingSale->status == 1 ? 2 : 1;
        $result = $amazingSale->save();
        if ($result) {
            if ($amazingSale->status == 1) {
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
     * @OA\Get(
     *     path="/api/admin/market/discount/amazing-sale/show/{amazingSale}",
     *     summary="Get details of a specific AmazingSale",
     *     description="Returns the `AmazingSale` details and provide details for edit method.",
     *     operationId="getAmazingSaleDetails",
     *     tags={"AmazingSale", "AmazingSale/Form","Discount","Discount/Form"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="amazingSale",
     *         in="path",
     *         description="ID of the AmazingSale to fetch",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully fetched AmazingSale details for editing",
     *         @OA\JsonContent(ref="#/components/schemas/AmazingSale"),
     *     )
     *  )
     */

    public function amazingSaleShow(AmazingSale $amazingSale)
    {
        $amazingSale->load('product:name,id');
        $amazingSale->product->makeHidden(['status_value', 'marketable_value', 'related_products_value']);
        return response()->json([
            'data' => $amazingSale
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/admin/market/discount/amazing-sale/store",
     *     summary="create new AmazingSale",
     *     description="this method creates a new `AmazingSale` and stores it.",
     *     tags={"AmazingSale","Discount"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *             @OA\Property(property="percentage", type="number", minimum="0", maximum="100", example=7),
     *             @OA\Property(property="product_id",description="ProductID.Id of the product for which the special amaingsale discount is being created ", type="integer", nullable="true", example=12),
     *             @OA\Property(property="start_date", type="integer", example=1677030400),
     *             @OA\Property(property="end_date", type="integer", example=1677030400),
     *                       )
     *             )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="successful AmazingSale creation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="true"),
     *             @OA\Property(property="message", type="string", example="محصول با موفقیت به فروش شگفت انگیز افزوده شد")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="invalid request",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="وارد کردن محصول الزامی است")
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
    public function amazingSaleStore(AmazingSaleRequest $request)
    {
        try {
            date_default_timezone_set('Iran');
            $startDateTimestamp = substr($request['start_date'], 0, 10);
            $request['start_date'] = date("Y-m-d H:i:s", (int) $startDateTimestamp);
            $endDateTimestamp = substr($request['end_date'], 0, 10);
            $request['end_date'] = date("Y-m-d H:i:s", (int) $endDateTimestamp);
            $inputs = $request->all();
            $amazingSale = AmazingSale::create($inputs);
            return response()->json([
                'status' => true,
                'message' => 'محصول با موفقیت به فروش شگفت انگیز افزوده شد'
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
     *     path="/api/admin/market/discount/amazing-sale/update/{amazingSale}",
     *     summary="Updates an existing AmazingSale",
     *     description="this method updates an existing `AmazingSale` and saves it.",
     *     tags={"AmazingSale","Discount"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="amazingSale",
     *         in="path",
     *         description="AmazingSale id to fetch record",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *             @OA\Property(property="percentage", type="number", minimum="0", maximum="100", example=7),
     *             @OA\Property(property="product_id",description="ProductID.Id of the product for which the special amaingsale discount is being created ", type="integer", nullable="true", example=12),
     *             @OA\Property(property="start_date", type="integer", example=1677030400),
     *             @OA\Property(property="end_date", type="integer", example=1677030400),
     *                       )
     *             )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="successful AmazingSale update",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="true"),
     *             @OA\Property(property="message", type="string", example="محصول فروش شگفت انگیز با موفقیت ویرایش شد")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="invalid request",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="وارد کردن محصول الزامی است")
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
    public function amazingSaleUpdate(AmazingSaleRequest $request, AmazingSale $amazingSale)
    {
        try {
            date_default_timezone_set('Iran');
            $startDateTimestamp = substr($request['start_date'], 0, 10);
            $request['start_date'] = date("Y-m-d H:i:s", (int) $startDateTimestamp);
            $endDateTimestamp = substr($request['end_date'], 0, 10);
            $request['end_date'] = date("Y-m-d H:i:s", (int) $endDateTimestamp);
            $inputs = $request->all();
            $update = $amazingSale->update($inputs);
            return response()->json([
                'status' => true,
                'message' => ' محصول فروش شگفت انگیز با موفقیت ویرایش شد'
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
     *     path="/api/admin/market/discount/amazing-sale/destroy/{amazingSale}",
     *     summary="Delete a AmazingSale",
     *     description="This endpoint allows the user to `delete an existing AmazingSale`.",
     *     operationId="deleteAmazingSaley",
     *     tags={"AmazingSale","Discount"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="amazingSale",
     *         in="path",
     *         description="The ID of the AmazingSale to be deleted",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="AmazingSale deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="محصول با موفقیت از لیست فروش شگفت انگیز حذف شد")
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
    public function amazingSaleDestroy(AmazingSale $amazingSale)
    {
        try {
            $amazingSale->delete();
            return response()->json([
                'status' => true,
                'message' => 'محصول با موفقیت از لیست فروش شگفت انگیز حذف شد'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'عملیات با خطا مواجه شد. لطفا دوباره امتحان کنید'
            ], 500);
        }
    }

}
