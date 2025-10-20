@section('css')
    <style>
        /* Esconde input original de arquivo */
        input[type="file"] {
            display: none;
        }

        /* Botão customizado para upload */
        .file-certificado label {
            padding: 10px 15px;
            width: 100%;
            background-color: #8833FF;
            color: #FFF;
            text-transform: uppercase;
            text-align: center;
            display: block;
            margin-top: 17px;
            cursor: pointer;
            border-radius: .5rem;
            font-size: 0.9rem;
            transition: background 0.3s ease;
        }

        .file-certificado label:hover {
            background-color: #6f28cc;
        }

        .card-body strong {
            color: #8833FF;
        }
    </style>
@endsection

<div class="row g-3">

    {{-- Mensagem se houver diferença --}}
    @if(isset($diferenca) && $diferenca > 0)
        <div class="col-md-12 mb-3">
            <p class="text-info">Crie uma nova conta a receber ou finalize abaixo!</p>
            <a href="{{ route('conta-receber.index') }}" class="btn btn-dark btn-sm px-3">
                <i class="ri-arrow-left-double-fill"></i> Finalizar novo recebimento
            </a>
        </div>
    @endif

    {{-- Local --}}
    @if(__countLocalAtivo() > 1)
        <div class="col-md-2">
            <label for="inp-local_id" class="form-label">Local<span>*</span></label>
            <select id="inp-local_id" required class="form-select select2" name="local_id">
                <option value="">Selecione</option>
                @foreach(__getLocaisAtivoUsuario() as $local)
                    <option value="{{ $local->id }}" {{ isset($item) && $item->local_id == $local->id ? 'selected' : '' }}>
                        {{ $local->descricao }}
                    </option>
                @endforeach
            </select>
        </div>
    @else
        <input type="hidden" id="inp-local_id" value="{{ __getLocalAtivo()->id ?? '' }}" name="local_id">
    @endif

    {{-- Cliente --}}
    <div class="col-md-12">
        <label class="form-label">Cliente<span>*</span></label>
        <div class="input-group">
            <select id="inp-cliente_id" name="cliente_id" class="form-select select2" required>
                <option value="">Selecione...</option>
                @isset($item)
                    <option value="{{ $item->cliente_id }}" selected>
                        {{ $item->cliente->razao_social }}
                    </option>
                @endisset

                @foreach($clientes as $cliente)
                    <option value="{{ $cliente->id }}">{{ $cliente->razao_social }}</option>
                @endforeach
            </select>
            @can('clientes_create')
                <button class="btn btn-dark" type="button" data-bs-toggle="modal" data-bs-target="#modal_novo_cliente">
                    <i class="ri-add-circle-fill"></i>
                </button>
            @endcan
        </div>
    </div>

    {{-- Descrição --}}
    <div class="col-md-12">
        {!! Form::text('descricao', 'Descrição')->required() !!}
    </div>

    <div class="col-md-12">
        {!! Form::select(
    'categoria_conta_id',
    'Categoria da Receita',
    \App\Models\CategoriaConta::where('tipo', 'receita')
        ->orderBy('nome')
        ->pluck('nome', 'id')
        ->toArray(),
    isset($item) ? $item->categoria_conta_id : null
)->attrs(['class' => 'form-select'])->required() !!}
    </div>

    {{-- Valor Integral --}}
    <div class="col-md-2">
        {!! Form::text('valor_integral', 'Valor Integral')
    ->attrs(['class' => 'form-control moeda', 'id' => 'inp-valor_integral'])
    ->value(isset($item) ? __moeda($item->valor_integral) : '')
    ->required() !!}
    </div>

    {{-- Data Vencimento --}}
    <div class="col-md-2">
        {!! Form::date('data_vencimento', 'Data Vencimento')
    ->attrs(['id' => 'inp-data_vencimento'])
    ->required() !!}
    </div>

    {{-- Data Compétencia --}}
    <div class="col-md-2">
        {!! Form::date('data_competencia', 'Data de Competência')
    ->attrs(['id' => 'inp-data_competencia'])
    ->required() !!}
    </div>

    {{-- Status --}}
    <div class="col-md-2">
        {!! Form::select('status', 'Conta Recebida', ['0' => 'Não', '1' => 'Sim'])
    ->attrs(['class' => 'form-select', 'id' => 'inp-status'])
    ->required() !!}
    </div>

    {{-- Valor Recebido (escondido inicialmente) --}}
    <div class="col-md-2">
        {!! Form::text('valor_recebido', 'Valor Recebido')
    ->attrs(['class' => 'form-control moeda', 'id' => 'inp-valor_recebido'])
    ->disabled()
    ->value(isset($item) ? __moeda($item->valor_recebido ?? '') : '') !!}
    </div>

    {{-- Data Recebimento (escondido inicialmente) --}}
    <div class="col-md-2">
        {!! Form::date('data_recebimento', 'Data Recebimento')
    ->attrs(['id' => 'inp-data_recebimento'])
    ->disabled()
    ->value(isset($item) ? $item->data_recebimento : '') !!}
    </div>

    {{-- Tipo de Pagamento --}}
    <div class="col-md-3">
        {!! Form::select('tipo_pagamento', 'Tipo Pagamento', App\Models\ContaReceber::tiposPagamento())
    ->attrs(['class' => 'form-select'])
    ->required() !!}
    </div>

    {{-- Parcelas --}}
    <div class="col-md-2">
        {!! Form::select('parcelas', 'Parcelas', ['1' => 'À vista', '2' => '2x', '3' => '3x', '4' => '4x', '5' => '5x', '6' => '6x', '7' => '7x', '8' => '8x', '9' => '9x', '10' => '10x', '11' => '11x', '12' => '12x', '13' => '13x', '14' => '14x', '15' => '15x', '16' => '16x', '17' => '17x', '18' => '18x', '19' => '19x', '20' => '20x', '21' => '21x', '22' => '22x', '23' => '23x', '24' => '24x'])
    ->attrs(['class' => 'form-select', 'id' => 'inp-parcelas'])
    ->required() !!}
    </div>

    {{-- Valor Parcela --}}
    <div class="col-md-2">
        {!! Form::text('valor_parcelas', 'Valor das Parcelas')
    ->attrs(['class' => 'form-control moeda', 'id' => 'inp-valor_parcelas'])
    ->disabled() !!}
    </div>

    {{-- Centro de Custo --}}
    <div class="col-md-5">
        {!! Form::select('centro_custo_id', 'Centro de Custo', ['' => 'Selecione'] + $centrosCusto->pluck('descricao', 'id')->all())
    ->attrs(['class' => 'form-select'])
    ->value(isset($item) ? $item->centro_custo_id : '') !!}
    </div>

    {{-- Observação --}}
    <div class="col-12">
        {!! Form::text('observacao', 'Observação') !!}
    </div>

    {{-- Upload de Arquivo --}}
    <div class="col-md-5 file-certificado">
        {!! Form::file('file', 'Anexar arquivo')->attrs(['accept' => '.pdf,image/*']) !!}
        <span class="text-danger small" id="filename"></span>
    </div>

    {{-- Link para download se já existe arquivo --}}
    @if(isset($item) && $item->arquivo)
        <div class="col-12">
            <a href="{{ route('conta-receber.download-file', [$item->id]) }}" class="btn btn-outline-primary btn-sm">
                <i class="ri-file-download-line"></i> Baixar arquivo
            </a>
        </div>
    @endif

    {{-- Recorrência --}}
    @if(!isset($item))
        <div class="col-12">
            <p class="text-danger small mt-4">
                * Campo abaixo deve ser preenchido se o recebimento for recorrente (ex: mensalidades, assinaturas, aluguel,
                serviços contínuos).
            </p>
        </div>

        <div class="col-md-2">
            {!! Form::tel('recorrencia', 'Recorrência até')
            ->attrs(['data-mask' => '00/00', 'id' => 'inp-recorrencia'])
            ->placeholder('mm/aa') !!}
        </div>
    @endif

    <div class="row tbl-recorrencia d-none mt-2"></div>

    <hr class="mt-4">

    {{-- Botão salvar --}}
    <div class="col-12 text-end">
        <button type="submit" class="btn btn-success px-5" id="btn-store">
            <i class="ri-check-line me-1"></i> Salvar
        </button>
    </div>
