<?php
include('../seguranca.php');

if ($cliente != "master")
{
	header("location: ../nova_index.php");
}
else
{

$cnx = mysql_connect("cloudservice.cgejdsdl842e.sa-east-1.rds.amazonaws.com", "gpstracker", "d1$1793689")
  or die("Could not connect: " . mysql_error());
mysql_select_db('tracker', $cnx);

$acao    = $_POST['acao'];
$codigo  = $_POST['codigo'];
$nome    = $_POST['nome'];
$apelido = $_POST['login'];
$senha   = $_POST['senha'];

if($acao == 'alterar'){	
	if(!empty($senha))
	{
		$senha = md5($senha);
	} 
	else 
	{
		$sqlSenha = "";
	}
	
	if(!mysql_query("UPDATE cliente SET nome='$nome', apelido='$apelido', senha = '$senha' WHERE id = '$codigo'", $cnx)){
        // echo "{sucesso:\"FAIL\", bug:\"".mysql_error($cnx)."\"}";
        $mensagem['sucesso'] = "FAIL";
        $mensagem['bug'] = mysql_error($cnx);
        echo json_encode($mensagem);
    }
	else
	{
        //$mensagem['sucesso'] = "OK";
        //$mensagem['cliente'] = $codigo;
        //echo json_encode($mensagem);
        // echo "{sucesso:\"OK\", cliente:\"$codigo\"}";
		header('location: ../nova_index.php?message=ok');
	}
	return;
}

$id = strip_tags($_GET['id']);
if(!empty($id)){
	$resCliente = mysql_query("SELECT * FROM cliente WHERE id = '$id'", $cnx);
	if($resCliente !== false){
		$dataCliente = mysql_fetch_assoc($resCliente);
	}
}

?>
<div class="row get-code-window">

<?php
    // Variável que volta a tela anterior
    $anterior = ($dataCliente['admin'] == "S") ? "admin" : "usuarios";
?>
<div class="row">
</div>

<h1>Trocar Senha do Administrador.</h1>	
    <form id="form_usuario" action="ajax/usuarios_adm.php" method="post" class="form-horizontal" role="form">
    	<input type="hidden" name="acao" value="alterar"/>
        <input type="hidden" name="codigo" value="<?=$dataCliente['id']?>" id="cliente_id"/>
        <fieldset class="container">
            <div class="row">
                <div class="form-group col-sm-2">
                    <label for="codigo">Código</label>
                    <input type="text" disabled value="<?=$dataCliente['id']?>" class="form-control" id="codigo" size="10">
                </div>
                <div class="form-group col-sm-5">
                    <label for="nome">Nome</label>
                    <input type="text" value="<?=$dataCliente['nome']?>" name="nome" required size="50" id="nome" class="form-control">
                </div>                
                <div class="form-group col-sm-2">
                    <label for="login">Login</label>
                    <input type="text" value="<?=$dataCliente['apelido']?>" name="login" id="login" required class="form-control">
                </div>
                <div class="form-group col-sm-2">
                    <label for="senha">Senha</label>
                    <input type="text" name="senha" id="senha" class="form-control">
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