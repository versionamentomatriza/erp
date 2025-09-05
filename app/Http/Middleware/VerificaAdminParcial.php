<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class VerificaAdminParcial
{
    public function handle($request, Closure $next)
    {
        // Permite acesso para Super Admin completo (master = 2) e Super Admin parcial (master = 1)
        if (Auth::check() && (Auth::user()->master === 2 || Auth::user()->master === 1)) {
            return $next($request);
        }

        // Redireciona para home com mensagem de erro se não tiver permissão
        session()->flash("flash_error", "Acesso restrito");
        return redirect()->route('home');
    }
}
