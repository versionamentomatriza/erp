<table style="width: 100%; border-collapse: collapse;">
    <thead>
        <tr>
            <th style=" width: 70px; background-color: #629972; color: #ffffff;">Data</th>
            <th style="width: 100px; background-color: #629972; color: #ffffff;">Horário</th>
            <th style="width: 110px; background-color: #629972; color: #ffffff;">Cliente</th>
            <th style="width: 130px; background-color: #629972; color: #ffffff;">Funcionário</th> 
            <th style="width: 120px; background-color: #629972; color: #ffffff;">Serviços</th>
            <th style="width: 115px; background-color: #629972; color: #ffffff;">Valor Total</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $agendamento)
        <tr>
            <td style="padding: 8px; border: 1px solid #ddd;">{{ \Carbon\Carbon::parse($agendamento->data)->format('d/m/Y') }}</td>
            <td style="padding: 8px; border: 1px solid #ddd;">{{ \Carbon\Carbon::parse($agendamento->data)->format('H:i') }}</td>
            <td style="padding: 8px; border: 1px solid #ddd;">{{ $agendamento->cliente->razao_social ?? '-' }}</td>
            <td style="padding: 8px; border: 1px solid #ddd;">{{ $agendamento->funcionario->nome ?? '-' }}</td>
            <td style="padding: 8px; border: 1px solid #ddd;">
                @foreach($agendamento->itens as $item)
                    {{ $item->servico->nome ?? '---' }}@if(!$loop->last), @endif
                @endforeach
            </td>
            <td style="padding: 8px; border: 1px solid #ddd;">
                R$ {{ number_format($agendamento->itens->sum('valor'), 2, ',', '.') }}
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
