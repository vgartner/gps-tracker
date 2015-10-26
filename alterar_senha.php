<?php include('seguranca.php');

//Variavel $cliente setada na sessão no include de segurança

$senha=$_GET["senha_atual"];
$nova=$_GET["nova_senha"];
$repita=$_GET["repita_senha"];
$cliente=$_SESSION['clienteSession'];
$grupo=$_SESSION['grupoSession'];

$cliente = str_repeat('0', 10-strlen($cliente)).$cliente;


$con = mysql_connect('cloudservice.cgejdsdl842e.sa-east-1.rds.amazonaws.com', 'gpstracker', 'd1$1793689');
if (!$con) die('Could not connect: ' . mysql_error());

mysql_select_db("tracker", $con);

if ($senha != "" && $nova != "" && $repita != ""){
	$result = "";
	if($grupo != '') $result = mysql_query("SELECT 1 FROM grupo WHERE senha = '".md5($senha)."' AND id = '$grupo'", $con);
	else $result = mysql_query("SELECT 1 FROM cliente WHERE senha = '".md5($senha)."' AND id = '$cliente'", $con);

	if($result != false && mysql_num_rows($result) > 0) $data = mysql_fetch_assoc($result);
	else die("<div class='alert alert-danger'> <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button> <strong>Erro: </strong>A senha atual está incorreta.</div>");
	
	// if($nova != $repita) die("<div class='alert alert-warning'> <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button> <strong>Erro: </strong>As senhas não coincidem.</div>");
	
	$mudou = false;
	if($grupo != ''){
		$mudou = mysql_query("UPDATE grupo SET senha = '".md5($nova)."' WHERE id = '$grupo'", $con);
	} else {
		$mudou = mysql_query("UPDATE cliente SET senha = '".md5($nova)."' WHERE id = '$cliente'", $con);
	}
	
	if (!$mudou){
		die("<div class='alert alert-warning'> <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button> <strong>Falha no Banco de Dados: </strong>" . mysql_error() . ".</div>");
	}
	else {
		//Gravado com sucesso
		echo "<div class='alert alert-success'> <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button> <strong>Sucesso! </strong>A senha foi alterada com êxito.</div>";
	}
} else echo "Existem campos obrigatórios em branco!";

mysql_close($con);
?>
