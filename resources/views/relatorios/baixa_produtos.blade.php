@extends('relatorios.default', ['title' => 'Relatório de Baixa de Produtos'])   
@section('content')




<p><b>Período: {{ \Carbon\Carbon::parse($start_date)->format('d/m/Y') }} a {{ \Carbon\Carbon::parse($end_date)->format('d/m/Y') }} </b></p>

<table class="table-sm table-borderless" style="border-bottom: 1px solid rgb(206, 206, 206); margin-bottom:10px; width: 100%;">
    <thead>
        <tr>
            <th>#</th>
            <th>Produto</th>
            <th>Qtd. Baixada</th>
            <th>% de Vendas</th>
        </tr>
    </thead>
    <tbody>
        @foreach($produtos->sortByDesc(function ($produto) {
            return $produto->movimentacoes->sum('quantidade');
        }) as $key => $produto)
        <tr class="@if($key % 2 == 0) pure-table-odd @endif">
            <td>{{ $key + 1 }}</td>
            <td>{{ $produto->nome }}</td>
            <td>{{ number_format($produto->movimentacoes->sum('quantidade'), 0, ',', '.') }}</td>
            <td>
                @php
                    $percentual = ($produto->movimentacoes->sum('quantidade') / $totalBaixado) * 100;
                @endphp
                {{ number_format($percentual, 2) }}%
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
<h4 >Total de produtos baixados: {{ number_format($totalBaixado, 0, ',', '.') }}</h4>

@endsection

