<div class="row g-2">
    <div class="col-md-3">
        {!!Form::text('nome', 'Nome')
        ->required()
        !!}
    </div>
    <div class="col-md-2">
        {!!Form::text('banco', 'Banco')
        !!}
    </div>
    <div class="col-md-2">
        {!!Form::text('agencia', 'Agência')
        !!}
    </div>
    <div class="col-md-2">
        {!!Form::text('conta', 'Conta')
        !!}
    </div>

    @if(!isset($item))
    <div class="col-md-2">
        {!!Form::tel('saldo_inicial', 'Saldo inicial')
        ->attrs(['class' => 'moeda'])
        ->required()
        !!}
    </div>
    @endif

    <div class="col-md-2">
        {!!Form::select('status', 'Status', [1 => 'Ativa', 0 => 'Desativada'])
        ->attrs(['class' => 'form-select'])
        ->required()
        !!}
    </div>

    @if(__isAdmin() && isset($item))
    <div class="col-md-2">
        {!!Form::tel('saldo', 'Saldo atual')
        ->attrs(['class' => 'moeda'])
        ->value(__moeda($item->saldo))
        ->required()
        !!}
    </div>
    @endif

    <div class="col-md-4">
        {!!Form::select('plano_conta_id', 'Plano de conta')
        ->attrs(['class' => 'form-select'])
        ->required()
        ->options(isset($item) ? [$item->plano_conta_id => $item->plano->descricao] : [])
        !!}
    </div>

    <hr class="mt-4">
    <div class="col-12" style="text-align: right;">
        <button type="submit" class="btn btn-success px-5" id="btn-store">Salvar</button>
    </div>
</div>