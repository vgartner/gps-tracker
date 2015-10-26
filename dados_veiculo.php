<?php
	if (isset($_GET['filtro'])) {
		$imei = $_GET['filtro'];
		include_once 'seguranca.php';
		include_once 'usuario/config.php';

		$cnx = mysql_connect($DB_SERVER, $DB_USER, $DB_PASS) or die(mysql_error());
		mysql_select_db($DB_NAME, $cnx);

		if (strstr($imei, 'grupo_')) {
			$grupo = explode('_', $imei);
			$dadosBem = mysql_query("SELECT b.name, b.operadora, b.hodometro, b.modelo_rastreador, b.status_sinal, b.bloqueado, b.tipo, b.identificacao, b.apelido, b.marca, b.ligado, la.latitudeDecimalDegrees, la.longitudeDecimalDegrees, la.longitudeHemisphere, la.latitudeHemisphere, la.converte, la.speed FROM bem b LEFT JOIN loc_atual la ON la.imei = b.imei JOIN grupo_bem gb ON gb.bem = b.id JOIN grupo g ON g.id = gb.grupo WHERE g.id = $grupo[1]");
		}
		else {
			$dadosBem = mysql_query("SELECT b.name, b.operadora, b.hodometro, b.modelo_rastreador, b.status_sinal, b.bloqueado, b.tipo, b.identificacao, b.apelido, b.marca, b.ligado, la.latitudeDecimalDegrees, la.longitudeDecimalDegrees, la.longitudeHemisphere, la.latitudeHemisphere, la.converte, la.speed FROM bem b LEFT JOIN loc_atual la ON la.imei = b.imei WHERE b.imei = $imei");
		}
		$strInfo = '[';

		while ($row = mysql_fetch_assoc($dadosBem)) {
		    if($row['converte'] == 1){
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
			
				//$longitudeDecimalDegrees = $longitudeDecimalDegrees * -1;
			} else {
				$latitudeDecimalDegrees = $row['latitudeDecimalDegrees'];
				$longitudeDecimalDegrees = $row['longitudeDecimalDegrees'];
			}

			$strInfo .= '{';
			$strInfo .= "'imei':'".$imei."',";
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

	if (isset($_GET['posicao'])) {
		include_once 'seguranca.php';
		include_once 'usuario/config.php';
		$posicao = $_GET['posicao'];

		$cnx = mysql_connect($DB_SERVER, $DB_USER, $DB_PASS) or die(mysql_error());
		mysql_select_db($DB_NAME, $cnx);

		if (strstr($posicao, 'grupo_')) {
			$grupo = explode('_', $posicao);
			$novaPos = mysql_query("SELECT la.latitudeDecimalDegrees, la.longitudeDecimalDegrees, la.longitudeHemisphere, la.latitudeHemisphere, la.converte, b.status_sinal, b.bloqueado, b.tipo, b.operadora, b.ligado, b.hodometro FROM loc_atual la LEFT JOIN bem b ON la.imei = b.imei JOIN grupo_bem gb ON gb.bem = b.id JOIN grupo g ON g.id = gb.grupo WHERE g.id = $grupo[1]");
		}
		else {
			$novaPos = mysql_query("SELECT la.latitudeDecimalDegrees, la.latitudeHemisphere, la.longitudeDecimalDegrees, la.longitudeHemisphere, la.converte, b.status_sinal, b.bloqueado, b.tipo, b.ligado, b.operadora, b.hodometro FROM loc_atual la INNER JOIN bem b ON b.imei = la.imei WHERE la.imei = $posicao");
		}

		$strInfo = "[";
		while ($row = mysql_fetch_assoc($novaPos)) {
	        if($row['converte'] == 1){
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
	    	
	    		//$longitudeDecimalDegrees = $longitudeDecimalDegrees * -1;
	    	} else {
	    		$latitudeDecimalDegrees = $row['latitudeDecimalDegrees'];
	    		$longitudeDecimalDegrees = $row['longitudeDecimalDegrees'];
	    	}

			$strInfo .= "{";
			$strInfo .= "'latitude':'".$latitudeDecimalDegrees."',";
			$strInfo .= "'longitude':'".$longitudeDecimalDegrees."',";
			$strInfo .= "'sinal':'".$row['status_sinal']."',";
			$strInfo .= "'block':'".$row['bloqueado']."',";
			$strInfo .= "'tipo':'".$row['tipo']."',";
			$strInfo .= "'ligado':'".$row['ligado']."'";
			$strInfo .= "'velocidade':'".@$row['speed']."',";
			$strInfo .= "'hodometro':'".$row['hodometro']."',";
			$strInfo .= "'modelo':'".@$row['modelo']."',";
			$strInfo .= "'chip':'".$row['operadora']."',";
			$strInfo .= "},";
		}
		$strInfo .= "]";
		$strInfo = str_replace(',]', ']', $strInfo);
		echo $strInfo;
	}
?>