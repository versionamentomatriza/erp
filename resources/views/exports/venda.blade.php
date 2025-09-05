<table class="table-sm table-borderless" style="border-bottom: 1px solid rgb(206, 206, 206); margin-bottom:10px; width: 100%;">
    <thead>
        <tr>
            <th style="width: 80px;background-color: #629972; color: #ffffff;">ID</th>
            <th style="width: 240px;background-color: #629972; color: #ffffff;">Cliente</th>
            <th style="width: 150px;background-color: #629972; color: #ffffff;">Cidade</th>
            <th style="width: 100px;background-color: #629972; color: #ffffff;">Estado</th>
            <th style="width: 150px;background-color: #629972; color: #ffffff;">Centro de Custo</th>
            <th style="width: 140px;background-color: #629972; color: #ffffff;">Data</th>
            <th style="width: 130px;background-color: #629972; color: #ffffff;">Total</th>
        </tr> 
    </thead>
    <tbody>
        @foreach($data as $key => $item)
        <tr class="@if($key % 2 == 0) pure-table-odd @endif">
            <td>{{ $item['id'] }}</td>
            <td>{{ $item['cliente'] }}</td>
            <td>{{ $item['cidade'] }}</td>
            <td>{{ strtoupper($item['estado']) }}</td>
            <td>{{ $item['centro_custo'] }}</td>
            <td>{{ __data_pt($item['data']) }}</td>
            <td>{{ __moeda($item['total']) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
<h4>Total: R$ {{ __moeda(collect($data)->sum('total')) }}</h4>
