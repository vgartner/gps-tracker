<?php include('../seguranca.php');

$imei = $_GET['imei'];
$dataInicio = $_GET['dtIniBkp'];
$dataFinal = $_GET['dtFimBkp'];
$dataInicioHr = $_GET['dtHrIniBkp'];
$dataFinalHr = $_GET['dtHrFimBkp'];

$apagarHistorico = $_GET['CheckboxHistorico'];

$dtIniHistSql = substr($dataInicio, 6, 4) . "-" . substr($dataInicio, 3, 2) . "-" . substr($dataInicio, 0, 2);
$dtFimHistSql = substr($dataFinal, 6, 4) . "-" . substr($dataFinal, 3, 2) . "-" . substr($dataFinal, 0, 2);

$table = 'gprmc';

$outstr = NULL;

header("Content-type: application/vnd.ms-excel");
header("Content-Disposition: attachment;Filename=imei-$imei-$dtIniHistSql-a-$dtFimHistSql.xls");

$conn = mysql_connect("cloudservice.cgejdsdl842e.sa-east-1.rds.amazonaws.com", "gpstracker", "d1$1793689");
mysql_select_db("tracker",$conn);

$style = "style='border: 1px solid #e0e0e0;border-collapse: collapse;'";			

$outstr =  "<table style='$style'>";
$outstr .= "<tr style='$style'>";
	$outstr .= "<td style='$style'><b>Data</b></td>";
	$outstr .= "<td style='$style'><b>Hora</b></td>";
	$outstr .= "<td style='$style'><b>Latitude</b></td>";
	$outstr .= "<td style='$style'><b>Longitude</b></td>";
	$outstr .= "<td style='$style'><b>Velocidade (km/h)</b></td>";
	$outstr .= "<td style='$style' width='300px'><b>Local</b></td>";
	$outstr .= "<td style='$style'><b>Alerta</b></td>";
$outstr .= "</tr>";

// Query database to get data
$rowesult = mysql_query("select id, date, latitudeDecimalDegrees, longitudeDecimalDegrees, latitudeHemisphere, longitudeHemisphere, speed, address
						from $table where imei = $imei and date between '$dtIniHistSql $dataInicioHr:00' and '$dtFimHistSql $dataFinalHr:59' order by id DESC",$conn);
// Write data rows
while ($row = mysql_fetch_assoc($rowesult)) {

		$idRota = $row['id'];
		
		// Calculo das coordenadas. Convertendo coordenadas do modo GPRS para GPS
		$trackerdate = ereg_replace("^(..)(..)(..)(..)(..)$","\\3/\\2/\\1 \\4:\\5",$row['date']);
		strlen($row['latitudeDecimalDegrees']) == 9 && $row['latitudeDecimalDegrees'] = '0'.$row['latitudeDecimalDegrees'];
		$g = substr($row['latitudeDecimalDegrees'],0,3);
		$d = substr($row['latitudeDecimalDegrees'],3);
		$latitudeDecimalDegrees = $g + ($d/60);
		$row['latitudeHemisphere'] == "S" && $latitudeDecimalDegrees = $latitudeDecimalDegrees * -1;
		$latitudeDecimalDegrees = number_format($latitudeDecimalDegrees, 6);

		
		strlen($row['longitudeDecimalDegrees']) == 9 && $row['longitudeDecimalDegrees'] = '0'.$row['longitudeDecimalDegrees'];
		$g = substr($row['longitudeDecimalDegrees'],0,3);
		$d = substr($row['longitudeDecimalDegrees'],3);
		$longitudeDecimalDegrees = $g + ($d/60);
		$row['longitudeHemisphere'] == "W" && $longitudeDecimalDegrees = $longitudeDecimalDegrees * -1;
		$longitudeDecimalDegrees = number_format($longitudeDecimalDegrees, 6);
		

		$speed = $row['speed'] * 1.609;

		$infotext = $row['infotext'];

		$address = utf8_encode($row['address']);
		//Testa se tem endereço, se nao tiver obtem do google geocode e grava
		if ($address == null or $address == "")
		{
			# Convert the GPS coordinates to a human readable address
			$tempstr = "http://maps.google.com/maps/geo?q=$latitudeDecimalDegrees,$longitudeDecimalDegrees&oe=utf-8&sensor=true&key=ABQIAAAAWMue4WPDqeaqR1xeJKhdwBS81-GPhkZXKrOVBuAjatosaJGYEBSHp7MexNAMWt1kfvHLuM5PIU2zrQ&output=csv"; //output = csv, xml, kml, json
			$rev_geo_str = file_get_contents($tempstr);
			$rev_geo_str = ereg_replace("\"","", $rev_geo_str);
			$rev_geo = explode(',', $rev_geo_str);
			$address = $rev_geo[2] .",". $rev_geo[3] ;
		
			if (!mysql_query("UPDATE gprmc set address = '". utf8_decode($address) ."', date = date where id = $idRota", $conn))
			{
				//die('Error: ' . mysql_error());
			}
		}
		
		$latitudeDecimalDegrees = str_replace(".",",",$latitudeDecimalDegrees);
		$longitudeDecimalDegrees = str_replace(".",",",$longitudeDecimalDegrees);

		$alerta = ""; //adiciona imagens de alerta na grid
		switch($infotext)
		{
			case "low battery": $alerta = "Bat. Fraca"; break;
			case "help me": $alerta = "SOS"; break;
			
			default: $alerta = "";
		}


    $outstr .= "<tr style='$style'>";
        $outstr .= "<td style='$style'>" . date('d/m/Y', strtotime($row['date'])) . "</td>";
        $outstr .= "<td style='$style'>" . date('H:i:s', strtotime($row['date'])) . "</td>";
        $outstr .= "<td style='$style'>" . $latitudeDecimalDegrees . "</td>";
        $outstr .= "<td style='$style'>" . $longitudeDecimalDegrees . "</td>";
		$outstr .= "<td style='$style'>" . floor($speed). " </td>";
		$outstr .= "<td style='$style'>" . $address . " </td>";
		$outstr .= "<td style='$style'>" . $alerta . " </td>";
	$outstr .= "</tr>";

}

$outstr .= "</table>";

if ($apagarHistorico == 'true')
{
	mysql_query("delete from $table where imei = $imei and date between '$dtIniHistSql $dataInicioHr:00' and '$dtFimHistSql $dataFinalHr:59' ", $conn);
}

echo $outstr;
mysql_close($conn);

?>
