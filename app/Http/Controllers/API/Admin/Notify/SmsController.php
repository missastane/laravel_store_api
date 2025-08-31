<?php

namespace App\Http\Controllers\API\Admin\Notify;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Notify\SMSRequest;
use App\Http\Services\Message\MessageService;
use App\Http\Services\Message\SMS\SmsService;
use App\Jobs\SendSMSToUsers;
use App\Models\Notify\SMS;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class SmsController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/admin/notify/sms",
     *     summary="Retrieve list of SMS",
     *     description="Retrieve list of all `SMS`",
     *     tags={"SMS"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *     @OA\Response(
     *         response=200,
     *         description="A list of SMS",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="array",
     *                 @OA\Items(
     *                     ref="#/components/schemas/SMS"
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function index()
    {
        $msgs = SMS::orderBy('created_at', 'desc')->simplePaginate(15);
        return response()->json([
            'data' => $msgs
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/notify/sms/search",
     *     summary="Searchs among SMS by title",
     *     description="This endpoint allows users to search for `SMS` by title. The search is case-insensitive and returns results that contain the given keyword. The results are paginated for better performance",
     *     tags={"SMS"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *   @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="type title of SMS which you're searching for",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="A list of SMS",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="array",
     *                 @OA\Items(
     *                     ref="#/components/schemas/SMS"
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function search(Request $request)
    {
        $msgs = SMS::where('title', 'LIKE', "%" . $request->search . "%")->orWhere('body', 'LIKE', "%" . $request->search . "%")->orderBy('title')->get();
        return response()->json([
            'data' => $msgs
        ], 200);
    }


    /**
     * @OA\Get(
     *     path="/api/admin/notify/sms/show/{sms}",
     *     summary="Get details of a specific SMS",
     *     description="Returns the `SMS` details along with tags and provide details for edit method.",
     *     operationId="getSMSDetails",
     *     tags={"SMS", "SMS/Form"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="sms",
     *         in="path",
     *         description="ID of the SMS to fetch",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64",example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully fetched SMS details with tags for editing",
     *         @OA\JsonContent(ref="#/components/schemas/SMS"),
     *     )
     *  )
     */
    public function show(SMS $sms)
    {
        return response()->json([
            'data' => $sms
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/admin/notify/sms/store",
     *     summary="create new SMS",
     *     description="this method creates a new `SMS` and stores it",
     *     tags={"SMS"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                
     *             @OA\Property(property="title", type="string", pattern="^[a-zA-Z\u0600-\u06FF0-9\s\-\.\?\!]+$", description="This field can only contain Persian and English letters, Persian and English numbers, and symbols (?!.،). Any other characters will result in a validation error.", example="حراج بهاره"),
     *             @OA\Property(property="body", type="string", pattern="^[a-zA-Z\u0600-\u06FF0-9\s\-\.\?\!]+$", description="This field can only contain Persian and English letters, Persian and English numbers, and symbols (?!.،). Any other characters will result in a validation error.", example="توضیح حراج بهاره"),
     *             @OA\Property(property="published_at", type="integer", example=1677030400),
     *             @OA\Property(
     *                 property="status",
     *                 oneOf={
     *                     @OA\Schema(type="integer", example=1, description="1 = active"),
     *                     @OA\Schema(type="integer", example=2, description="2 = inactive")
     *                 }
     *             ),
     *            
     *            
     *                       )
     *             )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="successful SMS creation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="true"),
     *             @OA\Property(property="message", type="string", example="پیامک با عنوان x با موفقیت افزوده شد")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="invalid request",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="عنوان الزامی است")
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
    public function store(SMSRequest $request)
    {
        try {
            date_default_timezone_set('Iran');
            $realTimestamp = substr($request['published_at'], 0, 10);
            $request['published_at'] = date("Y-m-d H:i:s", (int) $realTimestamp);
            $inputs = $request->all();
            $sms = SMS::create($inputs);
            return response()->json([
                'status' => true,
                'message' => 'پیامک با عنوان ' . $sms->title . ' با موفقیت افزوده شد'
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'خطایی غیرمنتظره در سرور رخ داده است. لطفا مجددا تلاش کنید'
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/admin/notify/sms/status/{sms}",
     *     summary="Change the status of a SMS",
     *     description="This endpoint `toggles the status of a SMS` (active/inactive)",
     *     operationId="updateSMSStatus",
     *     security={{"bearerAuth": {}}},
     *     tags={"SMS"},
     *     @OA\Parameter(
     *         name="sms",
     *         in="path",
     *         description="SMS id to change the status",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     security={ {"bearerAuth": {}} },
     *     @OA\Response(
     *         response=200,
     *         description="SMS status updated successfully",
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
    public function status(SMS $sms)
    {
        $sms->status = $sms->status == 1 ? 2 : 1;
        $result = $sms->save();
        if ($result) {
            if ($sms->status == 1) {
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
     *     path="/api/admin/notify/sms/update/{sms}",
     *     summary="update an existing SMS",
     *     description="this method updates an existing `SMS` and stores it",
     *     tags={"SMS"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="sms",
     *         in="path",
     *         description="SMS id to fetch record",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                
     *             @OA\Property(property="title", type="string", pattern="^[a-zA-Z\u0600-\u06FF0-9\s\-\.\?\!]+$", description="This field can only contain Persian and English letters, Persian and English numbers, and symbols (?!.،). Any other characters will result in a validation error.", example="حراج بهاره"),
     *             @OA\Property(property="body", type="string", pattern="^[a-zA-Z\u0600-\u06FF0-9\s\-\.\?\!]+$", description="This field can only contain Persian and English letters, Persian and English numbers, and symbols (?!.،). Any other characters will result in a validation error.", example="توضیح حراج بهاره"),
     *             @OA\Property(property="published_at", type="integer", example=1677030400),
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
     *         description="successful SMS update",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="true"),
     *             @OA\Property(property="message", type="string", example="پیامک با عنوان x با موفقیت بروزرسانی شد")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="invalid request",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="عنوان الزامی است")
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
    public function update(SMSRequest $request, SMS $sms)
    {
        try {
            date_default_timezone_set('Iran');
            $realTimestamp = substr($request['published_at'], 0, 10);
            $request['published_at'] = date("Y-m-d H:i:s", (int) $realTimestamp);
            $inputs = $request->all();
            $result = $sms->update($inputs);
            return response()->json([
                'status' => true,
                'message' => 'پیامک با عنوان ' . $sms->title . ' با موفقیت بروزرسانی شد'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'خطایی غیرمنتظره در سرور رخ داده است. لطفا مجددا تلاش کنید'
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/admin/notify/sms/destroy/{sms}",
     *     summary="Delete a SMS",
     *     description="This endpoint allows the user to `delete an existing SMS`.",
     *     operationId="deleteSMS",
     *     tags={"SMS"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="sms",
     *         in="path",
     *         description="The ID of the SMS to be deleted",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="SMS deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="پیامک با عنوان x با موفقیت حذف شد")
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
    public function destroy(SMS $sms)
    {
        try {
            $sms->delete();
            return response()->json([
                'status' => true,
                'message' => 'پیامک با عنوان ' . $sms->title . ' با موفقیت حذف شد'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'خطایی غیرمنتظره در سرور رخ داده است. لطفا مجددا امتحان کنید'
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/admin/notify/sms/send-sms/{sms}",
     *     summary="Send SMS To Users",
     *     description="This method `sends sms to users through a job`",
     *     operationId="sendSMS",
     *     tags={"SMS"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="sms",
     *         in="path",
     *         description="ID of SMS To Send",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="SMS Sends Successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="ارسال پیامک با موفقیت انجام شد")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not Found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="مسیر مورد نظر یافت نشد")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="خطایی غیرمنتظره در سرور رخ داده است. لطفا دوباره تلاش کنید")
     *         )
     *     )
     * )
     */
    public function sendSms(SMS $sms)
    {
        try {
            SendSMSToUsers::dispatch($sms);
            return response()->json([
                'status' => true,
                'message' => 'ارسال پیامک با موفقیت انجام شد'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'خطایی غیرمنتظره در سرور رخ داده است. لطفا دوباره تلاش کنید'
            ], 500);
        }
    }
}
