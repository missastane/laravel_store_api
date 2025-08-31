<?php

namespace App\Http\Controllers\API\Admin\Content;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Content\MenuRequest;
use App\Models\Content\Menu;
use Exception;
use Illuminate\Http\Request;


class MenuController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/admin/content/menu",
     *     summary="Retrieve list of Menus",
     *     description="Retrieve list of all `Menus`",
     *     tags={"Menu"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *     @OA\Response(
     *         response=200,
     *         description="A list of Menus with their Parent",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="array",
     *                 @OA\Items(
     *                     ref="#/components/schemas/Menu"
     *                 )
     *             )
     *         )
     *     )
     * )
     */

    public function index()
    {
        $menus = Menu::orderBy('created_at', 'desc')->with('parent:id,name')->simplePaginate(15);
        $menus->getCollection()->each(function ($item) {
            if (isset($item->parent)) {
                $item->parent->makeHidden(['status_value', 'parent']);
            }
        });
        return response()->json([
            'data' => $menus
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/content/menu/search",
     *     summary="Searchs among Menus by name",
     *     description="This endpoint allows users to search for `Menus` by name. The search is case-insensitive and returns results that contain the given keyword. The results are paginated for better performance",
     *     tags={"Menu"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *   @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="type name of Menu which you're searching for",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="A list of Menus with their Parent",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="array",
     *                 @OA\Items(
     *                     ref="#/components/schemas/Menu"
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function search(Request $request)
    {
        $menus = Menu::where('name', 'LIKE', "%" . $request->search . "%")->with('parent:id,name')->orderBy('name')->simplePaginate(15);
        $menus->getCollection()->each(function ($item) {
            if (isset($item->parent)) {
                $item->parent->makeHidden(['status_value', 'parent']);
            }
        });
        return response()->json([
            'data' => $menus
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/content/menu/show/{menu}",
     *     summary="Get details of a specific Menu",
     *     description="Returns the `Menu` details along with tags and provide details for edit method. also `parentMenus` in this method is specially provided details for edit form",
     *     operationId="getMenuDetails",
     *     tags={"Menu", "Menu/Form"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="menu",
     *         in="path",
     *         description="ID of the Menu to fetch",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully fetched Menu details with tags for editing",
     *     @OA\JsonContent(
     *         @OA\Property(
     *             property="data",
     *             type="object",
     *             @OA\Property(
     *                 property="Menu",
     *                 ref="#/components/schemas/Menu"  
     *             ),
     *             @OA\Property(
     *                 property="parentMenus",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="کالای دیجیتال")
     *                 )
     *             ),
     *         )
     *     )
     *   )
     * )
     */
    public function show(Menu $menu)
    {
        // this parentMenus will use for edit method
        $parentMenus = Menu::where('parent_id', null)->whereNot('id', $menu->id)->select(['name', 'id'])->simplePaginate(15);
        $parentMenus->getCollection()->each(function ($item) {
            $item->makeHidden(['status_value', 'parent']);
        });
        $menu->load('parent:id,name');
        if (isset($menu->parent)) {
            $menu->parent->makeHidden(['status_value', 'parent']);
        }
        return response()->json([
            'data' => [
                'menu' => $menu,
                'parentMenus' => $parentMenus
            ]
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/content/menu/options",
     *     summary="Get necessary options for Menu forms",
     *     description="This endpoint returns all `parentMenus` which can be used to create a new menu",
     *     tags={"Menu", "Menu/Form"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successfully fetched parentMenus that you may need to make create form",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="parentMenus",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer"),
     *                         @OA\Property(property="name", type="string")
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
        // this $parentMenus will use for create method
        $parentMenus = Menu::where('parent_id', null)->select(['name', 'id'])->simplePaginate(15);
        $parentMenus->getCollection()->each(function ($item) {
            $item->makeHidden(['status_value', 'parent']);
        });
        return response()->json([
            'data' => $parentMenus
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/admin/content/menu/store",
     *     summary="create new Menu",
     *     description="this method creates a new `Menu` and stores its.",
     *     tags={"Menu"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *             @OA\Property(property="name", type="string", pattern="^[a-zA-Z\u0600-\u06FF0-9\s\-\_\.\,]+$", description="This field can only contain Persian and English letters, Persian and English numbers, and symbols (-.,_). Any other characters will result in a validation error.", example="موبایل"),
     *             @OA\Property(property="url", type="string", format="url", description="a valid url", example="https://example.com"),
     *             @OA\Property(
     *                 property="status",
     *                 oneOf={
     *                     @OA\Schema(type="integer", example=1, description="1 = active"),
     *                     @OA\Schema(type="integer", example=2, description="2 = inactive")
     *                 }
     *             ),
     *           
     *             @OA\Property(property="parent_id",description="ParentID.This field is optional when creating or updating the menu.", type="integer", nullable="true", example=5),
     *                       )
     *             )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="successful menu creation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="true"),
     *             @OA\Property(property="message", type="string", example="منوی x با موفقیت افزوده شد")
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
    public function store(MenuRequest $request)
    {
        try {
            $inputs = $request->all();
            $menu = Menu::create($inputs);
            return response()->json([
                'status' => true,
                'message' => 'منوی ' . $menu->name . ' با موفقیت افزوده شد'
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
     *     path="/api/admin/content/menu/status/{menu}",
     *     summary="Change the status of a Menu",
     *     description="This endpoint `toggles the status of a Menu` (active/inactive)",
     *     operationId="updateMenuStatus",
     *     security={{"bearerAuth": {}}},
     *     tags={"Menu"},
     *     @OA\Parameter(
     *         name="menu",
     *         in="path",
     *         description="Menu id to change the status",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     security={ {"bearerAuth": {}} },
     *     @OA\Response(
     *         response=200,
     *         description="Menu status updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="checked", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="وضعیت با موفقیت فعال شد")
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
    public function status(Menu $menu)
    {
        $menu->status = $menu->status == 1 ? 2 : 1;
        $result = $menu->save();
        if ($result) {
            if ($menu->status == 1) {
                return response()->json([
                    'status' => true,
                    'checked' => true,
                    'message' => 'وضعیت با موفقیت فعال شد'
                ]);
            } else {
                return response()->json([
                    'status' => true,
                    'checked' => false,
                    'message' => 'وضعیت با موفقیت غیرفعال شد'
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
     *     path="/api/admin/content/menu/update/{menu}",
     *     summary="Update an existing Menu",
     *     description="this method update an existing `Menu` and stores it.",
     *     tags={"Menu"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="menu",
     *         in="path",
     *         description="Menu id to fetch",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *             @OA\Property(property="name", type="string", pattern="^[a-zA-Z\u0600-\u06FF0-9\s\-\_\.\,]+$", description="This field can only contain Persian and English letters, Persian and English numbers, and symbols (-.,_). Any other characters will result in a validation error.", example="موبایل"),
     *             @OA\Property(property="url", type="string", format="url", description="a valid url", example="https://example.com"),
     *             @OA\Property(
     *                 property="status",
     *                 oneOf={
     *                     @OA\Schema(type="integer", example=1, description="1 = active"),
     *                     @OA\Schema(type="integer", example=2, description="2 = inactive")
     *                 }
     *             ),
     *           
     *             @OA\Property(property="parent_id",description="ParentID.This field is optional when creating or updating the category.", type="integer", nullable="true", example=5),
     * )
     *             )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="successful menu update",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="true"),
     *             @OA\Property(property="message", type="string", example="منوی x با موفقیت بروزرسانی شد")
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
    public function update(MenuRequest $request, Menu $menu)
    {
        try {
            $inputs = $request->all();
            $result = $menu->update($inputs);
            return response()->json([
                'status' => true,
                'message' => 'منوی ' . $menu->name . ' با موفقیت بروزرسانی شد'
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
     *     path="/api/admin/content/menu/destroy/{menu}",
     *     summary="Delete a Menu",
     *     description="This endpoint allows the user to `delete an existing Menu`.",
     *     operationId="deleteMenu",
     *     tags={"Menu"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="menu",
     *         in="path",
     *         description="The ID of the Menu to be deleted",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Menu deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="منوی Example با موفقیت حذف شد")
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

    public function destroy(Menu $menu)
    {
        try {
            $result = $menu->delete();
            return response()->json([
                'status' => true,
                'message' => 'منوی ' . $menu->name . ' با موفقیت حذف شد'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'عملیات با خطا مواجه شد. لطفا دوباره امتحان کنید'
            ], 500);

        }
    }
}
