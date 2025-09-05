@extends('layouts.app', ['title' => 'Importar Produtos'])
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

    /* Estilos do Modal */
    .modal {
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100vw;
        height: 100vh;
        background-color: rgba(0, 0, 0, 0.5);
        display: none;
        align-items: center;
        justify-content: center;
    }

    .modal-content {
        background: white;
        padding: 20px;
        width: 300px;
        text-align: center;
        border-radius: 10px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
    }

    .modal-text {
        font-size: 16px;
        color: #333;
        margin-bottom: 15px;
    }

    .modal-buttons {
        display: flex;
        justify-content: space-between;
    }

    .modal-btn {
        flex: 1;
        padding: 8px;
        margin: 5px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 14px;
        text-decoration: none;
        text-align: center;
        display: inline-block;
    }

    .close-btn {
        background-color: #ccc;
        color: #000;
    }

    .logout-btn {
        background-color: #d9534f;
        color: white;
    }

    .close-btn:hover {
        background-color: #bbb;
    }

    .logout-btn:hover {
        background-color: #c9302c;
    }
</style>
@endsection

@section('content')
<div class="card mt-1">
    <div class="card-header">
        <h4>Importar Produtos</h4>
        <div style="text-align: right; margin-top: -35px;">
            <a href="{{ route('produtos.index') }}" class="btn btn-danger btn-sm px-3">
                <i class="ri-arrow-left-double-fill"></i>Voltar
            </a>
        </div>
    </div>
    <div class="card-body">
        <p>Baixar modelo em Tabela de Excel para preenchimento dos dados para importação. 
        <a href="https://suporte.matriza.com.br/cadastros/cadastro-de-produtos.html#importacao" target="_blank" title="Ajuda">
            <i class="fa fa-question-circle" style="font-size: 20px; color: #629972;"></i>
        </a>
        <p><em>Obs: Campos com <span class="text-danger">*</span> são obrigatórios</em></p>
        <hr>    

            <div class="row">
		
            <div class="col-12 col-md-6">
                <h5><strong class="text-primary">Nome</strong><span class="text-danger">*</span> - tipo texto</h5>
                <h5><strong class="text-primary">Categoria</strong> - tipo texto</h5>
                <h5><strong class="text-primary">Valor de venda</strong><span class="text-danger">*</span> - tipo moeda</h5>
                <h5><strong class="text-primary">Valor de compra</strong> - tipo moeda</h5>
                <h5><strong class="text-primary">NCM</strong><span class="text-danger">*</span> - tipo númerico</h5>
                <h5><strong class="text-primary">Código de barras</strong> - tipo texto</h5>
                <h5><strong class="text-primary">CEST</strong> - tipo númerico</h5>
                <h5><strong class="text-primary">CST/CSOSN</strong><span class="text-danger">*</span> - tipo númerico</h5>
                <h5><strong class="text-primary">CST PIS</strong><span class="text-danger">*</span> - tipo númerico</h5>
                <h5><strong class="text-primary">CST COFINS</strong><span class="text-danger">*</span> - tipo númerico</h5>
                <h5><strong class="text-primary">CST IPI</strong><span class="text-danger">*</span> - tipo númerico</h5>
                <h5><strong class="text-primary">% Red. base de cálculo</strong> - tipo percentual</h5>
                <h5><strong class="text-primary">Origem</strong> - tipo númerico</h5>
                <h5><strong class="text-primary">Código de enquadramento IPI</strong> - tipo númerico</h5>
            </div>
            <div class="col-12 col-md-6">
                <h5><strong class="text-primary">CFOP estadual</strong><span class="text-danger">*</span> - tipo númerico</h5>
                <h5><strong class="text-primary">CFOP outro estado</strong><span class="text-danger">*</span> - tipo númerico</h5>
                <h5><strong class="text-primary">Código do benefício</strong> - tipo texto</h5>
                <h5><strong class="text-primary">Unidade</strong> - tipo texto</h5>
                <h5><strong class="text-primary">Origem</strong> - tipo númerico</h5>
                <h5><strong class="text-primary">Gerenciar Estoque</strong> - tipo binário 1 ou 0</h5>
                <h5><strong class="text-primary">%ICMS</strong> - tipo percentual</h5>
                <h5><strong class="text-primary">%PIS</strong> - tipo percentual</h5>
                <h5><strong class="text-primary">%COFINS</strong> - tipo percentual</h5>
                <h5><strong class="text-primary">%IPI</strong> - tipo percentual</h5>
       
            </div>
			<!-- Adicione o link do Font Awesome no <head> do seu HTML -->
			<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

        </div>

            <a href="{{ route('produtos.import-download') }}" class="btn btn-primary">
                <i class="ri-file-download-line"></i>
                Download do Modelo para Preenchimento
            </a>
			<p>
			
    </div>

    <div class="card-footer"> 
        <hr>
        <form id="form-import" class="row" method="post" action="{{ route('produtos.import-store') }}" enctype="multipart/form-data">
            @csrf
            <p>Importar Modelo preenchido para importação dos dados
            <a href="https://suporte.matriza.com.br/cadastros/cadastro-de-produtos.html#upload" target="_blank" title="Ajuda">
                <i class="fa fa-question-circle" style="font-size: 20px; color: #629972;"></i>
            </a>

            <div class="form-group validated col-12 col-lg-6">
                <label class="col-form-label">.xls/.xlsx</label>
                <div>
                    <span class="btn btn-success btn-file">
                        <i class="ri-file-search-line"></i> Procurar Arquivo Preenchido
                        <input accept=".xls, .xlsx" name="file" type="file" id="file">
                    </span>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal de Alerta -->
<div id="logoutModal" class="modal">
    <div class="modal-content">
        <p class="modal-text">Os produtos importados só serão listados após o logoff.<br>Deseja sair agora ou mais tarde?</p>
        <div class="modal-buttons">
            <button class="modal-btn close-btn" onclick="closeModal()">Sair mais tarde</button>
            <a class="modal-btn logout-btn" href="{{ route('logout') }}" 
               onclick="event.preventDefault();document.getElementById('logout-form').submit();">
                Sair
            </a>
        </div>
    </div>
</div>

<form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
    @csrf
</form>
@endsection

@section('js')
<script type="text/javascript">
    $('#file').change(function() {
        $('#form-import').submit();
        $body = $("body");
        $body.addClass("loading");
    });

    document.addEventListener("DOMContentLoaded", function() {
        if ("{{ session('flash_success') }}" !== "") {
            setTimeout(function() {
                document.getElementById('logoutModal').style.display = 'flex';
            }, 200);
        }
    });

    function closeModal() {
        document.getElementById('logoutModal').style.display = 'none';
    }

    // Arquivo: public/js/importar_produtos.js

    $('#file').change(function() {
    let file = $(this).val();
    if (file) {
        swal({
            title: "Importando Produtos",
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
