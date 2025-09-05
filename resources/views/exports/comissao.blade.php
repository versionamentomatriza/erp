<table class="table-sm table-borderless" style="border-bottom: 1px solid rgb(206, 206, 206); margin-bottom:10px; width: 100%;">
    <thead>
        <tr>
            <th style="width: 180px; background-color: #629972; color: #ffffff;">Funcionário</th>
            <th style="background-color: #629972; color: #ffffff;">Tipo</th>
            <th style="width: 100px; background-color: #629972; color: #ffffff;">Valor da Venda</th>
            <th style="width: 120px;background-color: #629972; color: #ffffff;">Valor da Comissão</th>
            <th style="width: 130px; background-color: #629972; color: #ffffff;">Data</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $key => $item)
        <tr class="@if($key % 2 == 0) pure-table-odd @endif">
            <td>{{ $item->funcionario->nome ?? '-' }}</td>
            <td>{{ $item->tabela === 'nfce' ? 'PDV' : 'Pedido' }}</td>
            <td>{{ __moeda($item->valor_venda) }}</td>
            <td>{{ __moeda($item->valor) }}</td>
            <td>{{ __data_pt($item->created_at) }}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="2"><strong>Total</strong></td>
            <td><strong>{{ __moeda($data->sum('valor_venda')) }}</strong></td>
            <td><strong>{{ __moeda($data->sum('valor')) }}</strong></td>
            <td></td>
        </tr>
    </tfoot>
</table>