<?php

use App\Http\Controllers\API\Admin\Content\TagController;
use App\Http\Controllers\API\Auth\AuthController;
use App\Http\Controllers\API\Auth\EmailVerificationController;
use App\Http\Controllers\API\Auth\OTPController;
use App\Http\Controllers\API\Auth\PasswordResetController;
use App\Http\Controllers\API\Customer\FilterProductsController;
use App\Http\Controllers\API\Customer\SearchCotroller;
use Illuminate\Http\Request;
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
use App\Http\Controllers\API\Auth\GoogleAuthController;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');



// admin 
Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->namespace('Admin')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('admin.home');


    // --/market/
    Route::prefix('market')->namespace('Market')->group(function () {
        // category
        Route::prefix('category')->group(function () {
            Route::get('/', [CategoryController::class, 'index'])->name('admin.market.category.index');
            Route::get('/search', [CategoryController::class, 'search'])->name('admin.market.category.search');
            Route::get('/options', [CategoryController::class, 'options'])->name('admin.market.category.options');
            Route::get('/show/{category}', [CategoryController::class, 'show'])->name('admin.market.category.show');
            Route::post('/store', [CategoryController::class, 'store'])->name('admin.market.category.store');
            Route::get('/status/{category}', [CategoryController::class, 'status'])->name('admin.market.category.status');
            Route::get('/show-in-menu/{category}', [CategoryController::class, 'showInMenu'])->name('admin.market.category.showInMenu');
            Route::put('/update/{category}', [CategoryController::class, 'update'])->name('admin.market.category.update');
            Route::delete('/destroy/{category}', [CategoryController::class, 'destroy'])->name('admin.market.category.destroy');
        });

        // brand

        Route::prefix('brand')->group(function () {
            Route::get('/', [BrandController::class, 'index'])->name('admin.market.brand.index');
            Route::get('/search', [BrandController::class, 'search'])->name('admin.market.brand.search');
            Route::post('/store', [BrandController::class, 'store'])->name('admin.market.brand.store');
            Route::get('/status/{brand}', [BrandController::class, 'status'])->name('admin.market.brand.status');
            Route::get('/show/{brand}', [BrandController::class, 'show'])->name('admin.market.brand.show');
            Route::put('/update/{brand}', [BrandController::class, 'update'])->name('admin.market.brand.update');
            Route::delete('/destroy/{brand}', [BrandController::class, 'destroy'])->name('admin.market.brand.destroy');
        });

        // comment
        Route::prefix('comment')->group(function () {
            Route::get('/', [CommentController::class, 'index'])->name('admin.market.comment.index');
            Route::get('/search', [CommentController::class, 'search'])->name('admin.market.comment.search');
            Route::get('/show/{comment}', [CommentController::class, 'show'])->name('admin.market.comment.show');
            Route::post('/answer/{comment}', [CommentController::class, 'answer'])->name('admin.market.comment.answer');
            Route::get('/status/{comment}', [CommentController::class, 'status'])->name('admin.market.comment.status');
            Route::get('/approved/{comment}', [CommentController::class, 'approved'])->name('admin.market.comment.approved');

        });

        // delivery
        Route::prefix('delivery')->group(function () {
            Route::get('/', [DeliveryController::class, 'index'])->name('admin.market.delivery.index');
            Route::get('/search', [DeliveryController::class, 'search'])->name('admin.market.delivery.search');
            Route::post('/store', [DeliveryController::class, 'store'])->name('admin.market.delivery.store');
            Route::get('/show/{delivery}', [DeliveryController::class, 'show'])->name('admin.market.delivery.show');
            Route::get('/status/{delivery}', [DeliveryController::class, 'status'])->name('admin.market.delivery.status');
            Route::put('/update/{delivery}', [DeliveryController::class, 'update'])->name('admin.market.delivery.update');
            Route::delete('/destroy/{delivery}', [DeliveryController::class, 'destroy'])->name('admin.market.delivery.destroy');


            Route::prefix('province')->group(function () {
                Route::get('/', [ProvinceController::class, 'index'])->name('admin.market.delivery-province.index');
                Route::get('/search', [ProvinceController::class, 'search'])->name('admin.market.delivery-province.search');
                Route::post('/store', [ProvinceController::class, 'store'])->name('admin.market.delivery-province.store');
                Route::get('/show/{province}', [ProvinceController::class, 'show'])->name('admin.market.delivery-province.show');
                Route::put('/update/{province}', [ProvinceController::class, 'update'])->name('admin.market.delivery-province.update');
                Route::delete('/destroy/{province}', [ProvinceController::class, 'destroy'])->name('admin.market.delivery-province.destroy');

            });


            Route::prefix('city')->group(function () {
                Route::get('/search/{province}', [CityController::class, 'search'])->name('admin.market.delivery-city.search');
                Route::get('/{province}', [CityController::class, 'index'])->name('admin.market.delivery-city.index');
                Route::post('/store/{province}', [CityController::class, 'store'])->name('admin.market.delivery-city.store');
                Route::get('/show/{city}', [CityController::class, 'show'])->name('admin.market.delivery-city.show');
                Route::put('/update/{city}', [CityController::class, 'update'])->name('admin.market.delivery-city.update');
                Route::delete('/destroy/{city}', [CityController::class, 'destroy'])->name('admin.market.delivery-city.destroy');

            });
        });

        // discount
        Route::prefix('discount')->group(function () {
            Route::get('/options', [DiscountController::class, 'options'])->name('admin.market.discount.options');
            Route::get('/copan', [DiscountController::class, 'copan'])->name('admin.market.discount.copan');
            Route::get('/copan/search', [DiscountController::class, 'copanSearch'])->name('admin.market.discount.copan.search');
            Route::get('/copan/show/{copan}', [DiscountController::class, 'copanShow'])->name('admin.market.discount.copan.show');
            Route::post('/copan/store', [DiscountController::class, 'copanStore'])->name('admin.market.discount.copan.store');
            Route::delete('/copan/destroy/{copan}', [DiscountController::class, 'copanDestroy'])->name('admin.market.discount.copan.destroy');
            Route::put('/copan/update/{copan}', [DiscountController::class, 'copanUpdate'])->name('admin.market.discount.copan.update');
            Route::get('/common-discount', [DiscountController::class, 'commonDiscount'])->name('admin.market.discount.commonDiscount');
            Route::get('/common-discount/search', [DiscountController::class, 'commonDiscountSearch'])->name('admin.market.discount.commonDiscount.search');
            Route::post('/common-discount/store', [DiscountController::class, 'commonDiscountStore'])->name('admin.market.discount.commonDiscount.store');
            Route::get('/common-discount/show/{commonDiscount}', [DiscountController::class, 'commonDiscountShow'])->name('admin.market.discount.commonDiscount.show');
            Route::put('/common-discount/update/{commonDiscount}', [DiscountController::class, 'commonDiscountUpdate'])->name('admin.market.discount.commonDiscount.update');
            Route::delete('/common-discount/destroy/{commonDiscount}', [DiscountController::class, 'commonDiscountDestroy'])->name('admin.market.discount.commonDiscount.destroy');
            Route::get('/amazing-sale', [DiscountController::class, 'amazingSale'])->name('admin.market.discount.amazingSale');
            Route::get('/amazing-sale/search', [DiscountController::class, 'amazingSaleSearch'])->name('admin.market.discount.amazingSale.search');
            Route::post('/amazing-sale/store', [DiscountController::class, 'amazingSaleStore'])->name('admin.market.discount.amazingSale.store');
            Route::get('/amazing-sale/show/{amazingSale}', [DiscountController::class, 'amazingSaleShow'])->name('admin.market.discount.amazingSale.show');
            Route::put('/amazing-sale/update/{amazingSale}', [DiscountController::class, 'amazingSaleUpdate'])->name('admin.market.discount.amazingSale.update');
            Route::delete('/amazing-sale/destroy/{amazingSale}', [DiscountController::class, 'amazingSaleDestroy'])->name('admin.market.discount.amazingSale.destroy');
            Route::get('/copan/status/{copan}', [DiscountController::class, 'copanStatus'])->name('admin.market.discount.copan-status');
            Route::get('/common-discount/status/{commonDiscount}', [DiscountController::class, 'commonDiscountStatus'])->name('admin.market.discount.commonDiscount-status');
            Route::get('/amazing-sale/status/{amazingSale}', [DiscountController::class, 'amazingSaleStatus'])->name('admin.market.discount.amazingSale-status');

        });

        // order
        Route::prefix('order')->group(function () {
            Route::get('/', [OrderController::class, 'all'])->name('admin.market.order.all');
            Route::get('/show/{order}', [OrderController::class, 'show'])->name('admin.market.order.show');
            Route::get('/change-order-status/{order}', [OrderController::class, 'changeOrderStatus'])->name('admin.market.order.changeOrderStutus');
            Route::put('/tracking-post-code/{order}', [OrderController::class, 'postalTrackingCode'])->name('admin.market.order.postalTrackingCode');
            Route::get('/change-send-status/{order}', [OrderController::class, 'changeSendStatus'])->name('admin.market.order.changeSendStutus');
            Route::get('/cancel-order/{order}', [OrderController::class, 'cancelOrder'])->name('admin.market.order.cancelOrder');
        });

        // payment
        Route::prefix('payment')->group(function () {
            Route::get('/', [PaymentController::class, 'all'])->name('admin.market.payment.all');
            Route::get('/canceled/{payment}', [PaymentController::class, 'canceled'])->name('admin.market.payment.canceled');
            Route::get('/returned/{payment}', [PaymentController::class, 'returned'])->name('admin.market.payment.returned');
            Route::get('/show/{payment}', [PaymentController::class, 'show'])->name('admin.market.payment.show');
        });

        // product
        Route::prefix('product')->group(function () {
            Route::get('/', [ProductController::class, 'index'])->name('admin.market.product.index');
            Route::get('/search', [ProductController::class, 'search'])->name('admin.market.product.search');
            Route::get('/options', [ProductController::class, 'options'])->name('admin.market.product.options');
            Route::get('/status/{product}', [ProductController::class, 'status'])->name('admin.market.product.status');
            Route::post('/store', [ProductController::class, 'store'])->name('admin.market.product.store');
            Route::get('/show/{product}', [ProductController::class, 'show'])->name('admin.market.product.show');
            Route::put('/update/{product}', [ProductController::class, 'update'])->name('admin.market.product.update');
            Route::delete('/destroy/{product}', [ProductController::class, 'destroy'])->name('admin.market.product.destroy');
            Route::delete('/delete-meta/{meta}', [ProductController::class, 'deleteMeta'])->name('admin.market.product.deleteMeta');

            // properties
            Route::get('/properties/{product}', [ProductPropertiesController::class, 'properties'])->name('admin.market.product.properties');
            Route::get('/properties/search/{product}', [ProductPropertiesController::class, 'search'])->name('admin.market.product.properties-search');
            Route::post('/properties/{product}', [ProductPropertiesController::class, 'storeProperties'])->name('admin.market.product.properties.store');
            Route::get('/properties/show/{attribute}', [ProductPropertiesController::class, 'show'])->name('admin.market.product.properties.show');
            Route::put('/properties/{attribute}', [ProductPropertiesController::class, 'updateProperties'])->name('admin.market.product.properties.update');

            // property values

            Route::get('/property-values/{product}/{attribute}', [ProductPropertiesController::class, 'propertyValues'])->name('admin.market.product.properties.values');
            Route::post('/property-values/store/{product}/{attribute}', [ProductPropertiesController::class, 'storePropertyValue'])->name('admin.market.product.properties.store-value');
            Route::put('/property-values/update/{value}', [ProductPropertiesController::class, 'updatePropertyValue'])->name('admin.market.product.properties.update-value');

            // product-color

            Route::get('/color/{product}', [ProductColorController::class, 'index'])->name('admin.market.product-color.index');
            Route::get('/color/search/{product}', [ProductColorController::class, 'search'])->name('admin.market.product-color.search');
            Route::post('/color/{product}/store', [ProductColorController::class, 'store'])->name('admin.market.product-color.store');
            Route::get('/color/show/{color}', [ProductColorController::class, 'show'])->name('admin.market.product-color.show');
            Route::get('/color/status/{color}', [ProductColorController::class, 'status'])->name('admin.market.product-color.status');
            Route::put('/color/update/{color}', [ProductColorController::class, 'update'])->name('admin.market.product-color.update');
            Route::delete('/color/destroy/{color}', [ProductColorController::class, 'destroy'])->name('admin.market.product-color.destroy');

            // product-guarantee

            Route::get('/guarantee/{product}', [ProductGuaranteeController::class, 'index'])->name('admin.market.product-guarantee.index');
            Route::get('/guarantee/search/{product}', [ProductGuaranteeController::class, 'search'])->name('admin.market.product-guarantee.search');
            Route::post('/guarantee/{product}/store', [ProductGuaranteeController::class, 'store'])->name('admin.market.product-guarantee.store');
            Route::get('/guarantee/show/{guarantee}', [ProductGuaranteeController::class, 'show'])->name('admin.market.product-guarantee.show');
            Route::get('/guarantee/status/{guarantee}', [ProductGuaranteeController::class, 'status'])->name('admin.market.product-guarantee.status');
            Route::put('/guarantee/update/{guarantee}', [ProductGuaranteeController::class, 'update'])->name('admin.market.product-guarantee.update');
            Route::delete('/guarantee/destroy/{guarantee}', [ProductGuaranteeController::class, 'destroy'])->name('admin.market.product-guarantee.destroy');

            // gallery

            Route::get('/gallery/{product}', [GalleryController::class, 'index'])->name('admin.market.gallery.index');
            Route::get('/gallery/search/{product}', [GalleryController::class, 'search'])->name('admin.market.gallery.search');
            Route::post('/gallery/store/{product}', [GalleryController::class, 'store'])->name('admin.market.gallery.store');
            Route::get('/gallery/show/{gallery}', [GalleryController::class, 'show'])->name('admin.market.gallery.show');
            Route::put('/gallery/update/{gallery}', [GalleryController::class, 'update'])->name('admin.market.gallery.update');
            Route::delete('/gallery/destroy/{gallery}', [GalleryController::class, 'destroy'])->name('admin.market.gallery.destroy');

        });


        // property
        Route::prefix('property')->group(function () {
            Route::get('/', [PropertyController::class, 'index'])->name('admin.market.property.index');
            Route::get('/search', [PropertyController::class, 'search'])->name('admin.market.property.search');
            Route::get('/options', [PropertyController::class, 'options'])->name('admin.market.property.options');
            Route::post('/store', [PropertyController::class, 'store'])->name('admin.market.property.store');
            Route::get('/show/{attribute}', [PropertyController::class, 'show'])->name('admin.market.property.show');
            Route::put('/update/{attribute}', [PropertyController::class, 'update'])->name('admin.market.property.update');
            Route::delete('/destroy/{attribute}', [PropertyController::class, 'destroy'])->name('admin.market.property.destroy');


            // property-value

            Route::get('/value/{attribute}', [PropertyValueController::class, 'index'])->name('admin.market.property-value.index');
            Route::get('/value/show/{value}', [PropertyValueController::class, 'show'])->name('admin.market.property-value.show');
            Route::post('/value/{attribute}/store', [PropertyValueController::class, 'store'])->name('admin.market.property-value.store');
            Route::put('/value/update/{value}', [PropertyValueController::class, 'update'])->name('admin.market.property-value.update');
            Route::delete('/value/destroy/{value}', [PropertyValueController::class, 'destroy'])->name('admin.market.property-value.destroy');



        });



        // store
        Route::prefix('store')->group(function () {
            Route::post('/store/{product}', [StoreController::class, 'store'])->name('admin.market.store.store');
            Route::put('/update/{product}', [StoreController::class, 'update'])->name('admin.market.store.update');

        });


    });

    // --/content/
    Route::prefix('content')->namespace('Content')->group(function () {

        // category
        Route::prefix('category')->group(function () {
            Route::get('/', [ContentCategoryController::class, 'index'])->name('admin.content.category.index');
            Route::get('/search', [ContentCategoryController::class, 'search'])->name('admin.content.category.search');
            Route::get('/show/{postCategory}', [ContentCategoryController::class, 'show'])->name('admin.content.category.show');
            Route::post('/store', [ContentCategoryController::class, 'store'])->name('admin.content.category.store');
            Route::put('/update/{postCategory}', [ContentCategoryController::class, 'update'])->name('admin.content.category.update');
            // Route::put('/update/{postCategory}', [ContentCategoryController::class, 'update'])->name('admin.content.category.update')->middleware('can:update,postCategory');
            // Route::put('/update/{postCategory}', [ContentCategoryController::class, 'update'])->name('admin.content.category.update')->can('update','postCategory');
            Route::get('/status/{postCategory}', [ContentCategoryController::class, 'status'])->name('admin.content.category.status');
            Route::delete('/destroy/{postCategory}', [ContentCategoryController::class, 'destroy'])->name('admin.content.category.destroy');
        });

        // banner
        Route::prefix('banner')->group(function () {
            Route::get('/', [BannerController::class, 'index'])->name('admin.content.banner.index');
            Route::get('/search', [BannerController::class, 'search'])->name('admin.content.banner.search');
            Route::get('/show/{banner}', [BannerController::class, 'show'])->name('admin.content.banner.show');
            Route::get('/options', [BannerController::class, 'options'])->name('admin.content.banner.options');
            Route::post('/store', [BannerController::class, 'store'])->name('admin.content.banner.store');
            Route::put('/update/{banner}', [BannerController::class, 'update'])->name('admin.content.banner.update');
            Route::get('/status/{banner}', [BannerController::class, 'status'])->name('admin.content.banner.status');
            Route::delete('/destroy/{banner}', [BannerController::class, 'destroy'])->name('admin.content.banner.destroy');
        });


        // comment
        Route::prefix('comment')->group(function () {
            Route::get('/', [ContentCommentController::class, 'index'])->name('admin.content.comment.index');
            Route::get('/search', [ContentCommentController::class, 'search'])->name('admin.content.comment.search');
            Route::get('/show/{comment}', [ContentCommentController::class, 'show'])->name('admin.content.comment.show');
            Route::get('/status/{comment}', [ContentCommentController::class, 'status'])->name('admin.content.comment.status');
            Route::get('/approved/{comment}', [ContentCommentController::class, 'approved'])->name('admin.content.comment.approved');
            Route::post('/answer/{comment}', [ContentCommentController::class, 'answer'])->name('admin.content.comment.answer');
            Route::get('/show/{comment}', [ContentCommentController::class, 'show'])->name('admin.content.comment.show');
            Route::delete('/destroy/{comment}', [ContentCommentController::class, 'destroy'])->name('admin.content.comment.destroy');

        });


        // faq
        Route::prefix('faq')->group(function () {
            Route::get('/', [FaqController::class, 'index'])->name('admin.content.faq.index');
            Route::get('/search', [FaqController::class, 'search'])->name('admin.content.faq.search');
            Route::post('/store', [FaqController::class, 'store'])->name('admin.content.faq.store');
            Route::get('/show/{faq}', [FaqController::class, 'show'])->name('admin.content.faq.show');
            Route::get('/status/{faq}', [FaqController::class, 'status'])->name('admin.content.faq.status');
            Route::put('/update/{faq}', [FaqController::class, 'update'])->name('admin.content.faq.update');
            Route::delete('/destroy/{faq}', [FaqController::class, 'destroy'])->name('admin.content.faq.destroy');

        });

        // menu
        Route::prefix('menu')->group(function () {
            Route::get('/', [MenuController::class, 'index'])->name('admin.content.menu.index');
            Route::get('/search', [MenuController::class, 'search'])->name('admin.content.menu.search');
            Route::get('/options', [MenuController::class, 'options'])->name('admin.content.menu.options');
            Route::get('/show/{menu}', [MenuController::class, 'show'])->name('admin.content.menu.show');
            Route::post('/store', [MenuController::class, 'store'])->name('admin.content.menu.store');
            Route::get('/status/{menu}', [MenuController::class, 'status'])->name('admin.content.menu.status');
            Route::put('/update/{menu}', [MenuController::class, 'update'])->name('admin.content.menu.update');
            Route::delete('/destroy/{menu}', [MenuController::class, 'destroy'])->name('admin.content.menu.destroy');

        });

        // page
        Route::prefix('page')->group(function () {
            Route::get('/', [PageController::class, 'index'])->name('admin.content.page.index');
            Route::get('/search', [PageController::class, 'search'])->name('admin.content.page.search');
            Route::get('/show/{page}', [PageController::class, 'show'])->name('admin.content.page.show');
            Route::get('/status/{page}', [PageController::class, 'status'])->name('admin.content.page.status');
            Route::post('/store', [PageController::class, 'store'])->name('admin.content.page.store');
            Route::put('/update/{page}', [PageController::class, 'update'])->name('admin.content.page.update');
            Route::delete('/destroy/{page}', [PageController::class, 'destroy'])->name('admin.content.page.destroy');

        });


        // post
        Route::prefix('post')->group(function () {
            Route::get('/', [PostController::class, 'index'])->name('admin.content.post.index');
            Route::get('/search', [PostController::class, 'search'])->name('admin.content.post.search');
            Route::get('/options', [PostController::class, 'options'])->name('admin.content.post.options');
            Route::get('/show/{post}', [PostController::class, 'show'])->name('admin.content.post.show');
            Route::post('/store', [PostController::class, 'store'])->name('admin.content.post.store');
            Route::get('/status/{post}', [PostController::class, 'status'])->name('admin.content.post.status');
            Route::get('/commentable/{post}', [PostController::class, 'commentable'])->name('admin.content.post.commentable');
            Route::put('/update/{post}', [PostController::class, 'update'])->name('admin.content.post.update');
            Route::delete('/destroy/{post}', [PostController::class, 'destroy'])->name('admin.content.post.destroy');

        });

        // tag
        Route::prefix('tag')->group(function () {
            Route::get('/', [TagController::class, 'index'])->name('admin.content.tag.index');
            Route::get('/search', [TagController::class, 'search'])->name('admin.content.tag.search');
            Route::get('/show/{tag}', [TagController::class, 'show'])->name('admin.content.tag.show');
            Route::post('/store', [TagController::class, 'store'])->name('admin.content.tag.store');
            Route::put('/update/{tag}', [TagController::class, 'update'])->name('admin.content.tag.update');
            Route::delete('/destroy/{tag}', [TagController::class, 'destroy'])->name('admin.content.tag.destroy');

        });

    });

    // user
    Route::prefix('user')->namespace('User')->group(function () {
        // admin-user
        Route::prefix('admin-user')->group(function () {
            Route::get('/', [AdminUserController::class, 'index'])->name('admin.user.admin-user.index');
            Route::get('/options', [AdminUserController::class, 'options'])->name('admin.user.admin-user.options');
            Route::get('/search', [AdminUserController::class, 'search'])->name('admin.user.admin-user.search');
            Route::get('/status/{admin}', [AdminUserController::class, 'status'])->name('admin.user.admin-user.status');
            Route::get('/activation/{admin}', [AdminUserController::class, 'activation'])->name('admin.user.admin-user.activation');
            Route::post('/store', [AdminUserController::class, 'store'])->name('admin.user.admin-user.store');
            Route::get('/show/{admin}', [AdminUserController::class, 'show'])->name('admin.user.admin-user.show');
            Route::put('/update/{admin}', [AdminUserController::class, 'update'])->name('admin.user.admin-user.update');
            Route::delete('/destroy/{admin}', [AdminUserController::class, 'destroy'])->name('admin.user.admin-user.destroy');
            Route::post('/roles/{admin}/store', [AdminUserController::class, 'rolesStore'])->name('admin.user.admin-user.roles-store');
            Route::post('/permissions/{admin}/store', [AdminUserController::class, 'permissionsStore'])->name('admin.user.admin-user.permissions-store');
        });

        // customer
        Route::prefix('customer')->group(function () {
            Route::get('/', [CustomerController::class, 'index'])->name('admin.user.customer.index');
            Route::get('/search', [CustomerController::class, 'search'])->name('admin.user.customer.search');
            Route::post('/store', [CustomerController::class, 'store'])->name('admin.user.customer.store');
            Route::get('/show/{customer}', [CustomerController::class, 'show'])->name('admin.user.customer.show');
            Route::put('/update/{customer}', [CustomerController::class, 'update'])->name('admin.user.customer.update');
            Route::delete('/destroy/{customer}', [CustomerController::class, 'destroy'])->name('admin.user.customer.destroy');
            Route::get('/status/{customer}', [CustomerController::class, 'status'])->name('admin.user.customer.status');
            Route::get('/activation/{customer}', [CustomerController::class, 'activation'])->name('admin.user.customer.activation');
        });

        // role
        Route::prefix('role')->group(function () {
            Route::get('/', [RoleController::class, 'index'])->name('admin.user.role.index');
            Route::get('/search', [RoleController::class, 'search'])->name('admin.user.role.search');
            Route::get('/options', [RoleController::class, 'options'])->name('admin.user.role.options');
            Route::post('/store', [RoleController::class, 'store'])->name('admin.user.role.store');
            Route::get('/show/{role}', [RoleController::class, 'show'])->name('admin.user.role.show');
            Route::put('/update/{role}', [RoleController::class, 'update'])->name('admin.user.role.update');
            Route::delete('/destroy/{role}', [RoleController::class, 'destroy'])->name('admin.user.role.destroy');
            Route::put('/permission/{role}', [RoleController::class, 'permission'])->name('admin.user.role.permission');


        });


        // permission
        Route::prefix('permission')->group(function () {
            Route::get('/', [PermissionController::class, 'index'])->name('admin.user.permission.index');
            Route::get('/search', [PermissionController::class, 'search'])->name('admin.user.permission.search');
            Route::post('/store', [PermissionController::class, 'store'])->name('admin.user.permission.store');
            Route::get('/status/{permission}', [PermissionController::class, 'status'])->name('admin.user.permission.status');
            Route::get('/show/{permission}', [PermissionController::class, 'show'])->name('admin.user.permission.show');
            Route::put('/update/{permission}', [PermissionController::class, 'update'])->name('admin.user.permission.update');
            Route::delete('/destroy/{permission}', [PermissionController::class, 'destroy'])->name('admin.user.permission.destroy');

        });


    });

    // notify
    Route::prefix('notify')->namespace('Notify')->group(function () {
        // email
        Route::prefix('email')->group(function () {
            Route::get('/', [EmailController::class, 'index'])->name('admin.notify.email.index');
            Route::get('/search', [EmailController::class, 'search'])->name('admin.notify.email.search');
            Route::post('/store', [EmailController::class, 'store'])->name('admin.notify.email.store');
            Route::get('/status/{email}', [EmailController::class, 'status'])->name('admin.notify.email.status');
            Route::get('/show/{email}', [EmailController::class, 'show'])->name('admin.notify.email.show');
            Route::put('/update/{email}', [EmailController::class, 'update'])->name('admin.notify.email.update');
            Route::delete('/destroy/{email}', [EmailController::class, 'destroy'])->name('admin.notify.email.destroy');
            Route::get('/send-mail/{email}', [EmailController::class, 'sendMail'])->name('admin.notify.email.send');

        });
        // email-file
        Route::prefix('email-file')->group(function () {
            Route::get('/{email}', [EmailFileController::class, 'index'])->name('admin.notify.email-file.index');
            Route::get('/search/{email}', [EmailFileController::class, 'search'])->name('admin.notify.email-file.search');
            Route::post('/{email}/store', [EmailFileController::class, 'store'])->name('admin.notify.email-file.store');
            Route::get('/status/{file}', [EmailFileController::class, 'status'])->name('admin.notify.email-file.status');
            Route::get('/show/{file}', [EmailFileController::class, 'show'])->name('admin.notify.email-file.show');
            Route::put('/update/{file}', [EmailFileController::class, 'update'])->name('admin.notify.email-file.update');
            Route::delete('/destroy/{file}', [EmailFileController::class, 'destroy'])->name('admin.notify.email-file.destroy');
            Route::get('/open-file/{file}', [EmailFileController::class, 'openFile'])->name('admin.notify.email-file.openFile');
        });

        // sms
        Route::prefix('sms')->group(function () {
            Route::get('/', [SmsController::class, 'index'])->name('admin.notify.sms.index');
            Route::get('/search', [SmsController::class, 'search'])->name('admin.notify.sms.search');
            Route::get('/show/{sms}', [SmsController::class, 'show'])->name('admin.notify.sms.show');
            Route::post('/store', [SmsController::class, 'store'])->name('admin.notify.sms.store');
            Route::get('/status/{sms}', [SmsController::class, 'status'])->name('admin.notify.sms.status');
            Route::put('/update/{sms}', [SmsController::class, 'update'])->name('admin.notify.sms.update');
            Route::delete('/destroy/{sms}', [SmsController::class, 'destroy'])->name('admin.notify.sms.destroy');
            Route::get('/send-sms/{sms}', [SmsController::class, 'sendSms'])->name('admin.notify.sms.send');
        });


    });


    // ticket
    Route::prefix('ticket')->namespace('Ticket')->group(function () {
        Route::get('/', [TicketController::class, 'index'])->name('admin.ticket.index');
        Route::get('/search', [TicketController::class, 'search'])->name('admin.ticket.search');
        Route::post('/answer/{ticket}', [TicketController::class, 'answer'])->name('admin.ticket.answer');
        Route::get('/show/{ticket}', [TicketController::class, 'show'])->name('admin.ticket.show');
        Route::get('/change/{ticket}', [TicketController::class, 'change'])->name('admin.ticket.change');




        // ticket-category
        Route::prefix('category')->namespace('Ticket')->group(function () {
            Route::get('/', [TicketCategoryController::class, 'index'])->name('admin.ticket.category.index');
            Route::get('/search', [TicketCategoryController::class, 'search'])->name('admin.ticket.category.search');
            Route::get('/status/{ticketCategory}', [TicketCategoryController::class, 'status'])->name('admin.ticket.category.status');
            Route::post('/store', [TicketCategoryController::class, 'store'])->name('admin.ticket.category.store');
            Route::get('/show/{ticketCategory}', [TicketCategoryController::class, 'show'])->name('admin.ticket.category.show');
            Route::put('/update/{ticketCategory}', [TicketCategoryController::class, 'update'])->name('admin.ticket.category.update');
            Route::delete('/destroy/{ticketCategory}', [TicketCategoryController::class, 'destroy'])->name('admin.ticket.category.destroy');

        });

        // ticket-priority
        Route::prefix('priority')->namespace('Ticket')->group(function () {
            Route::get('/', [TicketPriorityController::class, 'index'])->name('admin.ticket.priority.index');
            Route::get('/search', [TicketPriorityController::class, 'search'])->name('admin.ticket.priority.search');
            Route::get('/status/{ticketPriority}', [TicketPriorityController::class, 'status'])->name('admin.ticket.priority.status');
            Route::post('/store', [TicketPriorityController::class, 'store'])->name('admin.ticket.priority.store');
            Route::get('/show/{ticketPriority}', [TicketPriorityController::class, 'show'])->name('admin.ticket.priority.show');
            Route::put('/update/{ticketPriority}', [TicketPriorityController::class, 'update'])->name('admin.ticket.priority.update');
            Route::delete('/destroy/{ticketPriority}', [TicketPriorityController::class, 'destroy'])->name('admin.ticket.priority.destroy');

        });
        // ticket-admin

        Route::prefix('admin')->namespace('Ticket')->group(function () {
            Route::get('/', [TicketAdminController::class, 'index'])->name('admin.ticket.admin.index');
            Route::get('/search', [TicketAdminController::class, 'search'])->name('admin.ticket.admin.search');
            Route::get('/set/{admin}', [TicketAdminController::class, 'set'])->name('admin.ticket.admin.set');


        });
    });

    // setting
    Route::prefix('setting')->group(function () {
        Route::get('/', [SettingController::class, 'index'])->name('admin.setting.index');
        Route::put('/update', [SettingController::class, 'update'])->name('admin.setting.update');
    });

    Route::patch('/notification/read-all', [NotificationController::class, 'readAll'])->name('admin.notification.readAll');

});

