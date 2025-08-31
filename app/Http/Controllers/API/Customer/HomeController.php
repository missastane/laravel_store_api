<?php

namespace App\Http\Controllers\API\Customer;

use App\Http\Controllers\Controller;
use App\Models\Content\Banner;
use App\Models\Content\Page;
use App\Models\Market\Brand;
use App\Models\Market\Category;
use App\Models\Market\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/options",
     *     summary="Get homepage options",
     *     description="Retrieve banners, brands, and product lists for the homepage",
     *     operationId="getHomepageOptions",
     *     tags={"Homepage"},
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="slideShowImages", type="array", @OA\Items(ref="#/components/schemas/Banner")),
     *             @OA\Property(property="topBanners", type="array", @OA\Items(ref="#/components/schemas/Banner")),
     *             @OA\Property(property="middleBanners", type="array", @OA\Items(ref="#/components/schemas/Banner")),
     *             @OA\Property(property="bottomBanner", type="object", ref="#/components/schemas/Banner"),
     *             @OA\Property(property="brands", type="array", @OA\Items(ref="#/components/schemas/Brand")),
     *             @OA\Property(property="mostVisitedProducts", type="array", @OA\Items(ref="#/components/schemas/Product")),
     *             @OA\Property(property="offerProducts", type="array", @OA\Items(ref="#/components/schemas/Product"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error"
     *     )
     * )
     */
    public function options()
    {
        $slideShowImages = Banner::where('position', 0)->where('status', 1)->get();
        $topBanners = Banner::where('position', 1)->where('status', 1)->take(2)->get();
        $middleBanners = Banner::where('position', 2)->where('status', 1)->take(2)->get();
        $bottomBanner = Banner::where('position', 3)->where('status', 1)->first();
        $brands = Brand::select('id', 'original_name', 'persian_name')->get();
        $mostVisitedProducts = Product::orderBy('view', 'desc')->take(10)->get();
        $offerProducts = Product::latest()->take(10)->get();
        return response()->json([
            'slideShowImages' => $slideShowImages,
            'topBanners' => $topBanners,
            'middleBanners' => $middleBanners,
            'bottomBanner' => $bottomBanner,
            'brands' => $brands,
            'mostVisitedProducts' => $mostVisitedProducts,
            'offerProducts' => $offerProducts
        ], 200);
    }

}
