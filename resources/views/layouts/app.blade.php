<!doctype html>
    <html lang="pt-br">

    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

		<meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate" />
		<meta http-equiv="Pragma" content="no-cache" />
		<meta http-equiv="Expires" content="0"/>


        <!-- CSRF Token -->
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{$title}}</title>

        <link rel="shortcut icon" href="/logo-sm.png">
        <link href="/assets/vendor/fullcalendar/main.min.css" rel="stylesheet" type="text/css" />
        <link href="/assets/vendor/select2/css/select2.min.css" rel="stylesheet" type="text/css" />
        <link href="/assets/vendor/daterangepicker/daterangepicker.css" rel="stylesheet" type="text/css" />
        <link href="/assets/vendor/bootstrap-touchspin/jquery.bootstrap-touchspin.min.css" rel="stylesheet" type="text/css" />
        <link href="/assets/vendor/admin-resources/jquery.vectormap/jquery-jvectormap-1.2.2.css">
        <link href="/assets/vendor/flatpickr/flatpickr.min.css" rel="stylesheet" type="text/css" />

        <script rel="stylesheet" src="/assets/js/config.js"></script>
        <link href="/assets/css/app.css" rel="stylesheet" type="text/css" id="app-style" />
        <link href="/assets/css/icons.min.css" rel="stylesheet" type="text/css" />
        <link rel="stylesheet" type="text/css" href="/assets/css/toastr.min.css">
        <link rel="stylesheet" type="text/css" href="/css/style.css">

        <link href="/bs5-tour/css/bs5-intro-tour.css" rel="stylesheet"/>

        <link rel='stylesheet' href='/css/bootstrap-duallistbox.min.css'/>
<style>
 

.favorites-bar {
	
	padding: 10px;
	border-radius: 5px;
	background-color: rgb(96, 145, 110);
	color: white;
	min-width: 360px;
	min-height: 60px;
	display: flex;
	flex-wrap: wrap;
	gap: 100px;
	width: 100%;
	margin: 0; /* Remove a margem padrão */
	padding-left: 0; /* Remove a padding esquerda */
	padding-right: 0; /* Remove a padding direita */
	overflow-x: hidden;
	white-space: nowrap;
	display: flex;
	align-items: center;
}

.favorite-item {
    background-color: #629972;
    padding: 5px 10px;
    border-radius: 5px;
    color: white;
    display: flex;
    align-items: center;
    text-decoration: none;
    white-space: nowrap;
    display: inline-block;
    margin-right: 5px;
}

.fix-favorites-container {
    margin-top: 10px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.favorite-item a {
    color: white;
    text-decoration: none;
}

        .table-produtos {
            table-layout: fixed; /* Mantém largura fixa para todas as colunas */
            width: 100%;         /* Ocupa toda a largura disponível */
        }

        /* Coluna 1 - Produto */
        .table-produtos td:nth-child(1),
        .table-produtos th:nth-child(1) {
            width: 450px;    /* largura inicial */
            max-width: 450px; /* valor máximo fixo */
        }

        /* Coluna 2 - Quantidade */
        .table-produtos td:nth-child(2),
        .table-produtos th:nth-child(2) {
            width: 120px;
            max-width: 120px;
        }

        /* Coluna 3 - Valor Unitário */
        .table-produtos td:nth-child(3),
        .table-produtos th:nth-child(3) {
            width: 120px;
            max-width: 120px;
        }

        /* Coluna 4 - Subtotal */
        .table-produtos td:nth-child(4),
        .table-produtos th:nth-child(4) {
            width: 120px;
            max-width: 120px;
        }

        /* Colunas restantes - mais compactas e flexíveis */
        .table-produtos td:nth-child(n+5),
        .table-produtos th:nth-child(n+5) {
            width: 300px;
            max-width: 300px;
        }

        /* Inputs ocupando toda a célula */
        .table-produtos input.qtd,
        .table-produtos input.valor_unit,
        .table-produtos input.sub_total {
            width: 100%;
            box-sizing: border-box;
            text-align: left; /* mantém alinhamento para milhar e centena */
            max-width: 100%;  /* não ultrapassa a largura da célula */
        }

        /* Ajuste do Select2 para coluna Produto */
        .select2-container.produto_id {
            width: 450px !important;
        }

        .select2-selection--single {
            text-overflow: ellipsis;
            white-space: nowrap;
            overflow: hidden;
        }

            

</style>

        @yield('css')
		
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Roboto&display=swap" rel="stylesheet">

<style>
    body {
        font-family: 'Inter', 'Roboto', sans-serif;
        font-size: 0.875rem; /* ~14px */
        line-height: 1.6;
    }

    h1, h2 {
        font-family: 'Inter', sans-serif;
        font-weight: 600;
        letter-spacing: -0.5px; /* opcional, mais moderno */
    }

    .card-title {
        font-size: 1rem; /* 16px */
        font-weight: 500;
    }
	
	#leftside-menu-container {
		font-size: 1.05rem; /* ~16.8px */
	}

	#leftside-menu-container .side-nav a,
	#leftside-menu-container .side-nav span {
		font-size: 1.05rem;
	}

	#leftside-menu-container .side-nav-second-level a,
	#leftside-menu-container .side-nav-second-level span {
		font-size: 0.91rem !important;
	}

	
