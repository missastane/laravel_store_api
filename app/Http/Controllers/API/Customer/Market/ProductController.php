<?php

namespace App\Http\Controllers\API\Customer\Market;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\CompareRequest;
use App\Http\Services\Compare\CompareService;
use App\Models\Content\Comment;
use App\Models\Market\Category;
use App\Models\Market\Compare;
use App\Models\Market\Product;
use App\Models\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Session;

class ProductController extends Controller
{
    protected $compareService;

    public function __construct(CompareService $compareService)
    {
        $this->compareService = $compareService;
    }
    /**
     * @OA\Get(
     *     path="/api/product/{product:slug}",
     *     summary="Get product details",
     *     description="Retrieve detailed information of a specific product including its images, guarantees, colors, metas, attributes, and related products to design a single product page.",
     *     operationId="getProductPageDetails",
     *     tags={"Market"},
     *     security={{"bearerAuth":{}}},
     * 
     *     @OA\Parameter(
     *         name="product:slug",
     *         in="path",
     *         required=true,
     *         description="The Slug of the product",
     *         @OA\Schema(type="string", example="موبایل-سامسونگ-مدل-a71")
     *     ),
     * 
     *     @OA\Response(
     *         response=200,
     *         description="Product details retrieved successfully",
     *         @OA\JsonContent(
     *              type="array",
     *            @OA\Items(
     *             @OA\Property(property="product", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Product Name"),
     *                  @OA\Property(property="image",type="object",
     *                     @OA\Property(property="indexArray",type="object",
     *                        @OA\Property(property="large", type="string", format="uri", example="images\\market\\product\\12\\2025\\02\\03\\1738570484\\1738570484_large.jpg"),
     *                        @OA\Property(property="medium", type="string", format="uri", example="images\\market\\product\\12\\2025\\02\\03\\1738570484\\1738570484_medium.jpg"),
     *                        @OA\Property(property="small", type="string", format="uri", example="images\\market\\product\\12\\2025\\02\\03\\1738570484\\1738570484_small.jpg")
     *                      ),
     *                     @OA\Property(property="directory",type="string",example="images\\market\\product\\12\\2025\\02\\03\\1738570484"),
     *                     @OA\Property(property="currentImage",type="string",example="medium")
     *                  ),
     *                 @OA\Property(property="price", type="number", example=199.99),
     *                 @OA\Property(property="description", type="string", example="This is a sample product description."),
     *                 @OA\Property(property="width", type="number", example=10.5),
     *                 @OA\Property(property="height", type="number", example=20),
     *                 @OA\Property(property="length", type="number", example=30),
     *                 @OA\Property(property="weight", type="number", example=2.5),
     *                 @OA\Property(property="marketable", type="boolean", example=true),
     *                 @OA\Property(property="marketable_number", type="integer", example=50),
     *                 @OA\Property(property="gallery", type="array", @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Gallery Image"),
     *                     @OA\Property(property="image",type="object",
     *                        @OA\Property(property="indexArray",type="object",
     *                           @OA\Property(property="large", type="string", format="uri", example="images\\market\\product\\12\\2025\\02\\03\\1738570484\\1738570484_large.jpg"),
     *                           @OA\Property(property="medium", type="string", format="uri", example="images\\market\\product\\12\\2025\\02\\03\\1738570484\\1738570484_medium.jpg"),
     *                           @OA\Property(property="small", type="string", format="uri", example="images\\market\\product\\12\\2025\\02\\03\\1738570484\\1738570484_small.jpg")
     *                          ),
     *                        @OA\Property(property="directory",type="string",example="images\\market\\product\\12\\2025\\02\\03\\1738570484"),
     *                        @OA\Property(property="currentImage",type="string",example="medium")
     *                      ),
     *                 
     *                  )),
     *                 @OA\Property(property="guarantees", type="array", @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="1 Year Warranty"),
     *                     @OA\Property(property="price_increase", type="number", example=20)
     *                 )),
     *                 @OA\Property(property="colors", type="array", @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="color_name", type="string", example="Red"),
     *                     @OA\Property(property="color", type="string", example="#FF0000"),
     *                     @OA\Property(property="price_increase", type="number", example=10),
     *                     @OA\Property(property="marketable_number", type="integer", example=100)
     *                 )),
     *                 @OA\Property(property="category_attributes", type="array", @OA\Items(
     *                     @OA\Property(property="category_attribute_id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Size"),
     *                     @OA\Property(property="unit", type="string", example="cm"),
     *                     @OA\Property(property="value",type="string", example="2 - 4 - 6")
     *                 )),
     *                 @OA\Property(property="meta", type="array", @OA\Items(
     *                     @OA\Property(property="meta_key", type="string", example="brand"),
     *                     @OA\Property(property="meta_value", type="string", example="Nike")
     *                 )),
     *                 @OA\Property(property="is_favorite", type="boolean", example=true)
     *             ),
     *             @OA\Property(property="related_products", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=2),
     *                 @OA\Property(property="name", type="string", example="Related Product"),
     *                 @OA\Property(property="price", type="number", example=149.99),
     *                 @OA\Property(property="image",type="object",
     *                     @OA\Property(property="indexArray",type="object",
     *                        @OA\Property(property="large", type="string", format="uri", example="images\\market\\product\\12\\2025\\02\\03\\1738570484\\1738570484_large.jpg"),
     *                        @OA\Property(property="medium", type="string", format="uri", example="images\\market\\product\\12\\2025\\02\\03\\1738570484\\1738570484_medium.jpg"),
     *                        @OA\Property(property="small", type="string", format="uri", example="images\\market\\product\\12\\2025\\02\\03\\1738570484\\1738570484_small.jpg")
     *                       ),
     *                     @OA\Property(property="directory",type="string",example="images\\market\\product\\12\\2025\\02\\03\\1738570484"),
     *                     @OA\Property(property="currentImage",type="string",example="medium")
     *                  ),
     *                @OA\Property(property="marketable_value", type="string", description="marketable_value: 'قابل فروش' if 1, 'غیرقابل فروش' if 2", example="قابل فروش"),
     * 
     *             )))
     *         )
     *     ),
     * 
     *     @OA\Response(
     *         response=404,
     *         description="Not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="مسیر مورد نظر پیدا نشد")
     *         )
     *     ),
     * 
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="An error occurred, please try again later")
     *         )
     *     )
     * )
     */
    public function options(Product $product)
    {
        $user = auth()->user();
        $userIp = request()->ip();

        // load relations
        $product->load([
            'images',
            'guarantees',
            'colors',
            'metas',
            'values.attribute:id,name,unit',
        ]);

        // submit product views
        if (
            !View::where('ip', $userIp)
                ->where('viewable_type', Product::class)
                ->where('viewable_id', $product->id)
                ->exists()
        ) {
            View::createViewLog(Product::class, $product, $userIp);
            $product->increment('view');
        }

        // get related products
        $relatedProductIds = explode(',', $product->related_products);
        $relatedProducts = Product::whereIn('id', $relatedProductIds)
            ->select('id', 'name', 'price', 'image')
            ->get();
        $relatedProducts->makeHidden(['status_value', 'related_products_value']);
        // get product attributes and values

        $category_attr_details = $product->values->groupBy('category_attribute_id')->map(function ($values, $category_attribute_id) {
            return [
                'category_attribute_id' => $category_attribute_id,
                'name' => $values->first()->attribute->name,
                'unit' => $values->first()->attribute->unit,
                'values' => implode(' - ', $values->map(fn($v) => $v->value['value'])->toArray()) // convert values array to a string
            ];
        })->values();

        // get product is_favorite
        $is_favorite = $user ? $product->users->contains($user->id) : false;

        return response()->json([
            'data' => [
                'product' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'image' => $product->image,
                    'price' => $product->price,
                    'description' => $product->introduction,
                    'width' => $product->width,
                    'height' => $product->height,
                    'length' => $product->length,
                    'weight' => $product->weight,
                    'marketable' => $product->marketable_value,
                    'marketable_number' => $product->marketable_number,
                    'gallery' => $product->images->map->only(['id', 'name', 'image']),
                    'guarantees' => $product->guarantees->map->only(['id', 'name', 'price_increase']),
                    'colors' => $product->colors->map->only(['id', 'color_name', 'color', 'price_increase', 'marketable_number']),
                    'category_attributes' => $category_attr_details,
                    'meta' => $product->metas->map->only(['meta_key', 'meta_value']),
                    'is_favorite' => $is_favorite,
                ],
                'related_products' => $relatedProducts,
            ]
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/product/add-comment/{product}",
     *     summary="Add a comment to a product",
     *     description="Users can `Submit a comment for a product`. The comment will be displayed after admin approval.",
     *     operationId="addCommentToProduct",
     *     tags={"Market"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="product",
     *         in="path",
     *         required=true,
     *         description="Product ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"body"},
     *             @OA\Property(
     *                 property="body",
     *                 type="string",
     *                 example="This is a test comment.",
     *                 pattern="^[a-zA-Z\u0600-\u06FF0-9\s\-\.\,]+$",
     *                 description="The comment text must be between 2 and 1000 characters.Also can only contain Persian and English letters, Persian and English numbers, and symbols (-.,). Any other characters will result in a validation error.",
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Comment submitted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="ثبت نظر با موفقیت انجام شد. پس از تأیید مدیر سایت نمایش داده خواهد شد")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Failed to submit comment"),
     *             @OA\Property(property="error", type="string", example="ارسال نظر با خطا مواجه شد")
     *         )
     *     )
     * )
     */
    public function addComment(Product $product, Request $request)
    {
        try {
            $validated = $request->validate([
                'body' => 'required|max:1000|min:2|regex:/^[ا-یa-zA-Z0-9\-۰-۹ء-ي.,،\.?؟! ]+$/u'
            ]);
            $inputs['body'] = str_replace(PHP_EOL, '<br/>', $request->body);
            $inputs['author_id'] = Auth::user()->id;
            $inputs['commentable_id'] = $product->id;
            $inputs['commentable_type'] = Product::class;
            $comment = Comment::create($inputs);

            return response()->json([
                'status' => true,
                'message' => 'ثبت نظر با موفقیت انجام شد. پس از تأیید مدیر سایت نمایش داده خواهد شد'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'ارسال نظر با خطا مواجه شد',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function addToFavorite(Product $product)
    {
        \Log::info('enterd method');
        if (Auth::check()) {
            $product->users()->toggle(Auth::user()->id);
            if ($product->users->contains(Auth::user()->id)) {
                return response()->json([
                    'status' => 1,
                    'message' => 'محصول به لیست علاقمندی ها افزوده شد'
                ]);
            } else {
                return response()->json([
                    'status' => 2,
                    'message' => 'محصول از لیست علاقمندی های کاربر پاک شد'
                ]);
            }
        } else {
            return response()->json([
                'status' => 3,
                'message' => 'کاربر احراز هویت نشده است. برای مشاهده لیست علاقمندی ها وارد شده یا ثبت نام کنید'
            ]);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/product/add-rate/{product}",
     *     summary="Rate a product",
     *     description="Allows authenticated users to `rate a product`. Users can rate `only if they have purchased the product`.",
     *     operationId="addRate",
     *     tags={"Market"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="product",
     *         in="path",
     *         required=true,
     *         description="Product ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"rating"},
     *             @OA\Property(
     *                 property="rating",
     *                 type="integer",
     *                 example=5,
     *                 description="Rating value must be between 1 and 5."
     *             )
     *         )
     *     ),
     * 
     *     @OA\Response(
     *         response=200,
     *         description="Rating submitted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="امتیاز شما با موفقیت ثبت گردید")
     *         )
     *     ),
     * 
     *     @OA\Response(
     *         response=403,
     *         description="User has not purchased the product",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="جهت ثبت امتیاز ابتدا باید محصول را خریداری نمایید")
     *         )
     *     ),
     * 
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation error"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     * 
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
    public function addRate(Product $product, Request $request)
    {
        // validation request
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5'
        ]);
        try {
            // if product perchased product and be authenticated allow to add rate to product
            $productIds = auth()->user()->isUserPerchasedProduct($product->id);
            if (Auth::check() && $productIds->count() > 0) {
                $user = Auth::user();
                $user->rate($product, $validated['rating']);
                return response()->json([
                    'status' => true,
                    'message' => 'امتیاز شما با موفقیت ثبت گردید'
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'جهت ثبت امتیاز ابتدا باید محصول را خریداری نمایید'
                ], 403);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'خطایی غیرمنتظره در سرور رخ داده است. لطفا دوباره تلاش کنید'
            ], 500);

        }
    }

    /**
     * @OA\Get(
     *     path="/api/product/compare/{product}",
     *     summary="Compare a product",
     *     description="Adds the first product to the comparison list and retrieves its attributes and details. The comparison list is stored on the client-side (e.g., in the session or local storage)",
     *     operationId="compareProduct",
     *     tags={"Comparison","Market"},
     * 
     *     @OA\Parameter(
     *         name="product",
     *         in="path",
     *         required=true,
     *         description="Product ID to be compared",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product added to comparison successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="اولین محصول با موفقیت به لیست مقایسه اضافه شد"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="products", type="array", @OA\Items(type="object")),
     *                 @OA\Property(property="attributes", type="array", @OA\Items(type="object"))
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="مسیر مورد نظر یافت نشد")
     *         )
     *     )
     * )
     */
    public function compare(Product $product)
    {
        // find product and load relations
        $product->load([
            'category.attributes' => function ($query) {
                $query->select('id', 'name', 'category_id')
                    ->with(['values']);
            },
            'metas'
        ]);

        $product = collect([$product]);

        // get attributes with their values list
        $attributes = $this->compareService->getProductAttributes($product);

        // get product details
        $formattedProducts = $this->compareService->formatProductDetails($product);

        return response()->json([
            'status' => true,
            'message' => 'اولین محصول با موفقیت به لیست مقایسه اضافه شد',
            'data' => [
                'products' => $formattedProducts,
                'attributes' => $attributes
            ]
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/product/add-to-compare/{product}",
     *     summary="Add a product to the comparison list",
     *     description="Adds a new product to the comparison list while ensuring the maximum limit (4 products) and category consistency. The comparison list is stored on the client-side (e.g., in the session or local storage)",
     *     tags={"Comparison","Market"},
     *     @OA\Parameter(
     *         name="product",
     *         in="path",
     *         required=true,
     *         description="ID of the new product to be compared",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"products"},
     *             @OA\Property(
     *                 property="products",
     *                 type="array",
     *                 description="List of product IDs already in the comparison list",
     *                 @OA\Items(type="integer")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Comparison list updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="لیست مقایسه با موفقیت ارسال شد"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="products",
     *                     type="array",
     *                     @OA\Items(type="object")
     *                 ),
     *                 @OA\Property(
     *                     property="attributes",
     *                     type="array",
     *                     @OA\Items(type="object")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error (e.g., exceeding max product limit or category mismatch)",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="حداکثر ۴ محصول را می‌توان مقایسه کرد"),
     *             @OA\Property(property="data", type="array", @OA\Items(type="integer"))
     *         )
     *     ),
     * )
     */
    public function addToCompare(CompareRequest $request, Product $product)
    {
        if (count($request['products']) >= 4) {
            return $this->compareService->errorResponse('حداکثر ۴ محصول را می‌توان مقایسه کرد', $request['products']);
        }

        // get new product and old products
        $oldProducts = Product::whereIn('id', $request['products'])->get();

        if ($oldProducts->isNotEmpty() && $oldProducts->first()->category_id !== $product->category_id) {
            return $this->compareService->errorResponse(
                'محصولات باید از یک دسته‌بندی باشند',
                [
                    'comparelistCategory' => $oldProducts->first()->category->name,
                    'newProductCategory' => $product->category->name
                ]
            );
        }

        $newList = array_merge($request['products'], [$product->id]);

        // get compare products and load their relations 
        $products = Product::with([
            'category.attributes' => function ($query) {
                $query->select('id', 'name', 'category_id')->with('values');
            },
            'metas'
        ])->whereIn('id', $newList)->get();

        // get products attributes and values
        $attributes = $this->compareService->getProductAttributes($products);

        // get products details
        $formattedProducts = $this->compareService->formatProductDetails($products);

        return response()->json([
            'status' => true,
            'message' => 'لیست مقایسه با موفقیت ارسال شد',
            'data' => [
                'products' => $formattedProducts,
                'attributes' => $attributes
            ]
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/product/remove-from-compare/{product}",
     *     summary="Remove a product from the comparison list",
     *     description="This endpoint removes a product from the comparison list. The comparison list is stored on the client-side (e.g., in the session or local storage), and no data is deleted from the database.",
     *     operationId="removeFromCompare",
     *     tags={"Comparison","Market"},
     *     @OA\Parameter(
     *         name="product",
     *         in="path",
     *         required=true,
     *         description="The ID of the product to be removed from the comparison list",
     *         @OA\Schema(type="integer", example=15)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="List of product IDs currently in the comparison",
     *         @OA\JsonContent(
     *             type="object",
     *             required={"products"},
     *             @OA\Property(
     *                 property="products",
     *                 type="array",
     *                 description="Array of product IDs in the comparison list",
     *                 @OA\Items(type="integer", example=12)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product successfully removed from the comparison list",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="محصول با موفقیت از لیست مقایسه حذف شد"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="products",
     *                     type="array",
     *                     description="Updated list of compared products",
     *                     @OA\Items(type="object")
     *                 ),
     *                 @OA\Property(
     *                     property="attributes",
     *                     type="array",
     *                     description="Attributes of the compared products",
     *                     @OA\Items(type="object")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="The product is not in the comparison list",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="محصول موردنظر در لیست مقایسه وجود ندارد"),
     *             @OA\Property(property="products", type="array", @OA\Items(type="integer"))
     *         )
     *     )
     * )
     */
    public function removeFromCompare(CompareRequest $request, Product $product)
    {
        // if product exists in compare list
        if (!in_array($product->id, $request['products'])) {
            return $this->compareService->errorResponse([
                'message' => 'محصول موردنظر در لیست مقایسه وجود ندارد',
            ], ['products' => $request['products']]);
        }

        // remove product from compare list
        $filteredProducts = array_values(array_filter($request['products'], function ($id) use ($product) {
            return $id != $product->id;
        }));

        // get remain products info
        $products = Product::with([
            'category.attributes' => function ($query) {
                $query->select('id', 'name', 'category_id')->with('values');
            },
            'metas'
        ])->whereIn('id', $filteredProducts)->get();

        // get products attributes
        $attributes = $this->compareService->getProductAttributes($products);

        // format products to show in output
        $formattedProducts = $this->compareService->formatProductDetails($products);

        return response()->json([
            'status' => true,
            'message' => 'محصول با موفقیت از لیست مقایسه حذف شد',
            'data' => [
                'products' => $formattedProducts,
                'attributes' => $attributes
            ]
        ], 200);
    }




}
