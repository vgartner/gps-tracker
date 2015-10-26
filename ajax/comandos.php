<?php
	if (isset($_GET['cod'], $_GET['acao'])) {
		include_once '../seguranca.php';
		$imei				= $_GET['cod'];
		$command			= $_GET['acao'];
		$command_path 		= $_SERVER['DOCUMENT_ROOT']."/sites/1";
		$commandSpeedLimit 	= $_GET['speed'];
		$commandTime		= $_GET['time'];

		error_log('|||||||||||||||||||||||'.$command_path);

		$cnx = mysql_connect("cloudservice.cgejdsdl842e.sa-east-1.rds.amazonaws.com", "gpstracker", "d1$1793689") or die("Could not connect: " . mysql_error());
		mysql_select_db('tracker', $cnx);

		$resultBem = mysql_query("select modelo_rastreador from bem where imei = '$imei'");
		$dataBem = mysql_fetch_assoc($resultBem);

		if(empty($dataBem['modelo_rastreador']) || $dataBem['modelo_rastreador'] == 'tk103'|| $dataBem['modelo_rastreador'] == 'tk104'){
			
			
			error_reporting(E_ALL);
			// Utilizando arquivos para guardar o comando
			//your path to command files
			$fn = $command_path.'/'.$imei;
			echo $fn;
			$fh = fopen($fn, 'w+') or die ("Can not create file");
			$tempstr = "**,imei:$imei$command"; 
			fwrite($fh, $tempstr);
			fclose($fh);
			// Guardando comandos a ser executado no banco
			$tempstr = "**,imei:$imei$command";

			// COMANDO PARA BLOQUEAR COMBUSTÍVEL
			if ($command == ',J') {
				$ip = (!empty($_SERVER['REMOTE_ADDR'])) ? $_SERVER['REMOTE_ADDR'] : ((!empty($_ENV['REMOTE_ADDR'])) ? $_ENV['REMOTE_ADDR'] : getenv("REMOTE_ADDR"));
				//Guardando log de bloqueio
				if (!mysql_query("INSERT INTO command_log (imei, command, cliente, ip) VALUES ('$imei', '$tempstr', '$cliente', '$ip')", $cnx)) die('Error: ' . mysql_error());
				if (!mysql_query("UPDATE bem set bloqueado = 'S' WHERE imei = '$imei'", $cnx)) die('Error: ' . mysql_error());
			}

			// COMANDO PARA LIBERAR COMBUSTÍVEL
			if ($command == ',K') {
				if (!mysql_query("UPDATE bem set bloqueado = 'N' WHERE imei = '$imei'", $cnx)) die('Error: ' . mysql_error());
			}

			// COMANDO DE VELOCIDADE
			if ($command == ',H,060') $command = ',H,0' . floor($commandSpeedLimit / 1.609);

			// COMANDO RASTREAR A CADA
			if ($command == ',C,30s') $command = $commandTime;

			if (!mysql_query("INSERT INTO command (imei, command, userid) VALUES ('$imei', '$tempstr', '$userid')", $cnx)){
				// Se der erro, atualiza o comando existente
				mysql_query("UPDATE command set command = '$tempstr' WHERE imei = '$imei'", $cnx);
				//echo "<script language=javascript>alert('Comando enviado com sucesso!'); window.location = 'mapa.php?imei=$imei';</script>";
				//die('Error: ' . mysql_error());
			}
			echo json_encode(true);

		}//FIM Modelo TK
		elseif ($dataBem['modelo_rastreador'] == 'gt06' || $dataBem['modelo_rastreador'] == 'gt06n' || $dataBem['modelo_rastreador'] == 'crx1') {
			
			$tempstr = "";
			// COMANDO PARA BLOQUEAR COMBUSTÍVEL
			if ($command == ',J') {
				$ip = (!empty($_SERVER['REMOTE_ADDR'])) ? $_SERVER['REMOTE_ADDR'] : ((!empty($_ENV['REMOTE_ADDR'])) ? $_ENV['REMOTE_ADDR'] : getenv("REMOTE_ADDR"));
				$tempstr = "DYD#"; 
				//Guardando log de bloqueio
				if (!mysql_query("INSERT INTO command_log (imei, command, cliente, ip) VALUES ('$imei', '$tempstr', '$cliente', '$ip')", $cnx)) die('Error: ' . mysql_error());
				if (!mysql_query("UPDATE bem set bloqueado = 'S' WHERE imei = '$imei'", $cnx)) die('Error: ' . mysql_error());
			}

			// COMANDO PARA LIBERAR COMBUSTÍVEL
			if ($command == ',K') {
				$tempstr = "HFYD#";
				if (!mysql_query("UPDATE bem set bloqueado = 'N' WHERE imei = '$imei'", $cnx)) die('Error: ' . mysql_error());
			}

			if ($command == 'FORCALOC') {
				$tempstr = "DWXX#";
			}

			if (!mysql_query("INSERT INTO command (imei, command, userid) VALUES ('$imei', '$tempstr', '$userid')", $cnx)) {
				// Se der erro, atualiza o comando existente
				mysql_query("UPDATE command set command = '$tempstr' WHERE imei = '$imei'", $cnx);
			}

			$fn = $command_path.$imei;
			$fh = fopen($fn, 'w') or die ("Can not create file");
			//$fh = fopen($fn.'_teste', 'w');
			fwrite($fh, $tempstr);
			fclose($fh);
			
			echo json_encode(true);

		}//FIM Modelo GT
	}
?>