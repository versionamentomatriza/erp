@extends('layouts.app', ['title' => 'Importar Clientes'])
@section('css')
<style type="text/css">
    .btn-file {
        position: relative;
        overflow: hidden;
    }

    .btn-file input[type=file] {
        position: absolute;
        top: 0;
        right: 0;
        min-width: 100%;
        min-height: 100%;
        font-size: 100px;
        text-align: right;
        filter: alpha(opacity=0);
        opacity: 0;
        outline: none;
        background: white;
        cursor: inherit;
        display: block;
    }
</style>
@endsection
@section('content')

<div class="card mt-1">
    <div class="card-header">
        <h4>Importar Clientes</h4>
        <div style="text-align: right; margin-top: -35px;">
            <a href="{{ route('clientes.index') }}" class="btn btn-danger btn-sm px-3">
                <i class="ri-arrow-left-double-fill"></i>Voltar
            </a>
        </div>
    </div>
    <div class="card-body">
	<hr>	
        
        <div class="row">

			<p>Baixar modelo em Tabela de Excel para preenchimento dos dados para importação. 
			
			<a href="https://suporte.matriza.com.br/cadastros/cadastro-de-clientes.html#importacao" target="_blank" title="Ajuda">
				<i class="fa fa-question-circle" style="font-size: 20px; color: #629972;"></i>
			</a>			


			<!-- Adicione o link do Font Awesome no <head> do seu HTML -->
			<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

        </div>

            <a href="{{ route('clientes.import-download') }}" class="btn btn-primary">
                <i class="ri-file-download-line"></i>
                Download do Modelo para Preenchimento
            </a>
			<p>
			<p><em>Obs: Campos com <span class="text-danger">*</span> são obrigatórios</em></p>
    </div>
    <div class="card-footer">
        <hr>
        <form id="form-import" class="row" method="post" action="{{ route('clientes.import-store') }}" enctype="multipart/form-data">
            @csrf
            <p>Importar Modelo preenchido para importação dos dados
			
			<a href="https://suporte.matriza.com.br/cadastros/cadastro-de-clientes.html#upload" target="_blank" title="Ajuda">
				<i class="fa fa-question-circle" style="font-size: 20px; color: #629972;"></i>
			</a>
			
            <div class="form-group validated col-12 col-lg-6">
                <label class="col-form-label">.xls/.xlsx</label>
                <div class="">
                    <span class="btn btn-success btn-file">
                        <i class="ri-file-search-line"></i>
                        Procurar Arquivo Preenchido<input accept=".xls, .xlsx" name="file" type="file" id="file">
                    </span>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
@section('js')
<script type="text/javascript">
    $('#file').change(function() {
        $('#form-import').submit();
        $body = $("body");
        $body.addClass("loading");
        
    });
 //loading importação
    $('#file').change(function() {
    let file = $(this).val();
    if (file) {
        swal({
            title: "Importando Clientes",
            text: "Aguarde, estamos processando seu arquivo...",
            buttons: false,
            closeOnClickOutside: false,
            closeOnEsc: false,
            content: {
                element: "div",
                attributes: {
                    innerHTML: '<i class="fa fa-spinner fa-spin" style="font-size: 24px; margin-top: 15px;"></i>'
                }
            }
        });

        $('#form-import').submit();
    }
});

// Quando a página carregar, verificar se houve sucesso (flash_success)
document.addEventListener("DOMContentLoaded", function() {
    if ("{{ session('flash_success') }}" !== "") {
        swal.close(); // Fecha o swal de carregamento
        setTimeout(function() {
            document.getElementById('logoutModal').style.display = 'flex';
        }, 200);
    }
});

// Função para fechar o modal
function closeModal() {
    document.getElementById('logoutModal').style.display = 'none';
}

</script>
@endsection
