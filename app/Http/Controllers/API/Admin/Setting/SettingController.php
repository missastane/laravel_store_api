<?php

namespace App\Http\Controllers\API\Admin\Setting;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Setting\SettingRequest;
use App\Http\Services\Image\ImageService;
use App\Models\Setting\Setting;
use App\Models\Tag;
use Database\Seeders\SettingSeeder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SettingController extends Controller
{
     /**
     * @OA\Get(
     *     path="/api/admin/setting",
     *     summary="Retrieve Setting Details",
     *     description="Retrieve `Setting Detail`",
     *     tags={"Setting"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *     @OA\Response(
     *         response=200,
     *         description="Details Of site Setting",
     *         @OA\JsonContent(ref="#/components/schemas/Setting"),
     *     )
     * )
     */
    public function index()
    {
        $setting = Setting::first(); {
            $default = new SettingSeeder();
            $default->run();
            $setting = Setting::first();
        }
        return response()->json([
            'data' => $setting->load('keywords')
        ], 200);
    }

     /**
     * @OA\Post(
     *     path="/api/admin/setting/update",
     *     summary="Create an new or Update an existing Setting",
     *     description="this method Create an new or update an existing `Setting` and stores it.",
     *     tags={"Setting"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *             @OA\Property(property="title", type="string", pattern="^[a-zA-Z\u0600-\u06FF0-9\s\-\.\,]+$", description="This field can only contain Persian and English letters, Persian and English numbers, and symbols (-.,). Any other characters will result in a validation error.", example="آمازون"),
     *             @OA\Property(property="description", type="string", example="توضیحات آمازون"),
     *             @OA\Property(property="icon", type="string", format="binary"),
     *             @OA\Property(property="logo", type="string", format="binary"),
     *             @OA\Property(
     *                 property="keywords[]",
     *                 type="array",
     *                 @OA\Items(type="string",pattern="^[a-zA-Z\u0600-\u06FF0-9\s\-\.\?]+$", example="آیا api خوب است؟"),
     *              description="This field can only contain Persian and English letters, Persian and English numbers, hyphens (-),question marks (?), and periods (.). Any other characters will result in a validation error.",
     *             ),
     *             @OA\Property(property="_method", type="string", example="PUT"),
     *                       ),
     *             encoding={
     *                 "tags[]": {
     *                     "style": "form",
     *                     "explode": true
     *                 }
     *             }
     *             )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="successful Setting update",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="true"),
     *             @OA\Property(property="message", type="string", example="تنظیمات با موفقیت بروزرسانی شد")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="invalid request",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="عنوان الزامی است")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="خطای غیرمنتظره در سرور رخ داده است. لطفاً دوباره تلاش کنید.")
     *         )
     *     )
     * )
     */
    public function update(SettingRequest $request, ImageService $imageService)
    {
        try {
            DB::beginTransaction();
            $setting = Setting::first();
            $inputs = $request->all();
            if ($request->hasFile('icon')) {

                if (!empty($setting->icon)) {
                    $imageService->deleteImage($setting->icon);
                }

                $imageService->setExclusiveDirectory('images' . DIRECTORY_SEPARATOR . 'setting');
                $imageService->setImageName('icon');
                $icon = $imageService->save($request->file('icon'));

                if ($icon === false) {
                    return response()->json([
                        'status' => false,
                        'message' => 'بارگذاری آیکن با خطا مواجه شد'
                    ], 422);

                }
                $inputs['icon'] = $icon;
            }

            if (isset($inputs['icon']) && !empty($setting->icon)) {
                $icon = $setting->icon;
                $inputs['icon'] = $icon;
            }

            if ($request->hasFile('logo')) {

                if (!empty($setting->logo)) {
                    $imageService->deleteImage($setting->logo);
                }

                $imageService->setExclusiveDirectory('images' . DIRECTORY_SEPARATOR . 'setting');
                $imageService->setImageName('logo');
                $logo = $imageService->save($request->file('logo'));

                if ($logo === false) {
                    return response()->json([
                        'status' => true,
                        'message' => 'بارگذاری لوگو با خطا مواجه شد'
                    ], 422);

                }
                $inputs['logo'] = $logo;
            }

            if (isset($inputs['logo']) && !empty($setting->logo)) {
                $logo = $setting->logo;
                $inputs['logo'] = $logo;
            }

            $setting->update($inputs);
            if ($request->has('keywords')) {
                $keywordIds = [];
                foreach ($request->keywords as $keywordName) {
                    $keyword = Tag::firstOrCreate(['name' => $keywordName]);
                    $keywordIds[] = $keyword->id;
                }

                $setting->keywords()->sync($keywordIds);
            }
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'تنظیمات با موفقیت بروزرسانی شد'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'عملیات با خطا مواجه شد. لطفا دوباره امتحان کنید'
            ], 500);

        }
    }
}
