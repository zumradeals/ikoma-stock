<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\SupportLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ImpersonationController extends Controller
{
    public function start(Request $request, User $user): RedirectResponse
    {
        $superAdmin = $request->user();

        abort_unless($superAdmin->role === UserRole::SUPER_ADMIN, 403);
        abort_if($user->role === UserRole::SUPER_ADMIN, 403, 'Impossible d\'impersonner un super-admin.');
        abort_if($user->id === $superAdmin->id, 403, 'Auto-impersonation interdite.');
        abort_if(session()->has('impersonating_original_id'), 409, 'Déjà en mode support.');

        SupportLog::create([
            'super_admin_id'       => $superAdmin->id,
            'impersonated_user_id' => $user->id,
            'company_id'           => $user->company_id,
            'ip_address'           => $request->ip(),
            'started_at'           => now(),
        ]);

        session()->put('impersonating_original_id', $superAdmin->id);
        session()->put('impersonating_log_id', SupportLog::latest()->value('id'));

        Auth::login($user);
        session()->put('current_company_id', $user->company_id);
        session()->regenerate();

        return redirect()->route($user->role->landingRoute());
    }

    public function stop(Request $request): RedirectResponse
    {
        abort_unless(session()->has('impersonating_original_id'), 403, 'Pas en mode support.');

        $originalId = session()->pull('impersonating_original_id');
        $logId      = session()->pull('impersonating_log_id');

        if ($logId) {
            SupportLog::find($logId)?->update(['ended_at' => now()]);
        }

        $superAdmin = User::withoutGlobalScopes()->findOrFail($originalId);

        Auth::login($superAdmin);
        session()->forget('current_company_id');
        session()->regenerate();

        return redirect()->route('platform.index');
    }
}
