<?php

namespace App\Http\Controllers\API\Customer\Profile;

use App\Http\Controllers\API\Admin\Ticket\TicketController;
use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\Profile\CreateTicketRequest;
use App\Http\Requests\Customer\ProFile\TicketStoreRequest;
use App\Http\Services\File\FileService;
use App\Models\Ticket\Ticket;
use App\Models\Ticket\TicketCategory;
use App\Models\Ticket\TicketFile;
use App\Models\Ticket\TicketPriority;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProfileTicketController extends Controller
{

  /**
   * @OA\Get(
   *     path="/api/my-tickets",
   *     summary="Get a paginated list of authenticated user tickets to show in profile",
   *     description="Retrieve a list of `authenticated user tickets` with filtering options.",
   *     operationId="getTicketsForProfile",
   *     tags={"Profile","ProfileTicket"},
   *     security={
   *         {"bearerAuth": {}}
   *     },
   *     @OA\Parameter(
   *         name="status",
   *         in="query",
   *         description="Filter tickets by status",
   *         required=false,
   *         @OA\Schema(type="string",enum={"تیکت باز", "تیکت بسته"}, example="تیکت باز")
   *     ),
   *     @OA\Parameter(
   *         name="seen",
   *         in="query",
   *         description="Filter tickets by seen status ( Seen, Unseen)",
   *         required=false,
   *         @OA\Schema(type="string",enum={"تیکت جدید", "تیکت دیده شده"}, example="تیکت دیده شده")
   *     ),
   * 
   *     @OA\Response(
   *         response=200,
   *         description="List of tickets",
   *         @OA\JsonContent(
   *             type="object",
   *             @OA\Property(
   *                 property="data",
   *                 type="array",
   *                 @OA\Items(ref="#/components/schemas/Ticket")
   *             )
   *         )
   *     )
   * )
   */
  public function index()
  {
    $tickets = auth()->user()->tickets()->filter([
      'status' => request()->query('status'),
      'seen' => request()->query('seen'),
    ])->with('user:id,first_name,last_name', 'admin.user:id,first_name,last_name', 'category:id,name', 'priority:id,name', 'parent')->orderBy('created_at', 'desc')->whereNull('ticket_id')->simplePaginate(15);
    $tickets->getCollection()->each(function ($item) {
      $item->user->makeHidden(['status_value', 'user_type_value', 'activation_value']);
      $item->admin->makeHidden('user_id', 'created_at', 'updated_at', 'deleted_at');
      $item->admin->user->makeHidden(['status_value', 'user_type_value', 'activation_value']);
      $item->category->makeHidden(['status_value']);
      $item->priority->makeHidden(['status_value']);
    });
    return response()->json([
      'data' => $tickets
    ], 200);
  }

  /**
   * @OA\Get(
   *     path="/api/ticket-details/{ticket}",
   *     summary="Get details of a specific Ticket",
   *     description="Returns the `Ticket` details anlog its relations(`tiketFile, children`) and provide details for showing in profile ticket details",
   *     operationId="getTicketDetailsFoeProfile",
   *     tags={"Profile", "TicketProfile/Form","ProfileTicket"},
   *     security={{"bearerAuth": {}}},
   *     @OA\Parameter(
   *         name="ticket",
   *         in="path",
   *         description="ID of the Ticket to fetch",
   *         required=true,
   *         @OA\Schema(type="integer", format="int64")
   *     ),
   *     @OA\Response(
   *         response=200,
   *         description="Successfully fetched Ticket details for editing",
   *         @OA\JsonContent(type="object", 
   *             allOf={
   *               @OA\Schema(ref="#/components/schemas/Ticket"),
   *               @OA\Schema(
   *                   @OA\Property(property="ticketFile",type="object",
   *                       @OA\Property(property="id", type="integer", example=5),
   *                       @OA\Property(property="file_path", type="string", example="files\ticket-files\2025-01-01\16.jpg")
   *                     )
   *                 ),
   *               @OA\Schema(
   *                   @OA\Property(property="children",type="array",
   *                      @OA\Items(
   *                       @OA\Property(property="id", type="integer", example=5),
   *                       @OA\Property(property="subject", type="string", example="مشکل در رابطه با خرید محصول"),
   *                       @OA\Property(property="description", type="string", example="توضیح مشکل خرید محصول"),
   *                       @OA\Property(property="author_value", type="string", description="Ticket Author status: 'admin' if 1, 'customer' if 2", example="ادمین"),
   *                     )
   *                   )
   *                 )
   *             }
   *         )
   *      )
   *   )
   * )
   */
  public function show(Ticket $ticket)
  {
    $adminTicketController = new TicketController();
    return $adminTicketController->show($ticket);
  }

  /**
   * @OA\Post(
   *     path="/api/ticket-answer/{ticket}",
   *     summary="Reply to a Ticket",
   *     description="This endpoint allows an customer to `reply to his/her or admin Tickets`. Also this endpoint allows the customer to attach a file to his ticket",
   *     operationId="answerTicketForCustomer",
   *     tags={"ProfileTicket","Profile"},
   *     security={{"bearerAuth": {}}},
   *     
   *     @OA\Parameter(
   *         name="ticket",
   *         in="path",
   *         required=true,
   *         description="The ID of the Ticket to be replied to",
   *         @OA\Schema(type="integer", example=10)
   *     ),
   *
   *     @OA\RequestBody(
   *         required=true,
   *         @OA\MediaType(
   *             mediaType="multipart/form-data",
   *             @OA\Schema(
   *             required={"description"},
   *             @OA\Property(property="description", type="string", pattern="^[a-zA-Z\u0600-\u06FF0-9\s\-\.\?\!\,،]+$", description="This field can only contain Persian and English letters, Persian and English numbers, and symboles (-.,?؟.،!). Any other characters will result in a validation error", example="Thank you for your feedback, your order is being processed."),
   *             @OA\Property(property="file", type="string", format="binary"),
   *            )
   *       )
   *   ),
   *     @OA\Response(
   *         response=201,
   *         description="Reply successfully added",
   *         @OA\JsonContent(
   *             @OA\Property(property="status", type="boolean", example=true),
   *             @OA\Property(property="message", type="string", example="پاسخ شما با موفقیت ثبت شد")
   *         )
   *     ),
   *
   *     @OA\Response(
   *         response=500,
   *         description="Failed to send reply",
   *         @OA\JsonContent(
   *             @OA\Property(property="status", type="boolean", example=false),
   *             @OA\Property(property="message", type="string", example="ارسال پاسخ با خطا مواجه شد. لطفا دوباره امتحان کنید")
   *         )
   *     ),
   *
   *     @OA\Response(
   *         response=422,
   *         description="The discription is required",
   *         @OA\JsonContent(
   *             @OA\Property(property="status", type="boolean", example=false),
   *             @OA\Property(property="message", type="string", example="توضیحات الزامی است")
   *         )
   *     )
   * )
   */
  public function ticketAnswer(TicketStoreRequest $request, Ticket $ticket, FileService $fileService)
  {
    try {
      DB::beginTransaction();
      $inputs = $request->all();
      $inputs['subject'] = $ticket['subject'];
      $inputs['description'] = $request->description;
      $inputs['seen'] = 2;
      $inputs['ticket_id'] = $ticket['id'];
      $inputs['reference_id'] = $ticket['reference_id'];
      $inputs['category_id'] = $ticket['category_id'];
      $inputs['priority_id'] = $ticket['priority_id'];
      $inputs['user_id'] = Auth::user()->id;
      $answeredTicket = Ticket::create($inputs);
      if ($request->hasFile('file')) {
        $fileService->setExclusiveDirectory('files' . DIRECTORY_SEPARATOR . 'ticket-files');

        $fileService->setFileSize($request->file('file'));
        $fileSize = $fileService->getFileSize();

        // upload file
        $upload = $fileService->moveToPublic($request->file('file'), $request->file('file')->getClientOriginalName());

        // after upload file we should define file format
        $fileFormat = $fileService->getFileFormat();
        $input['file_path'] = $upload;
        $input['file_size'] = $fileSize;
        $input['file_type'] = $fileFormat;
        $input['ticket_id'] = $answeredTicket->id;
        $input['user_id'] = auth()->user()->id;
        $file = TicketFile::create($input);
      }

      DB::commit();
      return response()->json([
        'status' => true,
        'message' => 'پاسخ شما با موفقیت ثبت شد'
      ], 201);
    } catch (\Exception $e) {
      DB::rollBack();
      return response()->json([
        'status' => false,
        'message' => 'عملیات با خطا مواجه شد. لطفا دوباره امتحان کنید',
        'error' => $e->getMessage()
      ], 500);
    }
  }

  public function options()
  {
    $periorities = TicketPriority::select('id', 'name')->get();
    $categories = TicketCategory::select('id', 'name')->get();
    return response()->json([
      'data' => [
        'ticketPriorities' => $periorities,
        'ticketCategories' => $categories,
      ]
    ], 200);
  }

  /**
   * @OA\Post(
   *     path="/api/ticket-store",
   *     summary="create new Ticket",
   *     description="This method allows the customer to create a new `Ticket` along file and stores them.",
   *     tags={"Profile","ProfileTicket"},
   *     security={{"bearerAuth": {}}},
   *     @OA\RequestBody(
   *         required=true,
   *         @OA\MediaType(
   *             mediaType="multipart/form-data",
   *             @OA\Schema(
   *                 type="object",
   *             @OA\Property(property="subject", type="string", pattern="^[a-zA-Z\u0600-\u06FF0-9\s\-\.\?\!\,،]+$", description="This field can only contain Persian and English letters, Persian and English numbers, and symbols (-.,?؟.،). Any other characters will result in a validation error.", example="مشکل خرید محصول"),
   *             @OA\Property(property="description", type="string", pattern="^[a-zA-Z\u0600-\u06FF0-9\s\-\.\?\!\,،]+$", description="This field can only contain Persian and English letters, Persian and English numbers, and symboles (-.,?؟.،!). Any other characters will result in a validation error", example="Thank you for your feedback, your order is being processed."),
   *             @OA\Property(property="category_id",description="Category ID. This must be provided when creating or updating the Ticket.", type="integer", example=5),
   *             @OA\Property(property="priority_id",description="Priority ID. This must be provided when creating or updating the Ticket.", type="integer", example=5),
   *             @OA\Property(property="file", type="string", format="binary"),
   *
   *                       )
   *             )
   *     ),
   *     @OA\Response(
   *         response=201,
   *         description="successful Ticket creation",
   *         @OA\JsonContent(
   *             @OA\Property(property="status", type="bool", example="true"),
   *             @OA\Property(property="message", type="string", example="تیکت شما با موفقیت ثبت شد")
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
  public function ticketStore(CreateTicketRequest $request, FileService $fileService)
  {
    try {
      DB::beginTransaction();
      // ticket
      $inputs = $request->all();
      $inputs['reference_id'] = null;
      $inputs['user_id'] = Auth::user()->id;
      $ticket = Ticket::create($inputs);

      if ($request->hasFile('file')) {

        $fileService->setExclusiveDirectory('files' . DIRECTORY_SEPARATOR . 'ticket-files');

        $fileService->setFileSize($request->file('file'));
        $fileSize = $fileService->getFileSize();

        // upload file
        $upload = $fileService->moveToPublic($request->file('file'), $request->file('file')->getClientOriginalName());

        // after upload file we should define file format
        $fileFormat = $fileService->getFileFormat();
        $input['file_path'] = $upload;
        $input['file_size'] = $fileSize;
        $input['file_type'] = $fileFormat;
        $input['ticket_id'] = $ticket->id;
        $input['user_id'] = auth()->user()->id;
        $file = TicketFile::create($input);
      }
      DB::commit();
      return response()->json([
        'status' => true,
        'message' => 'تیکت شما با موفقیت ثبت شد'
      ], 201);
    } catch (\Exception $e) {
      DB::rollBack();
      return response()->json([
        'status' => false,
        'message' => 'عملیات با خطا مواجه شد. لطفا دوباره امتحان کنید',
        'error' => $e->getMessage()
      ], 500);
    }
  }


}
