<?php

namespace App\Http\Controllers\API\Admin\Market;

use App\Http\Controllers\Controller;
use App\Http\Services\Image\ImageService;
use App\Models\Market\Gallery;
use App\Models\Market\Product;
use Exception;
use Illuminate\Http\Request;

class GalleryController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/admin/market/product/gallery/{product}",
     *     summary="Retrieve list of `Gallery` with it product",
     *     description="Retrieve list of all `ProductImages` with their product",
     *  tags={"Gallery"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *  @OA\Parameter(
     *         name="product",
     *         in="path",
     *         description="product id to fetch its Images",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="A list of `Gallery` with their product",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="array",
     *                 @OA\Items(
     *                     ref="#/components/schemas/Gallery"
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function index(Product $product)
    {
        $galleries = $product->images()->with('product:name,id')->simplePaginate(15);
        $galleries->getCollection()->each(function ($item) {
            $item->product->makeHidden(['status_value', 'related_products_value', 'marketable_value']);
        });
        return response()->json([
            'data' => $galleries
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/market/product/gallery/search/{product}",
     *     summary="Searches among Galleries by name.",
     *     description="This endpoint allows users to search for `Gallery` by name. The search is case-insensitive and returns results that contain the given keyword. The results are paginated for better performance",
     *     tags={"Gallery"},
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
     *         description="type name of Gallery which you're searching for",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="A list of Gallery with their product",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="array",
     *                 @OA\Items(
     *                     ref="#/components/schemas/Gallery"
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function search(Request $request, Product $product)
    {
        $images = Gallery::where('product_id', $product->id)->where('name', 'LIKE', "%" . $request->search . "%")->with('product:name,id')->orderBy('name')->simplePaginate(15);
        $images->getCollection()->each(function ($item) {
            $item->product->makeHidden(['status_value', 'related_products_value', 'marketable_value']);
        });
        return response()->json([
            'data' => $images
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/market/product/gallery/show/{gallery}",
     *     summary="Returns Gallery details for edit form",
     *     description="Returns `Gallery` details with its product for edit form",
     *     tags={"Gallery","Gallery/Form"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *  @OA\Parameter(
     *         name="gallery",
     *         in="path",
     *         description="Id of Gallery that you want showing",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="A Gallery with its product",
     *        @OA\JsonContent(ref="#/components/schemas/Gallery"),
     *     )
     * )
     */
    public function show(Gallery $gallery)
    {
        $gallery->load('product:name,id');
        $gallery->product->makeHidden(['status_value', 'related_products_value', 'marketable_value']);
        return response()->json([
            'data' => $gallery
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/admin/market/product/gallery/store/{product}",
     *     summary="create new value for a Gallery",
     *     description="this method creates a new `Gallery` for the product and stores it.",
     *     tags={"Gallery"},
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
     *             @OA\Property(property="image",type="string",format="binary", example="/path/image.jpg"),
     *             )
     *        )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="successful Gallery creation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="true"),
     *             @OA\Property(property="message", type="string", example="تصویر گالری با موفقیت افزوده شد"),
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

    public function store(Request $request, Product $product, ImageService $imageService)
    {
        $request->validate([
            'name' => 'required|max:120|min:2|regex:/^[ا-یa-zA-Z0-9\-۰-۹ء-ي.,، ]+$/u',
            'image' => 'required|image|mimes:png,jpg,jpeg,gif',
            // 'g-recaptcha-response' => 'recaptcha',
        ]);
        try {
            $inputs = $request->all();
            $inputs['product_id'] = $product->id;
            if ($request->hasFile('image')) {
                $imageService->setExclusiveDirectory('images' . DIRECTORY_SEPARATOR . 'market' . DIRECTORY_SEPARATOR . 'product' . DIRECTORY_SEPARATOR . 'gallery' . DIRECTORY_SEPARATOR . $product->id);
                $result = $imageService->createIndexAndSave($request->file('image'));
                if ($result === false) {
                    return response()->json([
                        'status' => false,
                        'message' => 'بارگذاری عکس با خطا مواجه شد'
                    ], 422);

                }
                $inputs['image'] = $result;
            }
            Gallery::create($inputs);

            return response()->json([
                'status' => true,
                'message' => ' تصویر گالری با موفقیت افزوده شد'
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
     *     path="/api/admin/market/product/gallery/update/{gallery}",
     *     summary="update an existing Gallery",
     *     description="this method updates an existing `Gallery` for the product and stores it.",
     *     tags={"Gallery"},
     *     security={{"bearerAuth": {}}},
     *   @OA\Parameter(
     *         name="gallery",
     *         in="path",
     *         description="ID of the gallery to fetch",
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
     *             @OA\Property(property="image",type="string",format="binary", example="/path/image.jpg"),
     *             @OA\Property(property="_method", type="string", example="PUT"),
     *             )
     *        )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="successful Gallery update",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="true"),
     *             @OA\Property(property="message", type="string", example="تصویر گالری با موفقیت بروزرسانی شد"),
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
    public function update(Request $request, Gallery $gallery, ImageService $imageService)
    {
        $request->validate([
            'name' => 'required|max:120|min:2|regex:/^[ا-یa-zA-Z0-9\-۰-۹ء-ي.,، ]+$/u',
            'image' => 'image|mimes:png,jpg,jpeg,gif',
            // 'g-recaptcha-response' => 'recaptcha',
        ]);
        try{
        $inputs = $request->all();
        if ($request->hasFile('image')) {

            if (!empty($gallery->image)) {
                $imageService->deleteDirectoryAndFiles($gallery->image['directory']);
            }

            $imageService->setExclusiveDirectory('images' . DIRECTORY_SEPARATOR . 'market' . DIRECTORY_SEPARATOR . 'product' . DIRECTORY_SEPARATOR . 'gallery' . DIRECTORY_SEPARATOR . $gallery->product_id);
            $result = $imageService->createIndexAndSave($request->file('image'));

            if ($result === false) {
                return response()->json([
                    'status' => false,
                    'message' => 'بارگذاری عکس با خطا مواجه شد'
                ], 200);

            }
            $inputs['image'] = $result;
        }
        $gallery->update($inputs);
        
            return response()->json([
                'status' => true,
                'message' => 'تصویر گالری با موفقیت بروزرسانی شد'
            ], 200);
        } catch(Exception $e){
            return response()->json([
                'status' => false,
                'message' => 'خطایی غیرمنتظره رخ داده است. لطفا دوباره تلاش کنید'
            ], 500);
        }
    }

     /**
     * @OA\Delete(
     *     path="/api/admin/market/product/gallery/destroy/{gallery}",
     *     summary="Delete a Gallery",
     *     description="This endpoint allows the user to `delete an existing Gallery`.",
     *     operationId="deleteGallery",
     *     tags={"Gallery"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="gallery",
     *         in="path",
     *         description="The ID of the Gallery to be deleted",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Gallery deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="تصویر گالری با موفقیت حذف شد")
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
    public function destroy(Gallery $gallery, ImageService $imageService)
    {
        if (!empty($product->image)) {
            $imageService->deleteDirectoryAndFiles($gallery->image['directory']);
        }
        $result = $gallery->delete();
        if ($result) {
            return response()->json([
                'status' => true,
                'message' => ' تصویر گالری با موفقیت حذف شد'
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'خطایی غیرمنتظره رخ داده است. لطفا دوباره تلاش کنید'
            ], 500);
        }
    }

}
