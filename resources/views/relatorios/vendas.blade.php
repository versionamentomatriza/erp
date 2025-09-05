@extends('relatorios.default')
@section('content')

<table class="table-sm table-borderless" style="border-bottom: 1px solid rgb(206, 206, 206); margin-bottom:10px; width: 100%;">
    <thead>
        <tr>
            <th>ID</th>
            <th>Cliente</th>
            <th>Data</th>
            <th>Valor</th>
            <th>Cidade</th>
            <th>Estado</th>
            <th>Centro de Custo</th> <!-- Nova coluna para Centro de Custo -->
        </tr>
    </thead>
    <tbody>
        @foreach($data as $key => $item)
        <tr class="@if($key % 2 == 0) pure-table-odd @endif">
            <td>{{ $item['id'] }}</td>
            <td>{{ $item['cliente'] }}</td>
            <td>{{ __data_pt($item['data']) }}</td>
            <td>{{ __moeda($item['total']) }}</td>
            <td>{{ $item['cidade'] }}</td>
            <td>{{ $item['estado'] }}</td>
            <td>{{ $item['centro_custo'] }}</td> <!-- Valor do Centro de Custo -->
        </tr>
        @endforeach
    </tbody>
</table>
 
<h4>Total de Vendas: R$ {{ __moeda(collect($data)->sum('total')) }}</h4>
@endsection
