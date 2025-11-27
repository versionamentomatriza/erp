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

    <div class="col-md-2">
        {!! Form::tel('saldo_inicial', 'Saldo inicial')
        ->attrs([
            'class' => 'moeda',
            'disabled' => isset($item) ? true : false
        ])
        ->required()
                !!}
        {{--
        @if (!isset($item))
        {!! Form::tel('saldo_inicial', 'Saldo inicial')
        ->attrs([
        'class' => 'moeda',
        'disabled' => isset($item) ? true : false
        ])
        ->required()
        !!}
        @else
        <label for="saldoInicial"> Saldo inicial</label>
        <input type="text" id="saldoInicial" name="saldo_inicial" value="{{ $item->saldo_inicial }}"
            class="form-control moeda" disabled>
        @endif
        --}}
    </div>

    @if(__isAdmin() && isset($item))
        <div class="col-md-2">
            {!!Form::tel('saldo_atual', 'Saldo atual')
            ->attrs(['class' => 'moeda'])
            ->value(__moeda($item->saldo_atual))
            ->required()
                    !!}
        </div>
    @endif

    <hr class="mt-4">
    <div class="col-12" style="text-align: right;">
        <button type="submit" class="btn btn-success px-5" id="btn-store">Salvar</button>
    </div>
</div>