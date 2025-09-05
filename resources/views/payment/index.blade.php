@extends('layouts.app', ['title' => 'Planos'])
@section('css')
<style>
/* Mantém as tabs na horizontal, mas as alinha corretamente */
.eael-tabs-nav ul {
    display: flex;
    flex-direction: row;
    list-style: none;
    padding: 0;
    margin: 0;
}

/* Define o layout das tabs */
.eael-tab-item-trigger {
    cursor: pointer;
	position: relative;
	text-align: center;
	border: 1px solid transparent;
    padding: 10px;
    border: 1px solid #ddd;
    background-color: #f8f9fa;
    transition: background-color 0.3s ease;
    width: auto;
    display: inline-block;
	font-size: 16px;
    color: #333333;
}

/* Estilo para o estado ativo */
.eael-tab-item-trigger.active {
    background-color: #005cee;
    color: white;
}
.eael-tab-item-trigger.active::after {
    content: '';
    position: absolute;
    bottom: -10px; /* Posição do triângulo */
    left: 50%;
    transform: translateX(-50%);
    border-left: 10px solid transparent;
    border-right: 10px solid transparent;
    border-top: 10px solid #005cee; /* Cor do triângulo */
}
.eael-tab-item-trigger:hover {
	background-color: #004bbd; /* Cor mais escura ao passar o mouse */
    color: white;
}
/* Ajuste para o ícone e o texto */
.eael-tab-title i {
    margin-right: 5px;
    font-size: 18px; /* Tamanho ajustado do ícone */
}

.eael-tab-title b {
    font-weight: bold;
    font-size: 16px;
}

/* Ajusta o layout das divs dentro da eael-tabs-content */
.eael-tabs-content {
    display: flex;
    flex-wrap: nowrap; /* Garante que os itens fiquem lado a lado */
    justify-content: space-between; /* Espaço entre as caixas */
}

/* Divs internas (ativas) dentro da eael-tabs-content */
.eael-tabs-content .elementor.elementor-436 {
    width: 30%;
    display: inline-block;
    vertical-align: top;
    margin: 0 10px;
}

.eael-tabs-content .elementor.elementor-444 {
    width: 30%;
    display: inline-block;
    vertical-align: top;
    margin: 0 10px;
}
.eael-tabs-content .elementor.elementor-441 {
    width: 30%;
    display: inline-block;
    vertical-align: top;
    margin: 0 10px;
}
/* Estilo para o conteúdo ativo das tabs */
.eael-tab-content-item {
    display: none;
    opacity: 0;
    transition: opacity 0.5s ease;
}

.eael-tab-content-item.active {
    display: block;
    opacity: 1;
    width: 100%;
}

/* Indicador ativo */
.eael-tab-active-indicator {
    position: absolute;
    bottom: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 0;
    height: 0;
    border-style: solid;
    border-width: 10px 10px 0 10px;
    border-color: #007bff transparent transparent transparent;
    visibility: hidden;
}

.eael-tab-item-trigger.active .eael-tab-active-indicator {
    visibility: visible;
}

/* Ícones dentro das tabs */
.e-font-icon-svg {
    width: 26px;
    height: 26px;
	color: #333

}


.elementor-widget-price-list .elementor-price-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.elementor-widget-price-list .elementor-price-list li {
    padding: 0;
    margin: 0;
}

.elementor-price-list li:not(:last-child) {
    margin-bottom: 20px;
}

