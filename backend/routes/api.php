<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Intervention\InterventionController;
use App\Http\Controllers\Api\Service\ServiceController;
use App\Http\Controllers\Api\Intervention\TacheController;
use App\Http\Controllers\Api\Client\ClientController;
use App\Http\Controllers\Api\Intervenant\IntervenantController;
use App\Http\Controllers\Api\Admin\AdminController;
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
Route::middleware('web')->group(function () {
    Route::get('auth/google/redirect', [AuthController::class, 'redirectToGoogle']);
    Route::get('auth/google/callback', [AuthController::class, 'handleGoogleCallback']);
});
Route::post('auth/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('auth/verify-code', [AuthController::class, 'verifyCode']);
Route::post('auth/reset-password', [AuthController::class, 'resetPassword']);

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
Route::put('intervenants/{id}/taches/{tacheId}/configure', [IntervenantController::class, 'configureTask']);

// Service activation
Route::get('intervenants/{id}/services-with-activation', [IntervenantController::class, 'getServicesWithActivation']);
Route::post('intervenants/{id}/services/{serviceId}/toggle', [IntervenantController::class, 'toggleService']);
Route::post('intervenants/{id}/services/{serviceId}/status', [IntervenantController::class, 'updateServiceStatus']);
Route::post('intervenants/{id}/services/{serviceId}/materials', [IntervenantController::class, 'updateServiceMaterials']);
Route::post('intervenants/{id}/services/{serviceId}/request-activation', [IntervenantController::class, 'requestActivation']);

// ======================
// Routes Statistiques (publiques pour consultation)
// ======================
Route::get('stats', [StatsController::class, 'index']);

// Test route to verify no auth is required
Route::get('intervenants/test', function () {
    return response()->json(['message' => 'Test route works without auth', 'intervenant_id' => 5]);
});


// ======================
// Routes Témoignages (publiques pour consultation)
// ======================
Route::get('testimonials', [\App\Http\Controllers\Api\CommentaireController::class, 'landingTestimonials']);

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
    // Routes Intervenants (protégées - nécessitent authentification)
    // ======================
    Route::get('intervenants/me/taches', [IntervenantController::class, 'myTaches']);
    Route::put('intervenants/me/taches/{tacheId}', [IntervenantController::class, 'updateMyTache']);
    Route::post('intervenants/me/taches/{tacheId}/toggle-active', [IntervenantController::class, 'toggleActiveMyTache']);
    Route::delete('intervenants/me/taches/{tacheId}', [IntervenantController::class, 'deleteMyTache']);
    Route::post('auth/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('auth/verify-code', [AuthController::class, 'verifyCode']);
    Route::post('auth/reset-password', [AuthController::class, 'resetPassword']);

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

    // Routes for current intervenant's availability (must come before generic {id} routes)
    Route::get('intervenants/me/disponibilites', [IntervenantController::class, 'myDisponibilites']);
    Route::post('intervenants/me/disponibilites/regular', [IntervenantController::class, 'createRegularAvailability']);
    Route::post('intervenants/me/disponibilites/special', [IntervenantController::class, 'createSpecialAvailability']);
    Route::delete('intervenants/me/disponibilites/{id}', [IntervenantController::class, 'deleteDisponibilite']);

    // Generic routes for specific intervenant by ID
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

    // Routes for current intervenant's taches
    // TODO: Uncomment these routes when authentication is implemented and remove the temporary routes above
    // Route::get('intervenants/me/taches', [IntervenantController::class, 'myTaches']);
    // Route::put('intervenants/me/taches/{tacheId}', [IntervenantController::class, 'updateMyTache']);
    // Route::post('intervenants/me/taches/{tacheId}/toggle-active', [IntervenantController::class, 'toggleActiveMyTache']);
    // Route::delete('intervenants/me/taches/{tacheId}', [IntervenantController::class, 'deleteMyTache']);

    // Routes for current intervenant's reservations
    Route::get('intervenants/me/reservations', [IntervenantController::class, 'myReservations']);
    Route::post('intervenants/me/reservations/{id}/accept', [IntervenantController::class, 'acceptReservation']);
    Route::post('intervenants/me/reservations/{id}/refuse', [IntervenantController::class, 'refuseReservation']);

    Route::get('intervenants/me/taches', [IntervenantController::class, 'myTaches']);
    Route::put('intervenants/me/taches/{tacheId}', [IntervenantController::class, 'updateMyTache']);
    Route::post('intervenants/me/taches/{tacheId}/toggle-active', [IntervenantController::class, 'toggleActiveMyTache']);
    Route::delete('intervenants/me/taches/{tacheId}', [IntervenantController::class, 'deleteMyTache']);
});
