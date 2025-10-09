<div class="col">
    <div class="card card-conta h-100 shadow-sm border-0">
        <div class="card-header p-2 d-flex justify-content-between align-items-center ">
            <strong>{{ $transacao->descricao ?? 'Transação sem descrição' }}</strong>

            <div class="d-flex align-items-center">
                <!-- Dropdown menu -->
                <div class="dropdown">
                    @unless ($extrato->status === 'conciliado')
                        <button class="btn btn-sm dropdown-toggle no-shadow" type="button"
                            id="menuTransacao{{ $transacao->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                            ⋮
                        </button>
                    @endunless
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="menuTransacao{{ $transacao->id }}">
                        @unless ($transacao->movimentada())
                            <li>
                                <!-- Abrir modal Criar Conciliável -->
                                <a class="dropdown-item" href="javascript:void(0)" data-bs-toggle="modal"
                                    data-bs-target="#modalCriarConta" data-id="{{ $transacao->id }}"
                                    data-tipo="{{ $transacao->tipo }}" data-valor="{{ $transacao->valor }}"
                                    data-descricao="{{ $transacao->descricao }}" data-data="{{ $transacao->data }}">
                                    Criar Conciliável
                                </a>
                            </li>
                        @endunless
                        @unless ($transacao->conciliada())
                            <li>
                                <!-- Abrir modal de Tranferência entre contas -->
                                <a class="dropdown-item" href="javascript:void(0)" data-bs-toggle="modal"
                                    data-bs-target="#modalTransferirTransacao" data-id="{{ $transacao->id }}"
                                    data-tipo="{{ $transacao->tipo }}" data-valor="{{ $transacao->valor }}"
                                    data-descricao="{{ $transacao->descricao }}" data-data="{{ $transacao->data }}">
                                    Movimentar entre Contas
                                </a>
                            </li>
                        @endunless
                        @if ($transacao->movimentada())
                            <li>
                                <!-- Ignorar transação -->
                                <a class="dropdown-item text-danger"
                                    href="{{ route('extrato.desfazer_transferencia_transacao', ['transacao_id' => $transacao->id]) }}"
                                    onclick="return confirm('Tem certeza que deseja desfazer esta movimentação?');">
                                    Desfazer Movimentação
                                </a>
                            </li>
                        @endif
                        @unless ($transacao->conciliada())
                            <li>
                                <!-- Ignorar transação -->
                                <a class="dropdown-item text-danger"
                                    href="{{ route('extrato.ignorar_transacao', ['extrato_id' => $extrato->id, 'transacao_id' => $transacao->id]) }}"
                                    onclick="return confirm('Tem certeza que deseja ignorar esta transação?');">
                                    Ignorar Transação
                                </a>
                            </li>
                        @endunless
                    </ul>
                </div>
            </div>
        </div>

        <div class="card-body p-2">
            <p class="mb-1"><small><strong>Data:</strong>
                    {{ \Carbon\Carbon::parse($transacao->data)->format('d/m/Y') }}
                </small></p>
            <p class="mb-1"><small><strong>Valor:</strong>
                    R$ {{ number_format($transacao->valor, 2, ',', '.') }}
                </small></p>
            <p class="mb-1"><small><strong>Tipo:</strong>
                    {{ $transacao->tipo === 'CREDIT' ? 'Crédito' : 'Débito' }}
                </small></p>
            <p class="mb-1"><small><strong>Status:</strong>
                    @if($transacao->conciliada())
                        <span class="badge bg-success">Conciliado</span>
                    @else
                        <span class="badge bg-warning text-dark">Pendente</span>
                    @endif
                </small></p>
        </div>
    </div>
</div>

