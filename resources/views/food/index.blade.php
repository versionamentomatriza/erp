@extends('food.default', ['title' => 'Home'])
@section('css')
<style type="text/css">
	.set-bg:hover{
		cursor: pointer;
	}

	@media (max-width:480px)  {
		.hero__text{
			margin-left: -50px;
			margin-top: 50px;
		}
	}
</style>
@endsection
@section('content')

<section class="featured spad" style="margin-top: -100px">
	<div class="container">

		@if($funcionamento == null)
		<div class="card bg-danger mb-2">
			<div class="card-header" style="height: 50px;">
				@if(\App\Models\MarketPlaceConfig::getSegmentoServico($config))
				<p class="text-white">Estabelecimento está fechado hoje</p>
				@else
				<p class="text-white">Restaurante está fechado hoje</p>
				@endif
			</div>
		</div>
		@endif

		@if(!\App\Models\MarketPlaceConfig::getSegmentoServico($config))
		@if($funcionamento != null && !$funcionamento->aberto)
		<div class="card bg-danger mb-2">
			<div class="card-header" style="height: 50px;">
				<p class="text-white">Restaurante abrirá às {{ __hora_pt($funcionamento->inicio) }}</p>
			</div>
		</div>
		@endif
		@endif

		@if(sizeof($produtosEmDestaque) > 0)
		<div class="row">
			<div class="col-lg-12">
				<div class="section-title">
					<h2>Produtos em destaque</h2>
				</div>
				<div class="featured__controls">
					<ul>
						<li class="active" data-filter="*">Todos</li>
						@foreach($categoriasEmDestaque as $c)
						<li data-filter=".{{ $c->nome }}">{{ $c->nome }}</li>
						@endforeach
					</ul>
				</div>
			</div>
		</div>

		<div class="row featured__filter">
			@foreach($produtosEmDestaque as $p)
			<div class="col-lg-3 col-md-4 col-sm-6 mix {{$p->categoria->nome}} fresh-meat">

				<div class="featured__item">

					<div class="featured__item__pic set-bg" data-setbg="{{ $p->img }}" onclick="clicItem('{{ route('food.produto-detalhe', [$p->hash_delivery, 'link='.$config->loja_id]) }}')">
						<ul class="featured__item__pic__hover">
							<li><a href="{{ route('food.produto-detalhe', [$p->hash_delivery, 'link='.$config->loja_id]) }}"><i class="fa fa-shopping-cart"></i></a></li>
						</ul>
					</div>
					<div class="featured__item__text">
						<h6><a href="{{ route('food.produto-detalhe', [$p->hash_delivery, 'link='.$config->loja_id])}}">{{ $p->nome }}</a></h6>
						@if($p->categoria->tipo_pizza)
						<h6 class="text-main">{!! $p->valoresPizza() !!}</h6>
						@else
						<h5 class="text-main">R$ {{ __moeda($p->valor_delivery) }}</h5>
						@endif
					</div>
				</div>
			</div>
			@endforeach

		</div>
		@endif

		<!-- Serviços -->
		@if(sizeof($servicosEmDestaque) > 0)
		<div class="row">
			<div class="col-lg-12">
				<div class="section-title">
					<h2>Serviços em destaque</h2>
				</div>
				<div class="featured__controls">
					<ul>
						<li class="active" data-filter="*">Todos</li>
						@foreach($categoriasEmDestaqueServicos as $c)
						<li data-filter=".{{ $c->nome }}">{{ $c->nome }}</li>
						@endforeach
					</ul>
				</div>
			</div>
		</div>

		<div class="row featured__filter">
			@foreach($servicosEmDestaque as $s)
			<div class="col-lg-3 col-md-4 col-sm-6 mix {{$s->categoria->nome}} fresh-meat">

				<div class="featured__item">

					<div class="featured__item__pic set-bg" data-setbg="{{ $s->img }}" onclick="clicItem('{{ route('food.produto-detalhe', [$s->hash_delivery, 'link='.$config->loja_id]) }}')">
						<ul class="featured__item__pic__hover">
							<li><a href="{{ route('food.servico-detalhe', [$s->hash_delivery, 'link='.$config->loja_id]) }}"><i class="fa fa-shopping-cart"></i></a></li>
						</ul>
					</div>
					<div class="featured__item__text">
						<h6><a href="{{ route('food.servico-detalhe', [$s->hash_delivery, 'link='.$config->loja_id])}}">{{ $s->nome }}</a></h6>
						
						<h5 class="text-main">R$ {{ __moeda($s->valor) }}</h5>

					</div>
				</div>
			</div>
			@endforeach

		</div>
		@endif
	</div>
</section>

@section('js')
<script type="text/javascript">
	function clicItem(link){
		location.href = link
	}

	$(function(){
		let size = '{{sizeof($banners)}}';
		let cont = 0;
		$('.bg-'+cont).removeClass('d-none')
		setInterval(() => {
			cont++
			if(cont >= size){
				cont = 0
			}
			$('.hero__item').addClass('d-none')
			$('.bg-'+cont).removeClass('d-none')
		}, 10000)
	})
</script>
@endsection
@endsection