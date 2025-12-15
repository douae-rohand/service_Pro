<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Intervention\InterventionController;
use App\Http\Controllers\Api\Service\ServiceController;
use App\Http\Controllers\Api\Intervention\TacheController;
use App\Http\Controllers\Api\Client\ClientController;
use App\Http\Controllers\Api\Intervenant\IntervenantController;
use App\Http\Controllers\Api\Admin\AdminController;

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

    // ======================
    // Routes Admin
    // ======================
    Route::prefix('admin')->group(function () {
        // Dashboard Stats
        Route::get('stats', [AdminController::class, 'stats']);

        // Clients Management
        Route::get('clients', [AdminController::class, 'getClients']);
        Route::get('clients/{id}', [AdminController::class, 'getClientDetails']);
        Route::post('clients/{id}/toggle-status', [AdminController::class, 'toggleClientStatus']);

        // Intervenants Management
        Route::get('intervenants', [AdminController::class, 'getIntervenants']);
        Route::get('intervenants/{id}', [AdminController::class, 'getIntervenantDetails']);
        Route::post('intervenants/{id}/toggle-status', [AdminController::class, 'toggleIntervenantStatus']);
        
        // Justificatifs Download
        Route::get('justificatifs/{id}/download', [AdminController::class, 'downloadJustificatif']);

        // Demandes Management
        Route::get('demandes', [AdminController::class, 'getPendingRequests']);
        Route::post('demandes/{id}/approve', [AdminController::class, 'approveRequest']);
        Route::post('demandes/{id}/reject', [AdminController::class, 'rejectRequest']);

        // Services Management
        Route::get('services', [AdminController::class, 'getServices']);
        Route::post('services', [AdminController::class, 'createService']);
        Route::post('services/{id}/toggle-status', [AdminController::class, 'toggleServiceStatus']);
        Route::get('services/{id}/stats', [AdminController::class, 'getServiceStats']);
        
        // Taches (Sub-services) Management
        Route::get('services/{serviceId}/taches', [AdminController::class, 'getServiceTaches']);
        Route::post('services/{serviceId}/taches', [AdminController::class, 'createTache']);
        Route::put('taches/{tacheId}', [AdminController::class, 'updateTache']);
        Route::delete('taches/{tacheId}', [AdminController::class, 'deleteTache']);

        // Reclamations Management
        Route::get('reclamations', [AdminController::class, 'getReclamations']);
        Route::post('reclamations/{id}/handle', [AdminController::class, 'handleReclamation']);

        // Historique
        Route::get('historique', [AdminController::class, 'getHistorique']);
        Route::get('historique/export/csv', [AdminController::class, 'exportHistoriqueCSV']);
        Route::get('historique/export/pdf', [AdminController::class, 'exportHistoriquePDF']);
    });
});
