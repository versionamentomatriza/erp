@extends('food.default', ['title' => 'Serviços'])
@section('css')
<style type="text/css">
	.img-size{
		height: 80px;
	}
	.border-tamanho img{
		border: 1px solid #EFEFEF;
		border-radius: 5px;
		padding: 3px;
	}
	.border-tamanho:hover{
		cursor: pointer;
	}
	.active-border{
		background: var(--color-main);
	}

	.bg-active{
		background: #34A853;
	}
	.fresh-meat{
		border-radius: 10px;
	}
	.fixedbutton {
		position: fixed;
		bottom: 10px;
		z-index: 999;
		width: 84%;
	}
	@media (max-width:480px)  {
		.fixedbutton {
			width: 100%;
		}
	}
</style>
@endsection
@section('content')

<section class="featured spad" style="margin-top: -100px">
	<div class="container">
		<div class="row">
			<div class="col-lg-12">
				<div class="section-title">
					<h4>Serviços da categoria <strong>{{ $categoria->nome }}</strong></h4>
				</div>
				
			</div>
		</div>

		<div class="row featured__filter">

			@foreach($servicos as $s)
			<div class="col-lg-3 col-md-4 col-sm-6 mix fresh-meat produto-{{ $s->id }} m-1">
				<div class="featured__item">
					<br>
					<div class="featured__item__pic set-bg" data-setbg="{{ $s->img }}">
						<ul class="featured__item__pic__hover">
							
							<li><a href="{{ route('food.servico-detalhe', [$s->hash_delivery, 'link='.$config->loja_id])}}"><i class="fa fa-shopping-cart"></i></a></li>

						</ul>
					</div>
					<div class="featured__item__text" style="height: 120px">
						<h6>
							<a href="{{ route('food.servico-detalhe', [$s->hash_delivery, 'link='.$config->loja_id])}}">{{ $s->nome }}</a>
						</h6>
						
						<h5 class="text-main">R$ {{ __moeda($s->valor) }}</h5>

					</div>
					
				</div>
			</div>
			@endforeach
			
		</div>
		<div class="row">
			<button class="btn btn-main text-white fixedbutton d-none" type="button">
				<i class="fa fa-shopping-cart"></i>
				Adicionar ao carrinho
			</button>
		</div>

	
	</div>
</section>

@endsection