<!-- resources/views/exports/nfe.blade.php -->
<table>
    <thead>
        <tr>
            <b><th style ="width: 435px; background-color: #629972; color: #ffffff;">Cliente</th>
            <b><th style ="width: 54px;background-color: #629972; color: #ffffff;">Valor</th>
            <b><th style = "background-color: #629972; color: #ffffff;">Num. doc</th>
            <b><th style ="width: 320px ;background-color: #629972; color: #ffffff;">Chave</th>
            <b><th style ="width: 85px; background-color: #629972; color: #ffffff;">Estado</th>
            <b><th style ="width: 110px; background-color: #629972; color: #ffffff;">Data</th>
            <b><th style ="width: 90px; background-color: #629972; color: #ffffff;">Finalidade</th> 
            <b><th style ="width: 110px; background-color: #629972; color: #ffffff;">Tipo</th> 
            @if(__countLocalAtivo() > 1)
                <b><th style = "background-color: #629972; color: #ffffff;">Local</th>
            @endif
            <b><th style= "width: 125px; background-color: #629972; color: #ffffff;">Natureza Operação</th>
        </tr>
    </thead>  
    <tbody>
        @foreach($data as $key => $item)
            <tr>
                <td>
                    {{ $item->cliente ? $item->cliente->info : ($item->fornecedor->info ?? '') }}
                </td>
                <td>{{ __moeda($item->total) }}</td>
                <td>{{ $item->numero }}</td>
                <td>{{ $item->chave }}</td>
                <td>{{ strtoupper($item->estado) }}</td>
                <td>{{ __data_pt($item->data_emissao) }}</td>
                <td>{{ $item->getFinNFe() }}</td>
                <td>{{ $item->tpNF == 1 ? 'Saída' : 'Entrada' }}</td>
                @if(__countLocalAtivo() > 1)
                    <td>{{ $item->localizacao->descricao }}</td>
                @endif
                <td>{{ $item->natureza ? $item->natureza->descricao : '' }}</td>
            </tr>
        @endforeach
    </tbody>
</table> 

<h4>Total: R$ {{ __moeda($data->sum('total')) }}</h4>

