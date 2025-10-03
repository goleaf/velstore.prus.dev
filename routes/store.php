<?php

use App\Http\Controllers\Admin\LanguageController;
use App\Http\Controllers\Store\Auth\ForgotPasswordController;
use App\Http\Controllers\Store\Auth\LoginController;
use App\Http\Controllers\Store\Auth\RegisterController;
use App\Http\Controllers\Store\Auth\ResetPasswordController;
use App\Http\Controllers\Store\CartController;
use App\Http\Controllers\Store\CheckoutController;
use App\Http\Controllers\Store\CurrencyController;
use App\Http\Controllers\Store\PaymentGateway\StripeController;
use App\Http\Controllers\Store\PageController as StorePageController;
use App\Http\Controllers\Store\ProductController;
use App\Http\Controllers\Store\SearchController;
use App\Http\Controllers\Store\ShopController;
use App\Http\Controllers\Store\WishlistController;
use App\Http\Controllers\StoreController;
use Illuminate\Support\Facades\Route;

Route::get('/', [StoreController::class, 'index'])->name('xylo.home');
Route::get('/product/{slug}', [ProductController::class, 'show'])->name('product.show');
Route::post('/change-currency', [CurrencyController::class, 'changeCurrency'])->name('change.currency');
Route::get('/category/{slug}', [ShopController::class, 'showCategory'])->name('category.show');
Route::get('/pages/{slug}', [StorePageController::class, 'show'])->name('store.pages.show');

Route::post('/cart/add', [CartController::class, 'addToCart'])->name('cart.add');
Route::get('/cart', [CartController::class, 'viewCart'])->name('cart.view');
Route::post('/cart/update', [CartController::class, 'updateCart'])->name('cart.update');
Route::post('/cart/remove', [CartController::class, 'removeFromCart'])->name('cart.remove');

Route::post('/change-store-language', [LanguageController::class, 'changeLanguage'])->name('change.store.language');

Route::post('/cart/apply-coupon', [CartController::class, 'applyCoupon'])->name('cart.applyCoupon');
Route::post('/cart/remove-coupon', [CartController::class, 'removeCoupon'])->name('cart.removeCoupon');

Route::get('/products', [ShopController::class, 'index'])->name('shop.index');

Route::get('/search-suggestions', [SearchController::class, 'suggestions']);
Route::get('/search', [SearchController::class, 'searchResults']);

Route::get('/get-variant-price', [ProductController::class, 'getVariantPrice'])->name('product.variant.price');

Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');

Route::prefix('customer')->name('customer.')->group(function () {
    // Guest routes
    Route::middleware('guest:customer')->group(function () {
        Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
        Route::post('login', [LoginController::class, 'login']);

        Route::get('register', [RegisterController::class, 'showRegistrationForm'])->name('register');
        Route::post('register', [RegisterController::class, 'register']);

        Route::get('forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
        Route::post('forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');

        Route::get('reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
        Route::post('reset-password', [ResetPasswordController::class, 'reset'])->name('password.update');
    });

    // Authenticated routes
    Route::middleware('auth.customer')->group(function () {
        Route::post('logout', [LoginController::class, 'logout'])->name('logout');
        Route::post('/wishlist', [WishlistController::class, 'store'])->name('wishlist.store');
        Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');

        // Customer Addresses (Profile)
        Route::get('/addresses', [\App\Http\Controllers\Store\CustomerAddressController::class, 'index'])->name('addresses.index');
        Route::post('/addresses', [\App\Http\Controllers\Store\CustomerAddressController::class, 'store'])->name('addresses.store');
        Route::put('/addresses/{address}', [\App\Http\Controllers\Store\CustomerAddressController::class, 'update'])->name('addresses.update');
        Route::delete('/addresses/{address}', [\App\Http\Controllers\Store\CustomerAddressController::class, 'destroy'])->name('addresses.destroy');
        Route::post('/addresses/{address}/default', [\App\Http\Controllers\Store\CustomerAddressController::class, 'setDefault'])->name('addresses.default');
    });
});

Route::get('/stripe/checkout', [StripeController::class, 'checkout'])->name('stripe.checkout.process');
