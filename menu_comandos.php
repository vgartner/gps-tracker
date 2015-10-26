<?php include('seguranca.php');
header("Content-Type: text/html; charset=iso-8859-1");

$imei = $_POST['imei'];
$command = $_POST['command'];
$commandTime = $_POST['commandTime'];
$commandSpeedLimit = $_POST['commandSpeedLimit'];

$cnx = mysql_connect("cloudservice.cgejdsdl842e.sa-east-1.rds.amazonaws.com", "gpstracker", "d1$1793689") or die("Could not connect: " . mysql_error());
mysql_select_db('tracker', $cnx);

$cancelar = (isset($_GET['cancelar'])) ? $_GET['cancelar'] : false ;

$command_path = $_SERVER['DOCUMENT_ROOT']."/sites/1/";

$resultBem = mysql_query("SELECT modelo_rastreador FROM bem WHERE imei = '$imei'");
$dataBem = mysql_fetch_assoc($resultBem);

if(empty($dataBem['modelo_rastreador']) || $dataBem['modelo_rastreador'] == 'tk103'|| $dataBem['modelo_rastreador'] == 'tk104' || $dataBem['modelo_rastreador'] == 'tlt2n'){
	if ($command == ',C,30s') $command = $commandTime;
	elseif ($command == ',H,060') $command = ',H,0' . floor($commandSpeedLimit / 1.609);
	
	#echo "IMEI:$imei Command:$command";
	#echo "$_POST['imei']";
	
	if ($imei != "" and $command != ""){
	/****** DESCOMENTAR EM PRODUÇÃO *****/
	// Utilizando arquivos para guardar o comando
	// your path to command files
	$fn = "$command_path/$imei";
	$fh = fopen($fn, 'w') or die ("Can not create file");
	$tempstr = "**,imei:$imei$command"; 
	fwrite($fh, $tempstr);
	fclose($fh);
	
	// Guardando comandos a ser executado no banco
	$tempstr = "**,imei:$imei$command"; 
	
	if ($command == ',N'){
		//Ativando o modo SMS
		if (!mysql_query("UPDATE bem set modo_operacao = 'SMS' where imei = '$imei' and modo_operacao = 'GPRS'", $cnx))
			die('Error: ' . mysql_error());
	}
	
	if ($command == ',J'){
		$ip = (!empty($_SERVER['REMOTE_ADDR'])) ? $_SERVER['REMOTE_ADDR'] : ((!empty($_ENV['REMOTE_ADDR'])) ? $_ENV['REMOTE_ADDR'] : getenv("REMOTE_ADDR"));
		
		//Guardando log de bloqueio
		if (!mysql_query("INSERT INTO command_log (imei, command, cliente, ip) VALUES ('$imei', '$tempstr', '$cliente', '$ip')", $cnx))
			die('Error: ' . mysql_error());
			
		if (!mysql_query("UPDATE bem set bloqueado = 'S' WHERE imei = '$imei' and cliente = $cliente", $cnx))
			die('Error: ' . mysql_error());
	}	
	
	if ($command == ',K'){		
		if (!mysql_query("UPDATE bem set bloqueado = 'N' WHERE imei = '$imei' and cliente = $cliente", $cnx))
			die('Error: ' . mysql_error());
	}	
	
	if (!mysql_query("INSERT INTO command (imei, command, userid) VALUES ('$imei', '$tempstr', '$cliente')", $cnx)){
		// Se der erro, atualiza o comando existente
		mysql_query("UPDATE command set command = '$tempstr' WHERE imei = '$imei'", $cnx);
		/*
		// echo "<script language=javascript>alert('Comando enviado com sucesso!'); window.location = 'mapa.php?imei=$imei';</script>";
		//die('Error: ' . mysql_error());
		*/
		echo "OK";
	}
	/*
	// echo "<script language=javascript>alert('Comando enviado com sucesso!'); window.location = 'mapa.php?imei=$imei';</script>";
	*/
	echo "OK";
	}
} else if($dataBem['modelo_rastreador'] == 'gt06' || $dataBem['modelo_rastreador'] == 'gt06n' || $dataBem['modelo_rastreador'] == 'crx1'){
	if ($imei != "" and $command != "") {
		$tempstr = ""; 
		if ($command == ',J')
		{
			$ip = (!empty($_SERVER['REMOTE_ADDR'])) ? $_SERVER['REMOTE_ADDR'] : ((!empty($_ENV['REMOTE_ADDR'])) ? $_ENV['REMOTE_ADDR'] : getenv("REMOTE_ADDR"));
			
			$tempstr = "DYD#"; 
			
			//Guardando log de bloqueio
			if (!mysql_query("INSERT INTO command_log (imei, command, cliente, ip) VALUES ('$imei', '$tempstr', '$cliente', '$ip')", $cnx))
				die('Error: ' . mysql_error());
				
			if (!mysql_query("UPDATE bem set bloqueado = 'S' WHERE imei = '$imei' and cliente = $cliente", $cnx))
				die('Error: ' . mysql_error());
			
		}
		
		if ($command == ',K')
		{		
			$tempstr = "HFYD#"; 
				
			if (!mysql_query("UPDATE bem set bloqueado = 'N' WHERE imei = '$imei' and cliente = $cliente", $cnx))
				die('Error: ' . mysql_error());
		}	
		
		if (!mysql_query("INSERT INTO command (imei, command, userid) VALUES ('$imei', '$tempstr', '$userid')", $cnx))
		{
			// Se der erro, atualiza o comando existente
			mysql_query("UPDATE command set command = '$tempstr' WHERE imei = '$imei'", $cnx);
			//die('Error: ' . mysql_error());
			echo "OK";
		}
		
		$fn = "$command_path/$imei";
		$fh = fopen($fn, 'w') or die ("Can not create file");
		fwrite($fh, $tempstr);
		fclose($fh);
		
		echo "OK";
	}
}
mysql_close($cnx);
//Cancelando o comando enviado
if ($cancelar != "") {
	$cnx = mysql_connect("cloudservice.cgejdsdl842e.sa-east-1.rds.amazonaws.com", "gpstracker", "d1$1793689") or die("Could not connect: " . mysql_error());
	mysql_select_db('tracker', $cnx);

	if (!mysql_query("DELETE FROM command WHERE imei = '$cancelar'", $cnx)){
		die('Error: ' . mysql_error());
	}
		
	mysql_close($cnx);	
}
?>