<?php include('seguranca.php');
header("Content-Type: text/html; charset=utf-8");

$q=$_GET["imei"];

$con = mysql_connect('cloudservice.cgejdsdl842e.sa-east-1.rds.amazonaws.com', 'gpstracker', 'd1$1793689');
if (!$con) die('Could not connect: ' . mysql_error());

mysql_select_db("tracker", $con);
	
//Checando se está no modo SMS
$res = mysql_query("SELECT 1 FROM bem where imei = '$q' and modo_operacao = 'SMS' and cliente = $cliente");
if (mysql_num_rows($res) != 0) {
	echo "<tr>";
	echo "<td><b>Atenção:</b>Este GPS está operando em modo <i>SMS</i>. Para o rastreamento, ative o modo GPRS. Para os últimos registros, ver em histórico.</td>";
	echo "</tr>";
}
else {
	$loopcount = 0;
	$latAnt = '';
	$longAnt = '';
	$endAnt = '';
	
	$sql="SELECT id, infotext, date, latitudeDecimalDegrees, longitudeDecimalDegrees, latitudeHemisphere, longitudeHemisphere, speed, address, converte
		  FROM gprmc WHERE gpsSignalIndicator in ('F', 'L', 'A') and imei = '". $q ."' GROUP BY latitudeDecimalDegrees ORDER BY id DESC LIMIT 10";
	$result = mysql_query($sql);

	while($data = mysql_fetch_assoc($result)){
		$idRota = $data['id'];
		
		// Calculo das coordenadas. Convertendo coordenadas do modo GPRS para GPS
		$trackerdate = preg_replace("^(..)(..)(..)(..)(..)$^","\\3/\\2/\\1 \\4:\\5",$data['date']);
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
		
		$speed = $data['speed'];
		$infotext = $data['infotext'];

		$address = utf8_encode($data['address']);
		// Só busca o nome de um novo endereço caso as coordenadas sejam diferentes
		if ($latitudeDecimalDegrees != $latAnt && $longitudeDecimalDegrees != $longAnt) {
			//Testa se tem endereço, se nao tiver obtem do google geocode e grava
			if ($address == null or $address == ""){
				/*
				$json = json_decode(file_get_contents("http://maps.google.com/maps/api/geocode/json?sensor=false&latlng=$latitudeDecimalDegrees,$longitudeDecimalDegrees&language=es-ES"));
				if ( isset( $json->status ) && $json->status == 'OK') {
				    $address = $json->results[0]->formatted_address;
					if (!mysql_query("UPDATE gprmc set address = '". utf8_decode($address) ."', date = date where id = $idRota", $con))
					{
						//die('Error: ' . mysql_error());
					}
				}*/
				# Convert the GPS coordinates to a human readable address
				$json = json_decode(file_get_contents("http://maps.googleapis.com/maps/api/geocode/json?latlng=".$latitudeDecimalDegrees.",".$longitudeDecimalDegrees."&sensor=false"));
				if ( isset( $json->status ) && $json->status == 'OK') {
					$address = $json->results[0]->formatted_address;

					$query = "UPDATE gprmc set address = '". utf8_decode(mysql_real_escape_string($address)) ."' WHERE id = $idRota";
					$atualiza = mysql_query($query, $con);
				}
			}
		}
		else $address = $endAnt;
		
		$img = ""; //adiciona imagens de alerta na grid
		switch($infotext){
			case "low battery": $img = "<img src='imagens/battery-low.png' style='margin-left:2px;margin-bottom:-4px; padding:0px;height:16px;width:16px' title='Bat. Fraca' alt='Bat. Fraca' />"; break;
			case "help me": $img = "<img src='imagens/help.png' style='margin-left:2px;margin-bottom:-4px; padding:0px;height:16px;width:16px' title='SOS!' alt='SOS!' />"; break;
			case "acc alarm": $img = "<img src='imagens/ignicao.png' style='margin-left:2px;margin-bottom:-4px; padding:0px;height:16px;width:16px' title='Ignição' alt='Ignição' />"; break;
			default: $img = "";
		}	

		echo "<tr id='rota$idRota'>";
		echo "<td>" . date('d/m/Y', strtotime($data['date'])) . "</td>";
		echo "<td>" . date('H:i:s', strtotime($data['date'])) . "</td>";
		echo "<td>" . $latitudeDecimalDegrees . "</td>";
		echo "<td>" . $longitudeDecimalDegrees . "</td>";
		echo "<td>" . (int)$speed. " Km/h" . " </td>";
		echo "<td> <a href=javascript:getAddressGMaps(".$latitudeDecimalDegrees.",".$longitudeDecimalDegrees.");>Visualizar Endereço</a></td>";
		echo "<td><button type='button' title='Clique para ver no mapa' class='btn btn-default' onclick=\"verNoMapa(" . $latitudeDecimalDegrees . "," . $longitudeDecimalDegrees . ");\"><i class='fa fa-eye'></i></button> $img</td>";
		echo "</tr>";

		$endAnt = $address;	  
		$loopcount++;
	}
	
	if ($loopcount == 0) {
		if ($q == "ALL") {
			echo "<tr>";
			echo "<td>Visualizando toda a frota. Cada cor indica as últimas 20 posições.</td>";
			echo "</tr>";
		} else {
			echo "<tr>";
			echo "<td>Nenhum registro foi encontrado! Aguarde o sinal do GPS.</td>";
			echo "</tr>";
		}
	}
}

mysql_close($con);
?>