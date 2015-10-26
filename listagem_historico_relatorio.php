<?php
include('seguranca.php');
include('usuario/config.php');


$dataInicio = $_POST["txtDataInicio"];
$dataFinal = $_POST["txtDataFinal"];
$imei = $_POST["nrImeiConsulta"];
$hrDataInicio = $_POST["hrDataInicio"];
$hrDataFinal = $_POST["hrDataFinal"];
$mnDataInicio = $_POST["mnDataInicio"];
$mnDataFinal = $_POST["mnDataFinal"];

/** Retorna a hora no formato 00:00:00*/
function formataHora($hrEntrada, $mnEntrada)
{
    $hrSaida;
    $mnSaida;
    
    switch($hrEntrada)
    {
        case "0": $hrSaida = "00"; break;
        case "1": $hrSaida = "01"; break;
        case "2": $hrSaida = "02"; break;
        case "3": $hrSaida = "03"; break;
        case "4": $hrSaida = "04"; break;
        case "5": $hrSaida = "05"; break;
        case "6": $hrSaida = "06"; break;
        case "7": $hrSaida = "07"; break;
        case "8": $hrSaida = "08"; break;
        case "9": $hrSaida = "09"; break;
        case "10": $hrSaida = "10"; break;
        case "11": $hrSaida = "11"; break;
        case "12": $hrSaida = "12"; break;
        case "13": $hrSaida = "13"; break;
        case "14": $hrSaida = "14"; break;
        case "15": $hrSaida = "15"; break;
        case "16": $hrSaida = "16"; break;
        case "17": $hrSaida = "17"; break;
        case "18": $hrSaida = "18"; break;
        case "19": $hrSaida = "19"; break;
        case "20": $hrSaida = "20"; break;
        case "21": $hrSaida = "21"; break;
        case "22": $hrSaida = "22"; break;
        case "23": $hrSaida = "23"; break;
    }

    switch($mnEntrada)
    {
        case "00": $mnSaida = ":00:00"; break;
        case "10": $mnSaida = ":10:00"; break;
        case "15": $mnSaida = ":15:00"; break;
        case "20": $mnSaida = ":20:00"; break;
        case "25": $mnSaida = ":25:00"; break;
        case "30": $mnSaida = ":30:00"; break;
        case "35": $mnSaida = ":35:00"; break;
        case "40": $mnSaida = ":40:00"; break;
        case "45": $mnSaida = ":45:00"; break;
        case "50": $mnSaida = ":50:00"; break;
        case "55": $mnSaida = ":55:00"; break;
        case "59": $mnSaida = ":59:59"; break;
    }   
    

    return $hrSaida . $mnSaida;
}

$arDataInicio = explode('/', $dataInicio);

$dataInicioSql = $arDataInicio[2] . "-" . $arDataInicio[1] . "-" . $arDataInicio[0];
$dataFinalSql = substr($dataFinal, 6, 4) . "-" . substr($dataFinal, 3, 2) . "-" . substr($dataFinal, 0, 2);

$con = mysql_connect($DB_SERVER, $DB_USER, $DB_PASS);
mysql_select_db($DB_NAME, $con);


