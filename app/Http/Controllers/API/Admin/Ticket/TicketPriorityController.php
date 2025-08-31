<?php

namespace App\Http\Controllers\API\Admin\Ticket;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Ticket\TicketPriorityRequest;
use App\Models\Ticket\TicketPriority;
use Exception;
use Illuminate\Http\Request;

class TicketPriorityController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/admin/ticket/priority",
     *     summary="Retrieve list of TicketPriorities",
     *     description="Retrieve list of all `TicketPriorities`",
     *     tags={"TicketPriority"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *     @OA\Response(
     *         response=200,
     *         description="A list of TicketPriority",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="array",
     *                 @OA\Items(
     *                     ref="#/components/schemas/TicketPriority"
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function index()
    {
        $ticketPriorities = TicketPriority::orderBy('created_at', 'desc')->simplePaginate(15);
        return response()->json([
            'data' => $ticketPriorities
        ], 200);
    }
    /**
     * @OA\Get(
     *     path="/api/admin/ticket/priority/search",
     *     summary="Searchs among TicketPriorities by name",
     *     description="This endpoint allows users to search for `TicketPriorities` by name. The search is case-insensitive and returns results that contain the given keyword. The results are paginated for better performance",
     *     tags={"TicketPriority"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *   @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="type name of TicketPriority which you're searching for",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="A list of TicketPriorities",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="array",
     *                 @OA\Items(
     *                     ref="#/components/schemas/TicketPriority"
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function search(Request $request)
    {
        $ticketPriorities = TicketPriority::where('name', 'LIKE', "%" . $request->search . "%")->orderBy('name')->get();
        return response()->json([
            'data' => $ticketPriorities
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/ticket/priority/show/{ticketPriority}",
     *     summary="Get details of a specific TicketPriority",
     *     description="Returns the `TicketPriority` details and provide details for edit method.",
     *     operationId="getTicketPriorityDetails",
     *     tags={"TicketPriority", "TicketPriority/Form"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="ticketPriority",
     *         in="path",
     *         description="ID of the TicketPriority to fetch",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully fetched TicketPriority details for editing",
     *     @OA\JsonContent(ref="#/components/schemas/TicketPriority"),
     *     )
     * )
     */
    public function show(TicketPriority $ticketPriority)
    {
        return response()->json([
            'data' => $ticketPriority
        ], 200);
    }
    /**
     * @OA\Post(
     *     path="/api/admin/ticket/priority/store",
     *     summary="create new TicketPriority",
     *     description="this method creates a new `TicketPriority` and stores it.",
     *     tags={"TicketPriority"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *             @OA\Property(property="name", type="string", pattern="^[a-zA-Z\u0600-\u06FF0-9\s\-\.\,]+$", description="This field can only contain Persian and English letters, Persian and English numbers, and symbols (-.,). Any other characters will result in a validation error.", example="بسیار کم اهمیت"),
     *             @OA\Property(
     *                 property="status",
     *                 oneOf={
     *                     @OA\Schema(type="integer", example=1, description="1 = active"),
     *                     @OA\Schema(type="integer", example=2, description="2 = inactive")
     *                 }
     *             ),
     *                       )
     *             )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="successful TicketPriority creation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="true"),
     *             @OA\Property(property="message", type="string", example="اولویت x با موفقیت افزوده شد")
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
    public function store(TicketPriorityRequest $request)
    {
        try {
            $inputs = $request->all();
            $ticketPriority = TicketPriority::create($inputs);
            return response()->json([
                'status' => true,
                'message' => 'اولویت ' . $ticketPriority->name . ' با موفقیت افزوده شد'
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
     *     path="/api/admin/ticket/priority/status/{ticketPriority}",
     *     summary="Change the status of a TicketPriority",
     *     description="This endpoint `toggles the status of a TicketPriority` (active/inactive)",
     *     operationId="updateTicketPriorityStatus",
     *     security={{"bearerAuth": {}}},
     *     tags={"TicketPriority"},
     *     @OA\Parameter(
     *         name="ticketPriority",
     *         in="path",
     *         description="TicketPriority id to change the status",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     security={ {"bearerAuth": {}} },
     *     @OA\Response(
     *         response=200,
     *         description="TicketPriority status updated successfully",
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
    public function status(TicketPriority $ticketPriority)
    {
        $ticketPriority->status = $ticketPriority->status == 1 ? 2 : 1;
        $result = $ticketPriority->save();
        if ($result) {
            if ($ticketPriority->status == 1) {
                return response()->json([
                    'status' => true,
                    'checked' => true,
                    'message' => 'وضعیت ' . $ticketPriority->name . ' با موفقیت فعال شد'
                ]);
            } else {
                return response()->json([
                    'status' => true,
                    'checked' => false,
                    'message' => 'وضعیت ' . $ticketPriority->name . ' با موفقیت غیرفعال شد'
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
     *     path="/api/admin/ticket/priority/update/{ticketPriority}",
     *     summary="Update an existing TicketPriority",
     *     description="this method update an existing `TicketPriority` and stores it.",
     *     tags={"TicketPriority"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="ticketPriority",
     *         in="path",
     *         description="TicketPriority id to fetch",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *             @OA\Property(property="name", type="string", pattern="^[a-zA-Z\u0600-\u06FF0-9\s\-\.\,]+$", description="This field can only contain Persian and English letters, Persian and English numbers, and symbols (-.,). Any other characters will result in a validation error.", example="بسیار کم اهمیت"),
     *             @OA\Property(
     *                 property="status",
     *                 oneOf={
     *                     @OA\Schema(type="integer", example=1, description="1 = active"),
     *                     @OA\Schema(type="integer", example=2, description="2 = inactive")
     *                 }
     *             ),
     *                       )
     *             )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="successful TicketPriority update",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="true"),
     *             @OA\Property(property="message", type="string", example="اولویت x با موفقیت بروزرسانی شد")
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
    public function update(TicketPriority $ticketPriority, TicketPriorityRequest $request)
    {
        try {
            $inputs = $request->all();
            $result = $ticketPriority->update($inputs);
            return response()->json([
                'status' => true,
                'message' => 'اولویت ' . $ticketPriority->name . ' با موفقیت بروزرسانی شد'
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
     *     path="/api/admin/ticket/priority/destroy/{ticketPriority}",
     *     summary="Delete a TicketPriority",
     *     description="This endpoint allows the user to `delete an existing TicketPriority`.",
     *     operationId="deleteTicketPriority",
     *     tags={"TicketPriority"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="ticketPriority",
     *         in="path",
     *         description="The ID of the TicketPriority to be deleted",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="TicketPriority deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="اولویت Example با موفقیت حذف شد")
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
    public function destroy(TicketPriority $ticketPriority)
    {
        $result = $ticketPriority->delete();
        if ($result) {
            return response()->json([
                'status' => true,
                'message' => 'اولویت ' . $ticketPriority->name . ' با موفقیت حذف شد'
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'عملیات با خطا مواجه شد. لطفا دوباره امتحان کنید'
            ], 500);

        }
    }
}
