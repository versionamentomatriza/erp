@extends('layouts.app', ['title' => 'Conciliação Bancária'])

@section('title', 'Conciliação Bancária')

@section('content')
    @php
        $naoRelacionadas = $transacoes
            ->filter(fn($t) => !$t->conciliada())
            ->count();
    @endphp

    <div class="row p-3">
        <div class="col-xs-12 col-md-8">
            <div class="container">
                <div class="card border-0">
                    <div class="card-header">
                        <h5 class="mb-0">Conciliação Bancária</h5>
                    </div>
                    <div class="card-body">
                        <!-- Formulário de Importação OFX -->
                        <div class="mb-4">
                            @include('extrato.partials.form-importar-ofx')
                        </div>

                        <!-- Lista de Extratos -->
                        <div class="mb-4">
                            <h6 class="mb-3"><i class="bi bi-list-ul"></i> Conciliações Disponíveis</h6>

                            @if(!$extratos || $extratos->isEmpty())
                                <div class="alert alert-info text-center">
                                    Nenhum extrato encontrado. Envie um arquivo OFX para começar.
                                </div>
                            @else
                                <div class="row row-cols-1 row-cols-md-2 g-3">
                                    @foreach($extratos as $e)
                                        @include('extrato.partials.card-extrato', ['extrato' => $e])
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        @if ($extrato)
                            <div class="mb-4">
                                <h6 class="mb-3"><i class="bi bi-list-ul"></i> Contas financeiras envolvidas nesta conciliação</h6>

                                <!-- Contas Financeiras -->
                                <div class="row g-3 mb-3">
                                    @foreach($contasFinanceirasEnvolvidas as $conta)
                                        @php
                                            $saldoCalculado = $conta->calcularSaldoAtual($extrato->id);
                                        @endphp
                                        @include('extrato.partials.card-conta-financeira', ['conta' => $conta, 'extrato' => $extrato])
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if ($naoRelacionadas > 0)
                            <p class="small mb-1">
                                <i class="bi bi-exclamation-triangle-fill text-warning me-1"></i>
                                <strong>{{ $naoRelacionadas }}</strong> transações não foram conciliadas com nenhum pagamento ou recebimento.
                            </p>
                        @endif

                        <div class="container py-3">
                            @php
                                $receberActive = request()->filled('conta_receber_cliente_id') ||
                                                request()->filled('conta_receber_descricao') ||
                                                request()->filled('conta_receber_data_inicio') ||
                                                request()->filled('conta_receber_data_fim');
                            @endphp

                            <!-- Nav Tabs -->
                            <ul class="nav nav-tabs" id="financeTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link {{ $receberActive ? '' : 'active' }}" id="pagar-tab" data-bs-toggle="tab"
                                        data-bs-target="#pagar" type="button" role="tab">Pagamentos</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link {{ $receberActive ? 'active' : '' }}" id="receber-tab" data-bs-toggle="tab"
                                        data-bs-target="#receber" type="button" role="tab">Recebimentos</button>
                                </li>
                            </ul>

                            <div class="tab-content p-4 bg-white" id="financeTabsContent">
                                <!-- Contas a Pagar -->
                                <div class="tab-pane fade {{ $receberActive ? '' : 'show active' }}" id="pagar" role="tabpanel">
                                    @if ($extrato && $extrato->status !== 'conciliado')
                                        <form action="{{ url()->current() }}" method="GET" class="row g-3 mb-3">
                                            @csrf
                                            <div class="col-md-12">
                                                <input type="hidden" name="extrato" value="{{ $extrato->id }}">
                                                <label class="form-label">Descrição</label>
                                                <input type="text" class="form-control" name="descricao" placeholder="Descrição da conta"
                                                    value="{{ request()->descricao }}">
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label">Vencimento de</label>
                                                <input type="date" class="form-control" name="conta_pagar_data_inicio" value="{{ request()->conta_pagar_data_inicio }}">
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label">Até</label>
                                                <input type="date" class="form-control" name="conta_pagar_data_fim" value="{{ request()->conta_pagar_data_fim }}">
                                            </div>

                                            <div class="col-12 text-end">
                                                <button class="btn btn-primary" type="submit">
                                                    <i class="bi bi-search"></i> Buscar Contas a Pagar
                                                </button>
                                            </div>
                                        </form>
                                    @endif

                                    @include('extrato.partials.tabela-contas-pagar', [
                                        'contasPagar' => $contasPagar,
                                        'transacoes' => $transacoes,
                                        'extrato' => $extrato
                                    ])
                                </div>

                                <!-- Contas a Receber -->
                                <div class="tab-pane fade {{ $receberActive ? 'show active' : '' }}" id="receber" role="tabpanel">
                                    @if ($extrato && $extrato->status !== 'conciliado')
                                        <form action="{{ url()->current() }}" method="GET" class="row g-3 mb-3">
                                            @csrf
                                            <div class="col-md-12">
                                                <input type="hidden" name="extrato" value="{{ $extrato->id }}">
                                                <label class="form-label">Descrição</label>
                                                <input type="text" class="form-control" name="conta_receber_descricao" placeholder="Descrição da conta"
                                                    value="{{ request()->conta_receber_descricao }}">
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label">Vencimento de</label>
                                                <input type="date" class="form-control" name="conta_receber_data_inicio" value="{{ request()->conta_receber_data_inicio }}">
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label">Até</label>
                                                <input type="date" class="form-control" name="conta_receber_data_fim" value="{{ request()->conta_receber_data_fim }}">
                                            </div>

                                            <div class="col-12 text-end">
                                                <button class="btn btn-primary" type="submit">
                                                    <i class="bi bi-search"></i> Buscar Contas a Receber
                                                </button>
                                            </div>
                                        </form>
                                    @endif

                                    @include('extrato.partials.tabela-contas-receber', [
                                        'contasReceber' => $contasReceber,
                                        'transacoes' => $transacoes,
                                        'extrato' => $extrato
                                    ])
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Coluna direita com transações -->
        <div class="col-xs-12 col-md-4">
            <div class="container">
                <div class="card border-0">
                    <div class="card-header">
                        <h5 class="mb-0">Transações</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            @if ($extrato)
                                <p class="text-muted">
                                    Extrato bancário <strong>{{$extrato->banco}}</strong> de
                                    <strong>{{ \Carbon\Carbon::parse($extrato->inicio)->format('d/m/Y') }}</strong>
                                    até
                                    <strong>{{ \Carbon\Carbon::parse($extrato->fim)->format('d/m/Y') }}</strong>
                                </p>
                                @unless ($extrato->status === 'conciliado')
                                    <div class="d-grid gap-2 mb-2">
                                        <a href="{{ route('extrato.finalizar', ['extrato' => $extrato->id]) }}"
                                            class="btn btn-success {{ $naoRelacionadas > 0 ? 'disabled' : '' }}">
                                            Finalizar conciliação
                                        </a>
                                    </div>
                                @endunless
                            @else
                                <p class="text-muted">
                                    Nenhum extrato encontrado.
                                </p>
                            @endif
                        </div>

                        @if ($transacoes->count() > 0)
                            <!-- Filtro -->
                            <div class="mb-3">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                                    <input type="date" id="filtroTransacaoData" class="form-control"
                                        placeholder="Filtrar por data...">
                                </div>
                            </div>
                        @endif

                        <div style="max-height: 250vh; overflow-y: auto;" id="listaTransacoes">
                            @foreach ($transacoes as $transacao)
                                @include('extrato.partials.card-transacao', ['transacao' => $transacao])
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
