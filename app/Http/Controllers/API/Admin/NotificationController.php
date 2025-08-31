<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Notification;

class NotificationController extends Controller
{
    /**
     * @OA\Patch(
     *     path="/api/admin/notification/read-all",
     *     summary="Mark all notifications as read",
     *     description="This endpoint marks all unread notifications as read.",
     *     operationId="readAll",
     *     tags={"Notification"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Notifications marked as read",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="همه اعلان ها به عنوان خوانده شده علامت دار شدند"),
     *             @OA\Property(property="read_notifications_count", type="integer", example=5)
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="عملیات با خطا مواجه شد. لطفا دوباره امتحان کنید")
     *         )
     *     )
     * )
     */
    public function readAll()
    {
        try {
            $notifications = Notification::where('read_at', null)->get();

            foreach ($notifications as $notification) {
                $notification->update(['read_at' => date('Y-m-d H:i:s')]);
            }
            return response()->json([
                'status' => true,
                'message' => 'همه اعلان ها به عنوان خوانده شده علامت دار شدند',
                'read_notifications_count' => $notifications->count(),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'عملیات با خطا مواجه شد. لطفا دوباره امتحان کنید'
            ], 500);
        }
    }
}
