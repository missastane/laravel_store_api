<?php

namespace App\Http\Controllers\API\Customer\SalesProcess;

use App\Http\Controllers\Controller;
use App\Http\Services\Payment\PaymentService;
use App\Models\Market\CartItem;
use App\Models\Market\CashPayment;
use App\Models\Market\Copan;
use App\Models\Market\Delivery;
use App\Models\Market\OfflinePayment;
use App\Models\Market\OnlinePayment;
use App\Models\Market\Order;
use App\Models\Market\OrderItem;
use App\Models\Market\Payment;
use App\Traits\UserCartTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class PaymentController extends Controller
{
    use UserCartTrait;
    public function createOrderItem($orderId)
    {
        $user = $this->getAuthUser();
        $cartItems = $this->getCartItems();
        foreach ($cartItems as $cartItem) {
            OrderItem::create([
                'order_id' => $orderId,
                'product_id' => $cartItem->product_id,
                'product_object' => $cartItem->product,
                'amazing_sale_id' => $cartItem->product->activeAmazingSale()->id ?? null,
                'amazing_sale_object' => $cartItem->product->activeAmazingSale() ?? null,
                'amazing_sale_discount_amount' => empty($cartItem->product->activeAmazingSale()) ? 0 : ($cartItem->product->activeAmazingSale()->percentage / 100) * $cartItem->cartItemProductPrice(),
                'number' => $cartItem->number,
                'final_product_price' => empty($cartItem->product->activeAmazingSale()) ? $cartItem->cartItemProductPrice() : $cartItem->cartItemProductPrice() - ($cartItem->product->activeAmazingSale()->percentage / 100) * $cartItem->cartItemProductPrice(),
                'final_total_price' => (empty($cartItem->product->activeAmazingSale()) ? $cartItem->cartItemProductPrice() : $cartItem->cartItemProductPrice() - ($cartItem->product->activeAmazingSale()->percentage / 100) * $cartItem->cartItemProductPrice()) * $cartItem->number,
                'color_id' => $cartItem->color_id,
                'guarantee_id' => $cartItem->guarantee_id
            ]);
            $cartItem->delete();
        }
    }
    /**
     * @OA\Get(
     *     path="/api/payment/options",
     *     summary="Retrieve cart options and order details",
     *     description="Fetches the cart items and the current pending order for the authenticated user.",
     *     tags={"Payment Process"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successfully retrieved cart options and order details",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="cartItems", type="array", @OA\Items(type="object")),
     *                 @OA\Property(property="order", type="object", nullable=true)
     *             )
     *         ),
     *     )
     * )
     */
    public function options()
    {
        $user = $this->getAuthUser();
        $cartItems = $this->getCartItems();
        $order = Order::where('user_id', auth()->user()->id)->where('order_status', 0)->first();
        return response()->json([
            'data' => [
                'cartItems' => $cartItems,
                'order' => $order
            ]
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/copan-discount",
     *     summary="Apply a discount coupon",
     *     description="Validates and applies a discount coupon to the user's pending order.",
     *     tags={"Payment Process"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"copan_id"},
     *             @OA\Property(property="copan_id", type="string", description="The discount coupon code")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Coupon successfully applied",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean"),
     *             @OA\Property(property="message", type="string", example="کد تخفیف با موفقیت اعمال شد")
     *         ),
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="User not authorized to use this coupon",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean"),
     *             @OA\Property(property="message", type="string",example="شما مجوز استفاده از این کد تخفیف را ندارید")
     *         ),
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Invalid or expired coupon",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean"),
     *             @OA\Property(property="message", type="string", example="کد تخفیف نامعتبر بوده یا قبلا استفاده شده است")
     *         ),
     *     )
     * )
     */
    public function copanDisount(Request $request)
    {
        $request->validate([
            'copan_id' => 'required|exists:copans,code'
        ]);
        try {
            DB::beginTransaction();
            $copan = Copan::where([['code', $request->copan_id], ['status', 1], ['end_date', '>', now()], ['start_date', '<', now()]])->first();
            if ($copan === null) {
                return response()->json([
                    'status' => false,
                    'message' => 'کد تخفیف نامعتبر بوده یا قبلا استفاده شده است'
                ], 422);
            }

            if ($copan->user_id != null) {
                $copan = Copan::where([['code', $request->copan_id], ['status', 1], ['end_date', '>', now()], ['start_date', '<', now()], ['user_id', auth()->user()->id]])->first();
                if ($copan == null) {
                    return response()->json([
                        'status' => false,
                        'message' => 'شما مجوز استفاده از این کد تخفیف را ندارید'
                    ], 403);
                }
            }
            $order = Order::where('user_id', auth()->user()->id)->where('order_status', 0)->where('copan_id', null)->first();
            if ($order === null) {
                return response()->json([
                    'status' => false,
                    'message' => 'کد تخفیف نامعتبر بوده یا قبلا استفاده شده است'
                ], 422);
            }

            if ($copan->amount_type == 2) {
                $copanDiscountAmount = $order->order_final_amount * ($copan->amount / 100);
                if ($copanDiscountAmount > $copan->discount_ceiling) {
                    $copanDiscountAmount = $copan->discount_ceiling;
                }

            } else {
                $copanDiscountAmount = $copan->amount;
                if ($copanDiscountAmount > $copan->discount_ceiling) {
                    $copanDiscountAmount = $copan->discount_ceiling;
                }
            }

            $delivery = Delivery::find($order->delivery_id);
            $order->update([
                'copan_id' => $copan->id,
                'order_copan_discount_amount' => $copanDiscountAmount,
                'copan_object' => $copan,
                'order_total_products_discount_amount' => $order->order_total_products_discount_amount + $copanDiscountAmount,
                'order_final_amount' => ($order->order_final_amount + $delivery->amount) - $copanDiscountAmount
            ]);
            if ($copan->type == 1) {
                $copan->update(['status' => 2]);
            }
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'کد تخفیف با موفقیت اعمال شد',
                'meta' => [
                    'next_step' => 'redirect_to_peyment_submit'
                ]
            ], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'خطایی غیرمنتظره در سرور رخ داده است. لطفا دوباره تلاش کنید'
            ], 500);
        }

    }

    /**
     * @OA\Post(
     *     path="/api/payment-submit",
     *     summary="Submit a payment",
     *     description="Handles the payment process based on the selected payment method.",
     *     tags={"Payment Process"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"payment_type"},
     *             @OA\Property(property="payment_type", type="string", description="The payment method (1: Online, 2: Offline, 3: Cash)"),
     *             @OA\Property(property="cash_receiver", type="string", nullable=true, description="The person receiving the cash (if applicable)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Payment successfully submitted",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean"),
     *             @OA\Property(property="message", type="string", example="کاربر عزیز، سفارش شما با موفقیت ثبت شد")
     *         ),
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Unexpected server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean"),
     *             @OA\Property(property="message", type="string",example="خطایی غیرمنتظره در سرور رخ داده است. لطفا دوباره تلاش کنید")
     *         ),
     *     )
     * )
     */
    public function paymentSubmit(Request $request, PaymentService $paymentService)
    {
        $request->validate([
            'payment_type' => 'required',
            'cash_receiver' => 'nullable',
        ]);

        try {
            DB::beginTransaction();
            $user = $this->getAuthUser();
            $order = Order::where('user_id', $user->id)->where('order_status', 0)->first();

            $cashReceiver = null;
            switch ($request->payment_type) {
                case '1':
                    $targetModel = OnlinePayment::class;
                    $type = 0;
                    break;
                case '2':
                    $targetModel = OfflinePayment::class;
                    $type = 1;
                    break;
                case '3':
                    $targetModel = CashPayment::class;
                    $type = 2;
                    $cashReceiver = $request->cash_receiver ?? null;
                    break;
                default:
                    return response()->json([
                        'status' => false,
                        'message' => 'خطا'
                    ]);
            }


            // \Log::info($order);
            $paymented = $targetModel::create([
                'amount' => $order->order_final_amount,
                'user_id' => $user->id,
                'pay_date' => now(),
                'gateway' => 'زرین پال',
                'transaction_id' => null,
                'status' => 1,
                'cash_receiver' => $request->cash_receiver
            ]);


            $payment = Payment::create([
                'amount' => $order->order_final_amount,
                'user_id' => $user->id,
                'pay_date' => now(),
                'type' => $type,
                'paymentable_type' => $targetModel,
                'paymentable_id' => $paymented->id,
                'status' => 1
            ]);

            \Log::info($payment);
           
            if ($request->payment_type == 1) {
                $paymentService->zarinpal($order->order_final_amount, $order, $paymented);
            }


            $order->update([
                'order_status' => 3,
                'payment_status' => 1,
                'payment_object' => $paymented,
                'payment_type' => $type,
                'payment_id' => $paymented->id
            ]);


            $this->createOrderItem($order->id);
            DB::commit();

            return response()->json([
                'status' => true,
                'message' => "$user->fullName" . ' عزیز، سفارش شما با موفقیت ثبت شد'
            ], 200);
        } catch (Exception $e) {
            DB::rollBack();
            \Log::error($e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'خطایی غیرمنتظره در سرور رخ داده است. لطفا دوباره تلاش کنید',
                'err' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/payment-callback/{order}/{onlinePayment}",
     *     summary="Handle payment callback",
     *     description="Verifies the payment transaction and updates the order status accordingly.",
     *     tags={"Payment Process"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="order",
     *         in="path",
     *         required=true,
     *         description="Order ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="onlinePayment",
     *         in="path",
     *         required=true,
     *         description="OnlinePayment ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *  @OA\Parameter(
     *         name="Authority",
     *         in="query",
     *         required=true,
     *         description="Authority ID",
     *         @OA\Schema(type="string", example="S000000000000000000000000000000de5yj")
     *     ),
     *  @OA\Parameter(
     *         name="Status",
     *         in="query",
     *         required=true,
     *         description="Status",
     *         @OA\Schema(type="string", example="OK")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Payment successfully verified",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean"),
     *             @OA\Property(property="message", type="string", example="کاربر عزیز، پرداخت سفارش شما با موفقیت انجام و در اسرع وقت پیگیری خواهد شد")
     *         ),
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Payment verification failed",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean"),
     *             @OA\Property(property="message", type="string", example="کاربر عزیز، ثبت سفارش شما با خطا مواجه شد. لطفا مجددا تلاش کنید")
     *         ),
     *     )
     * )
     */
    public function paymentCallback(Order $order, OnlinePayment $onlinePayment, PaymentService $paymentService, Request $request)
    {
        $user = $this->getAuthUser();
        $amount = $onlinePayment->amount;
        $result = $paymentService->zarinpalVerify($amount, $onlinePayment);

        $this->createOrderItem($order->id);
        if ($result['success']) {

            $order->update([
                'order_status' => 3,
                'payment_status' => 1,
                'payment_object' => json_encode($onlinePayment),
                'payment_type' => 0,
                'payment_id' => $onlinePayment->id
            ]);

            return response()->json([
                'status' => true,
                'message' => "$user->fullName" . ' عزیز، پرداخت سفارش شما با موفقیت انجام و در اسرع وقت پیگیری خواهد شد'
            ], 200);
        } else {
            $order->update([
                'order_status' => 2,
                'payment_status' => 0
            ]);
            return response()->json([
                'status' => false,
                'message' => "$user->fullName" . ' عزیز، ثبت سفارش شما با خطا مواجه شد. لطفا مجددا تلاش کنید',

            ]);
        }
    }
}
