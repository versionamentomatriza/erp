@extends('layouts.app', ['title' => 'Lista de Caixa'])
@section('content')

<div class="card mt-1">
    <div class="card-body">
        @if(__isAdmin())
        <a href="{{ route('caixa.abertos-empresa') }}" class="btn btn-dark mb-2">
            <i class="ri-list-indefinite"></i>
            Listar todos os caixas abertos
        </a>
        @endif
        <div class="table-responsive">
            <table class="table table-striped table-centered mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Caixa</th>
                        <th>Data Abertura</th>
                        <th>Data Fechamento</th>
                        <th>Valor Abertura</th>
                        <th>Valor Fechamento</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($data as $item)
                    <tr>
                        <td>{{ $item->usuario->name }}</td>
                        <td>{{ __data_pt($item->created_at) }}</td>
                        <td>{{ $item->data_fechamento ? __data_pt($item->data_fechamento) : '--' }}</td>
                        <td>{{ __moeda($item->valor_abertura) }}</td>
                        <td>{{ __moeda($item->valor_fechamento) }}</td>
                        <td>
                            @if($item->status == 0)
								<a target="_blank" class="btn btn-dark btn-sm" 
								   href="{{ route('caixa.imprimir', $item) }}" 
								   title="Imprimir Comprovante">
									<i class="ri-printer-fill"></i>
								</a>

								<a target="_blank" class="btn btn-success btn-sm" 
								   href="{{ route('caixa.imprimir.produtos', $item) }}" 
								   title="Imprimir Produtos">
									<i class="ri-file-list-3-line"></i>
								</a>
								
								<a target="_blank" class="btn btn-warning btn-sm" 
								   href="{{ route('caixa.imprimir.produtos.clientes', $item) }}" 
								   title="Imprimir Produtos - Clientes">
									<i class="ri-file-user-line"></i>
								</a>
								
								<a class="btn btn-primary btn-sm" href="{{ route('caixa.show' , $item) }}"><i class=" ri-list-indefinite"></i></a>
							@else
								<a target="_blank" class="btn btn-success btn-sm" 
								   href="{{ route('caixa.imprimir.produtos', $item) }}" 
								   title="Imprimir Produtos">
									<i class="ri-file-list-3-line"></i>
								</a>
								
								<a class="btn btn-primary btn-sm" href="{{ route('caixa.show' , $item) }}"><i class=" ri-list-indefinite"></i></a>
                            @endif
							

                            
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center">Nada encontrado</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('js')

@endsection
