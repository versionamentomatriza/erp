@extends('relatorios.default')
@section('content')

<table class="table-sm table-borderless" style="border-bottom: 1px solid rgb(206, 206, 206); margin-bottom:10px;  width: 100%;">
	<thead>
		<tr>
			<th>Remetente</th>
			<th>Destinatário</th>
			<th>Valor a Receber</th>
			<th>Num. doc</th>
			<th>Chave</th>
			<th>Estado</th>
			<th>Data</th>
			@if(__countLocalAtivo() > 1)
			<th>Local</th>
			@endif
		</tr>
	</thead>
	<tbody>
		@foreach($data as $key => $item)
		<tr class="@if($key%2 == 0) pure-table-odd @endif">
			<td>{{ $item->remetente->razao_social }}</td>
			<td>{{ $item->destinatario->razao_social }}</td>

			<td>{{ __moeda($item->valor_receber) }}</td>
			<td>{{ $item->numero }}</td>
			<td>{{ $item->chave }}</td>
			<td>{{ strtoupper($item->estado) }}</td>
			<td>{{ __data_pt($item->created_at) }}</td>
			@if(__countLocalAtivo() > 1)
			<td class="text-danger">{{ $item->localizacao->descricao }}</td>
			@endif
		</tr>
		@endforeach
	</tbody>
</table>
<h4>Total a receber: R$ {{ __moeda($data->sum('valor_receber')) }}</h4>
@endsection
