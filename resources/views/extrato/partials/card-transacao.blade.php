<div class="card transacao-card mb-2 p-2" data-id="{{$transacao->id ?? '-' }}" data-data="{{ $transacao->data }}">

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
            <span class="badge bg-warning text-dark"><i class="bi bi-exclamation-circle-fill"></i></span>
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
            class="btn btn-success btn-sm w-100 d-flex align-items-center justify-content-center gap-1 py-1"
            data-bs-toggle="modal" data-bs-target="#modalCriarConta" data-id="{{ $transacao->id }}"
            data-tipo="{{ $transacao->tipo }}" data-valor="{{ $transacao->valor }}"
            data-descricao="{{ $transacao->descricao }}" data-data="{{ $transacao->data }}">
            <i class="bi bi-plus-circle"></i>
            Criar conciliável
        </button>
    </div>
</div>

<div class="modal fade" id="modalCriarConta" tabindex="-1" aria-labelledby="modalCriarContaLabel" aria-hidden="true">
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

                    <div class="mb-3" id="grupoFornecedor" style="display:none;">
                        <label for="fornecedorConta" class="form-label fw-semibold">Fornecedor</label>

                        {{-- Select vazio, será carregado via AJAX --}}
                        <select name="fornecedor_id" id="fornecedorConta" class="form-select rounded-3 select2"
                            style="width:100%;" data-empresa-id="{{ $extrato->empresa_id }}">
                            <option value="">Digite ao menos 2 caracteres...</option>
                        </select>
                    </div>

                    <div class="mb-3" id="grupoCliente" style="display:none;">
                        <label for="clienteConta" class="form-label fw-semibold">Cliente</label>

                        {{-- Select vazio, também carregado via AJAX --}}
                        <select name="cliente_id" id="clienteConta" class="form-select rounded-3 select2"
                            style="width:100%;" data-empresa-id="{{ $extrato->empresa_id }}">
                            <option value="">Digite ao menos 2 caracteres...</option>
                        </select>
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
                        <select name="categoria_conta_id" id="categoriaConta" class="form-select rounded-3" required>
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

            $('#transacaoId').val(id);
            $('#tipoConta').val(tipo);
            $('#descricaoConta').val(descricao || '');
            $('#valorConta').val(valor || '');
            $('#dataVencimento').val(data || '');
            $('#modalCriarContaLabel').text(
                tipo === 'DEBIT' ? 'Criar Conta a Pagar' : 'Criar Conta a Receber'
            );

            filtrarCategorias(tipo);
            toggleFornecedorCliente(tipo);
        });

    });
</script>
