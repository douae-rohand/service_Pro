<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Intervention\InterventionController;
use App\Http\Controllers\Api\Service\ServiceController;
use App\Http\Controllers\Api\Intervention\TacheController;
use App\Http\Controllers\Api\Client\ClientController;
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

// Route de test (à supprimer en production)
Route::get('/test', function () {
    return ['message' => 'API Laravel OK'];
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
    Route::get('services/{id}/taches', [ServiceController::class, 'getTaches']);

    // ======================
    // Routes Tâches
    // ======================
    Route::apiResource('taches', TacheController::class);

    // ======================
    // Routes Clients
    // ======================
    Route::apiResource('clients', ClientController::class);
    
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
