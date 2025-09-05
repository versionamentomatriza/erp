@extends('layouts.app', ['title' => isset($isCompra) ? 'Nova Compra' : (isset($isOrcamento) && $isOrcamento == 1 ? 'Novo orçamento' : 'Nova Venda')])
@section('content')

<div class="card mt-1">
    <div class="card-header">
        @isset($isCompra)
        <h4>Nova Compra</h4>
        @else
        @if(isset($isOrcamento) && $isOrcamento == 1)
        <h4>Novo Orçamento</h4>
        @else
        <h4>Nova Venda</h4>
        @endif
        @endif

        @isset($isReserva)
        <p>Consumo da reserva <strong>#{{ $item->numero_sequencial }}</strong></p>
        @endif

        @if(isset($isOrcamento))
        <input type="hidden" id="is_orcamento" value="1">
        @else
        <input type="hidden" id="is_orcamento" value="0">
        @endif

        <div style="text-align: right; margin-top: -35px;">
            @if(__countLocalAtivo() > 1 && isset($caixa))
            <h5 class="mt-2">Local: <strong class="text-danger">{{ $caixa->localizacao ? $caixa->localizacao->descricao : '' }}</strong></h5>
            @endif
            <a href="{{ !isset($isCompra) ? route('nfe.index') : route('compras.index') }}" class="btn btn-danger btn-sm px-3">
                <i class="ri-arrow-left-double-fill"></i>Voltar
            </a>
        </div>
    </div>
    <div class="card-body">
        {!!Form::open()
        ->post()
        ->id('form-nfe')
        ->route('nfe.store')
        ->attrs([
        'onsubmit' => "let btn=this.querySelector('button[type=submit]'); btn.disabled=true; btn.innerText='Salvando...';"
    ])
        
        !!}
        <div class="pl-lg-4">
            @include('nfe._forms')
        </div>
        {!!Form::close()!!}
    </div>
</div>
@isset($isCompra)
@include('modals._novo_fornecedor')
@else
@include('modals._novo_cliente')
@endif
@section('js')

<script type="text/javascript"> 
    $(".tipo_pagamento").change(() => {
        let tipo = $(".tipo_pagamento").val();
        if (tipo == "03" || tipo == "04") {
            $('#cartao_credito').modal('show')
        }
    })
</script>

<script src="/js/nfe.js"></script>
@isset($isCompra)
<script src="/js/novo_fornecedor.js"></script>
@else
<script src="/js/novo_cliente.js"></script>
@endif
@endsection
@endsection