Route::get('/options', [HomeController::class, 'options'])->name('customer.home');
Route::get('/autocomplete', [SearchCotroller::class, 'autocomplete'])->name('customer.products.autocomplete');
Route::get('/products', [FilterProductsController::class, 'options'])->name('customer.products.options');
Route::get('/products/filter/{category:slug?}', [FilterProductsController::class, 'products'])->name('customer.products');

Route::
        namespace('SalesProcess')->group(function () {

            Route::middleware(['auth:sanctum'])->group(function () {
                // cart
                Route::get('/cart/options', [CartController::class, 'options'])->name('customer.sales-process.cart');
                Route::put('/cart/update', [CartController::class, 'updateCart'])->name('customer.sales-process.update-cart');
                Route::post('/add-to-cart/{product:slug}', [CartController::class, 'addToCart'])->name('customer.sales-process.add-to-cart');
                Route::delete('/remove-from-cart/{cartItem}', [CartController::class, 'removeFromCart'])->name('customer.sales-process.remove-from-cart');
              
            });

            Route::middleware(['auth:sanctum', 'profile.completion'])->group(function () {
                // address
                Route::get('/address-and-delivery/options', [AddressController::class, 'options'])->name('customer.sales-process.address-and-delivery');
                Route::post('/add-address', [AddressController::class, 'addAddress'])->name('customer.sales-process.add-address');
                Route::put('/update-address/{address}', [AddressController::class, 'updateAddress'])->name('customer.sales-process.update-address');
                Route::get('/get-cities/{province}', [AddressController::class, 'getCities'])->name('customer.sales-process.get-cities');
                Route::post('/choose-address-and-delivery', [AddressController::class, 'chooseAddressAndDelivery'])->name('customer.sales-process.choose-address-and-delivery');

                // payment
                Route::get('/payment/options', [CustomerPaymentController::class, 'options'])->name('customer.sales-process.payment');
                Route::post('/copan-discount', [CustomerPaymentController::class, 'copanDisount'])->name('customer.sales-process.copan-discount');
                Route::post('/payment-submit', [CustomerPaymentController::class, 'paymentSubmit'])->name('customer.sales-process.payment-submit');
                Route::any('/payment-callback/{order}/{onlinePayment}', [CustomerPaymentController::class, 'paymentCallback'])->name('customer.sales-process.payment-callback');

            });
        });



