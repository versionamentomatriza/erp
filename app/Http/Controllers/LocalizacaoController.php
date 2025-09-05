<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Localizacao;
use Illuminate\Support\Facades\DB;
use App\Models\UsuarioLocalizacao;
use App\Models\Empresa;
use NFePHP\Common\Certificate;

class LocalizacaoController extends Controller
{
    public function index(Request $request){
        $data = Localizacao::where('empresa_id', $request->empresa_id)
        ->orderBy('status')
        ->get();

        return view('localizacao.index', compact('data'));
    }

    public function create(){
        $dadosCertificado = null;
        $count = Localizacao::where('empresa_id', request()->empresa_id)->count();
        $count++;
        return view('localizacao.create', compact('dadosCertificado', 'count'));
    }

    public function edit($id){
        $item = Localizacao::findOrFail($id);
        $temp = Localizacao::where('empresa_id', request()->empresa_id)->first();

        $firstLocation = $item == $temp;
        __validaObjetoEmpresa($item);

        $dadosCertificado = null;

        if ($item != null && $item->arquivo) {
            $dadosCertificado = $this->getInfoCertificado($item);
        }

        return view('localizacao.edit', compact('item', 'firstLocation', 'dadosCertificado'));
    }

    private function getInfoCertificado($item)
    {
        try {
            $infoCertificado = Certificate::readPfx($item->arquivo, $item->senha);
            $publicKey = $infoCertificado->publicKey;
            $inicio =  $publicKey->validFrom->format('Y-m-d H:i:s');
            $expiracao =  $publicKey->validTo->format('Y-m-d H:i:s');
            return [
                'serial' => $publicKey->serialNumber,
                'inicio' => \Carbon\Carbon::parse($inicio)->format('d-m-Y H:i'),
                'expiracao' => \Carbon\Carbon::parse($expiracao)->format('d-m-Y H:i'),
                'id' => $publicKey->commonName
            ];
        } catch (\Exception $e) {
            return [];
        }
    }

    public function store(Request $request)
    {
        $this->__validate($request);
        try {

            DB::transaction(function () use ($request) {

                if ($request->hasFile('certificado')) {
                    $file = $request->file('certificado');
                    $fileTemp = file_get_contents($file);
                    $request->merge([
                        'arquivo' => $fileTemp ?? '',
                        'cpf_cnpj' => preg_replace('/[^0-9]/', '', $request->cpf_cnpj),
                        'senha' => $request->senha_certificado,
                        'token' => $request->token ?? '',
                        'csc' => $request->csc ? $request->csc : 'AAAAAA',
                        'csc_id' => $request->csc_id ? $request->csc_id : '000001',
                    ]);
                }

                $localizacao = Localizacao::create($request->all());

                $empresa = Empresa::findOrFail($request->empresa_id);
                foreach($empresa->usuarios as $u){
                    UsuarioLocalizacao::updateOrCreate([
                        'usuario_id' => $u->usuario_id,
                        'localizacao_id' => $localizacao->id
                    ]);
                }

                return true;
            });
            session()->flash("flash_success", "Localização cadastrada!");
        } catch (\Exception $e) {
            // echo $e->getMessage();
            // die;
            session()->flash("flash_error", "Algo deu errado: " . $e->getMessage());
        }
        return redirect()->route('localizacao.index');
    }

    public function update(Request $request, $id)
    {
        $item = Localizacao::findOrFail($id);

        try {

            if ($request->hasFile('certificado')) {

                $file = $request->file('certificado');
                $fileTemp = file_get_contents($file);
                $request->merge([
                    'arquivo' => $fileTemp,
                    'senha' => $request->senha_certificado
                ]);
            }

            $item->fill($request->all())->save();
            session()->flash("flash_success", "Localização atualizada!");
        } catch (\Exception $e) {
            session()->flash("flash_error", "Algo deu errado: " . $e->getMessage());
        }
        return redirect()->route('localizacao.index');
    }

    public function destroy($id)
    {

        $item = Localizacao::findOrFail($id);

        try {

            $item->delete();
            session()->flash("flash_success", "Localização removida!");
        } catch (\Exception $e) {
            session()->flash("flash_error", "Algo deu Errado: " . $e->getMessage());
        }
        return redirect()->back();
    }

    private function __validate(Request $request)
    {
        $rules = [
            'nome' => 'required',
            'cpf_cnpj' => 'required',
            'ie' => 'required',
            'celular' => 'required',
            'cep' => 'required',
            'rua' => 'required',
            'numero' => 'required',
            'bairro' => 'required',
            'cidade_id' => 'required',
            'email' => 'unique:users',
        ];
        $messages = [
            'nome.required' => 'Campo Obrigatório',
            'cpf_cnpj.required' => 'Campo Obrigatório',
            'ie.required' => 'Campo Obrigatório',
            'email.required' => 'Campo Obrigatório',
            'celular.required' => 'Campo Obrigatório',
            'csc.required' => 'Campo Obrigatório',
            'csc_id.required' => 'Campo Obrigatório',
            'cep.required' => 'Campo Obrigatório',
            'rua.required' => 'Campo Obrigatório',
            'numero.required' => 'Campo Obrigatório',
            'bairro.required' => 'Campo Obrigatório',
            'cidade_id.required' => 'Campo Obrigatório',
            'numero_ultima_nfe_producao.required' => 'Campo Obrigatório',
            'numero_ultima_nfe_homologacao.required' => 'Campo Obrigatório',
            'numero_serie_nfe.required' => 'Campo Obrigatório',
            'numero_ultima_nfce_producao.required' => 'Campo Obrigatório',
            'numero_ultima_nfce_homologacao.required' => 'Campo Obrigatório',
            'numero_serie_nfce.required' => 'Campo Obrigatório',
            'email.unique' => 'Já existe um usuário com este email',
        ];
        $this->validate($request, $rules, $messages);
    }


}
