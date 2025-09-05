<html>

<head>

    <style type="text/css">
        @page {
            margin: 0cm 0cm;
        }

        /** Define now the real margins of every page in the PDF **/
        body {
            margin-top: 2cm;
            margin-left: 1cm;
            margin-right: 1cm;
            margin-bottom: 2cm;
        }


        /** Define the header rules **/
        header {
            position: relative;
            margin-top: 0px;
            margin-left: 40px;
            margin-right: 40px;
            margin-bottom: 25px;
            height: 20px;
        }

        .banner {
            text-align: center;
            display: flex;
            align-items: flex-start;
        }

        td {
            text-align: center;
        }

        p {
            font-size: 12px;
            margin-top: 0px;
            margin-bottom: 2px;
        }

        .pure-table-odd {
            background: #EBEBEB;
        }

        .logoBanner img {
            float: left;
            max-width: 70px;
        }

        .banner h1 {
            position: absolute;
            margin-top: 0;
        }

        .banner hr {
            margin-top: 29px;
            margin-left: 120px;
        }

        .date {
            float: right;
        }

        .provider {
            text-align: left;
            margin-top: 5px;
            margin-bottom: 10px;
        }


        .client {
            margin-bottom: 0.6rem;
        }

        footer {
            position: fixed;
            bottom: 1.9cm;
            left: 0.4cm;
            right: 0cm;
            height: 0cm;
        }

        img {
            max-width: 100px;
            height: auto;
        }


        table {
            font-size: 0.8rem;
            margin: 0;
        }

        table thead {
            border-bottom: 1px solid rgb(206, 206, 206);
            border-top: 1px solid rgb(206, 206, 206);
        }

        .caption {
            /* Make the caption a block so it occupies its own line. */
            display: block;
        }

        .row {
            display: -webkit-box;
            display: -ms-flexbox;
            display: flex;
            -ms-flex-wrap: wrap;
            flex-wrap: wrap;
            margin-right: -15px;
            margin-left: -15px;
        }

        .col-1,
        .col-2,
        .col-3,
        .col-4,
        .col-5,
        .col-6,
        .col-7,
        .col-8,
        .col-9,
        .col-10,
        .col-11,
        .col-12,
        .col,
        .col-auto,
        .col-sm-1,
        .col-sm-2,
        .col-sm-3,
        .col-sm-4,
        .col-sm-5,
        .col-sm-6,
        .col-sm-7,
        .col-sm-8,
        .col-sm-9,
        .col-sm-10,
        .col-sm-11,
        .col-sm-12,
        .col-sm,
        .col-sm-auto,
        .col-md-1,
        .col-md-2,
        .col-md-3,
        .col-md-4,
        .col-md-5,
        .col-md-6,
        .col-md-7,
        .col-md-8,
        .col-md-9,
        .col-md-10,
        .col-md-11,
        .col-md-12,
        .col-md,
        .col-md-auto,
        .col-lg-1,
        .col-lg-2,
        .col-lg-3,
        .col-lg-4,
        .col-lg-5,
        .col-lg-6,
        .col-lg-7,
        .col-lg-8,
        .col-lg-9,
        .col-lg-10,
        .col-lg-11,
        .col-lg-12,
        .col-lg,
        .col-lg-auto,
        .col-xl-1,
        .col-xl-2,
        .col-xl-3,
        .col-xl-4,
        .col-xl-5,
        .col-xl-6,
        .col-xl-7,
        .col-xl-8,
        .col-xl-9,
        .col-xl-10,
        .col-xl-11,
        .col-xl-12,
        .col-xl,
        .col-xl-auto {
            position: relative;
            width: 100%;
            min-height: 1px;
            padding-right: 15px;
            padding-left: 15px;
        }

        .col {
            -ms-flex-preferred-size: 0;
            flex-basis: 0;
            -webkit-box-flex: 1;
            -ms-flex-positive: 1;
            flex-grow: 1;
            max-width: 100%;
        }

        .col-auto {
            -webkit-box-flex: 0;
            -ms-flex: 0 0 auto;
            flex: 0 0 auto;
            width: auto;
            max-width: none;
        }

        .col-1 {
            -webkit-box-flex: 0;
            -ms-flex: 0 0 8.333333%;
            flex: 0 0 8.333333%;
            max-width: 8.333333%;
        }

        .col-2 {
            -webkit-box-flex: 0;
            -ms-flex: 0 0 16.666667%;
            flex: 0 0 16.666667%;
            max-width: 16.666667%;
        }

        .col-3 {
            -webkit-box-flex: 0;
            -ms-flex: 0 0 25%;
            flex: 0 0 25%;
            max-width: 25%;
        }

        .col-4 {
            -webkit-box-flex: 0;
            -ms-flex: 0 0 33.333333%;
            flex: 0 0 33.333333%;
            max-width: 33.333333%;
        }

        .col-5 {
            -webkit-box-flex: 0;
            -ms-flex: 0 0 41.666667%;
            flex: 0 0 41.666667%;
            max-width: 41.666667%;
        }

        .col-6 {
            -webkit-box-flex: 0;
            -ms-flex: 0 0 50%;
            flex: 0 0 50%;
            max-width: 50%;
        }

        .col-7 {
            -webkit-box-flex: 0;
            -ms-flex: 0 0 58.333333%;
            flex: 0 0 58.333333%;
            max-width: 58.333333%;
        }

        .col-8 {
            -webkit-box-flex: 0;
            -ms-flex: 0 0 66.666667%;
            flex: 0 0 66.666667%;
            max-width: 66.666667%;
        }

        .col-9 {
            -webkit-box-flex: 0;
            -ms-flex: 0 0 75%;
            flex: 0 0 75%;
            max-width: 75%;
        }

        .col-10 {
            -webkit-box-flex: 0;
            -ms-flex: 0 0 83.333333%;
            flex: 0 0 83.333333%;
            max-width: 83.333333%;
        }

        .col-11 {
            -webkit-box-flex: 0;
            -ms-flex: 0 0 91.666667%;
            flex: 0 0 91.666667%;
            max-width: 91.666667%;
        }

        .col-12 {
            -webkit-box-flex: 0;
            -ms-flex: 0 0 100%;
            flex: 0 0 100%;
            max-width: 100%;
        }

        .text-justify {
            text-align: justify !important;
        }

        .text-nowrap {
            white-space: nowrap !important;
        }

        .text-truncate {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .text-left {
            text-align: left !important;
        }

        .text-right {
            text-align: right !important;
        }

        .text-center {
            text-align: center !important;
        }

        .w-25 {
            width: 25% !important;
        }

        .w-50 {
            width: 50% !important;
        }

        .w-75 {
            width: 75% !important;
        }

        .w-100 {
            width: 100% !important;
        }

        .h-25 {
            height: 25% !important;
        }

        .h-50 {
            height: 50% !important;
        }

        .h-75 {
            height: 75% !important;
        }

        .h-100 {
            height: 100% !important;
        }

        .mw-100 {
            max-width: 100% !important;
        }

        .mh-100 {
            max-height: 100% !important;
        }

        .m-0 {
            margin: 0 !important;
        }

        .mt-0,
        .my-0 {
            margin-top: 0 !important;
        }

        .mr-0,
        .mx-0 {
            margin-right: 0 !important;
        }

        .mb-0,
        .my-0 {
            margin-bottom: 0 !important;
        }

        .ml-0,
        .mx-0 {
            margin-left: 0 !important;
        }

        .m-1 {
            margin: 0.25rem !important;
        }

        .mt-1,
        .my-1 {
            margin-top: 0.25rem !important;
        }

        .mr-1,
        .mx-1 {
            margin-right: 0.25rem !important;
        }

        .mb-1,
        .my-1 {
            margin-bottom: 0.25rem !important;
        }

        .ml-1,
        .mx-1 {
            margin-left: 0.25rem !important;
        }

        .m-2 {
            margin: 0.5rem !important;
        }

        .mt-2,
        .my-2 {
            margin-top: 0.5rem !important;
        }

        .mr-2,
        .mx-2 {
            margin-right: 0.5rem !important;
        }

        .mb-2,
        .my-2 {
            margin-bottom: 0.5rem !important;
        }

        .ml-2,
        .mx-2 {
            margin-left: 0.5rem !important;
        }

        .m-3 {
            margin: 1rem !important;
        }

        .mt-3,
        .my-3 {
            margin-top: 1rem !important;
        }

        .mr-3,
        .mx-3 {
            margin-right: 1rem !important;
        }

        .mb-3,
        .my-3 {
            margin-bottom: 1rem !important;
        }

        .ml-3,
        .mx-3 {
            margin-left: 1rem !important;
        }

        .m-4 {
            margin: 1.5rem !important;
        }

        .mt-4,
        .my-4 {
            margin-top: 1.5rem !important;
        }

        .mr-4,
        .mx-4 {
            margin-right: 1.5rem !important;
        }

        .mb-4,
        .my-4 {
            margin-bottom: 1.5rem !important;
        }

        .ml-4,
        .mx-4 {
            margin-left: 1.5rem !important;
        }

        .m-5 {
            margin: 3rem !important;
        }

        .mt-5,
        .my-5 {
            margin-top: 3rem !important;
        }

        .mr-5,
        .mx-5 {
            margin-right: 3rem !important;
        }

        .mb-5,
        .my-5 {
            margin-bottom: 3rem !important;
        }

        .ml-5,
        .mx-5 {
            margin-left: 3rem !important;
        }

        .p-0 {
            padding: 0 !important;
        }

        .pt-0,
        .py-0 {
            padding-top: 0 !important;
        }

        .pr-0,
        .px-0 {
            padding-right: 0 !important;
        }

        .pb-0,
        .py-0 {
            padding-bottom: 0 !important;
        }

        .pl-0,
        .px-0 {
            padding-left: 0 !important;
        }

        .p-1 {
            padding: 0.25rem !important;
        }

        .pt-1,
        .py-1 {
            padding-top: 0.25rem !important;
        }

        .pr-1,
        .px-1 {
            padding-right: 0.25rem !important;
        }

        .pb-1,
        .py-1 {
            padding-bottom: 0.25rem !important;
        }

        .pl-1,
        .px-1 {
            padding-left: 0.25rem !important;
        }

        .p-2 {
            padding: 0.5rem !important;
        }

        .pt-2,
        .py-2 {
            padding-top: 0.5rem !important;
        }

        .pr-2,
        .px-2 {
            padding-right: 0.5rem !important;
        }

        .pb-2,
        .py-2 {
            padding-bottom: 0.5rem !important;
        }

        .pl-2,
        .px-2 {
            padding-left: 0.5rem !important;
        }

        .p-3 {
            padding: 1rem !important;
        }

        .pt-3,
        .py-3 {
            padding-top: 1rem !important;
        }

        .pr-3,
        .px-3 {
            padding-right: 1rem !important;
        }

        .pb-3,
        .py-3 {
            padding-bottom: 1rem !important;
        }

        .pl-3,
        .px-3 {
            padding-left: 1rem !important;
        }

        .p-4 {
            padding: 1.5rem !important;
        }

        .pt-4,
        .py-4 {
            padding-top: 1.5rem !important;
        }

        .pr-4,
        .px-4 {
            padding-right: 1.5rem !important;
        }

        .pb-4,
        .py-4 {
            padding-bottom: 1.5rem !important;
        }

        .pl-4,
        .px-4 {
            padding-left: 1.5rem !important;
        }

        .p-5 {
            padding: 3rem !important;
        }

        .pt-5,
        .py-5 {
            padding-top: 3rem !important;
        }

        .pr-5,
        .px-5 {
            padding-right: 3rem !important;
        }

        .pb-5,
        .py-5 {
            padding-bottom: 3rem !important;
        }

        .pl-5,
        .px-5 {
            padding-left: 3rem !important;
        }

        .m-auto {
            margin: auto !important;
        }

        .mt-auto,
        .my-auto {
            margin-top: auto !important;
        }

        .mr-auto,
        .mx-auto {
            margin-right: auto !important;
        }

        .mb-auto,
        .my-auto {
            margin-bottom: auto !important;
        }

        .ml-auto,
        .mx-auto {
            margin-left: auto !important;
        }

        * {
            font-family: "Lucida Console", "Courier New", monospace;
        }

    </style>
</head>
<header>
    <div class="headReport" style="display:flex; justify-content:  padding-top:1rem">

        @if($config->logo != null)
        <img style="margin-top: -65px; height: 80px;" src="{{'data:image/png;base64,' . base64_encode(file_get_contents(@public_path('/uploads/logos/'. $config->logo)))}}" alt="Logo" class="mb-2">
        @else
        <img style="margin-top: -75px;" src="{{'data:image/png;base64,' . base64_encode(file_get_contents(@public_path('logo.png')))}}" alt="Logo" class="mb-2">
        @endif
        
        <div class="row text-right">
            <div class="col-12" style="margin-top: -50px;">
                <small class="float-right" style="color:grey; font-size: 11px;">Emissão:
                {{ date('d/m/Y - H:i') }}</small><br>
            </div>
        </div>

        <div class="row">

            <h4 style="text-align:center; margin-top: -50px;">Reserva #{{ $item->numero_sequencial }}</h4>
            
        </div>

    </div>
</header>
<body>
    <table>
        <tr>
            <td class="text-left" style="width: 700px;">
                <strong>Dados do prestador</strong>
            </td>
        </tr>
    </table>
    <table>
        <tr>
            <td class="b-top text-left" style="width: 450px;">
                Razão social: <strong>{{$config->nome}}</strong>
            </td>
            <td class="b-top" style="width: 247px;">
                Documento: <strong>{{ __setMask($config->cpf_cnpj) }}</strong>
            </td>
        </tr>
    </table>
    <table>
        <tr>
            <td class="b-top text-left" style="width: 700px;">
                Endereço: <strong>{{$config->rua}}, {{$config->numero}} - {{$config->bairro}} - {{$config->cidade->nome}} ({{$config->cidade->uf}})</strong>
            </td>
        </tr>
    </table>
    <table>
        <tr>
            <td class="b-top b-bottom text-left" style="width: 300px;">
                Complemento: <strong>{{$config->complemento}}</strong>
            </td>
            <td class="b-top b-bottom text-left" style="width: 200px;">
                CEP: <strong>{{$config->cep}}</strong>
            </td>
            <td class="b-top b-bottom text-left" style="width: 200px;">
                Telefone: <strong>{{ $config->celular }}</strong>
            </td>
        </tr>
    </table>
    <table>
        <tr>
            <td class="b-bottom text-left" style="width: 700px;">
                Email: <strong>{{$config->email}}</strong>
            </td>

        </tr>
    </table>
    <br>


    <table>
        <tr>
            <td class="text-left" style="width: 700px;">
                <strong>Dados do contratante</strong>
            </td>
        </tr>
    </table>
    <table>
        <tr>
            <td class="b-top text-left" style="width: 450px;">
                Nome: <strong>{{$item->cliente->razao_social}}</strong>
            </td>
            <td class="b-top" style="width: 247px;">
                CPF/CNPJ: <strong>{{$item->cliente->cpf_cnpj}}</strong>
            </td>
        </tr>
    </table>
    <table>
        <tr>
            <td class="b-top text-left" style="width: 500px;">
                Endereço: <strong>{{$item->cliente->rua}}, {{$item->cliente->numero}} - {{$item->cliente->bairro}} - 
                    @if($item->cliente->cidade)
                    {{$item->cliente->cidade->nome}} ({{$item->cliente->cidade->uf}})
                    @endif
                </strong>
            </td>

            <td class="b-top text-left" style="width: 200px;">
                CEP: <strong>{{$item->cliente->cep}}</strong>
            </td>
        </tr>
    </table>

    <table>
        <tr>
            <td class="b-top text-left" style="width: 300px;">
                Complemento: <strong>{{$item->cliente->complemento }}</strong>
            </td>

            <td class="b-top text-left" style="width: 200px;">
                Telefone: <strong>{{$item->cliente->telefone}}</strong>
            </td>
            
        </tr>
    </table>

    <table>
        <tr>
            <td class="b-top text-left" style="width: 700px;">
                Email: <strong>{{$item->cliente->email}}</strong>
            </td>

        </tr>
    </table>


    <table>
        <tr>
            <td class="b-top text-left" style="width: 350px;">
                Nº Doc: <strong>{{$item->numero_sequencial}}</strong>
            </td>
            <td class="b-top" style="width: 347px;">

            </td>
        </tr>
    </table>

    <table>
        <tr>
            <td class="b-top b-bottom" style="width: 700px; height: 50px;">
                <strong>PRODUTOS CONSUMIDOS</strong>
            </td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <td class="" style="width: 400px;">
                    Produto
                </td>
                
                <td class="" style="width: 100px;">
                    Qtd.
                </td>
                <td class="" style="width: 100px;">
                    Vl Unit.
                </td>
                <td class="" style="width: 100px;">
                    Subtotal
                </td>
            </tr>
        </thead>


        <tbody>
            @foreach($item->consumoProdutos as $i)
            <tr>

                <th class="b-top">
                    {{ $i->produto->nome }}
                </th>
                <th class="b-top">
                    {{ __moeda($i->quantidade) }}
                </th>
                <th class="b-top">{{ __moeda($i->valor_unitario) }}</th>
                <th class="b-top">{{ __moeda($i->sub_total) }}</th>

            </tr>
            

            @endforeach
        </tbody>
    </table>
    <br>
    <table>
        <tr>
            <td class="b-top b-bottom" style="width: 350px;">
                <center><strong>Valor de produtos:
                    R$ {{ __moeda($item->consumoProdutos->sum('sub_total')) }}
                </strong></center>
            </td>
        </tr>
    </table>

    <hr>
    <table>
        <tr>
            <td class="b-top b-bottom" style="width: 700px; height: 50px;">
                <strong>SERVIÇOS</strong>
            </td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <td class="" style="width: 400px;">
                    Serviço
                </td>
                
                <td class="" style="width: 100px;">
                    Qtd.
                </td>
                <td class="" style="width: 100px;">
                    Vl Unit.
                </td>
                <td class="" style="width: 100px;">
                    Subtotal
                </td>
            </tr>
        </thead>


        <tbody>
            @foreach($item->consumoServicos as $i)
            <tr>

                <th class="b-top">
                    {{ $i->servico->nome }}
                </th>
                <th class="b-top">
                    {{ __moeda($i->quantidade) }}
                </th>
                <th class="b-top">{{ __moeda($i->valor_unitario) }}</th>
                <th class="b-top">{{ __moeda($i->sub_total) }}</th>

            </tr>
            

            @endforeach
        </tbody>
    </table>
    <br>

    <table>
        <tr>
            <td class="b-top b-bottom" style="width: 350px;">
                <center><strong>Valor de serviços extras:
                    R$ {{ __moeda($item->consumoServicos->sum('sub_total')) }}
                </strong></center>
            </td>
        </tr>
    </table>

    @if(sizeof($item->fatura) > 0)

    <hr>

    <table>
        <tr>
            <td class="b-bottom" style="width: 700px; height: 50px;">
                <strong>FATURA</strong>
            </td>
        </tr>
    </table>
    <table>
        <thead>
            <tr>
                <td class="" style="width: 200px;">
                    Tipo de pagamento
                </td>
                <td class="b-bottom" style="width: 180px;">
                    Vencimento
                </td>
                <td class="b-bottom" style="width: 180px;">
                    Valor
                </td>
            </tr>
        </thead>

        <tbody>
            @foreach($item->fatura as $key => $d)
            <tr>
                <td class="b-top">
                    <strong>{{ App\Models\FaturaReserva::getTipoPagamento($d->tipo_pagamento) }}</strong>
                </td>
                <td class="b-top">
                    <strong>{{ __data_pt($d->data_vencimento, 0) }}</strong>
                </td>
                <td class="b-top">
                    <strong>{{ __moeda($d->valor) }}</strong>
                </td>
            </tr>
            @endforeach
        </tbody>
        <tr>
            <td></td>
            <td>Soma</td>
            <td>{{ __moeda($item->fatura->sum('valor')) }}</td>
        </tr>
    </table>

    <hr>

    @endif
    <br>
    <table>
        <tr>
            <td class="text-left" style="width: 300px;">
                Data de registro: <strong>{{ __data_pt($item->created_at) }}</strong>
            </td>
            
            <td class="text-left" style="width: 400px;">
                Data de checkin/checkout: <strong>{{ __data_pt($item->data_checkin, 0) }} - {{ __data_pt($item->data_checkout, 0) }}</strong>
            </td>
            
        </tr>
    </table>

    <table>
        <tr>
            <td class="text-left" style="width: 233px;">
                Desconto (-):
                <strong>
                    R$ {{__moeda($item->desconto)}}
                </strong>
            </td>

            <td class="text-left" style="width: 233px;">
                Acrescimo (+):
                <strong>
                    R$ {{__moeda($item->valor_outros)}}
                </strong>
            </td>

            <td class="text-left" style="width: 233px;">
                Estádia (+):
                <strong>
                    R$ {{__moeda($item->valor_estadia)}}
                </strong>
            </td>
        </tr>
        <tr>
            <td class="text-left" style="width: 233px;">
                Valor total:
                <strong>
                    R$ {{ __moeda($item->valor_total)}}
                </strong>
            </td>

            <td class="text-left" colspan="2">
                Acomodação:
                <strong>
                    {{ $item->acomodacao->info }}
                </strong>
            </td>
        </tr>
    </table>

    @if($item->observacao != "")
    <table>
        <tr>
            <td class="" style="width: 700px;">
                <span>Observação:
                    <strong> {{ $item->observacao }} </strong>
                </span>
            </td>
        </tr>
    </table>
    @endif

    <br><br><br>
    <table>
        <tr>
            <td class="" style="width: 350px;">
                <strong>
                    ________________________________________
                </strong><br>
                <span style="font-size: 11px;">{{$config->nome}}</span>

            </td>

            <td class="" style="width: 350px;">
                <strong>
                    ________________________________________
                </strong><br>

                <span style="font-size: 11px;">{{ $item->cliente->razao_social }}</span>

            </td>
        </tr>
    </table>



</body>
<footer id="footer_imagem">
    <table style="width: 100%; border-top: 1px solid #999;">
        <tbody>
            <tr>
                <td class="text-left ml-3 mb-3">
                    {{ env('SITE_SUPORTE') }}
                </td>
                <td class="text-right">

                    <img src="{{'data:image/png;base64,' . base64_encode(file_get_contents(@public_path('logo.png')))}}" alt="Logo" class="mr-3">
                </td>
            </tr>
        </tbody>
    </table>
</footer>
</html>
