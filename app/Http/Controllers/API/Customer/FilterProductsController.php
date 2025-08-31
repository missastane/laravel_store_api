<?php

namespace App\Http\Controllers\API\Customer;

use App\Http\Controllers\Controller;
use App\Models\Market\Brand;
use App\Models\Market\Category;
use App\Models\Market\Product;
use Illuminate\Http\Request;

class FilterProductsController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/products",
     *     summary="Get available brands and categories",
     *     description="Returns a list of brands and top-level categories.",
     *     tags={"Products"},
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             @OA\Property(property="brands", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="original_name", type="string", example="Nike"),
     *                 @OA\Property(property="persian_name", type="string", example="Nike"),
     *             )),
     *             @OA\Property(property="categories", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Electronics")
     *             ))
     *         )
     *     )
     * )
     */
    public function options()
    {
        // get brands
        $brands = Brand::select('id', 'original_name', 'persian_name')->orderBy('original_name')->get()->makeHidden(['status_value']);
        // get categories
        $categories = Category::whereNull('parent_id')->select('id', 'name')->get()->makeHidden(['status_value','show_in_menu_value']);
        return response()->json([
            'brands' => $brands,
            'categories' => $categories
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/products/filter/{category:slug?}",
     *     summary="Get filtered products",
     *     description="Returns a paginated list of products based on filters such as category, price range, and brands.",
     *     tags={"Products"},
     *     @OA\Parameter(
     *         name="category:slug?",
     *         in="path",
     *         description="Optional Category Slug to filter products",
     *         required=false,
     *         @OA\Schema(type="string", example="کالای-دیجیتال")
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="search products",
     *         required=false,
     *         @OA\Schema(type="string", example="سامسونگ")
     *     ),
     *     @OA\Parameter(
     *         name="min_price",
     *         in="query",
     *         description="Minimum price filter",
     *         required=false,
     *         @OA\Schema(type="number", format="float", example=100.00)
     *     ),
     *     @OA\Parameter(
     *         name="max_price",
     *         in="query",
     *         description="Maximum price filter",
     *         required=false,
     *         @OA\Schema(type="number", format="float", example=500.00)
     *     ),
     *     @OA\Parameter(
     *         name="brands[]",
     *         in="query",
     *         description="Array of brand IDs to filter products",
     *         required=false,
     *         @OA\Schema(type="array", @OA\Items(type="integer", example=1)),
     *         example={1,2,3}
     *     ),
     *     @OA\Parameter(
     *         name="sort",
     *         in="query",
     *         description="Sorting option: 1=Newest, 2=Price High-Low, 3=Price Low-High, 4=Most Viewed, 5=Best Selling",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="products", type="object", ref="#/components/schemas/Product"),
     *             )),
     *             @OA\Property(property="pagination", type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="total_pages", type="integer", example=5),
     *                 @OA\Property(property="total_items", type="integer", example=50)
     *             )
     *         )
     *     )
     * )
     */
    public function products(Request $request, Category $category = null)
    {
        // set category
        if ($category)
            $productModel = $category->products();
        else
            $productModel = new Product();

        // validate requests
        $request->validate([
            'min_price' => 'nullable|numeric',
            'max_price' => 'nullable|numeric',
            'brands.*' => 'nullable|exists:brands,id',
        ]);

        // switch for set sort filtering
        switch ($request->sort) {
            case "1":
                $colomn = "created_at";
                $direction = "DESC";
                break;
            case "2":
                $colomn = "price";
                $direction = "DESC";
                break;
            case "3":
                $colomn = "price";
                $direction = "ASC";
                break;
            case "4":
                $colomn = "view";
                $direction = "DESC";
                break;
            case "5":
                $colomn = "sold_number";
                $direction = "DESC";
                break;
            default:
                $colomn = "created_at";
                $direction = "ASC";

        }
        // get queries
        if ($request->search) {
            $query = $productModel->where('name', 'LIKE', "%" . $request->search . "%")->orderBy($colomn, $direction);
        } else {
            $query = $productModel->orderBy($colomn, $direction);
        }
        $products = $request->max_price && $request->min_price ? $query->whereBetween('price', [$request->min_price, $request->max_price]) :
            $query->when($request->min_price, function ($query) use ($request) {
                $query->where('price', '>=', $request->min_price)->get();
            })->when($request->max_price, function ($query) use ($request) {
                $query->where('price', '<=', $request->max_price)->get();
            })->when(!($request->max_price && $request->min_price), function ($query) {
                $query->get();
            });
        $products = $products->when($request->brands, function () use ($request, $products) {
            $products->whereIn('brand_id', $request->brands);
        });

        $products = $products->paginate(12);
        $products->appends($request->query());
        return response()->json([
            'data' => $products
        ], 200);
    }

}
