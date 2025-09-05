<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class Authh
{
	public function handle($request, Closure $next)
	{
		if (!Auth::user()) {
			return redirect('/login');
		}

		// Definir nível de acesso com base no e-mail do usuário
		if (Auth::user()->email === env("MAILMASTER")) {
			Auth::user()->master = 2; // Super Admin completo
		} elseif (Auth::user()->email === env("MAILSECONDARYADMIN")) {
			Auth::user()->master = 1; // Super Admin parcial
		} else {
			Auth::user()->master = 0; // Usuário comum
		}

		return $next($request);
	}
}
