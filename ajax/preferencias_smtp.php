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
	$QyUpdSmtpPreferencias = "
		update preferencias
		   SET smtp_host = '".$host."', smtp_auten = '".$auten."', smtp_user = '".$user."', smtp_passwd = '".$senha."'
	";

	$ans = mysql_query($QyUpdSmtpPreferencias) or die (mysql_error()); 
	if(!$ans)
	{
		echo "Desculpe, mas não foi possível configurar o SMTP no banco de dados. Tente novamente em instantes.";
	}
	else
	{
		header('location: ../nova_index.php?message=ok');
	}
	return;
}

$QySmtpParams ='
	select smtp_host, smtp_auten, smtp_user, smtp_passwd 
	  from preferencias
';
$rsSmtpParams = mysql_query($QySmtpParams, $cnx) or die(mysql_error());
$rowSmtpParams = mysql_fetch_assoc($rsSmtpParams);	

?>
<div class="row get-code-window">
<div class="row">
</div>

<h1>Configuração de SMTP</h1>	
    <form id="form_usuario" action="ajax/preferencias_smtp.php" method="post" class="form-horizontal" role="form">
    	<input type="hidden" name="acao" value="alterar"/>
        <fieldset class="container">
            <div class="row">
                <div class="form-group col-sm-2">
                    <label for="codigo">Host</label>
                    <input type="text" value="<?php echo $rowSmtpParams['smtp_host']; ?>" name="host" class="form-control" id="host" size="10" placeholder="Host de Smtp">
                </div>
                <div class="form-group col-sm-5">
                    <label for="nome">Autenticar?</label>
                    <input type="text" value="<?=$rowSmtpParams['smtp_auten']?>" name="auten" size="1" id="auten" class="form-control" placeholder="S ou N">
                </div>                
                <div class="form-group col-sm-2">
                    <label for="login">Usuário</label>
                    <input type="text" value="<?=$rowSmtpParams['smtp_user']?>" name="user" id="user" class="form-control" placeholder="Nome do usuário SMTP">
                </div>
                <div class="form-group col-sm-2">
                    <label for="login">Senha</label>
                    <input type="text" value="<?=$rowSmtpParams['smtp_passwd']?>" name="senha" id="senha" class="form-control" placeholder="Senha do usuário Stmp">
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