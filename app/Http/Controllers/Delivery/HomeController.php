<?php

namespace App\Http\Controllers\Delivery;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MarketPlaceConfig;
use App\Models\CategoriaProduto;
use App\Models\CategoriaServico;
use App\Models\Produto;
use App\Models\Servico;
use App\Models\DestaqueMarketPlace;
use App\Models\CarrinhoDelivery;
use App\Models\FuncionamentoDelivery;
use App\Models\TamanhoPizza;
use Illuminate\Support\Str;

class HomeController extends Controller
{

    public function __construct(){
      
    }

    private function _validaHash($config){
        $categorias = CategoriaProduto::where('delivery', 1)
        ->where('empresa_id', $config->empresa_id)
        ->orderBy('nome', 'asc')
        ->where('hash_delivery', null)
        ->get();

        foreach($categorias as $c){
            $c->hash_delivery = Str::random(50);
            $c->save();
        }

        $categorias = CategoriaServico::where('marketplace', 1)
        ->where('empresa_id', $config->empresa_id)
        ->where('hash_delivery', null)
        ->get();

        foreach($categorias as $c){
            $c->hash_delivery = Str::random(50);
            $c->save();
        }

        $produtos = Produto::where('empresa_id', $config->empresa_id)
        ->where('status', 1)
        ->where('delivery', 1)
        ->where('hash_delivery', null)
        ->get();

        foreach($produtos as $p){
            $p->hash_delivery = Str::random(50);
            $p->save();
        }

        $servicos = Servico::where('empresa_id', $config->empresa_id)
        ->where('status', 1)
        ->where('marketplace', 1)
        ->where('hash_delivery', null)
        ->get();

        foreach($servicos as $s){
            $s->hash_delivery = Str::random(50);
            $s->save();
        }

    }

    private function getCategorias($empresa_id){
        $categorias = CategoriaProduto::where('delivery', 1)
        ->orderBy('nome', 'asc')
        ->where('empresa_id', $empresa_id)->get();

        $categoriasServico = CategoriaServico::where('marketplace', 1)
        ->where('empresa_id', $empresa_id)->get();

        $categorias = $categorias->concat($categoriasServico);
        return $categorias;
    }

    public function index(Request $request){
        $config = MarketPlaceConfig::findOrfail($request->loja_id);

        $this->_validaHash($config);

        $categorias = $this->getCategorias($config->empresa_id);

        $produtosEmDestaque = $this->produtosEmDestaque($config->empresa_id);
        $servicosEmDestaque = $this->servicosEmDestaque($config->empresa_id);
        $categoriasEmDestaque = $this->getCategoriasEmDestaque($produtosEmDestaque);
        $categoriasEmDestaqueServicos = $this->getCategoriasEmDestaqueServicos($servicosEmDestaque);
        $carrinho = $this->_getCarrinho();

        $banners = DestaqueMarketPlace::where('status', 1)
        ->where('empresa_id', $config->empresa_id)->get();

        $funcionamento = $this->getFuncionamento($config);

        return view('food.index', compact('config', 'categorias', 'produtosEmDestaque', 'carrinho', 
            'banners', 'categoriasEmDestaque', 'funcionamento', 'servicosEmDestaque', 'categoriasEmDestaqueServicos'));
    }

    public function pesquisa(Request $request){

        $pesquisa = $request->pesquisa;

        $config = MarketPlaceConfig::findOrfail($request->loja_id);
        $categorias = $this->getCategorias($config->empresa_id);

        $produtosEmDestaque = $this->produtosEmDestaque($config->empresa_id);
        $categoriasEmDestaque = $this->getCategoriasEmDestaque($produtosEmDestaque);
        $carrinho = $this->_getCarrinho();

        $produtos = Produto::where('produtos.empresa_id', $config->empresa_id)
        ->select('produtos.*')
        ->where('produtos.status', 1)
        ->where('produtos.delivery', 1)
        ->when(!empty($pesquisa), function ($query) use ($pesquisa) {
            return $query->where('produtos.nome', 'like', "%$pesquisa%");
        })
        ->get();

        return view('food.pesquisa', compact('config', 'categorias', 'produtos', 'carrinho', 'categoriasEmDestaque', 'pesquisa'));
    }

    private function getCategoriasEmDestaque($produtos){
        $categorias = [];
        foreach($produtos as $p){
            if($p->categoria_id){
                $in_array = in_array($p->categoria_id, array_column($categorias, 'id'));
                if(!$in_array){
                    array_push($categorias, $p->categoria);
                }
            }
        }
        return $categorias;
    }

    private function getCategoriasEmDestaqueServicos($servicos){
        $categorias = [];
        foreach($servicos as $p){
            if($p->categoria_id){
                $in_array = in_array($p->categoria_id, array_column($categorias, 'id'));
                if(!$in_array){
                    array_push($categorias, $p->categoria);
                }
            }
        }
        return $categorias;
    }

    private function produtosEmDestaque($empresa_id){
        return Produto::where('empresa_id', $empresa_id)
        ->where('destaque_delivery', 1)
        ->where('status', 1)
        ->where('delivery', 1)->get();
    }

    private function servicosEmDestaque($empresa_id){
        return Servico::where('empresa_id', $empresa_id)
        ->where('destaque_marketplace', 1)
        ->where('status', 1)
        ->where('marketplace', 1)->get();
    }

