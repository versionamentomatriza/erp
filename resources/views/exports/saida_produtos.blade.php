<table style="width: 100%; border-collapse: collapse;">
    <thead>
        <tr>
            <th style="width: 200px;background-color: #629972; color: #ffffff; ">Produto</th>
            <th style="width: 120px;background-color: #629972; color: #ffffff; ">Total Baixado</th>
            <th style="width: 120px;background-color: #629972; color: #ffffff; ">Unidade</th>
            <th style="width: 120px;background-color: #629972; color: #ffffff; ">% Sobre Total</th>
        </tr>
    </thead>
    <tbody> 
        @foreach($data as $produto)
    @php
        $quantidade = $produto->movimentacoes->sum('quantidade');
        $percentual = $totalBaixado > 0 ? ($quantidade / $totalBaixado) * 100 : 0;
    @endphp
    <tr>
        <td style="padding: 8px; border: 1px solid #ddd;">{{ $produto->nome }}</td>
        <td style="padding: 8px; border: 1px solid #ddd;">{{ number_format($quantidade, 2) }}</td>
        <td style="padding: 8px; border: 1px solid #ddd;">{{ $produto->unidade }}</td>
        <td style="padding: 8px; border: 1px solid #ddd;">{{ number_format($percentual, 2) }}%</td>
    </tr>
    @endforeach

    </tbody>
</table>