</style>

    </head>
    <body>

        <div class="loader"></div>
        @if(isset(Auth::user()->empresa->empresa))
        <input type="hidden" value="{{ Auth::user()->empresa->empresa->id }}" id="empresa_id">
        @endif
        <input type="hidden" value="{{ Auth::user()->id }}" id="usuario_id">

        <div class="wrapper">
            <!-- ========== Topbar Start ========== -->
            <div class="navbar-custom">
                <div class="topbar container-fluid">
                    <div class="d-flex align-items-center gap-lg-2 gap-1" id="step1">

                        <!-- Topbar Brand Logo -->
                        <div class="logo-topbar">
                            <!-- Logo light -->
                            <a href="/" class="logo-light">
                                <span class="logo-lg">
                                    <img src="/logo-sm.png" alt="logo">

                                </span>
                                <span class="logo-sm">
                                    <img src="/logo-sm.png" alt="small logo">
                                </span>
                            </a>

                            <!-- Logo Dark -->
                            <a href="/" class="logo-dark">
                                <span class="logo-lg">
                                    <img src="/logo-sm.png" alt="dark logo">
                                </span>
                                <span class="logo-sm">
                                    <img src="/logo-sm.png" alt="small logo">
                                </span>
                            </a>
                        </div>

                                             <!-- Sidebar Menu Toggle Button -->
                        <button class="button-toggle-menu d-lg-none">
                            <i class="ri-menu-2-fill"></i>
                        </button>

                        <!-- Horizontal Menu Toggle Button -->
                        <button class="navbar-toggle" data-bs-toggle="collapse" data-bs-target="#topnav-menu-content">
                            <div class="lines">
                                <span></span>
                                <span></span>
                                <span></span>
                            </div>
                        </button>
						
						@if(optional(Auth::user()->empresa)->empresa?->id > 0)
							<span style="color: white;">
								<b> Empresa: {{ Auth::user()->empresa->empresa->nome }} </b>
							</span>
						@else
							<span style="color: white;">
								<b> Empresa: Sem Empresa Configurada </b>
							</span>							
						@endif

                        <!-- Topbar Search Form -->
                       

                     

                        @if(Auth::user()->empresa && Auth::user()->empresa->empresa->empresa_selecionada != null)
                        <div class="app-search dropdown d-none d-lg-block float-end">
                            <a href="{{ route('contador.show') }}" class="badge bg-success p-2">Empresa selecionada: {{ Auth::user()->empresa->empresa->empresaSelecionada->info }}</a>
                        </div>
                        @endif

						<!--
                        @if(Auth::user()->empresa && Auth::user()->empresa->empresa->plano)
                        <div class="app-search dropdown d-lg-block video" style="margin-left: 20px;">
                            <span class="badge bg-dark p-2">Plano: 
                                <strong class="text-success">{{ Auth::user()->empresa->empresa->plano->plano->nome }}</strong> - data de expiração: 
                                <strong>{{ __data_pt(Auth::user()->empresa->empresa->plano->data_expiracao, 0) }}</strong>
                            </span>
                            <a class="btn btn-light btn-sm" href="{{ route('upgrade.index') }}">Fazer upgrade</a>
                            <button class="btn btn-info btn-sm ml-1" id="click-tour">Tour do sistema</button>

                        </div>
                        @endif
						-->

                    </div>


                    <ul class="topbar-menu d-flex align-items-center gap-3 ">
                        <li class="dropdown d-none d-lg-blocke">
                            <a class="nav-link dropdown-toggle arrow-none" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                                <i class="ri-search-line fs-22"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end dropdown-menu-animated dropdown-lg p-0">

                            </div>
                        </li>  
						
						 <div  class="d-none d-lg-block" style="display: flex; align-items: center; gap: 5px; position: relative; width: 100%; padding: 5px; max-width: 800px;">
                            <div style="display: flex; align-items: center; position: relative; width: 100%; overflow: hidden;">
                                <!-- Seta para a esquerda -->
                                <div id="arrow-left" style="position: absolute; left: 0; top: 50%; transform: translateY(-50%); padding: 10px; cursor: pointer; z-index: 1; background-color: rgba(255, 255, 255, 0.8);">
                                    <i class="ri-arrow-left-s-line fs-20"></i>
                                </div>
                                
                                <!-- Barra de favoritos com rolagem -->
                                <div class="favorites-bar" id="favorites-bar" style="display: flex; gap: 5px; flex-wrap: nowrap; overflow-x: auto; scroll-behavior: smooth; width: 100%; padding: 0 40px;">
                                    <!-- Favoritos vão aqui -->
                                </div>
                                
                                <!-- Seta para a direita -->
                                <div id="arrow-right" style="position: absolute; right: 0; top: 50%; transform: translateY(-50%); padding: 10px; cursor: pointer; z-index: 1; background-color: rgba(255, 255, 255, 0.8);">
                                    <i class="ri-arrow-right-s-line fs-20"></i>
                                </div>
                                
                                <!-- Opção de limpar favoritos -->
                                <div id="clean-favorites" style="position: absolute; bottom: 5px; right: 5px; cursor: pointer;">
                                    <i class="ri-restart-line" style="color: white"></i>
                                </div>
                            </div>
                        </div>
						
                        <!-- inicio alertas -->

                        <li class="dropdown notification-list">
                            <a class="nav-link dropdown-toggle arrow-none" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                                <i class="ri-notification-3-fill fs-22"></i>
                                <div class="spinner-border spinner-border-sm text-danger" role="status">
                                    <span class="visually-hidden"></span>
                                </div>
                                <span class="noti-icon-badge d-none"></span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end dropdown-menu-animated dropdown-lg py-0" style="">
                                <div class="p-2 border-top-0 border-start-0 border-end-0 border-dashed border">
                                    <div class="row align-items-center">
                                        <div class="col">
                                            <h6 class="m-0 fs-16 fw-medium"> Notificações</h6>
                                        </div>
                                        <div class="col-auto">
                                            <a href="{{ route('notificacao.clear-all') }}" class="text-dark text-decoration-underline">
                                                <small>Limpar tudo</small>
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <div style="max-height: 300px;" data-simplebar="init"><div class="simplebar-wrapper" style="margin: 0px;"><div class="simplebar-height-auto-observer-wrapper"><div class="simplebar-height-auto-observer"></div></div><div class="simplebar-mask"><div class="simplebar-offset" style="right: 0px; bottom: 0px;"><div class="simplebar-content-wrapper" tabindex="0" role="region" aria-label="scrollable content" style="height: auto; overflow: hidden scroll;"><div class="simplebar-content alertas-main" style="padding: 0px;">


                                </div></div></div></div><div class="simplebar-placeholder" style="width: auto; height: 432px;"></div></div><div class="simplebar-track simplebar-horizontal" style="visibility: hidden;"><div class="simplebar-scrollbar" style="width: 0px; display: none;"></div></div><div class="simplebar-track simplebar-vertical" style="visibility: visible;"><div class="simplebar-scrollbar" style="transform: translate3d(0px, 0px, 0px); display: block;"></div></div></div>


                            </div>
                        </li>

                        @if(!__isContador())
                        @if(__isActivePlan(Auth::user()->empresa, 'PDV'))
                        @can('pdv_create')
                        <li class="d-none d-sm-inline-block">
                            <a title="PDV" class="nav-link" href="{{ route('frontbox.create')}}">
                                <i class="ri-shopping-basket-2-fill fs-22"></i>
                            </a>
                        </li>
                        @endcan
                        @endif
                        @endif

                       
                        <li class="d-none d-md-inline-block">
                            <a class="nav-link" href="" data-toggle="fullscreen">
                                <i class="ri-fullscreen-line fs-22"></i>
                            </a>
                        </li>

                   <li class="dropdown me-md-2" id="step3">
    <a class="nav-link dropdown-toggle arrow-none nav-user px-2" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
        <span class="account-user-avatar">
            @if(Auth::user()->imagem != null)
            <img src="{{ Auth::user()->img }}" height="32" class="rounded-circle">
            @else
            <img src="/assets/images/users/avatar-1.jpg" alt="user-image" width="32" class="rounded-circle">
            @endif
        </span>
        <span class="d-lg-flex flex-column gap-1 d-none">
            <h5 class="my-0"> {{ Auth::user()->name }}</h5>
            <h6 class="my-0 fw-normal">{{ Auth::user()->tipo }}</h6>
        </span>
    </a>
    <div class="dropdown-menu dropdown-menu-end dropdown-menu-animated profile-dropdown">
        <!-- item-->
        <div class=" dropdown-header noti-title">
            <h6 class="text-overflow m-0">Bem vindo!</h6>
        </div>

        @if(!__isContador())
        <a href="{{ route('usuarios.profile', Auth::user()->id) }}" class="dropdown-item">
            <i class="ri-account-circle-fill align-middle me-1"></i>
            <span>Minha Conta</span>
        </a>
		
        <!-- item-->
        <a href="{{ route('config.index') }}" class="dropdown-item">
            <i class="ri-settings-4-fill align-middle me-1"></i>
            <span>Configuração</span>
        </a>


		
        <a href="upgrade" class="dropdown-item" >
            <i class="ri-bank-card-line align-middle me-1"></i>
            <span>Assinar Planos</span>
        </a>
		
        <!-- Separação horizontal -->
        <div class="dropdown-divider"></div>
		
        <!-- item-->
        <a href="{{ route('ticket.index') }}" class="dropdown-item">
            <i class="ri-information-fill align-middle me-1"></i>
            <span>Abrir chamado</span>
        </a>

        <!-- Nova opção "Central de Ajuda" -->
        <a href="https://suporte.matriza.com.br" class="dropdown-item" target="_blank">
            <i class="ri-question-line align-middle me-1"></i>
            <span>Central de Ajuda</span>
        </a>
        @endif

        <!-- Separação horizontal -->
        <div class="dropdown-divider"></div>

        <!-- item-->
        <a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault();document.getElementById('logout-form').submit();">
            <i class="ri-logout-box-line  align-middle me-1"></i>
            Sair
        </a>
        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
            @csrf
        </form>
    </div>
