<?php

namespace App\Http\Middleware;

use Closure;

class VerificaMaster
{
  public function handle($request, Closure $next)
  {
    // Permitir acesso para Super Admin completo ou parcial
    if (__isMaster()) {
      return $next($request);
    } elseif (__isPartialSuperAdmin()) {
      return $next($request);
    }

    // Redireciona se o usuário não tiver permissão
    session()->flash("flash_error", "Acesso restrito");
    return redirect()->route('home');
  }
}
