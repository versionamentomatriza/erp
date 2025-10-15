@extends('layouts.app', ['title' => 'DRE'])

@section('title', 'DRE')

@section('content')
    <div class="container py-4">
        <div class="card shadow-lg border-0 p-4">
            <!-- Cabeçalho -->
            <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
                <div>
                    <h1 class="h3 mb-1">Demonstração do Resultado do Exercício (DRE)</h1>
                    <div class="text-muted small">
                        <i class="bi bi-building"></i> {{ $empresa->nome_fantasia ?? $empresa->nome }} <br>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <a href="#" class="btn btn-primary"><i class="bi bi-file-earmark-pdf"></i> Exportar PDF</a>
                </div>
            </div>

            @if ($dre)
                <!-- Cards resumo -->
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <div class="card shadow-none border-0">
                            <div class="card-body">
                                <div class="text-muted small">Receita Líquida</div>
                                <div class="fs-5 fw-semibold">R$
                                    {{ number_format($dre['receita_liquida'], 2, ',', '.') }}
                                </div>
                                <div class="small text-muted">Bruta - Deduções</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card shadow-none border-0">
                            <div class="card-body">
                                <div class="text-muted small">Lucro Bruto</div>
                                <div class="fs-5 fw-semibold">R$ {{ number_format($dre['lucro_bruto'], 2, ',', '.') }}
                                </div>
                                <div class="small text-muted">Receita Líquida - Custos</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card shadow-none border-0">
                            <div class="card-body">
                                <div class="text-muted small">Resultado Operacional</div>
                                <div class="fs-5 fw-semibold">R$
                                    {{ number_format($dre['resultado_operacional'], 2, ',', '.') }}
                                </div>
                                <div class="small text-muted">Lucro Bruto - Despesas Op.</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card shadow-none border-0">
                            <div class="card-body">
                                <div class="text-muted small">Lucro Líquido</div>
                                <div class="fs-5 fw-semibold {{ $dre['lucro_liquido'] < 0 ? 'text-danger' : 'text-success' }}">
                                    R$ {{ number_format($dre['lucro_liquido'], 2, ',', '.') }}
                                </div>
                                <div class="small text-muted">Resultado final do período</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col">
                        @include('extrato.partials.tabela-dre', ['dre' => $dre])
                    </div>
                </div>
            @else
                <form method="GET" action="{{ route('extrato.dre') }}" class="row g-3 mb-3">
                    <div class="row">
                        <div class="col-5">
                            <label for="inicio" class="form-label">Início</label>
                            <input type="month" class="form-control" name="inicio" id="inicio"
                                value="{{ request('inicio', now()->format('Y-m')) }}" required>
                        </div>

                        <div class="col-5">
                            <label for="fim" class="form-label">Fim</label>
                            <input type="month" class="form-control" name="fim" id="fim"
                                value="{{ request('fim', now()->format('Y-m')) }}" required>
                        </div>

                        <div class="col-2 align-self-end">
                            <button type="submit" class="btn btn-primary w-100 w-sm-auto">Filtrar</button>
                        </div>
                    </div>
                </form>
                <div class="alert alert-info">
                    Selecione um período para gerar a Demonstração do Resultado do Exercício.
                </div>
            @endif
        </div>
    </div>
@endsection