</li>


                    </ul>
                </li>
            </ul>
        </div>
    </div>
    <!-- ========== Topbar End ========== -->


        <div class="leftside-menu">


            <style>
                .custom-text {
                    font-family: 'GoodTimingRg-Bold', sans-serif;
                    font-size: 24px;
                    color: white;
                    text-align: center;
                    margin-top: 20px;
                }
                .custom-text .highlight {
                    color: #E0721B;
                }
				
				.highlight {
					color: #E0721B;
					font-weight: bold;
					font-size: 1.3em;
				}
            </style>

<div class="d-flex flex-column align-items-center justify-content-center py-3" style="background: linear-gradient(135deg, #4e5d4f, #629972); border-bottom: 1px solid rgba(255,255,255,0.1);">
    <a href="{{ route('home', Auth::user()->id) }}" class="text-white text-decoration-none text-center">
        <div style="font-family: 'GoodTimingRg-Bold', sans-serif; font-size: 22px;">
            <span class="highlight">M</span>ATRIZA
        </div>
        <small style="font-size: 0.7rem;">SISTEMAS ERP</small>
    </a>
</div>


        <!-- Sidebar -left -->
            <div class="h-100" id="leftside-menu-container" data-simplebar>

         <!--- Sidemenu -->
                <ul class="side-nav" id="step4">

                    <li class="side-nav-item">
                        <a href="{{ route('home') }}" class="side-nav-link">
                            <i class="ri-home-smile-line"></i>
                            <span class="badge bg-success float-end"></span>
                            <span> Home </span>
                        </a>
                    </li>
					
					 @if(__isPartialSuperAdmin())
                <li class="side-nav-item">
                    <a data-bs-toggle="collapse" href="#sidebarPages2" aria-expanded="false" aria-controls="sidebarPages" class="side-nav-link">
                        <i class="ri-stack-fill"></i>
                        <span> SuperAdmin </span>
                        <span class="menu-arrow"></span>
                    </a>
                    <div class="collapse" id="sidebarPages2">
                        <ul class="side-nav-second-level">
							<li>
								<a href="{{ route('empresas.index') }}">Empresas</a>
							</li>
                            <li>
                                <a href="{{ route('usuario-super.index') }}">Usuários</a>
                            </li>							
							<li>
                                    <a href="{{ route('contadores.index') }}">Contadores</a>
							</li>
                            <li>
                                <a href="{{ route('gerenciar-planos.index') }}">Gerenciar planos</a>
                            </li>
                            <li>
                                <a href="{{ route('ticket-super.index') }}">Ticket</a>
                            </li>
							<li>
								<a href="{{ route('notificacao-super.index') }}">Notificações</a>
							</li>
                            <li>
								<a href="{{ route('relatorios-adm.index') }}">Relatorio ADM</a>
							</li>
                        </ul>
                    </div>
                </li>
                @endif
					

                    @if(__isMaster())

                    <li class="side-nav-item">
                        <a data-bs-toggle="collapse" href="#sidebarPages2" aria-expanded="false" aria-controls="sidebarPages" class="side-nav-link">
                            <i class="ri-stack-fill"></i>
                            <span> SuperAdmin </span>
                            <span class="menu-arrow"></span>
                        </a>
                        <div class="collapse" id="sidebarPages2">
                            <ul class="side-nav-second-level">

                                <li>
                                    <a href="{{ route('empresas.index') }}">Empresas</a>
                                </li>

                                @if(env("CONTADOR") == 1)
                                <li>
                                    <a href="{{ route('contadores.index') }}">Contadores</a>
                                </li>
                                @endif

                                <li>
                                    <a href="{{ route('planos.index') }}">Planos</a>
                                </li>
                                <li>
                                    <a href="{{ route('cidades.index') }}">Cidades</a>
                                </li>
                                <li>
                                    <a href="{{ route('usuario-super.index') }}">Usuários</a>
                                </li>
                                <li>
                                    <a href="{{ route('gerenciar-planos.index') }}">Gerenciar planos</a>
                                </li>
                                <li>
                                    <a href="{{ route('planos-pendentes.index') }}">Planos pendentes</a>
                                </li>
                                <li>
                                    <a href="{{ route('ncm.index') }}">NCM</a>
                                </li>

                                <li>
                                    <a href="{{ route('ibpt.index') }}">IBPT</a>
                                </li>
								
                                <li>
                                    <a href="{{ route('ticket-super.index') }}">Ticket</a>
                                </li>
                                <li>
                                    <a href="{{ route('notificacao-super.index') }}">Notificações</a>
                                </li>
                                <li>
								<a href="{{ route('relatorios-adm.index') }}">Relatorio ADM</a>
							</li>
                            </ul>
                        </div>
                    </li>
					
					<li class="side-nav-item">
                    <a data-bs-toggle="collapse" href="#sidebarPermissao" aria-expanded="false" aria-controls="sidebarPages" class="side-nav-link">
                        <i class="ri-rotate-lock-line"></i>
                        <span> Controle de acesso </span>
                        <span class="menu-arrow"></span>
                    </a>
                    <div class="collapse" id="sidebarPermissao">
                        <ul class="side-nav-second-level">

                            <li>
                                <a href="{{ route('permissions.index') }}">Permissões</a>
                            </li>
                            <li>
                                <a href="{{ route('roles.index') }}">Atribuições</a>
                            </li>

                        </ul>
                    </div>
                </li>

                    <li class="side-nav-item">
                        <a data-bs-toggle="collapse" href="#sidebarPages1" aria-expanded="false" aria-controls="sidebarPages" class="side-nav-link">
                            <i class="ri-file-mark-fill"></i>
                            <span> Emissões </span>
                            <span class="menu-arrow"></span>
                        </a>
                        <div class="collapse" id="sidebarPages1">
                            <ul class="side-nav-second-level">

                                <li>
                                    <a href="{{ route('nfe-all') }}">NFe</a>
                                </li>
                                <li>
                                    <a href="{{ route('nfce-all') }}">NFCe</a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    @if(env("MARKETPLACE") == 1)
                    <li class="side-nav-item">
                        <a data-bs-toggle="collapse" href="#sidebarMarketPlace" aria-expanded="false" aria-controls="sidebarPages" class="side-nav-link">
                            <i class="ri-settings-4-fill"></i>
                            <span>Configuração </span>
                            <span class="menu-arrow"></span>
                        </a>
                        <div class="collapse" id="sidebarMarketPlace">
                            <ul class="side-nav-second-level">
							
								<li>
                                    <a href="{{ route('configuracao-super.index') }}">Configuração Super</a>
                                </li>

                                <li>
                                    <a href="{{ route('bairros-super.index') }}">Bairros</a>
                                </li>

                            </ul>
                        </div>
                    </li>
                    @endif

                    @endif

                    @if(!__isMaster())
						
                    @if(__isActivePlan(Auth::user()->empresa, 'PDV'))
                    <li class="side-nav-item">
                        <a data-bs-toggle="collapse" href="#sidebarPDV" aria-expanded="false" aria-controls="sidebarIcons" class="side-nav-link">
                            <i class="ri-shopping-cart-fill"></i>

                            <span>PDV</span>
                            <span class="menu-arrow"></span>
                        </a>
                        <div class="collapse" id="sidebarPDV">
                            <ul class="side-nav-second-level">
								@can('caixa_view')
                                <li>
                                    <a data-bs-toggle="collapse" href="#caixa" aria-expanded="false" aria-controls="caixa">
                                        <span> Caixa </span>
                                        <span class="menu-arrow"></span>
                                    </a>
                                    <div class="collapse" id="caixa">
                                        <ul class="side-nav-second-level">    
                                            <li>
                                                <a href="{{ route('caixa.create') }}">Abrir Caixa</a>
                                            </li>
                                            <li>
                                                <a href="{{ route('caixa.list') }}">Listar Caixa</a>
                                            </li>

                                        </ul>
                                    </div>
                                </li>
                            @endcan
                                <li class="d-none d-sm-inline-block">
                                    <a href="{{ route('frontbox.create')}}" data-toggle="fullscreen" class="dropdown-item">Novo PDV</a>
                                </li>							
                                <li>
                                    <a href="{{ route('frontbox.index') }}">Listar PDV</a>
                                </li>
                                @can('taxa_pagamento_view')
                                <li>
                                    <a href="{{ route('taxa-cartao.index') }}">Taxas de Cartão</a>
                                </li>
                                @endcan
                            </ul>
                        </div>
                    </li>
                    @endif					 
					
                    @if(__isActivePlan(Auth::user()->empresa, 'Vendas'))
                    <li class="side-nav-item">
                        <a data-bs-toggle="collapse" href="#sidebarNFe" aria-expanded="false" aria-controls="sidebarIcons" class="side-nav-link">
                            <i class="ri-file-list-fill"></i>

                            <span>Vendas</span>
                            <span class="menu-arrow"></span>
                        </a>
                        <div class="collapse" id="sidebarNFe">
                            <ul class="side-nav-second-level">
                                @if(__isActivePlan(Auth::user()->empresa, 'Vendas'))
                                <li >
                                    <a data-bs-toggle="collapse" href="#sidebarNfeop" aria-expanded="false" aria-controls="sidebarIcons" >
                                        
                                        <span>NFe</span>
                                        <span class="menu-arrow"></span>
                                    </a>
                                    <div class="collapse" id="sidebarNfeop">
                                        <ul class="side-nav-second-level">
                                            <li>
                                                <a href="{{ route('nfe.create') }}">Nova Venda</a>
                                            </li>							
                                            <li>
                                                <a href="{{ route('nfe.index') }}">Listar NFe</a>
                                            </li>
                                            <li>
                                                <a href="{{ route('nfe.inutilizar') }}">Inutilizar NFe</a>
                                            </li>
                                        </ul>
                                    </div>
                                </li>
                                @endif
								

                                

                                @if(__isActivePlan(Auth::user()->empresa, 'NFCe'))
                                <li >
                                    <a data-bs-toggle="collapse" href="#sidebarNfce" aria-expanded="false" aria-controls="sidebarIcons">
                                        
                                        <span>NFCe</span>
                                        <span class="menu-arrow"></span>
                                    </a>
                                    <div class="collapse" id="sidebarNfce">
                                        <ul class="side-nav-second-level">
                                            <li>
                                                <a href="{{ route('nfce.index') }}">Listar NFCe</a>
                                            </li>
                                            <li>
                                                <a href="{{ route('nfce.create') }}">Nova NFCe</a>
                                            </li>
                                            <li>
                                                <a href="{{ route('nfce.inutilizar') }}">Inutilizar NFCe</a>
                                            </li>
                                        </ul>
                                    </div>
                                </li>
                                @endif

                                @if(__isActivePlan(Auth::user()->empresa, 'NFSe'))
                                <li>
                                    <a data-bs-toggle="collapse" href="#sidebarNfse" aria-expanded="false" aria-controls="sidebarIcons" >

                                        <span>NFSe</span>
                                        <span class="menu-arrow"></span>
                                    </a>
                                    <div class="collapse" id="sidebarNfse">
                                        <ul class="side-nav-second-level">
                                            <li>
                                                <a href="{{ route('nota-servico.index') }}">Listar NFSe</a>
                                            </li>
                                            <li>
                                                <a href="{{ route('nota-servico.create') }}">Nova NFSe</a>
                                            </li>
                                        </ul>
                                    </div>
                                </li>
                                @endif															

                                @if(__isActivePlan(Auth::user()->empresa, 'CTe'))
                                <li >
                                    <a data-bs-toggle="collapse" href="#sidebarCte" aria-expanded="false" aria-controls="sidebarIcons" >
                                        
                                        <span>CTe</span>
                                        <span class="menu-arrow"></span>
                                    </a>
                                    <div class="collapse" id="sidebarCte">
                                        <ul class="side-nav-second-level">
                                            <li>
                                                <a href="{{ route('cte.index') }}">Listar CTe</a>
                                            </li>
                                            <li>
                                                <a href="{{ route('cte.create') }}">Nova CTe</a>
                                            </li>
                      
                                        </ul>
                                    </div>
                                </li>

                                @endif

                                @if(__isActivePlan(Auth::user()->empresa, 'MDFe'))
                                <li >
                                    <a data-bs-toggle="collapse" href="#sidebarMdfe" aria-expanded="false" aria-controls="sidebarIcons" >
                                        
                                        <span>MDFe</span>
                                        <span class="menu-arrow"></span>
                                    </a>
                                    <div class="collapse" id="sidebarMdfe">
                                        <ul class="side-nav-second-level">
                                            <li>
                                                <a href="{{ route('mdfe.index') }}">Listar MDFe</a>
                                            </li>
                                            <li>
                                                <a href="{{ route('mdfe.create') }}">Nova MDFe</a>
                                            </li>
                      
                                        </ul>
                                    </div>
                                </li>
                                @endif

                                <li>
                                    <a href="{{ route('orcamentos.index') }}">Orçamentos</a>
                                </li>															

									    @if(__isPlanoFiscal())
                                                    <li>
                                                        <a href="{{ route('devolucao.index')}}" data-toggle="fullscreen" class="dropdown-item">
                                                            Devolução
                                                        </a>
                                                    </li>
                                        @endif

                                        															
                                        @if(__isActivePlan(Auth::user()->empresa, 'NFCe'))
                                        <li>
                                            <a href="{{ route('pre-venda.index') }}">Pré Vendas</a>
                                        </li>
                                        @endif

                                        @if(__isActivePlan(Auth::user()->empresa, 'CTe'))
                                                    <li>
                                                        <a href="{{ route('cte-os.index') }}">CTe O.S</a>
                                                    </li>

                                                    {{-- <li>
                                                        <a href="{{ route('cte-os.inutilizar') }}">Cancelar CTe OS</a>
                                                    </li> --}}
                                        @endif

														



                            </ul>
                        </div>
                    </li>
                    @endif					
					
                

                    @if(__isActivePlan(Auth::user()->empresa, 'Serviços'))

                    <li class="side-nav-item">
                        <a data-bs-toggle="collapse" href="#sidebarExtendedServ" aria-expanded="false" aria-controls="sidebarExtendedSer" class="side-nav-link">
                            <i class="ri-tools-fill"></i>
                            <span> Serviços </span>
                            <span class="menu-arrow"></span>
                        </a>
                        <div class="collapse" id="sidebarExtendedServ">
                            <ul class="side-nav-second-level">
                                
                                @if(__isActivePlan(Auth::user()->empresa, 'Atendimento'))
								<li >
									<a data-bs-toggle="collapse" href="#sidebarAtendimento" aria-expanded="false" aria-controls="sidebarAtendimento" >
										
										<span> Atendimento & Agenda</span>
										<span class="menu-arrow"></span>
									</a>
									<div class="collapse" id="sidebarAtendimento">
										<ul class="side-nav-third-level">
											<li>
												<a href="{{ route('atendimentos.index') }}">Dias de Atendimento</a>
											</li>
											<li>
												<a href="{{ route('interrupcoes.index') }}">Interrupções</a>
											</li>
											<li>
												<a href="{{ route('funcionamentos.index') }}">Horário de Funcionamento</a>
											</li>
											
											@if(__isActivePlan(Auth::user()->empresa, 'Agendamentos'))
											<li>
												<a href="{{ route('agendamentos.index') }}">Agendamentos</a>
											</li>		
											@endif
                                           
								
										</ul>
									</div>
								</li>
								@endif	

                                <li>
                                    <a href="{{ route('categoria-servico.index') }}">Categorias</a>
                                </li>
                                <li>
                                    <a href="{{ route('servicos.index') }}">Listar Serviços</a>
                                </li>
                                <li>
                                    <a href="{{ route('servicos.create') }}">Novo Serviço</a>
                                </li>

                                <li>

                                <li>
                                    <a href="{{ route('ordem-servico.index') }}">Ordem de Serviço</a>
                                </li>

															
                            </ul>
                        </div>
                    </li>
                    @endif					
					


                    @if(__isActivePlan(Auth::user()->empresa, 'Pessoas'))
                    <li class="side-nav-item" id="step6">
                        <a data-bs-toggle="collapse" href="#sidebarPessoas" aria-expanded="false" aria-controls="sidebarIcons" class="side-nav-link">
                            <i class="ri-user-shared-line"></i>

                            <span>Cadastros PF & PJ</span>
                            <span class="menu-arrow"></span>
                        </a>
                        <div class="collapse" id="sidebarPessoas">
                            <ul class="side-nav-second-level">
                                <li>
                                    <a href="{{ route('clientes.index') }}">Clientes</a>
                                </li>
                                <li>
                                    <a href="{{ route('fornecedores.index') }}">Fornecedores</a>
                                </li>
                                <li>
                                    <a href="{{ route('transportadoras.index') }}">Transportadoras</a>
                                </li>
                                <li>
                                    <a href="{{ route('funcionarios.index') }}">Funcionários</a>
                                </li>								
								
								@if(__isActivePlan(Auth::user()->empresa, 'Veiculos'))
											<li>
												<a href="{{ route('veiculos.index') }}">Veículos</a>
											</li>

								@endif								
                            </ul>
                        </div>
                    </li>
                    @endif					
					

                    @if(__isActivePlan(Auth::user()->empresa, 'Financeiro'))
                @canany(['conta_pagar_view', 'conta_receber_view', 'relatorio_view', 'caixa_view', 'contas_empresa_view', 'contas_boleto_view', 'boleto_view', 'taxa_pagamento_view'])
                <li class="side-nav-item">
                    <a data-bs-toggle="collapse" href="#sidebarPagar" aria-expanded="false" aria-controls="sidebarIcons" class="side-nav-link">
                        <i class="ri-money-dollar-box-fill"></i>
                        <span>Financeiro</span>
                        <span class="menu-arrow"></span>
                    </a>
                    <div class="collapse" id="sidebarPagar">
                        <ul class="side-nav-second-level">
											<li>
                                                <a href="{{ route('caixa.index') }}">Movimentação</a>
                                            </li>

                                            <li>
                                                <a href="{{ route('extrato.conciliar') }}">Conciliação Bancária</a>
                                            </li>

                            @canany(['conta_pagar_view', 'conta_pagar_create'])
                                        <li>
                                            <a href="{{ route('conta-pagar.index') }}">Contas a Pagar</a>
                                        </li>
                            @endcanany

                            @canany(['conta_receber_view', 'conta_receber_create'])
                                        <li>
                                            <a href="{{ route('conta-receber.index') }}">Contas a Receber</a>
                                        </li>
                            @endcanany
							
							@can('contas_boleto_view')
                            <li>
                                <a href="{{ route('contas-boleto.index') }}">Contas para boleto</a>
                            </li>
                            @endcan

                            @can('boleto_view')
                            <li>
                                <a href="{{ route('boleto.index') }}">Boletos</a>
                            </li>
                            @endcan


                        </ul>
                    </div>
                </li>
                @endcanany
                @endif

                    @if(__isActivePlan(Auth::user()->empresa, 'Produtos'))
                    <li class="side-nav-item" id="step7">
                        <a data-bs-toggle="collapse" href="#sidebarExtendedProd" aria-expanded="false" aria-controls="sidebarExtendedUI" class="side-nav-link">
                            <i class="ri-product-hunt-fill"></i>
                            <span> Produtos </span>
                            <span class="menu-arrow"></span>
                        </a>
                        <div class="collapse" id="sidebarExtendedProd">
                            <ul class="side-nav-second-level">

                                <li>
                                    <a href="{{ route('estoque.index') }}">Estoque</a>
                                </li>							
								
                                <li>
                                    <a href="{{ route('produtos.index') }}">Listar Produtos</a>
                                </li>								
								
                                <li>
                                    <a href="{{ route('produtos.create') }}">Novo Produto</a>
                                </li>								
							
                                <li>
                                    <a href="{{ route('categoria-produtos.index') }}">Categorias</a>
                                </li>

                                <li>
                                    <a href="{{ route('variacoes.index') }}">Variações</a>
                                </li>

                                <li>
                                    <a href="{{ route('lista-preco.index') }}">Lista de preços</a>
                                </li>

                                <li>
                                    <a href="{{ route('produtopadrao-tributacao.index') }}">Configuração Padrão Fiscal</a>
                                </li>

                                <li>
                                    <a href="{{ route('marcas.index') }}">Marcas</a>
                                </li>
								<li>
                                <a href="{{ route('modelo-etiquetas.index') }}">Modelos de Etiqueta</a>
								</li>
                            </ul>
                        </div>
                    </li>
                    @endif


                    @if(__isActivePlan(Auth::user()->empresa, 'Compras'))
                    <li class="side-nav-item">
                        <a data-bs-toggle="collapse" href="#sidebarCompra" aria-expanded="false" aria-controls="sidebarIcons" class="side-nav-link">
                            <i class="ri-logout-box-line"></i>

                            <span>Compras</span>
                            <span class="menu-arrow"></span>
                        </a>
                        <div class="collapse" id="sidebarCompra">
                            <ul class="side-nav-second-level">
                                <li>
                                    <a href="{{ route('compras.index') }}">Listar Compras</a>
                                </li>
                                <li>
                                    <a href="{{ route('compras.create')}}" data-toggle="fullscreen" class="dropdown-item">Nova Compra
                                    </a>
                                </li>

                                <li>
                                    <a href="{{ route('compras.xml')}}" data-toggle="fullscreen" class="dropdown-item">
                                        Importar XML
                                    </a>
                                </li>

                                <li>
                                    <a href="{{ route('manifesto.index')}}" data-toggle="fullscreen" class="dropdown-item">
                                        Manifesto
                                    </a>
                                </li>

                                <li>
                                    <a href="{{ route('cotacoes.index')}}" data-toggle="fullscreen" class="dropdown-item">
                                        Cotação
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    @endif


					@if(__isActivePlan(Auth::user()->empresa, 'Pessoas'))
                    <li class="side-nav-item">
                        <a data-bs-toggle="collapse" href="#sidebarGestaoPessoal" aria-expanded="false" aria-controls="sidebarIcons" class="side-nav-link">
                            <i class="ri-folder-user-line"></i>

                            <span>Eventos</span>
                            <span class="menu-arrow"></span>
                        </a>
                        <div class="collapse" id="sidebarGestaoPessoal">
                            <ul class="side-nav-second-level">

                                <li>
                                    <a href="{{ route('evento-funcionarios.index') }}">Listar Eventos</a>
                                </li>
                                <li>
                                    <a href="{{ route('funcionario-eventos.index') }}">Funcionários x Eventos</a>
                                </li>
                                <li>
                                    <a href="{{ route('apuracao-mensal.index') }}">Apuração Mensal</a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    @can('relatorio_view')
                    <li class="side-nav-item">
                        <a data-bs-toggle="collapse" href="#sidebarRelatorios" aria-expanded="false" aria-controls="sidebarIcons" class="side-nav-link">
                            <i class="ri-file-chart-line"></i>

                            <span>Relatórios</span>
                            <span class="menu-arrow"></span>
                        </a>
                        <div class="collapse" id="sidebarRelatorios">
                            <ul class="side-nav-second-level">

                                <li>
                                    <a href="{{ route('relatorios.index') }}">Listar Relatórios</a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    @endcan
					@endif
					
                        <li class="side-nav-item" id="step5">
                            <a data-bs-toggle="collapse" href="#sidebarConfig" aria-expanded="false" aria-controls="sidebarIcons" class="side-nav-link">
                                <i class="ri-settings-4-fill"></i>
                                <span>Configuração</span>
                                <span class="menu-arrow"></span>
                            </a>
                            <div class="collapse" id="sidebarConfig">
                                <ul class="side-nav-second-level">
                                    <li>
                                        <a href="{{ route('config.index') }}">Emitente</a>
                                    </li>
                                    <li>
                                        <a href="{{ route('natureza-operacao.index') }}">Natureza de Operação</a>
                                    </li>			
									@if(auth()->check())
									<li>
									<a href="{{ route('centro-custo.index') }}"> Centros de Custo</a>
									</li>
									@endif
                                    <li>
                                        <a href="{{ route('usuarios.index') }}">Usuários</a>
                                    </li>	
								@if(__isActivePlan(Auth::user()->empresa, 'Usuários'))
                             @canany(['usuarios_view', 'controle_acesso_view'])
                            @can('controle_acesso_view')
                            <li>
                                <a href="{{ route('controle-acesso.index') }}">Controle de acesso</a>
                            </li>
                            @endcan
                               <li>
                                <a href="{{ route('config-geral.create') }}">Configuração Geral</a>
                            </li>
                        </ul>
                    </div>
					
			    @php
                    $user = Auth::user();
                    $empresa = $user->empresa ?? null;
                    $plano = $empresa->plano ?? null;
                    $dataExpiracao = $plano ? \Carbon\Carbon::parse($plano->data_expiracao) : null;
                    $diasRestantes = $dataExpiracao ? $dataExpiracao->diffInDays(\Carbon\Carbon::now()) : null;
                    $isExpirado = $dataExpiracao ? $dataExpiracao->isPast() : null;
                @endphp
				

                
				@if($empresa && $plano)
					<div class="mt-3" style="background-color: #e9f5ee; padding: 16px; border-radius: 6px; border-left: 5px solid {{ $isExpirado ? '#dc3545' : ($diasRestantes <= 5 ? '#ffc107' : '#198754') }}; font-size: 0.9rem;">
						<h6 style="margin: 0; font-weight: 600; color: #333; font-size: 0.95rem;">
							<span style="color: #198754; font-size: 0.9rem;">Plano:</span>
							<a href="{{ url('/upgrade') }}" style="color: #0d6efd; text-decoration: none; font-size: 0.9rem;">
								{{ $plano->plano->nome }}
							</a>
						</h6>

						@if($isExpirado)
							<p style="margin: 5px 0 0; color: #dc3545; font-size: 0.9rem;">
								<strong>Seu plano já expirou.</strong> Por favor, renove para continuar aproveitando os benefícios.
							</p>
						@else
							<p style="margin: 5px 0 0; color: {{ $diasRestantes <= 5 ? '#ffc107' : '#198754' }}; font-size: 0.9rem;">
								<strong>Expira em:</strong> {{ $diasRestantes }} dias
							</p>
						@endif
					</div>
				@endif
					
                </li>
                @endcanany
                @endif
                            </ul>
                        </div>
                    </li>

                    @endif

                    @if(Auth::user()->empresa && __isContador())
                    <li class="side-nav-item">
                        <a data-bs-toggle="collapse" href="#sidebarCad" aria-expanded="false" aria-controls="sidebarIcons" class="side-nav-link">
                            <i class="ri-draft-fill"></i>
                            <span>Cadastros</span>
                            <span class="menu-arrow"></span>
                        </a>
                        <div class="collapse" id="sidebarCad">
                            <ul class="side-nav-second-level">
                                <li>
                                    <a href="{{ route('contador-empresa.produtos') }}">Produtos</a>
                                </li>
                                <li>
                                    <a href="{{ route('contador-empresa.clientes') }}">Clientes</a>
                                </li>
                                <li>
                                    <a href="{{ route('contador-empresa.fornecedores') }}">Fornecedores</a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    <li class="side-nav-item">
                        <a data-bs-toggle="collapse" href="#sidebarDoc" aria-expanded="false" aria-controls="sidebarIcons" class="side-nav-link">
                            <i class="ri-clipboard-fill"></i>
                            <span>Documentos</span>
                            <span class="menu-arrow"></span>
                        </a>
                        <div class="collapse" id="sidebarDoc">
                            <ul class="side-nav-second-level">
                                <li>
                                    <a href="{{ route('contador-empresa.nfe') }}">NFe</a>
                                </li>

                                <li>
                                    <a href="{{ route('contador-empresa.nfce') }}">NFCe</a>
                                </li>
                                <li>
                                    <a href="{{ route('contador-empresa.cte') }}">CTe</a>
                                </li>
                                <li>
                                    <a href="{{ route('contador-empresa.mdfe') }}">MDFe</a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    @endif
                </ul>
            </div>
			</div>
      
        <div class="content-page">
            <div class="content">
                <div class="container-fluid">

                    @yield('content')

                </div>
            </div>
			<style>
				.footer { 
				 position: absolute;
				 rigth: 0;
				 bottom: 0;
				 left: 0;
				  width: 100%;
				  display: flex;
				  align-items: center;
				  justify-content: flex-start;
				  padding-left: 20px; /* adiciona um espaçamento à esquerda */
				  font-size: 16px; /* ajusta o tamanho da fonte */
				}


			</style>
            <footer class="footer" >
                <div class="container-fluid" >  
                    <div class="row">
                        <div class="col-md-6">
                            <script>
                                document.write( "&copy;" + new Date().getFullYear() )

                            </script> {{ env("APP_NAME") }}
                        </div>
                    </div>
                </div>
            </footer>
        </div>

        @if(!isset($not_loading))
        <div class="control-loading">
            <div class="modal-loading loading-class"></div>
        </div>
        @endif

        <div class="modal fade" id="modal-notificacao" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="staticBackdropLabel">Notificações</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Fechar</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modal-notificacao-delivery" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="staticBackdropLabel">Notificações de Delivery</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Fechar</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modal-notificacao-ecommerce" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="staticBackdropLabel">Notificações de Ecommerce</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Fechar</button>
                    </div>
                </div>
            </div>
        </div>
		

    <script type="text/javascript">
        let prot = window.location.protocol;
        let host = window.location.host;
        const path_url = prot + "//" + host + "/";

    </script>
    <script src="/assets/js/vendor.min.js"></script>
    <script src="/assets/vendor/select2/js/select2.min.js"></script>
    <script src="/assets/js/toastr.min.js"></script>
    <script src="/assets/vendor/dropzone/dropzone.js"></script>
    <script src="/assets/js/pages/component.fileupload.js"></script>
    <script src="/assets/vendor/daterangepicker/moment.min.js"></script>
    <script src="/assets/vendor/daterangepicker/daterangepicker.js"></script>
    <script src="/assets/vendor/jquery-mask-plugin/jquery.mask.min.js"></script>
    <script src="/assets/vendor/admin-resources/jquery.vectormap/jquery-jvectormap-1.2.2.min.js"></script>
    <script src="/assets/vendor/admin-resources/jquery.vectormap/maps/jquery-jvectormap-world-mill-en.js"></script>
    <script src="/assets/vendor/bootstrap-touchspin/jquery.bootstrap-touchspin.min.js"></script>
    <script src="/assets/vendor/bootstrap-maxlength/bootstrap-maxlength.min.js"></script>

    <script src='/js/jquery.bootstrap-duallistbox.min.js'></script>

    <script src="/js/uploadImagem.js"></script>
    <script type="text/javascript" src="/js/jquery.mask.min.js"></script>
    <script src='https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js'></script>
    <script src="/assets/js/app.js"></script>
    <script type="text/javascript" src="/js/main.js"></script>
    <script src="/assets/vendor/flatpickr/flatpickr.min.js"></script>
    <script src="/assets/js/pages/demo.flatpickr.js"></script>

    @if(__isNotificacao(Auth::user()->empresa) && Auth::user()->notificacao_cardapio)
    <script src="/js/notificacao.js"></script>
    @endif

    @if(__isNotificacaoMarketPlace(Auth::user()->empresa) && Auth::user()->notificacao_marketplace)
    <script src="/js/notificacao_marketplace.js"></script>
    @endif

    @if(__isNotificacaoEcommerce(Auth::user()->empresa) && Auth::user()->notificacao_ecommerce)
    <script src="/js/notificacao_ecommerce.js"></script>
    @endif

    @yield('js')

 <script type="text/javascript">
    toastr.options = {
        "progressBar": true,
        "onclick": null,
        "showDuration": "300",
        "hideDuration": "1000",
        "timeOut": "10000",
        "extendedTimeOut": "1000",
        "showEasing": "swing",
        "hideEasing": "linear",
        "showMethod": "fadeIn",
        "hideMethod": "fadeOut"
    };

    @if(session()->has('flash_success'))
    toastr.success('{{ session()->get('flash_success') }}');
    @endif

    @if(session()->has('flash_error'))
    toastr.error('{{ session()->get('flash_error') }}');
    @endif

    @if(session()->has('flash_warning'))
    toastr.warning('{{ session()->get('flash_warning') }}');
    @endif

    // Removido o bloco que definia 'data-sidenav-size' para condensar o menu

    window.addEventListener("load", () => {
        setTimeout(() => {
            document.querySelector(".loader").classList.add("loader--hidden");
        }, 100);
    });

    function audioError() {
        var audio = new Audio('/audio/error.mp3');
        audio.addEventListener('canplaythrough', function() {
            audio.play();
        });
    }

    function loadFont() {
        // Determinar o nível da subpasta com base na quantidade de barras '/' no path
        const pathDepth = window.location.pathname.split('/').length - 2;
        const relativePath = '../'.repeat(pathDepth);

        // Criar e aplicar o estilo para @font-face dinamicamente
        const style = document.createElement('style');
        style.innerHTML = `
            @font-face {
                font-family: 'GoodTimingRg-Bold';
                src: url('${relativePath}fonts/GoodTimingRg-Bold.woff2') format('woff2'),
                     url('${relativePath}fonts/GoodTimingRg-Bold.woff') format('woff');
                font-weight: bold;
                font-style: normal;
            }
        `;
        document.head.appendChild(style);
    }

    // Carregar a fonte quando a página for carregada
    window.onload = loadFont;
</script> 



    <script src="{{ asset('assets/js/favorites.js') }}"></script>  
    <script src="/bs5-tour/js/bs5-intro-tour.js"></script>
    <script src="/js/tour.js"></script>
</body>
</html>
