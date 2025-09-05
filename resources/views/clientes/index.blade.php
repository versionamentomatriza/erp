@extends('layouts.app', ['title' => 'Clientes'])
@section('content')
<div class="mt-3">
    <div class="row">
        <div class="card">
            <div class="card-body">
                <div class="col-md-12">
                    @can('clientes_create')
                    <a href="{{ route('clientes.create') }}" class="btn btn-success">
                        <i class="ri-add-circle-fill"></i>
                        Novo Cliente
                    </a>
                    
                    <a href="{{ route('clientes.import') }}" class="btn btn-info pull-right">
                        <i class="ri-file-upload-line"></i>
                        Upload
                    </a>
                    @endcan

                </div>
                <hr class="mt-3">
                <div class="col-lg-12">
                    {!!Form::open()->fill(request()->all())
                    ->get()
                    !!}
                    <div class="row mt-3">
                        <div class="col-md-5">
                            {!!Form::text('razao_social', 'Pesquisar por nome')
                            !!}
                        </div>
                        <div class="col-md-3">
                            {!!Form::text('cpf_cnpj', 'Pesquisar por CPF/CNPJ')
                            ->attrs(['class' => 'cpf_cnpj'])
                            ->type('tel')
                            !!}
                        </div>
                        <div class="col-md-3 text-left ">
                            <br>

                            <button class="btn btn-primary" type="submit"> <i class="ri-search-line"></i>Pesquisar</button>
                            <a id="clear-filter" class="btn btn-danger" href="{{ route('clientes.index') }}"><i class="ri-eraser-fill"></i>Limpar</a>
                        </div>
                    </div>
                    {!!Form::close()!!}
                </div>
                <div class="col-md-12 mt-3 table-responsive">
                    <div class="table-responsive-sm">
                        <table class="table table-striped table-centered mb-0">
                            <thead class="table-dark">
                                <tr>
                                    @can('clientes_delete')
                                    <th>
                                        <div class="form-check form-checkbox-danger mb-2">
                                            <input class="form-check-input" type="checkbox" id="select-all-checkbox">
                                        </div>
                                    </th>
                                    @endcan
                                    <th>
                                        <x-sortable-column field="razao_social" label="Razão Social" />
                                    </th>

                                    <th>CPF/CNPJ</th>
                                    <th>Cidade</th>
                                    <th>Endereço</th>
                                    <th>CEP</th>
                                    <th>Status</th>
                                    <th width="10%">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($data as $item)
                                <tr>
                                    @can('clientes_delete')
                                    <td>
                                        <div class="form-check form-checkbox-danger mb-2">
                                            <input class="form-check-input check-delete" type="checkbox" name="item_delete[]" value="{{ $item->id }}">
                                        </div>
                                    </td>
                                    @endcan
                                    <td width="500">{{ $item->razao_social }}</td>
                                    <td>{{ $item->cpf_cnpj }}</td>
                                    <td>{{ $item->cidade ? $item->cidade->info : '' }}</td>
                                    <td>{{ $item->rua ? $item->endereco : '--' }}</td>
                                    <td>{{ $item->cep }}</td>
                                    <td>
                                        @if($item->status)
                                        <i class="ri-checkbox-circle-fill text-success"></i>
                                        @else
                                        <i class="ri-close-circle-fill text-danger"></i>
                                        @endif
                                    </td>
                                    <td>
                                        <form action="{{ route('clientes.destroy', $item->id) }}" method="post" id="form-{{$item->id}}" style="width: 200px;">
                                            @method('delete')
                                            @can('clientes_edit')
                                            <a class="btn btn-warning btn-sm" href="{{ route('clientes.edit', [$item->id]) }}">
                                                <i class="ri-pencil-fill"></i>
                                            </a>
                                            @endcan

                                            @csrf
                                            @can('clientes_delete')
                                            <button type="button" class="btn btn-delete btn-sm btn-danger">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                            @endcan

                                            <a title="Informações de cashBack" class="btn btn-dark btn-sm" href="{{ route('clientes.cash-back', [$item->id]) }}">
                                                <i class="ri-coins-fill"></i>
                                            </a>

                                            <a title="Histórico" class="btn btn-primary btn-sm" href="{{ route('clientes.historico', [$item->id]) }}">
                                                <i class="ri-file-list-3-fill"></i>
                                            </a>
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
                        @can('clientes_delete')
                        <form action="{{ route('clientes.destroy-select') }}" method="post" id="form-delete-select">
                            @method('delete')
                            @csrf
                            <div></div>
                            <button type="button" class="btn btn-danger btn-sm btn-delete-all" disabled>
                                <i class="ri-close-circle-line"></i> Remover selecionados
                            </button>
                        </form>
                        @endcan
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
@endsection
