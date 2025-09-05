<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use App\Models\PlanoEmpresa;
use App\Models\Empresa;
use Carbon\Carbon;

class ValidaPlano
{
	public function handle($request, Closure $next)
	{
		if (__isMaster()) {
			return $next($request);
		}

		$empresa_id = auth::user()->empresa ? auth::user()->empresa->empresa_id : null;
		if ($empresa_id == null) {
			session()->flash("flash_error", "Usuário sem empresa!");
			return redirect()->route('home');
		}

		$emp = Empresa::findOrFail($empresa_id);
		if ($emp->status == 0) {
			session()->flash("flash_error", "Empresa desativada!");
			return redirect()->route('home');
		}

		$plano = PlanoEmpresa::where('empresa_id', $empresa_id)
			->orderBy('data_expiracao', 'desc')
			->first();

		if ($plano == null) {
			session()->flash("flash_error", "Empresa sem plano atribuído!");
			return redirect()->route('home');
		}

		$dataExpiracao = Carbon::parse($plano->data_expiracao);
		$graca = $this->addDiasUteis($dataExpiracao, 3);

		if (Carbon::today()->gt($graca)) {
			session()->flash("flash_error", "Plano expirado!");
			return redirect()->route('home');
		}

		return $next($request);
	}

	/**
	 * Soma dias úteis (ignora sábados e domingos)
	 */
	private function addDiasUteis(Carbon $data, int $dias): Carbon
	{
		$diasAdicionados = 0;
		while ($diasAdicionados < $dias) {
			$data->addDay();
			if (!$data->isWeekend()) {
				$diasAdicionados++;
			}
		}
		return $data;
	}
}
