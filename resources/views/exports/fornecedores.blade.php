<table class="table-sm table-borderless" style="border-bottom: 1px solid rgb(206, 206, 206); margin-bottom:10px;  width: 100%;">
	<thead>
		<tr>
			<th style ="width: 320px; background-color: #629972; color: #ffffff;">Razão social</th>
			<th style ="width: 300px; background-color: #629972; color: #ffffff;">Nome fantasia</th>
			<th style ="width: 125px; background-color: #629972; color: #ffffff;">CPF/CNPJ</th>
			<th style ="width: 100px; background-color: #629972; color: #ffffff;">IE</th>
			<th style ="width: 320px; background-color: #629972; color: #ffffff;">Endereço</th>
			<th style ="width: 170px; background-color: #629972; color: #ffffff;">Cidade</th>
			<th style ="width: 110px; background-color: #629972; color: #ffffff;">Data de cadastro</th>
			@if($tipo != '')
			<th>Total vendido</th>
			@endif
		</tr>
	</thead>
	<tbody>
		@foreach($data as $key => $item)
		<tr class="@if($key%2 == 0) pure-table-odd @endif">
			<td>{{ $item->razao_social }}</td>
			<td>{{ $item->nome_fantasia }}</td>
			<td>{{ $item->cpf_cnpj }}</td>
			<td>{{ $item->ie }}</td>
			<td>{{ $item->endereco }}</td>
			<td>{{ $item->cidade ? $item->cidade->info : '--' }}</td>
			<td>{{ __data_pt($item->created_at) }}</td>
			@if($tipo != '') 
			<td>{{ __moeda($item->total) }}</td>
			@endif
		</tr>
		@endforeach
	</tbody>
</table>
@if($tipo != '')
<h4>Total: R$ {{ __moeda($data->sum('total')) }}</h4>
@endif
