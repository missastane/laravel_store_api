<?php

namespace App\Http\Controllers\API\Customer\SalesProcess;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\SalesProcess\AddressRequest;
use App\Http\Requests\Customer\SalesProcess\ChooseAddressAndDeliveryRequest;
use App\Models\Market\CartItem;
use App\Models\Market\CommonDiscount;
use App\Models\Market\Delivery;
use App\Models\Market\Order;
use App\Models\User\Address;
use App\Models\User\City;
use App\Models\User\Province;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AddressController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/address-and-delivery/options",
     *     summary="Get necessary options for Address and delivery choose forms",
     *     description="This endpoint returns all authenticated user `CartItems` and `Provinces` and `Citiies` and `Delivery Methods` which can be used to Address and delivery choose forms",
     *     tags={"SaleProcess", "AddressAndDeliveryChoose/Form","AddressAndDelivery","Profile"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successfully fetched authenticated user `CartItems` and `Provinces` and `Citiies` and `Delivery Methods that you may need to make Address and delivery choose forms",
     *         @OA\JsonContent(
     *             @OA\Property(property="data",type="array",
     *            @OA\Items(
     *                @OA\Property(property="cartItems",type="array", 
     *                    @OA\Items(type="object",ref="#/components/schemas/CartItem")
     *                ),
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
     *                   @OA\Property(
     *                     property="deliveryMethods",
     *                     type="array",
     *                     @OA\Items(type="object",ref="#/components/schemas/Delivery")
     *                 ),
     *              )
     *           )
     *        )
     *    )
     * )
     */
    public function options()
    {
        // check profile info
        $user = Auth::user();
        $cartItems = CartItem::where('user_id', $user->id)->with('user:id,first_name,last_name', 'product:id,name,image,slug', 'color:id,name', 'guarantee:id,name')->simplePaginate(15);
        $cartItems->getCollection()->each(function ($item) {
            $item->product->makeHidden(['status_value', 'marketable_value']);
            $item->color->makeHidden(['status_value']);
            $item->guarantee->makeHidden(['status_value']);
        });
        $provinces = Province::select('id', 'name')->get();
        $cities = City::select('id', 'name', 'province_id')->get();
        $cities->makeVisible('province_id');
        $deliveryMethods = Delivery::where('status', 1)->get();
        return response()->json([
            'data' => [
                'cartItems' => $cartItems,
                'provinces' => $provinces,
                'cities' => $cities,
                'deliveryMethods' => $deliveryMethods
            ]
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/add-address",
     *     summary="create new address",
     *     description="this method creates a new `Address` and stores it.",
     *     tags={"SaleProcess","AddressAndDelivery","Profile"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *             @OA\Property(property="province_id",description="ProvinceID.This field is required when creating or updating the address.", type="integer", example=5),
     *             @OA\Property(property="city_id",description="CityID.This field is required when creating or updating the address.", type="integer", example=5),
     *             @OA\Property(property="no", type="string", example="19"),
     *             @OA\Property(property="unit", type="string",example="1"),
     *             @OA\Property(property="postal_code", type="string", example="4441775584"),
     *             @OA\Property(property="address", type="string",maximum="300", example="خ شهید نواب صفوی - ک شهید وزوایی"),
     *             @OA\Property(property="receiver", type="boolean", description="if this field is true, these fileds will be required : recipient_first_name, recipient_last_name and mobile", example="true"),
     *             @OA\Property(property="recipient_first_name", type="string", example="ایمان"),
     *             @OA\Property(property="recipient_last_name", type="string", example="مدائنی"),
     *             @OA\Property(property="mobile", type="string", example="09112563489"),
     *                       )
     *             )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="successful address and tags creation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="true"),
     *             @OA\Property(property="message", type="string", example="آدرس جدید با موفقیت ثبت شد")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="invalid request",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="آدرس الزامی است")
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
    public function addAddress(AddressRequest $request)
    {
        try {
            $inputs = $request->all();
            $inputs['user_id'] = auth()->user()->id;
            $inputs['postal_code'] = convertArabicToEnglish($request->postal_code);
            $inputs['postal_code'] = convertPersianToEnglish($inputs['postal_code']);
            $address = Address::create($inputs);
            return response()->json([
                'status' => true,
                'message',
                'آدرس جدید با موفقیت ثبت شد'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'خطایی غیرمنتظره در سرور رخ داده است. لطفا دوباره تلاش کنید',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/get-cities/{province}",
     *     summary="Get necessary cities of a province for Load in address create or edit forms",
     *     description="This endpoint returns all `Cities of a province` which can be used to create or edit an address",
     *     tags={"SaleProcess", "ProfileAddress/Form","Profile"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="province",
     *         in="path",
     *         description="ID of the province to fetch",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully fetched province cities that you may need to make create or edit form",
     *         @OA\JsonContent(
     *         @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="cities",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer",example=1),
     *                         @OA\Property(property="name", type="string", example="آستانه اشرفیه")
     *                     )
     *                 ),
     *             )
     *         )
     *     ),
     *    @OA\Response(
     *         response=204,
     *         description="No Content",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="cities", type="object", example="null")
     *         )
     *     )
     * )
     */
    public function getCities(Province $province)
    {
        $cities = $province->cities->makeHidden(['created_at','updated_at','deleted_at']);

        if ($cities != null) {
            return response()->json([
                'status' => true,
                'data' => $cities
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'cities' => null
            ], 204);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/update-address/{address}",
     *     summary="update an existing address",
     *     description="this method update an existing `Address` and stores it.",
     *     tags={"SaleProcess","Profile"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="address",
     *         in="path",
     *         description="Address id to fetch",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *            @OA\Property(property="province_id",description="ProvinceID.This field is required when creating or updating the address.", type="integer", example=5),
     *             @OA\Property(property="city_id",description="CityID.This field is required when creating or updating the address.", type="integer", example=5),
     *             @OA\Property(property="no", type="string", example="19"),
     *             @OA\Property(property="unit", type="string",example="1"),
     *             @OA\Property(property="postal_code", type="string", example="4441775584"),
     *             @OA\Property(property="address", type="string",maximum="300", example="خ شهید نواب صفوی - ک شهید وزوایی"),
     *             @OA\Property(property="receiver", type="boolean", description="if this field is true, these fileds will be required : recipient_first_name, recipient_last_name and mobile", example="true"),
     *             @OA\Property(property="recipient_first_name", type="string", example="ایمان"),
     *             @OA\Property(property="recipient_last_name", type="string", example="مدائنی"),
     *             @OA\Property(property="mobile", type="string", example="09112563489"),
     *                       )
     *             )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="successful address update",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="true"),
     *             @OA\Property(property="message", type="string", example="آدرس با موفقیت بروزرسانی شد")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="invalid request",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="آدرس الزامی است")
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
    public function updateAddress(Address $address, AddressRequest $request)
    {
        try {
            $inputs = $request->all();
            $inputs['user_id'] = auth()->user()->id;
            $inputs['postal_code'] = convertArabicToEnglish($request->postal_code);
            $inputs['postal_code'] = convertPersianToEnglish($inputs['postal_code']);
            $address->update($inputs);
            return response()->json([
                'status' => true,
                'message' => 'آدرس با موفقیت بروزرسانی شد'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'خطایی غیرمنتظره در سرور رخ داده است. لطفا دوباره تلاش کنید'
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/choose-address-and-delivery",
     *     summary="Choose Address and Delivery Method",
     *     description="Allows the user to select an address and delivery method. The total price of the cart is calculated, and the order is created or updated accordingly.",
     *     operationId="chooseAddressAndDelivery",
     *     tags={"SaleProcess"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"address_id", "delivery_id"},
     *             @OA\Property(property="address_id", type="integer", example=1, description="Selected address ID"),
     *             @OA\Property(property="delivery_id", type="integer", example=2, description="Selected delivery method ID")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order successfully created or updated.",
     *         @OA\JsonContent(
     *          @OA\Property(property="status", type="bool", example="true"),
     *             @OA\Property(property="message", type="string", example="آدرس و روش ارسال سفارش شما با موفقیت ثبت شد")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="An unexpected error occurred on the server. Please try again later.")
     *         )
     *     )
     * )
     */
    public function chooseAddressAndDelivery(ChooseAddressAndDeliveryRequest $request)
    {
        try {
            $user = auth()->user();

            // calculate price
            $cartItems = CartItem::where('user_id', $user->id)->get();
            $totalProductPrice = 0;
            $totalDiscount = 0;
            $totalFinalPrice = 0;
            $totalFinalDiscountPriceWithNumber = 0;
            foreach ($cartItems as $cartItem) {
                $totalProductPrice += $cartItem->cartItemProductPrice();
                $totalDiscount += $cartItem->cartItemProductDiscount();
                $totalFinalPrice += $cartItem->cartItemFinalPrice();
                $totalFinalDiscountPriceWithNumber += $cartItem->cartItemFinalDiscount();
            }

            // calculate commonDiscount
            $commonDisount = CommonDiscount::where([['status', 1], ['end_date', '>', now()], ['start_date', '<', now()]])->first();
            if ($commonDisount) {
                $inputs['common_discount_id'] = $commonDisount->id;
                $inputs['common_discount_object'] = json_encode($commonDisount);
                $discountPercentagePrice = $totalFinalPrice * ($commonDisount->percentage / 100);
                if ($discountPercentagePrice > $commonDisount->discount_ceiling) {
                    $discountPercentagePrice = $commonDisount->discount_ceiling;
                }
                if ($commonDisount != null && $totalFinalPrice >= $commonDisount->minimal_order_amount) {
                    $finalPrice = $totalFinalPrice - $discountPercentagePrice;
                } else {
                    $finalPrice = $totalFinalPrice;
                }
            } else {
                $discountPercentagePrice = 0;
                $finalPrice = $totalFinalPrice;
            }
            $delivery = Delivery::find($request->delivery_id);
            $address = Address::find($request->address_id);
            $inputs['user_id'] = $user->id;
            $inputs['order_final_amount'] = $finalPrice;
            $inputs['order_discount_amount'] = $totalFinalDiscountPriceWithNumber;
            $inputs['order_common_discount_amount'] = $discountPercentagePrice;
            $inputs['order_total_products_discount_amount'] = $inputs['order_discount_amount'] + $inputs['order_common_discount_amount'];
            $inputs['delivery_id'] = $request->delivery_id;
            $inputs['delivery_object'] = $delivery;
            $inputs['delivery_amount'] = $delivery->amount;
            $inputs['address_id'] = $request->address_id;
            $inputs['address_object'] = $address;
            $inputs['copan_id'] = null;
            $inputs['order_copan_discount_amount'] = null;
            $order = Order::updateOrCreate(
                ['user_id' => $user->id, 'order_status' => 0],
                $inputs
            );
            return response()->json([
               'status' => true,
               'message' => 'آدرس و روش ارسال سفارش شما با موفقیت ثبت شد',
               'meta' => [
                'next_step' => 'redirect_to_copan_discount'
               ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'خطایی غیرمنتظره در سرور رخ داده است. لطفا دوباره تلاش کنید'
            ], 500);
        }
    }

}
