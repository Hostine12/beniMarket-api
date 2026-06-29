<?php

namespace App\Http\Controllers;

use App\Models\WalletTransaction;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    /**
     * Portefeuille de l'utilisateur connecté : solde courant + historique des
     * paiements reçus (et autres mouvements).
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $transactions = WalletTransaction::with('order:id,reference')
            ->where('user_id', $user->id)
            ->latest()
            ->paginate(30);

        return response()->json([
            'balance'        => $user->walletBalance(),
            'currency'       => 'XOF',
            'total_received' => (float) WalletTransaction::where('user_id', $user->id)
                                    ->where('type', 'credit')->sum('amount'),
            'transactions'   => $transactions,
        ]);
    }
}
