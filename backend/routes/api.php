<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Intervention\InterventionController;
use App\Http\Controllers\Api\Service\ServiceController;
use App\Http\Controllers\Api\Intervention\TacheController;
use App\Http\Controllers\Api\Client\ClientController;
use App\Http\Controllers\Api\Intervenant\IntervenantController;
use App\Http\Controllers\Api\StatsController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Les requêtes OPTIONS sont gérées par le CorsMiddleware

// Routes publiques (sans authentification)
Route::post('auth/register', [AuthController::class, 'register']);
Route::post('auth/login', [AuthController::class, 'login']);

// Route de test (à supprimer en production)
Route::get('/test', function () {
    return ['message' => 'API Laravel OK'];
});

// ======================
// Routes Services (publiques pour consultation)
// ======================
Route::get('services', [ServiceController::class, 'index']);
Route::get('services/{id}', [ServiceController::class, 'show']);
Route::get('services/{id}/taches', [ServiceController::class, 'getTaches']);
Route::get('intervenants/{id}/active-services-tasks', [IntervenantController::class, 'getActiveServicesAndTasks']);

// ======================
// Routes Intervenants (publiques pour consultation)
// ======================
Route::get('intervenants', [IntervenantController::class, 'index']);
Route::get('intervenants/{id}', [IntervenantController::class, 'show']);
Route::get('intervenants/me/taches', [IntervenantController::class, 'myTaches']);
Route::put('intervenants/me/taches/{tacheId}', [IntervenantController::class, 'updateMyTache']);
Route::post('intervenants/me/taches/{tacheId}/toggle-active', [IntervenantController::class, 'toggleActiveMyTache']);
Route::delete('intervenants/me/taches/{tacheId}', [IntervenantController::class, 'deleteMyTache']);

// ======================
// Routes Statistiques (publiques pour consultation)
// ======================
Route::get('stats', [StatsController::class, 'index']);

// Test route to verify no auth is required
Route::get('intervenants/test', function () {
    return response()->json(['message' => 'Test route works without auth', 'intervenant_id' => 5]);
});


// Routes protégées (nécessitent une authentification)
Route::middleware('auth:sanctum')->group(function () {

    // ======================
    // Routes d'authentification
    // ======================
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::get('auth/user', [AuthController::class, 'user']);
    Route::put('auth/profile', [AuthController::class, 'updateProfile']);
    Route::post('auth/change-password', [AuthController::class, 'changePassword']);

    // ======================
    // Routes Interventions
    // ======================
    Route::apiResource('interventions', InterventionController::class);
    Route::get('interventions/filter/upcoming', [InterventionController::class, 'upcoming']);
    Route::get('interventions/filter/completed', [InterventionController::class, 'completed']);
    Route::post('interventions/{id}/photos', [InterventionController::class, 'addPhoto']);

    // ======================
    // Routes Services (protégées)
    // ======================
    // Route::post('services', [ServiceController::class, 'store']);
    // Route::get('services/{id}', [ServiceController::class, 'show']);
    // Route::put('services/{id}', [ServiceController::class, 'update']);
    // Route::delete('services/{id}', [ServiceController::class, 'destroy']);
    // Route::get('services/{id}/taches', [ServiceController::class, 'getTaches']);

    // ======================
    // Routes Tâches
    // ======================
    //Route::apiResource('taches', TacheController::class);

    // ======================
    // Routes Clients
    // ======================
    Route::apiResource('clients', ClientController::class);
    Route::get('clients/{id}/interventions', [ClientController::class, 'interventions']);
    Route::post('clients/{id}/favorites', [ClientController::class, 'addFavorite']);
    Route::delete('clients/{id}/favorites/{intervenantId}', [ClientController::class, 'removeFavorite']);

    // ======================
    // Routes Intervenants (modification - protégées)
    // ======================
    Route::post('intervenants', [IntervenantController::class, 'store']);
    Route::put('intervenants/{id}', [IntervenantController::class, 'update']);
    Route::delete('intervenants/{id}', [IntervenantController::class, 'destroy']);
    Route::get('intervenants/{id}/interventions', [IntervenantController::class, 'interventions']);
    Route::get('intervenants/{id}/disponibilites', [IntervenantController::class, 'disponibilites']);
    Route::get('intervenants/{id}/taches', [IntervenantController::class, 'taches']);
    
    // Routes for current intervenant's taches
    // TODO: Uncomment these routes when authentication is implemented and remove the temporary routes above
    // Route::get('intervenants/me/taches', [IntervenantController::class, 'myTaches']);
    // Route::put('intervenants/me/taches/{tacheId}', [IntervenantController::class, 'updateMyTache']);
    // Route::post('intervenants/me/taches/{tacheId}/toggle-active', [IntervenantController::class, 'toggleActiveMyTache']);
    // Route::delete('intervenants/me/taches/{tacheId}', [IntervenantController::class, 'deleteMyTache']);
});
