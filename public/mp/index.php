<?php
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);
?>

<?php include('config/config.php'); ?>
<?php include('config/produtos.php'); ?>
<?php include('helpers/intermediador_pagamento/token.php'); ?>
<?php include('helpers/class/class.MercadoPago.php');?>
<?php 
    $sql_check_venda = mysqli_query($conexao,"SELECT * FROM status WHERE id_venda = '".$_GET['id_venda']."'") or die("Erro");
    $resultado_check_venda = mysqli_fetch_assoc($sql_check_venda);
?>
<?php $objeto = new pagamentos;?>
<head>
  <title>Finalizar</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, user-scalable=no">   
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.3/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@10/dist/sweetalert2.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10/dist/sweetalert2.min.js"></script>
  <style>
        /* Estilos para os links */
        .link {
            text-decoration: none;
            border: 1px solid #ccc;
            border-radius: 5px;
            padding: 10px 15px;
            margin: 5px;
            display: inline-block;
            color: black; /* Cor padrão da fonte */
            box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.2);
        }

        /* Estilo padrão da borda e fonte */
        .default-style {
            border-color: #ccc;
            border-radius: 5px;
            color: black;
        }

        /* Estilo da borda e fonte quando o link é clicado */
        .clicked-style {
            border-color: #4b96e6;
            border-radius: 5px;
            color: #4b96e6;
        }
    </style>
  <link rel="stylesheet" href="views/css/modal.css">
  
  <script src="https://sdk.mercadopago.com/js/v2"></script>
  <script> const mp = new MercadoPago("<?php echo $public_key;?>");</script>
</head>

<br>
<?php
$sql_venda  = mysqli_query($conexao,"SELECT * FROM status ORDER BY id DESC") or die("Erro");
$resultado_venda = mysqli_fetch_assoc($sql_venda);

$num_caracteres = 10;

for ($i = 0; $i < $num_caracteres; $i++) {
  $num = rand(65, 90);
  $caractere = chr($num);
  $rand .= $caractere;
}

$IdVenda    = $resultado_venda['id'] + 1;
$CodVenda   = $IdVenda.rand(1,99).$rand;

if(isset($_POST['BtnGerar'])){
	
    $nome       = str_replace(array("#", "'", ";", "*", "=", "INSERT", "insert", "delete", "DELETE", "where", "WHERE", "update", "UPDATE"), '', $_POST['nome']);
    $email      = str_replace(array("#", "'", ";", "*", "=", "INSERT", "insert", "delete", "DELETE", "where", "WHERE", "update", "UPDATE"), '', $_POST['email']);
    $cpf        = str_replace(array("#", "'", ";", "*", "=", "INSERT", "insert", "delete", "DELETE", "where", "WHERE", "update", "UPDATE"), '', $_POST['cpf']);
    
    include('helpers/intermediador_pagamento/gerar_pix.php');
    
	//$id_empresa = $_GET['id_empresa'];
	
	$item = $_GET['item'];
	$id_empresa = $_GET['id_empresa'];
	
	$sql = "INSERT INTO status(
		status, 
		codigo, 
		nome, 
		email, 
		cpf, 
		id_venda, 
		qrcode, 
		linha, 
		total, 
		id_empresa, 
		item
	) VALUES(
		'".$resultado->status."', 
		'".$resultado->id."', 
		'".$nome."', 
		'".$email."', 
		'".$cpf."', 
		'".$_GET['id_venda']."', 
		'".$resultado->point_of_interaction->transaction_data->qr_code_base64."', 
		'".$resultado->point_of_interaction->transaction_data->qr_code."', 
		'".$preco_item."', 
		'".$id_empresa."', 
		'".$item."'
	)";

	
    mysqli_query($conexao, $sql);
	
	echo "<script> window.location.href='https://".$host."/pagamento/?item=".$_GET['item']."&id_venda=".$_GET['id_venda']."'; </script>";
    
}

