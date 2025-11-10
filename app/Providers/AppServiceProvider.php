<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Localizacao;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrap();
        
         // Aumenta o tempo máximo de execução para 300 segundos
        ini_set('max_execution_time', 300); // 300 segundos
        set_time_limit(300);

        // Configurações de memória e tamanho de upload
        ini_set('memory_limit', '512M'); // memória máxima
        ini_set('post_max_size', '64M');  // tamanho máximo POST
        ini_set('upload_max_filesize', '64M'); // tamanho máximo de upload

        // Aumenta o tempo máximo de execução para 300 segundos
        ini_set('max_execution_time', 300); // 300 segundos
        set_time_limit(300);

        // Configurações de memória e tamanho de upload
        ini_set('memory_limit', '512M'); // memória máxima
        ini_set('post_max_size', '64M');  // tamanho máximo POST
        ini_set('upload_max_filesize', '64M'); // tamanho máximo de upload

        // Macro para adicionar dias úteis considerando feriados
        Carbon::macro('addDiasUteisComFeriados', function (int $dias): Carbon {
            $carbon = $this->copy();
            while ($dias > 0) {
                $carbon->addDay();
                if ($carbon->isBusinessDay()) {
                    $dias--;
                }
            }
            return $carbon;
        });

        // Compartilha a variável $filial com todas as views
        View::composer('*', function ($view) {
            $filial = null;

            // Verifica se o usuário está autenticado
            if (Auth::check()) {
                $caixa = __isCaixaAberto();

                if ($caixa && $caixa->local_id) {
                    $filial = Localizacao::find($caixa->local_id);
                }
            }

            // Envia para a view
            $view->with('filial', $filial);
        });
    }
}