<div class="modal fade" id="modalCriarConta" tabindex="-1" aria-labelledby="modalCriarContaLabel" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content border-0 rounded-2 shadow-sm">
            <form id="formCriarConta" method="POST" action="{{ route('extrato.criar_conta') }}">
                @csrf

                <!-- Cabeçalho -->
                <div id="modalCriarHeader" class="modal-header border-bottom py-3">
                    <h5 class="modal-title fw-semibold mb-0" id="modalCriarContaLabel">
                        Criar Conta
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>

                <!-- Corpo -->
                <div class="modal-body">
                    <input type="hidden" name="transacao_id" id="transacaoId">
                    <input type="hidden" name="extrato_id" id="extratoId" value="{{ $extrato->id ?? null }}">
                    <input type="hidden" name="tipo" id="tipoConta">

                    <div class="row mb-2">
                        <div class="col">
                            <label for="descricaoConta" class="form-label">Descrição</label>
                            <input type="text" name="descricao" id="descricaoConta" class="form-control"
                                placeholder="Ex: Pagamento de fornecedor" required>
                        </div>
                    </div>

                    <div class="row mb-2" id="grupoFornecedor" style="display:none;">
                        <div class="col">
                            <label for="fornecedorConta" class="form-label">Fornecedor</label>
                            <select name="fornecedor_id" id="fornecedorConta" class="form-select select2"
                                style="width:100%;" data-empresa-id="{{ $extrato->empresa_id }}">
                                <option value="">Digite ao menos 2 caracteres...</option>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-2" id="grupoCliente" style="display:none;">
                        <div class="col">
                            <label for="clienteConta" class="form-label">Cliente</label>
                            <select name="cliente_id" id="clienteConta" class="form-select select2" style="width:100%;"
                                data-empresa-id="{{ $extrato->empresa_id }}">
                                <option value="">Digite ao menos 2 caracteres...</option>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-6">
                            <label for="valorConta" class="form-label">Valor</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">R$</span>
                                <input type="number" step="0.01" name="valor" id="valorConta" class="form-control"
                                    required>
                            </div>
                        </div>
                        <div class="col-6">
                            <label for="dataVencimento" class="form-label">Data de Vencimento</label>
                            <input type="date" name="data_vencimento" id="dataVencimento" class="form-control" required
                                readonly>
                        </div>
                    </div>

                    <div class="row mb-2">
                        <div class="col">
                            <label for="categoriaConta" class="form-label">Categoria da Conta</label>
                            <select name="categoria_conta_id" id="categoriaConta" class="form-select" required>
                                <option value="">Selecione</option>
                                @foreach ($categoriasContas as $categoria)
                                    <option value="{{ $categoria->id }}" data-tipo="{{ strtolower($categoria->tipo) }}">
                                        {{ $categoria->nome }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row mb-2">
                        <div class="col">
                            <label for="contaFinanceira" class="form-label">Conta Financeira</label>
                            <select name="conta_financeira_id" id="contaFinanceira" class="form-select" required>
                                <option value="">Selecione</option>
                                @foreach ($contasFinanceiras as $conta)
                                    <option value="{{ $conta->id }}">{{ $conta->nome }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row mb-2">
                        <div class="col">
                            <label for="centroCusto" class="form-label">Centro de Custo</label>
                            <select name="centro_custo_id" id="centroCusto" class="form-select">
                                <option value="">Selecione</option>
                                @foreach ($centrosCustos as $centro)
                                    <option value="{{ $centro->id }}">{{ $centro->descricao }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col">
                            <label for="observacaoConta" class="form-label">Observação</label>
                            <textarea name="observacao" id="observacaoConta" class="form-control" rows="2"
                                placeholder="Opcional"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Rodapé -->
                <div class="modal-footer d-flex justify-content-end">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">
                        Salvar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalTransferirTransacao" tabindex="-1" aria-labelledby="modalTransferirTransacaoLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content border-0 rounded-2 shadow-sm">
            <form id="formCriarConta" method="POST" action="{{ route('extrato.transferir_transacao') }}">
                @csrf

                <!-- Cabeçalho -->
                <div id="modalCriarHeader" class="modal-header border-bottom py-3">
                    <h5 class="modal-title fw-semibold mb-0" id="modalCriarContaLabel">
                        Movimentação entre contas
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>

                <!-- Corpo -->
                <div class="modal-body">
                    <input type="hidden" name="transacao_id" id="transferenciaTransacaoId">

                    <div class="row mb-2">
                        <div class="col">
                            <label for="contaOrigem" class="form-label">Conta Origem</label>
                            <select name="conta_origem_id" id="contaOrigem" class="form-select" required>
                                <option value="">Selecione</option>
                                @foreach ($contasFinanceiras as $conta)
                                    <option value="{{ $conta->id }}">{{ $conta->nome }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row mb-2">
                        <div class="col">
                            <label for="contaDestino" class="form-label">Conta Destino</label>
                            <select name="conta_destino_id" id="contaDestino" class="form-select" required>
                                <option value="">Selecione</option>
                                @foreach ($contasFinanceiras as $conta)
                                    <option value="{{ $conta->id }}">{{ $conta->nome }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Rodapé -->
                <div class="modal-footer d-flex justify-content-end">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">
                        Salvar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    $(function () {

        // Função genérica para inicializar Select2
        function initSelect2(selector, url) {
            $(selector).select2({
                placeholder: 'Digite ao menos 2 caracteres...',
                minimumInputLength: 2,
                allowClear: true,
                width: '100%',
                dropdownParent: $('#modalCriarConta'),
                ajax: {
                    url: url,
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            pesquisa: params.term,                            // termo digitado
                            empresa_id: $(selector).data('empresa-id')       // pega o ID da empresa do data-empresa-id
                        };
                    },
                    processResults: function (data) {
                        return {
                            results: data.map(function (item) {
                                return {
                                    id: item.id,
                                    text: item.nome_fantasia ?? item.razao_social
                                };
                            })
                        };
                    },
                    cache: true
                }
            });
        }

        // Ativa Select2 para Fornecedor e Cliente
        initSelect2('#fornecedorConta', '/api/fornecedores/pesquisa');
        initSelect2('#clienteConta', '/api/clientes/pesquisa');

        // Função para filtrar categorias de acordo com o tipo
        function filtrarCategorias(tipo) {
            const selectCategoria = document.getElementById('categoriaConta');
            selectCategoria.querySelectorAll('option').forEach(option => {
                const categoriaTipo = option.getAttribute('data-tipo');
                option.style.display =
                    (tipo === 'DEBIT' && (categoriaTipo === 'custo' || categoriaTipo === 'despesa')) ||
                        (tipo === 'CREDIT' && categoriaTipo === 'receita')
                        ? '' : 'none';
            });
            selectCategoria.selectedIndex = -1;
        }

        // Função para alternar entre Fornecedor e Cliente
        function toggleFornecedorCliente(tipo) {
            const grupoFornecedor = document.getElementById('grupoFornecedor');
            const grupoCliente = document.getElementById('grupoCliente');
            $('#fornecedorConta, #clienteConta').val(null).trigger('change');

            if (tipo === 'DEBIT') {
                grupoFornecedor.style.display = 'block';
                grupoCliente.style.display = 'none';
            } else if (tipo === 'CREDIT') {
                grupoFornecedor.style.display = 'none';
                grupoCliente.style.display = 'block';
            } else {
                grupoFornecedor.style.display = 'none';
                grupoCliente.style.display = 'none';
            }
        }

        // Evento ao abrir modal
        $('#modalCriarConta').on('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const id = button.getAttribute('data-id');
            const tipo = button.getAttribute('data-tipo');
            const descricao = button.getAttribute('data-descricao');
            const valor = button.getAttribute('data-valor');
            const data = button.getAttribute('data-data');
            const header = document.getElementById('modalCriarHeader');

            $('#transacaoId').val(id);
            $('#tipoConta').val(tipo);
            $('#descricaoConta').val(descricao || '');
            $('#valorConta').val(valor || '');
            $('#dataVencimento').val(data || '');
            $('#modalCriarContaLabel').text(
                tipo === 'DEBIT' ? 'Criar Pagamento' : 'Criar Recebimento'
            );

            filtrarCategorias(tipo);
            toggleFornecedorCliente(tipo);
        });

        $('#modalTransferirTransacao').on('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const id = button.getAttribute('data-id');

            $('#transferenciaTransacaoId').val(id);
        });

    });
</script>