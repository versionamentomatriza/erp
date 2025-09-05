@extends('layouts.app', ['title' => 'Contadores'])
@section('content')
<div class="mt-3">
    <div class="row">
        <div class="card">
            <div class="card-body">
                <div class="col-md-12">
                    <a href="{{ route('contadores.create') }}" class="btn btn-success">
                        <i class="ri-add-circle-fill"></i>
                        Novo Contador
                    </a>

                </div>
                <hr class="mt-3">
                <div class="col-lg-12">
                    {!!Form::open()->fill(request()->all())
                    ->get()
                    !!}
                    <div class="row mt-3">
                        <div class="col-md-4">
                            {!!Form::text('nome', 'Pesquisar por nome')
                            !!}
                        </div>
                        <div class="col-md-3">
                            {!!Form::tel('cpf_cnpj', 'Pesquisar por documento')
                            !!}
                        </div>
                        <div class="col-md-3 text-left ">
                            <br>
                            <button class="btn btn-primary" type="submit"> <i class="ri-search-line"></i>Pesquisar</button>
                            <a id="clear-filter" class="btn btn-danger" href="{{ route('contadores.index') }}"><i class="ri-eraser-fill"></i>Limpar</a>
                        </div>
                    </div>
                    {!!Form::close()!!}
                </div>
                <div class="col-md-12 mt-3">
                    <div class="table-responsive-sm">
                        <table class="table table-centered">
                            <thead class="table-dark">
                                <tr>
                                    <th>Razão social</th>
                                    <th>Nome fantasia</th>
                                    <th>CNPJ/CPF</th>
                                    <th>IE/RG</th>
                                    <th>Status</th>
                                    <th width="10%">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($data as $item)
                                <tr>
                                    <td>{{ $item->nome }}</td>
                                    <td>{{ $item->nome_fantasia }}</td>
                                    <td>{{ $item->cpf_cnpj }}</td>
                                    <td>{{ $item->ie }}</td>
                                    
                                    <td>
                                        @if($item->status)
                                        <i class="ri-checkbox-circle-fill text-success"></i>
                                        @else
                                        <i class="ri-close-circle-fill text-danger"></i>
                                        @endif
                                    </td>
                                    <td>
                                        <form action="{{ route('contadores.destroy', $item->id) }}" method="post" id="form-{{$item->id}}" style="width: 200px;">
                                            @method('delete')
                                            <a class="btn btn-warning btn-sm" href="{{ route('contadores.edit', [$item->id]) }}">
                                                <i class="ri-pencil-fill"></i>
                                            </a>
                                            @csrf
                                            <button type="button" class="btn btn-delete btn-sm btn-danger">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>

                                            <a title="Empresas do contador" class="btn btn-dark btn-sm" href="{{ route('contadores.show', [$item->id]) }}">
                                                <i class="ri-play-list-add-fill"></i>
                                            </a>

                                            <a title="Financeiro do contador" class="btn btn-success btn-sm" href="{{ route('contadores.financeiro', [$item->id]) }}">
                                                <i class="ri-cash-fill"></i>
                                            </a>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                {!! $data->appends(request()->all())->links() !!}

            </div>
        </div>
    </div>
</div>
@endsection
