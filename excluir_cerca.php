<?php include('seguranca.php');

$codCerca = $_GET["codCerca"];
$codImei = $_GET["codImei"];

$con = mysql_connect('cloudservice.cgejdsdl842e.sa-east-1.rds.amazonaws.com', 'gpstracker', 'd1$1793689');
if (!$con) {
	die('Could not connect: ' . mysql_error());
}

mysql_select_db("tracker", $con);

if ($codCerca != "") {
	$consulta = "DELETE FROM geo_fence WHERE id = $codCerca";
	if (!mysql_query($consulta)) {
		echo "Ops! Algo deu errado: " . mysql_error();
	} else {
		echo "OK";
	}
}

if ($codImei != "") {
	$consulta = "DELETE FROM geo_fence WHERE imei = $codImei";
	if (!mysql_query($consulta)) {
		echo "Ops! Algo deu errado: " . mysql_error();
	} else {
		echo "OK";
	}
}

mysql_close($con);
?>
