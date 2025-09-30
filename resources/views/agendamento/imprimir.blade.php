@extends('layouts.app')

@section('css')
<style>
    @page { margin: 15mm; }
    body { font-size: 13px; }
    .header { text-align: center; margin-bottom: 20px; }
    .table { width: 100%; border-collapse: collapse; margin-top: 20px; }
    .table th, .table td { border: 1px solid #ccc; padding: 6px; text-align: left; }
    .table th { background: #f5f5f5; }
</style>
@endsection

@section('content')
<div class="header">
	<p>
    <h2>Relatório de Agendamento</h2>
    <p><b>Data:</b> {{ \Carbon\Carbon::parse($item->data)->format('d/m/Y') }}</p>
</div>

<p><b>Cliente:</b> {{ $item->cliente->razao_social }}</p>
<p><b>CPF/CNPJ:</b> {{ $item->cliente->cpf_cnpj }}</p>
<p><b>Telefone:</b> {{ $item->cliente->telefone }}</p>
<p><b>Atendente:</b> {{ $item->funcionario?->nome }}</p>
<p><b>Observações:</b> {{ $item->observacao }}</p>

<table class="table">
    <thead>
        <tr>
            <th>Serviço</th>
            <th>Quantidade</th>
            <th>Valor</th>
        </tr>
    </thead>
    <tbody>
        @foreach($item->itens as $i)
        <tr>
            <td>{{ $i->servico->nome }}</td>
            <td>{{ number_format($i->quantidade, 2, ',', '.') }}</td>
            <td>{{ __moeda($i->valor) }}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="2"><b>Total</b></td>
            <td><b>{{ __moeda($item->total) }}</b></td>
        </tr>
        <tr>
            <td colspan="2"><b>Desconto</b></td>
            <td><span class="text-danger">{{ __moeda($item->desconto) }}</span></td>
        </tr>
    </tfoot>
</table>

{{-- Script para disparar a impressão automática --}}
<script type="text/javascript">
    window.onload = function() {
        window.print();
        // Opcional: fechar a aba/janela depois que abrir a caixa de impressão
        // window.onafterprint = () => window.close();
    }
</script>
@endsection
