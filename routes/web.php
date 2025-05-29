<?php

namespace App\Http\Controllers;

use App\Http\Controllers\AdminController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Middleware\AuthAdmin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;

use App\Models\User;
use Surfsidemedia\Shoppingcart\Facades\Cart;

Auth::routes();

Route::get('/', [HomeController::class, 'index'])->name('home.index');
Route::get('/shop', [ShopController::class, 'index'])->name('shop.index');
Route::get('/shop/{product_slug}', [ShopController::class, 'product_details'])->name('shop.product.details');

Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/shop/{product_slug}', [CartController::class, 'add_to_cart'])->name('cart.add');
Route::put('/cart/increase-quantity/{rowId}', [CartController::class, 'increase_cart_quantity'])->name('cart.qty.increase');
Route::put('/cart/increase-decrease/{rowId}', [CartController::class, 'decrease_cart_quantity'])->name('cart.qty.decrease');
Route::delete('/cart/remove/{rowId}', [CartController::class, 'remove_item'])->name('cart.item.remove');
Route::delete('/cart/clear', [CartController::class, 'empty_cart'])->name('cart.empty');

Route::post('/wishlist/add', [WishlistController::class, 'add_to_wishlist'])->name('wishlist.add');
Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
Route::delete('/wishlist/item/remove/{rowId}', [WishlistController::class, 'remove_item'])->name('wishlist.item.remove');
Route::delete('/wishlist/clear', [WishlistController::class, 'empty_wishlist'])->name('wishlist.items.clear');
Route::post('/wishlist/move-to-cart/{rowId}', [WishlistController::class, 'move_to_cart'])->name('wishlist.move.to.cart');

Route::get('/checkout', [CartController::class, 'checkout'])->name('cart.checkout');
Route::post('/place-an-order', [CartController::class, 'place_an_order'])->name('cart.place.an.order');
Route::get('/order-confirmation', [CartController::class, 'order_confirmation'])->name('cart.order.confirmation');
Route::get('/order/{order_id}/confirmation', [CartController::class, 'redirectToConfirmation'])->name('order.confirmation');

Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register'])->middleware('throttle:5,1');

Route::get('/search', [HomeController::class, 'search'])->name('home.search');

Route::middleware(['auth'])->group(function () {
    Route::get('/account-dashboard', [UserController::class, 'index'])->name('user.index');
    Route::get('/account-orders', [UserController::class, 'orders'])->name('user.orders');
    Route::get('/account-order/{order_id}/details', [UserController::class, 'order_details'])->name('user.order.details');
    Route::put('/account-order/cancel-order', [UserController::class, 'order_cancel'])->name('user.order.cancel');

    // Address routes
    Route::get('/account-addresses', [UserController::class, 'addresses'])->name('user.addresses');
    Route::get('/account-address/create', [UserController::class, 'createAddress'])->name('user.address.create');
    Route::post('/account-address/store', [UserController::class, 'storeAddress'])->name('user.address.store');
    Route::get('/account-address/{id}/edit', [UserController::class, 'editAddress'])->name('user.address.edit');
    Route::put('/account-address/{id}/update', [UserController::class, 'updateAddress'])->name('user.address.update');
    Route::delete('/account-address/{id}/delete', [UserController::class, 'deleteAddress'])->name('user.address.delete');
    Route::put('/account-address/{id}/set-default', [UserController::class, 'setDefaultAddress'])->name('user.address.default');

    Route::get('/account-details', [UserController::class, 'accountDetails'])->name('user.account.details');
    Route::post('/update-profile', [UserController::class, 'updateProfile'])->name('user.update.profile');
    Route::post('/change-password', [UserController::class, 'changePassword'])->name('user.change.password');
    Route::post('/update-profile-picture', [UserController::class, 'updateProfilePicture'])->name('user.update.profile.picture');

    Route::post('/set-password', [UserController::class, 'setPassword'])->name('user.set.password');
});

Route::get('auth/google', [GoogleAuthController::class, 'redirect'])->name('google-auth');
Route::get('auth/google/callback', [GoogleAuthController::class, 'callbackGoogle']);

