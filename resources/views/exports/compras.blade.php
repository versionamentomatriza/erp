<table>
    <thead>
        <tr>
            <th style="width: 100px;background-color: #629972; color: #ffffff;">ID</th>
            <th style="width: 340px;background-color: #629972; color: #ffffff;">Fornecedor</th>
            <th style="width: 200px;background-color: #629972; color: #ffffff;">Centro de Custo</th>
            <th style="width: 150px;background-color: #629972; color: #ffffff;">Local</th>
            <th style="width: 150px;background-color: #629972; color: #ffffff;">Estado</th>
            <th style="width: 180px;background-color: #629972; color: #ffffff;">Data</th>
            <th style="width: 150px;background-color: #629972; color: #ffffff;">Valor Total</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $item)
        <tr>
            <td>{{ $item->id }}</td>
            <td>{{ $item->fornecedor ? $item->fornecedor->razao_social : '--' }}</td>
            <td>{{ $item->centroCusto ? $item->centroCusto->descricao : 'N/A' }}</td>
            <td>{{ $item->localizacao ? $item->localizacao->descricao : '--' }}</td>
            <td>{{ $item->estado }}</td>
            <td>{{ __data_pt($item->created_at) }}</td>
            <td>{{ __moeda($item->total) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
<h4>Total: R$ {{ __moeda($data->sum('total')) }}</h4>