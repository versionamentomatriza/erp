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
                            <form action="{{ route('extrato.conciliar.post') }}" method="POST"
                                enctype="multipart/form-data">
                                @csrf
                                <label for="arquivos_ofx" class="form-label">Importe arquivos OFX ou selecione a conciliação
                                    desejada</label>
                                <div class="input-group mb-3">
                                    <input class="form-control" type="file" name="arquivos_ofx[]" id="arquivos_ofx"
                                        accept=".ofx" multiple required>
                                    <button type="submit" class="btn btn-success">
                                        <i class="bi bi-upload"></i> Importar
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Lista de Extratos -->
                        <div class="mb-4">
                            <h6 class="mb-3"><i class="bi bi-list-ul"></i> Conciliações Disponíveis</h6>

                            @if($extratos->isEmpty())
                                <div class="alert alert-info text-center">
                                    Nenhum extrato encontrado. Envie um arquivo OFX para começar.
                                </div>
                            @else
                                <div class="row row-cols-1 row-cols-md-2 g-3">
                                    @foreach($extratos as $e)
                                        <div class="col">
                                            <div
                                                class="card h-100 shadow-sm border-0 {{ request()->get('extrato') == $e->id ? 'border-primary' : '' }}">
                                                <div
                                                    class="card-header bg-primary text-white p-2 d-flex justify-content-between align-items-center">
                                                    <strong>{{ $e->banco ?? 'Banco não informado' }}</strong>
                                                    @if(request()->get('extrato') == $e->id)
                                                        <span class="badge bg-success">Selecionado</span>
                                                    @endif
                                                </div>
                                                <div class="card-body p-2">
                                                    <p class="mb-1"><small><strong>Período:</strong>
                                                            {{ \Carbon\Carbon::parse($e->inicio)->format('d/m/Y') }}
                                                            - {{ \Carbon\Carbon::parse($e->fim)->format('d/m/Y') }}
                                                        </small></p>
                                                    <p class="mb-1"><small><strong>Saldo:</strong>
                                                            R$ {{ number_format($e->saldo_final ?? 0, 2, ',', '.') }}
                                                        </small></p>
                                                    <p class="mb-1"><small><strong>Status:</strong>
                                                            @if($e->status === 'conciliado')
                                                                <span class="badge bg-success">Conciliado</span>
                                                            @else
                                                                <span class="badge bg-warning text-dark">Pendente</span>
                                                            @endif
                                                        </small></p>
                                                </div>
                                                <div class="card-footer text-center p-2 bg-light">
                                                    <a href="{{ route('extrato.conciliar', ['extrato' => $e->id]) }}"
                                                        class="btn btn-sm btn-outline-primary w-100 {{ request()->get('extrato') == $e->id ? 'disabled' : '' }}">
                                                        Selecionar
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        @if ($naoRelacionadas > 0)
                            <p class="small mb-1">
                                <i class="bi bi-exclamation-triangle-fill text-warning me-1"></i>
                                <strong>{{ $naoRelacionadas }}</strong> transações não foram conciliadas com nenhuma conta.
                            </p>
                        @endif

                        <div class="container py-3">
                            <!-- Nav Tabs -->
                            <ul class="nav nav-tabs" id="financeTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="pagar-tab" data-bs-toggle="tab"
                                        data-bs-target="#pagar" type="button" role="tab">Contas a Pagar</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="receber-tab" data-bs-toggle="tab" data-bs-target="#receber"
                                        type="button" role="tab">Contas a Receber</button>
                                </li>
                            </ul>

                            <!-- Tab Content -->
                            <div class="tab-content border border-top-0 p-4 bg-white" id="financeTabsContent">

                                <!-- Contas a Pagar -->
                                <div class="tab-pane fade show active" id="pagar" role="tabpanel">
                                    @php
                                        $perPage = 15;
                                        $pagePagamento = request()->get('page_pagamento', 1);
                                        $offsetPagamento = ($pagePagamento - 1) * $perPage;
                                        $contasPagarPaginadas = $contasPagar->slice($offsetPagamento, $perPage);
                                        $totalPaginasPagamento = ceil($contasPagar->count() / $perPage);
                                    @endphp

                                    <table class="table text-center table-hover">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Descrição</th>
                                                <th>Data do pagamento</th>
                                                <th>Valor</th>
                                                <th>Status</th>
                                                <th>Ações</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($contasPagarPaginadas as $conta)
                                                <tr>
                                                    <td>{{ $conta->id ?? '-' }}</td>
                                                    <td>{{ $conta->descricao ?? '-' }}</td>
                                                    <td>{{ \Carbon\Carbon::parse($conta->data_pagamento ?? now())->format('d/m/Y') }}
                                                    </td>
                                                    <td>R$ {{ number_format($conta->valor_pago ?? 0, 2, ',', '.') }}</td>
                                                    <td>
                                                        @if ($conta->conciliada())
                                                            <span class="badge bg-success"><i
                                                                    class="bi bi-check-circle-fill"></i></span>
                                                        @else
                                                            <span class="badge bg-warning text-dark"><i
                                                                    class="bi bi-exclamation-circle-fill"></i></span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if ($conta->conciliacoes->isNotEmpty())
                                                            <!-- Botão Desvincular -->
                                                            <button type="button" class="btn btn-danger btn-sm"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#modalDesvincularContas-{{ $conta->id }}"
                                                                title="Desvincular transações">
                                                                Desvincular
                                                            </button>

                                                            @if ($conta->aindaPodeConciliar())
                                                                <!-- Botão Vincular -->
                                                                <button type="button" class="btn btn-sm btn-primary"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#modalVincularConta-{{ $conta->id }}"
                                                                    title="Vincular a outra transação">
                                                                    Vincular
                                                                </button>
                                                            @endif
                                                        @else
                                                            <!-- Nenhuma conciliação ainda → só mostrar Vincular -->
                                                            <button type="button" class="btn btn-sm btn-primary"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#modalVincularConta-{{ $conta->id }}"
                                                                title="Vincular a uma transação">
                                                                Vincular
                                                            </button>
                                                        @endif
                                                    </td>
                                                </tr>

                                                <!-- Modal Desvincular exclusivo para essa conta -->
                                                @if ($conta->conciliacoes->isNotEmpty())
                                                    <div class="modal fade" id="modalDesvincularContas-{{ $conta->id }}"
                                                        tabindex="-1" aria-hidden="true">
                                                        <div class="modal-dialog modal-lg">
                                                            <div class="modal-content">
                                                                <form method="POST" action="{{ route('extrato.desvincular') }}">
                                                                    @csrf
                                                                    <input type="hidden" name="id_conta" value="{{ $conta->id }}">
                                                                    <input type="hidden" name="id_extrato"
                                                                        value="{{ $extrato->id ?? null }}">
                                                                    <input type="hidden" name="tipo_conta"
                                                                        value="{{ get_class($conta) }}">

                                                                    <div class="modal-header">
                                                                        <h5 class="modal-title">Desvincular transações conciliadas
                                                                        </h5>
                                                                        <button type="button" class="btn-close"
                                                                            data-bs-dismiss="modal" aria-label="Fechar"></button>
                                                                    </div>

                                                                    <div class="modal-body">
                                                                        <p>Selecione as conciliações que deseja desvincular:</p>
                                                                        <div class="list-group">
                                                                            @foreach($conta->conciliacoes as $conciliacao)
                                                                                <label class="list-group-item d-flex">
                                                                                    <input type="checkbox" name="ids_transacoes[]"
                                                                                        value="{{ $conciliacao->transacao->id }}"
                                                                                        class="form-check-input me-2">
                                                                                    <span>
                                                                                        {{ $conciliacao->transacao->descricao ?? 'Sem descrição' }}
                                                                                        <br>
                                                                                        <small class="text-muted">
                                                                                            {{ \Carbon\Carbon::parse($conciliacao->transacao->data)->format('d/m/Y') }}
                                                                                            - R$
                                                                                            {{ number_format($conciliacao->transacao->valor, 2, ',', '.') }}
                                                                                        </small>
                                                                                    </span>
                                                                                </label>
                                                                            @endforeach
                                                                        </div>
                                                                    </div>

                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-secondary"
                                                                            data-bs-dismiss="modal">Cancelar</button>
                                                                        <button type="submit" class="btn btn-danger">Desvincular
                                                                            Selecionadas</button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif

                                                <!-- Modal Vincular exclusivo para essa conta -->
                                                <div class="modal fade" id="modalVincularConta-{{ $conta->id }}" tabindex="-1"
                                                    aria-hidden="true">
                                                    <div class="modal-dialog modal-lg">
                                                        <div class="modal-content">
                                                            <form method="POST" action="{{ route('extrato.vincular') }}">
                                                                @csrf
                                                                <input type="hidden" name="id_conta" value="{{ $conta->id }}">
                                                                <input type="hidden" name="id_extrato"
                                                                    value="{{ $extrato->id }}">
                                                                <input type="hidden" name="tipo_conta"
                                                                    value="{{ get_class($conta) }}">

                                                                <div class="modal-header">
                                                                    <h5 class="modal-title">Vincular transações à conta
                                                                        #{{ $conta->id }}</h5>
                                                                    <button type="button" class="btn-close"
                                                                        data-bs-dismiss="modal" aria-label="Fechar"></button>
                                                                </div>

                                                                <div class="modal-body">
                                                                    <p>Selecione as transações para vincular:</p>
                                                                    <div class="list-group">
                                                                        @forelse($transacoes as $transacao)
                                                                            @php
                                                                                $tipoEsperado = str_contains(get_class($conta), 'ContaPagar') ? 'DEBIT' : 'CREDIT';
                                                                            @endphp

                                                                            @continue($transacao->tipo !== $tipoEsperado)

                                                                            <label class="list-group-item d-flex">
                                                                                <input type="checkbox" name="ids_transacoes[]"
                                                                                    value="{{ $transacao->id }}"
                                                                                    class="form-check-input me-2">
                                                                                <span>
                                                                                    {{ $transacao->descricao ?? 'Sem descrição' }}
                                                                                    <br>
                                                                                    <small class="text-muted">
                                                                                        {{ \Carbon\Carbon::parse($transacao->data)->format('d/m/Y') }}
                                                                                        - R$
                                                                                        {{ number_format($transacao->valor, 2, ',', '.') }}
                                                                                    </small>
                                                                                </span>
                                                                            </label>
                                                                        @empty
                                                                            <div class="alert alert-warning">
                                                                                Nenhuma transação disponível para vincular.
                                                                            </div>
                                                                        @endforelse
                                                                    </div>
                                                                </div>

                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary"
                                                                        data-bs-dismiss="modal">Cancelar</button>
                                                                    <button type="submit" class="btn btn-primary">Vincular
                                                                        Selecionadas</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            @empty
                                                <tr>
                                                    <td colspan="6" class="text-center text-muted">Nenhuma conta a pagar
                                                        encontrada.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>

                                    @if ($totalPaginasPagamento > 1)
                                        <nav>
                                            <ul class="pagination justify-content-center">
                                                @for ($i = 1; $i <= $totalPaginasPagamento; $i++)
                                                    <li class="page-item {{ $i == $pagePagamento ? 'active' : '' }}">
                                                        <a class="page-link"
                                                            href="{{ request()->fullUrlWithQuery(['page_pagamento' => $i]) }}">
                                                            {{ $i }}
                                                        </a>
                                                    </li>
                                                @endfor
                                            </ul>
                                        </nav>
                                    @endif
                                </div>

                                <!-- Contas a Receber -->
                                <div class="tab-pane fade" id="receber" role="tabpanel">
                                    @php
                                        $perPage = 15;
                                        $pageRecebimento = request()->get('page_recebimento', 1);
                                        $offsetRecebimento = ($pageRecebimento - 1) * $perPage;
                                        $contasReceberPaginadas = $contasReceber->slice($offsetRecebimento, $perPage);
                                        $totalPaginasReceber = ceil($contasReceber->count() / $perPage);
                                    @endphp

                                    <table class="table text-center table-hover">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Descrição</th>
                                                <th>Data do pagamento</th>
                                                <th>Valor</th>
                                                <th>Status</th>
                                                <th>Ações</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($contasReceberPaginadas as $conta)
                                                <tr>
                                                    <td>{{ $conta->id ?? '-' }}</td>
                                                    <td>{{ $conta->descricao ?? '-' }}</td>
                                                    <td>{{ \Carbon\Carbon::parse($conta->data_recebimento ?? now())->format('d/m/Y') }}
                                                    </td>
                                                    <td>R$ {{ number_format($conta->valor_recebido ?? 0, 2, ',', '.') }}</td>
                                                    <td>
                                                        @if ($conta->conciliada())
                                                            <span class="badge bg-success"><i
                                                                    class="bi bi-check-circle-fill"></i></span>
                                                        @else
                                                            <span class="badge bg-warning text-dark"><i
                                                                    class="bi bi-exclamation-circle-fill"></i></span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if ($conta->conciliacoes->isNotEmpty())
                                                            <!-- Botão Desvincular -->
                                                            <button type="button" class="btn btn-danger btn-sm"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#modalDesvincularContasReceber-{{ $conta->id }}"
                                                                title="Desvincular transações">
                                                                Desvincular
                                                            </button>

                                                            @if ($conta->aindaPodeConciliar())
                                                                <!-- Botão Vincular -->
                                                                <button type="button" class="btn btn-sm btn-primary"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#modalVincularContaReceber-{{ $conta->id }}"
                                                                    title="Vincular a outra transação">
                                                                    Vincular
                                                                </button>
                                                            @endif
                                                        @else
                                                            <!-- Nenhuma conciliação ainda → só mostrar Vincular -->
                                                            <button type="button" class="btn btn-sm btn-primary"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#modalVincularContaReceber-{{ $conta->id }}"
                                                                title="Vincular a uma transação">
                                                                Vincular
                                                            </button>
                                                        @endif
                                                    </td>
                                                </tr>

                                                <!-- Modal Desvincular exclusivo para essa conta -->
                                                @if ($conta->conciliacoes->isNotEmpty())
                                                    <div class="modal fade" id="modalDesvincularContasReceber-{{ $conta->id }}"
                                                        tabindex="-1" aria-hidden="true">
                                                        <div class="modal-dialog modal-lg">
                                                            <div class="modal-content">
                                                                <form method="POST" action="{{ route('extrato.desvincular') }}">
                                                                    @csrf
                                                                    <input type="hidden" name="id_conta" value="{{ $conta->id }}">
                                                                    <input type="hidden" name="id_extrato"
                                                                        value="{{ $extrato->id }}">
                                                                    <input type="hidden" name="tipo_conta"
                                                                        value="{{ get_class($conta) }}">

                                                                    <div class="modal-header">
                                                                        <h5 class="modal-title">Desvincular transações conciliadas
                                                                        </h5>
                                                                        <button type="button" class="btn-close"
                                                                            data-bs-dismiss="modal" aria-label="Fechar"></button>
                                                                    </div>

                                                                    <div class="modal-body">
                                                                        <p>Selecione as conciliações que deseja desvincular:</p>
                                                                        <div class="list-group">
                                                                            @foreach($conta->conciliacoes as $conciliacao)
                                                                                <label class="list-group-item d-flex">
                                                                                    <input type="checkbox" name="ids_transacoes[]"
                                                                                        value="{{ $conciliacao->transacao->id }}"
                                                                                        class="form-check-input me-2">
                                                                                    <span>
                                                                                        {{ $conciliacao->transacao->descricao ?? 'Sem descrição' }}<br>
                                                                                        <small class="text-muted">
                                                                                            {{ \Carbon\Carbon::parse($conciliacao->transacao->data)->format('d/m/Y') }}
                                                                                            - R$
                                                                                            {{ number_format($conciliacao->transacao->valor, 2, ',', '.') }}
                                                                                        </small>
                                                                                    </span>
                                                                                </label>
                                                                            @endforeach
                                                                        </div>
                                                                    </div>

                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-secondary"
                                                                            data-bs-dismiss="modal">Cancelar</button>
                                                                        <button type="submit" class="btn btn-danger">Desvincular
                                                                            Selecionadas</button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif

                                                <!-- Modal Vincular exclusivo para essa conta -->
                                                <div class="modal fade" id="modalVincularContaReceber-{{ $conta->id }}"
                                                    tabindex="-1" aria-hidden="true">
                                                    <div class="modal-dialog modal-lg">
                                                        <div class="modal-content">
                                                            <form method="POST" action="{{ route('extrato.vincular') }}">
                                                                @csrf
                                                                <input type="hidden" name="id_conta" value="{{ $conta->id }}">
                                                                <input type="hidden" name="tipo_conta"
                                                                    value="{{ get_class($conta) }}">

                                                                <div class="modal-header">
                                                                    <h5 class="modal-title">Vincular transações à conta
                                                                        #{{ $conta->id }}</h5>
                                                                    <button type="button" class="btn-close"
                                                                        data-bs-dismiss="modal" aria-label="Fechar"></button>
                                                                </div>

                                                                <div class="modal-body">
                                                                    <p>Selecione as transações para vincular:</p>
                                                                    <div class="list-group">
                                                                        @forelse($transacoes as $transacao)
                                                                            @php
                                                                                $tipoEsperado = 'CREDIT'; // Para contas a receber
                                                                            @endphp

                                                                            @continue($transacao->tipo !== $tipoEsperado)

                                                                            <label class="list-group-item d-flex">
                                                                                <input type="checkbox" name="ids_transacoes[]"
                                                                                    value="{{ $transacao->id }}"
                                                                                    class="form-check-input me-2">
                                                                                <span>
                                                                                    {{ $transacao->descricao ?? 'Sem descrição' }}<br>
                                                                                    <small class="text-muted">
                                                                                        {{ \Carbon\Carbon::parse($transacao->data)->format('d/m/Y') }}
                                                                                        - R$
                                                                                        {{ number_format($transacao->valor, 2, ',', '.') }}
                                                                                    </small>
                                                                                </span>
                                                                            </label>
                                                                        @empty
                                                                            <div class="alert alert-warning">
                                                                                Nenhuma transação disponível para vincular.
                                                                            </div>
                                                                        @endforelse
                                                                    </div>
                                                                </div>

                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary"
                                                                        data-bs-dismiss="modal">Cancelar</button>
                                                                    <button type="submit" class="btn btn-primary">Vincular
                                                                        Selecionadas</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            @empty
                                                <tr>
                                                    <td colspan="6" class="text-center text-muted">Nenhuma conta a receber
                                                        encontrada.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>

                                    @if ($totalPaginasReceber > 1)
                                        <nav>
                                            <ul class="pagination justify-content-center">
                                                @for ($i = 1; $i <= $totalPaginasReceber; $i++)
                                                    <li class="page-item {{ $i == $pageRecebimento ? 'active' : '' }}">
                                                        <a class="page-link"
                                                            href="{{ request()->fullUrlWithQuery(['page_recebimento' => $i]) }}">
                                                            {{ $i }}
                                                        </a>
                                                    </li>
                                                @endfor
                                            </ul>
                                        </nav>
                                    @endif
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
                                <div class="d-grid gap-2">
                                    <a href="{{ route('extrato.dre', ['extrato' => $extrato->id]) }}"
                                        class="btn btn-outline-primary {{ $extrato->status !== 'conciliado' ? 'disabled' : '' }}">
                                        Relatório DRE
                                    </a>
                                </div>
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

                        <div style="max-height: 200vh; overflow-y: auto;" id="listaTransacoes">
                            @foreach ($transacoes as $transacao)
                                <div class="card transacao-card mb-2 p-2" data-id="{{$transacao->id ?? '-' }}"
                                    data-data="{{ $transacao->data }}">

                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <small class="text-muted">
                                            <strong>#{{ $transacao->id ?? '-' }}</strong>
                                            → Conta(s):
                                            @if($transacao->conciliacoes->isNotEmpty())
                                                <strong>
                                                    {{ $transacao->conciliacoes->pluck('conciliavel_id')->implode(', ') }}
                                                </strong>
                                            @else
                                                <strong>-</strong>
                                            @endif
                                        </small>
                                        @if ($transacao->conciliada())
                                            <span class="badge bg-success"><i class="bi bi-check-circle-fill"></i></span>
                                        @else
                                            <span class="badge bg-warning text-dark"><i
                                                    class="bi bi-exclamation-circle-fill"></i></span>
                                        @endif
                                    </div>

                                    <div class="d-flex flex-wrap justify-content-between small text-muted">
                                        <div><strong>Data:</strong>
                                            {{ \Carbon\Carbon::parse($transacao->data ?? now())->format('d/m/Y') }}
                                        </div>
                                        <div><strong>Valor:</strong> R$
                                            {{ number_format(abs($transacao->valor ?? 0), 2, ',', '.') }}
                                        </div>
                                        <div><strong>Tipo:</strong>
                                            {{ ($transacao->tipo) === 'DEBIT' ? 'DÉBITO' : 'CRÉDITO' }}
                                        </div>
                                        <div><strong>Banco:</strong> {{ $transacao->banco ?? '-' }}</div>
                                        <div><strong>Descrição:</strong> {{ $transacao->descricao ?? '-' }}</div>
                                    </div>

                                    {{-- 🔹 Botão para criar Conta Pagar ou Receber --}}

                                    <div class="mt-2">
                                        <button type="button"
                                            class="btn btn-primary btn-sm w-100 d-flex align-items-center justify-content-center gap-1 py-1"
                                            data-bs-toggle="modal" data-bs-target="#modalCriarConta"
                                            data-id="{{ $transacao->id }}" data-tipo="{{ $transacao->tipo }}"
                                            data-valor="{{ $transacao->valor }}" data-descricao="{{ $transacao->descricao }}"
                                            data-data="{{ $transacao->data }}">
                                            <i class="bi bi-plus-circle"></i>
                                            Criar consiliável
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- (Modais e JS continuam iguais ao seu código original) -->
        <div class="modal fade" id="modalCriarConta" tabindex="-1" aria-labelledby="modalCriarContaLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-md modal-dialog-centered">
                <div class="modal-content shadow-lg border-0 rounded-3">
                    <form id="formCriarConta" method="POST" action="{{ route('extrato.criar_conta') }}" class="p-0">
                        @csrf
                        <div class="modal-header border-0 pb-0">
                            <h5 class="modal-title fw-bold text-primary" id="modalCriarContaLabel">Criar Conta</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                        </div>

                        <div class="modal-body">
                            <input type="hidden" name="transacao_id" id="transacaoId">
                            <input type="hidden" name="extrato_id" id="extratoId" value="{{$extrato->id ?? null}}">
                            <input type="hidden" name="tipo" id="tipoConta">

                            <div class="mb-3">
                                <label for="descricaoConta" class="form-label fw-semibold">Descrição</label>
                                <input type="text" name="descricao" id="descricaoConta" class="form-control rounded-3"
                                    placeholder="Ex: Pagamento de fornecedor" required>
                            </div>

                            <div class="mb-3">
                                <label for="valorConta" class="form-label fw-semibold">Valor</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-secondary">R$</span>
                                    <input type="number" step="0.01" name="valor" id="valorConta"
                                        class="form-control rounded-end" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="dataVencimento" class="form-label fw-semibold">Data de Vencimento</label>
                                <input type="date" name="data_vencimento" id="dataVencimento" class="form-control rounded-3"
                                    required readonly>
                            </div>

                            <div class="mb-3">
                                <label for="categoriaConta" class="form-label fw-semibold">Categoria da Conta</label>
                                <select name="categoria_conta_id" id="categoriaConta" class="form-select rounded-3"
                                    required>
                                    <option value="">Selecione</option>
                                    @foreach ($categoriasContas as $categoria)
                                        <option value="{{ $categoria->id }}" data-tipo="{{ strtolower($categoria->tipo) }}">
                                            {{ $categoria->nome }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="centroCusto" class="form-label fw-semibold">Centro de Custo</label>
                                <select name="centro_custo_id" id="centroCusto" class="form-select rounded-3">
                                    <option value="">Selecione</option>
                                    @foreach ($centrosCustos as $centro)
                                        <option value="{{ $centro->id }}">{{ $centro->descricao }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="observacaoConta" class="form-label fw-semibold">Observação</label>
                                <textarea name="observacao" id="observacaoConta" class="form-control rounded-3" rows="2"
                                    placeholder="Opcional"></textarea>
                            </div>
                        </div>

                        <div class="modal-footer border-0 pt-0">
                            <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary px-4 d-flex align-items-center gap-2">
                                <i class="bi bi-save"></i> Salvar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var modal = document.getElementById('modalCriarConta');
                modal.addEventListener('show.bs.modal', function (event) {
                    var button = event.relatedTarget;

                    var id = button.getAttribute('data-id');
                    var tipo = button.getAttribute('data-tipo'); // DEBIT ou CREDIT
                    var descricao = button.getAttribute('data-descricao');
                    var valor = button.getAttribute('data-valor');
                    var data = button.getAttribute('data-data');

                    document.getElementById('transacaoId').value = id;
                    document.getElementById('tipoConta').value = tipo;
                    document.getElementById('descricaoConta').value = descricao || '';
                    document.getElementById('valorConta').value = valor || '';
                    document.getElementById('dataVencimento').value = data || '';

                    // Ajustar título
                    var titulo = tipo === 'DEBIT' ? 'Criar Conta a Pagar' : 'Criar Conta a Receber';
                    document.getElementById('modalCriarContaLabel').textContent = titulo;

                    // 🔍 Filtrar categorias dinamicamente
                    var selectCategoria = document.getElementById('categoriaConta');
                    var options = selectCategoria.querySelectorAll('option');

                    options.forEach(function (option) {
                        var categoriaTipo = option.getAttribute('data-tipo'); // receita, despesa, custo
                        if ((tipo === 'DEBIT' && (categoriaTipo === 'custo' || categoriaTipo === 'despesa')) ||
                            (tipo === 'CREDIT' && categoriaTipo === 'receita')) {
                            option.style.display = ''; // mostra
                        } else {
                            option.style.display = 'none'; // esconde
                        }
                    });

                    // Resetar seleção para evitar erro
                    selectCategoria.selectedIndex = -1;
                });
            });
        </script>
@endsection