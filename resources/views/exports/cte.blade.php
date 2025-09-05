<table class="table-sm table-borderless" style="border-bottom: 1px solid rgb(206, 206, 206); margin-bottom:10px; width: 100%;">
	<thead>
		<tr>
			<th style ="width: 115px; background-color: #629972; color: #ffffff;">Cliente</th>
			<th style = "background-color: #629972; color: #ffffff;">Valor</th>
			<th style = "background-color: #629972; color: #ffffff;">Núm. doc</th>
			<th style ="width: 100px; background-color: #629972; color: #ffffff;">Chave</th>
			<th style ="width: 85px; background-color: #629972; color: #ffffff;">Estado</th>
			<th style ="width: 115px; background-color: #629972; color: #ffffff;">Data</th>
			@if(__countLocalAtivo() > 1)
			<th>Local</th>
			@endif 
		</tr>
	</thead>
	<tbody>
		@foreach($data as $key => $item)
		<tr class="@if($key%2 == 0) pure-table-odd @endif">
			<td>{{ $item->cliente ? $item->cliente->info : 'consumidor final' }}</td>
			<td>{{ __moeda($item->total) }}</td>
			<td>{{ $item->numero }}</td>
			<td>{{ $item->chave }}</td>
			<td>{{ strtoupper($item->estado) }}</td>
			<td>{{ __data_pt($item->created_at) }}</td>
			@if(__countLocalAtivo() > 1)
			<td>{{ $item->localizacao->descricao }}</td>
			@endif
		</tr>
		@endforeach
	</tbody>
</table>
<h4>Total: R$ {{ __moeda($data->sum('total')) }}</h4>