@extends('layouts.app', ['title' => 'Configurações Gerais'])
@section('content')
<div class="mt-3">
    <div class="row">
        <div class="card">
            <div class="card-body">
                <h4>Configuração Geral</h4>
                <hr>
                <div class="row mt-3">
                    <div class="col-lg-12">
                        {!!Form::open()->fill($item)
                        ->post()
                        ->route('config-geral.store')
                        ->multipart()
                        !!}
                        <div class="m-2">
                            <h5 class="card-header text-white" style="background-color: #629972;">PDV</h5>

                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        {!!Form::text('balanca_digito_verificador', 'Referência produto balança (dígitos)')->value(isset($item) ? $item->balanca_digito_verificador : '')
                                        !!}
                                    </div>
                                    <div class="col-md-2">
                                        {!!Form::select('balanca_valor_peso', 'Tipo unidade balança', ['valor' => 'Valor', 'peso' => 'Peso'])->attrs(['class' => 'form-select'])
                                        !!}
                                    </div>
                                    <div class="col-md-2">
                                        {!!Form::select('abrir_modal_cartao', 'Abrir modal dados do cartão', ['1' => 'Sim', '0' => 'Não'])->attrs(['class' => 'form-select'])
                                        !!}
                                    </div>
                                    <div class="col-md-2">
                                        {!!Form::text('senha_manipula_valor', 'Senha para desconto/acréscimo')
                                        !!}
                                    </div>

                                    <div class="row mt-2">
                                        <h5>Tipos de pagamento</h5>
                                        @if(!isset($item))
                                        <div class="row">
                                            <div class="col-lg-3 col-6">
                                                <input type="checkbox" class="form-check-input check_todos" style=" width: 25px; height: 25px;">
                                                <label class="form-check-label m-1" for="customCheck1">Marcar todos</label>
                                            </div>
                                        </div>
                                        @endif
                                        @foreach(\App\Models\Nfce::tiposPagamento() as $key => $t)
                                        <div class="col-lg-3 col-6">
                                            <input name="tipos_pagamento_pdv[]" value="{{$t}}" type="checkbox" class="form-check-input check-module" style=" width: 25px; height: 25px;" @isset($item) @if(sizeof($item->tipos_pagamento_pdv) > 0 && in_array($t, $item->tipos_pagamento_pdv)) checked="true" @endif @endif>
                                            <label class="form-check-label m-1" for="customCheck1">{{$t}}</label>
                                        </div>
                                        @endforeach
                                    </div>

                                </div>
                            </div>

                            <h5 class="card-header text-white" style="background-color: #629972;">Pré venda</h5>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-2">
                                        {!!Form::select('confirmar_itens_prevenda', 'Confirmar itens pré venda', ['0' => 'Não', '1' => 'Sim'])->attrs(['class' => 'form-select'])
                                        !!}
                                    </div>
                                </div>
                            </div>

                            <h5 class="card-header text-white" style="background-color: #629972;">Orçamento</h5>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-2">
                                        {!!Form::tel('percentual_desconto_orcamento', '% Máximo de desconto sobre lucro')
                                        ->attrs(['class' => 'percentual'])
                                        !!}
                                    </div>
                                </div>
                            </div>

                            <h5 class="card-header text-white" style="background-color: #629972;">Produto</h5>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-2">
                                        {!!Form::tel('percentual_lucro_produto', '% Lucro padrão')
                                        ->attrs(['class' => 'percentual'])
                                        !!}
                                    </div>

                                    <div class="col-md-2">
                                        {!!Form::tel('margem_combo', 'Margem % combo')
                                        ->attrs(['class' => 'percentual'])
                                        !!}
                                    </div>

                                    <div class="col-md-2">
                                        {!!Form::select('gerenciar_estoque', 'Gerenciar estoque', ['0' => 'Não', '1' => 'Sim'])->attrs(['class' => 'form-select'])
                                        !!}
                                    </div>
                                </div>
                            </div>

                            <h5 class="card-header text-white" style="background-color: #629972;">Alertas</h5>
                            <div class="card-body">



                                <div class="row m-3">
                                    @foreach(App\Models\ConfigGeral::getNotificacoes() as $n)
                                    <div class="col-lg-3 col-6">
                                        <input name="notificacoes[]" value="{{$n}}" type="checkbox" class="form-check-input" style=" width: 25px; height: 25px;" @isset($item) @if(sizeof($item->notificacoes) > 0 && in_array($n, $item->notificacoes)) checked="true" @endif @endif>
                                        <label class="form-check-label m-1" for="customCheck1">{{$n}}</label>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            <hr class="mt-2">
                            <div class="col-12" style="text-align: right;">
                                <button type="submit" class="btn btn-success px-5" id="btn-store">Salvar</button>
                            </div>
                        </div>
                        {!!Form::close()!!}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