Route::middleware(['auth', AuthAdmin::class])->group(function () {
    Route::get('/admin', [AdminController::class, 'index'])->name('admin.index');
    Route::get('/admin/brands', [AdminController::class, 'brands'])->name('admin.brands');
    Route::get('/admin/brand/add', [AdminController::class, 'add_brand'])->name('admin.brand.add');
    Route::post('/admin/brand/store', [AdminController::class, 'brand_store'])->name('admin.brand.store');
    Route::get('/admin/brand/edit/{id}', [AdminController::class, 'brand_edit'])->name('admin.brand.edit');
    Route::put('/admin/brand/update', [AdminController::class, 'brand_update'])->name('admin.brand.update');
    Route::delete('/admin/brand/{id}/delete', [AdminController::class, 'brand_delete'])->name('admin.brand.delete');

    Route::get('/admin/categories', [AdminController::class, 'categories'])->name('admin.categories');
    Route::get('/admin/category/add', [AdminController::class, 'category_add'])->name('admin.category.add');
    Route::post('/admin/category/store', [AdminController::class, 'category_store'])->name('admin.category.store');
    Route::get('/admin/category/{id}/edit', [AdminController::class, 'category_edit'])->name('admin.category.edit');
    Route::put('/admin/category/update', [AdminController::class, 'category_update'])->name('admin.category.update');
    Route::delete('/admin/category/{id}/delete', [AdminController::class, 'category_delete'])->name('admin.category.delete');

    Route::get('/admin/products', [AdminController::class, 'products'])->name('admin.products');
    Route::get('/admin/product/add', [AdminController::class, 'product_add'])->name('admin.product.add');
    Route::post('/admin/product/store', [AdminController::class, 'product_store'])->name('admin.product.store');
    Route::get('/admin/product/{id}/edit', [AdminController::class, 'product_edit'])->name('admin.product.edit');
    Route::put('/admin/product/update', [AdminController::class, 'product_update'])->name('admin.product.update');
    Route::delete('/admin/product/{id}/delete', [AdminController::class, 'product_delete'])->name('admin.product.delete');

    Route::get('/admin/orders', [AdminController::class, 'orders'])->name('admin.orders');
    Route::get('/admin/order/{order_id}/details', [AdminController::class, 'order_details'])->name('admin.order.details');
    Route::get('/admin/order/{order_id}/packing-slip', [AdminController::class, 'orderPackingSlip'])->name('admin.order.packing-slip');
    Route::put('/admin/order/update-status', [AdminController::class, 'update_order_status'])->name('admin.order.status.update');
    Route::get('/admin/users', [\App\Http\Controllers\Admin\UserController::class, 'index'])->name('admin.users');
    Route::get('/admin/user/{id}/edit', [\App\Http\Controllers\Admin\UserController::class, 'edit'])->name('admin.user.edit');
    Route::put('/admin/user/update', [\App\Http\Controllers\Admin\UserController::class, 'update'])->name('admin.user.update');
    Route::get('/admin/user/{id}/orders', [\App\Http\Controllers\Admin\UserController::class, 'orders'])->name('admin.user.orders');

    // Notification routes
    Route::get('/admin/notifications', [\App\Http\Controllers\NotificationController::class, 'getNotifications'])->name('notifications.get');
    Route::post('/admin/notifications/mark-read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
    Route::post('/admin/notifications/mark-all-read', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    Route::get('/admin/notifications/count', [\App\Http\Controllers\NotificationController::class, 'getUnreadCount'])->name('notifications.count');
});

// Add these routes for settings
Route::get('/admin/settings', [\App\Http\Controllers\Admin\AdminController::class, 'settings'])->name('admin.settings');
Route::put('/admin/settings/update', [\App\Http\Controllers\Admin\AdminController::class, 'updateSettings'])->name('admin.settings.update');

// Custom Password Reset Routes (Auth::routes() already provides these, but we override for custom views)
// Note: Auth::routes() already provides password reset routes, so we don't need to redefine them

// OTP Verification Routes
Route::get('/otp/verify', [\App\Http\Controllers\Auth\LoginController::class, 'showOtpForm'])
    ->middleware('guest')
    ->name('otp.verify.form');

Route::post('/otp/verify', [\App\Http\Controllers\Auth\LoginController::class, 'verifyOtp'])
    ->middleware(['guest', 'throttle:5,1'])
    ->name('otp.verify');

Route::get('/otp/resend', [\App\Http\Controllers\Auth\LoginController::class, 'resendOtp'])
    ->middleware(['guest', 'throttle:3,1'])
    ->name('otp.resend');

// User account deletion
Route::delete('/user/account/delete', [\App\Http\Controllers\UserController::class, 'deleteAccount'])->name('user.account.delete');

// Diagnostic route
Route::get('/user/account-status', [\App\Http\Controllers\UserController::class, 'checkAccountStatus'])
    ->middleware('auth')
    ->name('user.account.status');

// User account routes
Route::middleware(['auth'])->group(function () {
    // Make sure this route exists
    Route::post('/user/set-password', [\App\Http\Controllers\UserController::class, 'setPassword'])->name('user.set.password');
});

// Product reviews
Route::post('/product/{product_id}/review', [ReviewController::class, 'store'])->name('product.review.store');

// Payment Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/payment/{order}', [\App\Http\Controllers\PaymentController::class, 'showPaymentForm'])->name('payment.form');
    Route::post('/payment/process', [\App\Http\Controllers\PaymentController::class, 'processPayment'])->name('payment.process');
    Route::get('/payment/success', [\App\Http\Controllers\PaymentController::class, 'paymentSuccess'])->name('payment.success');
    Route::get('/payment/failed', [\App\Http\Controllers\PaymentController::class, 'paymentFailed'])->name('payment.failed');
});

// Payment Webhook Routes (no auth required)
Route::post('/webhooks/stripe', [\App\Http\Controllers\PaymentController::class, 'stripeWebhook'])->name('webhooks.stripe');
Route::post('/webhooks/paymongo', [\App\Http\Controllers\PaymentController::class, 'paymongoWebhook'])->name('webhooks.paymongo');

// Add these routes for legal pages
Route::get('/terms-of-service', function () {
    return view('legal.terms');
})->name('terms');

Route::get('/privacy-policy', function () {
    return view('legal.privacy');
})->name('privacy');

Route::get('/refund-policy', function () {
    return view('legal.refund');
})->name('refund');

// About and Contact pages
Route::get('/about', [HomeController::class, 'about'])->name('about');
Route::get('/contact', [HomeController::class, 'contact'])->name('contact');
Route::post('/contact', [HomeController::class, 'contactSubmit'])->name('contact.submit');

// Security Testing Routes (Only available when SECURITY_TESTING_MODE=true)
Route::prefix('security')->group(function () {
    // Simple test route
    Route::get('/test', function () {
        return 'Security routes are working!';
    });

    // Simple security dashboard
    Route::get('/dashboard', [App\Http\Controllers\SimpleSecurityController::class, 'index'])->name('security.dashboard');
    Route::post('/xss-test', [App\Http\Controllers\SimpleSecurityController::class, 'xssTest'])->name('security.xss');
    Route::post('/sql-test', [App\Http\Controllers\SimpleSecurityController::class, 'sqlTest'])->name('security.sql');
    Route::get('/status', [App\Http\Controllers\SimpleSecurityController::class, 'status'])->name('security.status');
});






