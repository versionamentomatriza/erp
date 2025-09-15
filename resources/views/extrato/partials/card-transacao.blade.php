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
            class="btn btn-primary btn-sm w-100 d-flex align-items-center justify-content-center gap-1 py-1"
            data-bs-toggle="modal" data-bs-target="#modalCriarConta" data-id="{{ $transacao->id }}"
            data-tipo="{{ $transacao->tipo }}" data-valor="{{ $transacao->valor }}"
            data-descricao="{{ $transacao->descricao }}" data-data="{{ $transacao->data }}">
            <i class="bi bi-plus-circle"></i>
            Criar consiliável
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
                        <select name="fornecedor_id" id="fornecedorConta" class="form-select rounded-3">
                            <option value="">Selecione</option>
                            @foreach ($fornecedores as $fornecedor)
                                <option value="{{ $fornecedor->id }}">{{ $fornecedor->nome_fantasia ?? $fornecedor->razao_social }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3" id="grupoCliente" style="display:none;">
                        <label for="clienteConta" class="form-label fw-semibold">Cliente</label>
                        <select name="cliente_id" id="clienteConta" class="form-select rounded-3">
                            <option value="">Selecione</option>
                            @foreach ($clientes as $cliente)
                                <option value="{{ $cliente->id }}">{{ $cliente->nome_fantasia ?? $cliente->razao_social }}</option>
                            @endforeach
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

            // Mostrar fornecedor ou cliente conforme o tipo
            var grupoFornecedor = document.getElementById('grupoFornecedor');
            var grupoCliente = document.getElementById('grupoCliente');

            // resetar selects
            document.getElementById('fornecedorConta').value = "";
            document.getElementById('clienteConta').value = "";

            if (tipo === 'DEBIT') {
                // contas a pagar → fornecedor
                grupoFornecedor.style.display = 'block';
                grupoCliente.style.display = 'none';
            } else if (tipo === 'CREDIT') {
                // contas a receber → cliente
                grupoFornecedor.style.display = 'none';
                grupoCliente.style.display = 'block';
            } else {
                grupoFornecedor.style.display = 'none';
                grupoCliente.style.display = 'none';
            }
        });
    });
</script>