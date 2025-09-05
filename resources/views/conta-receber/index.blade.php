@extends('layouts.app', ['title' => 'Conta a receber'])
@section('content')
<div class="mt-3">
    <div class="row">
        <div class="card">
            <div class="card-body">
                <div class="col-md-2">
                    @can('conta_receber_create')
                    <a href="{{ route('conta-receber.create') }}" class="btn btn-success">
                        <i class="ri-add-circle-fill"></i>
                        Nova Conta Receber
                    </a> 
                    @endcan
                </div>
                <hr class="mt-3">
                <div class="col-lg-12">
                    {!!Form::open()->fill(request()->all())
                    ->get()
                    !!}
                    <div class="row mt-3">
                        <div class="col-md-3">
                            {!!Form::select('cliente_id', 'Pesquisar por nome')->attrs(['class' => 'select2'])
                            !!}
                        </div>
                        <div class="col-md-2">
                            {!!Form::date('start_date', 'Data inicial')
                            !!}
                        </div>
                        <div class="col-md-2">
                            {!!Form::date('end_date', 'Data Final')
                            !!}
                        </div>
                        @if(__countLocalAtivo() > 1)
                        <div class="col-md-2">
                            {!!Form::select('local_id', 'Local', ['' => 'Selecione'] + __getLocaisAtivoUsuario()->pluck('descricao', 'id')->all())
                            ->attrs(['class' => 'select2'])
                            !!}
                        </div>
                        @endif
                        <div class="col-md-2">
                                {!! Form::select('ordenar_por', 'Ordenar por', [
                                    '' => 'Selecione',
                                    'data_vencimento_asc' => 'Data de Vencimento (Cres)',
                                    'data_vencimento_desc' => 'Data de Vencimento (Decres)',
                                ])->attrs(['class' => 'form-control']) !!}
                            </div>
                        <div class="col-md-3 text-left ">
                            <br>
                            <button class="btn btn-primary" type="submit"> <i class="ri-search-line"></i>Pesquisar</button>
                            <a id="clear-filter" class="btn btn-danger" href="{{ route('conta-receber.index') }}"><i class="ri-eraser-fill"></i>Limpar</a>
                        </div>
                    </div>
                    {!!Form::close()!!}
                </div>
                <div class="col-md-12 mt-3">
                    <div class="table-responsive-sm">
                        <table class="table table-striped table-centered mb-0">
                            <thead class="table-dark">
                                <tr>
                                    @can('conta_receber_delete')
                                    <th>
                                        <div class="form-check form-checkbox-danger mb-2">
                                            <input class="form-check-input" type="checkbox" id="select-all-checkbox">
                                        </div>
                                    </th>
                                    @endcan
                                    <th>Razão Social</th>
                                    @if(__countLocalAtivo() > 1)
                                    <th>Local</th>
                                    @endif
                                    <th>Valor Integral</th>
                                    <th>Valor Recebido</th>
                                    <th>Data Cadastro</th>
                                    <th>Data Vencimento</th>
                                    <th>Data Recebimento</th>
                                    <th>Estado</th>
                                    <th width="10%">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($data as $item)
                                <tr>
                                    @can('conta_receber_delete')
                                    <td>
                                        <div class="form-check form-checkbox-danger mb-2">
                                            <input class="form-check-input check-delete" type="checkbox" name="item_delete[]" value="{{ $item->id }}">
                                        </div>
                                    </td>
                                    @endcan
                                    <td>{{ ($item->cliente) ? $item->cliente->razao_social : '--' }}</td>
                                    @if(__countLocalAtivo() > 1)
                                    <td class="text-danger">{{ $item->localizacao->descricao }}</td>
                                    @endif
                                    <td>{{ __moeda($item->valor_integral) }}</td>
                                    <td>{{ __moeda($item->valor_recebido) }}</td>
                                    <td>{{ __data_pt($item->created_at, 0) }}</td>
                                    <td>
                                        {{ __data_pt($item->data_vencimento, 0) }}
                                        @if(!$item->status)
                                        <br>
                                        <span class="text-danger" style="font-size: 10px">{{ $item->diasAtraso() }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $item->status ? __data_pt($item->data_recebimento, false) : '--' }}</td>
                                    <td>
                                        @if($item->status)
                                        <span class="btn btn-success position-relative me-lg-5 btn-sm">
                                            <i class="ri-checkbox-line"></i> Recebido
                                        </span>
                                        @else
                                        <span class="btn btn-warning position-relative me-lg-5 btn-sm">
                                            <i class="ri-alert-line"></i> Pendente
                                        </span>
                                        @endif
                                    </td>
                                    <td>
                                        <form action="{{ route('conta-receber.destroy', $item->id) }}" method="post" id="form-{{$item->id}}" style="width: 200px;">


                                            @if(!$item->status)
                                            @method('delete')
                                            @can('conta_receber_edit')
                                            <a class="btn btn-warning btn-sm" href="{{ route('conta-receber.edit', [$item->id]) }}">
                                                <i class="ri-pencil-fill"></i>
                                            </a>
                                            @endcan
                                            @csrf
                                            @can('conta_receber_delete')
                                            <button type="button" class="btn btn-delete btn-sm btn-danger">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                            @endcan
                                            @can('conta_receber_edit')
                                            <a href="{{ route('conta-receber.pay', $item) }}" class="btn btn-success btn-sm text-white">
                                                <i class="ri-money-dollar-box-line"></i>
                                            </a>
                                            @endcan

                                            @endif

                                            @if(!$item->boleto && !$item->status)
                                            @can('boleto_create')
                                            <a title="Gerar boleto" class="btn btn-dark btn-sm" href="{{ route('boleto.create', [$item->id]) }}">
                                                <i class="ri-file-list-2-line"></i>
                                            </a>
                                            @endcan
                                            @else
                                            @can('boleto_view')
                                           @if($item->boleto)
											<a title="Visualizar boleto" class="btn btn-success btn-sm" href="{{ route('boleto.show', [$item->boleto->id]) }}">
												<i class="ri-file-list-3-fill"></i>
											</a>
											@endif

                                            
                                            @endcan
                                            
                                            @endif
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center">Nada encontrado</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                        <br>
                        <div class="row">
                            <div class="col-md-6">
                                @can('conta_receber_delete')
                                <form action="{{ route('conta-receber.destroy-select') }}" method="post" id="form-delete-select">
                                    @method('delete')
                                    @csrf
                                    <div></div>
                                    <button type="button" class="btn btn-danger btn-sm btn-delete-all" disabled>
                                        <i class="ri-close-circle-line"></i> Remover selecionados
                                    </button>
                                </form>
                                @endcan
                            </div>
                            <div class="col-md-6 text-end">
                                @can('boleto_create')
                                <form action="{{ route('boleto.create-several') }}" method="get" id="form-gerar-boletos">
                                    <div></div>
                                    <button type="submit" class="btn btn-dark btn-sm btn-boleto" disabled>
                                        <i class="ri-file-line"></i> Gerar boletos
                                    </button>
                                </form>
                                @endcan
                            </div>
                        </div>
                    </div>
                </div>
                {!! $data->appends(request()->all())->links() !!}
            </div>
        </div>
    </div>
</div>
@endsection
@section('js')
<script type="text/javascript" src="/js/delete_selecionados.js"></script>
<script type="text/javascript" src="/js/boleto.js"></script>
@endsection
