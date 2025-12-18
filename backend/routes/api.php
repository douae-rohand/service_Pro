<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Intervention\InterventionController;
use App\Http\Controllers\Api\Service\ServiceController;
use App\Http\Controllers\Api\Intervention\TacheController;
use App\Http\Controllers\Api\Client\ClientController;
use App\Http\Controllers\Api\Intervenant\IntervenantController;
use App\Http\Controllers\Api\StatsController;
use App\Http\Controllers\Api\Evaluation\EvaluationController;


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
