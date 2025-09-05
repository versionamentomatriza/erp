<?php

namespace App\Http\Controllers;

use App\Models\CategoriaServico;
use App\Models\Servico;
use App\Utils\UploadUtil;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ServicoController extends Controller
{
    protected $util;

    public function __construct(UploadUtil $util)
    {
        $this->util = $util;
        $this->middleware('permission:servico_create', ['only' => ['create', 'store']]);
        $this->middleware('permission:servico_edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:servico_view', ['only' => ['show', 'index']]);
        $this->middleware('permission:servico_delete', ['only' => ['destroy']]);
    }

    private function insertHash(){
        $servicos = Servico::where('empresa_id', request()->empresa_id)
        ->where('hash_delivery', null)->get();
        foreach($servicos as $s){
            $s->hash_delivery = Str::random(50);
            $s->save();
        }
    }

  public function index(Request $request)
{
    $this->insertHash();

    // Ordenação só se clicado
    $sort = $request->get('sort');
    $direction = $request->get('direction', 'asc');

    $status = $request->status;
    $nome = $request->nome;

    $query = Servico::where('empresa_id', $request->empresa_id)
        ->when(!empty($nome), function ($q) use ($nome) {
            $q->where('nome', 'LIKE', "%$nome%");
        })
        ->when($status !== null && $status !== '', function ($q) use ($status) {
            $q->where('status', $status);
        });

    // Só aplica ordenação se tiver "sort" no request
    if ($sort) {
        $query->orderBy($sort, $direction);
    }

    $data = $query->paginate(env("PAGINACAO"));

    return view('servicos.index', compact('data'));
}

    public function create(Request $request)
    {
        $categorias = CategoriaServico::where('empresa_id', request()->empresa_id)->get();

        if (sizeof($categorias) == 0) {
            session()->flash('flash_warning', 'Cadastre uma categoria de serviço antes de continuar!');
            return redirect()->route('categoria-servico.create');
        }

        $marketplace = 0;
        if (isset($request->marketplace)) {
            $marketplace = 1;
        }
        return view('servicos.create', compact('categorias', 'marketplace'));
    }

    public function store(Request $request)
    {
        // dd($request);
        try {
            $file_name = '';
            if ($request->hasFile('image')) {
                $file_name = $this->util->uploadImage($request, '/servicos');
            }

            if($request->padrao_reserva_nfse == 1){
                Servico::where('empresa_id', $request->empresa_id)
                ->update(['padrao_reserva_nfse' => 0]);
            }

            $request->merge([
                'valor' => __convert_value_bd($request->valor),
                'imagem' => $file_name,
                'comissao' => $request->comissao ? __convert_value_bd($request->comissao) : 0,
                'tempo_tolerancia' => $request->tempo_tolerancia ?? 0,
                'tempo_adicional' => $request->tempo_adicional ?? '0',
                'valor_adicional' => $request->valor_adicional ? __convert_value_bd($request->valor_adicional) : 0
            ]);

            if ($request->marketplace) {
                $request->merge([
                    'hash_delivery' => Str::random(50),
                ]);
            }

            Servico::create($request->all());
            session()->flash('flash_success', 'Serviço cadastrado com sucesso!');
        } catch (\Exception $e) {
            session()->flash('flash_error', 'Não foi possível concluir o cadastro' . $e->getMessage());
        }
        if(isset($request->redirect_marketplace)){
            return redirect()->route('servicos-marketplace.index');
        }
        return redirect()->route('servicos.index');
    }

    public function edit($id)
    {
        $item = Servico::findOrFail($id);
        __validaObjetoEmpresa($item);
        $categorias = CategoriaServico::where('empresa_id', request()->empresa_id)->get();
        return view('servicos.edit', compact('item', 'categorias'));
    }

    public function update(Request $request, $id)
    {
        if($request->padrao_reserva_nfse == 1){
            Servico::where('empresa_id', $request->empresa_id)
            ->update(['padrao_reserva_nfse' => 0]);
        }
        $item = Servico::findOrFail($id);
        __validaObjetoEmpresa($item);
        try {
            $file_name = $item->imagem;

            if ($request->hasFile('image')) {
                $this->util->unlinkImage($item, '/servicos');
                $file_name = $this->util->uploadImage($request, '/servicos');
            }

            $request->merge([
                'valor' => __convert_value_bd($request->valor),
                'imagem' => $file_name,
                'comissao' => $request->comissao ? __convert_value_bd($request->comissao) : 0,
                'tempo_tolerancia' => $request->tempo_tolerancia ?? 0,
                'tempo_adicional' => $request->tempo_adicional ?? '0',
                'valor_adicional' => $request->valor_adicional ? __convert_value_bd($request->valor_adicional) : 0
            ]);

            if ($request->marketplace) {
                $request->merge([
                    'hash_delivery' => Str::random(50),
                ]);
            }
            
            $item->fill($request->all())->save();
            session()->flash('flash_success', 'Serviço alterado com sucesso!');
        } catch (\Exception $e) {
            session()->flash('flash_error', 'Não foi possível alterar o cadastro' . $e->getMessage());
        }
        return redirect()->route('servicos.index');
    }

    public function destroy($id)
    {
        $item = Servico::findOrFail($id);
        __validaObjetoEmpresa($item);
        try {
            $item->delete();
            session()->flash('flash_success', 'Deletado com sucesso!');
        } catch (\Exception $e) {
            session()->flash('flash_error', 'Não foi possível deletar' . $e->getMessage());
        }
        return redirect()->route('servicos.index');
    }

    public function destroySelecet(Request $request)
    {
        $removidos = 0;
        for($i=0; $i<sizeof($request->item_delete); $i++){
            $item = Servico::findOrFail($request->item_delete[$i]);
            try {
                $item->delete();
                $removidos++;
            } catch (\Exception $e) {
                session()->flash("flash_error", 'Algo deu errado: '. $e->getMessage());
                return redirect()->back();
            }
        }

        session()->flash("flash_success", "Total de itens removidos: $removidos!");
        return redirect()->back();
    }
}
