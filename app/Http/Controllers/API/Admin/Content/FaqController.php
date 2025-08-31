<?php

namespace App\Http\Controllers\API\Admin\Content;
use App\Http\Requests\Admin\Content\FaqRequest;
use App\Models\Content\Faq;
use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class FaqController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/admin/content/faq",
     *     summary="Retrieve list of Faqs",
     *     description="Retrieve list of all `Faqs`",
     *     tags={"Faq"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *     @OA\Response(
     *         response=200,
     *         description="A list of Faqs with their Tags",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="array",
     *                 @OA\Items(
     *                     ref="#/components/schemas/Faq"
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function index()
    {
        $faqs = Faq::with('tags:name,id')->orderBy('created_at', 'desc')->simplePaginate(15);
        $faqs->getCollection()->each(function ($item) {
            $item->tags->makeHidden(['pivot']);
        });
        return response()->json([
            'data' => $faqs
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/content/faq/search",
     *     summary="Searchs among Faqs by name",
     *     description="This endpoint allows users to search for `Faqs` by name. The search is case-insensitive and returns results that contain the given keyword. The results are paginated for better performance",
     *     tags={"Faq"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *   @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="type name of Faq which you're searching for",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="A list of Faqs with their Tags",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="array",
     *                 @OA\Items(
     *                     ref="#/components/schemas/Faq"
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function search(Request $request)
    {
        $faqs = Faq::where('question', 'LIKE', "%" . $request->search . "%")->orWhere('answer', 'LIKE', "%" . $request->search . "%")->with('tags:name,id')->orderBy('question')->simplePaginate(15);
        $faqs->getCollection()->each(function ($item) {
            $item->tags->makeHidden(['pivot']);
        });
        return response()->json([
            'data' => $faqs
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/content/faq/show/{faq}",
     *     summary="Get details of a specific Faq",
     *     description="Returns the `Faq` details along with tags and provide details for edit method. also `productCategories` in this method is specially provided details for edit form",
     *     operationId="getFaqyDetails",
     *     tags={"Faq", "Faq/Form"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="faq",
     *         in="path",
     *         description="ID of the Faq to fetch",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully fetched Faq details with tags for editing",
     *         @OA\JsonContent(ref="#/components/schemas/Faq"),
     *   )
     * )
     */
    public function show(Faq $faq)
    {
        $faq->load('tags:name,id');
        $faq->tags->makeHidden(['pivot']);
        return response()->json([
            'data' => $faq
        ], 200);
    }

     /**
     * @OA\Post(
     *     path="/api/admin/content/faq/store",
     *     summary="create new Faq",
     *     description="this method creates a new `Faq` and stores its related tags.",
     *     tags={"Faq"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                
     *             @OA\Property(property="question", type="string",maximum=255,minimum=2, example="چگونه میتوانم در سایت ثبت نام کنم؟"),
     *             @OA\Property(property="answer", type="string", maximum=300 , minimum=5, example="به سادگی تمام"),
     *             @OA\Property(
     *                 property="status",
     *                 oneOf={
     *                     @OA\Schema(type="integer", example=1, description="1 = active"),
     *                     @OA\Schema(type="integer", example=2, description="2 = inactive")
     *                 }
     *             ),
     *             @OA\Property(
     *                 property="tags[]",
     *                 type="array",
     *                 @OA\Items(type="string",pattern="^[a-zA-Z\u0600-\u06FF0-9\s\-\.\?]+$", example="لوازم ورزشی"),
     *              description="This field can only contain Persian and English letters, Persian and English numbers, hyphens (-),question marks (?), and periods (.). Any other characters will result in a validation error.",
     *             )
     *                       ),
     *            encoding={
     *                 "tags[]": {
     *                     "style": "form",
     *                     "explode": true
     *                 }
     *             }
     *             )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="successful Faq and tags creation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="true"),
     *             @OA\Property(property="message", type="string", example="سؤال با موفقیت افزوده شد")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="invalid request",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="سؤال الزامی است")
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
    public function store(FaqRequest $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            $faq = Faq::create($inputs);
            if ($request->has('tags')) {
                foreach ($request->tags as $tagName) {
                    $tag = Tag::firstOrCreate(['name' => $tagName]);
                    $faq->tags()->attach($tag);
                }
            }
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'سؤال با موفقیت افزوده شد'
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'خطایی غیرمنتظره در سرور رخ داده است. لطفا دوباره تلاش کنید'
            ], 500);
        }
    }

 /**
     * @OA\Get(
     *     path="/api/admin/content/faq/status/{faq}",
     *     summary="Change the status of a Faq",
     *     description="This endpoint `toggles the status of a Faq` (active/inactive)",
     *     operationId="updateFaqStatus",
     *     security={{"bearerAuth": {}}},
     *     tags={"Faq"},
     *     @OA\Parameter(
     *         name="faq",
     *         in="path",
     *         description="Faq id to change the status",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     security={ {"bearerAuth": {}} },
     *     @OA\Response(
     *         response=200,
     *         description="Faq status updated successfully",
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
    public function status(Faq $faq)
    {
        $faq->status = $faq->status == 1 ? 2 : 1;
        $result = $faq->save();
        if ($result) {
            if ($faq->status == 1) {
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
     *     path="/api/admin/content/faq/update/{faq}",
     *     summary="Update an existing Faq",
     *     description="this method update an existing `Faq` and stores its related tags.",
     *     tags={"Faq"},
     *     security={{"bearerAuth": {}}},
     *    @OA\Parameter(
     *         name="faq",
     *         in="path",
     *         description="Faq id to fetch",
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
     *             @OA\Property(property="question", type="string",maximum=255,minimum=2, example="آیا متد استور سؤالات متداول درست کار میکنه؟"),
     *             @OA\Property(property="answer", type="string", maximum=300 , minimum=5, example="نمیدونم. باید تست کرد"),
     *             @OA\Property(
     *                 property="status",
     *                 oneOf={
     *                     @OA\Schema(type="integer", example=1, description="1 = active"),
     *                     @OA\Schema(type="integer", example=2, description="2 = inactive")
     *                 }
     *             ),
     *             @OA\Property(
     *                 property="tags",
     *                 type="array",
     *                 @OA\Items(type="string",pattern="^[a-zA-Z\u0600-\u06FF0-9\s\-\.\?]+$", example="لوازم ورزشی"),
     *              description="This field can only contain Persian and English letters, Persian and English numbers, hyphens (-),question marks (?), and periods (.). Any other characters will result in a validation error.",
     *             )
     *                       ),
     *            encoding={
     *                 "tags[]": {
     *                     "style": "form",
     *                     "explode": true
     *                 }
     *             }
     *             )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="successful Faq and tags update",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="true"),
     *             @OA\Property(property="message", type="string", example="سؤال با موفقیت بروزرسانی شد")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="invalid request",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="سؤال الزامی است")
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
    public function update(FaqRequest $request, Faq $faq)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();

            $faq->update($inputs);
            if ($request->has('tags')) {
                $tagIds = [];
                foreach ($request->tags as $tagName) {
                    $tag = Tag::firstOrCreate(['name' => $tagName]);
                     array_push($tagIds,$tag->id);
                }

                $faq->tags()->sync($tagIds);
            }
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'سؤال با موفقیت بروزرسانی شد'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'خطایی غیرمنتظره در سرور رخ داده است. لطفا دوباره تلاش کنید'
            ], 500);
        }
    }

     /**
     * @OA\Delete(
     *     path="/api/admin/content/faq/destroy/{faq}",
     *     summary="Delete a Faq",
     *     description="This endpoint allows the user to `delete an existing Faq`.",
     *     operationId="deleteFaq",
     *     tags={"Faq"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="faq",
     *         in="path",
     *         description="The ID of the Faq to be deleted",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Faq deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="سؤال با موفقیت حذف شد")
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
    public function destroy(Faq $faq)
    {
        $result = $faq->delete();
        if ($result) {
            return response()->json([
                'status' => true,
                'message' => 'سؤال موفقیت حذف شد'
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'عملیات با خطا مواجه شد. لطفا دوباره امتحان کنید'
            ], 500);
        }
    }
}
