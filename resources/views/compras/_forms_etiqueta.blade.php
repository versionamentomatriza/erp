<div class="row g-2">

    <div class="col-md-3">
        {!!Form::select('modelo_id', 'Modelo', ['' => 'Selecione'] + $modelos->pluck('nome', 'id')->all())->attrs(['class' => 'select2'])
        ->required()
        !!}
    </div>

    <div class="row">
        <div class="card">
            <div class="card-header">
                <h5>Selecione os itens da compra para gerer etiqueta</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach($item->itens as $p)
                    <div class="col-md-4">
                        {!!Form::checkbox('produto[]', $p->produto->nome . "  - QTD: " . $p->quantidade)->attrs(['class' => ''])
                        ->value($p->produto_id)
                        !!}
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    <hr>
    <div class="col-md-2">
        {!!Form::select('tipo', 'Tipo', ['simples' => 'Simples', 'gondola' => 'Gôndola'])->attrs(['class' => 'form-select'])
        ->required()
        !!}
    </div>
    <div class="col-md-2">
        {!!Form::tel('altura', 'Altura')->attrs(['data-mask' => '000.00', 'data-mask-reverse' => 'true'])->required()
        !!}
    </div>
    <div class="col-md-2">
        {!!Form::tel('largura', 'Largura')->attrs(['data-mask' => '000.00', 'data-mask-reverse' => 'true'])->required()
        !!}
    </div>
    <div class="col-md-2">
        {!!Form::tel('quantidade_etiquetas', 'Quantidade de etiquetas')->attrs(['data-mask' => '000'])->required()
        !!}
    </div>
    <div class="col-md-2">
        {!!Form::tel('etiquestas_por_linha', 'Etiquetas por linha')->attrs(['data-mask' => '00'])->required()
        !!}
    </div>
    <div class="col-md-2">
        {!!Form::tel('distancia_etiquetas_lateral', 'Distância etiqueta lateral')->attrs(['data-mask' => '000.00', 'data-mask-reverse' => 'true'])->required()
        !!}
    </div>
    <div class="col-md-2">
        {!!Form::tel('distancia_etiquetas_topo', 'Distância etiqueta topo')->attrs(['data-mask' => '000.00', 'data-mask-reverse' => 'true'])->required()
        !!}
    </div>
    <div class="col-md-2">
        {!!Form::tel('tamanho_fonte', 'Tamanho da fonte')->attrs(['data-mask' => '000.00', 'data-mask-reverse' => 'true'])->required()
        !!}
    </div>
    <div class="col-md-3">
        {!!Form::tel('tamanho_codigo_barras', 'Tamanho do código de barras')->attrs(['data-mask' => '000.00', 'data-mask-reverse' => 'true'])->required()
        !!}
    </div>
    <hr>
    <div class="col-md-3">
        {!!Form::checkbox('nome_empresa', 'Nome da empresa')->attrs(['class' => ''])
        ->value(1)
        !!}
    </div>
    <div class="col-md-3">
        {!!Form::checkbox('nome_produto', 'Nome do produto')->attrs(['class' => ''])
        ->value(1)
        !!}
    </div>
    <div class="col-md-3">
        {!!Form::checkbox('valor_produto', 'Valor do produto')->attrs(['class' => ''])
        ->value(1)
        !!}
    </div>
    <div class="col-md-3">
        {!!Form::checkbox('codigo_produto', 'Código do produto')->attrs(['class' => ''])
        ->value(1)
        !!}
    </div>


    
    <hr class="mt-4">
    <div class="col-12" style="text-align: right;">
        <button type="submit" class="btn btn-success px-5" id="btn-store">Gerar</button>
    </div>
</div>
