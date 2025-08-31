<?php

namespace App\Http\Controllers\API\Admin\Market;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Market\OrderFilterRequest;
use App\Models\Market\Order;
use Exception;
use Illuminate\Foundation\Http\Middleware\Concerns\ExcludesPaths;
use Illuminate\Http\Request;
use Response;

class OrderController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/admin/market/order",
     *     summary="Get a list of orders with optional filtering",
     *     description="Retrieve a paginated `list of orders with optional filtering based on order status, payment status, and delivery status`.",
     *     tags={"Order"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="order_status",
     *         in="query",
     *         description="Filter by order status. Possible values: unseen, processing, not-approved, approved, canceled, returned",
     *         required=false,
     *         @OA\Schema(type="string", example="processing,canceled")
     *     ),
     *     @OA\Parameter(
     *         name="payment_status",
     *         in="query",
     *         description="Filter by payment status. Possible values: unpaid, paid, canceled, returned",
     *         required=false,
     *         @OA\Schema(type="string", example="paid,unpaid")
     *     ),
     *     @OA\Parameter(
     *         name="delivery_status",
     *         in="query",
     *         description="Filter by delivery status. Possible values: not_sending, sending, sent, delivered",
     *         required=false,
     *         @OA\Schema(type="string", example="sending,delivered")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Order"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="باشد یا چند مورد به وسیله کاما از هم جدا شده باشد unpaid, paid, canceled, returned فیلد وضعیت پرداخت  باید یکی از موارد'")
     *         )
     *     )
     * )
     */
    public function all(OrderFilterRequest $request)
    {
        $orders = Order::filter($request->all())->with(['user:id,first_name,last_name'])->simplePaginate(15);
        $orders->getCollection()->each(function ($item) {
            $item->user->makeHidden(['status_value', 'activation_value', 'user_type_value']);
        });
        return response()->json([
            'data' => $orders
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/market/order/show/{order}",
     *     summary="Get order details",
     *     description="Retrieves the details of a specific order along with its related data.",
     *     tags={"Order"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="order",
     *         in="path",
     *         required=true,
     *         description="The unique identifier of the order",
     *         @OA\Schema(type="integer", example=24)
     *     ),
     *     @OA\Parameter(
     *         name="include",
     *         in="query",
     *         required=false,
     *         description="Comma-separated list of additional relationships to load. Available values: address, payment, delivery, copan, commonDiscount, orderItems,orderItems.product, orderItems.amazingSale",
     *         @OA\Schema(type="string", example="payment,delivery,orderItems.product,orderItems.amazingSale")
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
     *    )
     * )
     */
    public function show(Request $request, Order $order)
    {
        $defaultRelations = ['user:id,first_name,last_name', 'orderItems.color:id,color_name', 'orderItems.guarantee:id,name'];
        if (!empty($request->query('include'))) {
            $extraRelations = explode(',', $request->query('include', ''));
            $order = $order->load(array_merge($defaultRelations, $extraRelations));
        } else {
            $order = $order->load($defaultRelations);
        }
        $order->user->makeHidden(['status_value', 'activation_value', 'user_type_value']);
        return response()->json([
            'data' => $order
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/market/order/change-send-status/{order}",
     *     summary="Change the delivery status of an order",
     *     description="`Updates the delivery status of a given order and progresses it to the next stage` in every request.",
     *     tags={"Order"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="order",
     *         in="path",
     *         required=true,
     *         description="The ID of the order to update",
     *         @OA\Schema(type="integer", example=5)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Delivery status updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="وضعیت ارسال به ارسال شد تغییر کرد")
     *         )
     *     ),
     *  @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="خطایی غیرمنتظره در سرور رخ داده است. لطفا دوباره تلاش کنید")
     *         )
     *     )
     * )
     */
    public function changeSendStatus(Order $order)
    {
        try {
            switch ($order->delivery_status) {
                case 0:
                    $order->delivery_status = 1;
                    break;
                case 1:
                    $order->delivery_status = 2;
                    break;
                case 2:
                    $order->delivery_status = 3;
                    break;
                default:
                    $order->delivery_status = 0;
            }
            $order->save();
            return response()->json([
                'status' => true,
                'message' => 'وضعیت ارسال به ' . $order->delivery_status_value . ' تغییر کرد'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'خطایی غیرمنتظره در سرور رخ داده است. لطفا دوباره تلاش کنید'
            ], 500);
        }

    }

    /**
     * @OA\Get(
     *     path="/api/admin/market/order/change-order-status/{order}",
     *     summary="Change Order Status",
     *     description="`Updates the order status to the next stage` in every request.",
     *     tags={"Order"},
     *     security={{"bearerAuth":{}}},
     * 
     *     @OA\Parameter(
     *         name="order",
     *         in="path",
     *         required=true,
     *         description="Order ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     * 
     *     @OA\Response(
     *         response=200,
     *         description="Order status successfully updated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="وضعیت سفارش به ارسال شده تغییر کرد")
     *         )
     *     ),
     * 
     *     @OA\Response(
     *         response=500,
     *         description="Server error occurred",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="خطایی غیرمنتظره در سرور رخ داده است. لطفا دوباره تلاش کنید")
     *         )
     *     )
     * )
     */
    public function changeOrderStatus(Order $order)
    {
        try {
            switch ($order->order_status) {
                case 1:
                    $order->order_status = 2;
                    break;
                case 2:
                    $order->order_status = 3;
                    break;
                case 3:
                    $order->order_status = 4;
                    break;
                case 4:
                    $order->order_status = 5;
                    break;

                default:
                    $order->order_status = 1;
            }
            $order->save();
            return response()->json([
                'status' => true,
                'message' => 'وضعیت سفارش به ' . $order->order_status_value . ' تغییر کرد'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'خطایی غیرمنتظره در سرور رخ داده است. لطفا دوباره تلاش کنید'
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/admin/market/order/cancel-order/{order}",
     *     summary="Cancel an Order",
     *     description="Changes the order status to 'Canceled'.",
     *     tags={"Order"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="order",
     *         in="path",
     *         required=true,
     *         description="Order ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order successfully canceled",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="سفارش باطل شد")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error occurred",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="خطایی غیرمنتظره در سرور رخ داده است. لطفا دوباره تلاش کنید")
     *         )
     *     )
     * )
     */
    public function cancelOrder(Order $order)
    {
        try {
            $order->order_status = 4;
            $order->save();
            return response()->json([
                'status' => true,
                'message' => 'سفارش باطل شد'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'خطایی غیرمنتظره در سرور رخ داده است. لطفا دوباره تلاش کنید'
            ], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/admin/market/order/tracking-post-code/{order}",
     *     summary="Add or update postal tracking code",
     *     description="Allows the user to add or update the postal tracking code for shipped orders.",
     *     tags={"Order"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="order",
     *         in="path",
     *         required=true,
     *         description="The ID of the order to update.",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *             @OA\Property(property="postal_tracking_code", type="string", example="44417589650235698754"),
     *                       )
     *             )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Postal tracking code successfully updated.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="کد رهگیری با موفقیت ثبت شد. در صورت تمایل می توانید ویرایش کنید")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Cannot update postal tracking code for orders that are not shipped.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="امکان ثبت یا ویرایش کد رهگیری تنها برای سفارش های ارسال شده وجود دارد")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Unexpected server error.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="خطای پایگاه داده، لطفا دوباره تلاش کنید")
     *         )
     *     )
     * )
     */
    public function postalTrackingCode(Order $order, Request $request)
    {



        $request->validate([
            'postal_tracking_code' => 'required|digits:20',
            // 'g-recaptcha-response' => 'recaptcha',

        ]);

        try {
            if ($order->delivery_status === 2 || $order->delivery_status === 3) {

                $inputs['postal_tracking_code'] = $request->postal_tracking_code;

                $order->update(['postal_tracking_code' => $inputs['postal_tracking_code']]);
                return response()->json([
                    'status' => true,
                    'message' => 'کد رهگیری با موفقیت ثبت شد. در صورت تمایل می توانید ویرایش کنید'
                ], 200);
            } else {

                return response()->json([
                    'status' => false,
                    'message' => 'امکان ثبت یا ویرایش کد رهگیری تنها برای سفارش های ارسال شده وجود دارد'
                ], 422);
            }
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'خطایی غیرمنتظره در سرور رخ داده است. لطفا دوباره تلاش کنید'
            ], 500);
        }
    }

}
