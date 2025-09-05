@extends('relatorios.default')
@section('content')


<p>Período: {{ \Carbon\Carbon::parse($start_date)->format('d/m/Y') }} a {{ \Carbon\Carbon::parse($end_date)->format('d/m/Y') }}</p>

<table class="table-sm table-borderless" style="border-bottom: 1px solid rgb(206, 206, 206); margin-bottom:10px; width: 100%;">
	<thead>
		<tr>
			<th>#</th>
			<th>Funcionário</th>
			<th>Cliente</th>
			<th>Data</th>
			<th>Início</th>
			<th>Término</th>
			<th>Status</th>
			<th>Prioridade</th>
			<th>Total</th>
		</tr>
	</thead>
	<tbody>
		@foreach($agendamentos as $key => $agendamento)
		<tr class="@if($key % 2 == 0) pure-table-odd @endif">
			<td>{{ $key + 1 }}</td>
            <td>{{ optional($agendamento->funcionario)->nome ?? 'N/A' }}</td> <!-- Garantindo que o funcionário exista -->
			<td>{{ optional($agendamento->cliente)->nome ?? 'N/A' }}</td> <!-- Garantindo que o cliente exista -->
			<td>{{ \Carbon\Carbon::parse($agendamento->data)->format('d/m/Y') }}</td>
			<td>{{ \Carbon\Carbon::parse($agendamento->inicio)->format('H:i') }}</td>
			<td>{{ \Carbon\Carbon::parse($agendamento->termino)->format('H:i') }}</td>
			<td>{{ $agendamento->status }}</td>
			<td class="{{ $agendamento->getPrioridade() }}">{{ ucfirst($agendamento->prioridade) }}</td>
			<td>{{ number_format($agendamento->total, 2, ',', '.') }}</td>
		</tr>
		@endforeach
	</tbody>
</table>

<h4>Total Agendado: R$ {{ number_format($agendamentos->sum('total'), 2, ',', '.') }}</h4>

@endsection
