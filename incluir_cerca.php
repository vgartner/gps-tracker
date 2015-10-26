<?php

include('seguranca.php');

$imei = $_GET["imei"];
$nome = $_GET["NomeCerca"];
$coordenadas = $_GET["cerca"];
$tipoEnvio = 0;
$tipoAcao = 0;

$cnx = mysql_connect("cloudservice.cgejdsdl842e.sa-east-1.rds.amazonaws.com", "gpstracker", "d1$1793689") or die("Could not connect: " . mysql_error());
mysql_select_db('tracker', $cnx);

$resultGPRMC = mysql_query("SELECT latitudeDecimalDegrees, latitudeHemisphere, longitudeDecimalDegrees, longitudeHemisphere FROM gprmc WHERE gpsSignalIndicator = 'F' and imei = $imei order by id desc limit 1,1") or die(mysql_error());
if (mysql_num_rows($resultGPRMC)) {
	while ($data = mysql_fetch_assoc($resultGPRMC)) {
		strlen($data['latitudeDecimalDegrees']) == 9 && $data['latitudeDecimalDegrees'] = '0'.$data['latitudeDecimalDegrees'];
		$g = substr($data['latitudeDecimalDegrees'],0,3);
		$d = substr($data['latitudeDecimalDegrees'],3);
		$latitudeDecimalDegrees = $g + ($d/60);
		$data['latitudeHemisphere'] == "S" && $latitudeDecimalDegrees = $latitudeDecimalDegrees * -1;

		strlen($data['longitudeDecimalDegrees']) == 9 && $data['longitudeDecimalDegrees'] = '0'.$data['longitudeDecimalDegrees'];
		$g = substr($data['longitudeDecimalDegrees'],0,3);
		$d = substr($data['longitudeDecimalDegrees'],3);
		$longitudeDecimalDegrees = $g + ($d/60);
		$data['longitudeHemisphere'] == "S" && $longitudeDecimalDegrees = $longitudeDecimalDegrees * -1;

		$longitudeDecimalDegrees = $longitudeDecimalDegrees * -1;
	}

	$lat_point = $latitudeDecimalDegrees;
	$lng_point = $longitudeDecimalDegrees;
}


$cerca = str_replace("(", "", str_replace(")", "", str_replace(")(", "|", $coordenadas)));

$exp = explode("|", $cerca);

if((count($exp)) < 5) {
	$strExp = explode(",", $exp[0]);
	$strExp1 = explode(",", $exp[2]);
} else {
	$int = (count($exp)) / 2;
	$strExp = explode(",", $exp[0]);
	$strExp1 = explode(",", $exp[$int]);
}

$lat_vertice_1 = $strExp[0];
$lng_vertice_1 = $strExp[1];
$lat_vertice_2 = $strExp1[0];
$lng_vertice_2 = $strExp1[1];

if ( $lat_vertice_1 < $lat_point ||  $lat_point < $lat_vertice_2 && $lng_point < $lng_vertice_1 || $lng_vertice_2 < $lng_point ) {
	$status = '0';
} else {
	$status = '1';
}

if ($verifica = mysql_query("SELECT nome FROM geo_fence WHERE imei = '$imei'")) {
	if (mysql_num_rows($verifica)) {
		echo "Detectamos uma cerca existente para o veículo selecionado.";
	}
	else {
		$sql = "INSERT INTO geo_fence (coordenadas,nome,imei,tipo,tipoEnvio,tipoAcao,dt_incao,disp) VALUES('$cerca','$nome','$imei','$status','$tipoEnvio','$tipoAcao','". date("d/m/Y") ." ". date("H:i:s") ."','S')";
		$resultado = mysql_query($sql) or die (mysql_error());
	}
}
else echo mysql_error();

// echo "<script language='javascript'>window.alert('Cerca criada com sucesso!');window.location.href='mapa.php?imei=$imei';</script>";
echo "OK";

mysql_close($cnx);
?>
