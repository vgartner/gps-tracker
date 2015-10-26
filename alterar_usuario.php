<?php
	include_once 'seguranca.php';

	if (isset($_GET)) {
		$codCliente=$_GET["codCliente"];
		$nomeCliente=$_GET["nomeCliente"];
		$ativo=$_GET["ativo"];

		$con = mysql_connect('cloudservice.cgejdsdl842e.sa-east-1.rds.amazonaws.com', 'gpstracker', 'd1$1793689') or die(mysql_error());
		mysql_select_db("tracker", $con);

		if ($codCliente != "" && $nomeCliente != "" && $ativo != "") {
			if (!mysql_query("UPDATE cliente SET nome = '$nomeCliente', ativo = '$ativo' WHERE id = $codCliente")) {
				die(mysql_error());
			}
			else echo "OK";
		}

		mysql_close($con);
	}
?>