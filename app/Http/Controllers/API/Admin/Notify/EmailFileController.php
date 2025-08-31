<?php

namespace App\Http\Controllers\API\Admin\Notify;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Notify\EmailFileRequest;
use App\Http\Services\File\FileService;
use App\Models\Notify\Email;
use App\Models\Notify\EmailFile;
use Exception;
use Illuminate\Http\Request;
use Storage;

class EmailFileController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/admin/notify/email-file/{email}",
     *     summary="Retrieve list of special Email Files",
     *     description="Retrieve list of all `Files` which belongs to an email",
     *     tags={"EmailFile"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *  @OA\Parameter(
     *         name="email",
     *         in="path",
     *         description="ID of the Email to fetch its files",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="A list of EmailFiles",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="array",
     *                 @OA\Items(
     *                     ref="#/components/schemas/EmailFile"
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function index(Email $email)
    {
        $emailFiles = $email->files()->with('email:id,subject')->orderBy('file_type')->simplePaginate(15);
        $emailFiles->getCollection()->each(function ($item) {
            $item->email->makeHidden('status_value');
        });
        return response()->json([
            'data' => $emailFiles
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/notify/email-file/search/{email}",
     *     summary="Search email files by file type",
     *     description="Retrieve email files based on a search query for file type, filtered by a specific email ID.",
     *     operationId="searchEmailFiles",
     *     tags={"EmailFile"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *     @OA\Parameter(
     *         name="email",
     *         in="path",
     *         required=true,
     *         description="ID of the email",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         required=false,
     *         description="Search term to filter email files by file type",
     *         @OA\Schema(type="string", example="pdf")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully retrieved email files",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(ref="#/components/schemas/EmailFile")
     *             )
     *         )
     *     )
     * )
     */
    public function search(Request $request, Email $email)
    {
        $emailFiles = EmailFile::where('public_mail_id', $email->id)->where('file_type', 'LIKE', "%" . $request->search . "%")->with('email:id,subject')->orderBy('file_type')->simplePaginate(15);
        $emailFiles->getCollection()->each(function ($item) {
            $item->email->makeHidden('status_value');
        });

        return response()->json([
            'data' => $emailFiles
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/notify/email-file/status/{file}",
     *     summary="Change the status of a EmailFile",
     *     description="This endpoint `toggles the status of a EmailFile` (active/inactive)",
     *     operationId="updateEmailFileStatus",
     *     security={{"bearerAuth": {}}},
     *     tags={"EmailFile"},
     *     @OA\Parameter(
     *         name="file",
     *         in="path",
     *         description="EmailFile id to change the status",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     security={ {"bearerAuth": {}} },
     *     @OA\Response(
     *         response=200,
     *         description="EmailFile status updated successfully",
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
    public function status(EmailFile $file)
    {
        $file->status = $file->status == 1 ? 2 : 1;
        $result = $file->save();
        if ($result) {
            if ($file->status == 1) {
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
     * @OA\Get(
     *     path="/api/admin/notify/email-file/show/{file}",
     *     summary="Get details of a specific EmailFile",
     *     description="Returns the `EmailFile` details and provide details for edit method.",
     *     operationId="getEmailFileDetails",
     *     tags={"EmailFile", "EmailFile/Form"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="file",
     *         in="path",
     *         description="ID of the EmailFile to fetch",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully fetched EmailFile details for editing",
     *         @OA\JsonContent(ref="#/components/schemas/EmailFile"),
     *     )
     * )
     */
    public function show(EmailFile $file)
    {
        return response()->json([
            'data' => $file->load('email:id,subject')
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/admin/notify/email-file/{email}/store",
     *     summary="create new EmailFile",
     *     description="this method creates a new `EmailFile` and stores it.",
     *     tags={"EmailFile"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="email",
     *         in="path",
     *         description="Email id to change the status",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(
     *                     property="file",
     *                     type="string",
     *                     format="binary"
     *         ),
     *             @OA\Property(property="name", type="string", pattern="^[a-zA-Z\u0600-\u06FF0-9\s]+$", description="This field can only contain Persian and English letters, Persian and English numbers. Any other characters will result in a validation error.", example="فایل ایمیل"),
     *             @OA\Property(
     *                 property="status",
     *                 oneOf={
     *                     @OA\Schema(type="integer", example=1, description="1 = active"),
     *                     @OA\Schema(type="integer", example=2, description="2 = inactive")
     *                 }
     *             ),
     *             @OA\Property(
     *                 property="path",
     *                 description="file is private or public?",
     *                 oneOf={
     *                     @OA\Schema(type="integer", example=1, description="1 = private"),
     *                     @OA\Schema(type="integer", example=2, description="2 = public")
     *                 }
     *             ),
     *                       )
     *             )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="successful EmailFile creation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="true"),
     *             @OA\Property(property="message", type="string", example="فایل با موفقیت افزوده شد")
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
    public function store(EmailFileRequest $request, Email $email, FileService $fileService)
    {
        try {
            $inputs = $request->all();
            if ($request->hasFile('file')) {
                $fileService->setExclusiveDirectory('files' . DIRECTORY_SEPARATOR . 'email-files');

                $fileService->setFileSize($request->file('file'));
                $fileSize = $fileService->getFileSize();

                // upload file
                // if the file is very important use MoveToStorage() method to be safe
                if ($inputs['path'] == 1) {
                    $result = $fileService->moveToStorage($request->file('file'), $request->file('file')->getClientOriginalName());
                } else {
                    $result = $fileService->moveToPublic($request->file('file'), $request->file('file')->getClientOriginalName());
                }


                // after upload file we should define file format
                $fileFormat = $fileService->getFileFormat();
            }
            if ($result === false) {
                return response()->json([
                    'status' => false,
                    'message' => 'بارگذاری فایل با خطا مواجه شد'
                ], 422);

            }
            $inputs['original_name'] = $request->file('file')->getClientOriginalName();
            $inputs['public_mail_id'] = $email->id;
            $inputs['file_path'] = $result;
            $inputs['file_size'] = $fileSize;
            $inputs['file_type'] = $fileFormat;
            $file = EmailFile::create($inputs);
            return response()->json([
                'status' => true,
                'message' => 'فایل با موفقیت افزوده شد'
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'خطایی غیرمنتظره در سرور رخ داده است. لطفا دوباره تلاش کنید'
            ], 500);
        }
    }


    /**
     * @OA\Post(
     *     path="/api/admin/notify/email-file/update/{file}",
     *     summary="Update an existing EmailFile",
     *     description="this method updates an existing `EmailFile` and stores it",
     *     tags={"EmailFile"},
     *     security={{"bearerAuth": {}}},
     * @OA\Parameter(
     *         name="file",
     *         in="path",
     *         required=true,
     *         description="ID of the email file to fetch",
     *         @OA\Schema(type="integer", example=101)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(
     *                     property="file",
     *                     type="string",
     *                     format="binary"
     *         ),
     *             @OA\Property(property="name", type="string", pattern="^[a-zA-Z\u0600-\u06FF0-9\s]+$", description="This field can only contain Persian and English letters, Persian and English numbers. Any other characters will result in a validation error.", example="فایل ایمیل"),
     *             @OA\Property(
     *                 property="status",
     *                 oneOf={
     *                     @OA\Schema(type="integer", example=1, description="1 = active"),
     *                     @OA\Schema(type="integer", example=2, description="2 = inactive")
     *                 }
     *             ),
     *             @OA\Property(
     *                 property="path",
     *                 description="file is private or public?",
     *                 oneOf={
     *                     @OA\Schema(type="integer", example=1, description="1 = private"),
     *                     @OA\Schema(type="integer", example=2, description="2 = public")
     *                 }
     *             ),
     *             @OA\Property(property="_method", type="string", example="PUT"),
     * 
     *                       )
     *             )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="successful EmailFile creation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="true"),
     *             @OA\Property(property="message", type="string", example="فایل با موفقیت بروزرسانی شد")
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
     *   )
     * )
     */
    public function update(EmailFileRequest $request, EmailFile $file, FileService $fileService)
    {
        try {
            $inputs = $request->all();
            if ($request->hasFile('file')) {
                if (!empty($file->file_path)) {
                    if (file_exists(storage_path($file->file_path))) {
                        $fileService->deleteFile($file->file_path, true);
                    }
                    if (file_exists(public_path($file->file_path))) {
                        $fileService->deleteFile($file->file_path);
                    }
                }
                $fileService->setExclusiveDirectory('files' . DIRECTORY_SEPARATOR . 'email-files');

                $fileService->setFileSize($request->file('file'));
                $fileSize = $fileService->getFileSize();
                // upload file
                // if the file is very important use MoveToStorage() method to be safe

                if ($inputs['path'] == 1) {

                    $result = $fileService->moveToStorage($request->file('file'), $request->file('file')->getClientOriginalName());
                } else {
                    $result = $fileService->moveToPublic($request->file('file'), $request->file('file')->getClientOriginalName());
                }
                // after upload file we should define file format
                $fileFormat = $fileService->getFileFormat();
                if ($result === false) {
                    return response()->json([
                        'status' => false,
                        'message' => 'بارگذاری فایل با خطا مواجه شد'
                    ], 422);

                }
                $inputs['file_path'] = $result;
                $inputs['file_size'] = $fileSize;
                $inputs['file_type'] = $fileFormat;
                $inputs['original_name'] = $request->file('file')->getClientOriginalName();
            }

            $emailFile = $file->update($inputs);
            return response()->json([
                'status' => true,
                'message' => 'فایل با موفقیت بروزرسانی شد'
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
     *     path="/api/admin/notify/email-file/destroy/{file}",
     *     summary="Delete a EmailFile",
     *     description="This endpoint allows the user to `delete an existing EmailFile`.",
     *     operationId="deleteEmailFile",
     *     tags={"EmailFile"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="file",
     *         in="path",
     *         description="The ID of the EmailFile to be deleted",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="EmailFile deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="فایل با موفقیت حذف شد")
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
    public function destroy(EmailFile $file)
    {
        try {
            $result = $file->delete();
            return response()->json([
                'status' => true,
                'message' => 'فایل با موفقیت حذف شد'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'عملیات با خطا مواجه شد. لطفا دوباره امتحان کنید'
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/admin/notify/email-file/open-file/{file}",
     *     summary="Open or download an email file",
     *     description="Retrieves a file from storage or public directory and returns it for download. If the file does not exist, returns a 404 error.",
     *     operationId="openFile",
     *     tags={"Email Files"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="file",
     *         in="path",
     *         required=true,
     *         description="ID of the email file",
     *         @OA\Schema(type="integer", example=101)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="File successfully retrieved",
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/octet-stream"
     *             )
     *         }
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="File not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="فایل وجود ندارد")
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
    public function openFile(EmailFile $file)
    {

        if (file_exists(storage_path($file->file_path))) {
            return response()->file(
                storage_path($file->file_path),
                ['Content-Disposition' => 'attachment; filename="' . $file->name ?? 'file' . '"']
            );
        } elseif (file_exists(public_path($file->file_path))) {
            return response()->file(
                $file->file_path,
                ['Content-Disposition' => 'attachment; filename="' . $file->name ?? 'file' . '"']
            );
        } else {
            return response()->json([
                'status' => false,
                'message' => 'فایل وجود ندارد'
            ], 404);
        }

    }
}
