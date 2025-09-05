@extends('layouts.app', ['title' => 'NFe Lista Geral'])
@section('content')
<div class="mt-3">
    <div class="row">
        <div class="card">
            <div class="card-header">
                <h3>Lista de NFe</h3>
            </div>
            <div class="card-body">
                <div class="col-lg-12">
                    {!!Form::open()->fill(request()->all())
                    ->get()
                    !!}
                    <div class="row mt-3">
                        <div class="col-md-4">
                            {!!Form::select('empresa_id', 'Empresa', ['' => 'Selecione'] + $empresas->pluck('nome', 'id')->all())
                            ->attrs(['class' => 'select2'])
                            !!}
                        </div>
                        <div class="col-md-2">
                            {!!Form::date('start_date', 'Data inicial')
                            !!}
                        </div>
                        <div class="col-md-2">
                            {!!Form::date('end_date', 'Data final')
                            !!}
                        </div>
                        <div class="col-md-2">
                            {!!Form::select('estado', 'Estado',
                            ['novo' => 'Nova',
                            'rejeitado' => 'Rejeitadas',
                            'cancelado' => 'Canceladas',
                            'aprovado' => 'Aprovadas',
                            '' => 'Todos'])
                            ->attrs(['class' => 'select2'])
                            !!}
                        </div>
                        <div class="col-md-2">
                            <br>
                            <button class="btn btn-primary" type="submit"> <i class="ri-search-line"></i>Pesquisar</button>
                            <a id="clear-filter" class="btn btn-danger" href="{{ route('nfe-all') }}"><i class="ri-eraser-fill"></i> Limpar</a>

                        </div>
                    </div>
                    {!!Form::close()!!}
                </div>
                <div class="col-md-12 mt-3">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Empresa</th>
                                <th>Cliente</th>
                                <th>Número</th>
                                <th>Valor</th>
                                <th>Estado</th>
                                <th>Ambiente</th>
                                <th>Data</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($data as $item)
                            <tr>
                                <td>{{ $item->empresa->nome }} {{ $item->empresa->cpf_cnpj }}</td>
                                <td>{{ $item->cliente ? $item->cliente->razao_social : "--" }}</td>
                                <td>{{ $item->numero ? $item->numero : '' }}</td>
                                <td>{{ number_format($item->total, 2, ',', '.') }}</td>
                                <td width="150">
                                    @if($item->estado == 'aprovado')
                                    <span class="btn btn-success text-white btn-sm w-100">aprovado</span>
                                    @elseif($item->estado == 'cancelado')
                                    <span class="btn btn-danger text-white btn-sm w-100">cancelado</span>
                                    @elseif($item->estado == 'rejeitado')
                                    <span class="btn btn-warning text-white btn-sm w-100">rejeitado</span>
                                    @else
                                    <span class="btn btn-info text-white btn-sm w-100">novo</span>
                                    @endif
                                </td>
                                <td>{{ $item->ambiente == 2 ? 'Homologação' : 'Produção' }}</td>
                                <td>{{ \Carbon\Carbon::parse($item->created_at)->format('d/m/Y H:i') }}</td>
                                <td width="300">
                                    <form action="{{ route('nfe.destroy', $item->id) }}" method="post" id="form-{{$item->id}}">
                                        @method('delete')
                                        @csrf
                                        @if($item->estado == 'cancelado')
                                        <a class="btn btn-danger btn-sm" target="_blank" href="{{ route('nfe.imprimir-cancela', [$item->id]) }}">
                                            <i class="ri-printer-line"></i>
                                        </a>
                                        @endif
                                        @if($item->estado == 'aprovado')
                                        <a class="btn btn-primary btn-sm" target="_blank" href="{{ route('nfe.imprimir', [$item->id]) }}">
                                            <i class=" ri-printer-line"></i>
                                        </a>

                                        <button title="Cancelar NFe" type="button" class="btn btn-danger btn-sm" onclick="cancelar('{{$item->id}}', '{{$item->numero}}')">
                                            <i class="ri-close-circle-line"></i>
                                        </button>
                                        <button title="Corrigir NFe" type="button" class="btn btn-warning btn-sm" onclick="corrigir('{{$item->id}}', '{{$item->numero}}')">
                                            <i class="ri-file-warning-line"></i>
                                        </button>
                                        @endif

                                        @if($item->estado == 'aprovado' || $item->estado == 'rejeitado')
                                        <button type="button" class="btn btn-dark btn-sm" onclick="info('{{$item->motivo_rejeicao}}', '{{$item->chave}}', '{{$item->estado}}', '{{$item->recibo}}')">
                                            <i class="ri-file-line"></i>
                                        </button>
                                        @endif
                                        @if($item->estado == 'novo' || $item->estado == 'rejeitado')


                                        <a title="XML temporário" class="btn btn-light btn-sm" href="{{ route('nfe.xml-temp', $item->id) }}">
                                            <i class="ri-file-line"></i>
                                        </a>
                                        <button type="button" class="btn btn-danger btn-sm btn-delete">
                                            <i class="ri-delete-bin-line"></i>
                                        </button>

                                        <button title="Transmitir NFe" type="button" class="btn btn-success btn-sm" onclick="transmitir('{{$item->id}}')">
                                            <i class="ri-send-plane-fill"></i>
                                        </button>
                                        @endif

                                        @if($item->estado == 'aprovado' || $item->estado == 'cancelado')
                                        <button title="Consultar NFe" type="button" class="btn btn-light btn-sm" onclick="consultar('{{$item->id}}', '{{$item->numero}}')">
                                            <i class="ri-file-search-line"></i>
                                        </button>
                                        @endif

                                    </form>

                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    {!! $data->appends(request()->all())->links() !!}
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-cancelar" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Cancelar NFe <strong class="ref-numero"></strong></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">

                    <div class="col-md-12">
                        {!!Form::text('motivo-cancela', 'Motivo')
                        ->required()

                        !!}
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Fechar</button>
                <button type="button" id="btn-cancelar" class="btn btn-danger">Cancelar</button>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="modal-corrigir" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Corrigir NFe <strong class="ref-numero"></strong></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">

                    <div class="col-md-12">
                        {!!Form::text('motivo-corrigir', 'Motivo')
                        ->required()

                        !!}
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Fechar</button>
                <button type="button" id="btn-corrigir" class="btn btn-warning">Corrigir</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('js')
<script type="text/javascript">
    function info(motivo_rejeicao, chave, estado, recibo) {

        if (estado == 'rejeitado') {
            let text = "Motivo: " + motivo_rejeicao + "\n"
            text += "Chave: " + chave + "\n"
            swal("", text, "warning")
        } else {
            let text = "Chave: " + chave + "\n"
            text += "Recibo: " + recibo + "\n"
            swal("", text, "success")
        }
    }

</script>
<script type="text/javascript" src="/js/nfe_transmitir.js"></script>
@endsection
