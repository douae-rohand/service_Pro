<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Intervention\InterventionController;
use App\Http\Controllers\Api\Intervention\InterventionControllerIntervenant;
use App\Http\Controllers\Api\Service\ServiceController;
use App\Http\Controllers\Api\Intervention\TacheController;
use App\Http\Controllers\Api\Client\ClientController;
use App\Http\Controllers\Api\Client\ClientProfileController;
use App\Http\Controllers\Api\Intervenant\IntervenantController;
use App\Http\Controllers\Api\Admin\AdminController;
use App\Http\Controllers\Api\StatsController;
use App\Http\Controllers\Api\ImageController;
use App\Http\Controllers\Api\Evaluation\EvaluationController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// ======================
// Routes Images (Publiques)
// ======================
Route::get('images/avatars/{path}', [ImageController::class, 'show'])
    ->where('path', '.*')
    ->name('api.images.avatars');

Route::get('storage/{path}', [ImageController::class, 'showStorage'])
    ->where('path', '.*')
    ->name('api.storage');


// ======================
// Routes Publiques
// ======================

// Auth
Route::post('auth/register', [AuthController::class, 'register']);
Route::post('auth/login', [AuthController::class, 'login']);
Route::post('auth/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('auth/verify-code', [AuthController::class, 'verifyCode']);
Route::post('auth/reset-password', [AuthController::class, 'resetPassword']);

// Google Auth
Route::middleware('web')->group(function () {
    Route::get('auth/google/redirect', [AuthController::class, 'redirectToGoogle']);
    Route::get('auth/google/callback', [AuthController::class, 'handleGoogleCallback']);
});

// Services (Consultation publique)
Route::get('services', [ServiceController::class, 'index']);
Route::get('services/{id}', [ServiceController::class, 'show']);
Route::get('services/{id}/taches', [ServiceController::class, 'getTaches']);

// Intervenants (Consultation publique - Recherche)
Route::get('intervenants', [IntervenantController::class, 'index']); // Liste simple ou search
Route::get('intervenants/search', [IntervenantController::class, 'search']); // Recherche avancée
Route::get('intervenants/{id}', [IntervenantController::class, 'show']);
Route::get('intervenants/{id}/active-services-tasks', [IntervenantController::class, 'getActiveServicesAndTasks']);


Route::put('intervenants/{id}/taches/{tacheId}/configure', [IntervenantController::class, 'configureTask']);

// Service activation
Route::get('intervenants/{id}/services-with-activation', [IntervenantController::class, 'getServicesWithActivation']);
Route::post('intervenants/{id}/services/{serviceId}/toggle', [IntervenantController::class, 'toggleService']);
Route::post('intervenants/{id}/services/{serviceId}/status', [IntervenantController::class, 'updateServiceStatus']);
Route::post('intervenants/{id}/services/{serviceId}/materials', [IntervenantController::class, 'updateServiceMaterials']);
Route::get('intervenants/{id}/services/{serviceId}/materials', [IntervenantController::class, 'getIntervenantMaterials']);
Route::delete('intervenants/{id}/materials/{materialId}', [IntervenantController::class, 'deleteIntervenantMaterial']);
Route::post('intervenants/{id}/services/{serviceId}/request-activation', [IntervenantController::class, 'requestActivation']);

// ======================
// Routes Statistiques (publiques pour consultation)
// ======================
Route::get('stats', [StatsController::class, 'index']);
Route::get('intervenants/{intervenantId}/reviews-stats', [StatsController::class, 'getIntervenantReviewsStats']);
Route::get('testimonials', [\App\Http\Controllers\Api\CommentaireController::class, 'landingTestimonials']);


// ======================
// Routes Protégées (Auth Sanctuim)
// ======================
Route::middleware('auth:sanctum')->group(function () {
    
    // Auth User
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::get('auth/user', [AuthController::class, 'user']);
    Route::put('auth/profile', [AuthController::class, 'updateProfile']);
    Route::post('auth/profile/avatar', [AuthController::class, 'updateAvatar']);
    Route::post('auth/change-password', [AuthController::class, 'changePassword']);

    // Services (Modification - Admin principalement, ou via Resource except index/show)
    // On exclut index et show car ils sont définis en public au-dessus
    Route::apiResource('services', ServiceController::class)->except(['index', 'show']);
    Route::get('services/{id}/justificatifs', [ServiceController::class, 'getJustificatifs']);
    Route::get('services/{id}/information', [ServiceController::class, 'getInformation']);

    // Tâches
    Route::get('taches/{id}/intervenants', [TacheController::class, 'getIntervenants']);
    Route::get('taches/{id}/materiels', [TacheController::class, 'getMateriels']);
    Route::get('taches/{id}/contraintes', [TacheController::class, 'getContraintes']);

    // Intervenants (Actions authentifiées)
    Route::post('intervenants', [IntervenantController::class, 'store']);
    Route::put('intervenants/{id}', [IntervenantController::class, 'update']);
    Route::delete('intervenants/{id}', [IntervenantController::class, 'destroy']);
    
    // Intervenant Actuel (Me)
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
    // Specific routes must come BEFORE apiResource routes
    Route::get('clients/{interventionId}/profile-for-intervenant', [ClientController::class, 'getProfileForIntervenant']);
    Route::get('clients/{id}/interventions', [ClientController::class, 'interventions']);
    Route::post('clients/{id}/favorites', [ClientController::class, 'addFavorite']);
    Route::delete('clients/{id}/favorites/{intervenantId}', [ClientController::class, 'removeFavorite']);
    Route::apiResource('clients', ClientController::class);

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
    Route::get('intervenants/me/reservations', [IntervenantController::class, 'myReservations']);
    Route::post('intervenants/me/reservations/{id}/accept', [IntervenantController::class, 'acceptReservation']);
    Route::post('intervenants/me/reservations/{id}/refuse', [IntervenantController::class, 'refuseReservation']);

    // Actions sur Intervenant par ID (pour Admin ou autres)
    Route::put('intervenants/{id}/taches/{tacheId}/configure', [IntervenantController::class, 'configureTask']);
    Route::get('intervenants/{id}/services-with-activation', [IntervenantController::class, 'getServicesWithActivation']);
    Route::post('intervenants/{id}/services/{serviceId}/toggle', [IntervenantController::class, 'toggleService']);
    Route::post('intervenants/{id}/services/{serviceId}/status', [IntervenantController::class, 'updateServiceStatus']);
    Route::post('intervenants/{id}/services/{serviceId}/materials', [IntervenantController::class, 'updateServiceMaterials']);
    Route::post('intervenants/{id}/services/{serviceId}/request-activation', [IntervenantController::class, 'requestActivation']);
    Route::get('intervenants/{id}/interventions', [IntervenantController::class, 'interventions']);
    Route::get('intervenants/{id}/disponibilites', [IntervenantController::class, 'disponibilites']);
    Route::get('intervenants/{id}/taches', [IntervenantController::class, 'taches']);
    Route::get('intervenants/{id}/services', [IntervenantController::class, 'services']);
    Route::get('intervenants/{id}/evaluations', [IntervenantController::class, 'evaluations']);

    // Interventions
    Route::apiResource('interventions', InterventionController::class);
    
    // Intervenant Specific Interventions
    Route::apiResource('intervenant-interventions', InterventionControllerIntervenant::class);
    Route::get('intervenant-interventions/filter/upcoming', [InterventionControllerIntervenant::class, 'upcoming']);
    Route::get('intervenant-interventions/filter/completed', [InterventionControllerIntervenant::class, 'completed']);

    Route::get('interventions/index', [InterventionController::class, 'index']); // legacy?
    Route::get('interventions/filter/upcoming', [InterventionController::class, 'upcoming']);
    Route::get('interventions/filter/completed', [InterventionController::class, 'completed']);
    Route::post('interventions/{id}/cancel', [InterventionController::class, 'cancelIntervention']);
    Route::post('interventions/{id}/photos', [InterventionController::class, 'addPhoto']);

    // Evaluations
    Route::post('interventions/{interventionId}/evaluations', [\App\Http\Controllers\Api\Evaluation\EvaluationController::class, 'store']);
    Route::get('interventions/{interventionId}/evaluations', [\App\Http\Controllers\Api\Evaluation\EvaluationController::class, 'show']);

    // Clients
    Route::apiResource('clients', ClientController::class);
    Route::get('clients/{clientId}/interventions', [InterventionController::class, 'getClientInterventions']);
    Route::get('clients/{clientId}/statistics', [InterventionController::class, 'getClientStatistics']);
    Route::get('clients/{id}/profile/statistics', [ClientProfileController::class, 'getStatistics']);
    Route::get('clients/{id}/profile/history', [ClientProfileController::class, 'getHistory']);
    Route::get('clients/{id}/profile/evaluations', [ClientProfileController::class, 'getEvaluations']);
    
    // Favoris
    Route::get('clients/{id}/favorites', [\App\Http\Controllers\Api\Client\FavorisController::class, 'index']);
    Route::post('clients/{id}/favorites', [\App\Http\Controllers\Api\Client\FavorisController::class, 'toggle']);
    Route::get('clients/{id}/favorites/check', [\App\Http\Controllers\Api\Client\FavorisController::class, 'checkStatus']);

    // ======================
    // Routes Admin
    // ======================
    Route::prefix('admin')->middleware('admin')->group(function () {
        // Dashboard
        Route::get('stats', [AdminController::class, 'stats']);

        // Management
        Route::get('clients', [AdminController::class, 'getClients']);
        Route::get('clients/{id}', [AdminController::class, 'getClientDetails']);
        Route::post('clients/{id}/toggle-status', [AdminController::class, 'toggleClientStatus']);

        Route::get('intervenants', [AdminController::class, 'getIntervenants']);
        Route::get('intervenants/{id}', [AdminController::class, 'getIntervenantDetails']);
        Route::post('intervenants/{id}/toggle-status', [AdminController::class, 'toggleIntervenantStatus']);
        Route::get('justificatifs/{id}/download', [AdminController::class, 'downloadJustificatif']);

        Route::get('demandes', [AdminController::class, 'getPendingRequests']);
        Route::post('demandes/{id}/approve', [AdminController::class, 'approveRequest']);
        Route::post('demandes/{id}/reject', [AdminController::class, 'rejectRequest']);

        Route::get('services', [AdminController::class, 'getServices']);
        Route::post('services', [AdminController::class, 'createService']);
        Route::post('services/{id}/toggle-status', [AdminController::class, 'toggleServiceStatus']);
        Route::get('services/{id}/stats', [AdminController::class, 'getServiceStats']);
        
        Route::get('services/{serviceId}/taches', [AdminController::class, 'getServiceTaches']);
        Route::post('services/{serviceId}/taches', [AdminController::class, 'createTache']);
        Route::put('taches/{tacheId}', [AdminController::class, 'updateTache']);
        Route::delete('taches/{tacheId}', [AdminController::class, 'deleteTache']);

        Route::get('reclamations', [AdminController::class, 'getReclamations']);
        Route::post('reclamations/{id}/handle', [AdminController::class, 'handleReclamation']);

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

    // ======================
    // Routes Évaluations
    // ======================
    Route::get('evaluations/client-criteria', [EvaluationController::class, 'getClientCriteria']);
    Route::get('evaluations/intervenant-criteria', [EvaluationController::class, 'getIntervenantCriteria']);
    Route::post('evaluations/interventions/{interventionId}/rate-client', [EvaluationController::class, 'storeClientEvaluation']);
    Route::get('evaluations/interventions/{interventionId}/client-ratings', [EvaluationController::class, 'getClientEvaluations']);
    Route::get('evaluations/interventions/{interventionId}/can-rate-client', [EvaluationController::class, 'canRateClient']);
    Route::get('evaluations/interventions/{interventionId}/public', [EvaluationController::class, 'getPublicEvaluations']);
    Route::get('evaluations/clients/{clientId}/average-rating', [EvaluationController::class, 'getClientAverageRating']);
    Route::get('debug/auth', function () {
        $user = Auth::user();
        return response()->json([
            'authenticated' => !!$user,
            'user' => $user ? [
                'id' => $user->id,
                'email' => $user->email,
                'userable_type' => $user->userable_type,
                'userable_id' => $user->userable_id,
            ] : null
        ]);
    });
    Route::get('debug/intervention/{id}', function ($id) {
        $intervention = \App\Models\Intervention::find($id);
        $user = Auth::user();
        return response()->json([
            'intervention' => $intervention ? [
                'id' => $intervention->id,
                'status' => $intervention->status,
                'intervenant_id' => $intervention->intervenant_id,
                'client_id' => $intervention->client_id,
            ] : null,
            'current_user' => $user ? [
                'id' => $user->id,
                'email' => $user->email,
                'userable_type' => $user->userable_type,
                'userable_id' => $user->userable_id,
                'matches_intervenant' => $user->userable_type === 'App\\Models\\Intervenant' && $user->userable_id === $intervention->intervenant_id
            ] : null
        ]);
    });
    Route::get('debug/all-interventions', function () {
        $user = Auth::user();
        $interventions = \App\Models\Intervention::where('intervenant_id', $user->userable_id ?? -1)->get();
        return response()->json([
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'userable_type' => $user->userable_type,
                'userable_id' => $user->userable_id,
            ],
            'user_interventions' => $interventions->map(function ($intervention) {
                return [
                    'id' => $intervention->id,
                    'status' => $intervention->status,
                    'intervenant_id' => $intervention->intervenant_id,
                ];
            })
        ]);
    });

    Route::get('intervenants/me/taches', [IntervenantController::class, 'myTaches']);
    Route::put('intervenants/me/taches/{tacheId}', [IntervenantController::class, 'updateMyTache']);
    Route::post('intervenants/me/taches/{tacheId}/toggle-active', [IntervenantController::class, 'toggleActiveMyTache']);
    Route::delete('intervenants/me/taches/{tacheId}', [IntervenantController::class, 'deleteMyTache']);
});