    private function _getCarrinho(){
        $data = [];
        if(isset($_SESSION["session_cart_delivery"])){
            $data = CarrinhoDelivery::where('session_cart_delivery', $_SESSION["session_cart_delivery"])
            ->first();
        }
        return $data;
    }

    public function produtosDaCategoria(Request $request, $hash){
        $config = MarketPlaceConfig::findOrfail($request->loja_id);
        $categorias = $this->getCategorias($config->empresa_id);

        $categoria = CategoriaProduto::where('hash_delivery', $hash)
        ->first();

        if($categoria == null){
            abort(404);
        }
        $produtos = Produto::where('empresa_id', $config->empresa_id)
        ->where('categoria_id', $categoria->id)
        ->where('status', 1)
        ->where('delivery', 1)->get();

        $carrinho = $this->_getCarrinho();

        $tamanho = TamanhoPizza::where('empresa_id', $config->empresa_id)
        ->orderBy('maximo_sabores', 'desc')->first();
        $maximo_sabores_pizza = 0;
        if($tamanho != null){
            $maximo_sabores_pizza = $tamanho->maximo_sabores;
        }

        $tamanhosPizza = TamanhoPizza::where('empresa_id', $config->empresa_id)
        ->where('status', 1)
        ->with('produtos')
        ->get();

        return view('food.produtos_categoria', compact(
            'config', 'categorias', 'categoria', 'produtos', 'carrinho', 'maximo_sabores_pizza', 'tamanhosPizza'));
    }

    public function servicosDaCategoria(Request $request, $hash){
        $config = MarketPlaceConfig::findOrfail($request->loja_id);
        $categorias = $this->getCategorias($config->empresa_id);

        $categoria = CategoriaServico::where('hash_delivery', $hash)->first();
        
        if($categoria == null){
            abort(404);
        }

        $servicos = Servico::where('empresa_id', $config->empresa_id)
        ->where('categoria_id', $categoria->id)
        ->where('status', 1)
        ->where('marketplace', 1)->get();

        $carrinho = $this->_getCarrinho();

        return view('food.servicos_categoria', compact('config', 'categorias', 'categoria', 'servicos', 'carrinho'));
    }

    private function getFuncionamento($config){
        $dia = date('w');
        $hora = date('H:i');
        $dia = FuncionamentoDelivery::getDia($dia);

        $funcionamento = FuncionamentoDelivery::where('dia', $dia)
        ->where('empresa_id', $config->empresa_id)->first();

        if($funcionamento != null){

            $atual = strtotime(date('Y-m-d H:i'));
            $dataHoje = date('Y-m-d');
            $inicio = strtotime($dataHoje . " " . $funcionamento->inicio);
            $fim = strtotime($dataHoje . " " . $funcionamento->fim);
            if($atual > $inicio && $atual < $fim){
                $funcionamento->aberto = true;
            }else{
                $funcionamento->aberto = false;
            }
            return $funcionamento;
        }
        return null;
    }

    public function produtoDetalhe(Request $request, $hash){
        $config = MarketPlaceConfig::findOrfail($request->loja_id);
        $produto = Produto::where('empresa_id', $config->empresa_id)
        ->where('hash_delivery', $hash)
        ->where('status', 1)->first();

        if($produto == null){
            session()->flash("flash_error", "Produto não encontrado!");
            return redirect()->back();
        }

        $categorias = CategoriaProduto::where('delivery', 1)
        ->where('empresa_id', $config->empresa_id)
        ->orderBy('nome', 'asc')->get();
        $carrinho = $this->_getCarrinho();

        $funcionamento = $this->getFuncionamento($config);

        return view('food.produto_detalhe', compact('config', 'categorias', 'produto', 'carrinho', 'funcionamento'));
    }

    public function servicoDetalhe(Request $request, $hash){
        $config = MarketPlaceConfig::findOrfail($request->loja_id);
        $servico = Servico::where('empresa_id', $config->empresa_id)
        ->where('hash_delivery', $hash)
        ->where('status', 1)->first();

        if($servico == null){
            session()->flash("flash_error", "Serviço não encontrado!");
            return redirect()->back();
        }

        $categorias = $this->getCategorias($config->empresa_id);
        $carrinho = $this->_getCarrinho();

        $funcionamento = $this->getFuncionamento($config);

        return view('food.servico_detalhe', compact('config', 'categorias', 'servico', 'carrinho', 'funcionamento'));
    }

    public function pizzaDetalhe(Request $request){
        $config = MarketPlaceConfig::findOrfail($request->loja_id);
        $tamanho = TamanhoPizza::findOrFail($request->tamanho_id);
        $valorPizza = $request->valor_pizza;
        $pizzas = [];
        for($i=0; $i<sizeof($request->pizza); $i++){
            $p = Produto::where('hash_delivery', $request->pizza[$i])->first();
            if($p == null){
                session()->flash("flash_error", "Pizza não encontrada!");
                return redirect()->back();
            }
            array_push($pizzas, $p);
        }

        $categorias = CategoriaProduto::where('delivery', 1)
        ->orderBy('nome', 'asc')
        ->where('empresa_id', $config->empresa_id)->get();
        $carrinho = $this->_getCarrinho();

        $funcionamento = $this->getFuncionamento($config);

        return view('food.pizza_detalhe', compact('config', 'categorias', 'pizzas', 'carrinho', 
            'funcionamento', 'tamanho', 'valorPizza'));
    }

}
