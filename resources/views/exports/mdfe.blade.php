<table>
    <thead>
        <tr>
            <th style = " width: 150px;background-color: #629972; color: #ffffff;">Emitente</th>
            <th style = " width: 75px;background-color: #629972; color: #ffffff;">Valor Total</th>
            <th style = " width: 150px;background-color: #629972; color: #ffffff;">Estado de Emissão</th>
            <th style = " width: 110px;background-color: #629972; color: #ffffff;">Chave</th>
            <th style = " width: 120px;background-color: #629972; color: #ffffff;">Data de Emissão</th>
            @if(__countLocalAtivo() > 1)
            <th style = " width: 100px;background-color: #629972; color: #ffffff;">Local</th>
            @endif
        </tr>
    </thead> 
    <tbody>
        @foreach($data as $key => $item)
        <tr class="@if($key%2 == 0) pure-table-odd @endif">
            <td>{{ $item->emitente ? $item->emitente->razao_social : '---' }}</td>
            <td>{{ __moeda($item->valor_total) }}</td>
            <td>{{ $item->estado_emissao }}</td>
            <td>{{ $item->chave }}</td>
            <td>{{ __data_pt($item->created_at) }}</td>
            @if(__countLocalAtivo() > 1)
            <td>{{ $item->localizacao ? $item->localizacao->descricao : '---' }}</td>
            @endif
        </tr>
        @endforeach
    </tbody>
</table>
<h4>Total: R$ {{ __moeda($data->sum('total')) }}</h4>