<?php

use Illuminate\Support\Facades\Route;
use Maatwebsite\Excel\Row;
use App\Http\Controllers\ExtratoController;
use App\Http\Controllers\OrdemServicoController;
use App\Http\Controllers\UsuarioController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Auth::routes();
Route::post('/reset-pass', 'ResetPasswordController@reset')->name('reset.pass');

Route::get('/clear-all', function () {
    \Artisan::call('cache:clear');
    \Artisan::call('config:clear');
    \Artisan::call('view:clear');
    // system('composer dump-autoload');
});

Route::get('/', function () {
    return redirect()->route('home');
});
Route::post('/logout', [App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::resource('centro-custo', 'CentroCustoController');
});


Route::get('/cotacoes-resposta/{hash_link}', 'CotacaoRespostaController@index')->name('cotacoes.resposta');
Route::post('/cotacoes-resposta-store', 'CotacaoRespostaController@store')->name('cotacoes.resposta-store');
Route::get('/cotacoes-finish', 'CotacaoRespostaController@finish')->name('cotacoes.finish');
Route::get('/imprimir-nfce/{chave}', 'ImprimirNfceController@imprimir');

Route::get('/reserva-cliente/{codigo}', 'ReservaClienteController@index');
Route::post('/reserva-checkin-start-cliente/{id}', 'ReservaClienteController@checkinStart')->name('reservas.checkin-start-cliente');

Route::middleware(['validaDelivery'])->group(function () {
    Route::get('food', 'Delivery\\HomeController@index')->name('food.index');
    Route::get('food-produtos-categoria/{hash}', 'Delivery\\HomeController@produtosDaCategoria')->name('food.produtos-categoria');
    Route::get('food-servicos-categoria/{hash}', 'Delivery\\HomeController@servicosDaCategoria')->name('food.servicos-categoria');
    Route::get('produto-food-detalhe/{hash}', 'Delivery\\HomeController@produtoDetalhe')->name('food.produto-detalhe');
    Route::get('servico-food-detalhe/{hash}', 'Delivery\\HomeController@servicoDetalhe')->name('food.servico-detalhe');
    Route::get('food-pesquisa', 'Delivery\\HomeController@pesquisa')->name('food.pesquisa');
    Route::post('food-carrinho-adicionar', 'Delivery\\CarrinhoController@adicionar')->name('food.adicionar-carrinho');
    Route::post('food-carrinho-adicionar-servico', 'Delivery\\CarrinhoController@adicionarServico')->name('food.adicionar-carrinho-service');
    Route::get('food-carrinho', 'Delivery\\CarrinhoController@index')->name('food.carrinho');
    Route::get('food-carrinho-update', 'Delivery\\CarrinhoController@updateQuantidades')->name('food.carrinho-update');
    Route::delete('remove-item-food/{id}', 'Delivery\\CarrinhoController@removeItem')->name('food.remove-item');
    Route::get('food-pagamento', 'Delivery\\PagamentoController@index')->name('food.pagamento');
    Route::get('food-auth', 'Delivery\\ClienteController@auth')->name('food.auth');
    Route::post('food-endereco-store', 'Delivery\\ClienteController@enderecoStore')->name('food.endereco-store');
    Route::post('food-finalizar', 'Delivery\\PagamentoController@finalizar')->name('food.finalizar-pagamento');
    Route::get('pizza-food-detalhe', 'Delivery\\HomeController@pizzaDetalhe')->name('food.pizza-detalhe');
    Route::get('conta', 'Delivery\\ClienteController@conta')->name('food.conta');
    Route::post('food-endereco-update', 'Delivery\\ClienteController@enderecoUpdate')->name('food.endereco-update');
    Route::get('food-carrinho-pedir-novamente/{id}', 'Delivery\\PagamentoController@pedirNovamente')->name('food.carrinho-pedir-novamente');
    Route::get('food-aguardando-confirmar', 'Delivery\\PagamentoController@aguardandoConfirmar')->name('food.aguardando-confirmar');
    Route::get('food-logoff', 'Delivery\\ClienteController@logoff')->name('food.logoff');
    Route::get('food-qr_code/{transacao_id}', 'Delivery\\PagamentoController@qrCode')->name('food.qr_code');
    Route::post('food-pagamento-pix', 'Delivery\\PagamentoController@pagamentoPix')->name('food.pagamento-pix');
    Route::post('food-pagamento-cartao', 'Delivery\\PagamentoController@pagamentoCartao')->name('food.pagamento-cartao');
});

Route::middleware(['validaEcommerce'])->group(function () {
    Route::get('loja', 'Ecommerce\\HomeController@index')->name('loja.index');
    Route::get('loja-produtos-categoria/{hash}', 'Ecommerce\\HomeController@produtosDaCategoria')->name('loja.produtos-categoria');
    Route::get('produto-ecommerce-detalhe/{hash}', 'Ecommerce\\HomeController@produtoDetalhe')->name('loja.produto-detalhe');
    Route::post('carrinho-adicionar', 'Ecommerce\\CarrinhoController@adicionar')->name('loja.adicionar-carrinho');
    Route::get('carrinho', 'Ecommerce\\CarrinhoController@index')->name('loja.carrinho');
    Route::delete('remove-item-loja/{id}', 'Ecommerce\\CarrinhoController@removeItem')->name('loja.remove-item');
    Route::put('loja-atualiza-quantidade/{id}', 'Ecommerce\\CarrinhoController@atualizaQuantidade')->name('loja.atualiza-quantidade');
    Route::post('carrinho-setar-frete', 'Ecommerce\\CarrinhoController@setarFrete')->name('loja.carrinho-setar-frete');
    Route::get('loja-cadastro', 'Ecommerce\\ClienteController@cadastro')->name('loja.cadastro');
    Route::post('loja-cadastro', 'Ecommerce\\ClienteController@cadastroStore')->name('loja.cadastro');
    Route::get('loja-pesquisa', 'Ecommerce\\HomeController@pesquisa')->name('loja.pesquisa');
    Route::get('loja-pagamento', 'Ecommerce\\PagamentoController@index')->name('loja.pagamento');

    Route::get('loja-politica-privacidade', 'Ecommerce\\HomeController@politicaPrivacidade')->name('loja.politica-privacidade');
    Route::get('loja-minha-conta', 'Ecommerce\\ClienteController@minhaConta')->name('loja.minha-conta');
    Route::put('loja-update-cliente/{id}', 'Ecommerce\\ClienteController@update')->name('loja.update-cliente');
    Route::post('loja-store-endereco', 'Ecommerce\\ClienteController@storeEndereco')->name('loja.store-endereco');
    Route::get('loja-logoff', 'Ecommerce\\ClienteController@logoff')->name('loja.logoff');
    Route::get('loja-login', 'Ecommerce\\ClienteController@login')->name('loja.login');
    Route::post('loja-auth', 'Ecommerce\\ClienteController@auth')->name('loja.login-auth');
    Route::post('loja-pagamento-pix', 'Ecommerce\\PagamentoController@pagamentoPix')->name('loja.pagamento-pix');
    Route::post('loja-pagamento-novo-pix', 'Ecommerce\\PagamentoController@pagamentoNovoPix')->name('loja.pagamento-novo-pix');
    Route::post('loja-pagamento-boleto', 'Ecommerce\\PagamentoController@pagamentoBoleto')->name('loja.pagamento-boleto');
    Route::post('loja-pagamento-cartao', 'Ecommerce\\PagamentoController@pagamentoCartao')->name('loja.pagamento-cartao');
    Route::post('loja-pagamento-deposito', 'Ecommerce\\PagamentoController@pagamentoDeposito')->name('loja.pagamento-deposito');
    Route::get('loja-finalizar', 'Ecommerce\\PagamentoController@finalizar')->name('loja.finalizar');
    Route::get('loja-finalizar-deposito', 'Ecommerce\\PagamentoController@finalizarDeposito')->name('loja.finalizar-deposito');
    Route::get('loja-nova-chavepix', 'Ecommerce\\PagamentoController@novaChavePix')->name('loja.nova-chavepix');
    Route::post('loja-enviar-comprovante', 'Ecommerce\\PagamentoController@enviarComprovante')->name('loja.enviar-comprovante');
});

