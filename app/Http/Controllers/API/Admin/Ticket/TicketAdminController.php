<?php

namespace App\Http\Controllers\API\Admin\Ticket;

use App\Http\Controllers\Controller;
use App\Models\Ticket\TicketAdmin;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TicketAdminController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/admin/ticket/admin",
     *     summary="Retrieve list of TicketAdmins",
     *     description="Retrieve list of all `TicketAdmins`",
     *     tags={"TicketAdmin"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *     @OA\Response(
     *         response=200,
     *         description="A list of TicketAdmins",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="array",
     *                 @OA\Items(
     *                     ref="#/components/schemas/User"
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function index()
    {
        $admins = User::where('user_type', 1)->orderBy('created_at')->simplePaginate(15);
        return response()->json([
            'data' => $admins
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/ticket/admin/search",
     *     summary="Searchs among TicketAdmins by first name or last name",
     *     description="This endpoint allows users to search for `TicketAdmins` by first name or last name. The search is case-insensitive and returns results that contain the given keyword. The results are paginated for better performance",
     *     tags={"TicketAdmin"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *   @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="typefirst name or last name of TicketAdmin which you're searching for",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="A list of TicketAdmins",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="array",
     *                 @OA\Items(
     *                     ref="#/components/schemas/User"
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function search(Request $request)
    {
        $admins = User::where('user_type', 1)->where(function($query) use($request){
            $query->where('first_name', 'LIKE', "%" . $request->search . "%")->orWhere('last_name', 'LIKE', "%" . $request->search . "%");
        })->orderBy('last_name')->get();
        return response()->json([
            'data' => $admins
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/ticket/admin/set/{admin}",
     *     summary="Add or remove admin user from ticket admins",
     *     description="Toggle the admin status of a user for ticket management",
     *     tags={"TicketAdmin"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *     @OA\Parameter(
     *         name="admin",
     *         in="path",
     *         required=true,
     *         description="UserAdmin ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Operation successful",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="کاربر به لیست ادمین های تیکت اضافه شد")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized action",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="برای افزودن کاربر به لیست ادمین ها مجوز ندارید")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="خطایی غیرمنتظره در سرور رخ داده است. لطفا دوباره تلاش کنید")
     *         )
     *     )
     * )
     */
    public function set(User $admin)
    {
        try {
            if ($admin->user_type !== 1) {
                return response()->json([
                    'status' => false,
                    'message' => 'برای افزودن کاربر به لیست ادمین ها مجوز ندارید'
                ], 403);
            }
            $ticketAdmin = TicketAdmin::where('user_id', $admin->id)->first();
            if ($ticketAdmin) {
                $ticketAdmin->forceDelete();
                $message = 'کاربر از لیست ادمین های تیکت حذف شد';
            } else {
                TicketAdmin::create(['user_id' => $admin->id]);
                $message = 'کاربر به لیست ادمین های تیکت اضافه شد';
            }
            return response()->json([
                'status' => true,
                'message' => $message
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'خطایی غیرمنتظره در سرور رخ داده است. لطفا دوباره تلاش کنید'
            ], 500);
        }
    }
}
