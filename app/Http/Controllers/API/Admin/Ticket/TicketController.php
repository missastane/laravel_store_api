<?php

namespace App\Http\Controllers\API\Admin\Ticket;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Ticket\TicketRequest;
use App\Http\Services\File\FileService;
use App\Models\Ticket\Ticket;
use App\Models\Ticket\TicketFile;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TicketController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/admin/ticket",
     *     summary="Get a paginated list of tickets",
     *     description="Retrieve a list of `customer tickets` with filtering options and update the seen status if needed.",
     *     operationId="getTickets",
     *     tags={"Ticket"},
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
    public function index(Request $request)
    {
        $tickets = Ticket::filter([
            'status' => $request->query('status'),
            'seen' => $request->query('seen'),
        ])->with('user:id,first_name,last_name', 'admin.user:id,first_name,last_name', 'category:id,name', 'priority:id,name', 'parent')->orderBy('created_at', 'desc')->whereNull('ticket_id')->simplePaginate(15);

        $ticketIdsToUpdate = $tickets->where('seen', 2)->pluck('id');
        $tickets->getCollection()->each(function ($item) {
            $item->user->makeHidden(['status_value', 'user_type_value', 'activation_value']);
            $item->admin->makeHidden('user_id', 'created_at', 'updated_at', 'deleted_at');
            $item->admin->user->makeHidden(['status_value', 'user_type_value', 'activation_value']);
            $item->category->makeHidden(['status_value']);
            $item->priority->makeHidden(['status_value']);
        });
        if ($ticketIdsToUpdate->isNotEmpty()) {
            Ticket::whereIn('id', $ticketIdsToUpdate)->update(['seen' => 1]);
        }
        return response()->json([
            'data' => $tickets
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/ticket/search",
     *     summary="Searchs among Tickets by subject",
     *     description="This endpoint allows users to search for `Tickets` by subject. The search is case-insensitive and returns results that contain the given keyword. The results are paginated for better performance",
     *     tags={"Ticket"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *   @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="type subject of Ticket which you're searching for",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="A list of Tickets",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="array",
     *                 @OA\Items(
     *                     ref="#/components/schemas/Ticket"
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function search(Request $request)
    {
        $tickets = Ticket::whereNull('parent_id')->where('subject', 'LIKE', "%" . $request->search . "%")->with('user:id,first_name,last_name', 'admin.user:id,first_name,last_name', 'category:id,name', 'priority:id,name', 'parent:id,subject')->orderBy('subject')->simplePaginate(15);
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
     *     path="/api/admin/ticket/show/{ticket}",
     *     summary="Get details of a specific Ticket",
     *     description="Returns the `Ticket` details anlog its relations(`tiketFile, children`) and provide details for edit method.",
     *     operationId="getTicketDetails",
     *     tags={"Ticket", "Ticket/Form"},
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
        $ticket->load('user:id,first_name,last_name', 'admin.user:id,first_name,last_name', 'category:id,name', 'priority:name,id', 'parent:id,subject', 'ticketFile', 'children');
        $ticket->user->makeHidden(['status_value', 'user_type_value', 'activation_value']);
        $ticket->admin->makeHidden('user_id', 'created_at', 'updated_at', 'deleted_at');
        $ticket->admin->user->makeHidden(['status_value', 'user_type_value', 'activation_value']);
        $ticket->category->makeHidden(['status_value']);
        $ticket->priority->makeHidden(['status_value']);
        $ticket->children->makeHidden(['created_at', 'updated_at', 'deleted_at', 'children', 'seen_value', 'status_value']);
        return response()->json([
            'data' => $ticket
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/ticket/change/{ticket}",
     *     summary="Change the status of a Ticket",
     *     description="This endpoint `toggles the status of a Ticket` (open the Ticket/close the Ticket)",
     *     operationId="updateTicketStatus",
     *     security={{"bearerAuth": {}}},
     *     tags={"Ticket"},
     *     @OA\Parameter(
     *         name="ticket",
     *         in="path",
     *         description="Ticket id to change the status",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     security={ {"bearerAuth": {}} },
     *     @OA\Response(
     *         response=200,
     *         description="Ticket status updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="checked", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="تیکت x با موفقیت بسته شد")
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
    public function change(Ticket $ticket)
    {
        $ticket->status = $ticket->status == 1 ? 2 : 1;
        $result = $ticket->save();
        if ($result) {
            if ($ticket->status == 1) {
                return response()->json([
                    'status' => true,
                    'checked' => true,
                    'message' => 'تیکت ' . $ticket->subject . ' با موفقیت بسته شد'
                ]);
            } else {
                return response()->json([
                    'status' => true,
                    'checked' => false,
                    'message' => 'تیکت ' . $ticket->subject . ' با موفقیت باز شد'
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
     * @OA\Post(
     *     path="/api/admin/ticket/answer/{ticket}",
     *     summary="Reply to a customer Ticket",
     *     description="This endpoint allows an admin to `reply to customer Tickets`. Also this endpoint allows the admin to attach a file to his ticket",
     *     operationId="answerTicket",
     *     tags={"Ticket"},
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
    public function answer(TicketRequest $request, Ticket $ticket, FileService $fileService)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            $inputs['subject'] = $ticket['subject'];
            $inputs['description'] = $request->description;
            $inputs['author'] = 1; //if author is customer == 2,if is admin == 1
            $inputs['seen'] = 1;
            $inputs['ticket_id'] = $ticket['id'];
            $inputs['reference_id'] = auth()->user()->ticketAdmin->id;
            $ticket->update(['reference_id' => $inputs['reference_id']]);
            $inputs['category_id'] = $ticket['category_id'];
            $inputs['priority_id'] = $ticket['priority_id'];
            $inputs['user_id'] = $ticket->user_id;

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

        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'عملیات با خطا مواجه شد. لطفا دوباره امتحان کنید',
                'error' => $e->getMessage()
            ], 500);
        }
    }


}