.elementor-price-table {
    text-align: center;

}
.elementor-price-table .elementor-price-table__header_prata {
    background: var(--e-price-table-header-background-color, #555);
    padding: 20px 0;
}
.elementor-price-table .elementor-price-table__header_ouro {
    background: var(--e-price-table-header-gold-background-color, #3a996f);
    padding: 20px 0;
}
.elementor-price-table .elementor-price-table__header_diamante {
    background: var(--e-price-table-header-diamond-background-color, #fe7201);
    padding: 20px 0;
}
.elementor-price-table .elementor-price-table__heading {
    margin: 0;
    padding: 0;
    line-height: 1.2;
    font-size: 24px;
    font-weight: 600;
    color: #fff;
}

.elementor-price-table .elementor-price-table__subheading{
	font-size:13px;
	font-weight:400;
	color:#fff;
}

.elementor-price-table__period .elementor-typo-excluded {
	background: green;
}
.elementor-price-table .elementor-price-table__original-price{
	margin-inline-end:15px;
	text-decoration:line-through;
	font-size:.5em;
	line-height:1;
	font-weight:400;
	align-self:center;
}
.elementor-price-table .elementor-price-table__original-price .elementor-price-table__currency{
	font-size:1em;
	margin:0;
}
.elementor-price-table .elementor-price-table__price{
	display:flex;
	justify-content:center;
	align-items:center;
	flex-wrap:wrap;
	flex-direction:row;
	color:#555;
	font-weight:800;
	font-size:65px;
	padding:40px 0;
}
.elementor-price-table .elementor-price-table__price .elementor-typo-excluded{
	line-height:normal;
	letter-spacing:normal;
	text-transform:none;
	font-weight:400;
	font-size:medium;
	font-style:normal;
	color:#d34949;
}
.elementor-price-table .elementor-price-table__after-price{
	display:flex;
	flex-wrap:wrap;
	text-align:start;
	align-self:stretch;
	align-items:flex-start;
	flex-direction:column;
}
.elementor-price-table .elementor-price-table__integer-part{
	line-height:.8;
}
.elementor-price-table .elementor-price-table__currency,.elementor-price-table .elementor-price-table__fractional-part{
	line-height:1;
	font-size:.3em;
}
.elementor-price-table .elementor-price-table__currency{
	margin-inline-end:3px;
}
.elementor-price-table .elementor-price-table__period{
	width:100%;
	font-size:13px;
	font-weight:400;
}

.elementor-price-table .elementor-price-table__features-list{
	list-style-type:none;margin:0;
	padding:0;
	line-height:1;
	color:var(--e-price-table-features-list-color);
}
.elementor-price-table .elementor-price-table__features-list li{
	font-size:14px;
	line-height:1;
	margin:0;
	padding:0;
}
.elementor-price-table .elementor-price-table__features-list li .elementor-price-table__feature-inner{
	margin-left:15px;
	margin-right:15px;
}
.elementor-price-table .elementor-price-table__features-list li:not(:first-child):before{
	content:"";
	display:block;
	border:0 solid hsla(0,0%,47.8%,.3);
	margin:10px 12.5%;
}
.elementor-price-table .elementor-price-table__features-list i{
	margin-inline-end:10px;
	font-size:1.3em;
}
.elementor-price-table .elementor-price-table__features-list svg{
	margin-inline-end:10px;
	fill:var(--e-price-table-features-list-color);
	height:1.3em;
	width:1.3em;
}
.elementor-price-table .elementor-price-table__features-list svg~*{
	vertical-align:text-top;
}
.elementor-price-table .elementor-price-table__footer{padding:30px 0}
.elementor-price-table__ribbon .elementor-price-table__ribbon-inner{
	color: #fff;
}
.elementor-price-table .elementor-price-table__additional_info{margin:0;font-size:13px;line-height:1.4}
.elementor-price-table__ribbon{position:absolute;top:0;left:auto;right:0;transform:rotate(90deg);width:150px;overflow:hidden;height:150px}
.elementor-price-table__ribbon-inner{
	text-align:center;
	left:0;width:200%;
	transform:translateY(-50%) translateX(-50%) translateX(35px) rotate(-45deg);
	margin-top:35px;
	font-size:13px;
	line-height:2;
	font-weight:800;
	text-transform:uppercase;
	background:#000}
.elementor-price-table__ribbon.elementor-ribbon-left{
	transform:rotate(0);left:0;right:auto
}
.elementor-price-table__ribbon.elementor-ribbon-right{
	transform:rotate(90deg);left:auto;right:0
}
.elementor-widget-price-table .elementor-widget-container{
	overflow:hidden;
	background-color:#f9fafa;
	border-radius: 8px 8px 8px 8px;
	border-style: solid;
	border-width: 1px 1px 1px 1px;
	border-color: var(--e-price-table-header-background-color);

}



</style>
@endsection

@section('content')
<div class="card m-1">
    <div class="row m-3">
        @if($config != null && $config->mercadopago_public_key != "")
		@endif



			<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" />
			<link rel="stylesheet" id="contact-form-7-css" href="./planos_files/styles.css" media="all">
			<link rel="stylesheet" id="elementor-frontend-css" href="./planos_files/frontend-lite.min.css" media="all">
			<link rel="stylesheet" id="elementor-post-6-css" href="./planos_files/post-6.css" media="all">
			<link rel="stylesheet" id="elementor-global-css" href="./planos_files/global.css" media="all">
			<link rel="stylesheet" id="elementor-post-44-css" href="./planos_files/post-44.css" media="all">
			<link rel="stylesheet" id="google-fonts-1-css" href="./planos_files/css" media="all">
			<link rel="canonical" href="https://projetos.metamidia.com.br/site/matriza/">
			<link rel="shortlink" href="https://projetos.metamidia.com.br/site/matriza/">
			<link rel="alternate" type="application/json+oembed" href="https://projetos.metamidia.com.br/site/matriza/index.php/wp-json/oembed/1.0/embed?url=https%3A%2F%2Fprojetos.metamidia.com.br%2Fsite%2Fmatriza%2F">
			<link rel="alternate" type="text/xml+oembed" href="https://projetos.metamidia.com.br/site/matriza/index.php/wp-json/oembed/1.0/embed?url=https%3A%2F%2Fprojetos.metamidia.com.br%2Fsite%2Fmatriza%2F&amp;format=xml">


			<meta name="generator" content="Elementor 3.21.1; features: e_optimized_assets_loading, e_optimized_css_loading, e_font_icon_svg, additional_custom_breakpoints, e_lazyload; settings: css_print_method-external, google_font-enabled, font_display-swap">

			<link rel="icon" href="./planos_files/favicon.png" sizes="32x32">
			<link rel="icon" href="./planos_files/favicon.png" sizes="192x192">
			<link rel="apple-touch-icon" href="./planos_files/favicon.png">
			<meta name="msapplication-TileImage" content="https://projetos.metamidia.com.br/site/matriza/wp-content/uploads/2024/04/favicon.png">

			<body class="home page-template page-template-elementor_header_footer page page-id-44 wp-custom-logo elementor-default elementor-template-full-width elementor-kit-6 elementor-page elementor-page-44 e--ua-blink e--ua-chrome e--ua-webkit" data-elementor-device-mode="desktop">
				<ul class="eael-tab-inline-icon" role="tablist" style="display: flex; justify-content: space-between; font-size: 16px;">
					<li id="planos-anuais" class="eael-tab-item-trigger" data-tab="anuais" role="tab" tabindex="0" style="width: 33.33%; display: inline-block;">
						<svg class="e-font-icon-svg e-far-arrow-alt-circle-right"></svg>
						<span class="eael-tab-title"><i class="far fa-arrow-alt-circle-right" style="margin-right: 5px;"></i>Planos Anuais</span>
					</li>
					<li id="planos-trimestrais" class="eael-tab-item-trigger" data-tab="trimestrais" role="tab" tabindex="0" style="width: 33.33%; display: inline-block;">
						<svg class="e-font-icon-svg e-far-arrow-alt-circle-right"></svg>
						<span class="eael-tab-title"><i class="far fa-arrow-alt-circle-right" style="margin-right: 5px;"></i>Planos Trimestrais</span>
					</li>
					<li id="planos-mensais" class="eael-tab-item-trigger" data-tab="mensais" role="tab" tabindex="0" style="width: 33.33%; display: inline-block;">
						<svg class="e-font-icon-svg e-far-arrow-alt-circle-right"></svg>
						<span class="eael-tab-title"><i class="far fa-arrow-alt-circle-right" style="margin-right: 5px;"></i>Planos Mensais</span>
					</li>
				</ul>
		<div class="col-lg-12 col-12">
			<div class="eael-tabs-content"  >
			<div id="planos-anuais-tab" class="clearfix eael-tab-content-item inactive" data-title-link="planos-anuais-tab">
			<div data-elementor-type="section" data-elementor-id="436" class="elementor elementor-436" data-elementor-post-type="elementor_library" >
			<div class="elementor-element elementor-element-12d1d18 e-flex e-con-boxed e-con e-parent e-lazyloaded"  data-id="12d1d18" data-element_type="container">
			<div class="e-con-inner" >
			<div class="elementor-element elementor-element-d0f3654 e-flex e-con-boxed e-con e-child" data-id="d0f3654" data-element_type="container">
			<div class="e-con-inner">
			<div class="elementor-element elementor-element-7e91515 e-flex e-con-boxed e-con e-child"  data-id="7e91515" data-element_type="container">
			<div class="e-con-inner" >
			<div class="elementor-element elementor-element-089b9a1 elementor-widget__width-inherit elementor-widget elementor-widget-price-table" data-id="089b9a1" data-element_type="widget" data-widget_type="price-table.default" >
			<div class="elementor-widget-container" >
			<div class="elementor-price-table">
			<div class="elementor-price-table__header_prata">
			<h3 class="elementor-price-table__heading">
			Plano Prata						</h3>
			<span class="elementor-price-table__subheading">
			Plano para Microempresas							</span>
			</div>
			<div class="elementor-price-table__price">
			<div class="elementor-price-table__original-price elementor-typo-excluded"><b>
			<span class="elementor-price-table__currency">R$</span>99						</div></b>
			<span class="elementor-price-table__currency">R$</span>									<span class="elementor-price-table__integer-part">
			39					</span>
			<div class="elementor-price-table__after-price">
			<span class="elementor-price-table__fractional-part">
			90						</span>
			</div>
			<span class="elementor-price-table__period elementor-typo-excluded" style="color: green;">Promoção</span>							</div>
			<ul class="elementor-price-table__features-list">
			<li class="elementor-repeater-item-19a20a0">
			<div class="elementor-price-table__feature-inner">
			<svg aria-hidden="true" class="e-font-icon-svg e-far-check-circle" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M256 8C119.033 8 8 119.033 8 256s111.033 248 248 248 248-111.033 248-248S392.967 8 256 8zm0 48c110.532 0 200 89.451 200 200 0 110.532-89.451 200-200 200-110.532 0-200-89.451-200-200 0-110.532 89.451-200 200-200m140.204 130.267l-22.536-22.718c-4.667-4.705-12.265-4.736-16.97-.068L215.346 303.697l-59.792-60.277c-4.667-4.705-12.265-4.736-16.97-.069l-22.719 22.536c-4.705 4.667-4.736 12.265-.068 16.971l90.781 91.516c4.667 4.705 12.265 4.736 16.97.068l172.589-171.204c4.704-4.668 4.734-12.266.067-16.971z"></path></svg>																	<span>
			Gestão de Vendas e Clientes;									</span>
			</div>
			</li>
			<li class="elementor-repeater-item-a1ff7ed">
			<div class="elementor-price-table__feature-inner">
			<svg aria-hidden="true" class="e-font-icon-svg e-far-check-circle" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M256 8C119.033 8 8 119.033 8 256s111.033 248 248 248 248-111.033 248-248S392.967 8 256 8zm0 48c110.532 0 200 89.451 200 200 0 110.532-89.451 200-200 200-110.532 0-200-89.451-200-200 0-110.532 89.451-200 200-200m140.204 130.267l-22.536-22.718c-4.667-4.705-12.265-4.736-16.97-.068L215.346 303.697l-59.792-60.277c-4.667-4.705-12.265-4.736-16.97-.069l-22.719 22.536c-4.705 4.667-4.736 12.265-.068 16.971l90.781 91.516c4.667 4.705 12.265 4.736 16.97.068l172.589-171.204c4.704-4.668 4.734-12.266.067-16.971z"></path></svg>																	<span>
			CRM Integrado;									</span>
			</div>
			</li>
			<li class="elementor-repeater-item-84249dc">
			<div class="elementor-price-table__feature-inner">
			<svg aria-hidden="true" class="e-font-icon-svg e-far-check-circle" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M256 8C119.033 8 8 119.033 8 256s111.033 248 248 248 248-111.033 248-248S392.967 8 256 8zm0 48c110.532 0 200 89.451 200 200 0 110.532-89.451 200-200 200-110.532 0-200-89.451-200-200 0-110.532 89.451-200 200-200m140.204 130.267l-22.536-22.718c-4.667-4.705-12.265-4.736-16.97-.068L215.346 303.697l-59.792-60.277c-4.667-4.705-12.265-4.736-16.97-.069l-22.719 22.536c-4.705 4.667-4.736 12.265-.068 16.971l90.781 91.516c4.667 4.705 12.265 4.736 16.97.068l172.589-171.204c4.704-4.668 4.734-12.266.067-16.971z"></path></svg>																	<span>
			Gestão de Estoque e Produtos;									</span>
			</div>
			</li>
            <li class="elementor-repeater-item-a1ff7ed">
			<div class="elementor-price-table__feature-inner">
			<svg aria-hidden="true" class="e-font-icon-svg e-far-check-circle" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M256 8C119.033 8 8 119.033 8 256s111.033 248 248 248 248-111.033 248-248S392.967 8 256 8zm0 48c110.532 0 200 89.451 200 200 0 110.532-89.451 200-200 200-110.532 0-200-89.451-200-200 0-110.532 89.451-200 200-200m140.204 130.267l-22.536-22.718c-4.667-4.705-12.265-4.736-16.97-.068L215.346 303.697l-59.792-60.277c-4.667-4.705-12.265-4.736-16.97-.069l-22.719 22.536c-4.705 4.667-4.736 12.265-.068 16.971l90.781 91.516c4.667 4.705 12.265 4.736 16.97.068l172.589-171.204c4.704-4.668 4.734-12.266.067-16.971z"></path></svg>																	<span>
            Emissão de Notas Fiscais NF-e NFC-e NFS-e;
			</div>
			</li>
			<li class="elementor-repeater-item-bd4ea80">
			<div class="elementor-price-table__feature-inner">
			<svg aria-hidden="true" class="e-font-icon-svg e-far-check-circle" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M256 8C119.033 8 8 119.033 8 256s111.033 248 248 248 248-111.033 248-248S392.967 8 256 8zm0 48c110.532 0 200 89.451 200 200 0 110.532-89.451 200-200 200-110.532 0-200-89.451-200-200 0-110.532 89.451-200 200-200m140.204 130.267l-22.536-22.718c-4.667-4.705-12.265-4.736-16.97-.068L215.346 303.697l-59.792-60.277c-4.667-4.705-12.265-4.736-16.97-.069l-22.719 22.536c-4.705 4.667-4.736 12.265-.068 16.971l90.781 91.516c4.667 4.705 12.265 4.736 16.97.068l172.589-171.204c4.704-4.668 4.734-12.266.067-16.971z"></path></svg>																	<span>
			Ideal para micro empresas e MEI;									</span>
			</div>
			</li>
            <li class="elementor-repeater-item-a1ff7ed">
			<div class="elementor-price-table__feature-inner">
			<svg aria-hidden="true" class="e-font-icon-svg e-far-check-circle" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M256 8C119.033 8 8 119.033 8 256s111.033 248 248 248 248-111.033 248-248S392.967 8 256 8zm0 48c110.532 0 200 89.451 200 200 0 110.532-89.451 200-200 200-110.532 0-200-89.451-200-200 0-110.532 89.451-200 200-200m140.204 130.267l-22.536-22.718c-4.667-4.705-12.265-4.736-16.97-.068L215.346 303.697l-59.792-60.277c-4.667-4.705-12.265-4.736-16.97-.069l-22.719 22.536c-4.705 4.667-4.736 12.265-.068 16.971l90.781 91.516c4.667 4.705 12.265 4.736 16.97.068l172.589-171.204c4.704-4.668 4.734-12.266.067-16.971z"></path></svg>																	<span>
            Controle financeiro conta a pagar e contas a receber;
			</div>
			</li>
			<li class="elementor-repeater-item-96fea14">
			<div class="elementor-price-table__feature-inner">
			<svg aria-hidden="true" class="e-font-icon-svg e-far-check-circle" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M256 8C119.033 8 8 119.033 8 256s111.033 248 248 248 248-111.033 248-248S392.967 8 256 8zm0 48c110.532 0 200 89.451 200 200 0 110.532-89.451 200-200 200-110.532 0-200-89.451-200-200 0-110.532 89.451-200 200-200m140.204 130.267l-22.536-22.718c-4.667-4.705-12.265-4.736-16.97-.068L215.346 303.697l-59.792-60.277c-4.667-4.705-12.265-4.736-16.97-.069l-22.719 22.536c-4.705 4.667-4.736 12.265-.068 16.971l90.781 91.516c4.667 4.705 12.265 4.736 16.97.068l172.589-171.204c4.704-4.668 4.734-12.266.067-16.971z"></path></svg>																	<span>
			Até 1 usuário;									</span>
			</div>
			</li>
			</ul>
<div class="elementor-price-table__footer">
    <a class="elementor-price-table__button elementor-button elementor-size-md btn btn-success w-100"
       href="{{ url('/plano/contratar/8') }}"
       target="_blank"
       style="background-color: {{ Auth::user()->empresa->empresa->plano->plano_id == 8 ? '#FE7201' : '#28a745' }};">
       {{ Auth::user()->empresa->empresa->plano->plano_id == 8 ? 'Contratado' : 'Contratar' }}
    </a>
    <div class="elementor-price-table__additional_info"></div>
</div>
			</div>
			</div>
			</div>
			</div>
			</div>
			</div>
			</div>
			</div>
			</div>
			</div>


			<div data-elementor-type="section" data-elementor-id="436" class="elementor elementor-436" data-elementor-post-type="elementor_library">
			<div class="elementor-element elementor-element-bec27ff e-flex e-con-boxed e-con e-child"  data-id="bec27ff" data-element_type="container">
			<div class="e-con-inner" >
			<div class="elementor-element elementor-element-a6369f0 elementor-widget elementor-widget-price-table" data-id="a6369f0" data-element_type="widget" data-widget_type="price-table.default" >
			<div class="elementor-widget-container"  >
			<div class="elementor-price-table">
			<div class="elementor-price-table__header_ouro">
			<h3 class="elementor-price-table__heading" >
			Plano Ouro						</h3>
			<span class="elementor-price-table__subheading">
			Plano para pequenas empresas						</span>
			</div>
			<div class="elementor-price-table__price">
			<div class="elementor-price-table__original-price elementor-typo-excluded"><b>
			<span class="elementor-price-table__currency">R$</span>359					</div></b>
			<span class="elementor-price-table__currency">R$</span>									<span class="elementor-price-table__integer-part">
			189					</span>
			<div class="elementor-price-table__after-price">
			<span class="elementor-price-table__fractional-part">
			90						</span>
			</div>
			<span class="elementor-price-table__period elementor-typo-excluded" style="color: green;">Promoção</span>							</div>
			<ul class="elementor-price-table__features-list">
			<li class="elementor-repeater-item-19a20a0">
			<div class="elementor-price-table__feature-inner">
			<svg aria-hidden="true" class="e-font-icon-svg e-far-check-circle" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M256 8C119.033 8 8 119.033 8 256s111.033 248 248 248 248-111.033 248-248S392.967 8 256 8zm0 48c110.532 0 200 89.451 200 200 0 110.532-89.451 200-200 200-110.532 0-200-89.451-200-200 0-110.532 89.451-200 200-200m140.204 130.267l-22.536-22.718c-4.667-4.705-12.265-4.736-16.97-.068L215.346 303.697l-59.792-60.277c-4.667-4.705-12.265-4.736-16.97-.069l-22.719 22.536c-4.705 4.667-4.736 12.265-.068 16.971l90.781 91.516c4.667 4.705 12.265 4.736 16.97.068l172.589-171.204c4.704-4.668 4.734-12.266.067-16.971z"></path></svg>																	<span>
			Gestão de Vendas e Clientes;									</span>
			</div>
			</li>
			<li class="elementor-repeater-item-5804794">
			<div class="elementor-price-table__feature-inner">
			<svg aria-hidden="true" class="e-font-icon-svg e-far-check-circle" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M256 8C119.033 8 8 119.033 8 256s111.033 248 248 248 248-111.033 248-248S392.967 8 256 8zm0 48c110.532 0 200 89.451 200 200 0 110.532-89.451 200-200 200-110.532 0-200-89.451-200-200 0-110.532 89.451-200 200-200m140.204 130.267l-22.536-22.718c-4.667-4.705-12.265-4.736-16.97-.068L215.346 303.697l-59.792-60.277c-4.667-4.705-12.265-4.736-16.97-.069l-22.719 22.536c-4.705 4.667-4.736 12.265-.068 16.971l90.781 91.516c4.667 4.705 12.265 4.736 16.97.068l172.589-171.204c4.704-4.668 4.734-12.266.067-16.971z"></path></svg>																	<span>
			CRM Integrado;									</span>
			</div>
			</li>
			<li class="elementor-repeater-item-393b067">
			<div class="elementor-price-table__feature-inner">
			<svg aria-hidden="true" class="e-font-icon-svg e-far-check-circle" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M256 8C119.033 8 8 119.033 8 256s111.033 248 248 248 248-111.033 248-248S392.967 8 256 8zm0 48c110.532 0 200 89.451 200 200 0 110.532-89.451 200-200 200-110.532 0-200-89.451-200-200 0-110.532 89.451-200 200-200m140.204 130.267l-22.536-22.718c-4.667-4.705-12.265-4.736-16.97-.068L215.346 303.697l-59.792-60.277c-4.667-4.705-12.265-4.736-16.97-.069l-22.719 22.536c-4.705 4.667-4.736 12.265-.068 16.971l90.781 91.516c4.667 4.705 12.265 4.736 16.97.068l172.589-171.204c4.704-4.668 4.734-12.266.067-16.971z"></path></svg>																	<span>
			Gestão de Estoque e Produtos;									</span>
			</div>
			</li>
			<li class="elementor-repeater-item-102b5b8">
			<div class="elementor-price-table__feature-inner">
			<svg aria-hidden="true" class="e-font-icon-svg e-far-check-circle" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M256 8C119.033 8 8 119.033 8 256s111.033 248 248 248 248-111.033 248-248S392.967 8 256 8zm0 48c110.532 0 200 89.451 200 200 0 110.532-89.451 200-200 200-110.532 0-200-89.451-200-200 0-110.532 89.451-200 200-200m140.204 130.267l-22.536-22.718c-4.667-4.705-12.265-4.736-16.97-.068L215.346 303.697l-59.792-60.277c-4.667-4.705-12.265-4.736-16.97-.069l-22.719 22.536c-4.705 4.667-4.736 12.265-.068 16.971l90.781 91.516c4.667 4.705 12.265 4.736 16.97.068l172.589-171.204c4.704-4.668 4.734-12.266.067-16.971z"></path></svg>																	<span>
			Emissão de NF-e, NFS-e, NFC-e;									</span>
			</div>
			</li>
			<li class="elementor-repeater-item-1332cab">
			<div class="elementor-price-table__feature-inner">
			<svg aria-hidden="true" class="e-font-icon-svg e-far-check-circle" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M256 8C119.033 8 8 119.033 8 256s111.033 248 248 248 248-111.033 248-248S392.967 8 256 8zm0 48c110.532 0 200 89.451 200 200 0 110.532-89.451 200-200 200-110.532 0-200-89.451-200-200 0-110.532 89.451-200 200-200m140.204 130.267l-22.536-22.718c-4.667-4.705-12.265-4.736-16.97-.068L215.346 303.697l-59.792-60.277c-4.667-4.705-12.265-4.736-16.97-.069l-22.719 22.536c-4.705 4.667-4.736 12.265-.068 16.971l90.781 91.516c4.667 4.705 12.265 4.736 16.97.068l172.589-171.204c4.704-4.668 4.734-12.266.067-16.971z"></path></svg>																	<span>
			Ideal para empresas de pequenas e médio porte;									</span>
			</div>
			</li>
			<li class="elementor-repeater-item-a1ff7ed">
			<div class="elementor-price-table__feature-inner">
			<svg aria-hidden="true" class="e-font-icon-svg e-far-check-circle" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M256 8C119.033 8 8 119.033 8 256s111.033 248 248 248 248-111.033 248-248S392.967 8 256 8zm0 48c110.532 0 200 89.451 200 200 0 110.532-89.451 200-200 200-110.532 0-200-89.451-200-200 0-110.532 89.451-200 200-200m140.204 130.267l-22.536-22.718c-4.667-4.705-12.265-4.736-16.97-.068L215.346 303.697l-59.792-60.277c-4.667-4.705-12.265-4.736-16.97-.069l-22.719 22.536c-4.705 4.667-4.736 12.265-.068 16.971l90.781 91.516c4.667 4.705 12.265 4.736 16.97.068l172.589-171.204c4.704-4.668 4.734-12.266.067-16.971z"></path></svg>																	<span>
			Controle Financeiro, conta a pagar e contas a receber;									</span>
			</div>
			</li>
			<li class="elementor-repeater-item-84249dc">
			<div class="elementor-price-table__feature-inner">
			<svg aria-hidden="true" class="e-font-icon-svg e-far-check-circle" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M256 8C119.033 8 8 119.033 8 256s111.033 248 248 248 248-111.033 248-248S392.967 8 256 8zm0 48c110.532 0 200 89.451 200 200 0 110.532-89.451 200-200 200-110.532 0-200-89.451-200-200 0-110.532 89.451-200 200-200m140.204 130.267l-22.536-22.718c-4.667-4.705-12.265-4.736-16.97-.068L215.346 303.697l-59.792-60.277c-4.667-4.705-12.265-4.736-16.97-.069l-22.719 22.536c-4.705 4.667-4.736 12.265-.068 16.971l90.781 91.516c4.667 4.705 12.265 4.736 16.97.068l172.589-171.204c4.704-4.668 4.734-12.266.067-16.971z"></path></svg>																	<span>
			Gestão de Compras e Fornecedores;									</span>
			</div>
			</li>
			<li class="elementor-repeater-item-d835d07">
			<div class="elementor-price-table__feature-inner">
			<svg aria-hidden="true" class="e-font-icon-svg e-far-check-circle" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M256 8C119.033 8 8 119.033 8 256s111.033 248 248 248 248-111.033 248-248S392.967 8 256 8zm0 48c110.532 0 200 89.451 200 200 0 110.532-89.451 200-200 200-110.532 0-200-89.451-200-200 0-110.532 89.451-200 200-200m140.204 130.267l-22.536-22.718c-4.667-4.705-12.265-4.736-16.97-.068L215.346 303.697l-59.792-60.277c-4.667-4.705-12.265-4.736-16.97-.069l-22.719 22.536c-4.705 4.667-4.736 12.265-.068 16.971l90.781 91.516c4.667 4.705 12.265 4.736 16.97.068l172.589-171.204c4.704-4.668 4.734-12.266.067-16.971z"></path></svg>																	<span>
			Módulo de importação de dados;									</span>
			</div>
			</li>
			<li class="elementor-repeater-item-7b5f5d3">
			<div class="elementor-price-table__feature-inner">
			<svg aria-hidden="true" class="e-font-icon-svg e-far-check-circle" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M256 8C119.033 8 8 119.033 8 256s111.033 248 248 248 248-111.033 248-248S392.967 8 256 8zm0 48c110.532 0 200 89.451 200 200 0 110.532-89.451 200-200 200-110.532 0-200-89.451-200-200 0-110.532 89.451-200 200-200m140.204 130.267l-22.536-22.718c-4.667-4.705-12.265-4.736-16.97-.068L215.346 303.697l-59.792-60.277c-4.667-4.705-12.265-4.736-16.97-.069l-22.719 22.536c-4.705 4.667-4.736 12.265-.068 16.971l90.781 91.516c4.667 4.705 12.265 4.736 16.97.068l172.589-171.204c4.704-4.668 4.734-12.266.067-16.971z"></path></svg>																	<span>
			Relatórios gerenciais;									</span>
			</div>
			</li>
            <li class="elementor-repeater-item-7b5f5d3">
            <div class="elementor-price-table__feature-inner">
            <svg aria-hidden="true" class="e-font-icon-svg e-far-check-circle" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M256 8C119.033 8 8 119.033 8 256s111.033 248 248 248 248-111.033 248-248S392.967 8 256 8zm0 48c110.532 0 200 89.451 200 200 0 110.532-89.451 200-200 200-110.532 0-200-89.451-200-200 0-110.532 89.451-200 200-200m140.204 130.267l-22.536-22.718c-4.667-4.705-12.265-4.736-16.97-.068L215.346 303.697l-59.792-60.277c-4.667-4.705-12.265-4.736-16.97-.069l-22.719 22.536c-4.705 4.667-4.736 12.265-.068 16.971l90.781 91.516c4.667 4.705 12.265 4.736 16.97.068l172.589-171.204c4.704-4.668 4.734-12.266.067-16.971z"></path></svg>																	<span>
            Emissão de CT-e, Emissão de MDF-e;
            </div>
            </li>
            <li class="elementor-repeater-item-7b5f5d3">
            <div class="elementor-price-table__feature-inner">
            <svg aria-hidden="true" class="e-font-icon-svg e-far-check-circle" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M256 8C119.033 8 8 119.033 8 256s111.033 248 248 248 248-111.033 248-248S392.967 8 256 8zm0 48c110.532 0 200 89.451 200 200 0 110.532-89.451 200-200 200-110.532 0-200-89.451-200-200 0-110.532 89.451-200 200-200m140.204 130.267l-22.536-22.718c-4.667-4.705-12.265-4.736-16.97-.068L215.346 303.697l-59.792-60.277c-4.667-4.705-12.265-4.736-16.97-.069l-22.719 22.536c-4.705 4.667-4.736 12.265-.068 16.971l90.781 91.516c4.667 4.705 12.265 4.736 16.97.068l172.589-171.204c4.704-4.668 4.734-12.266.067-16.971z"></path></svg>																	<span>
            Frente de Caixa – PDV;
            </div>
            </li>
			<li class="elementor-repeater-item-81bfb28">
			<div class="elementor-price-table__feature-inner">
			<svg aria-hidden="true" class="e-font-icon-svg e-far-check-circle" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M256 8C119.033 8 8 119.033 8 256s111.033 248 248 248 248-111.033 248-248S392.967 8 256 8zm0 48c110.532 0 200 89.451 200 200 0 110.532-89.451 200-200 200-110.532 0-200-89.451-200-200 0-110.532 89.451-200 200-200m140.204 130.267l-22.536-22.718c-4.667-4.705-12.265-4.736-16.97-.068L215.346 303.697l-59.792-60.277c-4.667-4.705-12.265-4.736-16.97-.069l-22.719 22.536c-4.705 4.667-4.736 12.265-.068 16.971l90.781 91.516c4.667 4.705 12.265 4.736 16.97.068l172.589-171.204c4.704-4.668 4.734-12.266.067-16.971z"></path></svg>																	<span>
			Até 5 Usuários								</span>
			</div>
			</li>
			</ul>
<div class="elementor-price-table__footer">
    <a class="elementor-price-table__button elementor-button elementor-size-md btn btn-success w-100"
       href="{{ url('/plano/contratar/9') }}"
       target="_blank"
       style="background-color: {{ Auth::user()->empresa->empresa->plano->plano_id == 9 ? '#FE7201' : '#28a745' }};">
       {{ Auth::user()->empresa->empresa->plano->plano_id == 9 ? 'Contratado' : 'Contratar' }}
    </a>
    <div class="elementor-price-table__additional_info"></div>
</div>
			</div>
			<div class="elementor-price-table__ribbon">
			<div class="elementor-price-table__ribbon-inner">
			popular				</div>
			</div>
			</div>
			</div>
			</div>
			</div>
			</div>


			<div data-elementor-type="section" data-elementor-id="436" class="elementor elementor-436" data-elementor-post-type="elementor_library">
			<div class="elementor-element elementor-element-f24c5c6 e-flex e-con-boxed e-con e-child" data-id="f24c5c6" data-element_type="container" >
			<div class="e-con-inner" >
			<div class="elementor-element elementor-element-c43e8a2 elementor-widget elementor-widget-price-table" data-id="c43e8a2" data-element_type="widget" data-widget_type="price-table.default">
			<div class="elementor-widget-container">
			<div class="elementor-price-table">
			<div class="elementor-price-table__header_diamante">
			<h3 class="elementor-price-table__heading">
			Plano Diamante						</h3>
			<span class="elementor-price-table__subheading">
			Plano para pequenas e médias empresas						</span>
			</div>
			<div class="elementor-price-table__price">
			<div class="elementor-price-table__original-price elementor-typo-excluded"><b>
			<span class="elementor-price-table__currency">R$</span>529					</div></b>
			<span class="elementor-price-table__currency">R$</span>									<span class="elementor-price-table__integer-part">
			259					</span>
			<div class="elementor-price-table__after-price">
			<span class="elementor-price-table__fractional-part">
			90						</span>
			</div>
			<span class="elementor-price-table__period elementor-typo-excluded"style="color: green;">Promoção</span>							</div>
			<ul class="elementor-price-table__features-list">
			<li class="elementor-repeater-item-19a20a0">
			<div class="elementor-price-table__feature-inner">
			<svg aria-hidden="true" class="e-font-icon-svg e-far-check-circle" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M256 8C119.033 8 8 119.033 8 256s111.033 248 248 248 248-111.033 248-248S392.967 8 256 8zm0 48c110.532 0 200 89.451 200 200 0 110.532-89.451 200-200 200-110.532 0-200-89.451-200-200 0-110.532 89.451-200 200-200m140.204 130.267l-22.536-22.718c-4.667-4.705-12.265-4.736-16.97-.068L215.346 303.697l-59.792-60.277c-4.667-4.705-12.265-4.736-16.97-.069l-22.719 22.536c-4.705 4.667-4.736 12.265-.068 16.971l90.781 91.516c4.667 4.705 12.265 4.736 16.97.068l172.589-171.204c4.704-4.668 4.734-12.266.067-16.971z"></path></svg>																	<span>
			Gestão de Vendas e Clientes									</span>
			</div>
			</li>
			<li class="elementor-repeater-item-5804794">
			<div class="elementor-price-table__feature-inner">
			<svg aria-hidden="true" class="e-font-icon-svg e-far-check-circle" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M256 8C119.033 8 8 119.033 8 256s111.033 248 248 248 248-111.033 248-248S392.967 8 256 8zm0 48c110.532 0 200 89.451 200 200 0 110.532-89.451 200-200 200-110.532 0-200-89.451-200-200 0-110.532 89.451-200 200-200m140.204 130.267l-22.536-22.718c-4.667-4.705-12.265-4.736-16.97-.068L215.346 303.697l-59.792-60.277c-4.667-4.705-12.265-4.736-16.97-.069l-22.719 22.536c-4.705 4.667-4.736 12.265-.068 16.971l90.781 91.516c4.667 4.705 12.265 4.736 16.97.068l172.589-171.204c4.704-4.668 4.734-12.266.067-16.971z"></path></svg>																	<span>
			CRM Integrado 									</span>
			</div>
			</li>
			<li class="elementor-repeater-item-393b067">
			<div class="elementor-price-table__feature-inner">
			<svg aria-hidden="true" class="e-font-icon-svg e-far-check-circle" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M256 8C119.033 8 8 119.033 8 256s111.033 248 248 248 248-111.033 248-248S392.967 8 256 8zm0 48c110.532 0 200 89.451 200 200 0 110.532-89.451 200-200 200-110.532 0-200-89.451-200-200 0-110.532 89.451-200 200-200m140.204 130.267l-22.536-22.718c-4.667-4.705-12.265-4.736-16.97-.068L215.346 303.697l-59.792-60.277c-4.667-4.705-12.265-4.736-16.97-.069l-22.719 22.536c-4.705 4.667-4.736 12.265-.068 16.971l90.781 91.516c4.667 4.705 12.265 4.736 16.97.068l172.589-171.204c4.704-4.668 4.734-12.266.067-16.971z"></path></svg>																	<span>
			Gestão de Estoque e Produtos 									</span>
			</div>
			</li>
			<li class="elementor-repeater-item-102b5b8">
			<div class="elementor-price-table__feature-inner">
			<svg aria-hidden="true" class="e-font-icon-svg e-far-check-circle" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M256 8C119.033 8 8 119.033 8 256s111.033 248 248 248 248-111.033 248-248S392.967 8 256 8zm0 48c110.532 0 200 89.451 200 200 0 110.532-89.451 200-200 200-110.532 0-200-89.451-200-200 0-110.532 89.451-200 200-200m140.204 130.267l-22.536-22.718c-4.667-4.705-12.265-4.736-16.97-.068L215.346 303.697l-59.792-60.277c-4.667-4.705-12.265-4.736-16.97-.069l-22.719 22.536c-4.705 4.667-4.736 12.265-.068 16.971l90.781 91.516c4.667 4.705 12.265 4.736 16.97.068l172.589-171.204c4.704-4.668 4.734-12.266.067-16.971z"></path></svg>																	<span>
			Emissão de NF-e, NFS-e, NFC-e;									</span>
			</div>
			</li>
			<li class="elementor-repeater-item-1332cab">
			<div class="elementor-price-table__feature-inner">
			<svg aria-hidden="true" class="e-font-icon-svg e-far-check-circle" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M256 8C119.033 8 8 119.033 8 256s111.033 248 248 248 248-111.033 248-248S392.967 8 256 8zm0 48c110.532 0 200 89.451 200 200 0 110.532-89.451 200-200 200-110.532 0-200-89.451-200-200 0-110.532 89.451-200 200-200m140.204 130.267l-22.536-22.718c-4.667-4.705-12.265-4.736-16.97-.068L215.346 303.697l-59.792-60.277c-4.667-4.705-12.265-4.736-16.97-.069l-22.719 22.536c-4.705 4.667-4.736 12.265-.068 16.971l90.781 91.516c4.667 4.705 12.265 4.736 16.97.068l172.589-171.204c4.704-4.668 4.734-12.266.067-16.971z"></path></svg>																	<span>
			Ideal para empresas de pequeno/ médio e grande porte;
			</span>
			</div>
			</li>
			<li class="elementor-repeater-item-a1ff7ed">
			<div class="elementor-price-table__feature-inner">
			<svg aria-hidden="true" class="e-font-icon-svg e-far-check-circle" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M256 8C119.033 8 8 119.033 8 256s111.033 248 248 248 248-111.033 248-248S392.967 8 256 8zm0 48c110.532 0 200 89.451 200 200 0 110.532-89.451 200-200 200-110.532 0-200-89.451-200-200 0-110.532 89.451-200 200-200m140.204 130.267l-22.536-22.718c-4.667-4.705-12.265-4.736-16.97-.068L215.346 303.697l-59.792-60.277c-4.667-4.705-12.265-4.736-16.97-.069l-22.719 22.536c-4.705 4.667-4.736 12.265-.068 16.971l90.781 91.516c4.667 4.705 12.265 4.736 16.97.068l172.589-171.204c4.704-4.668 4.734-12.266.067-16.971z"></path></svg>																	<span>
			Controle Financeiro, conta a pagar e contas a receber;									</span>
			</div>
			</li>
			<li class="elementor-repeater-item-84249dc">
			<div class="elementor-price-table__feature-inner">
			<svg aria-hidden="true" class="e-font-icon-svg e-far-check-circle" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M256 8C119.033 8 8 119.033 8 256s111.033 248 248 248 248-111.033 248-248S392.967 8 256 8zm0 48c110.532 0 200 89.451 200 200 0 110.532-89.451 200-200 200-110.532 0-200-89.451-200-200 0-110.532 89.451-200 200-200m140.204 130.267l-22.536-22.718c-4.667-4.705-12.265-4.736-16.97-.068L215.346 303.697l-59.792-60.277c-4.667-4.705-12.265-4.736-16.97-.069l-22.719 22.536c-4.705 4.667-4.736 12.265-.068 16.971l90.781 91.516c4.667 4.705 12.265 4.736 16.97.068l172.589-171.204c4.704-4.668 4.734-12.266.067-16.971z"></path></svg>																	<span>
			Gestão de Compras e Fornecedores;									</span>
			</div>
			</li>
			<li class="elementor-repeater-item-d835d07">
			<div class="elementor-price-table__feature-inner">
			<svg aria-hidden="true" class="e-font-icon-svg e-far-check-circle" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M256 8C119.033 8 8 119.033 8 256s111.033 248 248 248 248-111.033 248-248S392.967 8 256 8zm0 48c110.532 0 200 89.451 200 200 0 110.532-89.451 200-200 200-110.532 0-200-89.451-200-200 0-110.532 89.451-200 200-200m140.204 130.267l-22.536-22.718c-4.667-4.705-12.265-4.736-16.97-.068L215.346 303.697l-59.792-60.277c-4.667-4.705-12.265-4.736-16.97-.069l-22.719 22.536c-4.705 4.667-4.736 12.265-.068 16.971l90.781 91.516c4.667 4.705 12.265 4.736 16.97.068l172.589-171.204c4.704-4.668 4.734-12.266.067-16.971z"></path></svg>																	<span>
			Módulo de importação de dados;									</span>
			</div>
			</li>
			<li class="elementor-repeater-item-7b5f5d3">
			<div class="elementor-price-table__feature-inner">
			<svg aria-hidden="true" class="e-font-icon-svg e-far-check-circle" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M256 8C119.033 8 8 119.033 8 256s111.033 248 248 248 248-111.033 248-248S392.967 8 256 8zm0 48c110.532 0 200 89.451 200 200 0 110.532-89.451 200-200 200-110.532 0-200-89.451-200-200 0-110.532 89.451-200 200-200m140.204 130.267l-22.536-22.718c-4.667-4.705-12.265-4.736-16.97-.068L215.346 303.697l-59.792-60.277c-4.667-4.705-12.265-4.736-16.97-.069l-22.719 22.536c-4.705 4.667-4.736 12.265-.068 16.971l90.781 91.516c4.667 4.705 12.265 4.736 16.97.068l172.589-171.204c4.704-4.668 4.734-12.266.067-16.971z"></path></svg>																	<span>
			Relatórios gerenciais;									</span>
			</div>
			</li>
			<li class="elementor-repeater-item-e426b7c">
			<div class="elementor-price-table__feature-inner">
			<svg aria-hidden="true" class="e-font-icon-svg e-far-check-circle" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M256 8C119.033 8 8 119.033 8 256s111.033 248 248 248 248-111.033 248-248S392.967 8 256 8zm0 48c110.532 0 200 89.451 200 200 0 110.532-89.451 200-200 200-110.532 0-200-89.451-200-200 0-110.532 89.451-200 200-200m140.204 130.267l-22.536-22.718c-4.667-4.705-12.265-4.736-16.97-.068L215.346 303.697l-59.792-60.277c-4.667-4.705-12.265-4.736-16.97-.069l-22.719 22.536c-4.705 4.667-4.736 12.265-.068 16.971l90.781 91.516c4.667 4.705 12.265 4.736 16.97.068l172.589-171.204c4.704-4.668 4.734-12.266.067-16.971z"></path></svg>																	<span>
			Emissão de CT-e, Emissão de MDF-e;									</span>
			</div>
			</li>
			<li class="elementor-repeater-item-7820c82">
			<div class="elementor-price-table__feature-inner">
			<svg aria-hidden="true" class="e-font-icon-svg e-far-check-circle" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M256 8C119.033 8 8 119.033 8 256s111.033 248 248 248 248-111.033 248-248S392.967 8 256 8zm0 48c110.532 0 200 89.451 200 200 0 110.532-89.451 200-200 200-110.532 0-200-89.451-200-200 0-110.532 89.451-200 200-200m140.204 130.267l-22.536-22.718c-4.667-4.705-12.265-4.736-16.97-.068L215.346 303.697l-59.792-60.277c-4.667-4.705-12.265-4.736-16.97-.069l-22.719 22.536c-4.705 4.667-4.736 12.265-.068 16.971l90.781 91.516c4.667 4.705 12.265 4.736 16.97.068l172.589-171.204c4.704-4.668 4.734-12.266.067-16.971z"></path></svg>																	<span>
			Integração logística;									</span>
			</div>
			</li>
			<li class="elementor-repeater-item-b91b7b7">
			<div class="elementor-price-table__feature-inner">
			<svg aria-hidden="true" class="e-font-icon-svg e-far-check-circle" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M256 8C119.033 8 8 119.033 8 256s111.033 248 248 248 248-111.033 248-248S392.967 8 256 8zm0 48c110.532 0 200 89.451 200 200 0 110.532-89.451 200-200 200-110.532 0-200-89.451-200-200 0-110.532 89.451-200 200-200m140.204 130.267l-22.536-22.718c-4.667-4.705-12.265-4.736-16.97-.068L215.346 303.697l-59.792-60.277c-4.667-4.705-12.265-4.736-16.97-.069l-22.719 22.536c-4.705 4.667-4.736 12.265-.068 16.971l90.781 91.516c4.667 4.705 12.265 4.736 16.97.068l172.589-171.204c4.704-4.668 4.734-12.266.067-16.971z"></path></svg>																	<span>
			Relatórios adicionais com gráficos;									</span>
			</div>
			</li>
			<li class="elementor-repeater-item-20276c2">
			<div class="elementor-price-table__feature-inner">
			<svg aria-hidden="true" class="e-font-icon-svg e-far-check-circle" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M256 8C119.033 8 8 119.033 8 256s111.033 248 248 248 248-111.033 248-248S392.967 8 256 8zm0 48c110.532 0 200 89.451 200 200 0 110.532-89.451 200-200 200-110.532 0-200-89.451-200-200 0-110.532 89.451-200 200-200m140.204 130.267l-22.536-22.718c-4.667-4.705-12.265-4.736-16.97-.068L215.346 303.697l-59.792-60.277c-4.667-4.705-12.265-4.736-16.97-.069l-22.719 22.536c-4.705 4.667-4.736 12.265-.068 16.971l90.781 91.516c4.667 4.705 12.265 4.736 16.97.068l172.589-171.204c4.704-4.668 4.734-12.266.067-16.971z"></path></svg>																	<span>
			Frente de Caixa – PDV;									</span>
			</div>
			</li>
			<li class="elementor-repeater-item-e71c192">
			<div class="elementor-price-table__feature-inner">
			<svg aria-hidden="true" class="e-font-icon-svg e-far-check-circle" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M256 8C119.033 8 8 119.033 8 256s111.033 248 248 248 248-111.033 248-248S392.967 8 256 8zm0 48c110.532 0 200 89.451 200 200 0 110.532-89.451 200-200 200-110.532 0-200-89.451-200-200 0-110.532 89.451-200 200-200m140.204 130.267l-22.536-22.718c-4.667-4.705-12.265-4.736-16.97-.068L215.346 303.697l-59.792-60.277c-4.667-4.705-12.265-4.736-16.97-.069l-22.719 22.536c-4.705 4.667-4.736 12.265-.068 16.971l90.781 91.516c4.667 4.705 12.265 4.736 16.97.068l172.589-171.204c4.704-4.668 4.734-12.266.067-16.971z"></path></svg>																	<span>
			Até 20 usuários									</span>
			</div>
			</li>
			</ul>
<div class="elementor-price-table__footer">
    <a class="elementor-price-table__button elementor-button elementor-size-md btn btn-success w-100"
       href="{{ url('/plano/contratar/10') }}"
       target="_blank"
       style="background-color: {{ Auth::user()->empresa->empresa->plano->plano_id == 10 ? '#FE7201' : '#28a745' }};">
       {{ Auth::user()->empresa->empresa->plano->plano_id == 10 ? 'Contratado' : 'Contratar' }}
    </a>
    <div class="elementor-price-table__additional_info"></div>
</div>
			</div>
			</div>
			</div>
			</div>
			</div>
			</div>
			</div>
			</div>


			<div class="eael-tabs-content">
			<div id="planos-trimestrais-tab" class="clearfix eael-tab-content-item active" data-title-link="planos-trimestrais-tab">
			<div data-elementor-type="section" data-elementor-id="444" class="elementor elementor-444" data-elementor-post-type="elementor_library">
			<div class="elementor-element elementor-element-12d1d18 e-flex e-con-boxed e-con e-parent e-lazyloaded" data-id="12d1d18" data-element_type="container">
			<div class="e-con-inner">
			<div class="elementor-element elementor-element-d0f3654 e-flex e-con-boxed e-con e-child" data-id="d0f3654" data-element_type="container">
			<div class="e-con-inner">
			<div class="elementor-element elementor-element-7e91515 e-flex e-con-boxed e-con e-child" data-id="7e91515" data-element_type="container">
			<div class="e-con-inner">
			<div class="elementor-element elementor-element-089b9a1 elementor-widget__width-inherit elementor-widget elementor-widget-price-table" data-id="089b9a1" data-element_type="widget" data-widget_type="price-table.default">
			<div class="elementor-widget-container">
			<div class="elementor-price-table">
			<div class="elementor-price-table__header_prata">
			<h3 class="elementor-price-table__heading">
			Plano Prata						</h3>
			<span class="elementor-price-table__subheading">
			Plano para Microempresas						</span>
			</div>
			<div class="elementor-price-table__price">
			<div class="elementor-price-table__original-price elementor-typo-excluded"><b>
			<span class="elementor-price-table__currency">R$</span>99					</div></b>
			<span class="elementor-price-table__currency">R$</span>									<span class="elementor-price-table__integer-part">
			59					</span>
			<div class="elementor-price-table__after-price">
			<span class="elementor-price-table__fractional-part">
			90						</span>
			</div>
			<span class="elementor-price-table__period elementor-typo-excluded" style="color: green;">Promoção</span>							</div>
			<ul class="elementor-price-table__features-list">
			<li class="elementor-repeater-item-19a20a0">
			<div class="elementor-price-table__feature-inner">
			<svg aria-hidden="true" class="e-font-icon-svg e-far-check-circle" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M256 8C119.033 8 8 119.033 8 256s111.033 248 248 248 248-111.033 248-248S392.967 8 256 8zm0 48c110.532 0 200 89.451 200 200 0 110.532-89.451 200-200 200-110.532 0-200-89.451-200-200 0-110.532 89.451-200 200-200m140.204 130.267l-22.536-22.718c-4.667-4.705-12.265-4.736-16.97-.068L215.346 303.697l-59.792-60.277c-4.667-4.705-12.265-4.736-16.97-.069l-22.719 22.536c-4.705 4.667-4.736 12.265-.068 16.971l90.781 91.516c4.667 4.705 12.265 4.736 16.97.068l172.589-171.204c4.704-4.668 4.734-12.266.067-16.971z"></path></svg>																	<span>
			Gestão de Vendas e Clientes;									</span>
			</div>
			</li>
			<li class="elementor-repeater-item-a1ff7ed">
			<div class="elementor-price-table__feature-inner">
			<svg aria-hidden="true" class="e-font-icon-svg e-far-check-circle" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M256 8C119.033 8 8 119.033 8 256s111.033 248 248 248 248-111.033 248-248S392.967 8 256 8zm0 48c110.532 0 200 89.451 200 200 0 110.532-89.451 200-200 200-110.532 0-200-89.451-200-200 0-110.532 89.451-200 200-200m140.204 130.267l-22.536-22.718c-4.667-4.705-12.265-4.736-16.97-.068L215.346 303.697l-59.792-60.277c-4.667-4.705-12.265-4.736-16.97-.069l-22.719 22.536c-4.705 4.667-4.736 12.265-.068 16.971l90.781 91.516c4.667 4.705 12.265 4.736 16.97.068l172.589-171.204c4.704-4.668 4.734-12.266.067-16.971z"></path></svg>																	<span>
			CRM Integrado;									</span>
			</div>
			</li>
			<li class="elementor-repeater-item-84249dc">
			<div class="elementor-price-table__feature-inner">
			<svg aria-hidden="true" class="e-font-icon-svg e-far-check-circle" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M256 8C119.033 8 8 119.033 8 256s111.033 248 248 248 248-111.033 248-248S392.967 8 256 8zm0 48c110.532 0 200 89.451 200 200 0 110.532-89.451 200-200 200-110.532 0-200-89.451-200-200 0-110.532 89.451-200 200-200m140.204 130.267l-22.536-22.718c-4.667-4.705-12.265-4.736-16.97-.068L215.346 303.697l-59.792-60.277c-4.667-4.705-12.265-4.736-16.97-.069l-22.719 22.536c-4.705 4.667-4.736 12.265-.068 16.971l90.781 91.516c4.667 4.705 12.265 4.736 16.97.068l172.589-171.204c4.704-4.668 4.734-12.266.067-16.971z"></path></svg>																	<span>
			Gestão de Estoque e Produtos;									</span>
			</div>
			</li>
            <li class="elementor-repeater-item-a1ff7ed">
            <div class="elementor-price-table__feature-inner">
            <svg aria-hidden="true" class="e-font-icon-svg e-far-check-circle" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M256 8C119.033 8 8 119.033 8 256s111.033 248 248 248 248-111.033 248-248S392.967 8 256 8zm0 48c110.532 0 200 89.451 200 200 0 110.532-89.451 200-200 200-110.532 0-200-89.451-200-200 0-110.532 89.451-200 200-200m140.204 130.267l-22.536-22.718c-4.667-4.705-12.265-4.736-16.97-.068L215.346 303.697l-59.792-60.277c-4.667-4.705-12.265-4.736-16.97-.069l-22.719 22.536c-4.705 4.667-4.736 12.265-.068 16.971l90.781 91.516c4.667 4.705 12.265 4.736 16.97.068l172.589-171.204c4.704-4.668 4.734-12.266.067-16.971z"></path></svg>																	<span>
            Emissão de Notas Fiscais NF-e NFC-e NFS-e;
            </div>
            </li>
			<li class="elementor-repeater-item-bd4ea80">
			<div class="elementor-price-table__feature-inner">
			<svg aria-hidden="true" class="e-font-icon-svg e-far-check-circle" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M256 8C119.033 8 8 119.033 8 256s111.033 248 248 248 248-111.033 248-248S392.967 8 256 8zm0 48c110.532 0 200 89.451 200 200 0 110.532-89.451 200-200 200-110.532 0-200-89.451-200-200 0-110.532 89.451-200 200-200m140.204 130.267l-22.536-22.718c-4.667-4.705-12.265-4.736-16.97-.068L215.346 303.697l-59.792-60.277c-4.667-4.705-12.265-4.736-16.97-.069l-22.719 22.536c-4.705 4.667-4.736 12.265-.068 16.971l90.781 91.516c4.667 4.705 12.265 4.736 16.97.068l172.589-171.204c4.704-4.668 4.734-12.266.067-16.971z"></path></svg>																	<span>
			Ideal para micro empresas e MEI;									</span>
			</div>
			</li>
            <li class="elementor-repeater-item-a1ff7ed">
            <div class="elementor-price-table__feature-inner">
            <svg aria-hidden="true" class="e-font-icon-svg e-far-check-circle" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M256 8C119.033 8 8 119.033 8 256s111.033 248 248 248 248-111.033 248-248S392.967 8 256 8zm0 48c110.532 0 200 89.451 200 200 0 110.532-89.451 200-200 200-110.532 0-200-89.451-200-200 0-110.532 89.451-200 200-200m140.204 130.267l-22.536-22.718c-4.667-4.705-12.265-4.736-16.97-.068L215.346 303.697l-59.792-60.277c-4.667-4.705-12.265-4.736-16.97-.069l-22.719 22.536c-4.705 4.667-4.736 12.265-.068 16.971l90.781 91.516c4.667 4.705 12.265 4.736 16.97.068l172.589-171.204c4.704-4.668 4.734-12.266.067-16.971z"></path></svg>																	<span>
            Controle financeiro conta a pagar e contas a receber;
            </div>
            </li>
			<li class="elementor-repeater-item-96fea14">
			<div class="elementor-price-table__feature-inner">
			<svg aria-hidden="true" class="e-font-icon-svg e-far-check-circle" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M256 8C119.033 8 8 119.033 8 256s111.033 248 248 248 248-111.033 248-248S392.967 8 256 8zm0 48c110.532 0 200 89.451 200 200 0 110.532-89.451 200-200 200-110.532 0-200-89.451-200-200 0-110.532 89.451-200 200-200m140.204 130.267l-22.536-22.718c-4.667-4.705-12.265-4.736-16.97-.068L215.346 303.697l-59.792-60.277c-4.667-4.705-12.265-4.736-16.97-.069l-22.719 22.536c-4.705 4.667-4.736 12.265-.068 16.971l90.781 91.516c4.667 4.705 12.265 4.736 16.97.068l172.589-171.204c4.704-4.668 4.734-12.266.067-16.971z"></path></svg>																	<span>
			Até 1 usuário;									</span>
			</div>
			</li>
			</ul>
<div class="elementor-price-table__footer">
    <a class="elementor-price-table__button elementor-button elementor-size-md btn btn-success w-100"
       href="{{ url('/plano/contratar/5') }}"
       target="_blank"
       style="background-color: {{ Auth::user()->empresa->empresa->plano->plano_id == 5 ? '#FE7201' : '#28a745' }};">
       {{ Auth::user()->empresa->empresa->plano->plano_id == 5 ? 'Contratado' : 'Contratar' }}
    </a>
    <div class="elementor-price-table__additional_info"></div>
</div>
			</div>
			</div>
			</div>
			</div>
			</div>
			</div>
			</div>
			</div>
			</div>
			</div>


			<div data-elementor-type="section" data-elementor-id="444" class="elementor elementor-444" data-elementor-post-type="elementor_library">
			<div class="elementor-element elementor-element-bec27ff e-flex e-con-boxed e-con e-child" data-id="bec27ff" data-element_type="container">
			<div class="e-con-inner">
			<div class="elementor-element elementor-element-a6369f0 elementor-widget elementor-widget-price-table" data-id="a6369f0" data-element_type="widget" data-widget_type="price-table.default">
			<div class="elementor-widget-container">
			<div class="elementor-price-table">
			<div class="elementor-price-table__header_ouro">
			<h3 class="elementor-price-table__heading">
			Plano Ouro						</h3>
			<span class="elementor-price-table__subheading">
			Plano para pequenas empresas						</span>
			</div>
			<div class="elementor-price-table__price">
			<div class="elementor-price-table__original-price elementor-typo-excluded"><b>
			<span class="elementor-price-table__currency">R$</span>359					</div></b>
			<span class="elementor-price-table__currency">R$</span>									<span class="elementor-price-table__integer-part">
			219					</span>
			<div class="elementor-price-table__after-price">
			<span class="elementor-price-table__fractional-part">
			90						</span>
			</div>
			<span class="elementor-price-table__period elementor-typo-excluded" style="color: green;">Promoção</span>							</div>
			<ul class="elementor-price-table__features-list">
			<li class="elementor-repeater-item-19a20a0">
			<div class="elementor-price-table__feature-inner">
			<svg aria-hidden="true" class="e-font-icon-svg e-far-check-circle" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M256 8C119.033 8 8 119.033 8 256s111.033 248 248 248 248-111.033 248-248S392.967 8 256 8zm0 48c110.532 0 200 89.451 200 200 0 110.532-89.451 200-200 200-110.532 0-200-89.451-200-200 0-110.532 89.451-200 200-200m140.204 130.267l-22.536-22.718c-4.667-4.705-12.265-4.736-16.97-.068L215.346 303.697l-59.792-60.277c-4.667-4.705-12.265-4.736-16.97-.069l-22.719 22.536c-4.705 4.667-4.736 12.265-.068 16.971l90.781 91.516c4.667 4.705 12.265 4.736 16.97.068l172.589-171.204c4.704-4.668 4.734-12.266.067-16.971z"></path></svg>																	<span>
			Gestão de Vendas e Clientes;									</span>
			</div>
			</li>
			<li class="elementor-repeater-item-5804794">
			<div class="elementor-price-table__feature-inner">
			<svg aria-hidden="true" class="e-font-icon-svg e-far-check-circle" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M256 8C119.033 8 8 119.033 8 256s111.033 248 248 248 248-111.033 248-248S392.967 8 256 8zm0 48c110.532 0 200 89.451 200 200 0 110.532-89.451 200-200 200-110.532 0-200-89.451-200-200 0-110.532 89.451-200 200-200m140.204 130.267l-22.536-22.718c-4.667-4.705-12.265-4.736-16.97-.068L215.346 303.697l-59.792-60.277c-4.667-4.705-12.265-4.736-16.97-.069l-22.719 22.536c-4.705 4.667-4.736 12.265-.068 16.971l90.781 91.516c4.667 4.705 12.265 4.736 16.97.068l172.589-171.204c4.704-4.668 4.734-12.266.067-16.971z"></path></svg>																	<span>
			CRM Integrado;									</span>
			</div>
			</li>
			<li class="elementor-repeater-item-393b067">
			<div class="elementor-price-table__feature-inner">
			<svg aria-hidden="true" class="e-font-icon-svg e-far-check-circle" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M256 8C119.033 8 8 119.033 8 256s111.033 248 248 248 248-111.033 248-248S392.967 8 256 8zm0 48c110.532 0 200 89.451 200 200 0 110.532-89.451 200-200 200-110.532 0-200-89.451-200-200 0-110.532 89.451-200 200-200m140.204 130.267l-22.536-22.718c-4.667-4.705-12.265-4.736-16.97-.068L215.346 303.697l-59.792-60.277c-4.667-4.705-12.265-4.736-16.97-.069l-22.719 22.536c-4.705 4.667-4.736 12.265-.068 16.971l90.781 91.516c4.667 4.705 12.265 4.736 16.97.068l172.589-171.204c4.704-4.668 4.734-12.266.067-16.971z"></path></svg>																	<span>
			Gestão de Estoque e Produtos;									</span>
			</div>
			</li>
			<li class="elementor-repeater-item-102b5b8">
			<div class="elementor-price-table__feature-inner">
			<svg aria-hidden="true" class="e-font-icon-svg e-far-check-circle" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M256 8C119.033 8 8 119.033 8 256s111.033 248 248 248 248-111.033 248-248S392.967 8 256 8zm0 48c110.532 0 200 89.451 200 200 0 110.532-89.451 200-200 200-110.532 0-200-89.451-200-200 0-110.532 89.451-200 200-200m140.204 130.267l-22.536-22.718c-4.667-4.705-12.265-4.736-16.97-.068L215.346 303.697l-59.792-60.277c-4.667-4.705-12.265-4.736-16.97-.069l-22.719 22.536c-4.705 4.667-4.736 12.265-.068 16.971l90.781 91.516c4.667 4.705 12.265 4.736 16.97.068l172.589-171.204c4.704-4.668 4.734-12.266.067-16.971z"></path></svg>																	<span>
			Emissão de NF-e, NFS-e, NFC-e;									</span>
			</div>
			</li>
			<li class="elementor-repeater-item-1332cab">
			<div class="elementor-price-table__feature-inner">
			<svg aria-hidden="true" class="e-font-icon-svg e-far-check-circle" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M256 8C119.033 8 8 119.033 8 256s111.033 248 248 248 248-111.033 248-248S392.967 8 256 8zm0 48c110.532 0 200 89.451 200 200 0 110.532-89.451 200-200 200-110.532 0-200-89.451-200-200 0-110.532 89.451-200 200-200m140.204 130.267l-22.536-22.718c-4.667-4.705-12.265-4.736-16.97-.068L215.346 303.697l-59.792-60.277c-4.667-4.705-12.265-4.736-16.97-.069l-22.719 22.536c-4.705 4.667-4.736 12.265-.068 16.971l90.781 91.516c4.667 4.705 12.265 4.736 16.97.068l172.589-171.204c4.704-4.668 4.734-12.266.067-16.971z"></path></svg>																	<span>
			Ideal para empresas de pequenas e médio porte;									</span>
			</div>
			</li>
			<li class="elementor-repeater-item-a1ff7ed">
			<div class="elementor-price-table__feature-inner">
			<svg aria-hidden="true" class="e-font-icon-svg e-far-check-circle" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M256 8C119.033 8 8 119.033 8 256s111.033 248 248 248 248-111.033 248-248S392.967 8 256 8zm0 48c110.532 0 200 89.451 200 200 0 110.532-89.451 200-200 200-110.532 0-200-89.451-200-200 0-110.532 89.451-200 200-200m140.204 130.267l-22.536-22.718c-4.667-4.705-12.265-4.736-16.97-.068L215.346 303.697l-59.792-60.277c-4.667-4.705-12.265-4.736-16.97-.069l-22.719 22.536c-4.705 4.667-4.736 12.265-.068 16.971l90.781 91.516c4.667 4.705 12.265 4.736 16.97.068l172.589-171.204c4.704-4.668 4.734-12.266.067-16.971z"></path></svg>																	<span>
			Controle Financeiro, conta a pagar e contas a receber;									</span>
			</div>
			</li>
			<li class="elementor-repeater-item-84249dc">
			<div class="elementor-price-table__feature-inner">
			<svg aria-hidden="true" class="e-font-icon-svg e-far-check-circle" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M256 8C119.033 8 8 119.033 8 256s111.033 248 248 248 248-111.033 248-248S392.967 8 256 8zm0 48c110.532 0 200 89.451 200 200 0 110.532-89.451 200-200 200-110.532 0-200-89.451-200-200 0-110.532 89.451-200 200-200m140.204 130.267l-22.536-22.718c-4.667-4.705-12.265-4.736-16.97-.068L215.346 303.697l-59.792-60.277c-4.667-4.705-12.265-4.736-16.97-.069l-22.719 22.536c-4.705 4.667-4.736 12.265-.068 16.971l90.781 91.516c4.667 4.705 12.265 4.736 16.97.068l172.589-171.204c4.704-4.668 4.734-12.266.067-16.971z"></path></svg>																	<span>
			Gestão de Compras e Fornecedores;									</span>
			</div>
			</li>
			<li class="elementor-repeater-item-d835d07">
			<div class="elementor-price-table__feature-inner">
			<svg aria-hidden="true" class="e-font-icon-svg e-far-check-circle" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M256 8C119.033 8 8 119.033 8 256s111.033 248 248 248 248-111.033 248-248S392.967 8 256 8zm0 48c110.532 0 200 89.451 200 200 0 110.532-89.451 200-200 200-110.532 0-200-89.451-200-200 0-110.532 89.451-200 200-200m140.204 130.267l-22.536-22.718c-4.667-4.705-12.265-4.736-16.97-.068L215.346 303.697l-59.792-60.277c-4.667-4.705-12.265-4.736-16.97-.069l-22.719 22.536c-4.705 4.667-4.736 12.265-.068 16.971l90.781 91.516c4.667 4.705 12.265 4.736 16.97.068l172.589-171.204c4.704-4.668 4.734-12.266.067-16.971z"></path></svg>																	<span>
			Módulo de importação de dados;									</span>
			</div>
			</li>
			<li class="elementor-repeater-item-7b5f5d3">
			<div class="elementor-price-table__feature-inner">
			<svg aria-hidden="true" class="e-font-icon-svg e-far-check-circle" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M256 8C119.033 8 8 119.033 8 256s111.033 248 248 248 248-111.033 248-248S392.967 8 256 8zm0 48c110.532 0 200 89.451 200 200 0 110.532-89.451 200-200 200-110.532 0-200-89.451-200-200 0-110.532 89.451-200 200-200m140.204 130.267l-22.536-22.718c-4.667-4.705-12.265-4.736-16.97-.068L215.346 303.697l-59.792-60.277c-4.667-4.705-12.265-4.736-16.97-.069l-22.719 22.536c-4.705 4.667-4.736 12.265-.068 16.971l90.781 91.516c4.667 4.705 12.265 4.736 16.97.068l172.589-171.204c4.704-4.668 4.734-12.266.067-16.971z"></path></svg>																	<span>
			Relatórios gerenciais;									</span>
			</div>
			</li>
            <li class="elementor-repeater-item-7b5f5d3">
            <div class="elementor-price-table__feature-inner">
            <svg aria-hidden="true" class="e-font-icon-svg e-far-check-circle" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M256 8C119.033 8 8 119.033 8 256s111.033 248 248 248 248-111.033 248-248S392.967 8 256 8zm0 48c110.532 0 200 89.451 200 200 0 110.532-89.451 200-200 200-110.532 0-200-89.451-200-200 0-110.532 89.451-200 200-200m140.204 130.267l-22.536-22.718c-4.667-4.705-12.265-4.736-16.97-.068L215.346 303.697l-59.792-60.277c-4.667-4.705-12.265-4.736-16.97-.069l-22.719 22.536c-4.705 4.667-4.736 12.265-.068 16.971l90.781 91.516c4.667 4.705 12.265 4.736 16.97.068l172.589-171.204c4.704-4.668 4.734-12.266.067-16.971z"></path></svg>																	<span>
            Emissão de CT-e, Emissão de MDF-e;
            </div>
            </li>
            <li class="elementor-repeater-item-7b5f5d3">
            <div class="elementor-price-table__feature-inner">
            <svg aria-hidden="true" class="e-font-icon-svg e-far-check-circle" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M256 8C119.033 8 8 119.033 8 256s111.033 248 248 248 248-111.033 248-248S392.967 8 256 8zm0 48c110.532 0 200 89.451 200 200 0 110.532-89.451 200-200 200-110.532 0-200-89.451-200-200 0-110.532 89.451-200 200-200m140.204 130.267l-22.536-22.718c-4.667-4.705-12.265-4.736-16.97-.068L215.346 303.697l-59.792-60.277c-4.667-4.705-12.265-4.736-16.97-.069l-22.719 22.536c-4.705 4.667-4.736 12.265-.068 16.971l90.781 91.516c4.667 4.705 12.265 4.736 16.97.068l172.589-171.204c4.704-4.668 4.734-12.266.067-16.971z"></path></svg>																	<span>
            Frente de Caixa – PDV;
            </div>
            </li>
			<li class="elementor-repeater-item-81bfb28">
			<div class="elementor-price-table__feature-inner">
			<svg aria-hidden="true" class="e-font-icon-svg e-far-check-circle" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M256 8C119.033 8 8 119.033 8 256s111.033 248 248 248 248-111.033 248-248S392.967 8 256 8zm0 48c110.532 0 200 89.451 200 200 0 110.532-89.451 200-200 200-110.532 0-200-89.451-200-200 0-110.532 89.451-200 200-200m140.204 130.267l-22.536-22.718c-4.667-4.705-12.265-4.736-16.97-.068L215.346 303.697l-59.792-60.277c-4.667-4.705-12.265-4.736-16.97-.069l-22.719 22.536c-4.705 4.667-4.736 12.265-.068 16.971l90.781 91.516c4.667 4.705 12.265 4.736 16.97.068l172.589-171.204c4.704-4.668 4.734-12.266.067-16.971z"></path></svg>																	<span>
			Até 5 Usuários								</span>
			</div>
			</li>
			</ul>
<div class="elementor-price-table__footer">
    <a class="elementor-price-table__button elementor-button elementor-size-md btn btn-success w-100"
       href="{{ url('/plano/contratar/6') }}"
       target="_blank"
       style="background-color: {{ Auth::user()->empresa->empresa->plano->plano_id == 6 ? '#FE7201' : '#28a745' }};">
       {{ Auth::user()->empresa->empresa->plano->plano_id == 6 ? 'Contratado' : 'Contratar' }}
    </a>
    <div class="elementor-price-table__additional_info"></div>
</div>
			</div>
			<div class="elementor-price-table__ribbon">
			<div class="elementor-price-table__ribbon-inner">
			popular				</div>
			</div>
			</div>
			</div>
			</div>
			</div>
			</div>


			<div data-elementor-type="section" data-elementor-id="444" class="elementor elementor-444" data-elementor-post-type="elementor_library">
			<div class="elementor-element elementor-element-f24c5c6 e-flex e-con-boxed e-con e-child" data-id="f24c5c6" data-element_type="container">
			<div class="e-con-inner">
			<div class="elementor-element elementor-element-c43e8a2 elementor-widget elementor-widget-price-table" data-id="c43e8a2" data-element_type="widget" data-widget_type="price-table.default">
			<div class="elementor-widget-container">
			<div class="elementor-price-table">
			<div class="elementor-price-table__header_diamante">
			<h3 class="elementor-price-table__heading">
			Plano Diamante						</h3>
			<span class="elementor-price-table__subheading">
			Plano para pequenas e médias empresas						</span>
			</div>
			<div class="elementor-price-table__price">
			<div class="elementor-price-table__original-price elementor-typo-excluded"><b>
			<span class="elementor-price-table__currency">R$</span>529					</div></b>
			<span class="elementor-price-table__currency">R$</span>									<span class="elementor-price-table__integer-part">
			299					</span>
			<div class="elementor-price-table__after-price">
			<span class="elementor-price-table__fractional-part">
			90						</span>
			</div>
			<span class="elementor-price-table__period elementor-typo-excluded" style="color: green;">Promoção</span>							</div>
			<ul class="elementor-price-table__features-list">
			<li class="elementor-repeater-item-19a20a0">
			<div class="elementor-price-table__feature-inner">
			<svg aria-hidden="true" class="e-font-icon-svg e-far-check-circle" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M256 8C119.033 8 8 119.033 8 256s111.033 248 248 248 248-111.033 248-248S392.967 8 256 8zm0 48c110.532 0 200 89.451 200 200 0 110.532-89.451 200-200 200-110.532 0-200-89.451-200-200 0-110.532 89.451-200 200-200m140.204 130.267l-22.536-22.718c-4.667-4.705-12.265-4.736-16.97-.068L215.346 303.697l-59.792-60.277c-4.667-4.705-12.265-4.736-16.97-.069l-22.719 22.536c-4.705 4.667-4.736 12.265-.068 16.971l90.781 91.516c4.667 4.705 12.265 4.736 16.97.068l172.589-171.204c4.704-4.668 4.734-12.266.067-16.971z"></path></svg>																	<span>
			Gestão de Vendas e Clientes									</span>
			</div>
			</li>
			<li class="elementor-repeater-item-5804794">
			<div class="elementor-price-table__feature-inner">
			<svg aria-hidden="true" class="e-font-icon-svg e-far-check-circle" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M256 8C119.033 8 8 119.033 8 256s111.033 248 248 248 248-111.033 248-248S392.967 8 256 8zm0 48c110.532 0 200 89.451 200 200 0 110.532-89.451 200-200 200-110.532 0-200-89.451-200-200 0-110.532 89.451-200 200-200m140.204 130.267l-22.536-22.718c-4.667-4.705-12.265-4.736-16.97-.068L215.346 303.697l-59.792-60.277c-4.667-4.705-12.265-4.736-16.97-.069l-22.719 22.536c-4.705 4.667-4.736 12.265-.068 16.971l90.781 91.516c4.667 4.705 12.265 4.736 16.97.068l172.589-171.204c4.704-4.668 4.734-12.266.067-16.971z"></path></svg>																	<span>
			CRM Integrado 									</span>
			</div>
			</li>
			<li class="elementor-repeater-item-393b067">
			<div class="elementor-price-table__feature-inner">
			<svg aria-hidden="true" class="e-font-icon-svg e-far-check-circle" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M256 8C119.033 8 8 119.033 8 256s111.033 248 248 248 248-111.033 248-248S392.967 8 256 8zm0 48c110.532 0 200 89.451 200 200 0 110.532-89.451 200-200 200-110.532 0-200-89.451-200-200 0-110.532 89.451-200 200-200m140.204 130.267l-22.536-22.718c-4.667-4.705-12.265-4.736-16.97-.068L215.346 303.697l-59.792-60.277c-4.667-4.705-12.265-4.736-16.97-.069l-22.719 22.536c-4.705 4.667-4.736 12.265-.068 16.971l90.781 91.516c4.667 4.705 12.265 4.736 16.97.068l172.589-171.204c4.704-4.668 4.734-12.266.067-16.971z"></path></svg>																	<span>
			Gestão de Estoque e Produtos 									</span>
			</div>
			</li>
			<li class="elementor-repeater-item-102b5b8">
			<div class="elementor-price-table__feature-inner">
			<svg aria-hidden="true" class="e-font-icon-svg e-far-check-circle" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M256 8C119.033 8 8 119.033 8 256s111.033 248 248 248 248-111.033 248-248S392.967 8 256 8zm0 48c110.532 0 200 89.451 200 200 0 110.532-89.451 200-200 200-110.532 0-200-89.451-200-200 0-110.532 89.451-200 200-200m140.204 130.267l-22.536-22.718c-4.667-4.705-12.265-4.736-16.97-.068L215.346 303.697l-59.792-60.277c-4.667-4.705-12.265-4.736-16.97-.069l-22.719 22.536c-4.705 4.667-4.736 12.265-.068 16.971l90.781 91.516c4.667 4.705 12.265 4.736 16.97.068l172.589-171.204c4.704-4.668 4.734-12.266.067-16.971z"></path></svg>																	<span>
			Emissão de NF-e, NFS-e, NFC-e;									</span>
			</div>
			</li>
			<li class="elementor-repeater-item-1332cab">
			<div class="elementor-price-table__feature-inner">
			<svg aria-hidden="true" class="e-font-icon-svg e-far-check-circle" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M256 8C119.033 8 8 119.033 8 256s111.033 248 248 248 248-111.033 248-248S392.967 8 256 8zm0 48c110.532 0 200 89.451 200 200 0 110.532-89.451 200-200 200-110.532 0-200-89.451-200-200 0-110.532 89.451-200 200-200m140.204 130.267l-22.536-22.718c-4.667-4.705-12.265-4.736-16.97-.068L215.346 303.697l-59.792-60.277c-4.667-4.705-12.265-4.736-16.97-.069l-22.719 22.536c-4.705 4.667-4.736 12.265-.068 16.971l90.781 91.516c4.667 4.705 12.265 4.736 16.97.068l172.589-171.204c4.704-4.668 4.734-12.266.067-16.971z"></path></svg>																	<span>
			Ideal para empresas de pequeno/ médio e grande porte;
			</span>
			</div>
			</li>
			<li class="elementor-repeater-item-a1ff7ed">
			<div class="elementor-price-table__feature-inner">
			<svg aria-hidden="true" class="e-font-icon-svg e-far-check-circle" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M256 8C119.033 8 8 119.033 8 256s111.033 248 248 248 248-111.033 248-248S392.967 8 256 8zm0 48c110.532 0 200 89.451 200 200 0 110.532-89.451 200-200 200-110.532 0-200-89.451-200-200 0-110.532 89.451-200 200-200m140.204 130.267l-22.536-22.718c-4.667-4.705-12.265-4.736-16.97-.068L215.346 303.697l-59.792-60.277c-4.667-4.705-12.265-4.736-16.97-.069l-22.719 22.536c-4.705 4.667-4.736 12.265-.068 16.971l90.781 91.516c4.667 4.705 12.265 4.736 16.97.068l172.589-171.204c4.704-4.668 4.734-12.266.067-16.971z"></path></svg>																	<span>
			Controle Financeiro, conta a pagar e contas a receber;									</span>
			</div>
			</li>
			<li class="elementor-repeater-item-84249dc">
			<div class="elementor-price-table__feature-inner">
			<svg aria-hidden="true" class="e-font-icon-svg e-far-check-circle" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M256 8C119.033 8 8 119.033 8 256s111.033 248 248 248 248-111.033 248-248S392.967 8 256 8zm0 48c110.532 0 200 89.451 200 200 0 110.532-89.451 200-200 200-110.532 0-200-89.451-200-200 0-110.532 89.451-200 200-200m140.204 130.267l-22.536-22.718c-4.667-4.705-12.265-4.736-16.97-.068L215.346 303.697l-59.792-60.277c-4.667-4.705-12.265-4.736-16.97-.069l-22.719 22.536c-4.705 4.667-4.736 12.265-.068 16.971l90.781 91.516c4.667 4.705 12.265 4.736 16.97.068l172.589-171.204c4.704-4.668 4.734-12.266.067-16.971z"></path></svg>																	<span>
			Gestão de Compras e Fornecedores;									</span>
			</div>
			</li>
			<li class="elementor-repeater-item-d835d07">
			<div class="elementor-price-table__feature-inner">
			<svg aria-hidden="true" class="e-font-icon-svg e-far-check-circle" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M256 8C119.033 8 8 119.033 8 256s111.033 248 248 248 248-111.033 248-248S392.967 8 256 8zm0 48c110.532 0 200 89.451 200 200 0 110.532-89.451 200-200 200-110.532 0-200-89.451-200-200 0-110.532 89.451-200 200-200m140.204 130.267l-22.536-22.718c-4.667-4.705-12.265-4.736-16.97-.068L215.346 303.697l-59.792-60.277c-4.667-4.705-12.265-4.736-16.97-.069l-22.719 22.536c-4.705 4.667-4.736 12.265-.068 16.971l90.781 91.516c4.667 4.705 12.265 4.736 16.97.068l172.589-171.204c4.704-4.668 4.734-12.266.067-16.971z"></path></svg>																	<span>
			Módulo de importação de dados;									</span>
			</div>
			</li>
			<li class="elementor-repeater-item-7b5f5d3">
			<div class="elementor-price-table__feature-inner">
			<svg aria-hidden="true" class="e-font-icon-svg e-far-check-circle" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M256 8C119.033 8 8 119.033 8 256s111.033 248 248 248 248-111.033 248-248S392.967 8 256 8zm0 48c110.532 0 200 89.451 200 200 0 110.532-89.451 200-200 200-110.532 0-200-89.451-200-200 0-110.532 89.451-200 200-200m140.204 130.267l-22.536-22.718c-4.667-4.705-12.265-4.736-16.97-.068L215.346 303.697l-59.792-60.277c-4.667-4.705-12.265-4.736-16.97-.069l-22.719 22.536c-4.705 4.667-4.736 12.265-.068 16.971l90.781 91.516c4.667 4.705 12.265 4.736 16.97.068l172.589-171.204c4.704-4.668 4.734-12.266.067-16.971z"></path></svg>																	<span>
			Relatórios gerenciais;									</span>
			</div>
			</li>
			<li class="elementor-repeater-item-e426b7c">
			<div class="elementor-price-table__feature-inner">
			<svg aria-hidden="true" class="e-font-icon-svg e-far-check-circle" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M256 8C119.033 8 8 119.033 8 256s111.033 248 248 248 248-111.033 248-248S392.967 8 256 8zm0 48c110.532 0 200 89.451 200 200 0 110.532-89.451 200-200 200-110.532 0-200-89.451-200-200 0-110.532 89.451-200 200-200m140.204 130.267l-22.536-22.718c-4.667-4.705-12.265-4.736-16.97-.068L215.346 303.697l-59.792-60.277c-4.667-4.705-12.265-4.736-16.97-.069l-22.719 22.536c-4.705 4.667-4.736 12.265-.068 16.971l90.781 91.516c4.667 4.705 12.265 4.736 16.97.068l172.589-171.204c4.704-4.668 4.734-12.266.067-16.971z"></path></svg>																	<span>
			Emissão de CT-e, Emissão de MDF-e;									</span>
			</div>
			</li>
			<li class="elementor-repeater-item-7820c82">
			<div class="elementor-price-table__feature-inner">
			<svg aria-hidden="true" class="e-font-icon-svg e-far-check-circle" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M256 8C119.033 8 8 119.033 8 256s111.033 248 248 248 248-111.033 248-248S392.967 8 256 8zm0 48c110.532 0 200 89.451 200 200 0 110.532-89.451 200-200 200-110.532 0-200-89.451-200-200 0-110.532 89.451-200 200-200m140.204 130.267l-22.536-22.718c-4.667-4.705-12.265-4.736-16.97-.068L215.346 303.697l-59.792-60.277c-4.667-4.705-12.265-4.736-16.97-.069l-22.719 22.536c-4.705 4.667-4.736 12.265-.068 16.971l90.781 91.516c4.667 4.705 12.265 4.736 16.97.068l172.589-171.204c4.704-4.668 4.734-12.266.067-16.971z"></path></svg>																	<span>
			Integração logística;									</span>
			</div>
			</li>
			<li class="elementor-repeater-item-b91b7b7">
			<div class="elementor-price-table__feature-inner">
			<svg aria-hidden="true" class="e-font-icon-svg e-far-check-circle" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M256 8C119.033 8 8 119.033 8 256s111.033 248 248 248 248-111.033 248-248S392.967 8 256 8zm0 48c110.532 0 200 89.451 200 200 0 110.532-89.451 200-200 200-110.532 0-200-89.451-200-200 0-110.532 89.451-200 200-200m140.204 130.267l-22.536-22.718c-4.667-4.705-12.265-4.736-16.97-.068L215.346 303.697l-59.792-60.277c-4.667-4.705-12.265-4.736-16.97-.069l-22.719 22.536c-4.705 4.667-4.736 12.265-.068 16.971l90.781 91.516c4.667 4.705 12.265 4.736 16.97.068l172.589-171.204c4.704-4.668 4.734-12.266.067-16.971z"></path></svg>																	<span>
			Relatórios adicionais com gráficos;									</span>
			</div>
			</li>
			<li class="elementor-repeater-item-20276c2">
			<div class="elementor-price-table__feature-inner">
			<svg aria-hidden="true" class="e-font-icon-svg e-far-check-circle" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M256 8C119.033 8 8 119.033 8 256s111.033 248 248 248 248-111.033 248-248S392.967 8 256 8zm0 48c110.532 0 200 89.451 200 200 0 110.532-89.451 200-200 200-110.532 0-200-89.451-200-200 0-110.532 89.451-200 200-200m140.204 130.267l-22.536-22.718c-4.667-4.705-12.265-4.736-16.97-.068L215.346 303.697l-59.792-60.277c-4.667-4.705-12.265-4.736-16.97-.069l-22.719 22.536c-4.705 4.667-4.736 12.265-.068 16.971l90.781 91.516c4.667 4.705 12.265 4.736 16.97.068l172.589-171.204c4.704-4.668 4.734-12.266.067-16.971z"></path></svg>																	<span>
			Frente de Caixa – PDV;									</span>
			</div>
			</li>
			<li class="elementor-repeater-item-e71c192">
			<div class="elementor-price-table__feature-inner">
			<svg aria-hidden="true" class="e-font-icon-svg e-far-check-circle" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M256 8C119.033 8 8 119.033 8 256s111.033 248 248 248 248-111.033 248-248S392.967 8 256 8zm0 48c110.532 0 200 89.451 200 200 0 110.532-89.451 200-200 200-110.532 0-200-89.451-200-200 0-110.532 89.451-200 200-200m140.204 130.267l-22.536-22.718c-4.667-4.705-12.265-4.736-16.97-.068L215.346 303.697l-59.792-60.277c-4.667-4.705-12.265-4.736-16.97-.069l-22.719 22.536c-4.705 4.667-4.736 12.265-.068 16.971l90.781 91.516c4.667 4.705 12.265 4.736 16.97.068l172.589-171.204c4.704-4.668 4.734-12.266.067-16.971z"></path></svg>																	<span>
			Até 20 usuários									</span>
			</div>
			</li>
			</ul>
<div class="elementor-price-table__footer">
    <a class="elementor-price-table__button elementor-button elementor-size-md btn btn-success w-100"
       href="{{ url('/plano/contratar/7') }}"
       target="_blank"
       style="background-color: {{ Auth::user()->empresa->empresa->plano->plano_id == 7 ? '#FE7201' : '#28a745' }};">
       {{ Auth::user()->empresa->empresa->plano->plano_id == 7 ? 'Contratado' : 'Contratar' }}
    </a>
    <div class="elementor-price-table__additional_info"></div>
</div>
			</div>
			</div>
			</div>
			</div>
			</div>
			</div>
			</div>
			</div>


			<div class="eael-tabs-content">
			<div id="planos-mensais-tab" class="clearfix eael-tab-content-item inactive" data-title-link="planos-mensais-tab">
			<div data-elementor-type="section" data-elementor-id="441" class="elementor elementor-441" data-elementor-post-type="elementor_library">
			<div class="elementor-element elementor-element-12d1d18 e-flex e-con-boxed e-con e-parent e-lazyloaded" data-id="12d1d18" data-element_type="container">
			<div class="e-con-inner">
			<div class="elementor-element elementor-element-d0f3654 e-flex e-con-boxed e-con e-child" data-id="d0f3654" data-element_type="container">
			<div class="e-con-inner">
			<div class="elementor-element elementor-element-7e91515 e-flex e-con-boxed e-con e-child" data-id="7e91515" data-element_type="container">
			<div class="e-con-inner">
			<div class="elementor-element elementor-element-089b9a1 elementor-widget__width-inherit elementor-widget elementor-widget-price-table" data-id="089b9a1" data-element_type="widget" data-widget_type="price-table.default">
			<div class="elementor-widget-container">
			<div class="elementor-price-table">
			<div class="elementor-price-table__header_prata">
			<h3 class="elementor-price-table__heading">
			Plano Prata						</h3>
			<span class="elementor-price-table__subheading">
			Plano para Microempresas						</span>
			</div>
			<div class="elementor-price-table__price">
			<div class="elementor-price-table__original-price elementor-typo-excluded"><b>
			<span class="elementor-price-table__currency">R$</span>99					</div></b>
			<span class="elementor-price-table__currency">R$</span>									<span class="elementor-price-table__integer-part">
			69					</span>
			<div class="elementor-price-table__after-price">
			<span class="elementor-price-table__fractional-part">
			90						</span>
			</div>
			<span class="elementor-price-table__period elementor-typo-excluded" style="color: green;">Promoção</span>							</div>
			<ul class="elementor-price-table__features-list">
			<li class="elementor-repeater-item-19a20a0">
			<div class="elementor-price-table__feature-inner">
			<svg aria-hidden="true" class="e-font-icon-svg e-far-check-circle" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M256 8C119.033 8 8 119.033 8 256s111.033 248 248 248 248-111.033 248-248S392.967 8 256 8zm0 48c110.532 0 200 89.451 200 200 0 110.532-89.451 200-200 200-110.532 0-200-89.451-200-200 0-110.532 89.451-200 200-200m140.204 130.267l-22.536-22.718c-4.667-4.705-12.265-4.736-16.97-.068L215.346 303.697l-59.792-60.277c-4.667-4.705-12.265-4.736-16.97-.069l-22.719 22.536c-4.705 4.667-4.736 12.265-.068 16.971l90.781 91.516c4.667 4.705 12.265 4.736 16.97.068l172.589-171.204c4.704-4.668 4.734-12.266.067-16.971z"></path></svg>																	<span>
			Gestão de Vendas e Clientes;									</span>
			</div>
			</li>
			<li class="elementor-repeater-item-a1ff7ed">
			<div class="elementor-price-table__feature-inner">
			<svg aria-hidden="true" class="e-font-icon-svg e-far-check-circle" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M256 8C119.033 8 8 119.033 8 256s111.033 248 248 248 248-111.033 248-248S392.967 8 256 8zm0 48c110.532 0 200 89.451 200 200 0 110.532-89.451 200-200 200-110.532 0-200-89.451-200-200 0-110.532 89.451-200 200-200m140.204 130.267l-22.536-22.718c-4.667-4.705-12.265-4.736-16.97-.068L215.346 303.697l-59.792-60.277c-4.667-4.705-12.265-4.736-16.97-.069l-22.719 22.536c-4.705 4.667-4.736 12.265-.068 16.971l90.781 91.516c4.667 4.705 12.265 4.736 16.97.068l172.589-171.204c4.704-4.668 4.734-12.266.067-16.971z"></path></svg>																	<span>
			CRM Integrado;									</span>
			</div>
			</li>
			<li class="elementor-repeater-item-84249dc">
			<div class="elementor-price-table__feature-inner">
			<svg aria-hidden="true" class="e-font-icon-svg e-far-check-circle" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M256 8C119.033 8 8 119.033 8 256s111.033 248 248 248 248-111.033 248-248S392.967 8 256 8zm0 48c110.532 0 200 89.451 200 200 0 110.532-89.451 200-200 200-110.532 0-200-89.451-200-200 0-110.532 89.451-200 200-200m140.204 130.267l-22.536-22.718c-4.667-4.705-12.265-4.736-16.97-.068L215.346 303.697l-59.792-60.277c-4.667-4.705-12.265-4.736-16.97-.069l-22.719 22.536c-4.705 4.667-4.736 12.265-.068 16.971l90.781 91.516c4.667 4.705 12.265 4.736 16.97.068l172.589-171.204c4.704-4.668 4.734-12.266.067-16.971z"></path></svg>																	<span>
			Gestão de Estoque e Produtos;									</span>
			</div>
			</li>
            <li class="elementor-repeater-item-a1ff7ed">
            <div class="elementor-price-table__feature-inner">
            <svg aria-hidden="true" class="e-font-icon-svg e-far-check-circle" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M256 8C119.033 8 8 119.033 8 256s111.033 248 248 248 248-111.033 248-248S392.967 8 256 8zm0 48c110.532 0 200 89.451 200 200 0 110.532-89.451 200-200 200-110.532 0-200-89.451-200-200 0-110.532 89.451-200 200-200m140.204 130.267l-22.536-22.718c-4.667-4.705-12.265-4.736-16.97-.068L215.346 303.697l-59.792-60.277c-4.667-4.705-12.265-4.736-16.97-.069l-22.719 22.536c-4.705 4.667-4.736 12.265-.068 16.971l90.781 91.516c4.667 4.705 12.265 4.736 16.97.068l172.589-171.204c4.704-4.668 4.734-12.266.067-16.971z"></path></svg>																	<span>
            Emissão de Notas Fiscais NF-e NFC-e NFS-e;
            </div>
            </li>
			<li class="elementor-repeater-item-bd4ea80">
			<div class="elementor-price-table__feature-inner">
			<svg aria-hidden="true" class="e-font-icon-svg e-far-check-circle" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M256 8C119.033 8 8 119.033 8 256s111.033 248 248 248 248-111.033 248-248S392.967 8 256 8zm0 48c110.532 0 200 89.451 200 200 0 110.532-89.451 200-200 200-110.532 0-200-89.451-200-200 0-110.532 89.451-200 200-200m140.204 130.267l-22.536-22.718c-4.667-4.705-12.265-4.736-16.97-.068L215.346 303.697l-59.792-60.277c-4.667-4.705-12.265-4.736-16.97-.069l-22.719 22.536c-4.705 4.667-4.736 12.265-.068 16.971l90.781 91.516c4.667 4.705 12.265 4.736 16.97.068l172.589-171.204c4.704-4.668 4.734-12.266.067-16.971z"></path></svg>																	<span>
			Ideal para micro empresas e MEI;									</span>
			</div>
			</li>
            <li class="elementor-repeater-item-a1ff7ed">
            <div class="elementor-price-table__feature-inner">
            <svg aria-hidden="true" class="e-font-icon-svg e-far-check-circle" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M256 8C119.033 8 8 119.033 8 256s111.033 248 248 248 248-111.033 248-248S392.967 8 256 8zm0 48c110.532 0 200 89.451 200 200 0 110.532-89.451 200-200 200-110.532 0-200-89.451-200-200 0-110.532 89.451-200 200-200m140.204 130.267l-22.536-22.718c-4.667-4.705-12.265-4.736-16.97-.068L215.346 303.697l-59.792-60.277c-4.667-4.705-12.265-4.736-16.97-.069l-22.719 22.536c-4.705 4.667-4.736 12.265-.068 16.971l90.781 91.516c4.667 4.705 12.265 4.736 16.97.068l172.589-171.204c4.704-4.668 4.734-12.266.067-16.971z"></path></svg>																	<span>
            Controle financeiro conta a pagar e contas a receber;
            </div>
            </li>
			<li class="elementor-repeater-item-96fea14">
			<div class="elementor-price-table__feature-inner">
			<svg aria-hidden="true" class="e-font-icon-svg e-far-check-circle" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M256 8C119.033 8 8 119.033 8 256s111.033 248 248 248 248-111.033 248-248S392.967 8 256 8zm0 48c110.532 0 200 89.451 200 200 0 110.532-89.451 200-200 200-110.532 0-200-89.451-200-200 0-110.532 89.451-200 200-200m140.204 130.267l-22.536-22.718c-4.667-4.705-12.265-4.736-16.97-.068L215.346 303.697l-59.792-60.277c-4.667-4.705-12.265-4.736-16.97-.069l-22.719 22.536c-4.705 4.667-4.736 12.265-.068 16.971l90.781 91.516c4.667 4.705 12.265 4.736 16.97.068l172.589-171.204c4.704-4.668 4.734-12.266.067-16.971z"></path></svg>																	<span>
			Até 1 usuário;									</span>
			</div>
			</li>
			</ul>
<div class="elementor-price-table__footer">
    <a class="elementor-price-table__button elementor-button elementor-size-md btn btn-success w-100"
       href="{{ url('/plano/contratar/2') }}"
       target="_blank"
       style="background-color: {{ Auth::user()->empresa->empresa->plano->plano_id == 2 ? '#FE7201' : '#28a745' }};">
       {{ Auth::user()->empresa->empresa->plano->plano_id == 2 ? 'Contratado' : 'Contratar' }}
    </a>
    <div class="elementor-price-table__additional_info"></div>
</div>
			</div>
			</div>
			</div>
			</div>
			</div>
			</div>
			</div>
			</div>
			</div>
			</div>




			<div data-elementor-type="section" data-elementor-id="441" class="elementor elementor-441" data-elementor-post-type="elementor_library">
			<div class="elementor-element elementor-element-bec27ff e-flex e-con-boxed e-con e-child" data-id="bec27ff" data-element_type="container">
			<div class="e-con-inner">
			<div class="elementor-element elementor-element-a6369f0 elementor-widget elementor-widget-price-table" data-id="a6369f0" data-element_type="widget" data-widget_type="price-table.default">
			<div class="elementor-widget-container">
			<div class="elementor-price-table">
			<div class="elementor-price-table__header_ouro">
			<h3 class="elementor-price-table__heading">
			Plano Ouro						</h3>
			<span class="elementor-price-table__subheading">
			Plano para pequenas empresas						</span>
			</div>
			<div class="elementor-price-table__price">
			<div class="elementor-price-table__original-price elementor-typo-excluded"><b>
			<span class="elementor-price-table__currency">R$</span>359					</div></b>
			<span class="elementor-price-table__currency">R$</span>									<span class="elementor-price-table__integer-part">
			249					</span>
			<div class="elementor-price-table__after-price">
			<span class="elementor-price-table__fractional-part">
			90						</span>
			</div>
			<span class="elementor-price-table__period elementor-typo-excluded" style="color: green;">Promoção</span>							</div>
			<ul class="elementor-price-table__features-list">
			<li class="elementor-repeater-item-19a20a0">
			<div class="elementor-price-table__feature-inner">
			<svg aria-hidden="true" class="e-font-icon-svg e-far-check-circle" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M256 8C119.033 8 8 119.033 8 256s111.033 248 248 248 248-111.033 248-248S392.967 8 256 8zm0 48c110.532 0 200 89.451 200 200 0 110.532-89.451 200-200 200-110.532 0-200-89.451-200-200 0-110.532 89.451-200 200-200m140.204 130.267l-22.536-22.718c-4.667-4.705-12.265-4.736-16.97-.068L215.346 303.697l-59.792-60.277c-4.667-4.705-12.265-4.736-16.97-.069l-22.719 22.536c-4.705 4.667-4.736 12.265-.068 16.971l90.781 91.516c4.667 4.705 12.265 4.736 16.97.068l172.589-171.204c4.704-4.668 4.734-12.266.067-16.971z"></path></svg>																	<span>
			Gestão de Vendas e Clientes;									</span>
			</div>
			</li>
			<li class="elementor-repeater-item-5804794">
			<div class="elementor-price-table__feature-inner">
			<svg aria-hidden="true" class="e-font-icon-svg e-far-check-circle" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M256 8C119.033 8 8 119.033 8 256s111.033 248 248 248 248-111.033 248-248S392.967 8 256 8zm0 48c110.532 0 200 89.451 200 200 0 110.532-89.451 200-200 200-110.532 0-200-89.451-200-200 0-110.532 89.451-200 200-200m140.204 130.267l-22.536-22.718c-4.667-4.705-12.265-4.736-16.97-.068L215.346 303.697l-59.792-60.277c-4.667-4.705-12.265-4.736-16.97-.069l-22.719 22.536c-4.705 4.667-4.736 12.265-.068 16.971l90.781 91.516c4.667 4.705 12.265 4.736 16.97.068l172.589-171.204c4.704-4.668 4.734-12.266.067-16.971z"></path></svg>																	<span>
			CRM Integrado;									</span>
			</div>
			</li>
			<li class="elementor-repeater-item-393b067">
			<div class="elementor-price-table__feature-inner">
			<svg aria-hidden="true" class="e-font-icon-svg e-far-check-circle" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M256 8C119.033 8 8 119.033 8 256s111.033 248 248 248 248-111.033 248-248S392.967 8 256 8zm0 48c110.532 0 200 89.451 200 200 0 110.532-89.451 200-200 200-110.532 0-200-89.451-200-200 0-110.532 89.451-200 200-200m140.204 130.267l-22.536-22.718c-4.667-4.705-12.265-4.736-16.97-.068L215.346 303.697l-59.792-60.277c-4.667-4.705-12.265-4.736-16.97-.069l-22.719 22.536c-4.705 4.667-4.736 12.265-.068 16.971l90.781 91.516c4.667 4.705 12.265 4.736 16.97.068l172.589-171.204c4.704-4.668 4.734-12.266.067-16.971z"></path></svg>																	<span>
			Gestão de Estoque e Produtos;									</span>
			</div>
			</li>
			<li class="elementor-repeater-item-102b5b8">
			<div class="elementor-price-table__feature-inner">
			<svg aria-hidden="true" class="e-font-icon-svg e-far-check-circle" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M256 8C119.033 8 8 119.033 8 256s111.033 248 248 248 248-111.033 248-248S392.967 8 256 8zm0 48c110.532 0 200 89.451 200 200 0 110.532-89.451 200-200 200-110.532 0-200-89.451-200-200 0-110.532 89.451-200 200-200m140.204 130.267l-22.536-22.718c-4.667-4.705-12.265-4.736-16.97-.068L215.346 303.697l-59.792-60.277c-4.667-4.705-12.265-4.736-16.97-.069l-22.719 22.536c-4.705 4.667-4.736 12.265-.068 16.971l90.781 91.516c4.667 4.705 12.265 4.736 16.97.068l172.589-171.204c4.704-4.668 4.734-12.266.067-16.971z"></path></svg>																	<span>
			Emissão de NF-e, NFS-e, NFC-e;									</span>
			</div>
			</li>
			<li class="elementor-repeater-item-1332cab">
			<div class="elementor-price-table__feature-inner">
			<svg aria-hidden="true" class="e-font-icon-svg e-far-check-circle" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M256 8C119.033 8 8 119.033 8 256s111.033 248 248 248 248-111.033 248-248S392.967 8 256 8zm0 48c110.532 0 200 89.451 200 200 0 110.532-89.451 200-200 200-110.532 0-200-89.451-200-200 0-110.532 89.451-200 200-200m140.204 130.267l-22.536-22.718c-4.667-4.705-12.265-4.736-16.97-.068L215.346 303.697l-59.792-60.277c-4.667-4.705-12.265-4.736-16.97-.069l-22.719 22.536c-4.705 4.667-4.736 12.265-.068 16.971l90.781 91.516c4.667 4.705 12.265 4.736 16.97.068l172.589-171.204c4.704-4.668 4.734-12.266.067-16.971z"></path></svg>																	<span>
			Ideal para empresas de pequenas e médio porte;									</span>
			</div>
			</li>
			<li class="elementor-repeater-item-a1ff7ed">
			<div class="elementor-price-table__feature-inner">
			<svg aria-hidden="true" class="e-font-icon-svg e-far-check-circle" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M256 8C119.033 8 8 119.033 8 256s111.033 248 248 248 248-111.033 248-248S392.967 8 256 8zm0 48c110.532 0 200 89.451 200 200 0 110.532-89.451 200-200 200-110.532 0-200-89.451-200-200 0-110.532 89.451-200 200-200m140.204 130.267l-22.536-22.718c-4.667-4.705-12.265-4.736-16.97-.068L215.346 303.697l-59.792-60.277c-4.667-4.705-12.265-4.736-16.97-.069l-22.719 22.536c-4.705 4.667-4.736 12.265-.068 16.971l90.781 91.516c4.667 4.705 12.265 4.736 16.97.068l172.589-171.204c4.704-4.668 4.734-12.266.067-16.971z"></path></svg>																	<span>
			Controle Financeiro, conta a pagar e contas a receber;									</span>
			</div>
			</li>
			<li class="elementor-repeater-item-84249dc">
			<div class="elementor-price-table__feature-inner">
			<svg aria-hidden="true" class="e-font-icon-svg e-far-check-circle" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M256 8C119.033 8 8 119.033 8 256s111.033 248 248 248 248-111.033 248-248S392.967 8 256 8zm0 48c110.532 0 200 89.451 200 200 0 110.532-89.451 200-200 200-110.532 0-200-89.451-200-200 0-110.532 89.451-200 200-200m140.204 130.267l-22.536-22.718c-4.667-4.705-12.265-4.736-16.97-.068L215.346 303.697l-59.792-60.277c-4.667-4.705-12.265-4.736-16.97-.069l-22.719 22.536c-4.705 4.667-4.736 12.265-.068 16.971l90.781 91.516c4.667 4.705 12.265 4.736 16.97.068l172.589-171.204c4.704-4.668 4.734-12.266.067-16.971z"></path></svg>																	<span>
			Gestão de Compras e Fornecedores;									</span>
			</div>
			</li>
			<li class="elementor-repeater-item-d835d07">
			<div class="elementor-price-table__feature-inner">
			<svg aria-hidden="true" class="e-font-icon-svg e-far-check-circle" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M256 8C119.033 8 8 119.033 8 256s111.033 248 248 248 248-111.033 248-248S392.967 8 256 8zm0 48c110.532 0 200 89.451 200 200 0 110.532-89.451 200-200 200-110.532 0-200-89.451-200-200 0-110.532 89.451-200 200-200m140.204 130.267l-22.536-22.718c-4.667-4.705-12.265-4.736-16.97-.068L215.346 303.697l-59.792-60.277c-4.667-4.705-12.265-4.736-16.97-.069l-22.719 22.536c-4.705 4.667-4.736 12.265-.068 16.971l90.781 91.516c4.667 4.705 12.265 4.736 16.97.068l172.589-171.204c4.704-4.668 4.734-12.266.067-16.971z"></path></svg>																	<span>
			Módulo de importação de dados;									</span>
			</div>
			</li>
			<li class="elementor-repeater-item-7b5f5d3">
			<div class="elementor-price-table__feature-inner">
			<svg aria-hidden="true" class="e-font-icon-svg e-far-check-circle" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M256 8C119.033 8 8 119.033 8 256s111.033 248 248 248 248-111.033 248-248S392.967 8 256 8zm0 48c110.532 0 200 89.451 200 200 0 110.532-89.451 200-200 200-110.532 0-200-89.451-200-200 0-110.532 89.451-200 200-200m140.204 130.267l-22.536-22.718c-4.667-4.705-12.265-4.736-16.97-.068L215.346 303.697l-59.792-60.277c-4.667-4.705-12.265-4.736-16.97-.069l-22.719 22.536c-4.705 4.667-4.736 12.265-.068 16.971l90.781 91.516c4.667 4.705 12.265 4.736 16.97.068l172.589-171.204c4.704-4.668 4.734-12.266.067-16.971z"></path></svg>																	<span>
			Relatórios gerenciais;									</span>
			</div>
			</li>
            <li class="elementor-repeater-item-7b5f5d3">
            <div class="elementor-price-table__feature-inner">
            <svg aria-hidden="true" class="e-font-icon-svg e-far-check-circle" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M256 8C119.033 8 8 119.033 8 256s111.033 248 248 248 248-111.033 248-248S392.967 8 256 8zm0 48c110.532 0 200 89.451 200 200 0 110.532-89.451 200-200 200-110.532 0-200-89.451-200-200 0-110.532 89.451-200 200-200m140.204 130.267l-22.536-22.718c-4.667-4.705-12.265-4.736-16.97-.068L215.346 303.697l-59.792-60.277c-4.667-4.705-12.265-4.736-16.97-.069l-22.719 22.536c-4.705 4.667-4.736 12.265-.068 16.971l90.781 91.516c4.667 4.705 12.265 4.736 16.97.068l172.589-171.204c4.704-4.668 4.734-12.266.067-16.971z"></path></svg>																	<span>
            Emissão de CT-e, Emissão de MDF-e;
            </div>
            </li>
			<li class="elementor-repeater-item-81bfb28">
			<div class="elementor-price-table__feature-inner">
			<svg aria-hidden="true" class="e-font-icon-svg e-far-check-circle" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M256 8C119.033 8 8 119.033 8 256s111.033 248 248 248 248-111.033 248-248S392.967 8 256 8zm0 48c110.532 0 200 89.451 200 200 0 110.532-89.451 200-200 200-110.532 0-200-89.451-200-200 0-110.532 89.451-200 200-200m140.204 130.267l-22.536-22.718c-4.667-4.705-12.265-4.736-16.97-.068L215.346 303.697l-59.792-60.277c-4.667-4.705-12.265-4.736-16.97-.069l-22.719 22.536c-4.705 4.667-4.736 12.265-.068 16.971l90.781 91.516c4.667 4.705 12.265 4.736 16.97.068l172.589-171.204c4.704-4.668 4.734-12.266.067-16.971z"></path></svg>																	<span>
			Até 5 Usuários								</span>
			</div>
			</li>
			</ul>
<div class="elementor-price-table__footer">
    <a class="elementor-price-table__button elementor-button elementor-size-md btn btn-success w-100"
       href="{{ url('/plano/contratar/3') }}"
       target="_blank"
       style="background-color: {{ Auth::user()->empresa->empresa->plano->plano_id == 3 ? '#FE7201' : '#28a745' }};">
       {{ Auth::user()->empresa->empresa->plano->plano_id == 3 ? 'Contratado' : 'Contratar' }}
    </a>
    <div class="elementor-price-table__additional_info"></div>
</div>
			</div>
			<div class="elementor-price-table__ribbon">
			<div class="elementor-price-table__ribbon-inner">
			popular				</div>
			</div>
			</div>
			</div>
			</div>
			</div>
			</div>


			<div data-elementor-type="section" data-elementor-id="441" class="elementor elementor-441" data-elementor-post-type="elementor_library">
			<div class="elementor-element elementor-element-0f7b9e0 elementor-widget elementor-widget-price-table" data-id="0f7b9e0" data-element_type="widget" data-widget_type="price-table.default">
			<div class="elementor-element elementor-element-f24c5c6 e-flex e-con-boxed e-con e-child" data-id="f24c5c6" data-element_type="container">
			<div class="e-con-inner">
			<div class="elementor-element elementor-element-c43e8a2 elementor-widget elementor-widget-price-table" data-id="c43e8a2" data-element_type="widget" data-widget_type="price-table.default">
			<div class="elementor-widget-container">
			<div class="elementor-price-table">
			<div class="elementor-price-table__header_diamante">
			<h3 class="elementor-price-table__heading">
			Plano Diamante						</h3>
			<span class="elementor-price-table__subheading">
			Plano para pequenas e médias empresas						</span>
			</div>
			<div class="elementor-price-table__price">
			<div class="elementor-price-table__original-price elementor-typo-excluded"><b>
			<span class="elementor-price-table__currency">R$</span>529					</div></b>
			<span class="elementor-price-table__currency">R$</span>									<span class="elementor-price-table__integer-part">
			359					</span>
			<div class="elementor-price-table__after-price">
			<span class="elementor-price-table__fractional-part">
			90						</span>
			</div>
			<span class="elementor-price-table__period elementor-typo-excluded" style="color: green;">Promoção</span>							</div>
			<ul class="elementor-price-table__features-list">
			<li class="elementor-repeater-item-19a20a0">
			<div class="elementor-price-table__feature-inner">
			<svg aria-hidden="true" class="e-font-icon-svg e-far-check-circle" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M256 8C119.033 8 8 119.033 8 256s111.033 248 248 248 248-111.033 248-248S392.967 8 256 8zm0 48c110.532 0 200 89.451 200 200 0 110.532-89.451 200-200 200-110.532 0-200-89.451-200-200 0-110.532 89.451-200 200-200m140.204 130.267l-22.536-22.718c-4.667-4.705-12.265-4.736-16.97-.068L215.346 303.697l-59.792-60.277c-4.667-4.705-12.265-4.736-16.97-.069l-22.719 22.536c-4.705 4.667-4.736 12.265-.068 16.971l90.781 91.516c4.667 4.705 12.265 4.736 16.97.068l172.589-171.204c4.704-4.668 4.734-12.266.067-16.971z"></path></svg>																	<span>
			Gestão de Vendas e Clientes									</span>
			</div>
			</li>
			<li class="elementor-repeater-item-5804794">
			<div class="elementor-price-table__feature-inner">
			<svg aria-hidden="true" class="e-font-icon-svg e-far-check-circle" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M256 8C119.033 8 8 119.033 8 256s111.033 248 248 248 248-111.033 248-248S392.967 8 256 8zm0 48c110.532 0 200 89.451 200 200 0 110.532-89.451 200-200 200-110.532 0-200-89.451-200-200 0-110.532 89.451-200 200-200m140.204 130.267l-22.536-22.718c-4.667-4.705-12.265-4.736-16.97-.068L215.346 303.697l-59.792-60.277c-4.667-4.705-12.265-4.736-16.97-.069l-22.719 22.536c-4.705 4.667-4.736 12.265-.068 16.971l90.781 91.516c4.667 4.705 12.265 4.736 16.97.068l172.589-171.204c4.704-4.668 4.734-12.266.067-16.971z"></path></svg>																	<span>
			CRM Integrado 									</span>
			</div>
			</li>
			<li class="elementor-repeater-item-393b067">
			<div class="elementor-price-table__feature-inner">
			<svg aria-hidden="true" class="e-font-icon-svg e-far-check-circle" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M256 8C119.033 8 8 119.033 8 256s111.033 248 248 248 248-111.033 248-248S392.967 8 256 8zm0 48c110.532 0 200 89.451 200 200 0 110.532-89.451 200-200 200-110.532 0-200-89.451-200-200 0-110.532 89.451-200 200-200m140.204 130.267l-22.536-22.718c-4.667-4.705-12.265-4.736-16.97-.068L215.346 303.697l-59.792-60.277c-4.667-4.705-12.265-4.736-16.97-.069l-22.719 22.536c-4.705 4.667-4.736 12.265-.068 16.971l90.781 91.516c4.667 4.705 12.265 4.736 16.97.068l172.589-171.204c4.704-4.668 4.734-12.266.067-16.971z"></path></svg>																	<span>
			Gestão de Estoque e Produtos 									</span>
			</div>
			</li>
			<li class="elementor-repeater-item-102b5b8">
			<div class="elementor-price-table__feature-inner">
			<svg aria-hidden="true" class="e-font-icon-svg e-far-check-circle" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M256 8C119.033 8 8 119.033 8 256s111.033 248 248 248 248-111.033 248-248S392.967 8 256 8zm0 48c110.532 0 200 89.451 200 200 0 110.532-89.451 200-200 200-110.532 0-200-89.451-200-200 0-110.532 89.451-200 200-200m140.204 130.267l-22.536-22.718c-4.667-4.705-12.265-4.736-16.97-.068L215.346 303.697l-59.792-60.277c-4.667-4.705-12.265-4.736-16.97-.069l-22.719 22.536c-4.705 4.667-4.736 12.265-.068 16.971l90.781 91.516c4.667 4.705 12.265 4.736 16.97.068l172.589-171.204c4.704-4.668 4.734-12.266.067-16.971z"></path></svg>																	<span>
			Emissão de NF-e, NFS-e, NFC-e;									</span>
			</div>
			</li>
			<li class="elementor-repeater-item-1332cab">
			<div class="elementor-price-table__feature-inner">
			<svg aria-hidden="true" class="e-font-icon-svg e-far-check-circle" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M256 8C119.033 8 8 119.033 8 256s111.033 248 248 248 248-111.033 248-248S392.967 8 256 8zm0 48c110.532 0 200 89.451 200 200 0 110.532-89.451 200-200 200-110.532 0-200-89.451-200-200 0-110.532 89.451-200 200-200m140.204 130.267l-22.536-22.718c-4.667-4.705-12.265-4.736-16.97-.068L215.346 303.697l-59.792-60.277c-4.667-4.705-12.265-4.736-16.97-.069l-22.719 22.536c-4.705 4.667-4.736 12.265-.068 16.971l90.781 91.516c4.667 4.705 12.265 4.736 16.97.068l172.589-171.204c4.704-4.668 4.734-12.266.067-16.971z"></path></svg>																	<span>
			Ideal para empresas de pequeno/ médio e grande porte;
			</span>
			</div>
			</li>
			<li class="elementor-repeater-item-a1ff7ed">
			<div class="elementor-price-table__feature-inner">
			<svg aria-hidden="true" class="e-font-icon-svg e-far-check-circle" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M256 8C119.033 8 8 119.033 8 256s111.033 248 248 248 248-111.033 248-248S392.967 8 256 8zm0 48c110.532 0 200 89.451 200 200 0 110.532-89.451 200-200 200-110.532 0-200-89.451-200-200 0-110.532 89.451-200 200-200m140.204 130.267l-22.536-22.718c-4.667-4.705-12.265-4.736-16.97-.068L215.346 303.697l-59.792-60.277c-4.667-4.705-12.265-4.736-16.97-.069l-22.719 22.536c-4.705 4.667-4.736 12.265-.068 16.971l90.781 91.516c4.667 4.705 12.265 4.736 16.97.068l172.589-171.204c4.704-4.668 4.734-12.266.067-16.971z"></path></svg>																	<span>
			Controle Financeiro, conta a pagar e contas a receber;									</span>
			</div>
			</li>
			<li class="elementor-repeater-item-84249dc">
			<div class="elementor-price-table__feature-inner">
			<svg aria-hidden="true" class="e-font-icon-svg e-far-check-circle" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M256 8C119.033 8 8 119.033 8 256s111.033 248 248 248 248-111.033 248-248S392.967 8 256 8zm0 48c110.532 0 200 89.451 200 200 0 110.532-89.451 200-200 200-110.532 0-200-89.451-200-200 0-110.532 89.451-200 200-200m140.204 130.267l-22.536-22.718c-4.667-4.705-12.265-4.736-16.97-.068L215.346 303.697l-59.792-60.277c-4.667-4.705-12.265-4.736-16.97-.069l-22.719 22.536c-4.705 4.667-4.736 12.265-.068 16.971l90.781 91.516c4.667 4.705 12.265 4.736 16.97.068l172.589-171.204c4.704-4.668 4.734-12.266.067-16.971z"></path></svg>																	<span>
			Gestão de Compras e Fornecedores;									</span>
			</div>
			</li>
			<li class="elementor-repeater-item-d835d07">
			<div class="elementor-price-table__feature-inner">
			<svg aria-hidden="true" class="e-font-icon-svg e-far-check-circle" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M256 8C119.033 8 8 119.033 8 256s111.033 248 248 248 248-111.033 248-248S392.967 8 256 8zm0 48c110.532 0 200 89.451 200 200 0 110.532-89.451 200-200 200-110.532 0-200-89.451-200-200 0-110.532 89.451-200 200-200m140.204 130.267l-22.536-22.718c-4.667-4.705-12.265-4.736-16.97-.068L215.346 303.697l-59.792-60.277c-4.667-4.705-12.265-4.736-16.97-.069l-22.719 22.536c-4.705 4.667-4.736 12.265-.068 16.971l90.781 91.516c4.667 4.705 12.265 4.736 16.97.068l172.589-171.204c4.704-4.668 4.734-12.266.067-16.971z"></path></svg>																	<span>
			Módulo de importação de dados;									</span>
			</div>
			</li>
			<li class="elementor-repeater-item-7b5f5d3">
			<div class="elementor-price-table__feature-inner">
			<svg aria-hidden="true" class="e-font-icon-svg e-far-check-circle" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M256 8C119.033 8 8 119.033 8 256s111.033 248 248 248 248-111.033 248-248S392.967 8 256 8zm0 48c110.532 0 200 89.451 200 200 0 110.532-89.451 200-200 200-110.532 0-200-89.451-200-200 0-110.532 89.451-200 200-200m140.204 130.267l-22.536-22.718c-4.667-4.705-12.265-4.736-16.97-.068L215.346 303.697l-59.792-60.277c-4.667-4.705-12.265-4.736-16.97-.069l-22.719 22.536c-4.705 4.667-4.736 12.265-.068 16.971l90.781 91.516c4.667 4.705 12.265 4.736 16.97.068l172.589-171.204c4.704-4.668 4.734-12.266.067-16.971z"></path></svg>																	<span>
			Relatórios gerenciais;									</span>
			</div>
			</li>
			<li class="elementor-repeater-item-e426b7c">
			<div class="elementor-price-table__feature-inner">
			<svg aria-hidden="true" class="e-font-icon-svg e-far-check-circle" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M256 8C119.033 8 8 119.033 8 256s111.033 248 248 248 248-111.033 248-248S392.967 8 256 8zm0 48c110.532 0 200 89.451 200 200 0 110.532-89.451 200-200 200-110.532 0-200-89.451-200-200 0-110.532 89.451-200 200-200m140.204 130.267l-22.536-22.718c-4.667-4.705-12.265-4.736-16.97-.068L215.346 303.697l-59.792-60.277c-4.667-4.705-12.265-4.736-16.97-.069l-22.719 22.536c-4.705 4.667-4.736 12.265-.068 16.971l90.781 91.516c4.667 4.705 12.265 4.736 16.97.068l172.589-171.204c4.704-4.668 4.734-12.266.067-16.971z"></path></svg>																	<span>
			Emissão de CT-e, Emissão de MDF-e;									</span>
			</div>
			</li>
			<li class="elementor-repeater-item-7820c82">
			<div class="elementor-price-table__feature-inner">
			<svg aria-hidden="true" class="e-font-icon-svg e-far-check-circle" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M256 8C119.033 8 8 119.033 8 256s111.033 248 248 248 248-111.033 248-248S392.967 8 256 8zm0 48c110.532 0 200 89.451 200 200 0 110.532-89.451 200-200 200-110.532 0-200-89.451-200-200 0-110.532 89.451-200 200-200m140.204 130.267l-22.536-22.718c-4.667-4.705-12.265-4.736-16.97-.068L215.346 303.697l-59.792-60.277c-4.667-4.705-12.265-4.736-16.97-.069l-22.719 22.536c-4.705 4.667-4.736 12.265-.068 16.971l90.781 91.516c4.667 4.705 12.265 4.736 16.97.068l172.589-171.204c4.704-4.668 4.734-12.266.067-16.971z"></path></svg>																	<span>
			Integração logística;									</span>
			</div>
			</li>
			<li class="elementor-repeater-item-b91b7b7">
			<div class="elementor-price-table__feature-inner">
			<svg aria-hidden="true" class="e-font-icon-svg e-far-check-circle" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M256 8C119.033 8 8 119.033 8 256s111.033 248 248 248 248-111.033 248-248S392.967 8 256 8zm0 48c110.532 0 200 89.451 200 200 0 110.532-89.451 200-200 200-110.532 0-200-89.451-200-200 0-110.532 89.451-200 200-200m140.204 130.267l-22.536-22.718c-4.667-4.705-12.265-4.736-16.97-.068L215.346 303.697l-59.792-60.277c-4.667-4.705-12.265-4.736-16.97-.069l-22.719 22.536c-4.705 4.667-4.736 12.265-.068 16.971l90.781 91.516c4.667 4.705 12.265 4.736 16.97.068l172.589-171.204c4.704-4.668 4.734-12.266.067-16.971z"></path></svg>																	<span>
			Relatórios adicionais com gráficos;									</span>
			</div>
			</li>
			<li class="elementor-repeater-item-20276c2">
			<div class="elementor-price-table__feature-inner">
			<svg aria-hidden="true" class="e-font-icon-svg e-far-check-circle" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M256 8C119.033 8 8 119.033 8 256s111.033 248 248 248 248-111.033 248-248S392.967 8 256 8zm0 48c110.532 0 200 89.451 200 200 0 110.532-89.451 200-200 200-110.532 0-200-89.451-200-200 0-110.532 89.451-200 200-200m140.204 130.267l-22.536-22.718c-4.667-4.705-12.265-4.736-16.97-.068L215.346 303.697l-59.792-60.277c-4.667-4.705-12.265-4.736-16.97-.069l-22.719 22.536c-4.705 4.667-4.736 12.265-.068 16.971l90.781 91.516c4.667 4.705 12.265 4.736 16.97.068l172.589-171.204c4.704-4.668 4.734-12.266.067-16.971z"></path></svg>																	<span>
			Frente de Caixa – PDV;									</span>
			</div>
			</li>
			<li class="elementor-repeater-item-e71c192">
			<div class="elementor-price-table__feature-inner">
			<svg aria-hidden="true" class="e-font-icon-svg e-far-check-circle" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M256 8C119.033 8 8 119.033 8 256s111.033 248 248 248 248-111.033 248-248S392.967 8 256 8zm0 48c110.532 0 200 89.451 200 200 0 110.532-89.451 200-200 200-110.532 0-200-89.451-200-200 0-110.532 89.451-200 200-200m140.204 130.267l-22.536-22.718c-4.667-4.705-12.265-4.736-16.97-.068L215.346 303.697l-59.792-60.277c-4.667-4.705-12.265-4.736-16.97-.069l-22.719 22.536c-4.705 4.667-4.736 12.265-.068 16.971l90.781 91.516c4.667 4.705 12.265 4.736 16.97.068l172.589-171.204c4.704-4.668 4.734-12.266.067-16.971z"></path></svg>																	<span>
			Até 20 usuários									</span>
			</div>
			</li>
			</ul>
<div class="elementor-price-table__footer">
    <a class="elementor-price-table__button elementor-button elementor-size-md btn btn-success w-100"
       href="{{ url('/plano/contratar/4') }}"
       target="_blank"
       style="background-color: {{ Auth::user()->empresa->empresa->plano->plano_id == 4 ? '#FE7201' : '#28a745' }};">
       {{ Auth::user()->empresa->empresa->plano->plano_id == 4 ? 'Contratado' : 'Contratar' }}
    </a>
    <div class="elementor-price-table__additional_info"></div>
</div>
			</div>
			</div>
			</div>
			</div>
			</div>
			</div>
			</div>
			</div>
			</div>
		</div>
	</div>
</div>



@endsection
@section('js')
			<script type="text/javascript">
			//https://stackoverflow.com/questions/41712480/how-to-add-form-field-value-to-query-parameter-to-contact-form-7-on-sent-ok-redi
			//https://wordpress.org/plugins/contact-form-7-dynamic-text-extension/
			document.addEventListener('wpcf7mailsent', function(event) {
			location = 'https://matreiza.com/cadastrado/?tickets=' + jQuery('input[name=tickets]').val();
			}, false);
			</script>			<script type="text/javascript">
			const lazyloadRunObserver = () => {
			const lazyloadBackgrounds = document.querySelectorAll( `.e-con.e-parent:not(.e-lazyloaded)` );
			const lazyloadBackgroundObserver = new IntersectionObserver( ( entries ) => {
			entries.forEach( ( entry ) => {
			if ( entry.isIntersecting ) {
			let lazyloadBackground = entry.target;
			if( lazyloadBackground ) {
				lazyloadBackground.classList.add( 'e-lazyloaded' );
			}
			lazyloadBackgroundObserver.unobserve( entry.target );
			}
			});
			}, { rootMargin: '200px 0px 200px 0px' } );
			lazyloadBackgrounds.forEach( ( lazyloadBackground ) => {
			lazyloadBackgroundObserver.observe( lazyloadBackground );
			} );
			};
			const events = [
			'DOMContentLoaded',
			'elementor/lazyload/observe',
			];
			events.forEach( ( event ) => {
			document.addEventListener( event, lazyloadRunObserver );
			} );
			</script>
			<link rel="stylesheet" id="e-animations-css" href="./planos_files/animations.min.css" media="all">
			<script src="./planos_files/index.js.download" id="swv-js"></script>
			<script id="contact-form-7-js-extra">
			var wpcf7 = {"api":{"root":"https:\/\/projetos.metamidia.com.br\/site\/matriza\/index.php\/wp-json\/","namespace":"contact-form-7\/v1"}};
			</script>
			<script src="./planos_files/index(1).js.download" id="contact-form-7-js"></script>
			<script src="./planos_files/api.js.download" id="google-recaptcha-js"></script>
			<script src="./planos_files/wp-polyfill-inert.min.js.download" id="wp-polyfill-inert-js"></script>
			<script src="./planos_files/regenerator-runtime.min.js.download" id="regenerator-runtime-js"></script>
			<script src="./planos_files/wp-polyfill.min.js.download" id="wp-polyfill-js"></script>
			<script id="wpcf7-recaptcha-js-extra">
			var wpcf7_recaptcha = {"sitekey":"6LclvIoaAAAAANk8MiKFchziMNhWK3mccfaoqpTD","actions":{"homepage":"homepage","contactform":"contactform"}};
			</script>
			<script src="./planos_files/index(2).js.download" id="wpcf7-recaptcha-js"></script>
			<script id="eael-general-js-extra">
			var localize = {"ajaxurl":"https:\/\/projetos.metamidia.com.br\/site\/matriza\/wp-admin\/admin-ajax.php","nonce":"b7c2e8a15b","i18n":{"added":"Adicionado ","compare":"Comparar","loading":"Carregando..."},"eael_translate_text":{"required_text":"\u00e9 um campo obrigat\u00f3rio","invalid_text":"Inv\u00e1lido","billing_text":"Faturamento","shipping_text":"Envio","fg_mfp_counter_text":"de"},"page_permalink":"https:\/\/projetos.metamidia.com.br\/site\/matriza\/","cart_redirectition":"","cart_page_url":"","el_breakpoints":{"mobile":{"label":"Dispositivos m\u00f3veis no modo retrato","value":767,"default_value":767,"direction":"max","is_enabled":true},"mobile_extra":{"label":"Dispositivos m\u00f3veis no modo paisagem","value":880,"default_value":880,"direction":"max","is_enabled":false},"tablet":{"label":"Tablet no modo retrato","value":1024,"default_value":1024,"direction":"max","is_enabled":true},"tablet_extra":{"label":"Tablet no modo paisagem","value":1200,"default_value":1200,"direction":"max","is_enabled":false},"laptop":{"label":"Notebook","value":1366,"default_value":1366,"direction":"max","is_enabled":false},"widescreen":{"label":"Tela ampla (widescreen)","value":2400,"default_value":2400,"direction":"min","is_enabled":false}}};
			</script>
			<script src="./planos_files/general.min.js.download" id="eael-general-js"></script>
			<script src="./planos_files/jquery.smartmenus.min.js.download" id="smartmenus-js"></script>
			<script src="./planos_files/imagesloaded.min.js.download" id="imagesloaded-js"></script>
			<script src="./planos_files/webpack-pro.runtime.min.js.download" id="elementor-pro-webpack-runtime-js"></script>
			<script src="./planos_files/webpack.runtime.min.js.download" id="elementor-webpack-runtime-js"></script>
			<script src="./planos_files/frontend-modules.min.js.download" id="elementor-frontend-modules-js"></script>
			<script src="./planos_files/hooks.min.js.download" id="wp-hooks-js"></script>
			<script src="./planos_files/i18n.min.js.download" id="wp-i18n-js"></script>
			<script id="wp-i18n-js-after">
			wp.i18n.setLocaleData( { 'text direction\u0004ltr': [ 'ltr' ] } );
			</script>
			<script id="elementor-pro-frontend-js-before">
			var ElementorProFrontendConfig = {"ajaxurl":"https:\/\/projetos.metamidia.com.br\/site\/matriza\/wp-admin\/admin-ajax.php","nonce":"ff61582239","urls":{"assets":"https:\/\/projetos.metamidia.com.br\/site\/matriza\/wp-content\/plugins\/pro-elements\/assets\/","rest":"https:\/\/projetos.metamidia.com.br\/site\/matriza\/index.php\/wp-json\/"},"shareButtonsNetworks":{"facebook":{"title":"Facebook","has_counter":true},"twitter":{"title":"Twitter"},"linkedin":{"title":"LinkedIn","has_counter":true},"pinterest":{"title":"Pinterest","has_counter":true},"reddit":{"title":"Reddit","has_counter":true},"vk":{"title":"VK","has_counter":true},"odnoklassniki":{"title":"OK","has_counter":true},"tumblr":{"title":"Tumblr"},"digg":{"title":"Digg"},"skype":{"title":"Skype"},"stumbleupon":{"title":"StumbleUpon","has_counter":true},"mix":{"title":"Mix"},"telegram":{"title":"Telegram"},"pocket":{"title":"Pocket","has_counter":true},"xing":{"title":"XING","has_counter":true},"whatsapp":{"title":"WhatsApp"},"email":{"title":"Email"},"print":{"title":"Print"},"x-twitter":{"title":"X"},"threads":{"title":"Threads"}},"facebook_sdk":{"lang":"pt_BR","app_id":""},"lottie":{"defaultAnimationUrl":"https:\/\/projetos.metamidia.com.br\/site\/matriza\/wp-content\/plugins\/pro-elements\/modules\/lottie\/assets\/animations\/default.json"}};
			</script>
			<script src="./planos_files/waypoints.min.js.download" id="elementor-waypoints-js"></script>
			<script src="./planos_files/core.min.js.download" id="jquery-ui-core-js"></script>
			<script id="elementor-frontend-js-before">
			var elementorFrontendConfig = {"environmentMode":{"edit":false,"wpPreview":false,"isScriptDebug":false},"i18n":{"shareOnFacebook":"Compartilhar no Facebook","shareOnTwitter":"Compartilhar no Twitter","pinIt":"Fixar","download":"Baixar","downloadImage":"Baixar imagem","fullscreen":"Tela cheia","zoom":"Zoom","share":"Compartilhar","playVideo":"Reproduzir v\u00eddeo","previous":"Anterior","next":"Pr\u00f3ximo","close":"Fechar","a11yCarouselWrapperAriaLabel":"Carrossel | Rolagem horizontal: Setas para esquerda e direita","a11yCarouselPrevSlideMessage":"Slide anterior","a11yCarouselNextSlideMessage":"Pr\u00f3ximo slide","a11yCarouselFirstSlideMessage":"Este \u00e9 o primeiro slide","a11yCarouselLastSlideMessage":"Este \u00e9 o \u00faltimo slide","a11yCarouselPaginationBulletMessage":"Ir para o slide"},"is_rtl":false,"breakpoints":{"xs":0,"sm":480,"md":768,"lg":1025,"xl":1440,"xxl":1600},"responsive":{"breakpoints":{"mobile":{"label":"Dispositivos m\u00f3veis no modo retrato","value":767,"default_value":767,"direction":"max","is_enabled":true},"mobile_extra":{"label":"Dispositivos m\u00f3veis no modo paisagem","value":880,"default_value":880,"direction":"max","is_enabled":false},"tablet":{"label":"Tablet no modo retrato","value":1024,"default_value":1024,"direction":"max","is_enabled":true},"tablet_extra":{"label":"Tablet no modo paisagem","value":1200,"default_value":1200,"direction":"max","is_enabled":false},"laptop":{"label":"Notebook","value":1366,"default_value":1366,"direction":"max","is_enabled":false},"widescreen":{"label":"Tela ampla (widescreen)","value":2400,"default_value":2400,"direction":"min","is_enabled":false}}},"version":"3.21.1","is_static":false,"experimentalFeatures":{"e_optimized_assets_loading":true,"e_optimized_css_loading":true,"e_font_icon_svg":true,"additional_custom_breakpoints":true,"container":true,"e_swiper_latest":true,"container_grid":true,"theme_builder_v2":true,"hello-theme-header-footer":true,"home_screen":true,"ai-layout":true,"landing-pages":true,"e_lazyload":true,"notes":true,"display-conditions":true,"form-submissions":true,"taxonomy-filter":true},"urls":{"assets":"https:\/\/projetos.metamidia.com.br\/site\/matriza\/wp-content\/plugins\/elementor\/assets\/"},"swiperClass":"swiper","settings":{"page":[],"editorPreferences":[]},"kit":{"active_breakpoints":["viewport_mobile","viewport_tablet"],"global_image_lightbox":"yes","lightbox_enable_counter":"yes","lightbox_enable_fullscreen":"yes","lightbox_enable_zoom":"yes","lightbox_enable_share":"yes","lightbox_title_src":"title","lightbox_description_src":"description","hello_header_logo_type":"logo","hello_header_menu_layout":"horizontal","hello_footer_logo_type":"logo"},"post":{"id":44,"title":"Matriza%20%E2%80%93%20Sistemas%20ERP","excerpt":"","featuredImage":false}};
			</script>
			<script src="./planos_files/elements-handlers.min.js.download" id="pro-elements-handlers-js"></script><svg style="display: none;" class="e-font-icon-svg-symbols"></svg>
			<script src="./planos_files/jquery.sticky.min.js.download" id="e-sticky-js"></script>

			<script src="./planos_files/swiper.min.js.download"></script>

<script src="https://secure.mlstatic.com/sdk/javascript/v1/mercadopago.js"></script>
<script type="text/javascript">


    $(function(){
        window.Mercadopago.setPublishableKey('{{ $config->mercadopago_public_key }}');
        setTimeout(() => {
            window.Mercadopago.getIdentificationTypes();
        }, 100)
    })
    function selectPlano(id, valor, nome){

        $('.plano_nome').text(nome + " R$ " + convertFloatToMoeda(valor))
        $('#plano_id').val(id)
        $('#plano_valor').val(valor)
    }

    $('.btn-gerar').click(() => {
        $body.addClass("loading");
    })
	document.addEventListener("DOMContentLoaded", function() {
    // Obtenha todos os itens de abas e conteúdos das abas
    const tabs = document.querySelectorAll('.eael-tab-item-trigger');
    const tabContents = document.querySelectorAll('.eael-tab-content-item');

    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            // Remove a classe ativa de todos os itens de abas e conteúdos
            tabs.forEach(item => item.classList.remove('active'));
            tabContents.forEach(content => content.classList.add('inactive'));

            // Adiciona a classe ativa ao item de aba clicado
            this.classList.add('active');

            // Obtenha o id do conteúdo correspondente e exiba-o
            const tabId = this.getAttribute('data-tab');
            document.querySelector(`#planos-${tabId}-tab`).classList.remove('inactive');
        });
    });
});

