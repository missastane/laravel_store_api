<?php

namespace App\Http\Controllers\API\Customer\Profile;

use App\Http\Controllers\Controller;
use App\Models\Market\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/orders",
     *     summary="Get user orders",
     *     description="Retrieve a list of orders for the authenticated user. Optionally, filter by order status.",
     *     tags={"Profile"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         required=false,
     *         description="Filter orders by status `0` means `unseen`, `1` means `processing`, `2` means `not approved`, `3` means `approved`, `4` means `canceled`, `5` means `returned`",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *        @OA\JsonContent(type="object",
     *              allOf={
     *            @OA\Schema(ref="#/components/schemas/Order"),
     *            @OA\Schema(
     *             @OA\Property(property="orderItems",type="array",description="OrderItems details",
     *                   @OA\Items(ref="#/components/schemas/OrderItem"),
     *                     )
     *              )
     *           }   
     *        )
     *    ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        if (isset($request->type)) {
            $orders = auth()->user()->orders()->where('order_status', $request->type)->with(['user:id,first_name,last_name','orderItems.color:id,color_name', 'orderItems.guarantee:id,name'])->orderBy('id', 'desc')->get();
        } else {
            $orders = auth()->user()->orders()->with(['user:id,first_name,last_name','orderItems.color:id,color_name', 'orderItems.guarantee:id,name'])->orderBy('id', 'desc')->get();
        }
        return response()->json([
            'data' => $orders
        ], 200);
    }


}
