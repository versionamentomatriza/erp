@extends('layouts.app', ['title' => 'Centro de Custo'])

@section('content')
<div class="mt-3">
    <div class="row">
        <div class="card">
            <div class="card-body">
                <!-- Botão de Criar -->
                <div class="col-md-2">
                    <a href="{{ route('centro-custo.create') }}" class="btn btn-success">
                        <i class="ri-add-circle-fill"></i>
                        Novo Centro de Custo
                    </a>
                </div>
                <hr class="mt-3">
                
                <!-- Filtro de Pesquisa -->
                <div class="col-lg-12">
                    <form method="GET" action="{{ route('centro-custo.index') }}">
                        <div class="row mt-3">
                            <div class="col-md-4">
                                <input type="text" name="descricao" class="form-control" placeholder="Pesquisar por nome" value="{{ request()->descricao }}">
                            </div>
                            <div class="col-md-3 text-left">
                                <button class="btn btn-primary" type="submit">
                                    <i class="ri-search-line"></i> Pesquisar
                                </button>
                                <a id="clear-filter" class="btn btn-danger" href="{{ route('centro-custo.index') }}">
                                    <i class="ri-eraser-fill"></i> Limpar
                                </a>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Tabela -->
                <div class="col-md-12 mt-3">
                    <div class="table-responsive-sm">
                        <table class="table table-centered">
                            <thead class="table-dark">
                                <tr>
                                    <th>Descrição</th>
                                    <th width="20%">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($data as $item)
                                <tr>
                                    <td>{{ $item->descricao }}</td>
                                    <td>
                                        <!-- Botões de Ação -->
                                        <a href="{{ route('centro-custo.edit', [$item->id]) }}" class="btn btn-warning btn-sm text-white">
                                            <i class="ri-pencil-fill"></i> 
                                        </a>
                                        <form action="{{ route('centro-custo.destroy', $item->id) }}" method="POST" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm">
                                                <i class="ri-delete-bin-line"></i> 
                                            </button>
                                        </form>
                                        <a href="{{ route('centro-custo.show', [$item->id]) }}" class="btn btn-light btn-sm" title="Detalhes">
                                            <i class="ri-eye-line"></i>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="2" class="text-center">Nenhum Centro de Custo encontrado.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Paginação -->
                <div class="mt-3">
                    {!! $data->appends(request()->all())->links() !!}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
