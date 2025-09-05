@extends('layouts.app', ['title' => 'Produtos Nuvem Shop'])
@section('content')
<div class="mt-3">
    <div class="row">
        <div class="card">
            <div class="card-body">
                <div class="col-md-12">
                    <a href="{{ route('produtos.create', ['nuvemshop=1']) }}" class="btn btn-success">
                        <i class="ri-add-circle-fill"></i>
                        Novo Produto
                    </a>
                </div>
                <hr class="mt-3">
                
                <div class="col-md-12 mt-3">
                    <div class="table-responsive-sm">
                        <table class="table table-striped table-centered mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>Ações</th>
                                    <th></th>
                                    <th>#</th>
                                    <th>Nome</th>
                                    <th>Código de barras</th>
                                    <th>Valor</th>
                                    <th>Valor promocional</th>
                                    <th>Estoque</th>
                                    <th>Categoria</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($data as $item)
                                <tr>

                                    <td>
                                        <form action="{{ route('nuvem-shop-produtos.destroy', $item->id) }}" method="post" id="form-{{$item->id}}">
                                            @method('delete')
                                            @csrf

                                            <a class="btn btn-warning btn-sm text-white" href="{{ route('nuvem-shop-produtos.edit', [$item->id]) }}">
                                                <i class="ri-pencil-fill"></i>
                                            </a>
                                            <button type="button" class="btn btn-delete btn-sm btn-danger">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>

                                            <a class="btn btn-dark btn-sm text-white" href="{{ route('nuvem-shop-produtos.galery', [$item->id]) }}">
                                                <i class="ri-image-edit-line"></i>
                                            </a>
                                        </form>
                                    </td>
                                    <td>
                                        @if(sizeof($item->images) > 0)
                                        <img class="img-60" src="{{ $item->images[0]->src }}">
                                        @else
                                        <img class="img-60" src="/imgs/no-image.png">
                                        @endif
                                    </td>

                                    <td>{{ $item->id }}</td>
                                    <td>{{ $item->name->pt }}</td>
                                    <td>{{ $item->variants[0]->barcode }}</td>
                                    <td>{{ __moeda($item->variants[0]->price) }}</td>
                                    <td>{{ __moeda($item->variants[0]->promotional_price) }}</td>
                                    <td>{{ number_format($item->variants[0]->stock, 2, '.', '') }}</td>
                                    
                                    <td>
                                        @foreach($item->categories as $key => $c)
                                        {{$c->name->pt}} 
                                        @if(!$loop->last) | @endif
                                        @endforeach
                                    </td>
                                    
                                </tr>

                                @empty
                                <tr>
                                    <td colspan="7" class="text-center">Nada encontrado</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                        <br>
                        
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection

