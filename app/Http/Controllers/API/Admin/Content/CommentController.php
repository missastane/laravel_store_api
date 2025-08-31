<?php

namespace App\Http\Controllers\API\Admin\Content;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Content\CommentRequest;
use App\Models\Content\Comment;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/admin/content/comment",
     *     summary="Get post comments",
     *     description="Retrieves a paginated list of comments related to posts. Also updates the 'seen' status of unseen comments.",
     *     tags={"Comment","PostComment"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(ref="#/components/schemas/Comment")
     *             )
     *         )
     *     )
     * )
     */
    public function index()
    {
        $unSeenComments = Comment::where('seen', 2)->get();
        foreach ($unSeenComments as $unSeenComment) {
            $unSeenComment->seen = 1;
            $unSeenComment->save();
        }
        $comments = Comment::where('commentable_type', 'App\Models\Content\Post')->with('commentable:id,title', 'parent:id,body', 'user:id,first_name,last_name')->orderBy('created_at', 'desc')->simplePaginate(15);
        $comments->getCollection()->each(function ($item) {
            if (isset($item->parent)) {
                $item->parent->makeHidden(['status_value', 'approved_value', 'seen_value', 'commentable_type_value', 'parent']);
            }
            $item->user->makeHidden(['status_value', 'activation_value', 'user_type_value']);
            $item->commentable->makeHidden(['status_value','commentable_value']);
        });
        return response()->json([
            'data' => $comments
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/content/comment/search",
     *     summary="Searchs among PostComments by keyword in comment body",
     *     description="This endpoint allows users to search for `Comments` by keyword in comment body. The search is case-insensitive and returns results that contain the given keyword. The results are paginated for better performance",
     *     tags={"Comment","PostComment"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *   @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="type keyword which you're searching for in body of Comment",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="A list of Comments with their relations: commentable,parent and author",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="array",
     *                 @OA\Items(
     *                     ref="#/components/schemas/Comment"
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function search(Request $request)
    {
        $comments = Comment::where('commentable_type', 'App\Models\Content\Post')->where('body', 'LIKE', "%" . $request->search . "%")->with('commentable:id,title', 'parent:id,body', 'user:id,first_name,last_name')->simplePaginate(15);
        $comments->getCollection()->each(function ($item) {
            if (isset($item->parent)) {
                $item->parent->makeHidden(['status_value', 'approved_value', 'seen_value', 'commentable_type_value', 'parent']);
            }
            $item->user->makeHidden(['status_value', 'activation_value', 'user_type_value']);
            $item->commentable->makeHidden(['status_value']);
        });
        return response()->json([
            'data' => $comments
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/content/comment/show/{comment}",
     *     summary="Get details of a specific Comment",
     *     description="Returns the `PostComment` details along with commentable,parent and author and provide details for edit method.",
     *     operationId="getPostCommentDetails",
     *     tags={"Comment","PostComment","PostComment/Form"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="comment",
     *         in="path",
     *         description="ID of the Comment to fetch",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *   @OA\Response(
     *         response=200,
     *         description="Comment Details with its relations: commentable,parent and author",
     *         @OA\JsonContent(ref="#/components/schemas/Comment"),
     *     )
     * )
     */
    public function show(Comment $comment)
    {
        $comment->load('commentable:id,title', 'parent:id,body', 'user:id,first_name,last_name');
        if (isset($comment->parent)) {
            $comment->parent->makeHidden(['status_value', 'approved_value', 'seen_value', 'commentable_type_value', 'parent']);
        }
        $comment->user->makeHidden(['status_value', 'activation_value', 'user_type_value']);
        $comment->commentable->makeHidden(['status_value']);
        return response()->json([
            'data' => $comment
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/admin/content/comment/answer/{comment}",
     *     summary="Reply to a customer comment",
     *     description="This endpoint allows an admin to `reply to customer comments`. Only comments without replies can be answered.",
     *     operationId="answerPostComment",
     *     tags={"Comment","PostComment"},
     *     security={{"bearerAuth": {}}},
     *     
     *     @OA\Parameter(
     *         name="comment",
     *         in="path",
     *         required=true,
     *         description="The ID of the comment to be replied to",
     *         @OA\Schema(type="integer", example=10)
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *    @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *             required={"body"},
     *             @OA\Property(property="body", type="string", pattern="^[a-zA-Z\u0600-\u06FF0-9\s\-\.\?]+$", description="This field can only contain Persian and English letters, Persian and English numbers, and symboles (-.,?؟.،). Any other characters will result in a validation error", example="Thank you for your feedback, your order is being processed.")
     *         )
     * )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Reply successfully added",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="پاسخ نظر با موفقیت افزوده شد")
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
     *         description="The comment has already been replied to",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="امکان ارسال پاسخ به این نظر وجود ندارد")
     *         )
     *     )
     * )
     */
    public function answer(CommentRequest $request, Comment $comment)
    {
        if ($comment->parent_id == null) {
            try {
                $inputs = $request->all();
                $inputs['author_id'] = auth()->user()->id;
                $inputs['parent_id'] = $comment->id;
                $inputs['commentable_id'] = $comment->commentable_id;
                $inputs['commentable_type'] = $comment->commentable_type;
                $inputs['status'] = 1;
                $inputs['approved'] = 1;
                $answer_comment = Comment::create($inputs);
                return response()->json([
                    'status' => true,
                    'message' => 'پاسخ نظر با موفقیت افزوده شد'
                ], 201);
            } catch (Exception $e) {
                return response()->json([
                    'status' => false,
                    'message' => 'ارسال پاسخ با خطا مواجه شد. لطفا دوباره امتحان کنید'
                ], 500);
            }
        }
        return response()->json([
            'status' => false,
            'message' => 'امکان ارسال پاسخ به این نظر وجود ندارد'
        ], 422);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/content/comment/status/{comment}",
     *     summary="Change the status of a PostComment",
     *     description="This endpoint `toggles the status of a PostComment` (active/inactive)",
     *     operationId="updatePostCommentStatus",
     *     security={{"bearerAuth": {}}},
     *     tags={"PostComment","Comment"},
     *     @OA\Parameter(
     *         name="comment",
     *         in="path",
     *         description="PostComment id to change the status",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     security={ {"bearerAuth": {}} },
     *     @OA\Response(
     *         response=200,
     *         description="PostComment status updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="checked", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="وضعیت نظر x با موفقیت فعال شد")
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
    public function status(Comment $comment)
    {
        $comment->status = $comment->status == 1 ? 2 : 1;
        $result = $comment->save();
        if ($result) {
            if ($comment->status == 1) {
                return response()->json([
                    'status' => true,
                    'checked' => true,
                    'message' => 'نظر با موفقیت فعال شد'
                ]);
            } else {
                return response()->json([
                    'status' => true,
                    'checked' => false,
                    'message' => 'نظر با موفقیت غیرفعال شد'
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
     *     path="/api/admin/content/comment/approved/{comment}",
     *     summary="Change the PostComment's Approval State",
     *     description="This endpoint `toggles the PostComment's Approval State` (active/inactive)",
     *     operationId="updatePostCommentApproved",
     *     security={{"bearerAuth": {}}},
     *     tags={"PostComment","Comment"},
     *     @OA\Parameter(
     *         name="comment",
     *         in="path",
     *         description="PostComment id to change the approval state",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     security={ {"bearerAuth": {}} },
     *     @OA\Response(
     *         response=200,
     *         description="PostComment approval status updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="checked", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="وضعیت تأییدیه نظر x با موفقیت فعال شد")
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
    public function approved(Comment $comment)
    {
        $comment->approved = $comment->approved == 1 ? 2 : 1;
        $result = $comment->save();
        if ($result) {
            if ($comment->approved == 1) {
                return response()->json([
                    'status' => true,
                    'checked' => true,
                    'message' => 'نظر با موفقیت تأیید شد'
                ]);
            } else {
                return response()->json([
                    'status' => true,
                    'checked' => false,
                    'message' => 'تأییدیه نظر غیرغعال شد'
                ]);
            }
        } else {
            return response()->json([
                'status' => false,
                'message' => 'عملیات با خطا مواجه شد. دوباره امتحان کنید'
            ]);
        }
    }
}
