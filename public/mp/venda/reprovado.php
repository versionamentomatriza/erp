<head>
  <title>Pagamento Aprovado</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.3/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  
  <link rel="stylesheet" href="views/css/modal.css">
</head>
<br>
<div class="container">
<div class="row container">
    <div class="col-lg-3"></div>
    <div class="col-lg-6" style="border: 1px solid grey; border-radius: 10px;">
        <div>
            <h4 style="padding: 4px;">PAGAMENTO REPROVADO</h4>
            <b style="color: red;">Tentar efetuar pagamento novamente!</b>
            <hr>
            <div class="row">
                <div class="col-lg-6">
                    <b style="float: left; color: grey;">Produto</b>
                </div>
                <div class="col-lg-6">
                    <b style="float: right; color: grey;">Preço</b>
                </div>
                <div class="col-lg-12"><br></div>
                <div class="col-lg-6">
                    <b style="float: left;"><?php echo $titulo_item;?></b>
                </div>
                <div class="col-lg-6">
                    <b style="float: right;">1X R$ <?php echo $preco_item;?></b>
                </div>
            </div>
            <hr>
        </div>
        <a href="https://<?php echo $host;?>/?item=<?php echo $_GET['item'];?>" class="btn btn-primary" style="width: 100%;">Tentar novamente</a>
        <br><br>
    </div>
</div>
</div>