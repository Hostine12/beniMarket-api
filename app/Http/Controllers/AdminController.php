<?php

namespace App\Http\Controllers;

use App\Models\Dispute;
use App\Models\Notification;
use App\Models\Order;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function stats()
    {
        $totalUsers    = User::where('role', '!=', 'admin')->count();
        $totalVendors  = User::where('role', 'vendor')->count();
        $totalCouriers = User::where('role', 'courier')->count();
        $totalClients  = User::where('role', 'client')->count();

        $pendingVendors  = User::where('role', 'vendor')->where('status', 'pending')->count();
        $pendingCouriers = User::where('role', 'courier')->where('status', 'pending')->count();

        $totalOrders    = Order::count();
        $paidOrders     = Order::where('payment_status', 'paid')->count();

        // ── Répartition financière (commandes payées) ──────────────────────────
        // GMV = volume total des transactions (≠ revenu). Chaque part a un destinataire :
        //   • service_fee  → revenu de la plateforme (administrateur)
        //   • subtotal     → versé aux vendeurs
        //   • delivery_fee → versé aux livreurs (600 F par course)
        $gmv             = Order::where('payment_status', 'paid')->sum('total');
        $platformRevenue = Order::where('payment_status', 'paid')->sum('service_fee');
        $vendorPayouts   = Order::where('payment_status', 'paid')->sum('subtotal');
        $courierPayouts  = Order::where('payment_status', 'paid')->sum('delivery_fee');

        $openDisputes   = Dispute::whereIn('status', ['open', 'in_review'])->count();
        $totalShops     = Shop::count();
        $activeShops    = Shop::where('status', 'active')->count();

        $salesByMonth = [];
        for ($m = 1; $m <= 12; $m++) {
            $salesByMonth[] = Order::where('payment_status', 'paid')
                ->whereMonth('created_at', $m)
                ->whereYear('created_at', date('Y'))
                ->sum('total');
        }

        $recentOrders = Order::with(['client', 'items'])
            ->latest()
            ->take(10)
            ->get();

        $topVendors = Shop::withSum(['products as revenue' => fn($q) => $q->join('order_items', 'products.id', '=', 'order_items.product_id')], 'order_items.total')
            ->orderByDesc('revenue')
            ->take(5)
            ->get();

        return response()->json([
            'total_users'       => $totalUsers,
            'total_vendors'     => $totalVendors,
            'total_couriers'    => $totalCouriers,
            'total_clients'     => $totalClients,
            'pending_vendors'   => $pendingVendors,
            'pending_couriers'  => $pendingCouriers,
            'total_orders'      => $totalOrders,
            'paid_orders'       => $paidOrders,
            'gmv'               => $gmv,
            'platform_revenue'  => $platformRevenue, // revenu réel de la plateforme (frais de service)
            'vendor_payouts'    => $vendorPayouts,    // montant dû aux vendeurs
            'courier_payouts'   => $courierPayouts,   // montant dû aux livreurs (600 F / course)
            'open_disputes'     => $openDisputes,
            'total_shops'       => $totalShops,
            'active_shops'      => $activeShops,
            'sales_by_month'    => $salesByMonth,
            'recent_orders'     => $recentOrders,
            'top_vendors'       => $topVendors,
        ]);
    }

    public function users(Request $request)
    {
        $users = User::with('shop')
            ->when($request->role, fn($q) => $q->where('role', $request->role))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->search, fn($q) => $q->where(function ($q2) use ($request) {
                $q2->where('name', 'like', '%' . $request->search . '%')
                   ->orWhere('email', 'like', '%' . $request->search . '%')
                   ->orWhere('phone', 'like', '%' . $request->search . '%');
            }))
            ->latest()
            ->paginate(30);

        return response()->json($users);
    }

    public function updateUserStatus(Request $request, $id)
    {
        $data = $request->validate([
            'status' => 'required|in:actif,pending,banned',
        ]);

        $user = User::findOrFail($id);

        if ($user->role === 'admin') {
            return response()->json(['message' => 'Impossible de modifier un administrateur.'], 403);
        }

        $user->update(['status' => $data['status']]);

        Notification::create([
            'user_id' => $user->id,
            'type'    => 'account_status',
            'title'   => 'Statut de compte mis à jour',
            'body'    => $data['status'] === 'actif'
                ? 'Votre compte a été validé par l\'administration. Bienvenue sur BeniMarket !'
                : ($data['status'] === 'banned'
                    ? 'Votre compte a été suspendu. Contactez l\'administration.'
                    : 'Votre compte est en attente de validation.'),
            'data' => ['status' => $data['status']],
        ]);

        return response()->json($user);
    }

    public function pendingVendors()
    {
        return response()->json(
            User::where('role', 'vendor')
                ->where('status', 'pending')
                ->with('shop')
                ->latest()
                ->get()
        );
    }

    public function pendingCouriers()
    {
        return response()->json(
            User::where('role', 'courier')
                ->where('status', 'pending')
                ->latest()
                ->get()
        );
    }

    public function validateAccount(Request $request, $id)
    {
        $data = $request->validate([
            'action' => 'required|in:approve,reject',
            'reason' => 'nullable|string|max:500',
        ]);

        $user   = User::findOrFail($id);
        $status = $data['action'] === 'approve' ? 'actif' : 'pending';
        $user->update(['status' => $status]);

        if ($data['action'] === 'approve' && $user->role === 'vendor') {
            Shop::where('vendor_id', $user->id)->update(['status' => 'active']);
        }

        Notification::create([
            'user_id' => $user->id,
            'type'    => 'account_validated',
            'title'   => $data['action'] === 'approve' ? 'Compte validé !' : 'Validation en attente',
            'body'    => $data['action'] === 'approve'
                ? 'Félicitations ! Votre compte a été validé par l\'équipe BeniMarket. Vous pouvez maintenant vous connecter.'
                : 'Votre dossier nécessite des compléments d\'information. ' . ($data['reason'] ?? ''),
            'data' => ['action' => $data['action']],
        ]);

        return response()->json(['message' => 'Compte mis à jour.', 'user' => $user]);
    }

    public function pendingShops()
    {
        return response()->json(
            Shop::with('vendor')
                ->where('status', 'pending')
                ->latest()
                ->get()
        );
    }

    public function validateShop(Request $request, $id)
    {
        $data = $request->validate([
            'action' => 'required|in:approve,reject',
        ]);

        $shop = Shop::with('vendor')->findOrFail($id);
        $newStatus = $data['action'] === 'approve' ? 'active' : 'rejected';
        $shop->update(['status' => $newStatus]);

        if ($shop->vendor) {
            Notification::create([
                'user_id' => $shop->vendor_id,
                'type'    => 'shop_validated',
                'title'   => $data['action'] === 'approve' ? 'Boutique activée !' : 'Boutique rejetée',
                'body'    => $data['action'] === 'approve'
                    ? "Votre boutique \"{$shop->name}\" a été validée. Vous pouvez maintenant ajouter vos produits !"
                    : "Votre boutique \"{$shop->name}\" n'a pas pu être validée. Contactez l'administration.",
                'data'    => ['shop_id' => $shop->id, 'action' => $data['action']],
            ]);
        }

        return response()->json(['message' => 'Boutique mise à jour.', 'shop' => $shop]);
    }

    public function shops(Request $request)
    {
        return response()->json(
            Shop::with('vendor')
                ->when($request->status, fn($q) => $q->where('status', $request->status))
                ->latest()
                ->paginate(30)
        );
    }

    public function disputes(Request $request)
    {
        return response()->json(
            Dispute::with(['order.client', 'order.items', 'client'])
                ->when($request->status, fn($q) => $q->where('status', $request->status))
                ->latest()
                ->paginate(30)
        );
    }
}
