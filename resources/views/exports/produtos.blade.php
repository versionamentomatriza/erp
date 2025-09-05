
<table class="table-sm table-borderless" style="border-bottom: 1px solid rgb(206, 206, 206); margin-bottom:10px;  width: 100%;">
	<thead>
  <tr>
    <th style="width: 200px; background-color: #629972; color: #ffffff;">Produto</th>
    <th style="width: 120px; background-color: #629972; color: #ffffff;">Vl. venda</th>
    <th style="width: 120px; background-color: #629972; color: #ffffff;">Vl. compra</th>
    <th style="width: 120px; background-color: #629972; color: #ffffff;">Dt. cadastro</th>

    @if(__countLocalAtivo() > 1)
      <th style="background-color: #629972; color: #ffffff;">Disponibilidade</th>
    @endif

    <th style="background-color: #629972; color: #ffffff;">Estoque</th>

    @if($tipo != '')
      <th style="background-color: #629972; color: #ffffff;">Qtd. vendida</th>
    @endif
  </tr>
</thead>

	<tbody>
		@foreach($data as $key => $item)
		<tr class="@if($key%2 == 0) pure-table-odd @endif">
	
			<td>{{ $item->nome }}</td>
			<td>{{ __moeda($item->valor_unitario) }}</td>
			<td>{{ __moeda($item->valor_compra) }}</td>
			<td>{{ __data_pt($item->created_at) }}</td>
			@if(__countLocalAtivo() > 1)
			<td>
				@foreach($item->locais as $l)
				@if($l->localizacao)
				<strong>{{ $l->localizacao->descricao }}</strong>
				@if(!$loop->last) | @endif
				@endif
				@endforeach
			</td>
			<td>
				@foreach($item->estoqueLocais as $e)
				@if($e->local)
				{{ $e->local->descricao }}:
				<strong class="text-success">
					@if($item->unidade == 'UN' || $item->unidade == 'UNID')
					{{ number_format($e->quantidade, 0) }}
					@else
					{{ number_format($e->quantidade, 3) }}
					@endif
				</strong>
				@endif
				@if(!$loop->last) | @endif
				@endforeach
			</td>

			@else
			<td>{{ $item->estoque ? number_format($item->estoque->quantidade, 2) : '0' }} - {{ $item->unidade }}</td>
			@endif


			@if($tipo != '')
			<td>{{ $item->quantidade_vendida }}</td>
			@endif
		</tr>
		@endforeach
	</tbody>
</table>