@extends('relatorios.default')
@section('content')

<table class="table-sm table-borderless" style="border-bottom: 1px solid rgb(206, 206, 206); margin-bottom:10px;  width: 100%;">
	<thead>
    <tr>
        <th>Fornecedor</th>
        <th>Valor</th>
        <th>Data Vencimento</th>
        <th>Estado</th>
        @if(__countLocalAtivo() > 1)
            <th>Local</th>
        @endif
        @if($request->filled('centro_custo_id'))
            <th>Centro de Custo</th>
        @endif
    </tr>
</thead>
<tbody>
    @foreach($data as $key => $item)
    <tr class="@if($key%2 == 0) pure-table-odd @endif">
        <td>{{ $item->fornecedor->razao_social }}</td>
        <td>{{ __moeda($item->valor_integral) }}</td>
        <td>{{ __data_pt($item->data_vencimento, 0) }}</td>
        <td>{{ $item->status == 0 ? 'Pendente' : 'Quitado' }}</td>
        @if(__countLocalAtivo() > 1)
            <td class="text-danger">{{ $item->localizacao->descricao }}</td>
        @endif
        @if($request->filled('centro_custo_id'))
            <td>{{ $item->centroCusto->descricao ?? '' }}</td>
        @endif
    </tr>
    @endforeach
</tbody>

</table>
<h4>Total: R$ {{ __moeda($data->sum('valor_integral')) }}</h4>
@endsection
