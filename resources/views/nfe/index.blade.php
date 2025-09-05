@extends('layouts.app', ['title' => 'NFe'])
@section('content')
<style>
    /* Adiciona uma borda preta aos cartões */
    .grade-card {
        border: 1px solid rgb(170, 170, 170);
        /* Define a borda  */
        border-radius: 0.35rem;
        /* Opcional: arredonda os cantos da borda */
        box-shadow: 0 0 0.125rem rgba(0, 0, 0, 0.075);
        /* Opcional: adiciona uma sombra sutil */
    }
</style>
<div class="mt-3">
    <div class="row">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    @can('nfe_create')
                    <div class="col-md-2">
                        <a href="{{ route('nfe.create') }}" class="btn btn-success">
                            <i class="ri-add-circle-fill"></i>
                            Nova Venda
                        </a>
                    </div>
                    @endcan

                    <div class="col-md-8"></div>

                    @if(__isPlanoFiscal())
                    <div class="col-md-2">
                        <button id="btn-consulta-sefaz" class="btn btn-dark" style="float: right;">
                            <i class="ri-refresh-line"></i>
                            Consultar Status Sefaz
                        </button>
                    </div>
                    @endif
                </div>
                <hr class="mt-3">
                <div class="col-lg-12">
                    {!!Form::open()->fill(request()->all())
                    ->get()
                    !!}
                    <div class="row mt-3 g-1">
                        <div class="col-md-4">
                            {!!Form::select('cliente_id', 'Cliente')
                            ->attrs(['class' => 'select2'])
                            !!}
                        </div>
                        <div class="col-md-2">
                            {!!Form::date('start_date', 'Data inicial')
                            !!}
                        </div>
                        <div class="col-md-2">
                            {!!Form::date('end_date', 'Data final')
                            !!}
                        </div>
                        @if(__isPlanoFiscal())
                        <div class="col-md-2">
                            {!!Form::select('estado', 'Estado',
                            ['novo' => 'Novas',
                            'rejeitado' => 'Rejeitadas',
                            'cancelado' => 'Canceladas',
                            'aprovado' => 'Aprovadas',
                            '' => 'Todos'])
                            ->attrs(['class' => 'form-select'])
                            !!}
                        </div>

                        <div class="col-md-2">
                            {!!Form::select('tpNF', 'Tipo',
                            [
                            '1' => 'Saída',
                            '0' => 'Entrada',
                            '-' => 'Todos'
                            ])
                            ->attrs(['class' => 'form-select'])
                            !!}
                        </div>
                        @endif

                        @if(__countLocalAtivo() > 1)
                        <div class="col-md-2">
                            {!!Form::select('local_id', 'Local', ['' => 'Selecione'] + __getLocaisAtivoUsuario()->pluck('descricao', 'id')->all())
                            ->attrs(['class' => 'select2'])
                            !!}
                        </div>
                        @endif
                        <div class="col-md-2">
                            {!! Form::select('ordenar_por', 'Ordenar por', [
                            '' => 'Selecione',
                            'razao_social_asc' => 'Razão Social (A-Z)',
                            'data_cadastro_asc' => 'Vendas mais antigas',
                            'data_cadastro_desc' => 'Vendas mais recentes',
                            ])->attrs(['class' => 'form-control']) !!}
                        </div>



                        <div class="col-lg-4 col-12">
                            <br>

                            <button class="btn btn-primary" type="submit"> <i class="ri-search-line"></i>Pesquisar</button>
                            <a id="clear-filter" class="btn btn-danger" href="{{ route('nfe.index') }}"><i class="ri-eraser-fill"></i>Limpar</a>
                        </div>
                    </div>
                    {!!Form::close()!!}

                </div>


                <!-- Botões de Alternância -->
                <div class="d-flex justify-content-end mb-3">
                    <button class="btn btn-primary mx-2" id="btn-lista">Visualizar em Lista</button>
                    <button class="btn btn-ligth mx-2" id="btn-grade">Visualizar em Grade</button>
                </div>

                <div id="visualizacao-container">
                    <div class="table-responsive" , id="view-lista">
                        <table class="table table-striped table-centered mb-0">
                            <thead class="table-dark">
                                <tr>

                                    @can('nfe_delete')
                                    <th>
                                        <div class="form-check form-checkbox-danger mb-2">
                                            <input class="form-check-input" type="checkbox" id="select-all-checkbox">
                                        </div>
                                    </th>
                                    @endcan

                                    <th>#</th>
                                    <th>Ações</th>
                                    <th>*</th>
                                    <th>Cliente/Fornecedor</th>
                                    <th>CPF/CNPJ</th>
                                    @if(__countLocalAtivo() > 1)
                                    <th>Local</th>
                                    @endif
                                    <th>Usuário</th>
                                    <th>Número</th>
                                    <th>Valor</th>
                                    @if(__isPlanoFiscal())
                                    <th>Estado</th>
                                    <th>Ambiente</th>
                                    @endif
                                    <th>Data de cadastro</th>
                                    <th>Data de emissão</th>
                                    <th>Local de emissão</th>
                                    <th>Tipo</th>



                                </tr>
                            </thead>
                            <tbody>
                                @forelse($data as $item)
                                <tr>
                                    @can('nfe_delete')
                                    <td>
                                        <div class="form-check form-checkbox-danger mb-2">
                                            <input class="form-check-input check-delete" type="checkbox" name="item_delete[]" value="{{ $item->id }}">
                                        </div>
                                    </td>
                                    @endcan

                                    <td>{{ $item->numero_sequencial }}</td>

                                    <td>
                                        <form action="{{ route('nfe.destroy', $item->id) }}" method="post" id="form-{{$item->id}}" style="width: 400px">
                                            @method('delete')
                                            @csrf

                                            @if($item->estado == 'cancelado')
                                            <a class="btn btn-danger btn-sm" target="_blank" href="{{ route('nfe.imprimir-cancela', [$item->id]) }}">
                                                <i class="ri-printer-line"></i>
                                            </a>
                                            @endif
                                            @if($item->estado == 'aprovado')

                                            <button type="button" onclick="imprimir('{{$item->id}}', '{{$item->numero}}')" class="btn btn-primary btn-sm" title="Imprimir NFe">
                                                <i class="ri-printer-line"></i>
                                            </button>
                                            @can('nfe_transmitir')
                                            <button title="Cancelar NFe" type="button" class="btn btn-danger btn-sm" onclick="cancelar('{{$item->id}}', '{{$item->numero}}')">
                                                <i class="ri-close-circle-line"></i>
                                            </button>
                                            <button title="Corrigir NFe" type="button" class="btn btn-warning btn-sm" onclick="corrigir('{{$item->id}}', '{{$item->numero}}')">
                                                <i class="ri-file-warning-line"></i>
                                            </button>
                                            @endcan
                                            @endif

                                            @if($item->estado == 'aprovado' || $item->estado == 'rejeitado')
                                            <button title="Consultar status" type="button" class="btn btn-dark btn-sm" onclick="info('{{$item->motivo_rejeicao}}', '{{$item->chave}}', '{{$item->estado}}', '{{$item->recibo}}')">
                                                <i class="ri-file-line"></i>
                                            </button>
                                            @endif

                                            @if($item->estado == 'novo' || $item->estado == 'rejeitado')
                                            @can('nfe_edit')
                                            <a class="btn btn-warning btn-sm" href="{{ route('nfe.edit', $item->id) }}">
                                                <i class="ri-edit-line"></i>
                                            </a>
                                            @endcan
                                            @if(__isPlanoFiscal())
                                            <a target="_blank" title="XML temporário" class="btn btn-light btn-sm" href="{{ route('nfe.xml-temp', $item->id) }}">
                                                <i class="ri-file-line"></i>
                                            </a>
                                            @endif

                                            @can('nfe_delete')
                                            <button type="button" title="Excluir" class="btn btn-danger btn-sm btn-delete"><i class="ri-delete-bin-line"></i></button>
                                            @endcan

                                            @if(__isPlanoFiscal())
                                            @can('nfe_transmitir')
                                            <button title="Transmitir NFe" type="button" class="btn btn-success btn-sm" onclick="transmitir('{{$item->id}}')">
                                                <i class="ri-send-plane-fill"></i>
                                            </button>
                                            @endcan
                                            @endif

                                            @endif
                                            <a class="btn btn-info btn-sm" title="Imprimir Pedido" target="_blank" href="{{ route('nfe.imprimirVenda', [$item->id]) }}">
                                                <i class="ri-printer-line"></i>
                                            </a>
                                            @if($item->estado == 'aprovado' || $item->estado == 'cancelado' || $item->estado == 'rejeitado')
                                            <button title="Consultar NFe" type="button" class="btn btn-light btn-sm" onclick="consultar('{{$item->id}}', '{{$item->numero}}')">
                                                <i class="ri-file-search-line"></i>
                                            </button>
                                            @endif
                                            @if(__isPlanoFiscal())
                                            @can('nfe_edit')
                                            <a title="Alterar estado fiscal" class="btn btn-danger btn-sm" href="{{ route('nfe.alterar-estado', $item->id) }}">
                                                <i class="ri-arrow-up-down-line"></i>
                                            </a>
                                            @endcan
                                            @endif

                                            <a class="btn btn-light btn-sm" title="DANFE Temporária" target="_blank" href="{{ route('nfe.danfe-temporaria', [$item->id]) }}">
                                                <i class="ri-printer-fill"></i>
                                            </a>

                                            <a class="btn btn-warning btn-sm" title="Detalhes" href="{{ route('nfe.show', $item->id) }}"><i class="ri-eye-line"></i></a>



                                        </form>
                                    </td>
                                    <td>

                                        @if($item->ordemServico)
                                        <a title="Ordem de serviço" class="btn btn-sm btn-primary" href="{{ route('ordem-servico.show', [$item->ordemServico->id]) }}">OS</a>

                                        @else
                                        --
                                        @endif
                                    </td>

                                    @if($item->cliente)
                                    <td><label style="width: 350px">{{ $item->cliente ? $item->cliente->razao_social : "--" }}</label></td>
                                    <td>{{ $item->cliente ? $item->cliente->cpf_cnpj : "--" }}</td>
                                    @else
                                    <td><label style="width: 350px">{{ $item->fornecedor ? $item->fornecedor->razao_social : "--" }}</label></td>
                                    <td>{{ $item->fornecedor ? $item->fornecedor->cpf_cnpj : "--" }}</td>
                                    @endif
                                    @if(__countLocalAtivo() > 1)
                                    <td class="text-danger">{{ $item->localizacao->descricao }}</td>
                                    @endif
                                    <td>{{ $item->user ? $item->user->name : '--' }}</td>

                                    <td>{{ $item->numero ? $item->numero : '' }}</td>
                                    <td>{{ __moeda($item->total) }}</td>
                                    @if(__isPlanoFiscal())
                                    <td width="150">
                                        @if($item->estado == 'aprovado')
                                        <span class="btn btn-success text-white btn-sm w-100">Aprovado</span>
                                        @elseif($item->estado == 'cancelado')
                                        <span class="btn btn-danger text-white btn-sm w-100">Cancelado</span>
                                        @elseif($item->estado == 'rejeitado')
                                        <span class="btn btn-warning text-white btn-sm w-100">Rejeitado</span>
                                        @else
                                        <span class="btn btn-info text-white btn-sm w-100">Novo</span>
                                        @endif
                                    </td>
                                    <td>{{ $item->ambiente == 2 ? 'Homologação' : 'Produção' }}</td>
                                    @endif
                                    <td><label style="width: 120px">{{ __data_pt($item->created_at) }}</label></td>
                                    <td><label style="width: 120px">{{ $item->data_emissao ? __data_pt($item->data_emissao, 1) : '--' }}</label></td>
                                    <td>
                                        @if($item->api)
                                        <span class="text-success">API</span>
                                        @else
                                        <span class="text-primary">Painel</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($item->tpNF)
                                        <span class="text-success">Saída</span>
                                        @else
                                        <span class="text-primary">Entrada</span>
                                        @endif
                                    </td>




                                </tr>
                                @empty
                                <tr>
                                    <td colspan="14" class="text-center">Nada encontrado</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {!! $data->appends(request()->all())->links() !!}

                </div>
            </div>
        </div>

        <div class="modal fade" id="modal-print" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">

                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Imprimir NFe <strong class="ref-numero"></strong></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <div class="d-flex flex-wrap gap-2">
                            <button type="button" class="btn btn-success flex-fill" onclick="gerarDanfe('danfe')">
                                <i class="ri-printer-line"></i> DANFE
                            </button>

                            <button type="button" class="btn btn-primary flex-fill" onclick="gerarDanfe('simples')">
                                <i class="ri-printer-line"></i> DANFE Simples
                            </button>

                            <button type="button" class="btn btn-dark flex-fill" onclick="gerarDanfe('etiquetas')">
                                <i class="ri-printer-line"></i> DANFE Etiqueta
                            </button>

                            <button type="button" class="btn btn-info flex-fill" onclick="gerarDanfe('etiqueta_correio')">
                                <i class="ri-printer-line"></i> Etiqueta para Correios
                            </button>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Fechar</button>
                    </div>

                </div>
            </div>
        </div>


        <div class="modal fade" id="modal-cancelar" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">

                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Cancelar NFe <strong class="ref-numero"></strong></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">

                            <div class="col-md-12">
                                {!!Form::text('motivo-cancela', 'Motivo')
                                ->required()

                                !!}
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Fechar</button>
                        <button type="button" id="btn-cancelar" class="btn btn-danger">Cancelar</button>
                    </div>
                </div>
            </div>
        </div>


        <div class="modal fade" id="modal-corrigir" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">

                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Corrigir NFe <strong class="ref-numero"></strong></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">

                            <div class="col-md-12">
                                {!!Form::text('motivo-corrigir', 'Motivo')
                                ->required()

                                !!}
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Fechar</button>
                        <button type="button" id="btn-corrigir" class="btn btn-warning">Corrigir</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- Visualização em Grade -->
        <div id="view-grade" class="row d-none">
            @forelse($data as $item)
            <div class="col-sm-4 mb-3">
                <div class="card grade-card">
                    <div class="card-body">
                        <h5 class="card-title">Número: {{ $item->numero_sequencial }}</h5>
                        <p class="card-text">
                            @if($item->cliente)
                            Cliente: {{ $item->cliente->razao_social  }}<br>
                            CPF/CNPJ: {{ $item->cliente->cpf_cnpj }}
                            @else
                            Fornecedor: {{ $item->fornecedor->razao_social }}<br>
                            CPF/CNPJ: {{ $item->fornecedor->cpf_cnpj }}
                            @endif
                        </p>
                        <p class="card-text">
                            Data de Cadastro: {{ __data_pt($item->created_at) }}<br>
                            Data Emissão: {{ $item->data_emissao ? __data_pt($item->data_emissao, 1) : '--' }} <br>
                            Usuário: {{ $item->user->name ?? '--' }}<br>
                            Total: {{ __moeda($item->total) }}<br>
                            Status:
                            @if($item->estado == 'aprovado')
                            <span class="badge bg-success text-white">Aprovado</span>
                            @elseif($item->estado == 'cancelado')
                            <span class="badge bg-danger text-white">Cancelado</span>
                            @elseif($item->estado == 'rejeitado')
                            <span class="badge bg-warning text-dark">Rejeitado</span>
                            @else
                            <span class="badge bg-info text-white">Novo</span>
                            @endif
                        </p>
                        <p class="card-text">Ambiente: {{ $item->ambiente == 2 ? 'Homologação' : 'Produção' }}</p>
                        <!-- Ações (conforme necessário) -->


                        <form action="{{ route('nfe.destroy', $item->id) }}" method="post" id="form-{{$item->id}}" style="display: flex; flex-wrap: wrap; gap: 5px; align-items: center;">
                            @method('delete')
                            @csrf

                            @if($item->ordemServico)
                            <a title="Ordem de serviço" class="btn btn-sm btn-primary" href="{{ route('ordem-servico.show', [$item->ordemServico->id]) }}">OS</a>
                            @else
                            <span class="text-muted">--</span>
                            @endif

                            @if($item->estado == 'cancelado')
                            <a class="btn btn-danger btn-sm" target="_blank" href="{{ route('nfe.imprimir-cancela', [$item->id]) }}">
                                <i class="ri-printer-line"></i>
                            </a>
                            @endif

                            @if($item->estado == 'aprovado')
                            <button type="button" onclick="imprimir('{{$item->id}}', '{{$item->numero}}')" class="btn btn-primary btn-sm" title="Imprimir NFe">
                                <i class="ri-printer-line"></i>
                            </button>

                            @can('nfe_transmitir')
                            <button title="Cancelar NFe" type="button" class="btn btn-danger btn-sm" onclick="cancelar('{{$item->id}}', '{{$item->numero}}')">
                                <i class="ri-close-circle-line"></i>
                            </button>

                            <button title="Corrigir NFe" type="button" class="btn btn-warning btn-sm" onclick="corrigir('{{$item->id}}', '{{$item->numero}}')">
                                <i class="ri-file-warning-line"></i>
                            </button>
                            @endcan
                            @endif

                            @if($item->estado == 'aprovado' || $item->estado == 'rejeitado')
                            <button title="Consultar status" type="button" class="btn btn-dark btn-sm" onclick="info('{{$item->motivo_rejeicao}}', '{{$item->chave}}', '{{$item->estado}}', '{{$item->recibo}}')">
                                <i class="ri-file-line"></i>
                            </button>
                            @endif

                            @if($item->estado == 'novo' || $item->estado == 'rejeitado')
                            @can('nfe_edit')
                            <a class="btn btn-warning btn-sm" href="{{ route('nfe.edit', $item->id) }}">
                                <i class="ri-edit-line"></i>
                            </a>
                            @endcan

                            @if(__isPlanoFiscal())
                            <a target="_blank" title="XML temporário" class="btn btn-light btn-sm" href="{{ route('nfe.xml-temp', $item->id) }}">
                                <i class="ri-file-line"></i>
                            </a>
                            @endif

                            @can('nfe_delete')
                            <button type="button" class="btn btn-danger btn-sm btn-delete"><i class="ri-delete-bin-line"></i></button>
                            @endcan

                            @if(__isPlanoFiscal())
                            @can('nfe_transmitir')
                            <button title="Transmitir NFe" type="button" class="btn btn-success btn-sm" onclick="transmitir('{{$item->id}}')">
                                <i class="ri-send-plane-fill"></i>
                            </button>
                            @endcan
                            @endif
                            @endif

                            <a class="btn btn-info btn-sm" title="Imprimir Pedido" target="_blank" href="{{ route('nfe.imprimirVenda', [$item->id]) }}">
                                <i class="ri-printer-line"></i>
                            </a>

                            @if($item->estado == 'aprovado' || $item->estado == 'cancelado' || $item->estado == 'rejeitado')
                            <button title="Consultar NFe" type="button" class="btn btn-light btn-sm" onclick="consultar('{{$item->id}}', '{{$item->numero}}')">
                                <i class="ri-file-search-line"></i>
                            </button>
                            @endif

                            @if(__isPlanoFiscal())
                            @can('nfe_edit')
                            <a title="Alterar estado fiscal" class="btn btn-danger btn-sm" href="{{ route('nfe.alterar-estado', $item->id) }}">
                                <i class="ri-arrow-up-down-line"></i>
                            </a>
                            @endcan
                            @endif

                            <a class="btn btn-light btn-sm" title="Detalhes" href="{{ route('nfe.show', $item->id) }}">
                                <i class="ri-eye-line"></i>
                            </a>

                            <a class="btn btn-danger btn-sm" title="DANFE Temporária" target="_blank" href="{{ route('nfe.danfe-temporaria', [$item->id]) }}">
                                <i class="ri-printer-fill"></i>
                            </a>
                        </form>

                    </div>
                </div>
            </div>

            @empty
            <div class="col-12 text-center">
                <p>Nada encontrado</p>
            </div>


            @endforelse
        </div>
        <!-- Card com a soma -->
        <div class="card mt-4">
            <div class="card-body">

                @can('nfe_delete')
                <form action="{{ route('nfe.destroy-batch') }}" method="post" id="form-delete-select">
                    @method('delete')
                    @csrf
                    <div></div>
                    <button type="button" class="btn btn-danger btn-sm btn-delete-all" disabled>
                        <i class="ri-close-circle-line"></i> Remover Selecionados
                    </button>
                </form>
                @endcan

                <div class="non-clickable">
                    <h5 class="mt-4">Soma: <strong class="text-success">R$ {{ __moeda($data->sum('total')) }}</strong></h5>
                </div>
            </div>
        </div>
    </div>


    @endsection

    @section('js')
    <script type="text/javascript">
        function info(motivo_rejeicao, chave, estado, recibo) {
            if (estado == 'rejeitado') {
                let text = "Motivo: " + motivo_rejeicao + "\n"
                text += "Chave: " + chave + "\n"
                swal("", text, "warning")
            } else {
                let text = "Chave: " + chave + "\n"
                text += "Recibo: " + recibo + "\n"
                swal("", text, "success")
            }
        }

        $('#btn-consulta-sefaz').click(() => {
            $.post(path_url + 'api/nfe_painel/consulta-status-sefaz', {
                    empresa_id: $('#empresa_id').val(),
                    usuario_id: $('#usuario_id').val(),
                })
                .done((res) => {
                    let msg = "cStat: " + res.cStat
                    msg += "\nMotivo: " + res.xMotivo
                    msg += "\nAmbiente: " + (res.tpAmb == 2 ? "Homologação" : "Produção")
                    msg += "\nverAplic: " + res.verAplic

                    swal("Sucesso", msg, "success")
                })
                .fail((err) => {
                    try {
                        swal("Erro", err.responseText, "error")
                    } catch {
                        swal("Erro", "Algo deu errado", "error")
                    }
                })
        })
        const btnLista = document.getElementById('btn-lista');
        const btnGrade = document.getElementById('btn-grade');
        const viewLista = document.getElementById('view-lista');
        const viewGrade = document.getElementById('view-grade');

        btnLista.addEventListener('click', function() {
            viewLista.classList.remove('d-none');
            viewGrade.classList.add('d-none');
            btnLista.classList.add('btn-primary');
            btnGrade.classList.remove('btn-primary');
        });

        btnGrade.addEventListener('click', function() {
            viewGrade.classList.remove('d-none');
            viewLista.classList.add('d-none');
            btnGrade.classList.add('btn-primary');
            btnLista.classList.remove('btn-primary');
        });
    </script>
    <script type="text/javascript" src="/js/nfe_transmitir.js"></script>
    <script type="text/javascript" src="/js/nfe_delete.js"></script>

    @endsection