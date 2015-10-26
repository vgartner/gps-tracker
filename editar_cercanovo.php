<?php
	if (isset($_GET['imei'])) {
		include_once 'seguranca.php';

		$strImei = $_GET['imei'];
		$strId = $_GET['id'];

		$cnx = mysql_connect("cloudservice.cgejdsdl842e.sa-east-1.rds.amazonaws.com", "gpstracker", "d1$1793689") or die(mysql_error());
		mysql_select_db('tracker', $cnx);

		$resultado = mysql_query("SELECT latitudeDecimalDegrees, latitudeHemisphere, longitudeDecimalDegrees, longitudeHemisphere FROM gprmc WHERE gpsSignalIndicator = 'F' and imei = $strImei order by id desc limit 1,1") or die(mysql_error());	
		if (mysql_num_rows($resultado)) {
			while ($data = mysql_fetch_assoc($resultado)) {
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
			$latitude = $latitudeDecimalDegrees;
			$longitude = $longitudeDecimalDegrees;
		}
		else {
			$latitude = 0;
			$longitude = 0;
		}

		$latCoord = array();
		$lngCoord = array();

		$resultado = mysql_query("SELECT * FROM geo_fence WHERE id = '$strId'") or die(mysql_error());
		while ($linha = mysql_fetch_assoc($resultado)) {
			$id = $linha["id"];
			$imei = $linha["imei"];
			$coordenada = $linha["coordenadas"];
			// $replace = str_replace("|", "), new google.maps.LatLng(", $coordenada);
			$replace = explode('|', $coordenada);
			$count = count($replace);
			for ($i=0; $i < $count; $i++) { 
				$coord = explode(",", $replace[$i]);
				$latCoord[] = $coord[0];
				$lngCoord[] = $coord[1];
			}
		}


		$imprima = array(
			'imei' => $strImei,
			'id' => $strId,
			'latitude' => $latitude,
			'longitude' => $longitude,
			'latCoord' => $latCoord,
			'lngCoord' => $lngCoord,
		);

		echo json_encode($imprima);
	}

?>