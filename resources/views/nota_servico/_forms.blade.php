<div class="row">
    <div class="col-md-12">

        <ul class="nav nav-tabs nav-primary" role="tablist">
            <li class="nav-item" role="presentation">
                <a class="nav-link active" data-bs-toggle="tab" href="#tomador" role="tab" aria-selected="true">
                    <div class="d-flex align-items-center">
                        <div class="tab-icon"><i class='fa fa-user me-2'></i>
                        </div>
                        <div class="tab-title">
                            <i class="ri-file-user-fill"></i>
                            Tomador
                        </div>
                    </div>
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link" data-bs-toggle="tab" href="#servico" role="tab" aria-selected="false">
                    <div class="d-flex align-items-center">
                        <div class="tab-icon"><i class='fa fa-shopping-cart me-2'></i>
                        </div>
                        <div class="tab-title">
                            <i class="ri-box-2-line"></i>
                            Serviço
                        </div>
                    </div>
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link" data-bs-toggle="tab" href="#lista_servicos" role="tab" aria-selected="false">
                    <div class="d-flex align-items-center">
                        <div class="tab-icon"><i class='fa fa-shopping-cart me-2'></i>
                        </div>
                        <div class="tab-title">
                            <i class="ri-box-2-line"></i>
                            Serviços de OS
                        </div>
                    </div>
                </a>
            </li>
        </ul>

        <hr>
        <div class="tab-content">
            <div class="tab-pane fade show active" id="tomador" role="tabpanel">
                <div class="card">

                    <div class="row m-3">
                        @isset($reserva)
                        <div class="col-md-5">
                            {!!Form::select('cliente_id', 'Cliente')->attrs(['class' => 'select2 cliente_id'])
                            ->options([$reserva->cliente_id => $reserva->cliente->razao_social])
                            !!}
                        </div>
                        @else
						@section('js')
						<script src="/js/nfse.js"></script>

						<script type="text/javascript">
							$(function(){
								// Força disparo do cliente, se já vier selecionado
								let clienteSelect = $('.cliente_id');
								if(clienteSelect.val()){
									clienteSelect.trigger('change');
								}

								// Força disparo do serviço, se já vier selecionado
								let servicoSelect = $('.servico_id');
								if(servicoSelect.val()){
									servicoSelect.trigger('change');
								}
							});
						</script>
						@endsection

						<div class="col-md-5">
							{!! Form::select('cliente_id', 'Cliente')
								->attrs(['class' => 'select2 cliente_id'])
								->options(
									isset($item) 
										? [$item->cliente_id => $item->cliente->razao_social] 
										: (isset($cliente) ? [$cliente->id => $cliente->razao_social] : [])
								)
							!!}
						</div>
                        @endif
                        <hr class="mt-3">
                        <div class="row g-2">
                            <div class="col-md-3">
                                {!!Form::text('razao_social', 'Razão social')->attrs(['class' => ''])->required()
                                ->value(isset($item) ? $item->razao_social : '')
                                !!}
                            </div>

                            <div class="col-md-2">
                                {!!Form::text('documento', 'Documento')->attrs(['class' => 'cpf_cnpj'])->required()
                                ->value(isset($item) ? $item->documento : '')
                                !!}
                            </div>

                            <div class="col-md-2">
                                {!!Form::text('ie', 'Ins. Estadual')->attrs(['class' => ''])
                                ->value(isset($item) ? $item->ie : '')
                                !!}
                            </div>

                            <div class="col-md-2">
                                {!!Form::text('im', 'Ins. Municipal')->attrs(['class' => ''])
                                ->value(isset($item) ? $item->im : '')
                                !!}
                            </div>

                            <div class="col-md-2">
                                {!!Form::text('cep', 'CEP')->attrs(['class' => 'cep'])
                                ->value(isset($item) ? $item->cep : '')->required()
                                !!}
                            </div>

                            <div class="col-md-4">
                                {!!Form::text('rua', 'Rua')->attrs(['class' => ''])
                                ->value(isset($item) ? $item->rua : '')->required()
                                !!}
                            </div>

                            <div class="col-md-2">
                                {!!Form::text('numero', 'Número')->attrs(['class' => ''])
                                ->value(isset($item) ? $item->numero : '')->required()
                                !!}
                            </div>

                            <div class="col-md-2">
                                {!!Form::text('bairro', 'Bairro')->attrs(['class' => ''])
                                ->value(isset($item) ? $item->bairro : '')->required()
                                !!}
                            </div>

                            <div class="col-md-4">
                                {!!Form::select('cidade_id', 'Cidade')
                                ->attrs(['class' => 'select2'])->options(isset($item) ? [$item->cidade_id => $item->cidade->info] : [])
                                ->required()
                                !!}
                            </div>

                            <div class="col-md-3">
                                {!!Form::text('complemento', 'Complemento')->attrs(['class' => ''])
                                ->value(isset($item) ? $item->complemento : '')
                                !!}
                            </div>

                            <div class="col-md-3">
                                {!!Form::text('email', 'Email')->attrs(['class' => ''])
                                ->value(isset($item) ? $item->email : '')
                                ->type('email')
                                !!}
                            </div>

                            <div class="col-md-2">
                                {!!Form::tel('telefone', 'Telefone')->attrs(['class' => 'fone'])
                                ->value(isset($item) ? $item->telefone : '')
                                !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade show" id="servico" role="tabpanel">
                <div class="card">
                    <div class="row m-3 g-2">
                        @isset($servicoPadrao)
                        <div class="col-md-5">
                            {!!Form::select('servico_id', 'Serviço')->attrs(['class' => 'select2 servico_id'])
                            ->options([$servicoPadrao->id => $servicoPadrao->nome])->required()
                            !!}
                        </div>
                        @else
                        <div class="col-md-5">
                            {!!Form::select('servico_id', 'Serviço')->attrs(['class' => 'select2 servico_id'])->options(isset($item) ? [$item->servico->servico_id => $item->servico->servico->nome] : [])->required()
                            !!}
                        </div>
                        @endif

                        <div class="col-md-3">
                            {!!Form::text('natureza_operacao', 'Natureza de Operação')->attrs(['class' => ''])
                            ->value(isset($item) ? $item->natureza_operacao : '')
                            !!}
                        </div>

                        <div class="col-md-2">
                            {!!Form::select('gerar_conta_receber', 'Gerar conta a receber', [0 => 'Não', 1 => 'Sim'])
                            ->attrs(['class' => 'form-select'])
                            !!}
                        </div>

                        <div class="col-md-2 div-data_vencimento d-none">
                            {!!Form::date('data_vencimento', 'Data de vencimento')->required()
                            !!}
                        </div>

                        @isset($descricaoServico)
						<div class="col-md-12">
							{!! Form::text('discriminacao', 'Discriminação')->attrs(['class' => ''])
								->value(isset($descricaoServico) ? strip_tags(html_entity_decode($descricaoServico)) : '')
								->required()
							!!}
						</div>
                        @else
                        <div class="col-md-12">
                            {!!Form::text('discriminacao', 'Discriminação')->attrs(['class' => ''])
                            ->value(isset($item) ? $item->servico->discriminacao : '')->required()
                            !!}
                        </div>
                        @endif

                        @isset($total)
                        <div class="col-md-2">
                            {!!Form::tel('valor_servico', 'Valor do serviço')->attrs(['class' => 'moeda'])
                            ->value(__moeda($total))->required()
                            !!}
                        </div>
                        @else
                        <div class="col-md-2">
                            {!!Form::tel('valor_servico', 'Valor do serviço')->attrs(['class' => 'moeda'])
                            ->value(isset($item) ? __moedaInput($item->servico->valor_servico) : '')->required()
                            !!}
                        </div>
                        @endif

                        <div class="col-md-2">
                            {!!Form::text('codigo_cnae', 'Cód. CNAE')->attrs(['class' => ''])
                            ->value(isset($item) ? $item->servico->codigo_cnae : '')
                            !!}
                        </div>

                        @isset($servicoPadrao)
                        <div class="col-md-2">
                            {!!Form::text('codigo_servico', 'Código do serviço')->attrs(['class' => ''])
                            ->value($servicoPadrao->codigo_servico)
                            !!}
                        </div>
                        @else
                        <div class="col-md-2">
                            {!!Form::text('codigo_servico', 'Código do serviço')->attrs(['class' => ''])
                            ->value(isset($item) ? $item->servico->codigo_servico : '')
                            !!}
                        </div>
                        @endif

                        <div class="col-md-3">
                            {!!Form::text('codigo_tributacao_municipio', 'Cód. de tributação do município')->attrs(['class' => ''])
                            ->value(isset($item) ? $item->servico->codigo_tributacao_municipio : '')
                            !!}
                        </div>
                        <div class="col-md-3">
                            {!!Form::text('regime_tributacao', 'Regime de tributação')->attrs(['class' => ''])
                            ->value(isset($item) ? $item->servico->regime_tributacao : '') 
                            !!}
                        </div>
                        <div class="col-md-2">
                            {!!Form::select('exigibilidade_iss', 'Exigibilidade ISS', \App\Models\NotaServico::exigibilidades())->attrs(['class' => 'form-select'])
                            ->value(isset($item) ? $item->servico->exigibilidade_iss : '')->required()
                            !!}
                        </div>

                        <div class="col-md-2">
                            {!!Form::select('iss_retido', 'ISS retido', [2 => 'Não', 1 => 'Sim'])->attrs(['class' => 'form-select'])
                            ->value(isset($item) ? $item->servico->iss_retido : '')->required()
                            !!}
                        </div>

                        <div class="col-md-2">
                            {!!Form::select('responsavel_retencao_iss', 'Resp. pela retenção', [1 => 'Tomador', 2 => 'Sim'])->attrs(['class' => 'form-select'])
                            ->value(isset($item) ? $item->servico->responsavel_retencao_iss : '')->required()
                            !!}
                        </div>

                        <div class="col-md-2">
                            {!!Form::date('data_competencia', 'Data da competência')->attrs(['class' => ''])
                            ->value(isset($item) ? $item->servico->data_competencia : '')
                            !!}
                        </div>

                        <div class="col-md-2">
                            {!!Form::select('estado_local_prestacao_servico', 'UF do local de prestação', \App\Models\Cidade::estados())->attrs(['class' => 'form-select'])
                            ->value(isset($item) ? $item->servico->estado_local_prestacao_servico : '')
                            !!}
                        </div>

                        <div class="col-md-3">
                            {!!Form::text('cidade_local_prestacao_servico', 'Cidade do local de prestação')->attrs(['class' => ''])
                            ->value(isset($item) ? $item->servico->cidade_local_prestacao_servico : '')
                            !!}
                        </div>

                        <hr>
                        <div class="col-md-2">
                            {!!Form::text('valor_deducoes', 'Valor deduções')->attrs(['class' => 'moeda'])
                            ->value(isset($item) ? $item->servico->valor_deducoes : '')
                            !!}
                        </div>
                        <div class="col-md-2">
                            {!!Form::text('desconto_incondicional', 'Desconto incondicional')->attrs(['class' => 'moeda'])
                            ->value(isset($item) ? $item->servico->desconto_incondicional : '')
                            !!}
                        </div>
                        <div class="col-md-2">
                            {!!Form::text('desconto_condicional', 'Desconto condicional')->attrs(['class' => 'moeda'])
                            ->value(isset($item) ? $item->servico->desconto_condicional : '')
                            !!}
                        </div>
                        <div class="col-md-2">
                            {!!Form::text('outras_retencoes', 'Outras retencoes')->attrs(['class' => 'moeda'])
                            ->value(isset($item) ? $item->servico->outras_retencoes : '')
                            !!}
                        </div>
                        <div class="col-md-2">
                            {!!Form::text('aliquota_iss', 'Aliquota ISS')->attrs(['class' => 'percentual'])
                            ->value(isset($item) ? $item->servico->aliquota_iss : '')
                            !!}
                        </div>
                        <div class="col-md-2">
                            {!!Form::text('aliquota_pis', 'Aliquota PIS')->attrs(['class' => 'percentual'])
                            ->value(isset($item) ? $item->servico->aliquota_pis : '')
                            !!}
                        </div>
                        <div class="col-md-2">
                            {!!Form::text('aliquota_cofins', 'Aliquota COFINS')->attrs(['class' => 'percentual'])
                            ->value(isset($item) ? $item->servico->aliquota_cofins : '')
                            !!}
                        </div>
                        <div class="col-md-2">
                            {!!Form::text('aliquota_inss', 'Aliquota INSS')->attrs(['class' => 'percentual'])
                            ->value(isset($item) ? $item->servico->aliquota_inss : '')
                            !!}
                        </div>
                        <div class="col-md-2">
                            {!!Form::text('aliquota_ir', 'Aliquota IR')->attrs(['class' => 'percentual'])
                            ->value(isset($item) ? $item->servico->aliquota_ir : '')
                            !!}
                        </div>
                        <div class="col-md-2">
                            {!!Form::text('aliquota_csll', 'Aliquota CSLL')->attrs(['class' => 'percentual'])
                            ->value(isset($item) ? $item->servico->aliquota_csll : '')
                            !!}
                        </div>
                    </div>
                </div>
            </div>
			<?php 
			//dd($os->servicos); 
			//dd($os); 
			?>
<div class="tab-pane fade show" id="lista_servicos" role="tabpanel">
		<div class="card">
			<div class="row m-3 g-2">
			@if(isset($os) && $os->servicos->count())
				<h6>Serviços da Ordem de Serviço #{{ $os->codigo_sequencial }}</h6>
			
			
				<?php
				// Configuração do banco
				$host = "127.0.0.1";
				$user = "root";
				$pass = "M@tr1z@$$2025_BR_DB#3006";
				$db   = "erp"; // nome do banco

				// Conexão
				$conn = new mysqli($host, $user, $pass, $db);

				// Checar erro
				if ($conn->connect_error) {
					die("Erro de conexão: " . $conn->connect_error);
				}

				// Defina o ID da OS que você quer consultar
				$os_id = isset($_GET['os_id']) ? intval($_GET['os_id']) : 0;

				// Query com join para puxar serviços da OS
				$sql = "
					SELECT so.id, so.quantidade, so.valor, so.subtotal, 
						   s.nome, s.descricao, s.valor as valor_padrao
					FROM servico_os so
					INNER JOIN servicos s ON s.id = so.servico_id
					WHERE so.ordem_servico_id = ?
				";

				$stmt = $conn->prepare($sql);
				$stmt->bind_param("i", $os_id);
				$stmt->execute();
				$result = $stmt->get_result();

				// Exibir os resultados
				echo "<table cellpadding='4'>";
				echo "<tr><th>Descrição</th><th>Qtd</th><th>Valor Unit.</th><th>Subtotal</th></tr>";

				while ($row = $result->fetch_assoc()) {
					echo "<tr>

							<td>{$row['descricao']}</td>
							<td>{$row['quantidade']}</td>
							<td>R$ " . number_format($row['valor'], 2, ',', '.') . "</td>
							<td>R$ " . number_format($row['subtotal'], 2, ',', '.') . "</td>
						  </tr>";
				}
				echo "</table>";

				$stmt->close();
				$conn->close();
				?>
			
			
			
			@else
				<p class="text-muted">Nenhum serviço associado a Ordem de Serviço.</p>
			@endif
			</div>
		</div>
</div>

			
        </div>
    </div>

    <hr class="mt-4">
    <div class="col-12" style="text-align: right;">
        <button type="submit" class="btn btn-success btn-salvar-nfe px-5 m-3">Salvar</button>
    </div>
</div>
