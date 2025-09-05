
@section('css')
<style type="text/css">
    .image-variation{
        width: 180px;
        height: 100px;
        margin-top: 10px;
        border-radius: 10px;
    }
</style>
@endsection
<div id="basicwizard">
    <ul class="nav nav-pills nav-justified form-wizard-header mb-4 m-2">
        <li class="nav-item">
            <a href="#tab-identificacao" data-bs-toggle="tab" data-toggle="tab"  class="nav-link rounded-0 py-1"> 
                <i class="ri-product-hunt-fill fw-normal fs-18 align-middle me-1"></i>
                <span class="d-none d-sm-inline">Identificação</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="#tab-fiscal" data-bs-toggle="tab" data-toggle="tab" class="nav-link rounded-0 py-1">
                <i class="ri-file-code-line fs-18 align-middle me-1"></i>
                <span class="d-none d-sm-inline tab-fiscal">Fiscal</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="#tab-outros" data-bs-toggle="tab" data-toggle="tab" class="nav-link rounded-0 py-1">
                <i class="ri-stack-line fs-18 align-middle me-1"></i>
                <span class="d-none d-sm-inline">Outros</span>
            </a>
        </li>
    </ul>

    <div class="tab-content b-0 mb-0">
        <div class="tab-pane" id="tab-identificacao">
            <div class="row g-2">

                <div class="col-md-6">
                    {!!Form::text('nome', 'Nome')
                    ->required()
                    !!}
                </div>
                <div class="col-md-2 col-produto">
                    {!!Form::tel('valor_compra', 'Valor de Compra')
                    ->required()
                    ->value(isset($item) ? __moeda($item->valor_compra) : '')
                    ->attrs(['class' => 'moeda'])
                    !!}
                </div>

                <div class="col-md-2 col-produto">
                    {!!Form::tel('percentual_lucro', '% lucro')
                    ->required()
                    ->value(isset($item) ? $item->percentual_lucro : ($configGeral ? $configGeral->percentual_lucro_produto : ''))
                    ->attrs(['class' => 'percentual'])
                    !!}
                </div>

                <div class="col-md-2 col-produto">
                    {!!Form::tel('valor_unitario', 'Valor de venda')
                    ->required()
                    ->value(isset($item) ? __moeda($item->valor_unitario) : '')
                    ->attrs(['class' => 'moeda'])
                    !!}
                </div>

                <div class="col-md-2">
                    <label class="form-label">Código de barras</label>
                    <div class="input-group input-group-merge" style="margin-top: -8px">
                        <input type="text" name="codigo_barras" value="{{ isset($item) ? $item->codigo_barras : old('codigo_barras') }}" id="codigo_barras" class="form-control">
                        <div class="input-group-text">
                            <span class="ri-barcode-box-line" onclick="gerarCode(1)"></span>
                        </div>
                    </div>
                </div>

                <div class="col-md-2">
                    <label class="form-label">2º Código de barras</label>
                    <div class="input-group input-group-merge" style="margin-top: -8px">
                        <input type="text" name="codigo_barras2" value="{{ isset($item) ? $item->codigo_barras2 : old('codigo_barras2') }}" id="codigo_barras2" class="form-control">
                        <div class="input-group-text">
                            <span class="ri-barcode-box-line" onclick="gerarCode(2)"></span>
                        </div>
                    </div>
                </div>

                <div class="col-md-2">
                    <label class="form-label">3º Código de barras</label>
                    <div class="input-group input-group-merge" style="margin-top: -8px">
                        <input type="text" name="codigo_barras3" value="{{ isset($item) ? $item->codigo_barras3 : old('codigo_barras3') }}" id="codigo_barras3" class="form-control">
                        <div class="input-group-text">
                            <span class="ri-barcode-box-line" onclick="gerarCode(3)"></span>
                        </div>
                    </div>
                </div>

                <div class="col-md-2">
                    {!!Form::tel('referencia', 'Referência')
                    !!}
                </div>

                @if(!isset($item))
                <div class="col-md-2">
                    {!!Form::tel('estoque_inicial', 'Estoque inicial')
                    ->attrs(['class' => 'quantidade'])
                    !!}
                </div>
                @endif

                <div class="col-md-3">
                    <label>Categoria</label>
                    <div class="input-group flex-nowrap">
                        <select class="select2" name="categoria_id" id="categoria_id">
                            <option value="">Selecione</option>
                            @foreach($categorias as $c)
                            <option @isset($item) @if($item->categoria_id == $c->id) selected @endif @endif value="{{ $c->id}}">{{ $c->nome }}</option>
                            @endforeach
                        </select>
                        <button class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#modal_categoria_produto" type="button">
                            <i class="ri-add-circle-fill"></i>
                        </button>
                    </div>
                </div>

                <div class="col-md-3">
                    {!!Form::select('sub_categoria_id', 'Subcategoria')
                    ->attrs(['class' => 'form-select'])
                    ->options(isset($item) && $item->subcategoria ? [$item->subcategoria->id => $item->subcategoria->nome] : [])
                    !!}
                </div>

                <div class="col-md-3">
                    <label>Marca</label>
                    <div class="input-group flex-nowrap">
                        <select class="select2" name="marca_id" id="marca_id">
                            <option value="">Selecione</option>
                            @foreach($marcas as $m)
                            <option @isset($item) @if($item->marca_id == $m->id) selected @endif @endif value="{{ $m->id}}">{{ $m->nome }}</option>
                            @endforeach
                        </select>
                        <button class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#modal_marca" type="button">
                            <i class="ri-add-circle-fill"></i>
                        </button>
                    </div>
                </div>

                <div class="col-md-2">
                    {!!Form::select('gerenciar_estoque', 'Gerenciar estoque', ['0' => 'Não', '1' => 'Sim'])
                    ->attrs(['class' => 'form-select'])
                    ->value(isset($item) ? $item->gerenciar_estoque : ($configGeral ? $configGeral->gerenciar_estoque : ''))
                    !!}
                </div>

                <div class="col-md-2">
                    {!!Form::text('estoque_minimo', 'Estoque mínimo')
                    ->attrs(['data-mask' => '00000.00', 'data-mask-reverse' => 'true'])
                    !!}
                </div>

                <div class="col-md-2">
                    {!!Form::text('alerta_validade', 'Alerta de validade (dias)')
                    ->attrs(['data-mask' => '000'])
                    !!}
                </div>

                <div class="col-md-2">
                    {!!Form::tel('referencia_balanca', 'Referência balança')
                    ->attrs()
                    !!}
                </div>

                <div class="col-md-2">
                    {!!Form::select('unidade', 'Unidade', App\Models\Produto::unidadesMedida())
                    ->required()
                    ->attrs(['class' => 'form-select'])
                    ->value(isset($item) ? $item->unidade_compra : 'UN')
                    !!}
                </div>

                <div class="col-md-2">
                    {!!Form::select('status', 'Ativo', ['1' => 'Sim', '0' => 'Não'])
                    ->attrs(['class' => 'form-select'])
                    !!}
                </div>

                <div class="col-md-2">
                    {!!Form::select('composto', 'Composto', ['0' => 'Não', '1' => 'Sim'])->attrs(['class' => 'form-select'])
                    !!}
                </div>

                <div class="col-md-2">
                    {!!Form::select('variavel', 'Com variações', ['0' => 'Não', '1' => 'Sim'])->attrs(['class' => 'form-select'])
                    ->value((isset($item) && $item->variacao_modelo_id != null) ? 1 : 0)
                    !!}
                </div>

                <div class="col-md-2">
                    {!!Form::select('combo', 'Tipo combo', ['0' => 'Não', '1' => 'Sim'])->attrs(['class' => 'form-select'])
                    !!}
                </div>

                @if(__countLocalAtivo() > 1)
                <div class="col-md-4">
                    <label for="">Disponibilidade</label>

                    <select required class="select2 form-control select2-multiple" data-toggle="select2" name="locais[]" multiple="multiple">
                        @foreach(__getLocaisAtivoUsuario() as $local)
                        <option @if(in_array($local->id, (isset($item) ? $item->locais->pluck('localizacao_id')->toArray() : []))) selected @endif value="{{ $local->id }}">{{ $local->descricao }}</option>
                        @endforeach
                    </select>
                </div>
                @else

                <input type="hidden" value="{{ __getLocalAtivo() ? __getLocalAtivo()->id : '' }}" name="local_id">
                @endif

                <div class="col-md-2">
                    {!!Form::tel('valor_atacado', 'Valor de atacado')
                    ->value(isset($item) ? __moeda($item->valor_atacado) : '')
                    ->attrs(['class' => 'moeda'])
                    !!}
                </div>

                <div class="col-md-2">
                    {!!Form::tel('quantidade_atacado', 'Quantidade para atacado')
                    ->value(isset($item) ? $item->quantidade_atacado : '')
                    ->attrs(['data-mask' => '000'])
                    !!}
                </div>

                <div class="col-12 div-variavel">
                    <div class="table-responsive">
                        <table class="table table-dynamic">
                            <thead class="table-dark">
                                <tr>
                                    <th>Variação</th>
                                    <th>Valores da variação</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td width="200px">
                                        {!!Form::select('variacao_modelo_id', '', ['' => 'Selecione'] + $variacoes->pluck('descricao', 'id')->all())
                                        ->attrs(['class' => 'form-select'])
                                        ->value(isset($item) ? $item->variacao_modelo_id : null)
                                        !!}
                                    </td>
                                    <td>
                                        <div class="row">
                                            <table class="table table-dynamic table-variacao">
                                                <thead class="table-success">
                                                    <tr>
                                                        <th>Descrição</th>
                                                        <th>Valor</th>
                                                        <th>Código de barras</th>
                                                        <th>Referência</th>
                                                        <th>Imagem</th>
                                                        <th>

                                                        </th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @isset($item)
                                                    @foreach($item->variacoes as $v)
                                                    <tr class="dynamic-form">
                                                        <input type="hidden" name="variacao_id[]]]" value="{{ $v->id }}">
                                                        <td>
                                                            <input type="text" class="form-control" name="descricao_variacao[]" value="{{ $v->descricao }}" required readonly>
                                                        </td>
                                                        <td>
                                                            <input type="tel" class="form-control moeda" name="valor_venda_variacao[]" value="{{ __moeda($v->valor) }}" required>
                                                        </td>

                                                        <td>
                                                            <input type="tel" class="form-control ignore" name="codigo_barras_variacao[]" value="{{ $v->codigo_barras }}">
                                                        </td>
                                                        <td>
                                                            <input type="text" class="form-control ignore" name="referencia_variacao[]" value="{{ $v->referencia }}">
                                                        </td>
                                                        <td>
                                                            <input class="ignore" accept="image/*" type="file" class="form-control" name="imagem_variacao[]" value="">
                                                            <img src="{{ $v->img }}" class="image-variation"><br>
                                                            <span>imagem atual</span>
                                                        </td>
                                                        <td>
                                                            <button type="button" class="btn btn-sm btn-danger btn-remove-tr-variacao">
                                                                <i class="ri-subtract-line"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                    @endif
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="row col-12 col-lg-3 mt-3">
                                            <button type="button" class="btn btn-dark btn-add-tr-variacao">
                                                <i class="ri-add-fill"></i>
                                                Adicionar linha
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="col-12 div-combo">

                    <div class="row m-2">
                        <div class="col-md-3"></div>
                        <div class="col-md-6 col-12">
                            <select id="inp-produto_combo_id"></select>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-dynamic table-combo">
                            <thead class="table-dark">
                                <tr>
                                    <th>Produto</th>
                                    <th>Quantidade</th>
                                    <th>Valor de compra</th>
                                    <th>Subtotal</th>
                                    <th>Ação</th>
                                </tr>
                            </thead>
                            <tbody>
                                @isset($item)
                                @foreach($item->itensDoCombo as $c)
                                <tr class="dynamic-form">
                                    <input type="hidden" name="produto_combo_id[]" value="{{ $c->item_id }}">
                                    <td style="width: 420px">
                                        <span>{{ $c->produtoDoCombo->nome }}</span>
                                    </td>
                                    <td style="width: 120px">
                                        <input type="tel" class="form-control qtd-combo quantidade" name="quantidade_combo[]" 
                                        value="{{ $c->quantidade }}">
                                    </td>
                                    <td>
                                        <input type="tel" class="form-control moeda valor-compra-combo" name="valor_compra_combo[]" value="{{ __moeda($c->valor_compra) }}">
                                    </td>
                                    <td>
                                        <input type="tel" class="form-control moeda subtotal-combo" name="subtotal_combo[]" value="{{ __moeda($c->sub_total) }}">
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-danger btn-remove-tr-combo">
                                            <i class="ri-subtract-line"></i>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                    <div class="row">
                        <div class="col-md-2">
                            {!!Form::tel('margem_combo', 'Margem %')
                            ->value(isset($item) ? $item->margem_combo : ($configGeral ? $configGeral->margem_combo : ''))
                            ->attrs(['class' => 'percentual'])
                            !!}
                        </div>

                        <div class="col-md-2">
                            {!!Form::tel('valor_combo', 'Valor do  combo')
                            ->value(isset($item) ? __moeda($item->valor_unitario) : __moeda(0))
                            ->attrs(['class' => 'moeda'])
                            !!}
                        </div>
                    </div>
                </div>

                <div class="col-12"></div>

                <div class="card col-md-3 mt-3 form-input">
                    <div class="preview">
                        <button type="button" id="btn-remove-imagem" class="btn btn-link-danger btn-sm btn-danger">x</button>
                        @isset($item)
                        <img id="file-ip-1-preview" src="{{ $item->img }}">
                        @else
                        <img id="file-ip-1-preview" src="/imgs/no-image.png">
                        @endif
                    </div>
                    <label for="file-ip-1">Imagem</label>
                    @isset($item)
                    <a class="btn btn-danger btn-sm w-50 mt-2 mb-1" href="{{ route('produtos.remove-image', [$item->id])}}">
                        <i class="ri-close-line"></i>
                        Remover imagem
                    </a>
                    @endif
                    <input type="file" id="file-ip-1" name="image" accept="image/*" onchange="showPreview(event);">
                </div>
            </div>
        </div>
    </div>
    <!-- fim identificação -->

    <div class="tab-content b-0 mb-0">
        <div class="tab-pane" id="tab-fiscal">
            <div class="row g-2">

                <div class="col-md-2">
                    {!!Form::select('padrao_id', 'Padrão de tributação', ['' => 'Selecione'] + $padroes->pluck('descricao', 'id')->all())
                    ->attrs(['class' => 'form-select'])
                    ->value(isset($item) ? $item->padrao_id : ($padraoTributacao != null ? $padraoTributacao->id : ''))
                    !!}
                </div>

                <div class="col-md-4">
                    {!!Form::select('ncm', 'NCM')
                    ->required(__isPlanoFiscal())
                    ->options(isset($item) ? ($item->_ncm ? [$item->ncm => $item->_ncm->descricao] : []) : [])
                    !!}
                </div>
                <div class="col-md-2">
                    {!!Form::tel('cest', 'CEST')
                    ->attrs(['class' => 'cest'])
                    !!}
                </div>
                <div class="col-md-2">
                    {!!Form::tel('perc_icms', '% ICMS')
                    ->attrs(['class' => 'percentual'])
                    ->required(__isPlanoFiscal())
                    !!}
                </div>

                <div class="col-md-2">
                    {!!Form::tel('perc_pis', '% PIS')
                    ->required(__isPlanoFiscal())
                    ->attrs(['class' => 'percentual'])
                    !!}
                </div>
                <div class="col-md-2">
                    {!!Form::tel('perc_cofins', '% COFINS')
                    ->required(__isPlanoFiscal())
                    ->attrs(['class' => 'percentual'])
                    !!}
                </div>
                <div class="col-md-2">
                    {!!Form::tel('perc_ipi', '% IPI')
                    ->required(__isPlanoFiscal())
                    ->attrs(['class' => 'percentual'])
                    !!}
                </div>
                <div class="col-md-2">
                    {!!Form::tel('perc_red_bc', '% Red BC')
                    ->attrs(['class' => 'percentual'])
                    !!}
                </div>

                <div class="col-md-2">
                    {!!Form::select('origem', 'Origem', App\Models\Produto::origens())
                    ->required(__isPlanoFiscal())
                    ->attrs(['class' => 'form-select'])
                    !!}
                </div>
                <div class="col-md-6">
                    {!!Form::select('cst_csosn', 'CST/CSOSN', $listaCTSCSOSN)
                    ->required(__isPlanoFiscal())
                    ->attrs(['class' => 'form-select'])
                    !!}
                </div>
                <div class="col-md-4">
                    {!!Form::select('cst_pis', 'CST PIS', App\Models\Produto::listaCST_PIS_COFINS())
                    ->required(__isPlanoFiscal())
                    ->attrs(['class' => 'form-select'])
                    !!}
                </div>
                <div class="col-md-4">
                    {!!Form::select('cst_cofins', 'CST COFINS', App\Models\Produto::listaCST_PIS_COFINS())
                    ->required(__isPlanoFiscal())
                    ->attrs(['class' => 'form-select'])
                    !!}
                </div>
                <div class="col-md-4">
                    {!!Form::select('cst_ipi', 'CST IPI', App\Models\Produto::listaCST_IPI())
                    ->required(__isPlanoFiscal())
                    ->attrs(['class' => 'form-select'])
                    !!}
                </div>
                <div class="col-md-6">
                    {!!Form::select('cEnq', 'Código de enquandramento de IPI', App\Models\Produto::listaCenqIPI())
                    ->required(__isPlanoFiscal())
                    ->attrs(['class' => 'form-select select2'])
                    !!}
                </div>
                <div class="col-md-2">
                    {!!Form::tel('cfop_estadual', 'CFOP Estadual')
                    ->required(__isPlanoFiscal())
                    ->attrs(['class' => 'cfop'])
                    !!}
                </div>
                <div class="col-md-2">
                    {!!Form::tel('cfop_outro_estado', 'CFOP Inter Estadual')
                    ->required(__isPlanoFiscal())
                    ->attrs(['class' => 'cfop'])
                    !!}
                </div>

                <div class="col-md-2">
                    {!!Form::tel('cfop_entrada_estadual', 'CFOP Entrada Estadual')
                    ->required(__isPlanoFiscal())
                    ->attrs(['class' => 'cfop'])
                    !!}
                </div>
                <div class="col-md-2">
                    {!!Form::tel('cfop_entrada_outro_estado', 'CFOP Entrada Inter Estadual')
                    ->required(__isPlanoFiscal())
                    ->attrs(['class' => 'cfop'])
                    !!}
                </div>
                <div class="col-md-2">
                    {!!Form::text('codigo_beneficio_fiscal', 'Código benefício')
                    !!}
                </div>

                <div class="col-md-3">
                    {!!Form::select('modBCST', 'Modalidade BC-ST', App\Models\Produto::modalidadesBCST())
                    ->attrs(['class' => 'form-select'])
                    !!}
                </div>

                <div class="col-md-2">
                    {!!Form::tel('pICMSST', '% ICMS ST')
                    ->attrs(['class' => 'percentual'])
                    !!}
                </div>

                <div class="col-md-2">
                    {!!Form::tel('pMVAST', '% MVA ST')
                    ->attrs(['class' => 'percentual'])
                    !!}
                </div>

                <div class="col-md-2">
                    {!!Form::tel('redBCST', '% Red BC ST')
                    ->attrs(['class' => 'percentual'])
                    !!}
                </div>
            </div>
        </div>
    </div>
    
    <div class="tab-content b-0 mb-0">
        <div class="tab-pane" id="tab-outros">
            <div class="row g-2">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>
                                    <div class="form-check form-switch">
                                        <input type="checkbox" class="form-check-input" id="inp-petroleo" @isset($item) @if($item->codigo_anp != '') checked @endif @endif> <strong>Derivado do petróleo</strong>
                                    </div>
                                </h4>
                            </div>
                            <div class="card-body div-petroleo d-none m-card" style="margin-top: -40px">

                                <div class="row">

                                    <div class="col-md-4">
                                        {!!Form::select('codigo_anp', 'ANP', ['' => 'Selecione'] + App\Models\Produto::listaAnp())
                                        ->attrs(['class' => 'select2'])
                                        !!}
                                    </div>

                                    <div class="col-md-1">
                                        {!!Form::tel('perc_glp', '%GLP')
                                        ->attrs(['class' => 'percentual'])
                                        !!}
                                    </div>
                                    <div class="col-md-1">
                                        {!!Form::tel('perc_gnn', '%GNN')
                                        ->attrs(['class' => 'percentual'])
                                        !!}
                                    </div>
                                    <div class="col-md-1">
                                        {!!Form::tel('perc_gni', '%GNI')
                                        ->attrs(['class' => 'percentual'])
                                        !!}
                                    </div>

                                    <div class="col-md-2">
                                        {!!Form::tel('valor_partida', 'Valor de partida')
                                        ->attrs(['class' => 'moeda'])
                                        !!}
                                    </div>

                                    <div class="col-md-2">
                                        {!!Form::text('unidade_tributavel', 'Un. tributável')
                                        !!}
                                    </div>

                                    <div class="col-md-2">
                                        {!!Form::tel('quantidade_tributavel', 'Qtd. tributável')
                                        !!}
                                    </div>

                                    <div class="col-md-3">
                                        {!!Form::tel('adRemICMSRet', 'Alíquota ad rem do imposto retido')
                                        ->attrs(['data-mask' => '00,0000'])
                                        !!}
                                    </div>
                                    <div class="col-md-3">
                                        {!!Form::tel('pBio', 'Indice de mistura do Biodiesel')
                                        ->attrs(['class' => 'percentual'])
                                        !!}
                                    </div>
                                    <div class="col-md-2">
                                        {!!Form::tel('pOrig', '% de origem')
                                        ->attrs(['class' => 'percentual'])
                                        !!}
                                    </div>

                                    <div class="col-md-2">
                                        {!!Form::select('indImport', 'Indicador de importação',
                                        [ 0 => 'Não', 1 => 'Sim']
                                        )
                                        ->attrs(['class' => 'form-select'])
                                        !!}
                                    </div>

                                    <div class="col-md-3">
                                        {!!Form::select('cUFOrig', 'UF de origem do produtor ou do importador', ['' => 'Selecione'] + App\Models\Cidade::getEstadosCodigo())
                                        ->attrs(['class' => 'select2'])
                                        !!}
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                @if(__isActivePlan(Auth::user()->empresa, 'Cardapio'))
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>
                                    @isset($cardapio)
                                    @if($cardapio == 1)
                                    <input type="hidden" name="redirect_cardapio" value="1">
                                    @endif
                                    @endif
                                    <div class="form-check form-switch form-checkbox-danger">
                                        <input type="checkbox" name="cardapio" class="form-check-input" id="inp-cardapio" @isset($item) @if($item->cardapio) checked @endif @endif @isset($cardapio) @if($cardapio == 1) checked @endif @endif ><strong>Cardápio</strong>
                                    </div>
                                </h4>
                            </div>
                            <div class="card-body div-cardapio d-none m-card" style="margin-top: -40px">

                                <div class="row">

                                    <div class="col-md-2">
                                        {!!Form::tel('valor_cardapio', 'Valor de Cardápio')
                                        ->value((isset($item) && $item->valor_cardapio > 0) ? __moeda($item->valor_cardapio) : '')
                                        ->attrs(['class' => 'moeda'])
                                        !!}
                                    </div>

                                    <div class="col-md-2">
                                        {!!Form::tel('tempo_preparo', 'Tempo de preparo (minutos)')
                                        ->attrs(['data-mask' => '000'])
                                        !!}
                                    </div>

                                    <div class="col-md-2">
                                        {!!Form::select('tipo_carne', 'Escolher ponto da carne', ['0' => 'Não', '1' => 'Sim'])
                                        ->attrs(['class' => 'form-select'])
                                        !!}
                                    </div>

                                    @if(__isInternacionalizar(Auth::user()->empresa))
                                    <div class="col-md-3">
                                        {!!Form::text('nome_en', 'Nome (em inglês)')
                                        !!}
                                    </div>
                                    <div class="col-md-3">
                                        {!!Form::text('nome_es', 'Nome (em espanhol)')
                                        !!}
                                    </div>
                                    @endif

                                    <div class="col-md-12">
                                        {!!Form::tel('descricao', 'Descrição')
                                        ->value(isset($item) ? $item->descricao_pt : '')
                                        ->attrs(['class' => ''])
                                        !!}
                                    </div>

                                    @if(__isInternacionalizar(Auth::user()->empresa))
                                    <div class="col-md-12">
                                        {!!Form::tel('descricao_en', 'Descrição (em inglês)')
                                        ->value(isset($item) ? $item->descricao_en : '')
                                        ->attrs(['class' => ''])
                                        !!}
                                    </div>

                                    <div class="col-md-12">
                                        {!!Form::tel('descricao_es', 'Descrição (em espanhol)')
                                        ->value(isset($item) ? $item->descricao_es : '')
                                        ->attrs(['class' => ''])
                                        !!}
                                    </div>

                                    @endif

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                @if(__isActivePlan(Auth::user()->empresa, 'Delivery'))
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>
                                    @isset($delivery)
                                    @if($delivery == 1)
                                    <input type="hidden" name="redirect_delivery" value="1">
                                    @endif
                                    @endif
                                    <div class="form-check form-switch form-checkbox-success">
                                        <input type="checkbox" name="delivery" class="form-check-input" id="inp-delivery" @isset($item) @if($item->delivery) checked @endif @endif @isset($delivery) @if($delivery == 1) checked @endif @endif ><strong>Delivery/MarketPlace</strong>
                                    </div>
                                </h4>
                            </div>

                            <div class="card-body div-delivery d-none m-card" style="margin-top: -40px">

                                <div class="row">

                                    <div class="col-md-2">
                                        {!!Form::tel('valor_delivery', 'Valor de Deivery')
                                        ->value((isset($item) && $item->valor_delivery > 0) ? __moeda($item->valor_delivery) : '')
                                        ->attrs(['class' => 'moeda'])
                                        !!}
                                    </div>

                                    <div class="col-md-2">
                                        {!!Form::select('destaque_delivery', 'Destaque', ['0' => 'Não', '1' => 'Sim'])
                                        ->attrs(['class' => 'form-select'])
                                        !!}
                                    </div>

                                    <div class="col-12">
                                        {!!Form::textarea('texto_delivery', 'Descrição')
                                        !!}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                @if(!isset($item))
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>
                                    @isset($nuvemshop)
                                    @if($nuvemshop == 1)
                                    <input type="hidden" name="redirect_nuvemshop" value="1">
                                    @endif
                                    @endif
                                    <div class="form-check form-switch form-checkbox-info">
                                        <input type="checkbox" name="nuvemshop" class="form-check-input" id="inp-nuvemshop" @isset($item) @if($item->mercado_livre_id != null) checked @endif @endif @isset($nuvemshop) @if($nuvemshop == 1) checked @endif @endif ><strong>Nuvem Shop</strong>
                                    </div>
                                </h4>
                            </div>
                            <div class="card-body div-nuvemshop d-none m-card" style="margin-top: -40px">

                                <div class="row">

                                    <div class="col-md-2">
                                        {!!Form::tel('nuvem_shop_valor', 'Valor para nuvem shop')
                                        ->attrs(['class' => 'moeda inp-nuvemshop'])
                                        !!}
                                    </div>

                                    <div class="col-md-2">
                                        {!!Form::tel('nuvem_shop_valor_promocional', 'Valor promocional')
                                        ->attrs(['class' => 'moeda'])
                                        !!}
                                    </div>

                                    <div class="col-md-4">
                                        {!!Form::select('categoria_nuvem_shop', 'Categoria')
                                        !!}
                                    </div>

                                    <div class="col-md-2">
                                        {!!Form::tel('altura_nuvem_shop', 'Altura')
                                        ->attrs(['class' => 'dimensao inp-nuvemshop'])
                                        !!}
                                    </div>

                                    <div class="col-md-2">
                                        {!!Form::tel('largura_nuvem_shop', 'Largura')
                                        ->attrs(['class' => 'dimensao inp-nuvemshop'])
                                        !!}
                                    </div>

                                    <div class="col-md-2">
                                        {!!Form::tel('comprimento_nuvem_shop', 'Comprimento')
                                        ->attrs(['class' => 'dimensao inp-nuvemshop'])
                                        !!}
                                    </div>

                                    <div class="col-md-2">
                                        {!!Form::tel('peso_nuvem_shop', 'Peso')
                                        ->attrs(['class' => 'peso inp-nuvemshop'])
                                        !!}
                                    </div>

                                    <div class="col-12">
                                        {!!Form::textarea('texto_nuvem_shop', 'Descrição')
                                        !!}
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                @if(!isset($item))
                @if($configMercadoLivre && $configMercadoLivre->access_token)
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>
                                    @isset($mercadolivre)
                                    @if($mercadolivre == 1)
                                    <input type="hidden" name="redirect_mercadolivre" value="1">
                                    @endif
                                    @endif
                                    <div class="form-check form-switch form-checkbox-warning">
                                        <input type="checkbox" name="mercadolivre" class="form-check-input" id="inp-mercadolivre" @isset($item) @if($item->mercado_livre_id != null) checked @endif @endif @isset($mercadolivre) @if($mercadolivre == 1) checked @endif @endif ><strong>Mercado livre</strong>
                                    </div>
                                </h4>
                            </div>

                            <div class="card-body div-mercadolivre d-none m-card" style="margin-top: -40px">

                                <div class="row">

                                    <div class="col-md-2">
                                        {!!Form::tel('mercado_livre_valor', 'Valor do anúcio')
                                        ->value((isset($item) && $item->mercado_livre_valor > 0) ? __moeda($item->mercado_livre_valor) : '')
                                        ->attrs(['class' => 'moeda input-ml'])
                                        !!}
                                    </div>

                                    <div class="col-md-4">
                                        {!!Form::select('mercado_livre_categoria', 'Categoria do anúcio')
                                        ->attrs(['class' => 'form-select select2 input-ml'])
                                        ->options((isset($item) && $item->mercado_livre_categoria) ? 
                                        [$item->mercado_livre_categoria => $item->categoriaMercadoLivre->nome] : [])
                                        !!}
                                    </div>

                                    <div class="col-md-2">
                                        {!!Form::select('condicao_mercado_livre', 'Condição do item', ['new' => 'Novo', 'used' => 'Usado', 'not_specified' => 'Não especificado'])
                                        ->attrs(['class' => 'form-select input-ml'])
                                        !!}
                                    </div>

                                    <div class="col-md-2">
                                        {!!Form::tel('quantidade_mercado_livre', 'Quantidade disponível')
                                        ->attrs(['data-mask' => '00000', 'class' => 'input-ml'])
                                        ->value((isset($item) && $item->estoque) ? number_format($item->estoque->quantidade,0) : '')
                                        !!}
                                    </div>

                                    <div class="col-md-2">
                                        {!!Form::select('mercado_livre_tipo_publicacao', 'Tipo publicação')
                                        ->attrs(['class' => 'select2 input-ml'])
                                        !!}
                                    </div>

                                    <input type="hidden" id="tipo_publicacao_hidden" value="{{ isset($item) ? $item->mercado_livre_tipo_publicacao : '' }}">

                                    <div class="col-md-6">
                                        {!!Form::text('mercado_livre_youtube', 'Link do youtube')
                                        ->attrs(['class' => ''])
                                        !!}
                                    </div>

                                    <div class="col-md-12">
                                        {!!Form::textarea('mercado_livre_descricao', 'Descrição')
                                        ->attrs(['rows' => '12'])
                                        !!}
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                @endif

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>
                                    <div class="form-check form-switch">
                                        <input type="checkbox" class="form-check-input" id="inp-veiculos" @isset($item) @if($item->placa != '') checked @endif @endif><strong>Veículos</strong>
                                    </div>
                                </h4>
                            </div>
                            <div class="card-body div-veiculos d-none m-card" style="margin-top: -40px">
                                <div class="row g-2">
                                    <div class="col-md-2">
                                        {!! Form::text('placa', 'Placa')->attrs(['class' => 'placa']) !!}
                                    </div>
                                    <div class="col-md-2">
                                        {!! Form::select('uf', 'UF', App\Models\Cidade::estados())->attrs(['class' => 'form-select select2']) !!}
                                    </div>
                                    <div class="col-md-2">
                                        {!! Form::select('estado_veiculo', 'Estado do Veículo', ['' => 'Selecione', 'Novo' => 'Novo', 'Semi Novo' => 'Semi Novo', 'Usado' => 'Usado'])->attrs(['class' => 'form-select']) !!}
                                    </div>
                                    <div class="col-md-2">
                                        {!! Form::select('marca', 'Marca', App\Models\Veiculo::marcas())->attrs(['class' => 'form-select']) !!}
                                    </div>
                                    <div class="col-md-2">
                                        {!! Form::text('modelo', 'Modelo')->attrs(['data-mask' => 'AAAAAAAAAAAAAAAAAAA']) !!}
                                    </div>
                                    <div class="col-md-2">
                                        {!! Form::text('chassi', 'Chassi')->attrs(['class' => 'form-control']) !!}
                                    </div>
                                    <div class="col-md-2">
                                        {!! Form::select('cor_interna', 'Cor Interna', App\Models\Veiculo::cores())->attrs(['class' => 'form-select']) !!}
                                    </div>
                                    <div class="col-md-2">
                                        {!! Form::select('cor_externa', 'Cor Externa', App\Models\Veiculo::cores())->attrs(['class' => 'form-select']) !!}
                                    </div>
                                    <div class="col-md-2">
                                        {!! Form::text('ano_fabricacao', 'Ano Fabricação')->attrs(['class' => 'form-control']) !!}
                                    </div>
                                    <div class="col-md-2">
                                        {!! Form::text('ano_modelo', 'Ano Modelo')->attrs(['class' => 'form-control']) !!}
                                    </div>
                                    <div class="col-md-2">
                                        {!! Form::text('numero_motor', 'N° do Motor')->attrs(['class' => 'form-control']) !!}
                                    </div>
                                    <div class="col-md-2">
                                        {!! Form::text('potencia_cv', 'Potência (CV)')->attrs(['class' => 'form-control']) !!}
                                    </div>
                                    <div class="col-md-2">
                                        {!! Form::text('cilindradas', 'Cilindradas')->attrs(['class' => 'form-control']) !!}
                                    </div>
                                    <div class="col-md-2">
                                        {!! Form::text('capacidade', 'Capacidade')->attrs(['data-mask' => '0000000000']) !!}
                                    </div>
                                    <div class="col-md-2">
                                        {!! Form::text('portas', 'Portas')->attrs(['class' => 'form-control']) !!}
                                    </div>
                                    <div class="col-md-2">
                                        {!! Form::select('combustivel', 'Combustível', App\Models\Veiculo::combustiveis())->attrs(['class' => 'form-select']) !!}
                                    </div>
                                    <div class="col-md-2">
                                        {!! Form::select('cidade_emplacamento', 'Cidade do Emplacamento', App\Models\Cidade::lista())->attrs(['class' => 'form-select']) !!}
                                    </div>
                                    
                                    <div class="col-md-2">
                                        {!! Form::text('rntrc', 'RNTRC')->attrs(['data-mask' => '00000000']) !!}
                                    </div>
                                    <div class="col-md-2">
                                        {!! Form::text('renavam', 'Renavam')->attrs(['data-mask' => '000000000']) !!}
                                    </div>
                                    <div class="col-md-2">
                                        {!! Form::text('taf', 'TAF')->attrs(['data-mask' => '00000000000000']) !!}
                                    </div>
                                    <div class="col-md-2">
                                        {!! Form::text('numero_registro_estadual', 'Nº registro estadual')->attrs(['data-mask' => '0000000000000000000000000']) !!}
                                    </div>
                                    <div class="col-md-2">
                                        {!! Form::text('tara', 'Tara')->attrs(['data-mask' => '0000000000']) !!}
                                    </div>
                                    
                                    <div class="col-md-3">
                                        {!! Form::text('proprietario_nome', 'Nome do proprietário') !!}
                                    </div>
                                    <div class="col-md-2">
                                        {!! Form::text('proprietario_documento', 'CPF/CNPJ')->attrs(['class' => 'cpf_cnpj']) !!}
                                    </div>
                                    <div class="col-md-2">
                                        {!! Form::text('proprietario_ie', 'IE')->attrs(['class' => 'ie_rg']) !!}
                                    </div>
                                    <div class="col-md-2">
                                        {!! Form::select('proprietario_uf', 'UF proprietário', App\Models\Cidade::estados())->attrs(['class' => 'form-select select2']) !!}
                                    </div>
                                    <div class="col-md-2">
                                        {!! Form::select('proprietario_tp', 'Tipo do proprietário', App\Models\Veiculo::tiposProprietario())->attrs(['class' => 'form-select']) !!}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>     

                @if(__isActivePlan(Auth::user()->empresa, 'Ecommerce'))
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>
                                    @isset($ecommerce)
                                    @if($ecommerce == 1)
                                    <input type="hidden" name="redirect_ecommerce" value="1">
                                    @endif
                                    @endif
                                    <div class="form-check form-switch form-checkbox-info">
                                        <input type="checkbox" name="ecommerce" class="form-check-input" id="inp-ecommerce" @isset($item) @if($item->ecommerce) checked @endif @endif @isset($ecommerce) @if($ecommerce == 1) checked @endif @endif ><strong>Ecommerce</strong>
                                    </div>
                                </h4>
                            </div>

                            <div class="card-body div-ecommerce d-none m-card" style="margin-top: -40px">

                                <div class="row">

                                    <div class="col-md-2">
                                        {!!Form::tel('valor_ecommerce', 'Valor de Ecommerce')
                                        ->value((isset($item) && $item->valor_ecommerce > 0) ? __moeda($item->valor_ecommerce) : '')
                                        ->attrs(['class' => 'moeda'])
                                        !!}
                                    </div>

                                    <div class="col-md-2">
                                        {!!Form::tel('percentual_desconto', '% de desconto')
                                        ->attrs(['class' => 'percentual'])
                                        !!}
                                    </div>

                                    <div class="col-md-8">
                                        {!!Form::text('descricao_ecommerce', 'Descrição curta')
                                        ->attrs(['class' => ''])
                                        !!}
                                    </div>

                                    <div class="col-md-2">
                                        {!!Form::tel('largura', 'Largura')
                                        ->attrs(['class' => 'dimensao'])
                                        !!}
                                    </div>
                                    <div class="col-md-2">
                                        {!!Form::tel('comprimento', 'Comprimento')
                                        ->attrs(['class' => 'dimensao'])
                                        !!}
                                    </div>
                                    <div class="col-md-2">
                                        {!!Form::tel('altura', 'Altura')
                                        ->attrs(['class' => 'dimensao'])
                                        !!}
                                    </div>
                                    <div class="col-md-2">
                                        {!!Form::tel('peso', 'Peso')
                                        ->attrs(['class' => 'peso'])
                                        !!}
                                    </div>

                                    <div class="col-md-2">
                                        {!!Form::select('destaque_ecommerce', 'Destaque', ['0' => 'Não', '1' => 'Sim'])
                                        ->attrs(['class' => 'form-select'])
                                        !!}
                                    </div>

                                    <div class="col-12">
                                        {!!Form::textarea('texto_ecommerce', 'Descrição longa')
                                        ->attrs(['class' => 'tiny'])
                                        !!}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                @if(__isActivePlan(Auth::user()->empresa, 'Reservas'))
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>
                                    @isset($reserva)
                                    @if($reserva == 1)
                                    <input type="hidden" name="redirect_reserva" value="1">
                                    @endif
                                    @endif
                                    <div class="form-check form-switch form-checkbox-danger">
                                        <input type="checkbox" name="reserva" class="form-check-input" id="inp-reserva" @isset($item) @if($item->reserva) checked @endif @endif @isset($reserva) @if($reserva == 1) checked @endif @endif ><strong>Reserva</strong>
                                    </div>
                                </h4>
                            </div>

                        </div>
                    </div>
                </div>
                @endif

            </div>
        </div>
    </div>

</div>
<hr class="mt-4">
@if(!isset($not_submit))
<div class="col-12" style="text-align: right;">
    <button type="submit" class="btn btn-success btn-action px-5">Salvar</button>
</div>
@endif
</div>
@if(!isset($not_submit))
@section('js')

<script type="text/javascript" src="/js/produto.js"></script>

<script src="/tinymce/tinymce.min.js"></script>
<script type="text/javascript">
    $(function(){
        tinymce.init({ selector: 'textarea.tiny', language: 'pt_BR'})

        setTimeout(() => {
            $('.tox-promotion, .tox-statusbar__right-container').addClass('d-none')
        }, 500)
    })
</script>
<script src="/assets/vendor/twitter-bootstrap-wizard/jquery.bootstrap.wizard.min.js"></script>
<script src="/assets/js/pages/demo.form-wizard.js"></script>
@endsection
@endif
