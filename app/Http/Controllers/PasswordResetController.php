<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    /**
     * Admin génère un lien/token de réinitialisation pour n'importe quel utilisateur.
     * Le token expire dans 24h et oblige le changement à la prochaine connexion.
     * Aucun mot de passe n'est envoyé en clair.
     */
    public function adminGenerateReset(Request $request, $userId)
    {
        $target = User::findOrFail($userId);

        $token = Str::random(64);

        $target->update([
            'reset_token'            => Hash::make($token),
            'reset_token_expires_at' => now()->addHours(24),
            'must_change_password'   => true,
        ]);

        // En production : envoyer le lien par email/SMS
        // Pour l'instant on retourne le token à l'admin (à transmettre de façon sécurisée)
        $resetUrl = config('app.frontend_url', 'http://localhost:5173')
            . '/reset-password?token=' . $token
            . '&uid=' . $target->id;

        // Notifier l'utilisateur
        Notification::create([
            'user_id' => $target->id,
            'type'    => 'password_reset',
            'title'   => 'Réinitialisation de mot de passe',
            'body'    => 'Un lien de réinitialisation de mot de passe a été généré pour votre compte. Il expire dans 24h.',
            'data'    => ['expires_at' => now()->addHours(24)->toIso8601String()],
        ]);

        return response()->json([
            'message'    => 'Lien de réinitialisation généré.',
            'reset_url'  => $resetUrl,
            'expires_at' => now()->addHours(24)->toIso8601String(),
        ]);
    }

    /**
     * Vérifie le token et réinitialise le mot de passe.
     * Oblige le changement à la prochaine connexion.
     */
    public function resetWithToken(Request $request)
    {
        $data = $request->validate([
            'uid'          => 'required|exists:users,id',
            'token'        => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::findOrFail($data['uid']);

        if (!$user->hasValidResetToken() || !Hash::check($data['token'], $user->reset_token)) {
            return response()->json(['message' => 'Lien invalide ou expiré.'], 422);
        }

        $user->update([
            'password'               => $data['new_password'],
            'reset_token'            => null,
            'reset_token_expires_at' => null,
            'must_change_password'   => false,
        ]);

        // Invalider tous les tokens de session
        $user->tokens()->delete();

        return response()->json(['message' => 'Mot de passe réinitialisé avec succès. Veuillez vous reconnecter.']);
    }

    /**
     * L'utilisateur authentifié change son propre mot de passe.
     * Typiquement après une réinitialisation forcée (must_change_password = true).
     */
    public function changeOwn(Request $request)
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

        return response()->json(['message' => 'Mot de passe mis à jour.']);
    }
}
