<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Empresa;
use App\Models\AcessoLog;
use App\Models\PlanoEmpresa;
use App\Models\Plano;
use Dompdf\Dompdf;
use NFePHP\Common\Certificate;

class RelatorioAdmController extends Controller
{
    public function index()
    {
        $planos = Plano::orderBy('nome')->pluck('nome', 'id');
        return view('relatorios_adm.index', compact('planos'));
    }

    public function empresas(Request $request)
    {
        $start_date = $request->start_date;
        $end_date = $request->end_date;

        $data = Empresa::when(!empty($start_date), fn($q) => $q->whereDate('created_at', '>=', $start_date))
            ->when(!empty($end_date), fn($q) => $q->whereDate('created_at', '<=', $end_date))
            ->get();

        $p = view('relatorios_adm.empresas', compact('data'))
            ->with('title', 'Relatório de Empresas');

        $domPdf = new Dompdf(["enable_remote" => true]);
        $domPdf->loadHtml($p);
        $pdf = ob_get_clean();

        $domPdf->setPaper("A4", "landscape");
        $domPdf->render();
        $domPdf->stream("Relatório de Empresas.pdf", ["Attachment" => false]);
    }

    public function historicoAcesso(Request $request)
    {
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $empresa = $request->empresa;

        $data = AcessoLog::when(!empty($start_date), fn($q) => $q->whereDate('acesso_logs.created_at', '>=', $start_date))
            ->when(!empty($end_date), fn($q) => $q->whereDate('acesso_logs.created_at', '<=', $end_date))
            ->when($empresa, fn($q) => $q->where('usuario_empresas.empresa_id', $empresa)
                ->join('usuario_empresas', 'acesso_logs.usuario_id', '=', 'usuario_empresas.usuario_id'))
            ->select('acesso_logs.*')
            ->get();

        $p = view('relatorios_adm.historico_acesso', compact('data'))
            ->with('title', 'Relatório de Histórico de Acesso');

        $domPdf = new Dompdf(["enable_remote" => true]);
        $domPdf->loadHtml($p);
        $pdf = ob_get_clean();

        $domPdf->setPaper("A4", "landscape");
        $domPdf->render();
        $domPdf->stream("Relatório de Histórico de Acesso.pdf", ["Attachment" => false]);
    }

    public function planos(Request $request)
    {
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $start_created_at = $request->start_created_at;
        $end_created_at = $request->end_created_at;
        $plano_id = $request->plano_id;
        $status =  $request->status;

        $data = PlanoEmpresa::when(!empty($start_date), fn($q) => $q->whereDate('data_expiracao', '>=', $start_date))
            ->when(!empty($end_date), fn($q) => $q->whereDate('data_expiracao', '<=', $end_date))
            ->when(!empty($start_created_at), fn($q) => $q->whereDate('created_at', '>=', $start_created_at))
            ->when(!empty($end_created_at), fn($q) => $q->whereDate('created_at', '<=', $end_created_at))
            ->when(!empty($status), function ($q) use ($status) {
                if ($status === 'ativo') {
                    $q->whereDate('data_expiracao', '>=', now());
                } elseif ($status === 'inativo') {
                    $q->whereDate('data_expiracao', '<', now());
                }
            })
            ->when(!empty($plano_id), fn($q) => $q->where('plano_id', $plano_id))
            ->get();

        $p = view('relatorios_adm.planos', compact('data'))
            ->with('title', 'Relatório de Planos');

        $domPdf = new Dompdf(["enable_remote" => true]);
        $domPdf->loadHtml($p);
        $pdf = ob_get_clean();

        $domPdf->setPaper("A4", "landscape");
        $domPdf->render();
        $domPdf->stream("Relatório de Planos.pdf", ["Attachment" => false]);
    }

    public function certificados(Request $request)
    {
        $start_date = $request->start_date;
        $end_date = $request->end_date;

        if (!$start_date || !$end_date) {
            session()->flash("flash_warning", "Informe a data inicial e final!");
            return redirect()->back();
        }

        $empresas = Empresa::all();
        $dataHoje = date('Y-m-d');
        $data = [];

        foreach ($empresas as $e) {
            if ($e->arquivo) {
                try {
                    $infoCertificado = Certificate::readPfx($e->arquivo, $e->senha);
                    $publicKey = $infoCertificado->publicKey;

                    $e->vencimento = $publicKey->validTo->format('Y-m-d');
                    $e->vencido = strtotime($dataHoje) > strtotime($e->vencimento);

                    if (
                        strtotime($e->vencimento) > strtotime($start_date) &&
                        strtotime($e->vencimento) < strtotime($end_date)
                    ) {
                        $data[] = $e;
                    }
                } catch (\Exception $e) {
                    echo $e->getMessage();
                }
            }
        }

        usort($data, fn($a, $b) => strtotime($a->vencimento) > strtotime($b->vencimento) ? 1 : 0);

        $p = view('relatorios_adm.certificados', compact('data'))
            ->with('title', 'Certificados');

        $domPdf = new Dompdf(["enable_remote" => true]);
        $domPdf->loadHtml($p);
        $pdf = ob_get_clean();

        $domPdf->setPaper("A4", "landscape");
        $domPdf->render();
        $domPdf->stream("Certificados.pdf", ["Attachment" => false]);
    }
}
