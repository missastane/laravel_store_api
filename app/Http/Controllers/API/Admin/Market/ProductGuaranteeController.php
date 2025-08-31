<?php

namespace App\Http\Controllers\API\Admin\Market;

use App\Http\Controllers\Controller;
use App\Models\Market\Guarantee;
use App\Models\Market\Product;
use Exception;
use Illuminate\Http\Request;
use Session;

class ProductGuaranteeController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/admin/market/product/guarantee/{product}",
     *     summary="Retrieve list of `Guarantees` with their product",
     *     description="Retrieve list of all `Guarantees` with their product",
     *  tags={"Guarantee"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *  @OA\Parameter(
     *         name="product",
     *         in="path",
     *         description="product id to fetch its guarantees",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="A list of `Guarantees` with their product",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="array",
     *                 @OA\Items(
     *                     ref="#/components/schemas/Guarantee"
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function index(Product $product)
    {
        $guarantees = $product->guarantees()->with('product:name,id')->simplePaginate(15);
        $guarantees->getCollection()->each(function ($item) {
            $item->product->makeHidden(['status_value', 'related_products_value', 'marketable_value']);
        });
        return response()->json([
            'data' => $guarantees
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/market/product/guarantee/search/{product}",
     *     summary="Searches among Guarantees by name.",
     *     description="This endpoint allows users to search for `Guarantee` by name. The search is case-insensitive and returns results that contain the given keyword. The results are paginated for better performance",
     *  tags={"Guarantee"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *  @OA\Parameter(
     *         name="product",
     *         in="path",
     *         description="Id of product that you want search fo its guarantee",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *   @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="type name of Guarantee which you're searching for",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="A list of Guarantee with their product",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="array",
     *                 @OA\Items(
     *                     ref="#/components/schemas/Guarantee"
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function search(Request $request, Product $product)
    {
        $guarantees = Guarantee::where('product_id', $product->id)->where('name', 'LIKE', "%" . $request->search . "%")->with('product:name,id')->orderBy('name')->simplePaginate(15);
        $guarantees->getCollection()->each(function ($item) {
            $item->product->makeHidden(['status_value', 'related_products_value', 'marketable_value']);
        });
        return response()->json([
            'data' => $guarantees
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/market/product/guarantee/show/{guarantee}",
     *     summary="Returns Guarantee details for edit form",
     *     description="Returns `Guarantee` details with its product for edit form",
     *  tags={"Guarantee","Guarantee/Form"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *  @OA\Parameter(
     *         name="guarantee",
     *         in="path",
     *         description="Id of guarantee that you want showing",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="A Guarantee with its product",
     *        @OA\JsonContent(ref="#/components/schemas/Guarantee"),
     *     )
     * )
     */
    public function show(Guarantee $guarantee)
    {
        $guarantee->load('product:name,id');
        $guarantee->product->makeHidden(['status_value', 'related_products_value', 'marketable_value']);
        return response()->json([
            'data' => $guarantee
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/admin/market/product/guarantee/{product}/store",
     *     summary="create new value for a Guarantee",
     *     description="this method creates a new `Guarantee` for the product and stores it.",
     *     tags={"Guarantee"},
     *     security={{"bearerAuth": {}}},
     *   @OA\Parameter(
     *         name="product",
     *         in="path",
     *         description="ID of the product to fetch",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *             @OA\Property(property="name", type="string", pattern="^[a-zA-Z\u0600-\u06FF0-9\s\-\.\?]+$", description="This field can only contain Persian and English letters, Persian and English numbers, and hyphens (-). Any other characters will result in a validation error.", example="2"),
     *             @OA\Property(property="price_increase", type="float", example=60000),
     *             )
     * )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="successful Guarantee creation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="true"),
     *             @OA\Property(property="message", type="string", example="گارانتی با موفقیت افزوده شد"),
     *            
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
    public function store(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|max:120|min:2|regex:/^[ا-یa-zA-Z0-9\-۰-۹ء-ي.,، ]+$/u',
            'price_increase' => 'required|numeric',
            // 'g-recaptcha-response' => 'recaptcha',
        ]);
        try {
            $inputs = $request->all();
            $inputs['product_id'] = $product->id;
            $guarantee = Guarantee::create($inputs);

            return response()->json([
                'status' => true,
                'message' => ' گارانتی با موفقیت افزوده شد'
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'خطای غیرمنتطره ای در سرور رخ داده است. لطفا دوباره تلاش کنید'
            ], 500);
        }
    }


    /**
     * @OA\Get(
     *     path="/api/admin/market/product/guarantee/status/{guarantee}",
     *     summary="Change the status of a guarantee",
     *     description="This endpoint `toggles the status of a Guarantee` (active/inactive)",
     *     operationId="updateGuaranteeStatus",
     *     security={{"bearerAuth": {}}},
     *     tags={"Guarantee"},
     *     @OA\Parameter(
     *         name="guarantee",
     *         in="path",
     *         description="Guarantee id to change the status",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     security={ {"bearerAuth": {}} },
     *     @OA\Response(
     *         response=200,
     *         description="Guarantee status updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="checked", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="وضعیت گارانتی با موفقیت فعال شد")
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
    public function status(Guarantee $guarantee)
    {
        $guarantee->status = $guarantee->status == 1 ? 2 : 1;
        $result = $guarantee->save();
        if ($result) {
            if ($guarantee->status == 1) {
                return response()->json([
                    'status' => true,
                    'checked' => true,
                    'message' => 'وضعیت ' . $guarantee->name . ' با موفقیت فعال شد'
                ]);
            } else {
                return response()->json([
                    'status' => true,
                    'checked' => false,
                    'message' => 'وضعیت ' . $guarantee->name . ' با موفقیت غیرفعال شد'
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
     *     path="/api/admin/market/product/guarantee/update/{guarantee}",
     *     summary="update an existing Guarantee",
     *     description="this method updates an existing `Guarantee` for the product and stores it.",
     *     tags={"Guarantee"},
     *     security={{"bearerAuth": {}}},
     *   @OA\Parameter(
     *         name="guarantee",
     *         in="path",
     *         description="ID of the guarantee to fetch",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *             @OA\Property(property="name", type="string", pattern="^[a-zA-Z\u0600-\u06FF0-9\s\-\.\?]+$", description="This field can only contain Persian and English letters, Persian and English numbers, and hyphens (-). Any other characters will result in a validation error.", example="گارانتی سازگار"),
     *             @OA\Property(property="price_increase", type="float", example=60000),
     *             )
     * )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="successful Guarantee update",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="true"),
     *             @OA\Property(property="message", type="string", example="گارانتی با موفقیت بروزرسانی شد"),
     *            
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
    public function update(Request $request, Guarantee $guarantee)
    {
        $validated = $request->validate([
            'name' => 'required|max:120|min:2|regex:/^[ا-یa-zA-Z0-9\-۰-۹ء-ي.,، ]+$/u',
            'price_increase' => 'required|numeric',
            // 'g-recaptcha-response' => 'recaptcha',
        ]);
        try {
            $inputs = $request->all();
            $inputs['product_id'] = $guarantee->product_id;
            $update = $guarantee->update($inputs);

            return response()->json([
                'status' => true,
                'message' => 'گارانتی با موفقیت بروزرسانی شد'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'خطایی غیرمنتظره در سرور رخ داده است. لطفا دوباره امتحان کنید'
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/admin/market/product/guarantee/destroy/{guarantee}",
     *     summary="Delete a Guarantee",
     *     description="This endpoint allows the user to `delete an existing guarantee`.",
     *     operationId="deleteGuarantee",
     *     tags={"Guarantee"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="guarantee",
     *         in="path",
     *         description="The ID of the guarantee to be deleted",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Guarantee deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="گارانتی با موفقیت حذف شد")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="خطایی غیرمنتظره در سرور رخ داده است. لطفا دوباره تلاش کنید")
     *         )
     *     )
     * )
     */
    public function destroy(Guarantee $guarantee)
    {
        try {
            $guarantee->delete();
            return response()->json([
                'status' => true,
                'message' => ' گارانتی با موفقیت حذف شد'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'خطایی غیرمنتظره در سرور رخ داده است. لطفا دوباره تلاش کنید'
            ], 500);
        }
    }
}
