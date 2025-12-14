<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Intervention\InterventionController;
use App\Http\Controllers\Api\Service\ServiceController;
use App\Http\Controllers\Api\Intervention\TacheController;
use App\Http\Controllers\Api\Client\ClientController;
use App\Http\Controllers\Api\Client\ClientProfileController;
use App\Http\Controllers\Api\Intervenant\IntervenantController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Les requêtes OPTIONS sont gérées par le CorsMiddleware

// Routes publiques (sans authentification)
Route::post('auth/register', [AuthController::class, 'register']);
Route::post('auth/login', [AuthController::class, 'login']);



// Routes protégées (nécessitent une authentification)
Route::middleware('auth:sanctum')->group(function () {

    // ======================
    // Routes d'authentification
    // ======================
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::get('auth/user', [AuthController::class, 'user']);
    Route::put('auth/profile', [AuthController::class, 'updateProfile']);
    Route::post('auth/profile/avatar', [AuthController::class, 'updateAvatar']);
    Route::post('auth/change-password', [AuthController::class, 'changePassword']);

    // ======================
    // Routes Interventions
    // ======================
    Route::apiResource('interventions', InterventionController::class);
    Route::get('interventions/index', [InterventionController::class, 'index']);
    Route::get('interventions/filter/upcoming', [InterventionController::class, 'upcoming']);
    Route::get('interventions/filter/completed', [InterventionController::class, 'completed']);
    Route::get('clients/{clientId}/interventions', [InterventionController::class, 'getClientInterventions']);
    Route::get('clients/{clientId}/statistics', [InterventionController::class, 'getClientStatistics']);
    Route::post('interventions/{id}/cancel', [InterventionController::class, 'cancelIntervention']);
    Route::post('interventions/{id}/photos', [InterventionController::class, 'addPhoto']);

    // ======================
    // Routes Evaluations
    // ======================
    Route::post('interventions/{interventionId}/evaluations', [\App\Http\Controllers\Api\Evaluation\EvaluationController::class, 'store']);
    Route::get('interventions/{interventionId}/evaluations', [\App\Http\Controllers\Api\Evaluation\EvaluationController::class, 'show']);

    // ======================
    // Routes Services
    // ======================
    Route::apiResource('services', ServiceController::class);
    Route::get('services/{id}/justificatifs', [ServiceController::class, 'getJustificatifs']);
    Route::get('services/{id}/information', [ServiceController::class, 'getInformation']);
    Route::get('services/{id}/taches', [ServiceController::class, 'getTaches']);

    // ======================
    // Routes Tâches
    // ======================
    Route::apiResource('taches', TacheController::class);
    Route::get('taches/{id}/intervenants', [TacheController::class, 'getIntervenants']);
    Route::get('taches/{id}/materiels', [TacheController::class, 'getMateriels']);

    // ======================
    // Routes Clients
    // ======================
    Route::apiResource('clients', ClientController::class);
    Route::get('clients/{id}/favorites', [ClientController::class, 'getFavorites']);
    Route::post('clients/{id}/favorites', [ClientController::class, 'addFavorite']);
    Route::delete('clients/{id}/favorites/{intervenantId}', [ClientController::class, 'removeFavorite']);
    
    // ======================
    // Routes Client Profile
    // ======================
    Route::get('clients/{id}/profile/statistics', [ClientProfileController::class, 'getStatistics']);
    Route::get('clients/{id}/profile/history', [ClientProfileController::class, 'getHistory']);
    Route::get('clients/{id}/profile/evaluations', [ClientProfileController::class, 'getEvaluations']);

    Route::get('intervenants/search', [IntervenantController::class, 'search']);
    // ======================
    // Routes Intervenants
    // ======================
    Route::apiResource('intervenants', IntervenantController::class);
    Route::get('intervenants/{id}/interventions', [IntervenantController::class, 'interventions']);
    Route::get('intervenants/{id}/disponibilites', [IntervenantController::class, 'disponibilites']);
    Route::get('intervenants/{id}/taches', [IntervenantController::class, 'taches']);
    Route::get('intervenants/{id}/services', [IntervenantController::class, 'services']);
    
});


use App\Http\Controllers\Api\ImageController;

/*
|--------------------------------------------------------------------------
| API Routes - Image Serving
|--------------------------------------------------------------------------
|
| Add these routes to your existing routes/api.php file
|
*/

// Public image routes (no authentication required)
Route::get('images/avatars/{path}', [ImageController::class, 'show'])
    ->where('path', '.*')
    ->name('api.images.avatars');

Route::get('storage/{path}', [ImageController::class, 'showStorage'])
    ->where('path', '.*')
    ->name('api.storage');

// Authentication routes
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    
    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('user', [AuthController::class, 'user']);
        Route::put('profile', [AuthController::class, 'updateProfile']);
        Route::post('profile/avatar', [AuthController::class, 'updateAvatar']);
        Route::post('change-password', [AuthController::class, 'changePassword']);
    });
});

