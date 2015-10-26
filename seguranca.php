<?php
//Sessão iniciada em default.php
session_start();

$cliente       = isset($_SESSION['clienteSession']) ? $_SESSION['clienteSession'] : "";
$grupo         = isset($_SESSION['grupoSession']) ? $_SESSION['grupoSession'] : "";
$nmCliente     = isset($_SESSION['logSessioUser']) ? $_SESSION['logSessioUser'] : "";
$token         = isset($_SESSION['tokenSession']) ? $_SESSION['tokenSession'] : "";
$id_admin      = isset($_SESSION['idClienteSession']) ? $_SESSION['idClienteSession'] : "0";
$config        = isset($_SESSION['configSession']) ? $_SESSION['configSession'] : "";
$representante = isset($_SESSION['representanteSession']) ? $_SESSION['representanteSession'] : "";

if ($cliente == "") {
	//redirect para login
	header("Location: index.php");
}


/*if ($token == "") {
	//redirect para login
	die('Error token!');
}*/
?>