<table>
    <thead>
        <tr>
            <th style = " width: 240px;background-color: #629972; color: #ffffff;">Cliente</th>
            <th style = " width: 115px;background-color: #629972; color: #ffffff;">Data</th>
            <th style = " width: 100px;background-color: #629972; color: #ffffff;">Valor de Venda</th>
            <th style = " width: 100px;background-color: #629972; color: #ffffff;">Valor de Custo</th>
            <th style = " width: 70px;background-color: #629972; color: #ffffff;">Lucro</th>
            <th style = " width: 75px;background-color: #629972; color: #ffffff;">Localização</th>
        </tr>
    </thead>
    <tbody>
        @php $totalLucro = 0; @endphp
        @foreach($data as $key => $item)
        @php
            $lucro = $item['valor_venda'] - $item['valor_custo'];
            $totalLucro += $lucro;
        @endphp
        <tr class="@if($key % 2 == 0) pure-table-odd @endif">
            <td>{{ $item['cliente'] }}</td>
            <td>{{ $item['data'] }}</td>
            <td>{{ __moeda($item['valor_venda']) }}</td>
            <td>{{ __moeda($item['valor_custo']) }}</td>
            <td>{{ __moeda($lucro) }}</td>
            <td>{{ $item['localizacao'] ? $item['localizacao']->descricao : '--' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>