Route::middleware(['authh', 'validaEmpresa'])->group(function () {
    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

    Route::middleware(['verificaAdminParcial'])->group(function () {
        Route::get('/gerenciar-planos', 'GerenciarPlanoController@index')->name('gerenciar.planos.index');
        Route::get('/ticket-super', 'TicketSuperController@index')->name('ticket-super.index');
    });

    Route::get('/conciliacao', [ExtratoController::class, 'conciliar'])->name('extrato.conciliar');
    Route::post('/conciliacao', [ExtratoController::class, 'conciliar'])->name('extrato.conciliar.post');
    Route::post('/conciliacao/vincular', [ExtratoController::class, 'vincular'])->name('extrato.vincular');
    Route::post('/conciliacao/desvincular', [ExtratoController::class, 'desvincular'])->name('extrato.desvincular');
    Route::get('/movimentacao_bancaria', [ExtratoController::class, 'movimentacao_bancaria'])->name('extrato.movimentacao_bancaria');
    Route::post('/conciliacao/criar_conta', [ExtratoController::class, 'criar_conta'])->name('extrato.criar_conta');
    Route::get('/conciliacao/finalizar', [ExtratoController::class, 'finalizar'])->name('extrato.finalizar');
    Route::post('/conciliacao/excedente', [ExtratoController::class, 'excedente'])->name('extrato.excedente');
    Route::post('/conciliacao/excluir_conta', [ExtratoController::class, 'excluir_conta'])->name('extrato.excluir_conta');
    Route::get('/conciliacao/ignorar_transacao', [ExtratoController::class, 'ignorar_transacao'])->name('extrato.ignorar_transacao');
    Route::post('/conciliacao/movimentar', [ExtratoController::class, 'transferir_conta'])->name('extrato.movimentar');

    Route::get('/empresaPorCnpj/{cnpj}', [EmpresaController::class, 'findByCnpj']);
        Route::get('/confirm-email/{token}', [\App\Http\Controllers\EmailConfirmationController::class, 'confirm'])
        ->name('confirm.email');
    Route::post('/usuarios/{id}/resend-confirmation', [UsuarioController::class, 'resendConfirmation'])
        ->name('usuarios.resend.confirmation');
    Route::middleware(['auth', 'nocache'])->group(function () {
        Route::resource('usuarios', App\Http\Controllers\UsuarioController::class);
    });


    Route::middleware(['verificaMaster'])->group(function () {
        Route::get('/nfe-all', [App\Http\Controllers\HomeController::class, 'nfe'])->name('nfe-all');
        Route::get('/nfce-all', [App\Http\Controllers\HomeController::class, 'nfce'])->name('nfce-all');
        Route::resource('empresas', 'EmpresaController');
        Route::resource('contadores', 'ContadorController');
        Route::get('contadores-financeiro/{id}', 'ContadorController@financeiro')->name('contadores.financeiro');
        Route::get('contadores-financeiro-create/{id}', 'ContadorController@createFinanceiro')->name('contadores.financeiro-create');
        Route::post('contadores-financeiro-store/{id}', 'ContadorController@storeFinanceiro')->name('contadores.financeiro-store');
        Route::put('contadores-add-business/{id}', 'ContadorController@addBusiness')->name('contadores.add-business');
        Route::delete('contadores-destroy-business/{id}', 'ContadorController@destroyBusiness')->name('contadores.destroy-business');
        Route::delete('contadores-destroy-financeiro/{id}', 'ContadorController@destroyFincanceiro')->name('contadores-financeiro.destroy');

        Route::resource('planos', 'PlanoController');
        Route::resource('padroes-etiqueta', 'PadraoEtiquetaController');
        Route::resource('segmentos', 'SegmentoController');
        Route::resource('gerenciar-planos', 'GerenciarPlanoController');
        Route::resource('ticket-super', 'TicketSuperController');
        Route::delete('ticket-super-destroy-select', 'TicketSuperController@destroySelecet')->name('ticket-super.destroy-select');

        Route::put('ticket-super-add-mensagem/{id}', 'TicketSuperController@addMensagem')->name('ticket-super.add-mensagem');
        Route::put('ticket-super-update-status/{id}', 'TicketSuperController@updateStatus')->name('ticket-super.update-status');
        Route::delete('ticket-super-destroy-mensagem/{id}', 'TicketSuperController@destroyMensagem')->name('ticket-super.destroy-mensagem');

        Route::resource('planos-pendentes', 'PlanoPendenteController');
        Route::resource('configuracao-super', 'ConfiguracaoSuperController');
        Route::resource('cidades', 'CidadeController');
        Route::resource('bairros-super', 'BairroSuperController');
        Route::resource('video-suporte', 'VideoSuporteController');
        Route::resource('financeiro-plano', 'FinanceiroPlanoController');

        Route::group(['prefix' => '/update-sql'], function () {
            Route::get('/', 'UpdateController@index');
            Route::get('/update', 'UpdateController@update');
            Route::post('/sql', 'UpdateController@sqlStore');
            Route::post('/run-sql', 'UpdateController@runSql');
        });

        Route::resource('ncm', 'NcmController');
        Route::resource('notificacao-super', 'NotificacaoSuperController');
        Route::delete('notificacao-super-destroy-select', 'NotificacaoSuperController@destroySelecet')->name('notificacao-super.destroy-select');

        Route::resource('ibpt', 'IbptController');
        Route::resource('permissions', 'PermissionController');
        Route::get('/permissions-update', 'PermissionController@updateAll')->name('permissions.update');

        Route::resource('roles', 'RoleController');

        Route::group(['prefix' => 'relatorios-adm'], function () {
            Route::get('/', 'RelatorioAdmController@index')->name('relatorios-adm.index');
            Route::get('empresas', 'RelatorioAdmController@empresas')->name('relatorios-adm.empresas');
            Route::get('historico-acesso', 'RelatorioAdmController@historicoAcesso')->name('relatorios-adm.historico-acesso');
            Route::get('planos', 'RelatorioAdmController@planos')->name('relatorios-adm.planos');
            Route::get('certificados', 'RelatorioAdmController@certificados')->name('relatorios-adm.certificados');
        });
        
    });

    Route::resource('cidades', 'CidadeController');

    // rotas do contador
    Route::get('contador-set/{id}', 'ContadorAdminController@setEmpresa')->name('contador.set-empresa');
    Route::get('contador-empresa', 'ContadorAdminController@show')->name('contador.show');
    Route::put('contador-empresa-update/{id}', 'ContadorAdminController@update')->name('contador-empresa.update');
    Route::get('contador-empresa-produtos', 'ContadorAdminController@produtos')->name('contador-empresa.produtos');
    Route::get('contador-empresa-produtos/{id}', 'ContadorAdminController@produtoShow')->name('contador-empresa-produtos.show');
    Route::get('contador-empresa-clientes', 'ContadorAdminController@clientes')->name('contador-empresa.clientes');
    Route::get('contador-empresa-fornecedores', 'ContadorAdminController@fornecedores')->name('contador-empresa.fornecedores');

    Route::get('contador-empresa-create', 'ContadorAdminController@empresaCreate')->name('contador.empresa-create');
    Route::post('contador-empresa-store', 'ContadorAdminController@empresaStore')->name('contador.empresa-store');
    Route::get('contador-empresa-plano/{empresa_id}', 'ContadorAdminController@plano')->name('contador-empresa.plano');
    Route::put('contador-empresa-set-plano/{empresa_id}', 'ContadorAdminController@setPlano')->name('contador-empresa.set-plano');

    //nfe
    Route::get('contador-empresa-nfe', 'ContadorAdminNFeController@nfe')->name('contador-empresa.nfe');
    Route::get('contador-empresa-nfe-download/{id}', 'ContadorAdminNFeController@downloadNFe')->name('contador-empresa-nfe.download');
    Route::get('contador-empresa-nfe-danfe/{id}', 'ContadorAdminNFeController@danfe')->name('contador-empresa-nfe.danfe');
    Route::get('contador-empresa-nfe-zip', 'ContadorAdminNFeController@downloadZip')->name('contador-empresa-nfe-zip');

    //nfce
    Route::get('contador-empresa-nfce', 'ContadorAdminNFCeController@nfe')->name('contador-empresa.nfce');
    Route::get('contador-empresa-nfce-download/{id}', 'ContadorAdminNFCeController@downloadNFCe')->name('contador-empresa-nfce.download');
    Route::get('contador-empresa-nfce-danfce/{id}', 'ContadorAdminNFCeController@danfce')->name('contador-empresa-nfce.danfce');
    Route::get('contador-empresa-nfce-zip', 'ContadorAdminNFCeController@downloadZip')->name('contador-empresa-nfce-zip');

    //cte
    Route::get('contador-empresa-cte', 'ContadorAdminCTeController@cte')->name('contador-empresa.cte');
    Route::get('contador-empresa-cte-download/{id}', 'ContadorAdminCTeController@downloadCTe')->name('contador-empresa-cte.download');
    Route::get('contador-empresa-cte-dacte/{id}', 'ContadorAdminCTeController@dacte')->name('contador-empresa-cte.dacte');
    Route::get('contador-empresa-cte-zip', 'ContadorAdminCTeController@downloadZip')->name('contador-empresa-cte-zip');

    //cte
    Route::get('contador-empresa-mdfe', 'ContadorAdminMDFeController@mdfe')->name('contador-empresa.mdfe');
    Route::get('contador-empresa-mdfe-download/{id}', 'ContadorAdminMDFeController@downloadMDFe')->name('contador-empresa-mdfe.download');
    Route::get('contador-empresa-mdfe-dacte/{id}', 'ContadorAdminMDFeController@damdfe')->name('contador-empresa-mdfe.damdfe');
    Route::get('contador-empresa-mdfe-zip', 'ContadorAdminMDFeController@downloadZip')->name('contador-empresa-mdfe-zip');
    // rotas do contador fim

    Route::get('nfe/imprimir/{id}', 'NfeController@imprimir')->name('nfe.imprimir');
    Route::get('nfe-danfe-temporaria/{id}', 'NfeController@danfeTemporaria')->name('nfe.danfe-temporaria');
    Route::get('nfe/danfe-simples/{id}', 'NfeController@danfeSimples')->name('nfe.danfe-simples');
    Route::get('nfe/danfe-etiqueta/{id}', 'NfeController@danfeEtiqueta')->name('nfe.danfe-etiqueta');
    Route::get('nfe/danfe-etiqueta-correio/{id}', 'NfeController@danfeEtiquetaCorreio')->name('nfe.danfe-etiqueta-correio');
    Route::get('nfe/imprimir-cancela/{id}', 'NfeController@imprimirCancela')->name('nfe.imprimir-cancela');
    Route::get('nfe/imprimir-correcao/{id}', 'NfeController@imprimirCorrecao')->name('nfe.imprimir-correcao');
    Route::get('nfe/xml-temp/{id}', 'NfeController@xmlTemp')->name('nfe.xml-temp');
    Route::get('nfe-import-zip', 'NfeController@importZip')->name('nfe.import-zip');
    Route::post('nfe-import-zip-store', 'NfeController@importZipStore')->name('nfe.import-zip-store');
    Route::post('nfe-import-zip-store-files', 'NfeController@importZipStoreFiles')->name('nfe.import-zip-store-files');

    Route::get('nfce-import-zip', 'NfceController@importZip')->name('nfce.import-zip');
    Route::post('nfce-import-zip-store', 'NfceController@importZipStore')->name('nfce.import-zip-store');
    Route::post('nfce-import-zip-store-files', 'NfceController@importZipStoreFiles')->name('nfce.import-zip-store-files');

    Route::get('nfce/imprimir/{id}', 'NfceController@imprimir')->name('nfce.imprimir');
    Route::get('nfce-danfce-temporaria/{id}', 'NfceController@danfceTemporaria')->name('nfce.danfce-temporaria');

    Route::get('nfe/alterar-estado/{id}', 'NfeController@alterarEstado')->name('nfe.alterar-estado');
    Route::put('nfe/storeEstado/{id}', 'NfeController@storeEstado')->name('nfe.storeEstado');
    Route::get('nfe/imprimirVenda/{id}', 'NfeController@imprimirVenda')->name('nfe.imprimirVenda');
    Route::get('nfe-download-xml/{id}', 'NfeController@downloadXml')->name('nfe.download-xml');

    Route::delete('nfe/destroy-select', 'NfeController@destroySelect')->name('nfe.destroy-select');
    Route::delete('nfe/destroy-batch', 'NfeController@destroyBatch')->name('nfe.destroy-batch');

    Route::get('nfce/xml-temp/{id}', 'NfceController@xmlTemp')->name('nfce.xml-temp');
    Route::get('nfce-download-xml/{id}', 'NfceController@downloadXml')->name('nfce.download-xml');
    Route::get('cte/xml-temp/{id}', 'CteController@xmlTemp')->name('cte.xml-temp');
    Route::get('cte/imprimir/{id}', 'CteController@imprimir')->name('cte.imprimir');
    Route::get('cte/download/{id}', 'CteController@download')->name('cte.download');
    Route::get('cte/imprimir-cancela/{id}', 'CteController@imprimirCancela')->name('cte.imprimir-cancela');
    Route::get('cte/imprimir-correcao/{id}', 'CteController@imprimirCorrecao')->name('cte.imprimir-correcao');

    Route::get('cte-os/xml-temp/{id}', 'CteOsController@xmlTemp')->name('cte-os.xml-temp');
    Route::get('cte-os/alterar-estado/{id}', 'CteOsController@alterarEstado')->name('cte-os.alterar-estado');
    Route::put('cte-os/storeEstado/{id}', 'CteOsController@storeEstado')->name('cte-os.storeEstado');
    Route::get('cte-os/imprimir/{id}', 'CteOsController@imprimir')->name('cte-os.imprimir');
    Route::get('cte-os/download/{id}', 'CteOsController@download')->name('cte-os.download');
    Route::get('cte-os/imprimir-cancela/{id}', 'CteOsController@imprimirCancela')->name('cte-os.imprimir-cancela');

    Route::get('mdfe/xml-temp/{id}', 'MdfeController@xmlTemp')->name('mdfe.xml-temp');
    Route::get('mdfe/imprimir/{id}', 'MdfeController@imprimir')->name('mdfe.imprimir');
    Route::get('mdfe/download/{id}', 'MdfeController@download')->name('mdfe.download');
    Route::get('mdfe/imprimir-cancela/{id}', 'MdfeController@imprimirCancela')->name('mdfe.imprimir-cancela');
    Route::get('mdfe/imprimir-correcao/{id}', 'MdfeController@imprimirCorrecao')->name('mdfe.imprimir-correcao');
    Route::get('mdfe/nao-encerrados', 'MdfeController@naoEncerrados')->name('mdfe.nao-encerrados');
    Route::get('mdfe/encerrar', 'MdfeController@encerrar')->name('mdfe.encerrar');
    Route::get('mdfe/create-by-vendas/{id}', 'MdfeController@createByVendas')->name('mdfe.create.vendas');

    


    // Route::group(['prefix' => 'empresas'], function () {
    //     Route::get('/config/{id}', 'EmpresaController@config')->name('empresas.config');
    //     Route::get('/painel', 'EmpresaController@painel')->name('empresas.painel');
    // });

    Route::get('mercado-livre-get-code', 'MercadoLivreAuthController@getCode')->name('mercado-livre.get-code');
    Route::get('mercado-livre-auth-code', 'MercadoLivreAuthController@authCode')->name('mercado-livre.auth');
    Route::get('mercado-livre-auth-token', 'MercadoLivreAuthController@authToken')->name('mercado-livre.auth-token');
    Route::get('mercado-livre-refresh-token', 'MercadoLivreAuthController@refreshToken')->name('mercado-livre.refresh-token');
    Route::get('mercado-livre-get-users', 'MercadoLivreAuthController@getUsers')->name('mercado-livre.get-users');
    Route::resource('mercado-livre-config', 'MercadoLivreConfigController');
    Route::get('mercado-livre-categorias', 'MercadoLivreProdutoController@categorias')->name('mercado-livre.categorias');
    Route::get('mercado-livre-produtos-news', 'MercadoLivreProdutoController@produtosNew')->name('mercado-livre.produtos-news');
    Route::get('mercado-livre-produtos-galery/{id}', 'MercadoLivreProdutoController@galery')->name('mercado-livre-produtos.galery');
    Route::post('mercado-livre-produtos-galery-store', 'MercadoLivreProdutoController@galeryStore')->name('mercado-livre-produtos-galery-store');
    Route::get('mercado-livre-produtos-galery-delete', 'MercadoLivreProdutoController@galeryDelete')->name('mercado-livre-produtos.galery-delete');
    Route::resource('mercado-livre-produtos', 'MercadoLivreProdutoController');
    Route::resource('mercado-livre-perguntas', 'MercadoLivrePerguntaController');
    Route::resource('mercado-livre-pedidos', 'PedidoMercadoLivreController');
    Route::get('mercado-livre-nfe/{id}', 'PedidoMercadoLivreController@gerarNfe')->name('mercado-livre-pedidos.gerar-nfe');
    Route::put('mercado-livre-pedido-set-cliente/{id}', 'PedidoMercadoLivreController@setCliente')->name('mercado-livre-pedidos.set-cliente');

    Route::get('mercado-livre-chat/{id}', 'PedidoMercadoLivreController@chat')->name('mercado-livre-pedidos.chat');
    Route::get('mercado-livre-chat-download/{id}', 'PedidoMercadoLivreController@downloadChat')->name('mercado-livre-pedidos.chat-download');
    Route::post('mercado-livre-chat-send/{id}', 'PedidoMercadoLivreController@chatSend')->name('mercado-livre-chat.send');
    Route::get('mercado-livre-chat-send-nfe/{id}', 'PedidoMercadoLivreController@chatSendNfe')->name('mercado-livre-chat.send-nfe');

    Route::resource('upgrade', 'UpgradePlanoController');
    Route::resource('plano-contas', 'PlanoContaController');
    Route::post('plano-contas-start', 'PlanoContaController@start')->name('plano-contas.start');

    Route::resource('nuvem-shop-auth', 'NuvemShopAuthController');
    Route::get('nuvem-shop-auth-code', 'NuvemShopAuthController@code');
    Route::resource('nuvem-shop-config', 'NuvemShopConfigController');
    Route::resource('nuvem-shop-categorias', 'NuvemShopCategoriaController');
    Route::resource('nuvem-shop-pedidos', 'NuvemShopPedidoController');
    Route::resource('nuvem-shop-produtos', 'NuvemShopProdutoController');
    Route::get('nuvem-shop-gerar-nfe/{id}', 'NuvemShopPedidoController@gerarNfe')->name('nuvem-shop-pedidos.gerar-nfe');
    Route::get('nuvem-shop-produtos-galery/{id}', 'NuvemShopProdutoController@galery')->name('nuvem-shop-produtos.galery');
    Route::get('nuvem-shop-produtos-galery-delete', 'NuvemShopProdutoController@galeryDelete')->name('nuvem-shop-produtos.galery-delete');
    Route::post('nuvem-shop-produtos-galery-store', 'NuvemShopProdutoController@galeryStore')->name('nuvem-shop-produtos-galery-store');

    Route::resource('contas-financeiras', 'ContaFinanceiraController');
    Route::resource('contas-empresa', 'ContaEmpresaController');
    Route::resource('contas-boleto', 'ContaBoletoController');
    Route::resource('remessa-boleto', 'RemessaBoletoController');
    Route::get('remessa-boleto-download/{id}', 'RemessaBoletoController@download')->name('remessa-boleto.download');
    Route::get('remessa-boleto-import', 'RemessaBoletoController@import')->name('remessa-boleto.import');
    Route::post('remessa-boleto-import-store', 'RemessaBoletoController@importStore')->name('remessa-boleto.import-store');
    Route::post('remessa-boleto-import-save', 'RemessaBoletoController@importSave')->name('remessa-boleto.import-save');

    Route::get('boletos', 'BoletoController@index')->name('boleto.index');
    Route::get('boleto-show/{id}', 'BoletoController@show')->name('boleto.show');
    Route::get('boleto-print/{id}', 'BoletoController@print')->name('boleto.print');
    Route::get('boleto-create/{conta_id}', 'BoletoController@create')->name('boleto.create');
    Route::get('boleto-create-several', 'BoletoController@createSeveral')->name('boleto.create-several');
    Route::post('boleto-store', 'BoletoController@store')->name('boleto.store');
    Route::delete('boleto-destroy/{id}', 'BoletoController@destroy')->name('boleto.destroy');

    Route::resource('ticket', 'TicketController');
    Route::put('ticket-add-mensagem/{id}', 'TicketController@addMensagem')->name('ticket.add-mensagem');

    Route::resource('cash-back-config', 'CashBackConfigController');
    Route::resource('usuario-super', 'UsuarioSuperController');
	
	
	Route::get('/plano/contratar/{plano_id}', function ($plano_id) {
		$id_empresa = auth()->user()->empresa->empresa->id;

		$response = Http::withHeaders([
			'INTEGRATION_TOKEN' => 'MDg3OTY1NTAwMDAxNzA='
		])->post("https://pagamento.matriza.net/api/payments/bank_slip", [
			'id_plano' => $plano_id,
			'id_empresa' => $id_empresa,
		]);

		$data = $response->json();

		if (isset($data['data']['url'])) {
			return redirect()->away($data['data']['url']);
		}

		return back()->with('error', 'Erro ao gerar o link de pagamento.');
	});
	
Route::get('/plano/pagar/{plano_id}', function ($plano_id) {
    $empresa_id = request()->get('empresa');
	$forma_pagamento = request()->get('forma_pagamento');
    $forma_pagamento = $forma_pagamento === 'pix' ? 'pix' : 'bank_slip';

    if (!$empresa_id) {
        return back()->with('error', 'Empresa não informada.');
    }

    $response = Http::withHeaders([
        'INTEGRATION_TOKEN' => 'MDg3OTY1NTAwMDAxNzA='
    ])->post("https://pagamento.matriza.net/api/payments/".$forma_pagamento, [
        'id_plano' => $plano_id,
        'id_empresa' => $empresa_id,
    ]);

    $data = $response->json();

    if (isset($data['data']['url'])) {
        return redirect()->away($data['data']['url']);
    }

    return back()->with('error', 'Erro ao gerar o link de pagamento.');
});

    Route::middleware(['verificaEmpresa', 'validaPlano'])->group(function () {
        Route::resource('nfe', 'NfeController');
        Route::resource('nfe-xml', 'NfeXmlController');
        Route::get('nfe-xml-download', 'NfeXmlController@download')->name('nfe-xml.download');
        Route::delete('nfe/destroy-batch', 'NfeController@destroyBatch')->name('nfe.destroy-batch');

        Route::get('/relatorios/centro-custo', [RelatorioController::class, 'centroCusto'])->name('relatorios.centro-custo');


        Route::resource('nfe-entrada-xml', 'NfeEntradaXmlController');
        Route::get('nfe-entrada-xml-download', 'NfeEntradaXmlController@download')->name('nfe-entrada-xml.download');

        Route::resource('nfce-xml', 'NfceXmlController');
        Route::get('nfce-xml-download', 'NfceXmlController@download')->name('nfce-xml.download');

        Route::resource('cte-xml', 'CteXmlController');
        Route::get('cte-xml-download', 'CteXmlController@download')->name('cte-xml.download');

        Route::resource('mdfe-xml', 'MdfeXmlController');
        Route::get('mdfe-xml-download', 'MdfeXmlController@download')->name('mdfe-xml.download');

        Route::get('nfe-inutilizar', 'NfeController@inutilizar')->name('nfe.inutilizar');
        Route::post('nfe-inutilizar-store', 'NfeController@inutilStore')->name('nfe-inutilizar.store');
        Route::post('nfce-inutilizar-store', 'NfceController@inutilStore')->name('nfce-inutilizar.store');
        Route::delete('nfe-inutilizar-destroy/{id}', 'NfeController@inutilDestroy')->name('nfe-inutilizar.destroy');
        Route::get('nfce-inutilizar', 'NfceController@inutilizar')->name('nfce.inutilizar');
        Route::get('nfce/alterar-estado/{id}', 'NfceController@alterarEstado')->name('nfce.alterar-estado');
        Route::put('nfce/storeEstado/{id}', 'NfceController@storeEstado')->name('nfce.storeEstado');

        Route::resource('nfce', 'NfceController');
        Route::resource('controle-acesso', 'ControleAcessoController');

        Route::get('/compras/info-validade/{id}', 'CompraController@infoValidade')->name('compras.info-validade');
        Route::post('/compras/setar-info-validade', 'CompraController@setarInfoValidade')->name('compras.setar-info-validade');
        Route::get('/compras-etiqueta/{id}', 'CompraController@etiqueta')->name('compras.etiqueta');
        Route::post('/compras-etiqueta-store/{id}', 'CompraController@etiquetaStore')->name('compras.etiqueta-store');

        Route::resource('compras', 'CompraController');
        Route::resource('cotacoes', 'CotacaoController');
        Route::get('/compras-purchase/{id}', 'CotacaoController@purchase')->name('cotacoes.purchase');

        Route::get('/compras-xml', 'CompraController@xml')->name('compras.xml');
        Route::post('/store-xml', 'CompraController@storeXml')->name('compras.store-xml');
        Route::post('/compras-finish-xml', 'CompraController@finishXml')->name('compras.finish-xml');

        Route::resource('devolucao', 'DevolucaoController');
        Route::resource('localizacao', 'LocalizacaoController');
        Route::get('/devolucao-xml', 'DevolucaoController@xml')->name('devolucao.xml');
        Route::post('/devolucao-store-xml', 'DevolucaoController@storeXml')->name('devolucao.store-xml');
        Route::post('/devolucao-finish-xml', 'DevolucaoController@finishXml')->name('devolucao.finish-xml');

        Route::group(['prefix' => 'manifesto'], function () {
            Route::get('/novaConsulta', 'ManifestoController@novaconsulta')->name('manifesto.novaConsulta');
            Route::get('/download/{id}', 'ManifestoController@download')->name('manifesto.download');
            Route::get('/danfe/{id}', 'ManifestoController@danfe')->name('manifesto.danfe');
            Route::post('/manifestar', 'ManifestoController@manifestar')->name('manifesto.manifestar');
        });
        Route::resource('manifesto', 'ManifestoController');

        Route::resource('nota-servico', 'NotaServicoController');
        Route::resource('nota-servico-config', 'NotaServicoConfigController');
        Route::get('nota-servico-config-certificado', 'NotaServicoConfigController@certificado')->name('nota-servico-config.certificado');
        Route::post('nota-servico-config-upload-certificado', 'NotaServicoConfigController@uploadCertificado')->name('nota-servico-config.upload-certificado');

        Route::get('nota-servico-imprimir/{id}', 'NotaServicoController@imprimir')->name('nota-servico.imprimir');
        Route::get('nota-servico-preview/{id}', 'NotaServicoController@preview')->name('nota-servico.preview');

        Route::get('cte-inutilizar', 'CteController@inutilizar')->name('cte.inutilizar');
        Route::post('cte-inutilizar-store', 'CteController@inutilStore')->name('cte-inutilizar.store');
        Route::delete('cte-inutilizar-destroy/{id}', 'CteController@inutilDestroy')->name('cte-inutilizar.destroy');
        Route::get('cte/alterar-estado/{id}', 'CteController@alterarEstado')->name('cte.alterar-estado');
        Route::put('cte/storeEstado/{id}', 'CteController@storeEstado')->name('cte.storeEstado');
        Route::resource('cte', 'CteController');

        Route::resource('veiculos', 'VeiculoController');
        Route::resource('cte-os', 'CteOsController');
        Route::resource('cupom-desconto', 'CupomDescontoController');
        Route::resource('bairros-empresa', 'BairroEmpresaController');
        Route::get('bairros-empresa-super', 'BairroEmpresaController@super')->name('bairros-empresa.super');
        Route::post('bairros-empresa-super', 'BairroEmpresaController@setBairros')->name('bairros-empresa.super');

        Route::resource('mdfe', 'MdfeController');
        Route::get('mdfe-inutilizar', 'MdfeController@inutilizar')->name('mdfe.inutilizar');
        Route::post('mdfe-inutilizar-store', 'MdfeController@inutilStore')->name('mdfe-inutilizar.store');
        Route::delete('mdfe-inutilizar-destroy/{id}', 'MdfeController@inutilDestroy')->name('mdfe-inutilizar.destroy');
        Route::get('mdfe/alterar-estado/{id}', 'MdfeController@alterarEstado')->name('mdfe.alterar-estado');
        Route::put('mdfe/storeEstado/{id}', 'MdfeController@storeEstado')->name('mdfe.storeEstado');

        Route::resource('clientes', 'ClienteController');
        Route::delete('clientes-destroy-select', 'ClienteController@destroySelecet')->name('clientes.destroy-select');
        Route::get('clientes-cash-back/{id}', 'ClienteController@cashBack')->name('clientes.cash-back');
        Route::get('clientes-import', 'ClienteController@import')->name('clientes.import');
        Route::get('clientes-historico/{id}', 'ClienteController@historico')->name('clientes.historico');
        Route::get('clientes-import-download', 'ClienteController@downloadModelo')->name('clientes.import-download');
        Route::post('clientes-import-store', 'ClienteController@storeModelo')->name('clientes.import-store');

        Route::resource('fornecedores', 'FornecedorController');
        Route::delete('fornecedores-destroy-select', 'FornecedorController@destroySelecet')->name('fornecedores.destroy-select');

        Route::get('fornecedores-import', 'FornecedorController@import')->name('fornecedores.import');
        Route::get('fornecedores-import-download', 'FornecedorController@downloadModelo')
            ->name('fornecedores.import-download');
        Route::post('fornecedores-import-store', 'FornecedorController@storeModelo')->name('fornecedores.import-store');
        Route::get('fornecedores-historico/{id}', 'FornecedorController@historico')->name('fornecedores.historico');

        Route::get('produtos-gerar-codigo-ean', 'ProdutoController@gerarCodigoEan');
        Route::get('produtos-reajuste', 'ProdutoController@reajuste')->name('produtos.reajuste');
        Route::post('produtos-reajuste-update', 'ProdutoController@reajusteUpdate')->name('produtos-reajuste.update');

        Route::resource('transportadoras', 'TransportadoraController');
        Route::delete('transportadoras-destroy-select', 'TransportadoraController@destroySelecet')->name('transportadoras.destroy-select');

        Route::resource('transferencia-estoque', 'TransferenciaEstoqueController');
        Route::get('transferencia-estoque-imprimir/{id}', 'TransferenciaEstoqueController@imprimir')->name('transferencia-estoque.imprimir');

        Route::resource('estoque', 'EstoqueController');
        Route::get('estoque-localizacao-define/{id}', 'EstoqueLocalizacaoController@define')->name('estoque-localizacao.define');
        Route::put('estoque-localizacao-store/{id}', 'EstoqueLocalizacaoController@store')->name('estoque-localizacao.store');
        Route::resource('categoria-produtos', 'CategoriaProdutoController');
        Route::delete('categoria-produtos-destroy-select', 'CategoriaProdutoController@destroySelecet')->name('categoria-produtos.destroy-select');

        Route::resource('produtos', 'ProdutoController');
        Route::delete('produtos-destroy-select', 'ProdutoController@destroySelecet')->name('produtos.destroy-select');

        Route::resource('variacoes', 'VariacaoController');
        Route::delete('variacoes-destroy-select', 'VariacaoController@destroySelecet')->name('variacoes.destroy-select');

        Route::get('/movimentacao/{id}', 'ProdutoController@movimentacao')->name('produtos.movimentacao');
        Route::get('/duplicar/{id}', 'ProdutoController@duplicar')->name('produtos.duplicar');
        Route::get('/remove-image/{id}', 'ProdutoController@removeImagem')->name('produtos.remove-image');
        Route::get('/produtos-galeria/{id}', 'ProdutoController@galeria')->name('produtos.galeria');
        Route::post('/produtos-galeria-store/{id}', 'ProdutoController@storeImage')->name('produtos.galeria-store');
        Route::delete('/produtos-galeria-destroy/{id}', 'ProdutoController@destroyImage')->name('produtos.destroy-image');

        Route::get('/produto-find-ml/{id}', 'ProdutoController@findProdutoMercadoLivre');
        Route::get('/produto-etiqueta/{id}', 'ProdutoController@etiqueta')->name('produtos.etiqueta');
        Route::post('/produto-etiqueta-store/{id}', 'ProdutoController@etiquetaStore')->name('produtos.etiqueta-store');

        Route::resource('marcas', 'MarcaController');
        Route::resource('modelo-etiquetas', 'ModeloEtiquetaController');
        Route::get('/modelo-etiquetas-importar', 'ModeloEtiquetaController@importar')->name('modelo-etiquetas.import');

        Route::delete('marcas-destroy-select', 'MarcaController@destroySelecet')->name('marcas.destroy-select');

        Route::group(['prefix' => 'produto-composto'], function () {
            Route::get('/create/{id}', 'ProdutoCompostoController@create')->name('produto-composto.create');
            // Route::get('/create_item/{id}', 'ProdutoController@createItem')->name('produtosComposto.create_item');
            Route::post('/store/{id}', 'ProdutoCompostoController@store')->name('produto-composto.store');
            Route::delete('/destroy/{id}', 'ProdutoCompostoController@destroy')->name('produto-composto.destroy');
            Route::get('/show/{id}', 'ProdutoCompostoController@show')->name('produto-composto.show');
        });

        Route::resource('apontamento', 'ApontamentoController');

        Route::get('/imprimir/{id}', 'ApontamentoController@imprimir')->name('apontamento.imprimir');

        Route::get('produtos-import', 'ProdutoController@import')->name('produtos.import');
        Route::get('produtos-tamanho-pizza/{id}', 'ProdutoCardapioController@tamanhosPizza')->name('produtos.tamanho-pizza');
        Route::put('produtos-tamanho-pizza/{id}', 'ProdutoCardapioController@setValoresTamnho')->name('produtos.setar-tamanhos-valores');
        Route::get('produtos-import-download', 'ProdutoController@downloadModelo')->name('produtos.import-download');
        Route::post('produtos-import-store', 'ProdutoController@storeModelo')->name('produtos.import-store');

        Route::resource('produtopadrao-tributacao', 'PadraoTributacaoProdutoController');
        Route::delete('produtopadrao-tributacao-destroy-select', 'PadraoTributacaoProdutoController@destroySelecet')->name('produtopadrao-tributacao.destroy-select');

        Route::get('produtopadrao-tributacao-alterar', 'PadraoTributacaoProdutoController@alterarProdutos')
            ->name('produtopadrao-tributacao.alterar');
        Route::post('produtopadrao-tributacao-set-tributacao', 'PadraoTributacaoProdutoController@setTributacao')
            ->name('produtopadrao-tributacao.set-tributacao');

        Route::group(['prefix' => 'funcionarios'], function () {
            Route::get('/atribuir/{id}', 'FuncionarioController@atribuir')->name('funcionarios.atribuir');
            Route::post('/atribuir-servico', 'FuncionarioController@atribuirServico')->name('funcionarios.atribuir-servico');
            Route::delete('/deletarAtribuicao/{id}', 'FuncionarioController@deletarAtribuicao')->name('funcionarios.deletarAtribuicao');
        });

        Route::resource('funcionarios', 'FuncionarioController');
        Route::resource('lista-preco', 'ListaPrecoController');
        Route::delete('lista-preco-destroy-select', 'ListaPrecoController@destroySelecet')->name('lista-preco.destroy-select');
        Route::post('/lista-preco-update-item', 'ListaPrecoController@updateItem')->name('lista-preco.update-item');

        Route::resource('evento-funcionarios', 'EventoFuncionarioController');
        Route::delete('evento-funcionarios-destroy-select', 'EventoFuncionarioController@destroySelecet')->name('evento-funcionarios.destroy-select');

        Route::resource('funcionario-eventos', 'FuncionarioEventoController');
        Route::resource('apuracao-mensal', 'ApuracaoMensalController');

        Route::get('/apuracao-mensal/get-eventos/{funcionario_id}', 'ApuracaoMensalController@getEventos')->name('apuracao-mensal.get-eventos');
        Route::get('/apuracao-mensal/conta-pagar/{apuracao_id}', 'ApuracaoMensalController@contaPagar')->name('apuracao-mensal.conta-pagar');

        Route::put('/apuracao-mensal/SetConta/{id}', 'ApuracaoMensalController@setConta')->name('apuracao-mensal.set-conta');

        Route::group(['prefix' => 'usuarios'], function () {
            Route::get('profile/{id}', 'UsuarioController@profile')->name('usuarios.profile');
        });

        Route::resource('usuarios', 'UsuarioController');
        Route::resource('natureza-operacao', 'NaturezaOperacaoController');
        Route::resource('centro-custo', 'CentroCustoController');
        Route::resource('conta-pagar', 'ContaPagarController');
        Route::delete('conta-pagar-destroy-select', 'ContaPagarController@destroySelecet')->name('conta-pagar.destroy-select');
        Route::get('conta-pagar-download-file/{id}', 'ContaPagarController@downloadFile')->name('conta-pagar.download-file');

        Route::resource('conta-receber', 'ContaReceberController');
        Route::delete('conta-receber-destroy-select', 'ContaReceberController@destroySelecet')->name('conta-receber.destroy-select');
        Route::get('conta-receber-download-file/{id}', 'ContaReceberController@downloadFile')->name('conta-receber.download-file');

        Route::resource('produtos-cardapio', 'ProdutoCardapioController');
        Route::resource('produtos-delivery', 'ProdutoDeliveryController');
        Route::resource('servicos-marketplace', 'ServicoMarketPlaceController');
        Route::resource('produtos-reserva', 'ProdutoReservaController');
        Route::resource('produtos-ecommerce', 'ProdutoEcommerceController');
        Route::resource('pedidos-ecommerce', 'PedidoEcommerceController');

        Route::get('/pedidos-ecommerce-alterar-estado/{id}', 'PedidoEcommerceController@alterarEstado')->name('pedidos-ecommerce.alterar-estado');
        Route::get('/pedidos-ecommerce-nfe/{id}', 'PedidoEcommerceController@gerarNfe')->name('pedidos-ecommerce.gerar-nfe');

        Route::resource('clientes-delivery', 'ClienteDeliveryController');
        Route::get('/clientes-delivery-enderecos/{id}', 'ClienteDeliveryController@enderecos')
            ->name('clientes-delivery.enderecos');

        Route::resource('notificacao', 'NotificacaoController');
        Route::get('notificacao-clear-all', 'NotificacaoController@clearAll')->name('notificacao.clear-all');

        Route::resource('config-cardapio', 'ConfigCardapioController');
        Route::resource('config-marketplace', 'MarketPlaceConfigController');
        Route::get('/config-marketplace-loja', 'MarketPlaceConfigController@verLoja')
            ->name('config-marketplace.loja');
        Route::resource('config-ecommerce', 'EcommerceConfigController');
        Route::get('/config-ecommerce-site', 'EcommerceConfigController@verSite')
            ->name('config-ecommerce.site');

        Route::get('/produtos-cardapio-ingredientes/{id}', 'ProdutoCardapioController@ingredientes')
            ->name('produtos-cardapio.ingredientes');

        Route::resource('avaliacao-cardapio', 'AvaliacaoCardapioController');
        Route::resource('tamanhos-pizza', 'TamanhoPizzaController');
        Route::resource('carrossel', 'CarrosselCardapioController');
        Route::resource('destaque-marketplace', 'DestaqueMarketPlaceController');
        Route::resource('motoboys', 'MotoboyController');
        Route::get('/motoboys-comissao', 'MotoboyController@comissao')->name('motoboys-comissao.index');
        Route::post('/motoboys-comissao-pay-multiple', 'MotoboyController@payMultiple')->name('motoboys-comissao.pay-multiple');
        Route::resource('pedidos-cardapio', 'PedidoCardapioController');
        Route::resource('pedido-cozinha', 'PedidoCozinhaController');

        Route::resource('pedidos-delivery', 'PedidoDeliveryController');
        Route::get('/pedido-delivery-altera-status', 'PedidoDeliveryController@alteraStatus')->name('pedido-delivery.altera-status');
        Route::post('/pedidos-delivery-store-item/{id}', 'PedidoDeliveryController@storeItem')->name('pedidos-delivery.store-item');
        Route::get('/pedidos-delivery-print/{id}', 'PedidoDeliveryController@print')->name('pedidos-delivery.print');
        Route::delete('/pedidos-delivery-destroy-item/{id}', 'PedidoDeliveryController@destroyItem')
            ->name('pedidos-delivery.destroy-item');
        Route::get('/pedidos-delivery-finish/{id}', 'PedidoDeliveryController@finish')->name('pedidos-delivery.finish');
        Route::post('/pedidos-delivery-set-endereco/{id}', 'PedidoDeliveryController@setEndereco')
            ->name('pedidos-delivery.set-endereco');
        Route::post('/pedidos-delivery-store-endereco/{id}', 'PedidoDeliveryController@storeEndereco')
            ->name('pedidos-delivery.store-endereco');
        Route::get('pedidos-delivery-update-item/{id}', 'PedidoDeliveryController@updateItem')->name('pedidos-delivery.update-item');

        Route::get('/pedido-cozinha-update-item/{id}', 'PedidoCozinhaController@updateItem')->name('pedido-cozinha.update-item');
        Route::get('/pedidos-cardapio-finish/{id}', 'PedidoCardapioController@finish')->name('pedidos-cardapio.finish');
        Route::get('/pedidos-cardapio-print/{id}', 'PedidoCardapioController@print')->name('pedidos-cardapio.print');
        Route::post('/pedidos-cardapio-store-item/{id}', 'PedidoCardapioController@storeItem')->name('pedidos-cardapio.store-item');
        Route::delete('/pedidos-cardapio-destroy-item/{id}', 'PedidoCardapioController@destroyItem')
            ->name('pedidos-cardapio.destroy-item');

        Route::resource('adicionais', 'AdicionalController');
        Route::get('/produtos-cardapio-categorias', 'ProdutoCardapioController@categorias')
            ->name('produtos-cardapio.categorias');
        Route::get('/produtos-delivery-categorias', 'ProdutoDeliveryController@categorias')
            ->name('produtos-delivery.categorias');

        Route::get('/categoria-servico-marketplace', 'ServicoMarketPlaceController@categorias')
            ->name('servico-marketplace.categorias');

        Route::get('/produtos-ecommerce-categorias', 'ProdutoEcommerceController@categorias')
            ->name('produtos-ecommerce.categorias');

        Route::post('/produtos-cardapio-store-adicional', 'ProdutoCardapioController@storeAdicional')
            ->name('produtos-cardapio.store-adicional');
        Route::post('/produtos-cardapio-store-ingrediente', 'ProdutoCardapioController@storeIngrediente')
            ->name('produtos-cardapio.store-ingrediente');
        Route::delete('/produtos-cardapio-destroy/{id}', 'ProdutoCardapioController@destroyAdicional')
            ->name('produtos-cardapio.destroy-adicional');
        Route::delete('/produtos-cardapio-ingrediente/{id}', 'ProdutoCardapioController@destroyIngrediente')
            ->name('produtos-cardapio.destroy-ingrediente');

	
Route::group(['prefix' => 'caixa'], function () {
    Route::post('/fechar', 'CaixaController@fechar')->name('caixa.fechar');
    Route::get('/fechar-empresa/{id}', 'CaixaController@fecharEmpresa')->name('caixa.fechar-empresa');
    Route::get('/list', 'CaixaController@list')->name('caixa.list');
    Route::get('/imprimir/{id}', 'CaixaController@imprimir')->name('caixa.imprimir');
    Route::get('/imprimir-produtos/{id}', 'CaixaController@imprimirProdutos')->name('caixa.imprimir.produtos');
    Route::get('/imprimir-produtos-clientes/{id}', 'CaixaController@imprimirProdutosClientes')->name('caixa.imprimir.produtos.clientes');
    Route::get('/fechar-conta/{id}', 'CaixaController@fecharConta')->name('caixa.fechar-conta');
    Route::post('/fechar-conta/{id}', 'CaixaController@fecharTiposPagamento')->name('caixa.fechar-tipos-pagamento');
    Route::get('/abertos-empresa', 'CaixaController@abertosEmpresa')->name('caixa.abertos-empresa');
});

        Route::resource('caixa', 'CaixaController');

        Route::resource('sangria', 'SangriaController');
        Route::get('/sangria-print/{id}', 'SangriaController@print')->name('sangria.print');
        Route::get('/suprimento-print/{id}', 'SuprimentoController@print')->name('suprimento.print');

        Route::resource('suprimento', 'SuprimentoController');

        Route::resource('categoria-servico', 'CategoriaServicoController');
        Route::delete('categoria-servico-destroy-select', 'CategoriaServicoController@destroySelecet')->name('categoria-servico.destroy-select');

        Route::resource('servicos', 'ServicoController');
        Route::delete('servicos-destroy-select', 'ServicoController@destroySelecet')->name('servicos.destroy-select');

        Route::resource('atendimentos', 'AtendimentoController');

        Route::resource('interrupcoes', 'InterrupcoesController');
        Route::group(['prefix' => 'interrupcoes'], function () {
            Route::get('/register/{id}', 'InterrupcoesController@register')->name('interrupcoes.register');
        });

        Route::resource('orcamentos', 'OrcamentoController');
        Route::group(['prefix' => 'orcamentos'], function () {
            Route::get('imprimir/{id}', 'OrcamentoController@imprimir')->name('orcamentos.imprimir');
            Route::get('gerar-venda/{id}', 'OrcamentoController@gerarVenda')->name('orcamentos.gerar-venda');
        });

        Route::group(['prefix' => 'conta-receber'], function () {
            Route::get('/{id}/pay', 'ContaReceberController@pay')->name('conta-receber.pay');
            Route::put('/{id}/pay-put', 'ContaReceberController@payPut')->name('conta-receber.pay-put');
        });

        Route::group(['prefix' => 'conta-pagar'], function () {
            Route::get('/{id}/pay', 'ContaPagarController@pay')->name('conta-pagar.pay');
            Route::put('/{id}/pay-put', 'ContaPagarController@payPut')->name('conta-pagar.pay-put');
        });

        Route::group(['prefix' => 'frontbox'], function () {
            Route::get('/imprimir-nao-fiscal/{id}', 'FrontBoxController@imprimirNaoFiscal')->name('frontbox.imprimir-nao-fiscal');
        });

        Route::resource('frontbox', 'FrontBoxController');

        Route::resource('pre-venda', 'PreVendaController');
        Route::get('/pre-venda/imprimir/{codigo}', 'PreVendaController@imprimir')->name('pre-venda.imprimir');

        Route::group(['prefix' => 'ordem-servico'], function () {
            Route::post('/store-servico', 'OrdemServicoController@storeServico')->name('ordem-servico.store-servico');
            Route::post('/store-produto', 'OrdemServicoController@storeProduto')->name('ordem-servico.store-produto');
            Route::delete('/delete-produto/{id}', 'OrdemServicoController@deletarProduto')->name('ordem-servico.deletar-produto');
            Route::delete('/deletar-servico/{id}', 'OrdemServicoController@deletarServico')->name('ordem-servico.deletar-servico');

            Route::get('/alterar-estado/{id}', 'OrdemServicoController@alterarEstado')->name('ordem-servico.alterar-estado');
            Route::post('/update-estado/{id}', 'OrdemServicoController@updateEstado')->name('ordem-servico.update-estado');
            Route::get('/imprimir/{id}', 'OrdemServicoController@imprimir')->name('ordem-servico.imprimir');
            Route::post('/store-funcionario', 'OrdemServicoController@storeFuncionario')->name('ordem-servico.store-funcionario');
            Route::get('/add-relatorio/{id}', 'OrdemServicoController@addRelatorio')->name('ordem-servico.add-relatorio');
            Route::get('/alterar-status-servico/{id}', 'OrdemServicoController@alterarStatusServico')->name('ordem-servico.alterar-status-servico');
            Route::delete('/delete-funcionario/{id}', 'OrdemServicoController@deleteFuncionario')->name('ordem-servico.deleteFuncionario');
            Route::post('/store-relatorio', 'OrdemServicoController@storeRelatorio')->name('ordem-servico.store-relatorio');
            Route::delete('/delete-relatorio/{id}', 'OrdemServicoController@deleteRelatorio')->name('ordem-servico.delete-relatorio');
            Route::get('/edit-relatorio/{id}', 'OrdemServicoController@editRelatorio')->name('ordem-servico.edit-relatorio');
            Route::post('/alterar-estado-post', 'OrdemServicoController@alterarEstadoPost')->name('ordem-servico.alterar-estado-post');

            Route::put('/update-relatorio/{id}', 'OrdemServicoController@updateRelatorio')->name('ordem-servico.update-relatorio');
            Route::get('/gerar-nfe/{id}', 'OrdemServicoController@gerarNfe')->name('ordem-servico.gerar-nfe');
			
			Route::post('/store-imagem', 'OrdemServicoController@storeImagem')->name('ordem-servico.store-imagem');
			
        });

        Route::resource('taxa-cartao', 'TaxaCartaoController');

        Route::resource('ordem-servico', 'OrdemServicoController');
        Route::delete('ordem-servico-destroy-select', 'OrdemServicoController@destroySelecet')->name('ordem-servico.destroy-select');

        Route::resource('agendamentos', 'AgendamentoController');
        Route::put('agendamentos-update-status/{id}', 'AgendamentoController@updateStatus')->name('agendamentos.update-status');
        Route::get('/agendamentos-pdv/{id}', 'AgendamentoController@pdv')->name('agendamentos.pdv');
		Route::get('agendamento/imprimir/{id}', 'AgendamentoController@imprimir')->name('agendamento.imprimir');
		Route::get('agendamento/pdf/{id}', 'AgendamentoController@pdf')->name('agendamento.pdf');

        Route::resource('config-reserva', 'ConfigReservaController');
        Route::resource('categoria-acomodacao', 'CategoriaAcomodacaoController');
        Route::resource('acomodacao', 'AcomodacaoController');
        Route::resource('frigobar', 'FrigobarController');
        Route::resource('reservas', 'ReservaController');
        Route::get('/reservas-checkin/{id}', 'ReservaController@checkin')->name('reservas.checkin');
        Route::get('/reservas-imprimir/{id}', 'ReservaController@imprimir')->name('reservas.imprimir');
        Route::post('/reservas-checkin/{id}', 'ReservaController@checkinStart')->name('reservas.checkin-start');
        Route::post('/reservas-store-produto/{id}', 'ReservaController@storeProduto')->name('reservas.store-produto');
        Route::post('/reservas-store-servico/{id}', 'ReservaController@storeServico')->name('reservas.store-servico');
        Route::post('/reservas-store-nota/{id}', 'ReservaController@storeNota')->name('reservas.store-nota');
        Route::post('/reservas-cancelamento/{id}', 'ReservaController@cancelamento')->name('reservas.cancelamento');
        Route::put('/reservas-update-hospedes/{id}', 'ReservaController@updateHospedes')->name('reservas.update-hospedes');
        Route::delete('/reservas-destroy-produto/{id}', 'ReservaController@destroyProduto')->name('reservas.destroy-produto');
        Route::delete('/reservas-destroy-servico/{id}', 'ReservaController@destroyServico')->name('reservas.destroy-servico');
        Route::delete('/reservas-destroy-nota/{id}', 'ReservaController@destroyNota')->name('reservas.destroy-nota');
        Route::post('/reservas-store-fatura/{id}', 'ReservaController@storeFatura')->name('reservas.store-fatura');
        Route::get('reservas-gerar-nfe/{id}', 'ReservaController@gerarNfe')->name('reservas.gerar-nfe');
        Route::get('reservas-gerar-nfse/{id}', 'ReservaController@gerarNfse')->name('reservas.gerar-nfse');
        Route::get('reservas-conferir-frigobar/{id}', 'ReservaController@conferirFrigobar')->name('reservas.conferir-frigobar');
        Route::put('reservas-update-estado/{id}', 'ReservaController@updateEstado')->name('reservas.update-estado');

        Route::post('/frigobar-store-default/{id}', 'FrigobarController@storeDefault')->name('frigobar.store-default');
        Route::post('/reservas-conferir-frigobar-save/{id}', 'ReservaController@conferenciaFrigobarSave')->name('reservas.conferir-frigobar-save');

        Route::resource('funcionamentos', 'FuncionamentoController');
        Route::resource('funcionamento-delivery', 'FuncionamentoDeliveryController');
        Route::resource('difal', 'DifalController');
        Route::resource('comissao', 'ComissaoController');
        Route::post('/comissao-pay-multiple', 'ComissaoController@payMultiple')->name('comissao.pay-multiple');

        Route::group(['prefix' => 'relatorios'], function () {
            Route::get('/', 'RelatorioController@index')->name('relatorios.index');
            Route::get('relatorio-produtos', 'RelatorioController@produtos')->name('relatorios.produtos');
            Route::get('relatorio-clientes', 'RelatorioController@clientes')->name('relatorios.clientes');
            Route::get('relatorio-fornecedores', 'RelatorioController@fornecedores')->name('relatorios.fornecedores');
            Route::get('relatorio-nfe', 'RelatorioController@nfe')->name('relatorios.nfe');
            Route::get('relatorio-nfce', 'RelatorioController@nfce')->name('relatorios.nfce');
            Route::get('relatorio-cte', 'RelatorioController@cte')->name('relatorios.cte');
            Route::get('relatorio-mdfe', 'RelatorioController@mdfe')->name('relatorios.mdfe');
            Route::get('relatorio-conta_pagar', 'RelatorioController@conta_pagar')->name('relatorios.conta_pagar');
            Route::get('relatorio-conta_receber', 'RelatorioController@conta_receber')->name('relatorios.conta_receber');
            Route::get('relatorio-comissao', 'RelatorioController@comissao')->name('relatorios.comissao');
            Route::get('relatorio-vendas', 'RelatorioController@vendas')->name('relatorios.vendas');
            Route::get('relatorio-compras', 'RelatorioController@compras')->name('relatorios.compras');
            Route::get('relatorio-taxas', 'RelatorioController@taxas')->name('relatorios.taxas');
            Route::get('relatorio-lucro', 'RelatorioController@lucro')->name('relatorios.lucro');
            Route::get('relatorio-baixa-produtos', 'RelatorioController@baixaProdutos')->name('relatorios.baixa_produtos');
            Route::get('relatorio-agendamentos', 'RelatorioController@agendamentos')->name('relatorios.agendamentos');
        });

        Route::resource('config-geral', 'ConfigGeralController');
    });
    Route::resource('config', 'ConfigController');
    Route::get('config-delete-logo', 'ConfigController@removerLogo')->name('config.delete-logo');

    Route::resource('payment', 'PaymentController');
    Route::get('payment/pix/{transacao_id}', 'PaymentController@pix')->name('payment.pix');
});
