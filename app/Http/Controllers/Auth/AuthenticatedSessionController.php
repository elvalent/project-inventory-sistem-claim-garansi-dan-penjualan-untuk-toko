<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Support\PortalRoute;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login', ['loginRole' => null]);
    }

    public function createForRole(string $routeRole): View
    {
        abort_unless(in_array($routeRole, ['admin', 'sales', 'customer'], true), 404);

        return view('auth.login', ['loginRole' => $routeRole]);
    }

    public function storeForRole(LoginRequest $request, string $routeRole): RedirectResponse
    {
        $expectedRole = match ($routeRole) {
            'admin' => 'admin',
            'sales' => 'sales',
            'customer' => 'pelanggan',
            default => abort(404),
        };

        // Credentials are checked together with the requested role.
        // An Admin account cannot be used to enter the Sales or Customer portal.
        $request->authenticate($expectedRole);
        $request->session()->regenerate();

        return redirect()->route(PortalRoute::resolve('dashboard', $expectedRole));
    }

    public function destroyForRole(Request $request, string $routeRole): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/' . $routeRole . '/login');
    }

    /** Legacy default-session logout, retained for non-portal Breeze pages. */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
