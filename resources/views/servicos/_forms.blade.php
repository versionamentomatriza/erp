<div class="row g-2">
    <div class="col-md-4">
        {!!Form::text('nome', 'Nome')->attrs(['class' => ''])->required()
        !!}
    </div>
    <div class="col-md-2">
        {!!Form::tel('valor', 'Valor')->attrs(['class' => 'moeda'])->required()
        ->value(isset($item) ? __moeda($item->valor) : '')
        !!}
    </div>
    <div class="col-md-2">
        {!!Form::tel('tempo_servico', 'Tempo de execução (minutos)')->attrs(['data-mask' => '000'])->required()
        !!}
    </div>
    <div class="col-md-2">
        {!!Form::tel('comissao', 'Comissão (opcional)')->attrs(['class' => 'moeda'])
        !!}
    </div>
    <div class="col-md-2">
        {!!Form::select('unidade_cobranca', 'Unidade cobrança', ['UND' => 'UND', 'HORAS' => 'HORAS', 'MIN' => 'MIN'])->attrs(['class' => 'form-select'])
        !!}
    </div>
    <div class="col-md-2">
        {!!Form::select('categoria_id', 'Categoria', ['' => 'Selecione uma categoria'] + $categorias->pluck('nome', 'id')->all())->attrs(['class' => 'form-select'])->required()
        !!}
    </div>
    <div class="col-md-2">
        {!!Form::text('tempo_adicional', 'Tempo adicional')
        ->attrs(['data-mask' => '00'])
        !!}
    </div>

    <div class="col-md-2">
        {!!Form::tel('valor_adicional', 'Valor adicional')->attrs(['class' => 'moeda'])
        ->value(isset($item) ? __moeda($item->valor_adicional) : '')
        !!}
    </div>

    <div class="col-md-2">
        {!!Form::text('tempo_tolerancia', 'Tempo de tolerância')
        ->attrs(['data-mask' => '00'])
        !!}
    </div>

    <div class="col-md-2">
        {!!Form::tel('codigo_servico', 'Código do serviço')->attrs(['class' => ''])
        !!}
    </div>

    <div class="col-md-3">
        {!!Form::tel('codigo_tributacao_municipio', 'Código do tributação municipal')->attrs(['class' => ''])
        !!}
    </div>

    <div class="col-md-2">
        {!!Form::select('status', 'Ativo', ['1' => 'Sim', '0' => 'Não'])
        ->attrs(['class' => 'form-select'])
        !!}
    </div>

    @if(__isActivePlan(Auth::user()->empresa, 'Reservas'))
    <div class="col-md-2">
        {!!Form::select('reserva', 'Usar em reserva', ['0' => 'Não', '1' => 'Sim'])
        ->attrs(['class' => 'form-select'])
        !!}
    </div>
    <div class="col-md-2">
        {!!Form::select('padrao_reserva_nfse', 'Padrão reserva NFSe', ['0' => 'Não', '1' => 'Sim'])
        ->attrs(['class' => 'form-select tooltipp'])
        !!}
        <div class="text-tooltip d-none">
            Marcar como sim se for usar este serviço como padrão na emissão da NFSe de reserva
        </div>
    </div>
    @endif

    @if(__isActivePlan(Auth::user()->empresa, 'Delivery'))
    <div class="col-md-2">
        {!!Form::select('marketplace', 'Usar em marketplace', ['0' => 'Não', '1' => 'Sim'])
        ->attrs(['class' => 'form-select'])
        ->value(isset($item) ? $item->marketplace : (isset($marketplace) && $marketplace == 1 ? 1 : 0))
        !!}
    </div>

    <div class="col-md-2">
        {!!Form::select('destaque_marketplace', 'Destaque no marketplace', ['0' => 'Não', '1' => 'Sim'])
        ->attrs(['class' => 'form-select'])
        !!}
    </div>

    @if(isset($marketplace) && $marketplace == 1)
    <input type="hidden" name="redirect_marketplace" value="1">
    @endif

    @endif

    <div class="col-md-12">
        {!!Form::textarea('descricao', 'Descrição')
        ->attrs(['rows' => '4'])
		->required()
        !!}
    </div>

    <div class="card mt-4">

        <div class="card-body row">
            <h4 class="">Tributação</h4>

            <div class="col-md-2">
                {!!Form::tel('aliquota_iss', '% ISS')->attrs(['class' => 'percentual'])
                !!}
            </div>
            <div class="col-md-2">
                {!!Form::tel('aliquota_pis', '% PIS')->attrs(['class' => 'percentual'])
                !!}
            </div>
            <div class="col-md-2">
                {!!Form::tel('aliquota_cofins', '% COFINS')->attrs(['class' => 'percentual'])
                !!}
            </div>
            <div class="col-md-2">
                {!!Form::tel('aliquota_inss', '% INSS')->attrs(['class' => 'percentual'])
                !!}
            </div>
        </div>
    </div>

    <hr class="">
    {{-- Imagem --}}
    <div class="card col-md-3 form-input">
        <div class="preview">
            <button type="button" id="btn-remove-imagem" class="btn btn-link-danger btn-sm btn-danger">x</button>
            @isset($item)
            <img id="file-ip-1-preview" src="{{ $item->img }}">
            @else
            <img id="file-ip-1-preview" src="/imgs/no-image.png">
            @endif
        </div>
        <label for="file-ip-1">Imagem</label>
        <input type="file" id="file-ip-1" name="image" accept="image/*" onchange="showPreview(event);">
    </div>
    @if($errors->has('image'))
    <div class="text-danger mt-2">
        {{ $errors->first('image') }}
    </div>
    @endif
    <hr class="mt-2">
    {{-- Fim Imagem --}}
    <div class="col-12" style="text-align: right;">
        <button type="submit" class="btn btn-success px-5" id="btn-store">Salvar</button>
    </div>
</div>

@section('js')
<script type="text/javascript" src="/js/uploadImagem.js">

</script>
@endsection
