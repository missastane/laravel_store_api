<?php

namespace App\Http\Controllers\API\Admin\Market;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Market\DeliveryRequest;
use App\Models\Market\Delivery;
use Exception;
use Illuminate\Http\Request;

class DeliveryController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/admin/market/delivery",
     *     summary="Retrieve list of Delivery Methods",
     *     description="Retrieve list of all `Delivery Methods`",
     *     tags={"Delivery"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="A list of Dilivery methods",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="array",
     *                 @OA\Items(
     *                     ref="#/components/schemas/Delivery"
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function index()
    {
        $delivery_methods = Delivery::orderBy('created_at', 'desc')->simplePaginate(15);
        return response()->json([
            'data' => $delivery_methods
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/market/delivery/search",
     *     summary="Searchs among Delivery methods by name",
     *     description="This endpoint allows users to search for `Delivery methods` by name. The search is case-insensitive and returns results that contain the given keyword. The results are paginated for better performance",
     *    tags={"Delivery"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *   @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="type name of Delivery which you're searching for",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="A list of Delivery methods",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="array",
     *                 @OA\Items(
     *                     ref="#/components/schemas/Delivery"
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function search(Request $request)
    {
        $delivery_methods = Delivery::where('name', 'LIKE', "%" . $request->search . "%")->orderBy('name')->get();
        return response()->json([
            'data' => $delivery_methods
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/market/delivery/show/{delivery}",
     *     summary="Get details of a specific Delivery",
     *     description="Returns the `Delivery` details and provide details for edit method.",
     *     operationId="getDeliveryyDetails",
     *     tags={"Delivery", "Delivery/Form"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="delivery",
     *         in="path",
     *         description="ID of the delivery to fetch",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully fetched Delivery details for editing",
     *     @OA\JsonContent(ref="#/components/schemas/Delivery"),
     *   )
     * )
     */
    public function show(Delivery $delivery)
    {
        return response()->json([
            'data' => $delivery
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/admin/market/delivery/store",
     *     summary="create new delivery",
     *     description="this method creates a new `Delivery method` and stores it",
     *     tags={"Delivery"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *             @OA\Property(property="name", type="string", pattern="^[a-zA-Z\u0600-\u06FF0-9\s\-\.\?]+$", description="This field can only contain Persian and English letters, Persian and English numbers, and symobols (-). Any other characters will result in a validation error.", example="پست ممتاز"),
     *             @OA\Property(property="amount", type="float", example=125000),
     *             @OA\Property(property="delivery_time", type="integer", example=1),
     *             @OA\Property(property="delivery_time_unit", type="string",pattern="^[a-zA-Z\u0600-\u06FF0-9\s\-\.\?]+$", description="This field can only contain Persian and English letters, Persian and English numbers, and symobols (-). Any other characters will result in a validation error.", example="ساعت"),
     *             @OA\Property(
     *                 property="status",
     *                 oneOf={
     *                     @OA\Schema(type="integer", example=1, description="1 = active"),
     *                     @OA\Schema(type="integer", example=2, description="2 = inactive")
     *                 }
     *             ),
     *          
     *            )
     *        )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="successful Delivery creation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="true"),
     *             @OA\Property(property="message", type="string", example="روش x با موفقیت افزوده شد")
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
    public function store(DeliveryRequest $request)
    {
        try {
            $inputs = $request->all();
            $delivery = Delivery::create($inputs);

            return response()->json([
                'status' => true,
                'message' => 'روش ' . $delivery->name . ' با موفقیت افزوده شد'
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message',
                'خطایی غیرمنتظره در سرور رخ داده است. لطفا دوباره تلاش کنید'
            ], 500);

        }
    }

    /**
     * @OA\Get(
     *     path="/api/admin/market/delivery/status/{delivery}",
     *     summary="Change the status of a Delivery",
     *     description="This endpoint `toggles the status of a Delivery` (active/inactive)",
     *     operationId="updateDeliveryStatus",
     *     security={{"bearerAuth": {}}},
     *     tags={"Delivery"},
     *     @OA\Parameter(
     *         name="delivery",
     *         in="path",
     *         description="Delivery id to change the status",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     security={ {"bearerAuth": {}} },
     *     @OA\Response(
     *         response=200,
     *         description="Delivery status updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="checked", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="وضعیت روش x با موفقیت فعال شد")
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
    public function status(Delivery $delivery)
    {
        $delivery->status = $delivery->status == 1 ? 2 : 1;
        $result = $delivery->save();
        if ($result) {
            if ($delivery->status == 1) {
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
     *     path="/api/admin/market/delivery/update/{delivery}",
     *     summary="updates an existing Delivery method",
     *     description="this method updates an existing `Delivery method` and save changes",
     *     tags={"Delivery"},
     *     security={{"bearerAuth": {}}},
     *  @OA\Parameter(
     *         name="delivery",
     *         in="path",
     *         description="Delivery id to fetch",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *             @OA\Property(property="name", type="string", pattern="^[a-zA-Z\u0600-\u06FF0-9\s\-\.\?]+$", description="This field can only contain Persian and English letters, Persian and English numbers, and symobols (-). Any other characters will result in a validation error.", example="پست ممتاز"),
     *             @OA\Property(property="amount", type="float", example=125000),
     *             @OA\Property(property="delivery_time", type="integer", example=1),
     *             @OA\Property(property="delivery_time_unit", type="string",pattern="^[a-zA-Z\u0600-\u06FF0-9\s\-\.\?]+$", description="This field can only contain Persian and English letters, Persian and English numbers, and symobols (-). Any other characters will result in a validation error.", example="ساعت"),
     *             @OA\Property(
     *                 property="status",
     *                 oneOf={
     *                     @OA\Schema(type="integer", example=1, description="1 = active"),
     *                     @OA\Schema(type="integer", example=2, description="2 = inactive")
     *                 }
     *             ),
     *            )
     *        )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="successful Delivery update",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="true"),
     *             @OA\Property(property="message", type="string", example="روش x با موفقیت بروزرسانی شد")
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
    public function update(DeliveryRequest $request, Delivery $delivery)
    {
        $inputs = $request->all();
        $update = $delivery->update($inputs);
        if ($update) {
            return response()->json([
                'status' => true,
                'message' => 'روش ' . $delivery->name . ' با موفقیت بروزرسانی شد'
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'خطایی غیرمنتظره در سرور رخ داده است. لطفا دوباره تلاش کنید'
            ], 500);

        }
    }

    /**
     * @OA\Delete(
     *     path="/api/admin/market/delivery/destroy/{delivery}",
     *     summary="Delete a Delivery",
     *     description="This endpoint allows the user to `delete an existing Delivery`.",
     *     operationId="deleteDelivery",
     *     tags={"Delivery"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="delivery",
     *         in="path",
     *         description="The ID of the delivery to be deleted",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Delivery deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="دسته Example delivery با موفقیت حذف شد")
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
    public function destroy(Delivery $delivery)
    {
        try {
            $result = $delivery->delete();
            return response()->json([
                'status' => true,
                'message' => 'روش ' . $delivery->name . ' با موفقیت حذف شد'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'عملیات با خطا مواجه شد. لطفا دوباره امتحان کنید'
            ], 500);

        }
    }
}
