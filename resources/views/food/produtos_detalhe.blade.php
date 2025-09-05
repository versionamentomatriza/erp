@extends('food.default', ['title' => $produto->nome])
@section('content')

<section class="product-details spad" style="margin-top: -100px">
	<div class="container">
		<div class="row">
			<div class="col-lg-6 col-md-6">
				<div class="product__details__pic">
					<div class="product__details__pic__item">
						<img class="product__details__pic__item--large"
						src="{{ $produto->img }}" alt="">
					</div>

				</div>
			</div>
			<div class="col-lg-6 col-md-6">
				<form class="product__details__text">
					<h3>{{ $produto->nome }}</h3>
					
					<div class="product__details__price">R$ {{ __moeda($produto->valor_delivery) }}</div>
					<p>{{ $produto->texto_delivery }}</p>

					<div class="row mb-3"> 
						@if(sizeof($produto->adicionaisAtivos) > 0)
						@foreach($produto->adicionaisAtivos as $a)
						<div class="col-md-6">
							<input type="checkbox" name="">
							{{ $a->adicional->nome }} R${{ __moeda($a->adicional->valor) }}
						</div>
						@endforeach
						@endif
					</div>
					<div class="product__details__quantity">
						<div class="quantity">
							<div class="pro-qty">
								<input type="text" value="1">
							</div>
						</div>
					</div>
					<a href="#" class="primary-btn btn-main">ADICIONAR AO CARRINHO</a>


					<ul>
						@if($produto->categoria)
						<li><b>Categoria</b> <span>{{ $produto->categoria->nome }}</span></li>
						@endif

						@if($produto->destaque_delivery)
						<li><span>{{ $produto->nome }}<samp> esta em destaque em nosso cardápio</samp></span></li>
						@endif
					</ul>
				</form>
			</div>
		</div>
		@if(sizeof($produto->ingredientes) > 0)
		<div class="col-lg-12">
			<div class="product__details__tab__desc">
				<h5 style="font-weight: bold">Ingredientes</h5>
				<p>@foreach($produto->ingredientes as $i) {{ $i->ingrediente }}{{ !$loop->last ? ', ' : '' }} @endforeach</p>
			</div>
		</div>
		@endif
	</div>
</section>
@endsection