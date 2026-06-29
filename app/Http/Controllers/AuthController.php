<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'name'         => 'required|string|max:255',
            'role'         => 'required|in:client,vendor,courier',
            'password'     => 'required|string|min:6',
            'email'        => 'nullable|email|unique:users,email',
            'phone'        => 'nullable|string|max:20|unique:users,phone',
            'vehicle_type' => 'nullable|string|max:100',
            'plate_number' => 'nullable|string|max:50',
            'avatar'       => 'nullable|image|max:2048', // photo de profil optionnelle
        ]);

        if ($data['role'] === 'client' && empty($data['email'])) {
            return response()->json(['message' => 'L\'email est requis pour un compte client.'], 422);
        }
        if (in_array($data['role'], ['vendor', 'courier']) && empty($data['phone'])) {
            return response()->json(['message' => 'Le numéro de téléphone est requis.'], 422);
        }

        $status = $data['role'] === 'client' ? 'actif' : 'pending';

        // Gestion de la photo de profil
        $avatarPath = null;
        if ($request->hasFile('avatar') && $request->file('avatar')->isValid()) {
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
        }

        $user = User::create([
            'name'         => $data['name'],
            'email'        => $data['email'] ?? null,
            'phone'        => $data['phone'] ?? null,
            'password'     => $data['password'],
            'role'         => $data['role'],
            'status'       => $status,
            'avatar'       => $avatarPath,
            'vehicle_type' => $data['role'] === 'courier' ? ($data['vehicle_type'] ?? null) : null,
            'plate_number' => $data['role'] === 'courier' ? ($data['plate_number'] ?? null) : null,
        ]);

        Notification::create([
            'user_id' => $user->id,
            'type'    => 'welcome',
            'title'   => 'Bienvenue sur BeniMarket !',
            'body'    => $data['role'] === 'client'
                ? 'Votre compte client est actif. Bonne navigation !'
                : 'Votre inscription a été reçue. L\'équipe BeniMarket va vérifier votre dossier sous 72h.',
        ]);

        if ($data['role'] === 'client') {
            $token = $user->createToken('auth_token')->plainTextToken;
            return response()->json([
                'message'      => 'Compte créé avec succès.',
                'user'         => $user,
                'access_token' => $token,
            ], 201);
        }

        return response()->json([
            'message' => 'Inscription enregistrée. En attente de validation administrative.',
            'status'  => 'pending',
        ], 201);
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'identifier' => 'required|string',
            'password'   => 'required|string',
        ]);

        $identifier = trim($data['identifier']);

        $user = filter_var($identifier, FILTER_VALIDATE_EMAIL)
            ? User::where('email', $identifier)->first()
            : User::where('phone', $identifier)->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'identifier' => ['Identifiant ou mot de passe incorrect.'],
            ]);
        }

        if ($user->status === 'banned') {
            return response()->json(['message' => 'Votre compte a été suspendu.'], 403);
        }

        if (in_array($user->role, ['vendor', 'courier']) && $user->status === 'pending') {
            return response()->json(['message' => 'Votre compte est en attente de validation.', 'status' => 'pending'], 403);
        }

        $user->tokens()->delete();
        $token = $user->createToken('auth_token')->plainTextToken;

        $user->load('shop');

        return response()->json([
            'user'                => $user,
            'access_token'        => $token,
            'must_change_password' => $user->must_change_password,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Déconnecté avec succès.']);
    }

    public function me(Request $request)
    {
        $user = $request->user()->load('shop');
        return response()->json($user);
    }

    /**
     * Mise à jour des informations personnelles (tous les rôles).
     * Champs : nom, e-mail, téléphone, photo de profil (data URL/base64),
     * et infos livreur (véhicule, plaque). Le mot de passe a son propre endpoint.
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'name'         => 'sometimes|required|string|max:255',
            'email'        => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone'        => ['nullable', 'string', 'max:20', Rule::unique('users', 'phone')->ignore($user->id)],
            'avatar'       => 'nullable|string', // data URL (base64) ou URL
            'zone'         => 'nullable|string|max:100',
            'vehicle_type' => 'nullable|string|max:100',
            'plate_number' => 'nullable|string|max:50',
        ]);

        // Un client doit conserver un e-mail ; un vendeur/livreur un téléphone.
        if ($user->role === 'client' && array_key_exists('email', $data) && empty($data['email'])) {
            return response()->json(['message' => 'L\'e-mail est requis pour un compte client.'], 422);
        }
        if (in_array($user->role, ['vendor', 'courier']) && array_key_exists('phone', $data) && empty($data['phone'])) {
            return response()->json(['message' => 'Le numéro de téléphone est requis.'], 422);
        }

        $user->update($data);

        return response()->json([
            'message' => 'Profil mis à jour avec succès.',
            'user'    => $user->fresh()->load('shop'),
        ]);
    }

    /**
     * Changement de mot de passe forcé (doit_changer = true).
     */
    public function changePassword(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'current_password' => 'required|string',
            'new_password'     => 'required|string|min:8|confirmed',
        ]);

        if (!Hash::check($data['current_password'], $user->password)) {
            return response()->json(['message' => 'Mot de passe actuel incorrect.'], 422);
        }

        $user->update([
            'password'             => $data['new_password'],
            'must_change_password' => false,
        ]);

        return response()->json(['message' => 'Mot de passe mis à jour avec succès.']);
    }
}
