<?php

namespace App\Http\Controllers\API\Admin\Ticket;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Ticket\TicketCategoryRequest;
use App\Models\Ticket\TicketCategory;
use Exception;
use Illuminate\Http\Request;

class TicketCategoryController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/admin/ticket/category",
     *     summary="Retrieve list of TicketCategories",
     *     description="Retrieve list of all `TicketCategories`",
     *     tags={"TicketCategory"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *     @OA\Response(
     *         response=200,
     *         description="A list of TicketCategories",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="array",
     *                 @OA\Items(
     *                     ref="#/components/schemas/TicketCategory"
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function index()
    {
        $ticketCategories = TicketCategory::orderBy('created_at', 'desc')->simplePaginate(15);
        return response()->json([
            'data' => $ticketCategories
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/ticket/category/search",
     *     summary="Searchs among TicketCategories by name",
     *     description="This endpoint allows users to search for `TicketCategories` by name. The search is case-insensitive and returns results that contain the given keyword. The results are paginated for better performance",
     *     tags={"TicketCategory"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *   @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="type name of TicketCategory which you're searching for",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="A list of TicketCategories",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="array",
     *                 @OA\Items(
     *                     ref="#/components/schemas/TicketCategory"
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function search(Request $request)
    {
        $ticketCategories = TicketCategory::where('name', 'LIKE', "%" . $request->search . "%")->orderBy('name')->get();
        return response()->json([
            'data' => $ticketCategories
        ], 200);
    }


    /**
     * @OA\Get(
     *     path="/api/admin/ticket/category/show/{ticketCategory}",
     *     summary="Get details of a specific TicketCategory",
     *     description="Returns the `TicketCategory` details and provide details for edit method.",
     *     operationId="getTicketCategoryDetails",
     *     tags={"TicketCategory", "TicketCategory/Form"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="ticketCategory",
     *         in="path",
     *         description="ID of the TicketCategory to fetch",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully fetched TicketCategory details for editing",
     *       @OA\JsonContent(ref="#/components/schemas/TicketCategory"),
     *     )
     * )
     */
    public function show(TicketCategory $ticketCategory)
    {
        return response()->json([
            'data' => $ticketCategory
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/admin/ticket/category/store",
     *     summary="create new TicketCategory",
     *     description="this method creates a new `TicketCategory` and stores it.",
     *     tags={"TicketCategory"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *             @OA\Property(property="name", type="string", pattern="^[a-zA-Z\u0600-\u06FF0-9\s\-\.\,]+$", description="This field can only contain Persian and English letters, Persian and English numbers, and symbols (-.,). Any other characters will result in a validation error.", example="لوازم ورزشی"),
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
     *         description="successful TicketCategory creation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="true"),
     *             @OA\Property(property="message", type="string", example="دسته x با موفقیت افزوده شد")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="invalid request",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="نام دسته بندی الزامی است")
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

    public function store(TicketCategoryRequest $request)
    {
        try {
            $inputs = $request->all();
            $ticketCategory = TicketCategory::create($inputs);
            return response()->json([
                'status' => true,
                'message' => 'دسته ' . $ticketCategory->name . ' با موفقیت افزوده شد'
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
     *     path="/api/admin/ticket/category/status/{ticketCategory}",
     *     summary="Change the status of a TicketCategory",
     *     description="This endpoint `toggles the status of a TicketCategory` (active/inactive)",
     *     operationId="updateTicketCategoryStatus",
     *     security={{"bearerAuth": {}}},
     *     tags={"TicketCategory"},
     *     @OA\Parameter(
     *         name="ticketCategory",
     *         in="path",
     *         description="TicketCategory id to change the status",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     security={ {"bearerAuth": {}} },
     *     @OA\Response(
     *         response=200,
     *         description="TicketCategory status updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="checked", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="وضعیت دسته x با موفقیت فعال شد")
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
    public function status(TicketCategory $ticketCategory)
    {
        $ticketCategory->status = $ticketCategory->status == 1 ? 2 : 1;
        $result = $ticketCategory->save();
        if ($result) {
            if ($ticketCategory->status == 1) {
                return response()->json([
                    'status' => true,
                    'checked' => true,
                    'message' => 'وضعیت ' . $ticketCategory->name . ' با موفقیت فعال شد'
                ]);
            } else {
                return response()->json([
                    'status' => true,
                    'checked' => false,
                    'message' => 'وضعیت ' . $ticketCategory->name . ' با موفقیت غیرفعال شد'
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
     *     path="/api/admin/ticket/category/update/{ticketCategory}",
     *     summary="Update an existing TicketCategory",
     *     description="this method update an existing `TicketCategory` and stores it.",
     *     tags={"TicketCategory"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="ticketCategory",
     *         in="path",
     *         description="TicketCategory id to fetch",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *             @OA\Property(property="name", type="string", pattern="^[a-zA-Z\u0600-\u06FF0-9\s\-\.\,]+$", description="This field can only contain Persian and English letters, Persian and English numbers, and symbols (-.,). Any other characters will result in a validation error.", example="لوازم ورزشی"),
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
     *         description="successful TicketCategory update",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="true"),
     *             @OA\Property(property="message", type="string", example="دسته x با موفقیت بروزرسانی شد")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="invalid request",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="نام دسته بندی الزامی است")
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
    public function update(TicketCategory $ticketCategory, TicketCategoryRequest $request)
    {
        try {
            $inputs = $request->all();
            $result = $ticketCategory->update($inputs);
            return response()->json([
                'status' => true,
                'message' => 'دسته ' . $ticketCategory->name . ' با موفقیت بروزرسانی شد'
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
     *     path="/api/admin/ticket/category/destroy/{ticketCategory}",
     *     summary="Delete a TicketCategory",
     *     description="This endpoint allows the user to `delete an existing TicketCategory`.",
     *     operationId="deleteTicketCategory",
     *     tags={"TicketCategory"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="ticketCategory",
     *         in="path",
     *         description="The ID of the TicketCategory to be deleted",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="TicketCategory deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="دسته Example با موفقیت حذف شد")
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
    public function destroy(TicketCategory $ticketCategory)
    {
        try {
            $ticketCategory->delete();
            return response()->json([
                'status' => true,
                'message' => 'دسته ' . $ticketCategory->name . ' با موفقیت حذف شد'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'عملیات با خطا مواجه شد. لطفا دوباره امتحان کنید'
            ], 500);

        }
    }
}
