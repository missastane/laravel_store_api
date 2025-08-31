<?php

namespace App\Http\Controllers\API\Customer;

use App\Http\Controllers\Controller;
use App\Models\Market\Product;
use Illuminate\Http\Request;

class SearchCotroller extends Controller
{
    // public function autocomplete(Request $request)
    // {
    //     if ($request->search) {
    //         $query = $request->search;
    //         $data = Product::select('category_id', 'name', 'slug')
    //             ->where('name', 'like', '%' . $query . '%')
    //             ->with('category')
    //             ->get()
    //             ->groupBy('category.name');
    //         $output = '<section class="search-result-title">نتایج جستجو برای <span class="search-words">' . $query . ' ' . '</span><span class="search-result-type">در دسته بندی ها</span></section>';


    //         if (count($data) > 0) {
    //             foreach ($data as $key => $value) {
    //                 $output .= '<section class="border-bottom p-1"><section class="text-danger text-bold">در دسته ' . $key . '</section>';
    //                 foreach ($value as $product) {
    //                     $output .= '<section class="search-result-item autocomplete-result"><a class="text-decoration-none" href="' . url('/product', $product->slug) . '"><i class="fa fa-link"></i>' . ' ' . $product->name . '</a></section>';
    //                 }
    //                 $output .= '</section>';

    //             }
    //         } else {
    //             $output .= '<section class="search-result-item"><span class="search-no-result">موردی یافت نشد</span></section>';
    //         }
    //         echo $output;
    //     }

    // }



    /**
     * @OA\Get(
     *     path="/api/autocomplete",
     *     summary="Search autocomplete for products",
     *     description="Returns a list of matching products grouped by category.",
     *     operationId="autocomplete",
     *     tags={"Search"},
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         required=true,
     *         description="Search query string",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="نتایج جستجو برای: گوشی"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 example={
     *                     "موبایل و تبلت": {
     *                         {
     *                             "category_id": 1,
     *                             "name": "Samsung Galaxy S23",
     *                             "slug": "samsung-galaxy-s23",
     *                             "category": {
     *                                 "id": 1,
     *                                 "name": "موبایل و تبلت"
     *                             }
     *                         },
     *                         {
     *                             "category_id": 1,
     *                             "name": "iPhone 14 Pro",
     *                             "slug": "iphone-14-pro",
     *                             "category": {
     *                                 "id": 1,
     *                                 "name": "موبایل و تبلت"
     *                             }
     *                         }
     *                     }
     *                 }
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request (missing search parameter)",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="پارامتر جستجو ارسال نشده است."),
     *             @OA\Property(property="data", type="array", @OA\Items(type="string"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not Fount",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="موردی یافت نشد"),
     *             @OA\Property(property="data", type="array", @OA\Items(type="string"))
     *         )
     *      )
     * )
     */
    public function autocomplete(Request $request)
    {
        if (!$request->has('search')) {
            return response()->json([
                'status' => false,
                'message' => 'پارامتر جستجو ارسال نشده است',
                'data' => []
            ], 400);
        }

        $query = $request->search;
        $data = Product::select('category_id', 'name', 'slug')
            ->where('name', 'like', '%' . $query . '%')
            ->with('category:id,name')
            ->get()->map(function($product){
                return[
                'name' => $product->name,
                'slug' => $product->slug,
                    'category' => [
                        'id' => $product->category_id,
                        'name' => $product->category->name
                    ]
                ];
            })
            ->groupBy('category.name');

            
            // $data->category->makeHidden(['status_value']);
        if ($data->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'موردی یافت نشد',
                'data' => []
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'نتایج جستجو برای: ' . $query,
            'data' => $data
        ], 200);
    }
}
