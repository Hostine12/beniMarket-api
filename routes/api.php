<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\DisputeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\WalletController;
use Illuminate\Support\Facades\Route;

// ─── Auth public ───────────────────────────────────────────────────────────────
Route::post('/register',          [AuthController::class, 'register']);
Route::post('/login',             [AuthController::class, 'login']);
Route::post('/payments/callback', [PaymentController::class, 'callback']);

// ─── Reset de mot de passe (token public) ──────────────────────────────────────
Route::post('/password/reset', [PasswordResetController::class, 'resetWithToken']);

// ─── Public ────────────────────────────────────────────────────────────────────
Route::get('/categories',                    [CategoryController::class, 'index']);
Route::get('/categories/{id}',               [CategoryController::class, 'show']);
Route::get('/categories/{id}/sellers',       [CategoryController::class, 'sellers']);
Route::get('/categories/{id}/products',      [CategoryController::class, 'products']);
Route::get('/products',                      [ProductController::class, 'index']);
Route::get('/products/{id}',                 [ProductController::class, 'show']);
Route::get('/products/{id}/recommendations', [ProductController::class, 'sellerRecommendations']);
Route::get('/shops',                         [ShopController::class, 'index']);
Route::get('/shops/{id}',                    [ShopController::class, 'show']);

// ─── Devis frais de livraison (public, utilisé au checkout) ───────────────────
Route::post('/delivery-fee/quote', [OrderController::class, 'deliveryFeeQuote']);

// ─── Authentifié ───────────────────────────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me',      [AuthController::class, 'me']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);
    Route::post('/password/change', [AuthController::class, 'changePassword']);

    // Cart
    Route::get('/cart',          [CartController::class, 'index']);
    Route::post('/cart',         [CartController::class, 'store']);
    Route::put('/cart/{id}',     [CartController::class, 'update']);
    Route::delete('/cart/clear', [CartController::class, 'clear']);
    Route::delete('/cart/{id}',  [CartController::class, 'destroy']);

    // Orders
    Route::get('/orders',                        [OrderController::class, 'index']);
    Route::post('/orders',                       [OrderController::class, 'store']);
    Route::get('/orders/{id}',                   [OrderController::class, 'show']);
    Route::put('/orders/{id}/status',            [OrderController::class, 'updateStatus']);
    Route::post('/orders/{id}/confirm-received', [OrderController::class, 'confirmReceived']);
    Route::get('/orders/recommendations',        [ProductController::class, 'orderRecommendations']);

    // Payments
    Route::post('/payments/initiate/{orderId}', [PaymentController::class, 'initiate']);
    Route::get('/payments/status/{orderId}',    [PaymentController::class, 'status']);

    // Deliveries (livreur)
    Route::get('/deliveries',                   [DeliveryController::class, 'index']);
    Route::put('/deliveries/{id}/accept',       [DeliveryController::class, 'accept']);
    Route::put('/deliveries/{id}/request-otp',  [DeliveryController::class, 'requestOtp']);
    Route::put('/deliveries/{id}/verify-otp',   [DeliveryController::class, 'verifyOtp']);

    // Portefeuille (vendeur / livreur) — solde + historique des paiements reçus
    Route::get('/wallet', [WalletController::class, 'index']);

    // Notifications
    Route::get('/notifications',            [NotificationController::class, 'index']);
    Route::put('/notifications/read-all',   [NotificationController::class, 'markAllRead']);
    Route::put('/notifications/{id}/read',  [NotificationController::class, 'markRead']);
    Route::delete('/notifications/{id}',    [NotificationController::class, 'destroy']);

    // Shops (vendor)
    Route::post('/shops',        [ShopController::class, 'store']);
    Route::put('/shops/{id}',    [ShopController::class, 'update']);
    Route::delete('/shops/{id}', [ShopController::class, 'destroy']);
    Route::get('/vendor/shop',   [ShopController::class, 'myShop']);

    // Products (vendor)
    Route::post('/products',        [ProductController::class, 'store']);
    Route::put('/products/{id}',    [ProductController::class, 'update']);
    Route::delete('/products/{id}', [ProductController::class, 'destroy']);
    Route::get('/vendor/products',  [ProductController::class, 'vendorProducts']);

    // Disputes — client, vendor, admin
    Route::get('/disputes',                        [DisputeController::class, 'index']);
    Route::post('/disputes',                       [DisputeController::class, 'store']);
    Route::get('/disputes/{id}',                   [DisputeController::class, 'show']);
    Route::post('/disputes/{id}/messages',         [DisputeController::class, 'addMessage']);
    Route::post('/disputes/{id}/evidences',        [DisputeController::class, 'addEvidence']);

    // ─── Admin ─────────────────────────────────────────────────────────────────
    Route::prefix('admin')->middleware('role:admin')->group(function () {
        Route::get('/stats',                        [AdminController::class, 'stats']);
        Route::get('/users',                        [AdminController::class, 'users']);
        Route::put('/users/{id}/status',            [AdminController::class, 'updateUserStatus']);
        Route::get('/vendors/pending',              [AdminController::class, 'pendingVendors']);
        Route::get('/couriers/pending',             [AdminController::class, 'pendingCouriers']);
        Route::put('/accounts/{id}/validate',       [AdminController::class, 'validateAccount']);
        Route::get('/shops/pending',                [AdminController::class, 'pendingShops']);
        Route::put('/shops/{id}/validate',          [AdminController::class, 'validateShop']);
        Route::get('/shops',                        [AdminController::class, 'shops']);
        Route::get('/disputes',                     [AdminController::class, 'disputes']);
        Route::put('/disputes/{id}/resolve',        [DisputeController::class, 'resolve']);
        Route::post('/users/{id}/password-reset',   [PasswordResetController::class, 'adminGenerateReset']);
        Route::post('/categories',                  [CategoryController::class, 'store']);
    });
});