if($_GET['item'] <> ''){
    
    if($_GET['id_venda'] == ''){
        echo "<script> window.location.href='https://".$host."/?item=".$_GET['item']."&id_venda=".$CodVenda."&id_empresa=".$_GET['id_empresa']."'; </script>";
    }
echo '<div class="container">
        <div class="row">
            <div class="col-lg-6">
                <b style="float: left; color: grey;">Produto</b>
            </div>
            <div class="col-lg-6">
                <b style="float: right; color: grey;">Preço</b>
            </div>
            <div class="col-lg-12"><br></div>
            <div class="col-lg-6">
                <b style="float: left;">'.$titulo_item.'</b>
            </div>
            <div class="col-lg-6">
                <b style="float: right;">1X R$ '.$preco_item.'</b>
            </div>
        </div>
    </div>';
echo '<br>';
echo '<div class="container">
        <a class="link" data-toggle="tab" href="#home" id="link1" style="width: 100px; text-decoration: none;">
            <center>
                PIX <svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 512 512"><!--! Font Awesome Free 6.4.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. --><path d="M242.4 292.5C247.8 287.1 257.1 287.1 262.5 292.5L339.5 369.5C353.7 383.7 372.6 391.5 392.6 391.5H407.7L310.6 488.6C280.3 518.1 231.1 518.1 200.8 488.6L103.3 391.2H112.6C132.6 391.2 151.5 383.4 165.7 369.2L242.4 292.5zM262.5 218.9C256.1 224.4 247.9 224.5 242.4 218.9L165.7 142.2C151.5 127.1 132.6 120.2 112.6 120.2H103.3L200.7 22.76C231.1-7.586 280.3-7.586 310.6 22.76L407.8 119.9H392.6C372.6 119.9 353.7 127.7 339.5 141.9L262.5 218.9zM112.6 142.7C126.4 142.7 139.1 148.3 149.7 158.1L226.4 234.8C233.6 241.1 243 245.6 252.5 245.6C261.9 245.6 271.3 241.1 278.5 234.8L355.5 157.8C365.3 148.1 378.8 142.5 392.6 142.5H430.3L488.6 200.8C518.9 231.1 518.9 280.3 488.6 310.6L430.3 368.9H392.6C378.8 368.9 365.3 363.3 355.5 353.5L278.5 276.5C264.6 262.6 240.3 262.6 226.4 276.6L149.7 353.2C139.1 363 126.4 368.6 112.6 368.6H80.78L22.76 310.6C-7.586 280.3-7.586 231.1 22.76 200.8L80.78 142.7H112.6z"/></svg>
            </center>
        </a>
        <a class="link"data-toggle="tab" href="#menu1" id="link2" style="text-decoration: none;">Pagar com Cartão <i class="fa fa-credit-card"></i></a>
        <div class="tab-content">
            <div id="home" class="tab-pane fade in active">
                <br>
                <form action="" method="POST">
                    <div class="form-group">
                      <label for="email">Nome completo:</label>
                      <input type="text" class="form-control" id="nome" name="nome" style="height: 43px; border-radius: 18px;" required>
                    </div>
                    <div class="form-group">
                      <label for="email">CPF:</label>
                      <input type="text" class="form-control" id="nome" name="cpf" style="height: 43px; border-radius: 18px;" required>
                    </div>
                    <div class="form-group">
                      <label for="email">E-mail:</label>
                      <input type="text" class="form-control" id="nome" name="email" style="height: 43px; border-radius: 18px;" required>
                    </div>
                    <button type="submit" class="btn btn-success" name="BtnGerar" style="width: 100%; font-size: 18px;"><b>Gerar Pix</b></button>
                    <center><b style="color: green;">Você está em um ambiente seguro <i class="fa fa-lock"></i></b></center>
                </form>
            </div>
            <div id="menu1" class="tab-pane fade">
            <br>
            <form id="form-checkout">
                <div class="row">
                    <div class="col-lg-8">
                        <div id="form-checkout__cardNumber" class="container form-control" style="height: 43px; border-radius: 18px;"></div>
                        <br>
                    </div>
                    <div class="col-lg-4">
                        <div id="form-checkout__expirationDate" class="container form-control" style="height: 43px; border-radius: 18px;"></div>
                        <br>
                    </div>
                    
                    <div class="col-lg-12">
                        <div id="form-checkout__securityCode" class="container form-control" style="height: 43px; border-radius: 18px;"></div>
                        <br>
                    </div>
                    
                    <div class="col-lg-12">
                        <input type="text" id="form-checkout__cardholderName" class="form-control" style="height: 43px; border-radius: 18px;" />
                        <br>
                    </div>
                    
                    <div class="col-lg-12">
                        <select id="form-checkout__issuer" class="form-control" style="height: 43px; border-radius: 18px;"></select>
                        <br>
                    </div>
                    
                    <div class="col-lg-12">
                        <select id="form-checkout__installments" class="form-control" style="height: 43px; border-radius: 18px;"></select>
                        <br>
                    </div>
                    
                    <div class="col-lg-12">
                        <select id="form-checkout__identificationType" class="form-control" style="height: 43px; border-radius: 18px;"></select>
                        <br>
                    </div>
                    
                    <div class="col-lg-12">
                        <input type="text" id="form-checkout__identificationNumber" class="form-control" style="height: 43px; border-radius: 18px;" />
                        <br>
                    </div>
                    
                    <div class="col-lg-12">
                        <input type="email" id="form-checkout__cardholderEmail" class="form-control" style="height: 43px; border-radius: 18px;" />
                        <br>
                    </div>
                    <div class="col-lg-12">
                        <button type="submit" id="form-checkout__submit" class="btn btn-success" name="BtnGerar" style="width: 100%; font-size: 18px;"><b>Efetuar pagamento</b></button>
                        <progress value="0" class="progress-bar">Carregando...</progress>
                        <center><b style="color: green;">Você está em um ambiente seguro <i class="fa fa-lock"></i></b></center>
                    </div>
                </div>
            </form>
            </div>
        </div>
    </div>';
}
?>
<script>
document.getElementById("form-checkout__submit").addEventListener("click", function() {
    let timerInterval
    Swal.fire({
      title: 'Processando pagamento',
      imageUrl: 'https://<?php echo $host;?>/img/loading.gif',
      showConfirmButton: false,
      timer: 20500
    });
});
</script>
<script>
    const cardForm = mp.cardForm({
      amount: "<?php echo $preco_item;?>",
      iframe: true,
      form: {
        id: "form-checkout",
        cardNumber: {
          id: "form-checkout__cardNumber",
          placeholder: "Número do cartão",
        },
        expirationDate: {
          id: "form-checkout__expirationDate",
          placeholder: "MM/YY",
        },
        securityCode: {
          id: "form-checkout__securityCode",
          placeholder: "Código de segurança",
        },
        cardholderName: {
          id: "form-checkout__cardholderName",
          placeholder: "Titular do cartão",
        },
        issuer: {
          id: "form-checkout__issuer",
          placeholder: "Banco emissor",
        },
        installments: {
          id: "form-checkout__installments",
          placeholder: "Parcelas",
        },        
        identificationType: {
          id: "form-checkout__identificationType",
          placeholder: "Tipo de documento",
        },
        identificationNumber: {
          id: "form-checkout__identificationNumber",
          placeholder: "Número do documento",
        },
        cardholderEmail: {
          id: "form-checkout__cardholderEmail",
          placeholder: "E-mail",
        },
      },
      callbacks: {
        onFormMounted: error => {
          if (error) return console.warn("Form Mounted handling error: ", error);
          console.log("Form mounted");
        },
        onSubmit: event => {
          event.preventDefault();

          const {
            paymentMethodId: payment_method_id,
            issuerId: issuer_id,
            cardholderEmail: email,
            amount,
            token,
            installments,
            identificationNumber,
            identificationType,
          } = cardForm.getCardFormData();

          fetch("https://<?php echo $host;?>/helpers/intermediador_pagamento/processa.php?item=<?php echo $_GET['item'];?>&id_venda=<?php echo $_GET['id_venda'];?>", {
            method: "POST",
            headers: {
              "Content-Type": "application/json",
            },
            body: JSON.stringify({
              token,
              issuer_id,
              payment_method_id,
              transaction_amount: Number(amount),
              installments: Number(installments),
              description: "Descrição do produto",
              payer: {
                email,
                identification: {
                  type: identificationType,
                  number: identificationNumber,
                },
              },
            }),
          });
        },
        onFetching: (resource) => {
          console.log("Fetching resource: ", resource);

          // Animate progress bar
          const progressBar = document.querySelector(".progress-bar");
          progressBar.removeAttribute("value");

          return () => {
            progressBar.setAttribute("value", "0");
          };
        }
      },
    });
