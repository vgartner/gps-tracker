<?php
include('../seguranca.php');

if ($cliente != "master")
{
	header("location: ../nova_index.php");
}
else
{
	$acao  = $_POST['acao'];

	if($acao == 'logo_login'){		
		
		$image_info = getimagesize($_FILES["imgLogoLogin"]["tmp_name"]);
		$image_width = $image_info[0];
		$image_height = $image_info[1];
		
		if ($image_width != 350 || $image_height != 150)
		{
			header('location: ../nova_index.php?message=Por favor envie um imagem com as dimensões de 350x150');
		}
		else
		{
			$uploaddir = '/var/www/imagens/';
			$uploadfile = $uploaddir . basename($_FILES['imgLogoLogin']['name']);
			
			if (move_uploaded_file($_FILES['imgLogoLogin']['tmp_name'], $uploadfile)) {
				header('location: ../nova_index.php?message=Imagem atualizada com sucesso');
			} 
			else 
			{
				header('location: ../nova_index.php?message=ERRO');;
			}
		}
	}

	if($acao == 'logo_header'){		
		
		$image_info = getimagesize($_FILES["imgLogoHeader"]["tmp_name"]);
		$image_width = $image_info[0];
		$image_height = $image_info[1];
		
		if ($image_width != 170 || $image_height != 90)
		{
			header('location: ../nova_index.php?message=Por favor envie um imagem com as dimensões de 170x90');
		}
		else
		{
			$uploaddir = '/var/www/imagens/';
			$uploadfile = $uploaddir . basename($_FILES['imgLogoHeader']['name']);
			
			if (move_uploaded_file($_FILES['imgLogoHeader']['tmp_name'], $uploadfile)) {
				header('location: ../nova_index.php?message=Imagem atualizada com sucesso');
			} 
			else 
			{
				header('location: ../nova_index.php?message=ERRO');;
			}
		}
	}

?>
<div class="row get-code-window">
<div class="row">
</div>

<h1>Envio de Logos</h1>		
    <form enctype="multipart/form-data" action="ajax/preferencias_logos.php" method="POST" class="form-horizontal" role="form">    
     	<input type="hidden" name="acao" value="logo_login"/>
        <fieldset class="container">
            <div class="row">
                <div class="col-sm-12 text-center">
                	
      <label for="rastradoresCadastrados">Imagem da Logomarca no Login.<font color="#003366"><strong>Tamanho 
      350 X 150 px</strong></font></label>
      <input name="imgLogoLogin" type="file" class="form-control"/>
                    <br />

                    <input type="submit" class="btn btn-success btn-lg" value="Enviar" id="btnLogoLogin" class="form-control">
                </div>
            </div>
        </fieldset>
    </form>
     <form enctype="multipart/form-data" action="ajax/preferencias_logos.php" method="POST" class="form-horizontal" role="form">    
     	<input type="hidden" name="acao" value="logo_header"/>
        <fieldset class="container">
            <div class="row">
                <div class="col-sm-12 text-center">
                	<label for="rastradoresCadastrados">Imagem da Logomarca no Cabeçalho.</label>
      <label for="rastradoresCadastrados"><font color="#003366"><strong>Tamanho 
      170 X 90 px</strong></font></label>
      <input name="imgLogoHeader" type="file" class="form-control"/>
                    <br />

                    <input type="submit" class="btn btn-success btn-lg" value="Enviar" id="bnLogoHeader="form-control">
                </div>
            </div>
        </fieldset>
    </form>
<?php
mysql_close($cnx);
?>

<?php
}
?>

<script>
$("#btnRetirar").click(function(){
	window.location.href="ajax/delTracker.php?rastreador="+$("#rastreadoresCadastrados").val();
});
</script>