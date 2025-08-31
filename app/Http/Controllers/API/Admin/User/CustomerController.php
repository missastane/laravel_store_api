<?php

namespace App\Http\Controllers\API\Admin\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\User\CustomerRequest;
use App\Http\Services\Image\ImageService;
use App\Models\User;
use App\Notifications\NewUserRegister;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class CustomerController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/admin/user/customer",
     *     summary="Retrieve list of Customers",
     *     description="Retrieve list of all `Customers`",
     *     tags={"Customer"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *     @OA\Response(
     *         response=200,
     *         description="A list of Customers",
     *         @OA\JsonContent(type="array", 
     *             @OA\Items(
     *             ref="#/components/schemas/User")
     *         )
     *     )  
     *  )
     */
    public function index()
    {
        $customers = User::where('user_type', 2)->orderBy('created_at', 'desc')->simplePaginate(15);
        return response()->json([
            'data' => $customers
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/user/customer/search",
     *     summary="Searchs among Customers by first name or last name",
     *     description="This endpoint allows users to search for `Customers` by first name or last name. The search is case-insensitive and returns results that contain the given keyword. The results are paginated for better performance",
     *     tags={"Customer"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *   @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="typefirst name or last name of Customer which you're searching for",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="A list of Customers",
     *          @OA\JsonContent(type="array", 
     *             @OA\Items(
     *            ref="#/components/schemas/User"
     *         ),
     *      )
     *   )
     * )
     */

    public function search(Request $request)
    {
        $customers = User::where('user_type', 2)->where(function ($query) use ($request) {
            $query->where('first_name', 'LIKE', "%" . $request->search . "%")->orWhere('last_name', 'LIKE', "%" . $request->search . "%");
        })->orderBy('last_name')->simplePaginate(15);
        return response()->json([
            'data' => $customers
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/user/customer/show/{customer}",
     *     summary="Get details of a specific Customer",
     *     description="Returns the `Customer` details and provide details for edit method.",
     *     operationId="getCustomerDetails",
     *     tags={"Customer", "Customer/Form"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="customer",
     *         in="path",
     *         description="ID of the Customer to fetch",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully fetched Customer details for editing",
     *         @OA\JsonContent(type="object", ref="#/components/schemas/User")
     *      )
     *   )
     */
    public function show(User $customer)
    {
        return response()->json([
            'data' => $customer
        ], 200);
    }
    /**
     * @OA\Post(
     *     path="/api/admin/user/customer/store",
     *     summary="Create new Customer",
     *     description="This method creates a new `Customer` and stores it.",
     *     tags={"Customer"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(type="object",
     *             @OA\Property(property="first_name", type="string", pattern="^[a-zA-Z\u0600-\u06FF0-9\ ]+$", description="This field can only contain Persian and English letters and space. Any other characters will result in a validation error.", example="ایمان"),
     *             @OA\Property(property="last_name", type="string", pattern="^[a-zA-Z\u0600-\u06FF0-9\ ]+$", description="This field can only contain Persian and English letters and space. Any other characters will result in a validation error.", example="مدائنی"),
     *             @OA\Property(property="password", type="string", example="S0h@6482"),
     *             @OA\Property(property="password_confirmation", type="string", example="S0h@6482"),
     *             @OA\Property(property="email", type="string", example="example@gmail.com"),
     *             @OA\Property(property="mobile", type="string", example="09123654789"),
     *             @OA\Property(property="profile_photo_path",type="string",format="binary"),
     *             @OA\Property(
     *                 property="activation",
     *                 oneOf={
     *                     @OA\Schema(type="integer", example=1, description="1 = active"),
     *                     @OA\Schema(type="integer", example=2, description="2 = inactive")
     *                 }
     *             ),
     *                       )
     *             )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="successful Customer creation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="true"),
     *             @OA\Property(property="message", type="string", example="فلانی با موفقیت افزوده شد")
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
    public function store(CustomerRequest $request, ImageService $imageService)
    {
        try {
            $inputs = $request->all();
            if ($request->hasFile('profile_photo_path')) {
                $imageService->setExclusiveDirectory('images' . DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'customers');
                $result = $imageService->save($request->file('profile_photo_path'));
                if ($result === false) {
                    return response()->json([
                        'status' => false,
                        'message' => 'بارگذاری عکس با خطا مواجه شد'
                    ], 422);

                }
                $inputs['profile_photo_path'] = $result;
            }
            $inputs['password'] = Hash::make($request['password']);
            $inputs['user_type'] = 2;
            $inputs['status'] = 1;
            $customer = User::create($inputs);
            $details = ['message' => 'یک کاربر جدید در سایت ثبت نام شد'];
            $adminUser = User::find(1);
            $adminUser->notify(new NewUserRegister($details));
            return response()->json([
                'status' => true,
                'message' => $customer->fullName . ' با موفقیت افزوده شد'
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'خطایی غیرمنتظره در سرور رخ داده است است. لطفا دوباره تلاش کنید'
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/admin/user/customer/status/{customer}",
     *     summary="Change the status of a Customer",
     *     description="This endpoint `toggles the status of a Customer` (active/inactive)",
     *     operationId="updateCustomerStatus",
     *     security={{"bearerAuth": {}}},
     *     tags={"Customer"},
     *     @OA\Parameter(
     *         name="customer",
     *         in="path",
     *         description="Customer id to change the status",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     security={ {"bearerAuth": {}} },
     *     @OA\Response(
     *         response=200,
     *         description="Customer status updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="checked", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="وضعیت فلانی با موفقیت فعال شد")
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
    public function status(User $customer)
    {
        $customer->status = $customer->status == 1 ? 2 : 1;
        $result = $customer->save();
        if ($result) {
            if ($customer->status == 1) {
                return response()->json([
                    'status' => true,
                    'checked' => true,
                    'message' => 'وضعیت ' . $customer->fullName . ' با موفقیت فعال شد'
                ]);
            } else {
                return response()->json([
                    'status' => true,
                    'checked' => false,
                    'message' => 'وضعیت ' . $customer->fullName . ' با موفقیت غیرفعال شد'
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
     * @OA\Get(
     *     path="/api/admin/user/customer/activation/{customer}",
     *     summary="Change the activation of a Customer",
     *     description="This endpoint `toggles the activation of a Customer` (active/inactive)",
     *     operationId="updateCustomerActivation",
     *     security={{"bearerAuth": {}}},
     *     tags={"Customer"},
     *     @OA\Parameter(
     *         name="customer",
     *         in="path",
     *         description="Customer id to change the activation",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     security={ {"bearerAuth": {}} },
     *     @OA\Response(
     *         response=200,
     *         description="Customer activation state updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="checked", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="وضعیت فعالسازی فلانی با موفقیت فعال شد")
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
    public function activation(User $customer)
    {
        $customer->activation = $customer->activation == 1 ? 2 : 1;
        $result = $customer->save();
        if ($result) {
            if ($customer->activation == 1) {
                return response()->json([
                    'status' => true,
                    'checked' => true,
                    'message' => 'وضعیت فعالسازی ' . $customer->fullName . ' با موفقیت فعال شد'
                ]);
            } else {
                return response()->json([
                    'status' => true,
                    'checked' => false,
                    'message' => 'وضعیت فعالسازی ' . $customer->fullName . ' با موفقیت غیرفعال شد'
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
     *     path="/api/admin/user/customer/update/{customer}",
     *     summary="Update an existing Customer",
     *     description="This method Update an existing `Customer` and stores it.",
     *     tags={"Customer"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="customer",
     *         in="path",
     *         description="Customer id to fetch",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(type="object",
     *             @OA\Property(property="first_name", type="string", pattern="^[a-zA-Z\u0600-\u06FF0-9\ ]+$", description="This field can only contain Persian and English letters and space. Any other characters will result in a validation error.", example="ایمان"),
     *             @OA\Property(property="last_name", type="string", pattern="^[a-zA-Z\u0600-\u06FF0-9\ ]+$", description="This field can only contain Persian and English letters and space. Any other characters will result in a validation error.", example="مدائنی"),
     *             @OA\Property(property="profile_photo_path",type="string",format="binary"),
     *             @OA\Property(
     *                 property="activation",
     *                 oneOf={
     *                     @OA\Schema(type="integer", example=1, description="1 = active"),
     *                     @OA\Schema(type="integer", example=2, description="2 = inactive")
     *                 }
     *             ),
     *             @OA\Property(property="_method",type="string",example="PUT"),
     *                       )
     *             )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="successful Customer update",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="true"),
     *             @OA\Property(property="message", type="string", example="فلانی با موفقیت بروزرسانی شد")
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
     *   )
     * )
     */
    public function update(CustomerRequest $request, ImageService $imageService, User $customer)
    {
        try {
            $inputs = $request->all();
            if ($request->hasFile('profile_photo_path')) {
                if (!empty($customer->profile_photo_path)) {
                    $imageService->deleteImage($customer->profile_photo_path);
                }
                $imageService->setExclusiveDirectory('images' . DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'customers');
                $result = $imageService->save($request->file('profile_photo_path'));
                if ($result === false) {
                    return response()->json([
                        'status' => false,
                        'message' => 'بارگذاری عکس با خطا مواجه شد'
                    ], 422);

                }
                $inputs['profile_photo_path'] = $result;
            }

            $update = $customer->update($inputs);
            return response()->json([
                'status' => true,
                'message' => $customer->fullName . ' با موفقیت بروزرسانی شد'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'خطایی غیرمنتظره در سرور رخ داده است. لطفا دوباره تلاش کنید'
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/admin/user/customer/destroy/{customer}",
     *     summary="Delete a Customer",
     *     description="This endpoint allows the user to `delete an existing Customer`.",
     *     operationId="deleteCustomer",
     *     tags={"Customer"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="customer",
     *         in="path",
     *         description="The ID of the Customer to be deleted",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Customer deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="فلانی با موفقیت حذف شد")
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
    public function destroy(User $customer)
    {
        try {
            $customer->delete();
            return response()->json([
                'status' => true,
                'message' => $customer->fullName . ' با موفقیت حذف شد'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'عملیات با خطا مواجه شد. لطفا دوباره امتحان کنید'
            ], 500);
        }
    }
}
