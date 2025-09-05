<table style="width: 100%; border-collapse: collapse;">
    <thead>
        <tr style="background-color: #f2f2f2;">
            <th style = " width: 150px;background-color: #629972; color: #ffffff;">Cliente</th>
            <th style = " width: 80px;background-color: #629972; color: #ffffff;">Valor Total</th>
            <th style = " width: 60px;background-color: #629972; color: #ffffff;">Taxa (%)</th>
            <th style = " width: 90px;background-color: #629972; color: #ffffff;">Valor da Taxa</th>
            <th style = " width: 100px;background-color: #629972; color: #ffffff;">Data</th>
            <th style = " width: 135px;background-color: #629972; color: #ffffff;">Forma de Pagamento</th>
            <th style = " width: 80px;background-color: #629972; color: #ffffff;">ID da Venda</th>
            <th style = " width: 100px;background-color: #629972; color: #ffffff;">Tipo</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $item)
        <tr>
            <td style="padding: 8px; border: 1px solid #ddd;">{{ $item['cliente'] }}</td>
            <td style="padding: 8px; border: 1px solid #ddd;">{{ __moeda($item['total']) }}</td>
            <td style="padding: 8px; border: 1px solid #ddd;">{{ number_format($item['taxa_perc'], 2) }}%</td>
            <td style="padding: 8px; border: 1px solid #ddd;">{{ __moeda($item['taxa']) }}</td>
            <td style="padding: 8px; border: 1px solid #ddd;">{{ $item['data'] }}</td>
            <td style="padding: 8px; border: 1px solid #ddd;">{{ $item['tipo_pagamento'] }}</td>
            <td style="padding: 8px; border: 1px solid #ddd;">{{ $item['venda_id'] }}</td>
            <td style="padding: 8px; border: 1px solid #ddd;">{{ $item['tipo'] }}</td>
        </tr>
        @endforeach
    </tbody>
</table>