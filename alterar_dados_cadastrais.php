<?php
include('seguranca.php');
include('usuario/config.php');

$email = $_GET['email'];
$celular = $_GET['celular'];
$endereco = $_GET['endereco'];
$bairro = $_GET['bairro'];
$cidade = $_GET['cidade'];
$estado = $_GET['estado'];
$cep = $_GET['cep'];
$telefone1 = $_GET['telefone1'];
$telefone2 = $_GET['telefone2'];


$con = mysql_connect($DB_SERVER, $DB_USER, $DB_PASS);

mysql_select_db($DB_NAME);

if(mysql_query("UPDATE cliente SET email = '$email', celular = '$celular', endereco = '$endereco', 
	bairro = '$bairro', cidade = '$cidade', estado = '$estado', cep = '$cep', telefone1 = '$telefone1', telefone2 = '$telefone2' where id = $cliente", $con)){
	echo 'OK';
} else echo mysql_error();

?>