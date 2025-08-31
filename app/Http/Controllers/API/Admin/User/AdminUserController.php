<?php

namespace App\Http\Controllers\API\Admin\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\User\AdminUserRequest;
use App\Http\Services\Image\ImageService;
use App\Models\User;
use App\Models\User\Permission;
use App\Models\User\Role;
use Exception;
use Hash;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/admin/user/admin-user",
     *     summary="Retrieve list of Admins",
     *     description="Retrieve list of all `Admins`",
     *     tags={"Admin"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *     @OA\Response(
     *         response=200,
     *         description="A list of Admins",
     *         @OA\JsonContent(type="array", 
     *               @OA\Items(
     *             allOf={
     *                   @OA\Schema(ref="#/components/schemas/User"),
     *                   @OA\Schema(
     *                       @OA\Property(property="roles",type="object",
     *                           @OA\Property(property="id", type="integer", example=1),
     *                           @OA\Property(property="name", type="string", example="superadmin")
     *                     )
     *                 ),
     *                  @OA\Schema(
     *                       @OA\Property(property="permissions",type="object",
     *                           @OA\Property(property="id", type="integer", example=4),
     *                           @OA\Property(property="name", type="string", example="delete-post")
     *                     )
     *                 ),
     *              }
     *           )
     *        )
     *      )
     *   )
     * )
     */
    public function index()
    {
        $admins = User::where('user_type', 1)->with('roles:id,name', 'permissions:id,name')->orderBy('created_at', 'desc')->simplePaginate(15);
        $admins->getCollection()->each(function($item){
            $item->roles->makeHidden('status_value');
            $item->permissions->makeHidden('status_value');
        });
        return response()->json([
            'data' => $admins
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/user/admin-user/search",
     *     summary="Searchs among Admins by first name or last name",
     *     description="This endpoint allows users to search for `Admins` by first name or last name. The search is case-insensitive and returns results that contain the given keyword. The results are paginated for better performance",
     *     tags={"Admin"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *   @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="typefirst name or last name of Admin which you're searching for",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="A list of Admins",
     *          @OA\JsonContent(type="array", 
     *             @OA\Items(
     *             allOf={
     *                   @OA\Schema(ref="#/components/schemas/User"),
     *                   @OA\Schema(
     *                       @OA\Property(property="roles",type="object",
     *                           @OA\Property(property="id", type="integer", example=1),
     *                           @OA\Property(property="name", type="string", example="superadmin")
     *                     )
     *                 ),
     *                  @OA\Schema(
     *                       @OA\Property(property="permissions",type="object",
     *                           @OA\Property(property="id", type="integer", example=4),
     *                           @OA\Property(property="name", type="string", example="delete-post")
     *                     )
     *                 ),
     *            }
     *          )
     *         )
     *      )
     *   )
     * )
     */
    public function search(Request $request)
    {
        $admins = User::where('user_type', 1)->where(function ($query) use ($request) {
            $query->where('first_name', 'LIKE', "%" . $request->search . "%")->orWhere('last_name', 'LIKE', "%" . $request->search . "%");
        })->with('roles:id,name', 'permissions:id,name')->orderBy('last_name')->simplePaginate(15);
        $admins->getCollection()->each(function($item){
            $item->roles->makeHidden('status_value');
            $item->permissions->makeHidden('status_value');
        });
        return response()->json([
            'data' => $admins
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/user/admin-user/show/{admin}",
     *     summary="Get details of a specific Admin",
     *     description="Returns the `Admin` details and provide details for edit method.",
     *     operationId="getAdminDetails",
     *     tags={"Admin", "Admin/Form"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="admin",
     *         in="path",
     *         description="ID of the Admin to fetch",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully fetched Admin details for editing",
     *         @OA\JsonContent(type="object", 
     *             allOf={
     *                   @OA\Schema(ref="#/components/schemas/User"),
     *                   @OA\Schema(
     *                       @OA\Property(property="roles",type="object",
     *                           @OA\Property(property="id", type="integer", example=1),
     *                           @OA\Property(property="name", type="string", example="superadmin")
     *                     )
     *                 ),
     *                  @OA\Schema(
     *                       @OA\Property(property="permissions",type="object",
     *                           @OA\Property(property="id", type="integer", example=4),
     *                           @OA\Property(property="name", type="string", example="delete-post")
     *                     )
     *                 ),
     *            }
     *         )
     *      )
     *   )
     * )
     */
    public function show(User $admin)
    {
        $admin->load('roles:id,name', 'permissions:id,name');
        $admin->roles->makeHidden('status_value');
        $admin->permissions->makeHidden('status_value');
        return response()->json([
            'data' => $admin
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/user/admin-user/options",
     *     summary="Get necessary options for admin forms",
     *     description="This endpoint returns all `Roles` and `Permissions`, which can be used to set role or permission for admins in roleStore and permissionStore methods",
     *     tags={"Admin", "Admin/Form"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successfully fetched Permissions and Roles that you may need to set role or permission for admin forms",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="roles",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer"),
     *                         @OA\Property(property="name", type="string")
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="permissions",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer"),
     *                         @OA\Property(property="name", type="string"),
     *                     )
     *                 ),
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
        $roles = Role::select('id', 'name')->get();
        $permissions = Permission::select('id', 'name')->get();
        return response()->json([
            'data' => [
                'roles' => $roles,
                'permissions' => $permissions
            ]
        ], 200);
    }
    /**
     * @OA\Post(
     *     path="/api/admin/user/admin-user/store",
     *     summary="Create new Admin",
     *     description="This method creates a new `Admin` and stores it.",
     *     tags={"Admin"},
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
     *         description="successful Admin creation",
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
    public function store(AdminUserRequest $request, ImageService $imageService)
    {
        try {
            $inputs = $request->all();
            if ($request->hasFile('profile_photo_path')) {
                $imageService->setExclusiveDirectory('images' . DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'admins');
                $result = $imageService->save($request->file('profile_photo_path'));
                if ($result === false) {
                    return response()->json([
                        'message',
                        'بارگذاری عکس با خطا مواجه شد'
                    ], 422);

                }
                $inputs['profile_photo_path'] = $result;
            }
            $inputs['password'] = Hash::make($request['password']);
            $inputs['user_type'] = 1;
            $inputs['status'] = 1;
            $adminUser = User::create($inputs);
            return response()->json([
                'status' => true,
                'message' => $adminUser->fullName . ' با موفقیت افزوده شد'
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'خطایی غیرمنتظره در سرور رخ داده است. لطفا دوباره تلاش کنید'
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/admin/user/admin-user/status/{admin}",
     *     summary="Change the status of a Admin",
     *     description="This endpoint `toggles the status of a Admin` (active/inactive)",
     *     operationId="updateAdminStatus",
     *     security={{"bearerAuth": {}}},
     *     tags={"Admin"},
     *     @OA\Parameter(
     *         name="admin",
     *         in="path",
     *         description="Admin id to change the status",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     security={ {"bearerAuth": {}} },
     *     @OA\Response(
     *         response=200,
     *         description="Admin status updated successfully",
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
    public function status(User $admin)
    {
        $admin->status = $admin->status == 1 ? 2 : 1;
        $result = $admin->save();
        if ($result) {
            if ($admin->status == 1) {
                return response()->json([
                    'status' => true,
                    'checked' => true,
                    'message' => 'وضعیت ' . $admin->fullName . ' با موفقیت فعال شد'
                ]);
            } else {
                return response()->json([
                    'status' => true,
                    'checked' => false,
                    'message' => 'وضعیت ' . $admin->fullName . ' با موفقیت غیرفعال شد'
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
     *     path="/api/admin/user/admin-user/activation/{admin}",
     *     summary="Change the activation of a Admin",
     *     description="This endpoint `toggles the activation of a Admin` (active/inactive)",
     *     operationId="updateAdminActivation",
     *     security={{"bearerAuth": {}}},
     *     tags={"Admin"},
     *     @OA\Parameter(
     *         name="admin",
     *         in="path",
     *         description="Admin id to change the activation",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     security={ {"bearerAuth": {}} },
     *     @OA\Response(
     *         response=200,
     *         description="Admin activation state updated successfully",
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
    public function activation(User $admin)
    {
        $admin->activation = $admin->activation == 1 ? 2 : 1;
        $result = $admin->save();
        if ($result) {
            if ($admin->activation == 1) {
                return response()->json([
                    'status' => true,
                    'checked' => true,
                    'message' => 'وضعیت فعالسازی ' . $admin->fullName . ' با موفقیت فعال شد'
                ]);
            } else {
                return response()->json([
                    'status' => true,
                    'checked' => false,
                    'message' => 'وضعیت فعالسازی ' . $admin->fullName . ' با موفقیت غیرفعال شد'
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
     *     path="/api/admin/user/admin-user/update/{admin}",
     *     summary="Update an existing Admin",
     *     description="This method Update an existing `Admin` and stores it.",
     *     tags={"Admin"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="admin",
     *         in="path",
     *         description="Admin id to fetch",
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
     *         description="successful Admin update",
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
    public function update(AdminUserRequest $request, ImageService $imageService, User $admin)
    {
        try {
            $inputs = $request->all();
            if ($request->hasFile('profile_photo_path')) {
                if (!empty($admin->profile_photo_path)) {
                    $imageService->deleteImage($admin->profile_photo_path);
                }
                $imageService->setExclusiveDirectory('images' . DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'admins');
                $result = $imageService->save($request->file('profile_photo_path'));
                if ($result === false) {
                    return response()->json([
                        'status' => false,
                        'message' => 'بارگذاری عکس با خطا مواجه شد'
                    ], 422);

                }
                $inputs['profile_photo_path'] = $result;
            }

            $update = $admin->update($inputs);
            return response()->json([
                'status' => true,
                'message' => $admin->fullName . ' با موفقیت بروزرسانی شد'
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
     *     path="/api/admin/user/admin-user/destroy/{admin}",
     *     summary="Delete a Admin",
     *     description="This endpoint allows the user to `delete an existing Admin`.",
     *     operationId="deleteAdmin",
     *     tags={"Admin"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="admin",
     *         in="path",
     *         description="The ID of the Admin to be deleted",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Admin deleted successfully",
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
    public function destroy(User $admin)
    {
        try {
            $admin->delete();
            return response()->json([
                'status' => true,
                'message' => $admin->fullName . ' با موفقیت حذف شد'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'عملیات با خطا مواجه شد. لطفا دوباره امتحان کنید'
            ], 500);
        }
    }


    /**
     * @OA\Post(
     *     path="/api/admin/user/admin-user/roles/{admin}/store",
     *     summary="Update Admin Roles",
     *     description="This endpoint assigns new roles to an admin user.",
     *     operationId="rolesStore",
     *     tags={"Admin"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="admin",
     *         in="path",
     *         required=true,
     *         description="Admin user ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"roles"},
     *             @OA\Property(
     *                 property="roles",
     *                 type="array",
     *                 description="List of role IDs",
     *                 @OA\Items(type="integer", example=2)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Roles successfully updated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="نقش های ادمین با موفقیت بروزرسانی شد")
     *         )
     *     ),
     *     @OA\Response(
     *         response=405,
     *         description="Access denied",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="اجازه دسترسی به این متد را ندارید")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="عملیات با خطا مواجه شد. لطفا دوباره امتحان کنید")
     *         )
     *     )
     * )
     */
    public function rolesStore(User $admin, Request $request)
    {
        if ($admin->user_type !== 1) {
            return response()->json([
                'status' => true,
                'message' => 'اجازه دسترسی ندارید'
            ], 405);
        }
        try {
            $request->validate([
                'roles' => 'nullable|exists:roles,id|array',
                // 'g-recaptcha-response' => 'recaptcha',
            ]);
            $admin->roles()->sync($request->roles);
            return response()->json([
                'status' => true,
                'message' => 'نقش های ادمین با موفقیت بروزرسانی شد'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'عملیات با خطا مواجه شد. لطفا دوباره امتحان کنید'
            ], 500);
        }
    }

 /**
     * @OA\Post(
     *     path="/api/admin/user/admin-user/permissions/{admin}/store",
     *     summary="Update Admin Permissions",
     *     description="This endpoint assigns new Permissions to an admin user.",
     *     operationId="PermissionsStore",
     *     tags={"Admin"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="admin",
     *         in="path",
     *         required=true,
     *         description="Admin user ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"permissions"},
     *             @OA\Property(
     *                 property="permissions",
     *                 type="array",
     *                 description="List of permission IDs",
     *                 @OA\Items(type="integer", example=2)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Permissions successfully updated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="دسترسی های ادمین با موفقیت بروزرسانی شد")
     *         )
     *     ),
     *     @OA\Response(
     *         response=405,
     *         description="Access denied",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="اجازه دسترسی به این متد را ندارید")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="عملیات با خطا مواجه شد. لطفا دوباره امتحان کنید")
     *         )
     *     )
     * )
     */

    public function permissionsStore(User $admin, Request $request)
    {
        if ($admin->user_type !== 1) {
            return response()->json([
                'status' => true,
                'message' => 'اجازه دسترسی ندارید'
            ], 405);
        }
        try {
            $request->validate([
                'permissions' => 'nullable|exists:permissions,id|array',
                // 'g-recaptcha-response' => 'recaptcha',
            ]);

            $admin->permissions()->sync($request->permissions);
            return response()->json([
                'status' => true,
                'message' => 'دسترسی های ادمین با موفقیت بروزرسانی شد'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'عملیات با خطا مواجه شد. لطفا دوباره امتحان کنید'
            ], 500);
        }
    }
}
