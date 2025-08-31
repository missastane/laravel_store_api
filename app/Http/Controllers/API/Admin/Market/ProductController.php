<?php


namespace App\Http\Controllers\API\Admin\Market;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Market\ProductRequest;
use App\Http\Services\Image\ImageService;
use App\Models\Market\Brand;
use App\Models\Market\Category;
use App\Models\Market\CategoryAttribute;
use App\Models\Market\Product;
use App\Models\Market\Product_Meta;
use App\Models\Market\ProductMeta;
use App\Models\Tag;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use OpenApi\Annotations as OA;
/**
 * @OA\OpenApi(
 *     @OA\Info(
 *         title="laravel_api_store",
 *         version="1.0.0",
 *         description="توضیحات درباره پروژه Api لاراول",
 *         @OA\Contact(
 *             email="missastaneh@gmail.com"
 *         ),
 *         @OA\License(
 *             name="Missastane",
 *             url="https://missastane.com"
 *         )
 *     ),
 *     @OA\Components(
 *         @OA\SecurityScheme(
 *             securityScheme="bearerAuth",
 *             type="http",
 *             scheme="bearer"
 *         )
 *     )
 * )
 * @OA\Security(
 *     securityScheme="bearerAuth"
 * )
 */

class ProductController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/admin/market/product",
     *     summary="Retrieve list of products",
     *     description="Retrieve list of all `Products`",
     *  tags={"Product","Store"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *     @OA\Response(
     *         response=200,
     *         description="A list of Products with relations Brand, Category, Tags",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="array",
     *                 @OA\Items(
     *                     ref="#/components/schemas/Product"
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function index()
    {
        $products = Product::with('brand:persian_name,id', 'category:name,id', 'tags:name,id')->orderBy('created_at', 'desc')->simplePaginate(15);
        $products->getCollection()->each(function ($item) {
            if (isset($item->brand)) {
                $item->brand->makeHidden(['status_value']);
            }
            $item->category->makeHidden(['status_value', 'show_in_menu_value']);
            $item->tags->makeHidden(['pivot']);
        });
        return response()->json([
            'products' => $products
        ], 200);
    }
    /**
     * @OA\Get(
     *     path="/api/admin/market/product/search",
     *     summary="Searchs among products by name",
     *     description="This endpoint allows users to search for products by name. The search is case-insensitive and returns results that contain the given keyword. The results are paginated for better performance",
     *    tags={"Product","Store"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *   @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="type name of product which you're searching for",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="A list of products with relations Brand, Category, Tags",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="array",
     *                 @OA\Items(
     *                     ref="#/components/schemas/Product"
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function search(Request $request)
    {
        $products = Product::where('name', 'LIKE', "%" . $request->search . "%")->with('brand:persian_name,id', 'category:name,id', 'tags:name,id')->orderBy('name')->simplePaginate(15);
        $products->getCollection()->each(function ($item) {
            if (isset($item->brand)) {
                $item->brand->makeHidden(['status_value']);
            }
            $item->category->makeHidden(['status_value', 'show_in_menu_value']);
            $item->tags->makeHidden(['pivot']);
        });
        return response()->json([
            'products' => $products
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/market/product/status/{product}",
     *     summary="Change the status of a product",
     *     description="This endpoint toggles the status of a product (active/inactive)",
     *     operationId="updateProductStatus",
     *     security={{"bearerAuth": {}}},
     *     tags={"Product"},
     *     @OA\Parameter(
     *         name="product",
     *         in="path",
     *         description="Product id to change the status",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     security={ {"bearerAuth": {}} },
     *     @OA\Response(
     *         response=200,
     *         description="Product status updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="checked", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="وضعیت محصول با موفقیت فعال شد")
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
    public function status(Product $product)
    {
        $product->status = $product->status == 1 ? 2 : 1;
        $result = $product->save();
        if ($result) {
            if ($product->status == 1) {
                return response()->json([
                    'status' => true,
                    'checked' => true,
                    'message' => 'وضعیت ' . $product->name . ' با موفقیت فعال شد'
                ]);
            } else {
                return response()->json([
                    'status' => true,
                    'checked' => false,
                    'message' => 'وضعیت ' . $product->name . ' با موفقیت غیرفعال شد'
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
     *     path="/api/admin/market/product/store",
     *     summary="create new product",
     *     description="this method creates a new product and stores its related tags and metadata. The product must have a name, price, and category,...,tags , while metadata are optional. If provided, they will be saved alongside the product.",
     *     tags={"Product"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *             @OA\Property(property="image", type="string",format="binary" ),
     *             @OA\Property(property="name", type="string", pattern="^[a-zA-Z\u0600-\u06FF0-9\s\-\.\?]+$", description="This field can only contain Persian and English letters, Persian and English numbers, and hyphens (-). Any other characters will result in a validation error.", example="لپ تاپ ایسوس مدل s540"),
     *             @OA\Property(property="price", type="integer", example=35000000),
     *             @OA\Property(property="category_id",description="Category ID. This must be provided when creating or updating the product.", type="integer", example=5),
     *             @OA\Property(property="brand_id", description="Brand ID. This must be provided when creating or updating the product.", type="integer", example=5),
     *             @OA\Property(property="width", type="number", format="float", example=15),
     *             @OA\Property(property="length", type="number", format="float", example=15),
     *             @OA\Property(property="weight", type="number", format="float", example=15),
     *             @OA\Property(property="height", type="number", format="float", example=15),
     *             @OA\Property(property="introduction", type="string", example="لپ‌تاپ گیمینگ با پردازنده Core i7"),
     *             @OA\Property(property="status",type="integer",enum={1, 2},description="1 = active, 2 = inactive",example=1),
     *             @OA\Property(property="marketable",
     *                 oneOf={
     *                     @OA\Schema(type="integer", example=1, description="1 = marketable"),
     *                     @OA\Schema(type="integer", example=2, description="2 = none marketable")
     *                 }
     *             ),
     *             @OA\Property(property="related_products[]",type="array",
     *                @OA\Items(type="integer", example=2)
     *             ),     
     *             @OA\Property(property="tags[]",type="array",
     *                 @OA\Items(type="string",pattern="^[a-zA-Z\u0600-\u06FF0-9\s\-\.\?]+$", example="آیا api خوب است؟"),
     *              description="This field can only contain Persian and English letters, Persian and English numbers, hyphens (-),question marks (?), and periods (.). Any other characters will result in a validation error.",
     *             ),
     *             @OA\Property(property="meta_key[]",type="array",
     *                 @OA\Items(type="string", example="حسگر اثر انگشت"),
     *              description="This field is nullable but not if meta_value[] is existed unless you will have a validation error.",
     *             ),
     *              @OA\Property(property="meta_value[]",type="array",
     *                 @OA\Items(type="string", example="دارد"),
     *              description="This field is nullable but not if meta_key[] is existed unless you will have a validation error.",
     *             ),
     *              @OA\Property(property="published_at", type="integer", example=1677030400)
     *          ),
     *             encoding={
     *                 "tags[]": {
     *                     "style": "form",
     *                     "explode": true
     *                 }
     *             } 
     *       )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="successful product and tags creation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="true"),
     *             @OA\Property(property="message", type="string", example="محصول با موفقیت افزوده شد")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="invalid request",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="نام محصول الزامی است")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="خطای غیرمنتظره در سرور رخ داده است. لطفاً دوباره تلاش کنید.")
     *         )
     *     )
     *  )
     */
    public function store(ImageService $imageService, ProductRequest $request)
    {
        try {
            DB::beginTransaction();
            date_default_timezone_set('Iran');
            $realTimestamp = substr($request['published_at'], 0, 10);
            $request['published_at'] = date("Y-m-d H:i:s", (int) $realTimestamp);
            $inputs = $request->all();

            if ($request->hasFile('image')) {
                $imageService->setExclusiveDirectory('images' . DIRECTORY_SEPARATOR . 'market' . DIRECTORY_SEPARATOR . 'product');
                $result = $imageService->createIndexAndSave($request->file('image'));

                if ($result === false) {
                    return response()->json([
                        'status' => false,
                        'message' => 'بارگذاری عکس با خطا مواجه شد'
                    ], 422);
                }
                $inputs['image'] = $result;
            }

            $inputs['related_products'] = implode(",", array_values($inputs['related_products']));


            try {
                $product = Product::create($inputs);

                if ($request->has('tags')) {
                    foreach ($request->tags as $tagName) {
                        $tag = Tag::firstOrCreate(['name' => $tagName]);
                        $product->tags()->attach($tag);
                    }
                }

                if ($request->meta_value != null && $request->meta_key != null) {
                    if (!in_array('', $request->meta_value)) {
                        $metas = array_combine($request->meta_key, $request->meta_value);
                        foreach ($metas as $meta_key => $meta_value) {
                            ProductMeta::create([
                                'meta_key' => $meta_key,
                                'meta_value' => $meta_value,
                                'product_id' => $product->id
                            ]);
                        }
                    }
                }

                DB::commit();
                return response()->json([
                    'status' => true,
                    'message' => 'محصول با موفقیت افزوده شد',
                ], 201);
            } catch (Exception $e) {
                DB::rollBack();
                return response()->json([
                    'status' => false,
                    'message' => 'خطایی در ذخیره محصول رخ داد. لطفاً مجدداً تلاش کنید.'
                ], 500);
            }
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'خطای غیرمنتظره‌ای در سرور رخ داده است. لطفا دوباره تلاش کنید'
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/admin/market/product/show/{product}",
     *     summary="Get details of a specific product",
     *     description="Returns the `product` details along with related products, categories, and brands. The product itself is removed from the `products` list to prevent self-selection in related products for editing. The `relatedProducts` field is returned as an array instead of a comma-separated string to simplify frontend processing in edit method",
     *     operationId="getProductDetails",
     *     tags={"Product","Product/Form","Store","Store/Form"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="product",
     *         in="path",
     *         description="ID of the product to fetch",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Parameter(
     *         name="include",
     *         in="query",
     *         description="Comma-separated list of additional relations to include (e.g. 'orderItems,cartItems,metas,colors,images,guarantees,attributes,values,amazingSales,comments,users')",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully fetched product details, related products, and other products for editing",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="product",ref="#/components/schemas/Product"),
     *                 @OA\Property(property="related_products[]",
     *                 type="array",
     *                 @OA\Items(type="object",
     *                 @OA\Property(property="id", type="integer", example=3),
     *                 @OA\Property(property="name", type="string", example="Gaming Mouse")
     *                 )
     *                 ),     
     *                 @OA\Property(
     *                     property="products",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer"),
     *                         @OA\Property(property="name", type="string")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="An error occurred. Please try again.")
     *         )
     *     )
     * )
     */

    public function show(Request $request, Product $product)
    {
        $relatedProducts = explode(',', $product['related_products']);
        // this $products will use for edit
        $products = Product::where('id', '!=', $product->id)->select(['name', 'id'])->orderBy('name')->get()->makeHidden(['status_value', 'marketable_value', 'related_products_value']);
        $defaultRelations = ['brand:persian_name,id', 'category:name,id', 'tags:name,id'];
        if (!empty($request->query('include'))) {
            $extraRelations = explode(',', $request->query('include', ''));
            $product = $product->load(array_merge($defaultRelations, $extraRelations));

        } else {
            $product = $product->load($defaultRelations);
            if (isset($product->brand)) {
                $product->brand->makeHidden(['status_value']);
            }
            $product->category->makeHidden(['status_value', 'show_in_menu_value']);
            $product->tags->makeHidden(['pivot']);
        }
        return response()->json([
            'data' => [
                'product' => $product,
                'relatedProducts' => $relatedProducts,
                'products' => $products
            ]
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/market/product/options",
     *     summary="Get necessary options for product forms",
     *     description="This endpoint returns all `productCategories`, `brands`, and `products`, which can be used to create a new product or edit method",
     *     tags={"Product", "Product/Form"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successfully fetched product categories, brands, and products that you may need to make edit,create,.. pages",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="productCategories",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer"),
     *                         @OA\Property(property="name", type="string")
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="brands",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer"),
     *                         @OA\Property(property="persian_name", type="string"),
     *                         @OA\Property(property="original_name", type="string")
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="products",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer"),
     *                         @OA\Property(property="name", type="string")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="An error occurred. Please try again.")
     *         )
     *     )
     * )
     */

    public function options()
    {
        $productCategories = Category::select('name', 'id')->orderBy('name')->get();
        $brands = Brand::select('persian_name', 'original_name', 'id')->orderBy('original_name')->get();
        //    this $products will use for create
        $products = Product::select(['name', 'id'])->orderBy('name')->get();
        return response()->json([
            'data' => [
                'productCategories' => $productCategories,
                'brands' => $brands,
                'products' => $products
            ]
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/admin/market/product/update/{product}",
     *     summary="Update an existing product",
     *     description="This endpoint allows the user to update an existing product, including its image, category, tags, and meta information",
     *  tags={"Product"},
     *     security={{"bearerAuth": {}}}, 
     *     @OA\Parameter(
     *         name="product",
     *         in="path",
     *         description="The ID of the product to be updated",
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
     *                     property="image",
     *                     type="string",
     *                     format="binary"
     *         ),
     *             @OA\Property(property="_method", type="string", example="PUT"),
     *             @OA\Property(property="name", type="string", pattern="^[a-zA-Z\u0600-\u06FF0-9\s\-\.\?]+$", description="This field can only contain Persian and English letters, Persian and English numbers, and hyphens (-). Any other characters will result in a validation error.", example="لپ تاپ ایسوس مدل s540"),
     *             @OA\Property(property="price", type="integer", example=35000000),
     *             @OA\Property(property="category_id",description="Category ID. This must be provided when creating or updating the product.", type="integer", example=5),
     *             @OA\Property(property="brand_id",description="Brand ID. This must be provided when creating or updating the product.", type="integer", example=5),
     *             @OA\Property(property="width", type="number", format="float", example=15),
     *             @OA\Property(property="length", type="number", format="float", example=15),
     *             @OA\Property(property="weight", type="number", format="float", example=15),
     *             @OA\Property(property="height", type="number", format="float", example=15),
     *             @OA\Property(property="introduction", type="string", example="لپ‌تاپ گیمینگ با پردازنده Core i7"),
     *             @OA\Property(
     *                 property="status",
     *                 oneOf={
     *                     @OA\Schema(type="integer", example=1, description="1 = active"),
     *                     @OA\Schema(type="integer", example=2, description="2 = inactive")
     *                 }
     *             ),
     *             @OA\Property(
     *                 property="marketable",
     *                 oneOf={
     *                     @OA\Schema(type="integer", example=1, description="1 = marketable"),
     *                     @OA\Schema(type="integer", example=2, description="2 = none marketable")
     *                 }
     *             ),
     *             @OA\Property(
     *                 property="related_products[]",
     *                 type="array",
     *                 @OA\Items(type="integer", example=2)
     *             ),     
     *             @OA\Property(
     *                 property="tags[]",
     *                 type="array",
     *                 @OA\Items(type="string",pattern="^[a-zA-Z\u0600-\u06FF0-9\s\-\.\?]+$", example="آیا api خوب است؟"),
     *              description="This field can only contain Persian and English letters, Persian and English numbers, hyphens (-),question marks (?), and periods (.). Any other characters will result in a validation error.",
     *             ),
     *             @OA\Property(
     *                 property="meta_key[]",
     *                 type="array",
     *                 @OA\Items(type="string", example="حسگر اثر انگشت"),
     *              description="This field is nullable but not if meta_value[] is existed unless you will have a validation error.",
     *             ),
     *             @OA\Property(
     *                 property="meta_value[]",
     *                 type="array",
     *                 @OA\Items(type="string", example="دارد"),
     *              description="This field is nullable but not if meta_key[] is existed unless you will have a validation error.",
     *             ),
     *                 @OA\Property(property="published_at", type="integer", example=1677030400)
     *                       ),
     *              encoding={
     *                 "tags[]": {
     *                     "style": "form",
     *                     "explode": true
     *                 }
     *             }
     *             )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="successful product and tags update",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="true"),
     *             @OA\Property(property="message", type="string", example="محصول با موفقیت ویرایش شد")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="invalid request",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="نام محصول الزامی است")
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


    public function update(Product $product, ImageService $imageService, ProductRequest $request)
    {
        try {
            DB::beginTransaction();
            date_default_timezone_set('Iran');
            $realTimestamp = substr($request['published_at'], 0, 10);
            $request['published_at'] = date("Y-m-d H:i:s", (int) $realTimestamp);
            $inputs = $request->all();

            if ($request->hasFile('image')) {
                if (!empty($product->image)) {
                    $imageService->deleteDirectoryAndFiles($product->image['directory']);
                }
                $imageService->setExclusiveDirectory('images' . DIRECTORY_SEPARATOR . 'market' . DIRECTORY_SEPARATOR . 'product');
                $result = $imageService->createIndexAndSave($request->file('image'));

                if ($result === false) {
                    return response()->json(['message' => 'بارگذاری عکس با خطا مواجه شد'], 422);
                }
                $inputs['image'] = $result;
            } else {
                $inputs['image'] = $product->image;
            }


            if ($inputs['category_id'] != $product->category_id) {
                $product->values()->delete();
            }

            $inputs['related_products'] = implode(",", array_values($inputs['related_products']));

            $product->update($inputs);

            if (!empty($request->meta_value) && !empty($request->meta_key)) {

                $newMetas = array_combine($request->meta_key, $request->meta_value);
                $existingMetas = $product->metas()->pluck('meta_value', 'meta_key')->toArray();

                foreach ($newMetas as $metaKey => $metaValue) {
                    if (isset($existingMetas[$metaKey])) {

                        if ($existingMetas[$metaKey] !== $metaValue) {
                            ProductMeta::where('product_id', $product->id)
                                ->where('meta_key', $metaKey)
                                ->update(['meta_value' => $metaValue]);
                        }
                        unset($existingMetas[$metaKey]);
                    } else {

                        ProductMeta::create([
                            'meta_key' => $metaKey,
                            'meta_value' => $metaValue,
                            'product_id' => $product->id
                        ]);
                    }
                }


                if (!empty($existingMetas)) {
                    ProductMeta::where('product_id', $product->id)
                        ->whereIn('meta_key', array_keys($existingMetas))
                        ->delete();
                }
            }

            if ($request->has('tags')) {
                $tagIds = [];
                foreach ($request->tags as $tagName) {
                    $tag = Tag::firstOrCreate(['name' => $tagName]);
                     array_push($tagIds,$tag->id);
                }

                $product->tags()->sync($tagIds);
            }
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'محصول با موفقیت ویرایش شد'
            ], 200);

        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'خطایی رخ داد'
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/admin/market/product/destroy/{product}",
     *     summary="Delete a product",
     *     description="This endpoint allows the user to delete an existing product.",
     *     operationId="deleteProduct",
     *     tags={"Product"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="product",
     *         in="path",
     *         description="The ID of the product to be deleted",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="محصول Example Product با موفقیت حذف شد")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="مشکلی پیش آمده است. لطفا دوباره امتحان کنید")
     *         )
     *     )
     * )
     */

    public function destroy(Product $product)
    {
        try {
            $result = $product->delete();

            return response()->json([
                'status' => true,
                'message' => 'محصول ' . $product->name . ' با موفقیت حذف شد'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message',
                'مشکلی پیش آمده است. لطفا دوباره امتحان کنید'
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/admin/market/product/delete-meta/{meta}",
     *     summary="Delete a productMeta",
     *     description="This endpoint allows the user to delete an existing productMeta.",
     *     operationId="deleteProductMeat",
     *     tags={"Product"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="product",
     *         in="path",
     *         description="The ID of the productMeat to be deleted",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="ProductMeat deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="ویژگی محصول با موفقیت حذف شد")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="مشکلی پیش آمده است. لطفا دوباره امتحان کنید")
     *         )
     *     )
     * )
     */

    public function deleteMeta(ProductMeta $meta)
    {
        try {
            $result = $meta->delete();

            return response()->json([
                'status' => true,
                'checked' => true,
                'message' => 'ویژگی با موفقیت حذف شد'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'عملیات با خطا مواجه شد. دوباره امتحان کنید'
            ], 500);
        }
    }


}
