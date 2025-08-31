<?php

namespace App\Http\Controllers\API\Admin\Market;

use App\Http\Controllers\Controller;
use App\Models\Market\Payment;
use Exception;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/admin/market/payment",
     *     summary="Retrieve a list of payments",
     *     description="This method retrieves a list of payments filtered by type and includes user information.",
     *     operationId="getAllPayments",
     *     tags={"Payment"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         required=false,
     *         description="Payment type: `online`, `offline`, `cash`",
     *         @OA\Schema(type="string", enum={"online", "offline", "cash"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully retrieved the list of payments",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="array",
     *                 @OA\Items(
     *                     ref="#/components/schemas/Payment"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="An unexpected error occurred on the server. Please try again.")
     *         )
     *     )
     * )
     */
    public function all(Request $request)
    {
        $payments = Payment::filterByType($request->query('type'))->with('paymentable:id,amount,pay_date','user:id,first_name,last_name')->orderBy('created_at', 'desc')->simplePaginate(15);
        $payments->getCollection()->each(function($item){
            $item->user->makeHidden(['status_value','activation_value','user_type_value']);
        });
        return response()->json([
            'data' => $payments
        ], 200);
    }

 /**
     * @OA\Get(
     *     path="/api/admin/market/payment/show/{payment}",
     *     summary="Get details of a specific Payment",
     *     description="Returns the `Payment` details and provide details for edit method.",
     *     operationId="getPaymentDetails",
     *     tags={"Payment", "Payment/Form"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="payment",
     *         in="path",
     *         description="ID of the Payment to fetch",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully fetched Payment details for editing",
     *    @OA\JsonContent(ref="#/components/schemas/Payment"),
     *     )
     * )
     */
    public function show(Payment $payment)
    {
        $payment->load('paymentable','user:id,first_name,last_name');
        $payment->user->makeHidden(['status_value','activation_value','user_type_value']);
        return response()->json([
            'data' => $payment
        ], 200);
    }

     /**
     * @OA\Get(
     *     path="/api/admin/market/payment/canceled/{payment}",
     *     summary="Cancel a Payment",
     *     description="Changes the Payment status to 'Canceled'.",
     *     tags={"Payment"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="payment",
     *         in="path",
     *         required=true,
     *         description="Payment ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Payment successfully canceled",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="تغییرات با موفقیت اعمال شد")
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
    public function canceled(Payment $payment)
    {
        try {
            $payment->status = 2;
            $payment->save();
            return response()->json([
                'status' => true,
                'message' => 'تغییرات با موفقیت اعمال شد'
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
     *     path="/api/admin/market/payment/returned/{payment}",
     *     summary="Return a Payment",
     *     description="Changes the Payment status to 'Returned'.",
     *     tags={"Payment"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="payment",
     *         in="path",
     *         required=true,
     *         description="Payment ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Payment successfully returned",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="تغییرات با موفقیت اعمال شد")
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
    public function returned(Payment $payment)
    {
        try {
            $payment->status = 3;
            $payment->save();
            return response()->json([
                'status' => true,
                'message' => 'تغییرات با موفقیت اعمال شد'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'خطایی غیرمنتظره در سرور رخ داده است. لطفا دوباره تلاش کنید'
            ], 500);
        }
    }
}
