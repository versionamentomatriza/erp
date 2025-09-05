@extends('layouts.app', ['title' => 'Taxa de Cartão'])
@section('content')
<div class="mt-3">
    <div class="row">
        <div class="card">
            <div class="card-body">
                <div class="col-md-4">
                    @can('taxa_pagamento_create')
                    <a href="{{ route('taxa-cartao.create') }}" class="btn btn-success">
                        <i class="ri-add-circle-fill"></i>
                        Nova Taxa
                    </a>
                    @endcan
                </div>
                <hr class="mt-3">
                <div class="col-md-12 mt-3">
                    <div class="table-responsive-sm">
                        <table class="table table-striped table-centered mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>Tipo Pagamento</th>
                                    <th>Bandeira</th>
                                    <th>Taxa</th>
                                    <th width="10%">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($data as $item)
                                <tr>
                                    <td>{{ $item->getTipo() }}</td>
                                    <td>{{ $item->bandeira_cartao ? $item->getBandeira() : '--' }}</td>
                                    <td>{{ __moeda($item->taxa) }}</td>
                                    <td>
                                        <form action="{{ route('taxa-cartao.destroy', $item->id) }}" method="post" id="form-{{$item->id}}">
                                            @method('delete')
                                            @can('taxa_pagamento_edit')
                                            <a class="btn btn-warning btn-sm" href="{{ route('taxa-cartao.edit', [$item->id]) }}">
                                                <i class="ri-edit-line"></i>
                                            </a>
                                            @endcan
                                            @csrf

                                            @can('taxa_pagamento_delete')
                                            <button type="button" class="btn btn-delete btn-sm btn-danger">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                            @endcan
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center">Nada encontrado</td>
                                </tr>
                                @endforelse
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
