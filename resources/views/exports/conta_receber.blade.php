<table class="table-sm table-borderless" style="border-bottom: 1px solid rgb(206, 206, 206); margin-bottom:10px; width: 100%;">
    <thead>
        <tr>
            <th style="width: 160px; background-color: #629972; color: #ffffff;">Cliente</th>
            <th style="width: 90px;background-color: #629972; color: #ffffff;">Valor Integral</th>
            <th style="width: 100px;background-color: #629972; color: #ffffff;">Valor Recebido</th>
            <th style="width: 120px;background-color: #629972; color: #ffffff;">Status</th>
            <th style="width: 130px; background-color: #629972; color: #ffffff;">Data de Vencimento</th>
            <th style="width: 135px; background-color: #629972; color: #ffffff;">Forma de Pagamento</th>
            <th style="width: 180px; background-color: #629972; color: #ffffff;">Conta</th>
            <th style="width: 110px; background-color: #629972; color: #ffffff;">Categoria</th>
            <th style="width: 110px; background-color: #629972; color: #ffffff;">Data de Criação</th>
            @if(__countLocalAtivo() > 1)
            <th style="background-color: #629972; color: #ffffff;">Local</th>
            @endif 
        </tr>
    </thead> 
    <tbody> 
        @foreach($data as $key => $item)
        <tr class="@if($key % 2 == 0) pure-table-odd @endif">
            <td>{{ $item->cliente->razao_social ?? '--' }}</td>
            <td>{{ __moeda($item->valor_integral) }}</td>
            <td>{{ __moeda($item->valor_recebido ?? 0) }}</td>
            <td>{{ __data_pt($item->data_vencimento) }}</td>
            <td>{{ $item->status == 1 ? 'Quitado' : 'Pendente' }}</td>
            <td>{{ $item->forma_pagamento->descricao ?? '--' }}</td>
            <td>{{ $item->conta->descricao ?? '--' }}</td>
            <td>{{ $item->categoria->descricao ?? '--' }}</td>
            <td>{{ __data_pt($item->created_at) }}</td>
            @if(__countLocalAtivo() > 1)
            <td>{{ $item->local->descricao ?? '--' }}</td>
            @endif
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
          
            <td><strong>Total</strong></td>
            <td><strong>{{ __moeda($data->sum('valor_integral')) }}</strong></td>
            <td><strong>{{ __moeda($data->sum('valor_recebido')) }}</strong></td>
            <td colspan="6"></td>
        </tr>
    </tfoot>
</table>


