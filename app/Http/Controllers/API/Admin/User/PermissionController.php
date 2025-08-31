<?php

namespace App\Http\Controllers\API\Admin\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\User\PermissionRequest;
use App\Models\User\Permission;
use Exception;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/admin/user/permission",
     *     summary="Retrieve list of Permissions",
     *     description="Retrieve list of all `Permissions`",
     *     tags={"Permission"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *     @OA\Response(
     *         response=200,
     *         description="A list of Permissions",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="array",
     *                 @OA\Items(
     *                     ref="#/components/schemas/Permission"
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function index()
    {
        $permissions = Permission::with('roles:id,name')->orderBy('created_at', 'desc')->simplePaginate(15);
        $permissions->getCollection()->each(function ($item) {
            $item->roles->makeHidden('status_value');
        });
        return response()->json([
            'data' => $permissions
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/user/permission/search",
     *     summary="Searchs among Permissions by name",
     *     description="This endpoint allows users to search for `Permissions` by name. The search is case-insensitive and returns results that contain the given keyword. The results are paginated for better performance",
     *     tags={"Permission"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *   @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="type name of Permission which you're searching for",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="A list of Permissions",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="array",
     *                 @OA\Items(
     *                     ref="#/components/schemas/Permission"
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function search(Request $request)
    {
        $permissions = Permission::where('name', 'LIKE', "%" . $request->search . "%")->orWhere('description', 'LIKE', "%" . $request->search . "%")->with('roles:id,name')->orderBy('name')->simplePaginate(15);
        $permissions->getCollection()->each(function ($item) {
            $item->roles->makeHidden('status_value');
        });
        return response()->json([
            'data' => $permissions
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/user/permission/show/{permission}",
     *     summary="Get details of a specific Permission",
     *     description="Returns the `Permission` details and provide details for edit method.",
     *     operationId="getPermissionDetails",
     *     tags={"Permission", "Permission/Form"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="permission",
     *         in="path",
     *         description="ID of the Permission to fetch",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully fetched Permission details for editing",
     *       @OA\JsonContent(type="object", ref="#/components/schemas/Permission"),
     *     )
     * )
     */
    public function show(Permission $permission)
    {
        $permission->load('roles:id,name');
        $permission->roles->makeHidden('status_value');
        return response()->json([
            'data' => $permission
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/admin/user/permission/store",
     *     summary="create new Permission",
     *     description="this method creates a new `Permission` and stores it.",
     *     tags={"Permission"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *             @OA\Property(property="name", type="string", pattern="^[a-zA-Z\u0600-\u06FF0-9\s\-\.\,]+$", description="This field can only contain Persian and English letters, Persian and English numbers, and symbols (-.,). Any other characters will result in a validation error.", example="delete-post"),
     *             @OA\Property(property="description", type="string", pattern="^[a-zA-Z\u0600-\u06FF0-9\s\-\.\,\!\?]+$", description="This field can only contain Persian and English letters, Persian and English numbers, and symbols (-.,!?). Any other characters will result in a validation error.", example="description-delete-post"),
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
     *         response=201,
     *         description="successful Permission creation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="true"),
     *             @OA\Property(property="message", type="string", example="دسترسی با نام x با موفقیت افزوده شد")
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
     *)
     * )
     */
    public function store(PermissionRequest $request)
    {
        try {
            $inputs = $request->all();
            $permission = Permission::create($inputs);
            return response()->json([
                'status' => true,
                'message' => 'دسترسی با نام ' . $permission->name . '  با موفقیت افزوده شد'
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
     *     path="/api/admin/user/permission/status/{permission}",
     *     summary="Change the status of a Permission",
     *     description="This endpoint `toggles the status of a Permission` (active/inactive)",
     *     operationId="updatePermissionStatus",
     *     security={{"bearerAuth": {}}},
     *     tags={"Permission"},
     *     @OA\Parameter(
     *         name="permission",
     *         in="path",
     *         description="Permission id to change the status",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     security={ {"bearerAuth": {}} },
     *     @OA\Response(
     *         response=200,
     *         description="Permission status updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="checked", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="وضعیت x با موفقیت فعال شد")
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
    public function status(Permission $permission)
    {
        $permission->status = $permission->status == 1 ? 2 : 1;
        $result = $permission->save();
        if ($result) {
            if ($permission->status == 1) {
                return response()->json([
                    'status' => true,
                    'checked' => true,
                    'message' => 'وضعیت ' . $permission->name . ' با موفقیت فعال شد'
                ]);
            } else {
                return response()->json([
                    'status' => true,
                    'checked' => false,
                    'message' => 'وضعیت ' . $permission->name . ' با موفقیت غیرفعال شد'
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
     * @OA\Put(
     *     path="/api/admin/user/permission/update/{permission}",
     *     summary="Update an existing Permission",
     *     description="this method Updates an existing `Permission` and stores it.",
     *     tags={"Permission"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="permission",
     *         in="path",
     *         description="Permission id to fetch",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *             @OA\Property(property="name", type="string", pattern="^[a-zA-Z\u0600-\u06FF0-9\s\-\.\,]+$", description="This field can only contain Persian and English letters, Persian and English numbers, and symbols (-.,). Any other characters will result in a validation error.", example="delete-post"),
     *             @OA\Property(property="description", type="string", pattern="^[a-zA-Z\u0600-\u06FF0-9\s\-\.\,\!\?]+$", description="This field can only contain Persian and English letters, Persian and English numbers, and symbols (-.,!?). Any other characters will result in a validation error.", example="description-delete-post"),
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
     *         description="successful Permission update",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="true"),
     *             @OA\Property(property="message", type="string", example="دسترسی با نام x با موفقیت بروزرسانی شد")
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
     *)
     * )
     */
    public function update(PermissionRequest $request, Permission $permission)
    {
        try {
            $inputs = $request->all();
            $result = $permission->update($inputs);
            return response()->json([
                'status' => true,
                'message' => 'دسترسی با نام ' . $permission->name . '  با موفقیت بروزرسانی شد'
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
     *     path="/api/admin/user/permission/destroy/{permission}",
     *     summary="Delete a Permission",
     *     description="This endpoint allows the user to `delete an existing Permission`.",
     *     operationId="deletePermission",
     *     tags={"Permission"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="permission",
     *         in="path",
     *         description="The ID of the Permission to be deleted",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Permission deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="دسترسی با نام Example با موفقیت حذف شد")
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
    public function destroy(Permission $permission)
    {
        try {
            $result = $permission->delete();
            return response()->json([
                'status' => true,
                'message' => 'دسترسی با نام ' . $permission->name . '  با موفقیت حذف شد'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'عملیات با خطا مواجه شد. لطفا دوباره امتحان کنید'
            ], 500);
        }
    }
}