</div>

@section('js')
    <script src="/js/novo_cliente.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const status = document.getElementById("inp-status");
            const valorIntegral = document.getElementById("inp-valor_integral");
            const valorRecebido = document.getElementById("inp-valor_recebido");
            const dataRecebimento = document.getElementById("inp-data_recebimento");
            const parcelas = document.getElementById("inp-parcelas");
            const valorParcelas = document.getElementById("inp-valor_parcelas");

            // --- Controle de recebimento ---
            function toggleCamposRecebimento() {
                if (status.value === "1") {
                    valorRecebido.removeAttribute("disabled");
                    dataRecebimento.removeAttribute("disabled");

                    if (!valorRecebido.value) valorRecebido.value = valorIntegral.value;
                    if (!dataRecebimento.value)
                        dataRecebimento.value = new Date().toISOString().split("T")[0];
                } else {
                    valorRecebido.setAttribute("disabled", true);
                    dataRecebimento.setAttribute("disabled", true);
                    valorRecebido.value = "";
                    dataRecebimento.value = "";
                }
            }

            status.addEventListener("change", toggleCamposRecebimento);
            toggleCamposRecebimento();

            // --- Controle de parcelas ---
            function toggleValorParcelas() {
                const qtd = parseInt(parcelas.value);
                const total = parseFloat(valorIntegral.value.replace(/\./g, '').replace(',', '.')) || 0;

                if (qtd > 1) {
                    valorParcelas.removeAttribute("disabled");

                    if (total > 0) {
                        const valorParcela = (total / qtd).toFixed(2);
                        valorParcelas.value = valorParcela.replace('.', ',');
                    }
                } else {
                    valorParcelas.setAttribute("disabled", true);
                    valorParcelas.value = "";
                }
            }

            // Agora o listener está no campo correto 👇
            parcelas.addEventListener("change", toggleValorParcelas);
            valorIntegral.addEventListener("blur", toggleValorParcelas);
            toggleValorParcelas();

            // --- Recorrência ---
            $('#inp-recorrencia').on('blur', function () {
                let data = $(this).val();
                if (data.length === 5) {
                    let vencimento = $('#inp-data_vencimento').val();
                    let valor = $('#inp-valor_integral').val();
                    if (valor && vencimento) {
                        $.get(path_url + 'api/conta-receber/recorrencia', { data, vencimento, valor })
                            .done((html) => $('.tbl-recorrencia').html(html).removeClass('d-none'))
                            .fail((err) => console.log(err));
                    } else {
                        swal("Algo saiu errado", "Informe o valor e vencimento da conta base!", "warning");
                    }
                } else if (data.length > 0) {
                    swal("Algo saiu errado", "Informe uma data válida no formato mm/aa (ex: 12/25)", "warning");
                }
            });
        });
    </script>
@endsection