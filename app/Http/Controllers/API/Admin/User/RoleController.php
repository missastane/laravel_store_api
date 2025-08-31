<?php

namespace App\Http\Controllers\API\Admin\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\User\Permission_RoleRequest;
use App\Http\Requests\Admin\User\RoleRequest;
use App\Models\User\Permission;
use App\Models\User\Permission_Role;
use App\Models\User\Role;
use Exception;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/admin/user/role",
     *     summary="Retrieve list of Roles",
     *     description="Retrieve list of all `Roles`",
     *     tags={"Role"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *     @OA\Response(
     *         response=200,
     *         description="A list of Roles",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="array",
     *                 @OA\Items(
     *                     ref="#/components/schemas/Role"
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function index()
    {
        $roles = Role::with('permissions:id,name')->orderBy('created_at', 'desc')->simplePaginate(15);
        $roles->getCollection()->each(function ($item) {
            $item->permissions->makeHidden('status_value');
        });
        return response()->json([
            'data' => $roles
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/user/role/search",
     *     summary="Searchs among Roles by name",
     *     description="This endpoint allows users to search for `Roles` by name. The search is case-insensitive and returns results that contain the given keyword. The results are paginated for better performance",
     *     tags={"Role"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *   @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="type name of Role which you're searching for",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="A list of Roles",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="array",
     *                 @OA\Items(
     *                     ref="#/components/schemas/Role"
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function search(Request $request)
    {
        $roles = Role::where('name', 'LIKE', "%" . $request->search . "%")->with('permissions:id,name')->orderBy('name')->simplePaginate(15);
        $roles->getCollection()->each(function ($item) {
            $item->permissions->makeHidden('status_value');
        });
        return response()->json([
            'data' => $roles
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/user/role/options",
     *     summary="Get necessary options for Role forms",
     *     description="This endpoint returns all `Permissions` which can be used to create a new role and permission method",
     *     tags={"Role", "Role/Form"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successfully fetched permissions that you may need to make create form",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="permissions",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer"),
     *                         @OA\Property(property="name", type="string")
     *                     )
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function options()
    {
        $permissions = Permission::select('name', 'id')->get();
        $permissions->makeHidden('status_value');
        return response()->json([
            'data' => $permissions
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/user/role/show/{role}",
     *     summary="Get details of a specific Role",
     *     description="Returns the `Role` details and provide details for edit method. also `permissionIds` in this method is specially provided details for permission method",
     *     operationId="getRoleDetails",
     *     tags={"Role", "Role/Form"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="role",
     *         in="path",
     *         description="ID of the Role to fetch",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully fetched Role details with tags for editing",
     *     @OA\JsonContent(
     *         @OA\Property(
     *             property="data",
     *             type="object",
     *             @OA\Property(
     *                 property="Role",
     *                 ref="#/components/schemas/Role"  
     *             ),
     *             @OA\Property(
     *                 property="permissionIds",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                 )
     *              )
     *           )
     *        )
     *    )
     * )
     */
    public function show(Role $role)
    {
        $role->load('permissions:id,name');
        $role->permissions->makeHidden('status_value');
        $permissionIds = $role->permissions()->pluck('id')->toArray();
        return response()->json([
            'data' => [
                'role' => $role,
                'permissionIds' => $permissionIds
            ]
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/admin/user/role/store",
     *     summary="create new Role",
     *     description="this method creates a new `Role` and stores it.",
     *     tags={"Role"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *             @OA\Property(property="name", type="string", pattern="^[a-zA-Z\u0600-\u06FF0-9\s\-\.\,]+$", description="This field can only contain Persian and English letters, Persian and English numbers, and symbols (-.,). Any other characters will result in a validation error.", example="superadmin"),
     *             @OA\Property(property="description", type="string", pattern="^[a-zA-Z\u0600-\u06FF0-9\s\-\.\,\!\?]+$", description="This field can only contain Persian and English letters, Persian and English numbers, and symbols (-.,!?). Any other characters will result in a validation error.", example="description of superadmin"),
     *             @OA\Property(
     *                 property="status",
     *                 oneOf={
     *                     @OA\Schema(type="integer", example=1, description="1 = active"),
     *                     @OA\Schema(type="integer", example=2, description="2 = inactive")
     *                 }
     *             ),
     *            @OA\Property(
     *                 property="permission_id[]",
     *                 type="array",
     *                 @OA\Items(type="integer", example=1),
     *              description="This field can only contain integer Ids which exist in Permissions Table. Any other characters will result in a validation error.",
     *             ),
     *                       )
     *             )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="successful Role creation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="true"),
     *             @OA\Property(property="message", type="string", example="نقش x با موفقیت افزوده شد")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="invalid request",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="نام دسته بندی الزامی است")
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

    public function store(RoleRequest $request)
    {
        try {
            $inputs = $request->all();
            $role = Role::create($inputs);
            $inputs['permission_id'] = $inputs['permission_id'] ?? [];
            $role->permissions()->sync($inputs['permission_id']);
            return response()->json([
                'status' => true,
                'message' => 'نقش ' . $role->name . ' با موفقیت افزوده شد'
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'خطایی غیرمنتظره در سرور رخ داده است. لطفا دوباره تلاش کنید'
            ], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/admin/user/role/update/{role}",
     *     summary="Update an existing Role",
     *     description="this method Updates an existing `Role` and stores it.",
     *     tags={"Role"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="role",
     *         in="path",
     *         description="Role id to fetch",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *             @OA\Property(property="name", type="string", pattern="^[a-zA-Z\u0600-\u06FF0-9\s\-\.\,]+$", description="This field can only contain Persian and English letters, Persian and English numbers, and symbols (-.,). Any other characters will result in a validation error.", example="superadmin"),
     *             @OA\Property(property="description", type="string", pattern="^[a-zA-Z\u0600-\u06FF0-9\s\-\.\,\!\?]+$", description="This field can only contain Persian and English letters, Persian and English numbers, and symbols (-.,!?). Any other characters will result in a validation error.", example="description of superadmin"),
     *             @OA\Property(
     *                 property="status",
     *                 oneOf={
     *                     @OA\Schema(type="integer", example=1, description="1 = active"),
     *                     @OA\Schema(type="integer", example=2, description="2 = inactive")
     *                 }
     *             ),
     *                       )
     *             )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="successful Role update",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="true"),
     *             @OA\Property(property="message", type="string", example="نقش x با موفقیت بروزرسانی شد")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="invalid request",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="نام دسته بندی الزامی است")
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
    public function update(Role $role, RoleRequest $request)
    {
        try {
            $inputs = $request->all();
            $role->update($inputs);
            return response()->json([
                'status' => true,
                'message' => 'نقش ' . $role->name . ' با موفقیت بروزرسانی شد'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'خطایی غیرمنتظره در سرور رخ داده است. لطفا دوباره تلاش کنید'
            ], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/admin/user/role/permission/{role}",
     *     summary="Update Permissions of an existing Role",
     *     description="this method Updates Permissions of an existing `Role` and stores it.",
     *     tags={"Role"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="role",
     *         in="path",
     *         description="Role id to fetch",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *            @OA\Property(
     *                 property="permission_id",
     *                 type="array",
     *                 @OA\Items(type="integer", example=1),
     *              description="This field can only contain integer Ids which exist in Permissions Table. Any other characters will result in a validation error.",
     *             ),
     *                       )
     *             )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="successful Role's Permissions update",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="true"),
     *             @OA\Property(property="message", type="string", example="دسترسی های نقش x با موفقیت بروزرسانی شد")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="invalid request",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="نام دسته بندی الزامی است")
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

    public function permission(Role $role, RoleRequest $request)
    {
        try {
            $inputs = $request->all();
            $inputs['permission_id'] = $inputs['permission_id'] ?? [];
            $role->permissions()->sync($inputs['permission_id']);
            return response()->json([
                'status' => true,
                'message' => 'دسترسی های نقش ' . $role->name . ' با موفقیت بروزرسانی شد'
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
     *     path="/api/admin/user/role/destroy/{role}",
     *     summary="Delete a Role",
     *     description="This endpoint allows the user to `delete an existing Role`.",
     *     operationId="deleteRole",
     *     tags={"Role"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="role",
     *         in="path",
     *         description="The ID of the Role to be deleted",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Role deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="نقش Example با موفقیت حذف شد")
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
    public function destroy(Role $role)
    {
        try {
            $result = $role->delete();
            return response()->json([
                'status' => true,
                'message' => 'نقش ' . $role->name . ' با موفقیت حذف شد'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'عملیات با خطا مواجه شد. لطفا دوباره امتحان کنید'
            ], 500);
        }
    }
}
