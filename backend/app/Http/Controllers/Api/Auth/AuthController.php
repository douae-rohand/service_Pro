<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\Utilisateur;
use App\Models\Client;
use App\Models\Intervenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Register a new user
     */
    public function register(Request $request)
    {
        try {
            // Nettoyer les données : convertir les chaînes vides en null
            $data = $request->all();
            foreach ($data as $key => $value) {
                if ($value === '' || $value === null) {
                    $data[$key] = null;
                }
            }
            $request->merge($data);

            $validated = $request->validate([
                'nom' => 'required|string|max:100',
                'prenom' => 'required|string|max:100', // Requis dans le formulaire
                'email' => 'required|string|email|max:150|unique:utilisateur,email',
                'password' => 'required|string|min:8',
                'confirmPassword' => 'nullable|same:password',
                'telephone' => 'nullable|string|max:20',
                'address' => 'nullable|string',
                'adresse' => 'nullable|string', // Alias pour address
                'type' => 'nullable|string|in:client,intervenant',
                // Champs spécifiques pour intervenant
                'ville' => 'nullable|string|max:100',
                'bio' => 'nullable|string',
                'description' => 'nullable|string', // Alias pour bio
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Erreur de validation',
                'errors' => $e->errors(),
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Utiliser adresse si fourni, sinon address
            $address = $validated['adresse'] ?? $validated['address'] ?? null;

            // Créer l'utilisateur
            $user = Utilisateur::create([
                'nom' => $validated['nom'],
                'prenom' => $validated['prenom'] ?? null,
                'email' => $validated['email'],
                'password' => $validated['password'], // Le cast 'hashed' gère automatiquement le hashage
                'telephone' => $validated['telephone'] ?? null,
                'address' => $address,
            ]);

            $userType = $validated['type'] ?? 'client';

            // Créer le client ou l'intervenant selon le type
            if ($userType === 'client') {
                Client::create([
                    'id' => $user->id,
                    'address' => $address,
                    'ville' => $validated['ville'] ?? null,
                    'is_active' => true,
                    'admin_id' => 1, // Par défaut, assigner au premier admin
                ]);
            } elseif ($userType === 'intervenant') {
                Intervenant::create([
                    'id' => $user->id,
                    'address' => $address,
                    'ville' => $validated['ville'] ?? null,
                    'bio' => $validated['bio'] ?? $validated['description'] ?? null,
                    'is_active' => true,
                    'admin_id' => 1, // Par défaut, assigner au premier admin
                ]);
            }

            DB::commit();

            // Charger les relations
            $user->load(['client', 'intervenant', 'admin']);

            $token = $user->createToken('auth-token')->plainTextToken;

            return response()->json([
                'message' => 'Utilisateur créé avec succès',
                'user' => $user,
                'token' => $token,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Erreur lors de l\'inscription: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'message' => 'Erreur lors de la création de l\'utilisateur',
                'error' => config('app.debug') ? $e->getMessage() : 'Une erreur est survenue. Veuillez réessayer.',
            ], 500);
        }
    }

    /**
     * Login a user
     */
    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Erreur de validation',
                'errors' => $e->errors(),
            ], 422);
        }

        $user = Utilisateur::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Les identifiants sont incorrects.',
                'errors' => [
                    'email' => ['Les identifiants sont incorrects.'],
                ],
            ], 401);
        }

        // Révoquer les anciens tokens
        $user->tokens()->delete();

        $token = $user->createToken('auth-token')->plainTextToken;

        // Charger les relations
        $user->load(['client', 'intervenant', 'admin']);

        return response()->json([
            'message' => 'Connexion réussie',
            'user' => $user,
            'token' => $token,
        ]);
    }

    /**
     * Logout the authenticated user
     */
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Déconnexion réussie',
        ]);
    }

    /**
     * Get the authenticated user
     */
    public function user(Request $request)
    {
        $user = $request->user()->load(['client', 'intervenant', 'admin']);
        return response()->json($user);
    }

    /**
     * Update the authenticated user's profile
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'nom' => 'sometimes|string|max:100',
            'prenom' => 'sometimes|string|max:100',
            'telephone' => 'sometimes|string|max:20',
            'address' => 'sometimes|string',
            'url' => 'sometimes|string',
        ]);

        $user->update($validated);

        return response()->json([
            'message' => 'Profil mis à jour avec succès',
            'user' => $user,
        ]);
    }

    /**
     * Change the authenticated user's password
     */
    public function changePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = $request->user();

        if (!Hash::check($validated['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['Le mot de passe actuel est incorrect.'],
            ]);
        }

        $user->update([
            'password' => $validated['password'],
        ]);

        return response()->json([
            'message' => 'Mot de passe changé avec succès',
        ]);
    }
}
