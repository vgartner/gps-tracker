<?php
header("Access-Control-Allow-Origin: *"); 

$databasehost     = "cloudservice.cgejdsdl842e.sa-east-1.rds.amazonaws.com";
$databasename     = "tracker";
$databaseusername = "gpstracker";
$databasepassword = "d1$1793689";

$con = mysql_connect($databasehost,$databaseusername,$databasepassword) or die(mysql_error());
mysql_select_db($databasename) or die(mysql_error());
mysql_query("SET CHARACTER SET utf8");

if ($_GET["service"] == 'mapAllDevices')
{
	getMapAllDevices();
}
else if($_GET["service"] == 'newMap')
{
	setNewMap();
}


include("seguranca.php");
include("usuario/config.php");

/******************** Functions ***********************************************/
function getMapAllDevices(){

	$cliente  = $_POST["cliente"];
	$grupoGet = $_POST["grupoGet"];
	$grupo    = $_POST["grupo"];
	$coords   = array();
	$i        = 0;

	$dadosBem = mysql_query("
	select b.name, b.imei, b.operadora, b.hodometro, b.modelo_rastreador, b.status_sinal, b.bloqueado, b.tipo, b.identificacao, b.apelido, b.marca, b.ligado, 
               la.latitudeDecimalDegrees, la.longitudeDecimalDegrees, la.longitudeHemisphere, la.latitudeHemisphere, la.converte, la.speed
	  from bem b,
		loc_atual la
	 where b.imei = la.imei
	");

	$strInfo = '[';

	while ($row = mysql_fetch_assoc($dadosBem)) {

		if($row['converte'] == 1)
		{
			strlen($row['latitudeDecimalDegrees']) == 9 && $row['latitudeDecimalDegrees'] = '0'.$row['latitudeDecimalDegrees'];
			$g = substr($row['latitudeDecimalDegrees'],0,3);
			$d = substr($row['latitudeDecimalDegrees'],3);
			$latitudeDecimalDegrees = $g + ($d/60);
			$row['latitudeHemisphere'] == "S" && $latitudeDecimalDegrees = $latitudeDecimalDegrees * -1;
		
			strlen($row['longitudeDecimalDegrees']) == 9 && $row['longitudeDecimalDegrees'] = '0'.$row['longitudeDecimalDegrees'];
			$g = substr($row['longitudeDecimalDegrees'],0,3);
			$d = substr($row['longitudeDecimalDegrees'],3);
			$longitudeDecimalDegrees = $g + ($d/60);
			$row['longitudeHemisphere'] == "W" && $longitudeDecimalDegrees = $longitudeDecimalDegrees * -1;
		
		} 
		else 
		{
			$latitudeDecimalDegrees = $row['latitudeDecimalDegrees'];
			$longitudeDecimalDegrees = $row['longitudeDecimalDegrees'];
		}

		
			$strInfo .= '{';
			$strInfo .= "'imei':'".$row['imei']."',";
			$strInfo .= "'name':'".$row['name']."',";
			$strInfo .= "'sinal':'".$row['status_sinal']."',";
			$strInfo .= "'block':'".$row['bloqueado']."',";
			$strInfo .= "'tipo':'".$row['tipo']."',";
			$strInfo .= "'latitude':'".$latitudeDecimalDegrees."',";
			$strInfo .= "'longitude':'".$longitudeDecimalDegrees."',";
			$strInfo .= "'velocidade':'".$row['speed']."',";
			$strInfo .= "'identificacao':'".$row['identificacao']."',";
			$strInfo .= "'apelido':'".$row['apelido']."',";
			$strInfo .= "'marca':'".$row['marca']."',";
			$strInfo .= "'modelo':'".$row['modelo_rastreador']."',";
			$strInfo .= "'hodometro':'".$row['hodometro']."',";
			$strInfo .= "'chip':'".$row['operadora']."',";
			$strInfo .= "'endereco':'',";
			$strInfo .= "'ligado':'".$row['ligado']."'";
			$strInfo .= '},';
	}
	$strInfo .= ']';
	$strInfo = str_replace(',]', ']', $strInfo);
	echo $strInfo;
}

function setNewMap()
{
	$row    = array();
	$imei   = $_GET["imei"];
	$dtini  = $_GET["dtini"];
	$hrini  = $_GET["hrini"];
	$dtfin  = $_GET["dtfin"];
	$hrfin  = $_GET["hrfin"];
	$limite = $_GET["limite"];
	$i      = 0;			
	
	switch($hrini)
    {
        case "0": $hrini = "00:00:00"; break;
        case "1": $hrini = "01:00:00"; break;
        case "2": $hrini = "02:00:00"; break;
        case "3": $hrini = "03:00:00"; break;
        case "4": $hrini = "04:00:00"; break;
        case "5": $hrini = "05:00:00"; break;
        case "6": $hrini = "06:00:00"; break;
        case "7": $hrini = "07:00:00"; break;
        case "8": $hrini = "08:00:00"; break;
        case "9": $hrini = "09:00:00"; break;
        case "10": $hrini = "10:00:00"; break;
        case "11": $hrini = "11:00:00"; break;
        case "12": $hrini = "12:00:00"; break;
        case "13": $hrini = "13:00:00"; break;
        case "14": $hrini = "14:00:00"; break;
        case "15": $hrini = "15:00:00"; break;
        case "16": $hrini = "16:00:00"; break;
        case "17": $hrini = "17:00:00"; break;
        case "18": $hrini = "18:00:00"; break;
        case "19": $hrini = "19:00:00"; break;
        case "20": $hrini = "20:00:00"; break;
        case "21": $hrini = "21:00:00"; break;
        case "22": $hrini = "22:00:00"; break;
        case "23": $hrini = "23:00:00"; break;
    }
	
	switch($hrfin)
    {
        case "0": $hrfin = "00:00:00"; break;
        case "1": $hrfin = "01:00:00"; break;
        case "2": $hrfin = "02:00:00"; break;
        case "3": $hrfin = "03:00:00"; break;
        case "4": $hrfin = "04:00:00"; break;
        case "5": $hrfin = "05:00:00"; break;
        case "6": $hrfin = "06:00:00"; break;
        case "7": $hrfin = "07:00:00"; break;
        case "8": $hrfin = "08:00:00"; break;
        case "9": $hrfin = "09:00:00"; break;
        case "10": $hrfin = "10:00:00"; break;
        case "11": $hrfin = "11:00:00"; break;
        case "12": $hrfin = "12:00:00"; break;
        case "13": $hrfin = "13:00:00"; break;
        case "14": $hrfin = "14:00:00"; break;
        case "15": $hrfin = "15:00:00"; break;
        case "16": $hrfin = "16:00:00"; break;
        case "17": $hrfin = "17:00:00"; break;
        case "18": $hrfin = "18:00:00"; break;
        case "19": $hrfin = "19:00:00"; break;
        case "20": $hrfin = "20:00:00"; break;
        case "21": $hrfin = "21:00:00"; break;
        case "22": $hrfin = "22:00:00"; break;
        case "23": $hrfin = "23:00:00"; break;
    }


	/*
	$date  = DateTime::createFromFormat('d/m/Y', $dtini);
	$dtini = $date->format('Y-m-d');
	
	$date  = DateTime::createFromFormat('d/m/Y', $dtfin);
	$dtfin = $date->format('Y-m-d');
	*/
	
	$dthrini = $dtini.' '.$hrini;
	$dthrfin = $dtfin.' '.$hrfin;
	
	$coords = array();

	$query = "
		select *, date_format(`date`,'%d/%m/%Y %H:%i') as dthrPosicao 
		  from gprmc 
		 where imei = '".$imei."'
		   and date between '".$dthrini."' and '".$dthrfin."'
  	     group by date
		 order by date desc
		 limit ".$limite."
	";	   
	$res = mysql_query($query);
	
	while($data = mysql_fetch_assoc($res))
	{		 
		if($data['converte'] == 1){
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
		} 
		else 
		{
			$latitudeDecimalDegrees = $data['latitudeDecimalDegrees'];
			$longitudeDecimalDegrees = $data['longitudeDecimalDegrees'];
		}
		$speed       = $data['speed'] * 1.609;
		$address     = utf8_encode($data['address']);		
		$imei        = $data['imei'];
		$dthrPosicao = $data['dthrPosicao'];
		
		$coords[$i]["speed"]                   = $speed;
		$coords[$i]["latitudeDecimalDegrees"]  = $latitudeDecimalDegrees;
		$coords[$i]["longitudeDecimalDegrees"] = $longitudeDecimalDegrees;	
		$coords[$i]["imei"]	                   = $imei;
		$coords[$i]["dthrPosicao"]             = $dthrPosicao;
		
		$i++;
	}
	print json_encode($coords);
}


function execQuery($obj)
{
	$sth = mysql_query($obj);
	
	if (mysql_errno()) {
		header("HTTP/1.1 500 Internal Server Error");
		echo $query.'\n';
		echo mysql_error();
	}
	else
	{
		$rows = array();
		while($r = mysql_fetch_assoc($sth)) {
			$rows[] = $r;
		}
		print json_encode($rows);
	}
}
?>
