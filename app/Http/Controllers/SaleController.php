<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\Sale;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class SaleController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $sales = Sale::query()
            ->with(['customer', 'outlet', 'invoice'])
            ->when(
                ! in_array($user->role, [UserRole::ADMIN_COMPANY, UserRole::OUTLET_MANAGER], true),
                fn ($query) => $query->where('outlet_id', $user->outlet_id)
            )
            ->latest()
            ->paginate(20);

        return view('sales.index', ['sales' => $sales]);
    }
}