Route::
        namespace('Market')->group(function () {
            Route::get('/product/{product:slug}', [CustomerProductController::class, 'options'])->name('customer.market.product');
            Route::get('/product/compare/{product}', [CustomerProductController::class, 'compare'])->name('customer.market.compare');
            Route::post('/product/add-to-compare/{product}', [CustomerProductController::class, 'addToCompare'])->name('customer.market.add-to-compare');
            Route::post('/product/remove-from-compare/{product}', [CustomerProductController::class, 'removeFromCompare'])->name('customer.market.remove-from-compare');
            Route::middleware('auth:sanctum')->group(function () {
                Route::post('/product/add-comment/{product}', [CustomerProductController::class, 'addComment'])->name('customer.market.add-comment');
                Route::get('/product/add-to-favorite/{product}', [CustomerProductController::class, 'addToFavorite'])->name('customer.market.add-to-favorite');
                Route::post('/product/add-rate/{product}', [CustomerProductController::class, 'addRate'])->name('customer.market.add-rate');
            });

        });

Route::
        namespace('Auth')->group(function () {
            // Route::get('/login-register', [LoginRegisterController::class, 'loginRegisterForm'])->name('auth.customer.login-register-form');
            // Route::middleware('throttle:customer-login-register-limitter')->post('/login-register', [LoginRegisterController::class, 'loginRegister'])->name('auth.customer.login-register');
            // Route::get('/login-confirm/{token}', [LoginRegisterController::class, 'loginConfirmForm'])->name('auth.customer.login-confirm-form');
            // Route::middleware('throttle:customer-login-confirm-limitter')->post('/login-confirm/{token}', [LoginRegisterController::class, 'loginConfirm'])->name('auth.customer.login-confirm');
            // Route::middleware('throttle:customer-login-resend-otp-limitter')->get('/login-resend-otp/{token}', [LoginRegisterController::class, 'loginResendOtp'])->name('auth.customer.login-resend-otp');
            // Route::get('/logout', [LoginRegisterController::class, 'logout'])->name('auth.customer.logout');
            Route::post('register', [AuthController::class, 'register'])->name('register');
            Route::middleware('auth:sanctum')->group(function () {
                Route::get('/email/verify', [EmailVerificationController::class, 'checkVerificationStatus']);

            });
            Route::post('/email/verification-notification', [EmailVerificationController::class, 'resendVerificationEmail'])->middleware('throttle:6,1');
            Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verifyEmail'])->middleware('signed', 'throttle:6,1')->name('verification.verify');
            Route::post('login', [AuthController::class, 'login'])->name('login');
            Route::middleware('auth:sanctum')->post('logout', [AuthController::class, 'logout'])->name('logout');
            Route::get('login/google', [GoogleAuthController::class, 'redirectToGoogle']);
            Route::get('login/google/callback', [GoogleAuthController::class, 'handleGoogleCallback']);
            // Route::post('password/email', [PasswordResetController::class, 'sendResetLink']);
            Route::post('reset-password', [PasswordResetController::class, 'resetPassword'])->name('reset-password');
            Route::post('forgot-password', [PasswordResetController::class, 'forgotPassword'])->name('forgot-password');
        });

