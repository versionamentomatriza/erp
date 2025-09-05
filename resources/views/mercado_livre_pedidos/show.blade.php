@extends('layouts.app', ['title' => 'Pedido Mercado Livre #'.$item->_id])
@section('css')
<style type="text/css">
    @page { size: auto;  margin: 0mm; }

    @media print {
        .print{
            margin: 10px;
        }
    }
</style>
@endsection
@section('content')

<div class="card mt-1 print">
    <div class="card-body">
        <div class="pl-lg-4">

            <div class="ms">

                <div class="mt-3 d-print-none" style="text-align: right;">
                    <a href="{{ route('mercado-livre-pedidos.index') }}" class="btn btn-danger btn-sm px-3">
                        <i class="ri-arrow-left-double-fill"></i>Voltar
                    </a>
                </div>
                <div class="row mb-2">

                    <div class="col-md-3 col-6">
                        <h5><strong class="text-danger">#{{ $item->_id }}</strong></h5>
                    </div>
                    <div class="col-md-3 col-6">
                        <h5>Data do pedido: <strong class="text-primary">{{ __data_pt($item->data_pedido) }}</strong></h5>
                    </div>

                    <div class="col-md-3 col-6">
                        <h5>Data de cadastro no sistema: <strong class="text-primary">{{ __data_pt($item->created_at) }}</strong></h5>
                    </div>

                    <div class="col-md-3 col-6">
                        <h5>Código de rastreamento: <strong class="text-primary">{{ $item->codigo_rastreamento ? $item->codigo_rastreamento : '--' }}</strong></h5>
                    </div>

                    <div class="col-md-3 col-6">
                        <h5>Valor Total: <strong class="text-primary">R$ {{ __moeda($item->total) }}</strong> </h5>
                    </div>

                    <div class="col-md-3 col-6">
                        <h5>Valor Entrega: <strong class="text-primary">R$ {{ __moeda($item->valor_entrega) }}</strong> </h5>
                    </div>

                </div>

                <a class="btn btn-primary btn-sm d-print-none" href="javascript:window.print()" ><i class="ri-printer-line d-print-none"></i>
                    Imprimir
                </a>
                @if($item->nfe_id == 0)
                <a class="btn btn-success btn-sm d-print-none" href="{{ route('mercado-livre-pedidos.gerar-nfe', $item->id) }}">
                    <i class="ri-file-text-line"></i>
                    Gerar NFe
                </a>
                @else
                <a class="btn btn-success btn-sm d-print-none" href="{{ route('nfe.show', $item->nfe_id) }}">
                    <i class="ri-file-text-line"></i>
                    Ver NFe
                </a>

                @endif

                <a class="btn btn-dark btn-sm" href="{{ route('mercado-livre-pedidos.chat', [$item->id]) }}">
                    <i class="ri-chat-4-fill"></i>
                    Chat
                </a>


            </div>

            <div class="row mt-2">
                <h4>Itens do pedido</h4>
                <div class="table-responsive-sm">
                    <table class="table table-striped table-centered mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>Produto</th>
                                <th>Quantidade</th>
                                <th>Valor unitário</th>
                                <th>Sub total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($item->itens as $i)
                            <tr>
                                <td>{{ $i->produto->nome }}</td>
                                <td>{{ number_format($i->quantidade, 0) }}</td>
                                <td>{{ __moeda($i->valor_unitario) }}</td>
                                <td>{{ __moeda($i->sub_total) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="row mt-4">

                <div class="col-md-6 col-12">
                    <h4>
                        Cliente: <strong>{{ $item->cliente_nome }}</strong>
                        @if($item->cliente)
                        <a href="{{ route('clientes.edit', [$item->cliente_id]) }}" class="btn btn-warning btn-sm d-print-none">
                            <i class="ri-edit-line"></i>
                        </a>
                        @else
                        <button class="btn btn-dark btn-sm d-print-none" data-bs-toggle="modal" data-bs-target="#modal-cliente">Atribuir cliente</button>
                        @endif
                    </h4>
                    <h4>ID: <strong>{{ $item->seller_id }}</strong></h4>

                </div>
                <div class="col-md-6 col-12">
                    <h4>Documento cliente: <strong>{{ $item->cliente_documento }}</strong></h4>
                    <h4>Comentário do pedido: <strong>{{ $item->comentario ? $item->comentario : '--' }}</strong></h4>
                </div>
            </div>

        </div>
    </div>
</div>

<div class="modal fade" id="modal-cliente" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="{{ route('mercado-livre-pedidos.set-cliente', [$item->id]) }}">
                @csrf
                @method('put')
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Atribuir cliente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">

                        <div class="col-md-12">
                            {!!Form::select('cliente_id', 'Cliente')
                            ->required()

                            !!}
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Fechar</button>
                    <button type="submit" class="btn btn-success">Atribuir</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('js')
<script type="text/javascript">
    $("#inp-cliente_id").select2({
        minimumInputLength: 2,
        language: "pt-BR",
        placeholder: "Digite para buscar o cliente",
        theme: "bootstrap4",
        dropdownParent: $('#modal-cliente'),
        ajax: {
            cache: true,
            url: path_url + "api/clientes/pesquisa",
            dataType: "json",
            data: function (params) {
                console.clear();
                var query = {
                    pesquisa: params.term,
                    empresa_id: $("#empresa_id").val(),
                };
                return query;
            },
            processResults: function (response) {
                var results = [];

                $.each(response, function (i, v) {
                    var o = {};
                    o.id = v.id;

                    o.text = v.razao_social + " - " + v.cpf_cnpj;
                    o.value = v.id;
                    results.push(o);
                });
                return {
                    results: results,
                };
            },
        },
    });
</script>

@endsection
