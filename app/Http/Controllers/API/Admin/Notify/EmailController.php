<?php

namespace App\Http\Controllers\API\Admin\Notify;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Notify\EmailRequest;
use App\Http\Services\Message\Email\EmailService;
use App\Http\Services\Message\MessageService;
use App\Jobs\SendMailToUsers;
use App\Models\Notify\Email;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;

class EmailController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/admin/notify/email",
     *     summary="Retrieve list of emails",
     *     description="Retrieve list of all `Emails`",
     *     tags={"Email"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *     @OA\Response(
     *         response=200,
     *         description="A list of Emails",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="array",
     *                 @OA\Items(
     *                     ref="#/components/schemas/Email"
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function index()
    {
        $emails = Email::orderBy('created_at')->simplePaginate(15);
        return response()->json([
            'data' => $emails
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/notify/email/search",
     *     summary="Search among Emails by subject",
     *     description="This endpoint allows users to search for `Emails` by subject. The search is case-insensitive and returns results that contain the given keyword. The results are paginated for better performance",
     *    tags={"Email"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *   @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="type subject of Email which you're searching for",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="A list of Emails",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="array",
     *                 @OA\Items(
     *                     ref="#/components/schemas/Email"
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function search(Request $request)
    {
        $emails = Email::where('subject', 'LIKE', "%" . $request->search . "%")->orWhere('body', 'LIKE', "%" . $request->search . "%")->orderBy('subject')->get();
        return response()->json([
            'data' => $emails
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/notify/email/show/{email}",
     *     summary="Get details of a specific Email",
     *     description="Returns the `Email` details and provide details for edit method.",
     *     operationId="getEmailDetails",
     *     tags={"Email", "Email/Form"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="email",
     *         in="path",
     *         description="ID of the Email to fetch",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully fetched Email details for editing",
     *         @OA\JsonContent(ref="#/components/schemas/Email"),
     *     )
     * )
     */
    public function show(Email $email)
    {
        return response()->json([
            'data' => $email
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/admin/notify/email/store",
     *     summary="create new Email",
     *     description="this method creates a new `Email` and stores it.",
     *     tags={"Email"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *             @OA\Property(property="subject", type="string", pattern="^[a-zA-Z\u0600-\u06FF0-9\s\-\.\?]+$", description="This field can only contain Persian and English letters, Persian and English numbers, and symbols (?!.,؟!.،). Any other characters will result in a validation error.", example="حراج زمستانه"),
     *             @OA\Property(property="body", type="string", example="توضیح حراج زمستانه"),
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
     *         response=201,
     *         description="successful Email creation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="true"),
     *             @OA\Property(property="message", type="string", example="ایمیل با عنوان subject با موفقیت افزوده شد")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="invalid request",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="عنوان ایمیل الزامی است")
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
    public function store(EmailRequest $request)
    {
        try {
            date_default_timezone_set('Iran');
            $realTimestamp = substr($request['published_at'], 0, 10);
            $request['published_at'] = date("Y-m-d H:i:s", (int) $realTimestamp);
            $inputs = $request->all();
            $email = Email::create($inputs);
            return response()->json([
                'status' => true,
                'message' => 'ایمیل با عنوان ' . $email->subject . ' با موفقیت افزوده شد'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'خطایی غیرمنتظره در سرور رخ داده است. لطفا مجددا تلاش کنید'
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/admin/notify/email/status/{email}",
     *     summary="Change the status of a Email",
     *     description="This endpoint `toggles the status of a Email` (active/inactive)",
     *     operationId="updateEmailStatus",
     *     security={{"bearerAuth": {}}},
     *     tags={"Email"},
     *     @OA\Parameter(
     *         name="email",
     *         in="path",
     *         description="Email id to change the status",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     security={ {"bearerAuth": {}} },
     *     @OA\Response(
     *         response=200,
     *         description="Email status updated successfully",
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
    public function status(Email $email)
    {
        $email->status = $email->status == 1 ? 2 : 1;
        $result = $email->save();
        if ($result) {
            if ($email->status == 1) {
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
     *     path="/api/admin/notify/email/update/{email}",
     *     summary="Update an existing Email",
     *     description="this method Updates an existingw `Email` and saves it.",
     *     tags={"Email"},
     *     security={{"bearerAuth": {}}},
     *  @OA\Parameter(
     *         name="email",
     *         in="path",
     *         description="ID of the Email to update",
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
     *             @OA\Property(property="subject", type="string", pattern="^[a-zA-Z\u0600-\u06FF0-9\s\-\.\?]+$", description="This field can only contain Persian and English letters, Persian and English numbers, and symbols (?!.,؟!.،). Any other characters will result in a validation error.", example="حراج زمستانه"),
     *             @OA\Property(property="body", type="string", example="توضیح حراج زمستانه"),
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
     *         description="successful Email update",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="true"),
     *             @OA\Property(property="message", type="string", example="ایمیل با عنوان subject با موفقیت ویرایش شد")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="invalid request",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="عنوان ایمیل الزامی است")
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
    public function update(EmailRequest $request, Email $email)
    {
        try {
            date_default_timezone_set('Iran');
            $realTimestamp = substr($request['published_at'], 0, 10);
            $request['published_at'] = date("Y-m-d H:i:s", (int) $realTimestamp);
            $inputs = $request->all();
            $result = $email->update($inputs);
            return response()->json([
                'status' => true,
                'message' => 'ایمیل با عنوان ' . $email->subject . ' با موفقیت ویرایش شد'
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
     *     path="/api/admin/notify/email/destroy/{email}",
     *     summary="Delete a Email",
     *     description="This endpoint allows the user to `delete an existing Email`.",
     *     operationId="deleteEmail",
     *     tags={"Email"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="email",
     *         in="path",
     *         description="The ID of the Email to be deleted",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Email deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="ایمیل با عنوان Example با موفقیت حذف شد")
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
    public function destroy(Email $email)
    {
        $result = $email->delete();
        if ($result) {
            return response()->json([
                'status' => true,
                'message' => 'ایمیل با عنوان ' . $email->subject . ' با موفقیت حذف شد'
            ], 200);
        }
        return response()->json([
            'status' => false,
            'message' => 'عملیات با خطا مواجه شد. لطفا دوباره امتحان کنید'
        ], 500);
    }

/**
 * @OA\Get(
 *     path="/api/admin/notify/email/send-mail/{email}",
 *     summary="Send Email To Users",
 *     description="This method `sends email to users through a job`",
 *     operationId="sendMail",
 *     tags={"Email"},
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(
 *         name="email",
 *         in="path",
 *         description="ID of Email To Send",
 *         required=true,
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Email Sends Successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="ایمیل x با موفقیت ارسال شد")
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
    public function sendMail(Email $email)
    {
        try{
        SendMailToUsers::dispatch($email);
        return response()->json([
            'status' => true,
            'message' => 'ایمیل ' . $email->subject . ' با موفقیت ارسال شد'
        ], 200);
    }catch(Exception $e){
        return response()->json([
            'status' => false,
            'message' => 'خطایی غیرمنتظره در سرور رخ داده است. لطفا دوباره تلاش کنید'
        ], 500);
    }
    }
}