Route::
        namespace('Profile')->group(function () {
            Route::middleware('auth:sanctum')->group(function () {

                // profile-completion
                Route::post('/profile-completion', [ProfileCompletionController::class, 'updateProfile'])->name('customer.sales-process.profile-completion-update');
                Route::get('/profile-required-fields', [ProfileCompletionController::class, 'getProfileRequiredField'])->name('customer.sales-process.get-profile-required-fileds');
                Route::post('/confirm-profile-info/{token}', [ProfileCompletionController::class, 'confirmProfileInfo'])->name('customer.sales-process.profile-info-confirm');
                Route::get('/resend-otp/{token}', [OTPController::class, 'resendOtp'])->name('customer.sales-process.resend-otp');
                //  profile
                Route::get('/orders', [CustomerOrderController::class, 'index'])->name('customer.profile.orders');
                Route::delete('/my-favorites/remove/{product}', [FavoriteController::class, 'remove'])->name('customer.profile.my-favorites-remove');
                Route::get('/profile', [ProfileController::class, 'index'])->name('customer.profile.index');
                Route::put('/profile/update', [ProfileController::class, 'update'])->name('customer.profile.update');
                Route::post('/profile/confirm-contact/{token}', [ProfileController::class, 'userCantactConfirm'])->name('customer.profile.user-contact-confirm');
                Route::put('/profile/edit-contact', [ProfileController::class, 'mobileOrEmailEdit'])->name('customer.profile.info.confirm');
                Route::get('/profile-resend-otp/{token}', [OTPController::class, 'resendOtp'])->name('customer.profile.resend-otp');

                Route::get('/my-addresses/options', [ProfileAddressController::class, 'options'])->name('customer.profile.my-addresses');
                Route::get('/my-tickets', [ProfileTicketController::class, 'index'])->name('customer.profile.my-tickets');
                Route::get('/ticket-details/{ticket}', [ProfileTicketController::class, 'show'])->name('customer.profile.ticket-details');
                Route::get('/ticket/options', [ProfileTicketController::class, 'options'])->name('customer.profile.ticket-create');
                Route::post('/ticket-store', [ProfileTicketController::class, 'ticketStore'])->name('customer.profile.ticket-store');
                Route::post('/ticket-answer/{ticket}', [ProfileTicketController::class, 'ticketAnswer'])->name('customer.profile.ticket-answer');
            });


        });
