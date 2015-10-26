<?php
include('../seguranca.php');

if ($cliente != "master")
{
	header("location: ../nova_index.php");
}
else
{

$cnx = mysql_connect("cloudservice.cgejdsdl842e.sa-east-1.rds.amazonaws.com", "gpstracker", "d1$1793689")  or die("Could not connect: " . mysql_error());
mysql_select_db('tracker', $cnx);

$acao  = $_POST['acao'];
$host  = $_POST['host'];
$auten = $_POST['auten'];
$user  = $_POST['user'];
$senha = $_POST['senha'];

if($acao == 'alterar'){		
	$Qy = "
		insert into rastreadores
		   (rastreador)
		   values
		   ('".$_POST["rastreador"]."')
	";
	if(!mysql_query($Qy,$cnx))
	{
		echo "Desculpe, mas não foi possível gravar o novo rastreador. Tente novamente em instantes.";
	}
	else
	{
		header('location: ../nova_index.php?message=OK');
	}
	return;
}
else
{
	$Qy ='
		select *
		  from rastreadores
	';
	$rs = mysql_query($Qy, $cnx);	
}

?>
<div class="row get-code-window">
<div class="row">
</div>

<h1>Cadastrar Rastreador</h1>	
	
    <form id="form" action="ajax/preferencias_rastreadores.php" method="post" class="form-horizontal" role="form">
     	<input type="hidden" name="acao" value="alterar"/>
        <fieldset class="container">
            <div class="row">
                <div class="form-group col-sm-12 text-center">
                	<label for="rastradoresCadastrados">Retirar Rastreador cadastrado.</label>
                    
                    <select id="rastreadoresCadastrados" class="form-control">
                     <?php
                     while($row = mysql_fetch_assoc($rs))
                     {
                        printf('
                            <option id="'.$row["rastreador"].')">'.$row["rastreador"].'</option>
                        '); 
                     }
                     ?>     
                     </select>
                     <br />

                     <input type="button" class="btn btn-success btn-lg" value="Retirar" id="btnRetirar" class="form-control">
                </div>
            </div>
            <div class="row">
                <div class="form-group col-sm-12 text-center">
                    <label for="codigo">Cadastrar novo Rastreador</label>
                    <input type="text" value="<?=$row['rastreador']?>" name="rastreador" class="form-control" id="rastreador" size="10">
                </div>
            </div>
            <div class="row">
                <div class="form-group col-sm-12 text-center">
                    <input type="submit" class="btn btn-success btn-lg" value="Salvar" class="form-control">
                    <img style="display:none" src="../imagens/executando.gif" id="usuarioFormExecutando">
                    <img id="usuarioFormSucesso" style="display:none" src="../imagens/sucesso.png">
                    <span id="formError" style="display:none; color:red; font-size:14px;"></span>
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