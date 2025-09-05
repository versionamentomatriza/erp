<!DOCTYPE html>
<html>
<head>
    <title>Relatório de Produtos Vendidos</title>
    <style type="text/css">
        * {
            font-family: "Lucida Console", "Courier New", monospace;
        }

        .b-top {
            border-top: 1px solid #000;
        }

        .b-bottom {
            border-bottom: 1px solid #000;
        }

        td, th {
            font-size: 12px;
            padding: 4px;
            border: 1px solid #000;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 10px;
        }

        .section-title {
            margin-top: 30px;
            font-weight: bold;
            font-size: 14px;
            border-bottom: 2px solid #000;
        }

        .total {
            text-align: right;
            font-weight: bold;
        }

	
		.logoBanner img {
			max-width: 140px;
			height: auto;
			vertical-align: middle;
		}


        .page-break {
            page-break-before: always;
        }
    </style>
</head>

<header>
	<div class="headReport" style="display:flex; justify-content:  padding-top:1rem">
		<img style="margin-top: -75px;" src="{{'data:image/png;base64,' . base64_encode(file_get_contents(@public_path('logo.png')))}}" alt="Logo" class="mb-2" height="160px">

		<div class="row text-right">
			<div class="col-12" style="margin-top: -50px;">
				<small class="float-right" style="color:grey; font-size: 11px;">Emissão:
				{{ date('d/m/Y - H:i') }}</small><br>
				<small class="float-right" style="color:grey; font-size: 11px;">Caixa nº:
				{{ $item->id }}</small><br>
			</div>
		</div>

<p><p>
		<div class="row">
			<h4 style="text-align:center; margin-top: -50px;">Relatório Caixa de Produtos Vendidos</h4>
		</div>
		
	</div>
</header>
<body>



    <br>

    <table>
        <tr>
            <td>Razão Social: <strong>{{ $config->nome_fantasia }}</strong></td>
            <td>Documento: <strong>{{ str_replace(' ', '', $config->cpf_cnpj) }}</strong></td>
        </tr>
        <tr>
            <td>Data de Abertura: <strong>{{ \Carbon\Carbon::parse($item->created_at)->format('d/m/Y H:i') }}</strong></td>
            <td>Fechamento: <strong>{{ \Carbon\Carbon::parse($item->updated_at)->format('d/m/Y H:i') }}</strong></td>
        </tr>
    </table>

    <div class="section-title">Produtos Vendidos:</div>

    @php
        $totalGeral = 0;
        $clientes = collect($produtos)->groupBy(function($p) {
            return $p->nf->cliente->razao_social ?? 'Não identificado';
        });
    @endphp

    @foreach($clientes as $clienteNome => $notas)
        <h4>Cliente: {{ $clienteNome }}</h4>

        @php
            $notasAgrupadas = $notas->groupBy(function($p) {
                return $p->nf->numero ?? 'Sem número';
            });
        @endphp

        @foreach($notasAgrupadas as $numeroNota => $itens)
            <p><strong>Nota nº {{ $numeroNota }}</strong></p>
            <table>
                <thead>
                    <tr>
                        <th>Produto</th>
                        <th>Quantidade</th>
                        <th>Valor Unitário</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @php $subtotalNota = 0; @endphp
                    @foreach ($itens as $produto)
                        @php
                            $descricao = method_exists($produto, 'descricao') ? $produto->descricao() : ($produto->nome ?? '---');
                            $quantidade = $produto->quantidade ?? 1;
                            $unitario = $produto->valor_unitario ?? 0;
                            $subtotal = $quantidade * $unitario;
                            $subtotalNota += $subtotal;
                            $totalGeral += $subtotal;
                        @endphp
                        <tr>
                            <td>{{ $descricao }}</td>
                            <td>{{ number_format($quantidade, 2, ',', '.') }}</td>
                            <td>R$ {{ number_format($unitario, 2, ',', '.') }}</td>
                            <td>R$ {{ number_format($subtotal, 2, ',', '.') }}</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td colspan="3" class="total">Subtotal da Nota</td>
                        <td class="total">R$ {{ number_format($subtotalNota, 2, ',', '.') }}</td>
                    </tr>
                </tbody>
            </table>
        @endforeach
        <br>
    @endforeach

    <table>
        <tr>
            <td class="total">TOTAL GERAL DE PRODUTOS: R$ {{ number_format($totalGeral, 2, ',', '.') }}</td>
        </tr>
    </table>

    <br><br>
    <table>
        <tr>
            <td style="width: 300px;">___________________________________________</td>
        </tr>
        <tr>
            <td>{{ $usuario->name }} - {{ date('d/m/Y H:i') }}</td>
        </tr>
    </table>

</body>
</html>
