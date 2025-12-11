<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\InterventionController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\TacheController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\Intervenant\IntervenantController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Routes publiques (sans authentification)
Route::post('auth/register', [AuthController::class, 'register']);
Route::post('auth/login', [AuthController::class, 'login']);

// Route de test (à supprimer en production)
Route::get('/test', function () {
    return ['message' => 'API Laravel OK'];
});

// TEMPORARY: Routes without authentication for testing (hardcoded intervenant ID=5)
// TODO: Move these routes back inside auth:sanctum middleware when authentication is implemented
// IMPORTANT: These routes must be defined BEFORE the apiResource routes to avoid route conflicts

// Test route to verify no auth is required
Route::get('intervenants/test', function () {
    return response()->json(['message' => 'Test route works without auth', 'intervenant_id' => 5]);
});

Route::get('intervenants/me/taches', [IntervenantController::class, 'myTaches']);
Route::put('intervenants/me/taches/{tacheId}', [IntervenantController::class, 'updateMyTache']);
Route::post('intervenants/me/taches/{tacheId}/toggle-active', [IntervenantController::class, 'toggleActiveMyTache']);
Route::delete('intervenants/me/taches/{tacheId}', [IntervenantController::class, 'deleteMyTache']);

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
    // Routes Services
    // ======================
    Route::apiResource('services', ServiceController::class);
    Route::get('services/{id}/taches', [ServiceController::class, 'getTaches']);

    // ======================
    // Routes Tâches
    // ======================
    Route::apiResource('taches', TacheController::class);

    // ======================
    // Routes Clients
    // ======================
    Route::apiResource('clients', ClientController::class);
    Route::get('clients/{id}/interventions', [ClientController::class, 'interventions']);
    Route::post('clients/{id}/favorites', [ClientController::class, 'addFavorite']);
    Route::delete('clients/{id}/favorites/{intervenantId}', [ClientController::class, 'removeFavorite']);

    // ======================
    // Routes Intervenants
    // ======================
    Route::apiResource('intervenants', IntervenantController::class);
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