</script>
<script src="https://code.jquery.com/jquery-3.1.1.min.js"></script>
<script>
    $(document).ready(function() {
        var tempo = 2000; //Dois segundos
    
        function selectNumUsuarios(){
            $.ajax({
                url: "https://<?php echo $host;?>/check.php?item=<?php echo $_GET['item'];?>&id_venda=<?php echo $_GET['id_venda'];?>",
                success: function(data){
                    if(data.aprovado == 'sim') {
                        window.location.replace(data.link);
                    }
                    if(data.reprovado == 'sim') {
                        window.location.replace(data.link);
                    }
                }
            });
        }
    
        setInterval(selectNumUsuarios, tempo);
    });
</script>
<script>
    // Adicione um evento de clique aos links
    document.getElementById("link1").addEventListener("click", function() {
        // Remova a classe 'clicked-style' de todos os links
        document.querySelectorAll(".link").forEach(function(link) {
            link.classList.remove("clicked-style");
            link.classList.add("default-style");
        });
        // Adicione a classe 'clicked-style' apenas ao link clicado
        this.classList.add("clicked-style");
    });

    document.getElementById("link2").addEventListener("click", function() {
        // Remova a classe 'clicked-style' de todos os links
        document.querySelectorAll(".link").forEach(function(link) {
            link.classList.remove("clicked-style");
            link.classList.add("default-style");
        });
        // Adicione a classe 'clicked-style' apenas ao link clicado
        this.classList.add("clicked-style");
    });
</script>