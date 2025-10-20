@extends('layouts.app', ['title' => 'Contas Financeiras'])
@section('content')
    <div class="mt-3">
        <div class="row">
            <div class="card">
                <div class="card-body">
                    <div class="col-md-2">
                        <a href="{{ route('contas-financeiras.create') }}" class="btn btn-success">
                            <i class="ri-add-circle-fill"></i>
                            Nova Conta
                        </a>
                    </div>

                    <hr class="mt-3">

                    <div class="col-md-12 mt-3">
                        <div class="table-responsive-sm">
                            <table class="table table-striped table-centered mb-0">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Nome</th>
                                        <th>Banco</th>
                                        <th>Agência</th>
                                        <th>Conta</th>
                                        <th>Saldo Inicial</th>
                                        <th>Saldo Atual</th>
                                        <th width="10%">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($contas as $conta)
                                        <tr>
                                            <td>{{ $conta->nome }}</td>
                                            <td>{{ $conta->banco }}</td>
                                            <td>{{ $conta->agencia }}</td>
                                            <td>{{ $conta->conta }}</td>
                                            <td>{{ __moeda($conta->saldo_inicial) }}</td>
                                            <td>{{ __moeda($conta->saldo_atual) }}</td>

                                            <td>
                                                <form action="{{ route('contas-financeiras.destroy', $conta->id) }}"
                                                    method="post" id="form-{{$conta->id}}" style="width: 150px;">
                                                    @csrf
                                                    @method('delete')
                                                    <a class="btn btn-warning btn-sm"
                                                        href="{{ route('contas-financeiras.edit', [$conta->id]) }}">
                                                        <i class="ri-pencil-fill"></i>
                                                    </a>

                                                    <button type="button" class="btn btn-delete btn-sm btn-danger">
                                                        <i class="ri-delete-bin-line"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center">Nada encontrado</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('js')

@endsection