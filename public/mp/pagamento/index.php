<?php include('../config/config.php'); ?>
<?php include('../helpers/intermediador_pagamento/token.php'); ?>
<?php include('../helpers/class/class.MercadoPago.php');?>
<?php 
    $sql_check_venda = mysqli_query($conexao,"SELECT * FROM status WHERE id_venda = '".$_GET['id_venda']."'") or die("Erro");
    $resultado_check_venda = mysqli_fetch_assoc($sql_check_venda);
    
?>
<head>
  <title>Pagamento</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.3/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  
  <link rel="stylesheet" href="views/css/modal.css">
</head>
<div id="venda"></div>
<div class="container">
    <div class="row container">
        <div class="col-lg-3"></div>
        <div class="col-lg-6" style="border: 1px solid grey; border-radius: 10px;">
            <div>
                <img src="../img/logopix.png" style="width: 20%; float: right; padding: 4px;">
                <h4 style="padding: 4px;"> FINALIZAR COMPRA</h4>
                <b style="color: green;">Você está em um ambiente seguro <i class="fa fa-lock"></i></b>
                <hr>
                <div class="row">
                    <div class="col-lg-12"><center><h3>Total R$ <?php echo $resultado_check_venda['total'];?></h3></center></div>
                    <div class="col-lg-2"></div>
                    <div class="col-lg-8">
                        <img style="display:block; width:100%;" id="base64image" src="data:image/jpeg;base64, <?php echo $resultado_check_venda['qrcode'];?>" />
                    </div>
                    <div class="col-lg-2"></div>
                    <div class="col-lg-12"><hr></div>
                    <div class="col-lg-12">
                        <textarea class="form-control" readonly><?php echo $resultado_check_venda['linha'];?></textarea>
                        <br>
                        <button class="btn btn-primary" style="width: 100%;">Copiar PIX</button>
                    </div>
                </div>
                <hr>
            </div>
        </div>
    </div>
</div>

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
            }
        });
    }

    setInterval(selectNumUsuarios, tempo);
});
</script>