document.addEventListener('DOMContentLoaded', function() {
    const tabTriggers = document.querySelectorAll('.eael-tab-item-trigger');
    const tabContents = document.querySelectorAll('.eael-tab-content-item');

    // Função para alternar a aba
    function activateTab(tabId) {
        // Remover classe "active" de todos os itens
        tabTriggers.forEach(trigger => trigger.classList.remove('active'));
        tabContents.forEach(content => {
            content.classList.remove('active');
            content.style.opacity = 0; // Iniciar opacidade com 0
        });

        // Adicionar classe "active" no item clicado
        const activeTrigger = document.querySelector(`#planos-${tabId}`);
        const activeContent = document.querySelector(`#planos-${tabId}-tab`);

        activeTrigger.classList.add('active');
        activeContent.classList.add('active');

        // Adiciona um pequeno delay para a opacidade para suavizar a transição
        setTimeout(() => {
            activeContent.style.opacity = 1;
        }, 50);
    }

    // Atribuir evento de clique para cada aba
    tabTriggers.forEach(trigger => {
        trigger.addEventListener('click', function() {
            const tabId = this.dataset.tab; // Obtém o ID do plano (1, 2, 3)
            activateTab(tabId);
        });
    });

    // Ativar a primeira aba por padrão
    activateTab('trimestrais');
});



</script>

@endsection
