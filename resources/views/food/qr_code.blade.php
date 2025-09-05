@extends('food.default', ['title' => 'PIX'])
@section('content')

<section class="featured spad" style="margin-top: -100px">
	<div class="container">
		
		<div class="row featured__filter">
			<input type="hidden" value="{{$item->transacao_id}}" id="transacao_id" name="">
			<input type="hidden" value="{{$item->status_pagamento}}" id="status" name="">

			<div class="col-md-4"></div>
			<div class="col-md-4 text-center div-pix">
				<img style="width: 300px; height: 300px;" src="data:image/jpeg;base64,{{$item->qr_code_base64}}"/>
			</div>		

			<div class="col-md-11 div-pix">
				<input type="text" readonly class="form-control" value="{{$item->qr_code}}" id="qrcode_input" />
			</div>
			<div class="col-md-1 div-pix">
				<button class="btn btn-dark w-100" onclick="copy()">
					Copiar
				</button>
			</div>

			<div class="col-12 status-approved text-center d-none">

				<h2 class="text-success mt-3" style="">
					<i class="fa fa-check"></i>
					PAGAMENTO APROVADO
				</h2>
				
			</div>

		</div>
	</div>
</section>

@endsection

@section('js')
<script type="text/javascript">
	var intervalVar = null

	function copy(){
		const inputTest = document.querySelector("#qrcode_input");

		inputTest.select();
		document.execCommand('copy');

		swal("", "Código pix copado!!", "success")
	}

	if($('#status').val() != "approved"){
		intervalVar =setInterval(() => {
			let transacao_id = $('#transacao_id').val();
			$.get(path_url+'api/delivery-link/consulta-pix/', {transacao_id: transacao_id})
			.done((success) => {
				console.log(success)
				if(success == "approved"){
					// location.reload()
					clearInterval(intervalVar)
					$('.div-pix').addClass('d-none')
					$('.status-approved').removeClass('d-none')

				}
			})
			.fail((err) => {
				console.log(err)
			})
		}, 2000)
	}

</script>
@endsection