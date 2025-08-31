<?php

use App\Http\Controllers\API\Admin\Content\BannerController;
use App\Http\Controllers\API\Admin\Content\FaqController;
use App\Http\Controllers\API\Admin\Content\MenuController;
use App\Http\Controllers\API\Admin\Content\PageController;
use App\Http\Controllers\API\Admin\Content\PostController;
use App\Http\Controllers\API\Admin\Market\BrandController;
use App\Http\Controllers\API\Admin\Market\CityController;
use App\Http\Controllers\API\Admin\Market\CommentController;
use App\Http\Controllers\API\Admin\Market\DeliveryController;
use App\Http\Controllers\API\Admin\Market\DiscountController;
use App\Http\Controllers\API\Admin\Market\GalleryController;
use App\Http\Controllers\API\Admin\Market\OrderController;
use App\Http\Controllers\API\Admin\Market\ProductPropertiesController;
use App\Http\Controllers\API\Admin\Market\ProvinceController;
use App\Http\Controllers\API\Customer\Profile\CompareController;
use App\Http\Controllers\API\Customer\Profile\FavoriteController;
use App\Http\Controllers\API\Customer\Profile\OrderController as CustomerOrderController;
use App\Http\Controllers\API\Admin\Market\PaymentController;
use App\Http\Controllers\API\Customer\Profile\ProfileAddressController;
use App\Http\Controllers\API\Customer\Profile\ProfileController;
use App\Http\Controllers\API\Customer\Profile\ProfileTicketController;
use App\Http\Controllers\API\Customer\SalesProcess\PaymentController as CustomerPaymentController;
use App\Http\Controllers\API\Admin\Market\ProductColorController;
use App\Http\Controllers\API\Admin\Market\ProductController;
use App\Http\Controllers\API\Customer\Market\ProductController as CustomerProductController;
use App\Http\Controllers\API\Admin\Market\ProductGuaranteeController;
use App\Http\Controllers\API\Admin\Market\PropertyController;
use App\Http\Controllers\API\Admin\Market\PropertyValueController;
use App\Http\Controllers\API\Admin\Market\StoreController;
use App\Http\Controllers\API\Admin\NotificationController;
use App\Http\Controllers\API\Admin\Notify\EmailController;
use App\Http\Controllers\API\Admin\Notify\EmailFileController;
use App\Http\Controllers\API\Admin\Notify\SmsController;
use App\Http\Controllers\API\Admin\Setting\SettingController;
use App\Http\Controllers\API\Admin\Ticket\TicketAdminController;
use App\Http\Controllers\API\Admin\Ticket\TicketCategoryController;
use App\Http\Controllers\API\Admin\Ticket\TicketController;
use App\Http\Controllers\API\Admin\Ticket\TicketPriorityController;
use App\Http\Controllers\API\Admin\User\AdminUserController;
use App\Http\Controllers\API\Admin\User\CustomerController;
use App\Http\Controllers\API\Admin\User\PermissionController;
use App\Http\Controllers\API\Admin\User\RoleController;
use App\Http\Controllers\API\Auth\Customer\LoginRegisterController;
use App\Http\Controllers\API\Customer\HomeController;
use App\Http\Controllers\API\Customer\SalesProcess\AddressController;
use App\Http\Controllers\API\Customer\SalesProcess\CartController;
use App\Http\Controllers\API\Customer\SalesProcess\ProfileCompletionController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\Admin\AdminDashboardController;
use App\Http\Controllers\API\Admin\Market\CategoryController;
use App\Http\Controllers\API\Admin\Content\CategoryController as ContentCategoryController;
use App\Http\Controllers\API\Admin\Content\CommentController as ContentCommentController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/



Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified'
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});
