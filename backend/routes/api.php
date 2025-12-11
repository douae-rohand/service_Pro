<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\InterventionController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\TacheController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\IntervenantController;

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

// Routes publiques pour consultation
Route::get('services', [ServiceController::class, 'index']);
Route::get('intervenants/{id}/active-services-tasks', [IntervenantController::class, 'getActiveServicesAndTasks']);

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
    Route::post('services', [ServiceController::class, 'store']);
    Route::get('services/{id}', [ServiceController::class, 'show']);
    Route::put('services/{id}', [ServiceController::class, 'update']);
    Route::delete('services/{id}', [ServiceController::class, 'destroy']);
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
});