$result = mysql_query("SELECT date as data, imei, latitudeDecimalDegrees, latitudeHemisphere, longitudeDecimalDegrees, longitudeHemisphere, km_rodado, address, speed as velocidade, converte, ligado
						from gprmc where imei = '$imei' 
						and date between '$dataInicioSql ". formataHora($hrDataInicio, $mnDataInicio) ."' and '$dataFinalSql ". formataHora($hrDataFinal, $mnDataFinal) ."'
						order by date desc", $con);
						
$resultBem = mysql_query("SELECT * from bem where imei = '$imei'", $con);
$dataBem = mysql_fetch_assoc($resultBem);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<script type="text/javascript" src="js/latlong.js"></script>
<script type="text/javascript" src="js/geo.js"></script>
<title>Relatório</title>
</head>

<body>

<table border="1" id="relatorio">
<tr>
<td colspan="5">
<table>
<tr>
<th>Veículo:</th>
<td width="150"><?=$dataBem[name]?></td>
<th>Data Incial:</th>
<td width="150"><?=$dataInicio.' '.formataHora($hrDataInicio, $mnDataInicio)?></td>
<th>Data Final:</th>
<td><?=$dataFinal.' '.formataHora($hrDataFinal, $mnDataFinal)?></td>
</tr>
</table>
</td>
</tr>
<tr>
<th>
Data
</th>
<th>
Latitude
</th>
<th>
Longitude
</th>
<th>
Endereço
</th>
<th>
Velocidade
</th>
<th>
Ligado
</th>
</tr>
<?php
$latAnt = '';
$lonAnt = '';
$posicoesLatitude = array();
$posicoesLongitude = array();
?>
<?php while($data = mysql_fetch_assoc($result)):?>
<?php

$lat = $data['latitudeDecimalDegrees'];
$lon = $data['longitudeDecimalDegrees'];
$ligado = $data['ligado'];

if($lat != $latAnt && $lon != $lonAnt && $ligado != $ligadoAnt):
$latitudeDecimalDegrees = 0;
$longitudeDecimalDegrees = 0;
if($data['converte'] == 1) {
	strlen($data['latitudeDecimalDegrees']) == 9 && $data['latitudeDecimalDegrees'] = '0'.$data['latitudeDecimalDegrees'];
	$g = substr($data['latitudeDecimalDegrees'],0,3);
	$d = substr($data['latitudeDecimalDegrees'],3);
	$latitudeDecimalDegrees = $g + ($d/60);
	$data['latitudeHemisphere'] == "S" && $latitudeDecimalDegrees = $latitudeDecimalDegrees * -1;
	
	
	strlen($data['longitudeDecimalDegrees']) == 9 && $data['longitudeDecimalDegrees'] = '0'.$data['longitudeDecimalDegrees'];
	$g = substr($data['longitudeDecimalDegrees'],0,3);
	$d = substr($data['longitudeDecimalDegrees'],3);
	$longitudeDecimalDegrees = $g + ($d/60);
	$data['longitudeHemisphere'] == "W" && $longitudeDecimalDegrees = $longitudeDecimalDegrees * -1;
} else {
	$latitudeDecimalDegrees = $data['latitudeDecimalDegrees'];
	$longitudeDecimalDegrees = $data['longitudeDecimalDegrees'];
}
// PREVINE ERROS DE COORDENADAS QUE ESTÃO SEM O SINAL NEGATIVO
if ($latitudeDecimalDegrees > 0) {
	$latitudeDecimalDegrees = $latitudeDecimalDegrees * -1;
}
if ($longitudeDecimalDegrees > 0) {
	$longitudeDecimalDegrees = $longitudeDecimalDegrees * -1;
}

$speed = $data['velocidade'] * 1.609;

if(empty($origin)){
	$origin = $latitudeDecimalDegrees.','.$longitudeDecimalDegrees;
} else {
	$destin .= $latitudeDecimalDegrees.','.$longitudeDecimalDegrees.'|';
}

$address = utf8_encode($data['address']);

if ($address == null or $address == "")
{
	# Convert the GPS coordinates to a human readable address
	$json = json_decode(file_get_contents("http://maps.google.com/maps/api/geocode/json?sensor=false&latlng=$latitudeDecimalDegrees,$longitudeDecimalDegrees&language=es-ES"));
	if ( isset( $json->status ) && $json->status == 'OK') {
		$address = $json->results[0]->formatted_address;
	}

	if (!mysql_query("UPDATE gprmc set address = '". utf8_decode($address) ."' where imei = '$imei' and latitudeDecimalDegrees = $data[latitudeDecimalDegrees] and longitudeDecimalDegrees = $data[longitudeDecimalDegrees] and date = $data[data]", $con))
	{
		//die('Error: ' . mysql_error());
	}
}
?>
<tr>
<td>
<?=date('d/m/Y H:i:s', strtotime($data[data]))?>
</td>
<td>
<?=$latitudeDecimalDegrees?>
</td>
<td>
<?=$longitudeDecimalDegrees?>
</td>
<td>
<?=$address?>
</td>
<td>
<?=(int)($data['velocidade']*1.609344)?>
</td>
<td>
<?=$data['ligado']=='S' || $data['velocidade'] > 0?'Sim':'Não'?>
</td>
</tr>
<?php
$latAnt = $lat;
$lonAnt = $lon;
$ligadoAnt = $ligado;
?>
<?php endif?>
<?php endwhile;?>
<tr>
<td colspan="4" align="right">Total de Km Rodados</td>

<td id="total_km"></td>
</tr>
</table>
<input type="button" value="Imprimir" onclick="javascript:window.print();" />
<script type="text/javascript">
	var tabRel = document.getElementById('relatorio');
	var latAnt = 0;
	var latAtu = 0;
	var lonAtu = 0;
	var lonAnt = 0;
	var distance = 0.00;

	latAtu = tabRel.rows[2].cells[1].innerHTML;
	lonAtu = tabRel.rows[2].cells[2].innerHTML;

	for(var i = 3; i<tabRel.rows.length-1; i++){
		var row = tabRel.rows[i];
		latAnt = row.cells[1].innerHTML;
		lonAnt = row.cells[2].innerHTML;

		var p1 = new LatLon(latAtu, lonAtu);
		var p2 = new LatLon(latAnt, lonAnt);

		distance += parseFloat(p1.distanceTo(p2));
		
		latAtu = latAnt;
		lonAtu = lonAnt;
	}
	
	document.getElementById('total_km').innerHTML = parseInt(distance)+' km';
</script>
</body>
</html>