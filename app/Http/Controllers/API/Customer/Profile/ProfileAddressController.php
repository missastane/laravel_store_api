<?php

namespace App\Http\Controllers\API\Customer\Profile;

use App\Http\Controllers\Controller;
use App\Models\User\City;
use App\Models\User\Province;
use Illuminate\Http\Request;

class ProfileAddressController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/my-addresses/options",
     *     summary="Get necessary options for Fram Address forms",
     *     description="This endpoint returns all `Provinces` and `Citiies` which can be used to create a new Address or edit an address",
     *     tags={"Profile", "ProfileAddress/Form"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successfully fetched `Provinces` and `Citiies` that you may need to make create or edit form",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                  @OA\Items(
     *                 @OA\Property(
     *                     property="provinces",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer"),
     *                         @OA\Property(property="name", type="string")
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="cities",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer"),
     *                         @OA\Property(property="name", type="string"),
     *                         @OA\Property(property="province_id", type="integer"),
     *                     )
     *                 ),
     *              )
     *           )
     *        )
     *    )
     * )
     */
    public function options()
    {
        $provinces = Province::select('id','name')->get();
        $cities = City::select('id','name','province_id')->get();
        $cities->makeVisible('province_id');
        return response()->json([
            'data' => [
                'provinces' => $provinces,
                'cities' => $cities
            ]
        ], 200);
    }
}
