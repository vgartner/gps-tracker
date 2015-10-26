#!/usr/bin/php -q
<?php
//waiting for system startup
//crontab: @reboot php -q /var/www/server/tracker.php
//sleep (180);

/**
  * Listens for requests and forks on each connection
  */

$tipoLog = "arquivo"; // tela //debug log, escreve na tela ou no arquivo de log.

$fh = null;
$remip = null;
$remport = null;
$imei = '';

/*if ($tipoLog == "arquivo") {
	//Criando arquivo de log
	$fn = ROOT_URL."/sites/1/logs/" . "Log_". date("dmyhis") .".log";
	$fh = fopen($fn, 'w') or die ("Can not create file");
	$tempstr = "Log Inicio".chr(13).chr(10); 
	fwrite($fh, $tempstr);
}*/

function abrirArquivoLog($imeiLog) {
	GLOBAL $fh;
	
	//$fn = ".".dirname(__FILE__)."/sites/1/logs/Log_". trim($imeiLog) .".log";
	$fn = "./var/www/sites/1/logs/Log_". trim($imeiLog) .".log";
	$fn = trim($fn);
	$fh = fopen($fn, 'a') or die ("Can not create file");
	$tempstr = "Log Inicio".chr(13).chr(10); 
	fwrite($fh, $tempstr);	
}

function fecharArquivoLog() {
	GLOBAL $fh;
	if ($fh != null)
		fclose($fh);
}

function printLog( $fh, $mensagem ) {
	GLOBAL $tipoLog;
	GLOBAL $fh;
	
    if ($tipoLog == "arquivo") {
		//escreve no arquivo
		if ($fh != null)
			fwrite($fh, $mensagem.chr(13).chr(10));
    } else {
		//escreve na tela
		echo $mensagem."<br />";
    }
}

//$cnx = mysql_connect("cloudservice.cgejdsdl842e.sa-east-1.rds.amazonaws.com", "gpstracker", "d1$1793689") 
//						  or die("Could not connect: " . mysql_error());
//mysql_select_db('tracker', $cnx);

//$res = mysql_query("select valor from preferencias where nome = 'host_apn'", $cnx);
//$dataIp = mysql_fetch_assoc($res);
//$res = mysql_query("select valor from preferencias where nome = 'port_apn'", $cnx);
//$dataPorta = mysql_fetch_assoc($res);
//$dataPorta = mysql_fetch_assoc($res);
//$res = mysql_query("select valor from preferencias where nome = 'email_alertas'", $cnx);
//$dataEmail = mysql_fetch_assoc($res);

// IP Local
//$ip = $dataIp['valor'];
$ip = '107.170.145.137';
// Port
//$port = $dataPorta['valor'];
$port = 70770;
// Path to look for files with commands to send
$command_path = "./var/www/sites/1/";
//$from_email = $dataEmail['valor'];
$from_email = 'teste@provedor.com.br';

//mysql_close($cnx);

$__server_listening = true;

error_reporting(E_ALL);
set_time_limit(0);
ob_implicit_flush();
declare(ticks = 1);
ini_set('sendmail_from', $from_email);

//printLog($fh, "become_daemon() in");
become_daemon();
//printLog($fh, "become_daemon() out");

/* nobody/nogroup, change to your host's uid/gid of the non-priv user 

** Comment by Andrew - I could not get this to work, i commented it out
   the code still works fine but mine does not run as a priv user anyway....
   uncommented for completeness
*/
//change_identity(65534, 65534);

/* handle signals */
pcntl_signal(SIGTERM, 'sig_handler');
pcntl_signal(SIGINT, 'sig_handler');
pcntl_signal(SIGCHLD, 'sig_handler');

//printLog($fh, "pcntl_signal ok");

/* change this to your own host / port */
//printLog($fh, "server_loop in");
server_loop($ip, $port);

//Finalizando arquivo
//fclose($fh);

/**
  * Change the identity to a non-priv user
  */
function change_identity( $uid, $gid ) {
    if( !posix_setgid( $gid ) ) {
        print "Unable to setgid to " . $gid . "!\n";
        exit;
    }

    if( !posix_setuid( $uid ) ) {
        print "Unable to setuid to " . $uid . "!\n";
        exit;
    }
}

/**
  * Creates a server socket and listens for incoming client connections
  * @param string $address The address to listen on
  * @param int $port The port to listen on
  */
function server_loop($address, $port) {
    GLOBAL $fh;
    GLOBAL $__server_listening;
	
	printLog($fh, "server_looping...");

    if(($sock = socket_create(AF_INET, SOCK_STREAM, 0)) < 0) {
		printLog($fh, "failed to create socket: ".socket_strerror($sock));
        exit();
    }

	if(($ret = socket_bind($sock, $address, $port)) < 0) {
		printLog($fh, "failed to bind socket: ".socket_strerror($ret));
		exit();
	}

	if( ( $ret = socket_listen( $sock, 0 ) ) < 0 ) {
		printLog($fh, "failed to listen to socket: ".socket_strerror($ret));
		exit();
	}

	socket_set_nonblock($sock);

	printLog($fh, "waiting for clients to connect...");

	while ($__server_listening) {
		$connection = @socket_accept($sock);
		if ($connection === false) {
			usleep(100);
		} elseif ($connection > 0) {
			handle_client($sock, $connection);
		} else {
			printLog($fh, "error: ".socket_strerror($connection));
			die;
		}
	}
}

/**
* Signal handler
*/
function sig_handler($sig) {
	switch($sig) {
		case SIGTERM:
		case SIGINT:
			//exit();
			break;

		case SIGCHLD:
			pcntl_waitpid(-1, $status);
		break;
	}
}

$firstInteraction = false;

/**
* Handle a new client connection
*/
function handle_client($ssock, $csock) {
	GLOBAL $__server_listening;
	GLOBAL $fh;
	GLOBAL $firstInteraction;
	GLOBAL $remip;
	GLOBAL $remport;

	$pid = pcntl_fork();

	if ($pid == -1) {
		/* fork failed */
		//printLog($fh, "fork failure!");
		die;
	} elseif ($pid == 0) {
		/* child process */
		$__server_listening = false;
		socket_getpeername($csock, $remip, $remport);
		
		//printLog($fh, date("d-m-y h:i:sa") . " Connection from $remip:$remport");

		$firstInteraction = true;
		
		socket_close($ssock);
		interact($csock);
		socket_close($csock);
		
		printLog($fh, date("d-m-y h:i:sa") . " Connection to $remip:$remport closed");
		
		fecharArquivoLog();
		
	} else {
		socket_close($csock);
	}
}

function interact($socket) {
	GLOBAL $fh;
	GLOBAL $command_path;
	GLOBAL $firstInteraction;
	GLOBAL $remip;
	GLOBAL $remport;	
	GLOBAL $imei;	

	$loopcount = 0;
	$conn_imei = "";
	/* TALK TO YOUR CLIENT */
	$rec = "";
	// Variavel que indica se comando est� em banco ou arquivo.
	$tipoComando = "banco"; //"arquivo";
	
	//Checando o protocolo
	$isGIMEI = false;
	$isGPRMC = false;
	
	$send_cmd = "";
	
	$last_status = "";

	# Read the socket but don't wait for data..
	while (@socket_recv($socket, $rec, 2048, 0x40) !== 0) {
	  
	  //if($last_status != ''){
	//	  socket_send($socket, $last_status, strlen($last_status), 0);
	 // }
	  
	  # If we know the imei of the phone and there is a pending command send it.
	  
	    if ($conn_imei != "") {
			if ($tipoComando == "arquivo" and file_exists("$command_path/$conn_imei")) {
				$send_cmd = file_get_contents("$command_path/$conn_imei");
				
				/**/
				$sendcmd = trataCommand($send_cmd, $conn_imei);
				socket_send($socket, $sendcmd, strlen($sendcmd), 0);
				
				unlink("$command_path/$conn_imei");
				printLog($fh, "Arquivo de comandos apagado: " . $sendcmd . " imei: " . $conn_imei);
			} else {
				if ($tipoComando == "banco" and file_exists("$command_path/$conn_imei")) {
					//Conecta e pega o comando pendente
					$cnx = mysql_connect("cloudservice.cgejdsdl842e.sa-east-1.rds.amazonaws.com", "gpstracker", "d1$1793689") 
					  or die("Could not connect: " . mysql_error());
					mysql_select_db('tracker', $cnx);
					$res = mysql_query("SELECT c.command FROM command c WHERE c.imei = '$conn_imei' ORDER BY date DESC LIMIT 1", $cnx);
					$sendcmd = '';
					while($data = mysql_fetch_assoc($res)) {
						
						$sendcmd = trataCommand($data['command'], $conn_imei);
					}
					// Deletando comando
					//mysql_query("DELETE FROM command WHERE imei = $conn_imei");
					
					socket_send($socket, $sendcmd, strlen($sendcmd), 0);
					
					mysql_close($cnx);
					
					unlink("$command_path/$conn_imei");
					
					printLog($fh, "Comandos do arquivo apagado: " . $sendcmd . " imei: " . $conn_imei);
				} else {
					//Se nao tiver comando na fila e for a primeira iteracao, obtem o ultimo comando v�lido enviado
					if ($firstInteraction == true) {
						sleep (1);
						$firstInteraction = false;
					}
				}
			}
		}
		
		if(file_exists("$command_path/$conn_imei")){
			$send_cmd = file_get_contents("$command_path/$conn_imei");
			if($send_cmd == 'shutdown'){
				unlink("$command_path/$conn_imei");
				socket_shutdown($socket, 2);
			}
		}
		# Some pacing to ensure we don't split any incoming data.
		sleep (1);

		# Timeout the socket if it's not talking...
		# Prevents duplicate connections, confusing the send commands
		$loopcount++;
		if ($loopcount > 120) return;

		#remove any whitespace from ends of string.

		if ($rec != "") {
			
			$isH08 = false;
			
			$arCommands = explode(',',$rec);
			
			if(substr($arCommands[0],1,2) == 'HQ'){
				$isH08 = true;
			}
			/*
			0 = *HQ,
			1 = 353505821000973,
			2 = V1,
			3 = 154850,
			4 = V,
			5 = 2251.0935,
			6 = S,
			7 = 04320.5078,
			8 = W,
			9 = 0.00,
			10 =0,
			11 = 200214,
			12 = FFFFFFFF,
			13 = 2d4,
			14 = 14,
			15 = 1401,
			16 = 2384#
			*/
			if($isH08){
				
				$imei = $arCommands[1];
				$conn_imei = $imei;
				
				abrirArquivoLog($imei);
				
				printLog($fh, date("d-m-y h:i:sa") . " Imei: $imei Got: $rec");
				
				$latitudeDecimalDegrees = $arCommands[5];
				$latitudeHemisphere = $arCommands[6];
				$longitudeDecimalDegrees = $arCommands[7];
				$longitudeHemisphere = $arCommands[8];
				$speed = $arCommands[9];
				
				$alarms = $arCommands[12];
				$ligado = 'N';
				
				$alarms2 = substr($alarms,2,1);
				$alarms2 = hexdec($alarms2);
				while(strlen($alarms2) < 4) $alarms2 = '0'.$alarms2;
				
				$ligado = $alarms2[2] == '0' ? 'S' : 'N';
				
				$dados = array('S', 
						$latitudeDecimalDegrees, 
						$longitudeDecimalDegrees, 
						$latitudeHemisphere, 
						$longitudeHemisphere, 
						$speed, 
						$imei,
						date('Y-m-d'),
						$alarm,
						1);
		
				tratarDados($dados);
				
			}
		}
		$rec = "";
	} //while

} //fim interact

/**
  * Become a daemon by forking and closing the parent
  */
function become_daemon() {
    GLOBAL $fh;

	//printLog($fh, "pcntl_fork() in");
    $pid = pcntl_fork();
	//printLog($fh, "pcntl_fork() out");

    if ($pid == -1) {
        /* fork failed */
		//printLog($fh, "fork failure!");
        exit();
    } elseif ($pid) {
		//printLog($fh, "pid: " . $pid);
        /* close the parent */
        exit();
    } else {
        /* child becomes our daemon */
        posix_setsid();
        chdir('/');
        umask(0);
        return posix_getpid();
    }

	//printLog($fh, "become_daemon() fim");
}

function gprsToGps($cord, $hemisphere){
	$novaCord = 0;
	strlen($cord) == 9 && $cord = '0'.$cord;
	$g = substr($cord,0,3);
	$d = substr($cord,3);
	$novaCord = $g + ($d/60);
	if($hemisphere == "S")
		$hemisphere == "S" && $novaCord = $novaCord * -1;
	if($hemisphere == "W")
		$hemisphere == "W" && $novaCord = $novaCord * -1;
	return $novaCord;
}

function sendSMS($contato, $mensagem, $remetente){
	$cnx = mysql_connect("cloudservice.cgejdsdl842e.sa-east-1.rds.amazonaws.com", "gpstracker", "d1$1793689") 
						  or die("Could not connect: " . mysql_error());
						mysql_select_db('tracker', $cnx);
	$res = mysql_query("select valor from preferencias where nome = 'url_sms'", $cnx);
	$data = mysql_fetch_assoc($res);
	$url = $data['valor'];
	
	$res = mysql_query("select valor from preferencias where nome = 'usuario_sms'", $cnx);
	$data = mysql_fetch_assoc($res);
	$usuario = $data['valor'];
	
	$res = mysql_query("select valor from preferencias where nome = 'senha_sms'", $cnx);
	$data = mysql_fetch_assoc($res);
	$senha = $data['valor'];
	
	$res = mysql_query("select valor from preferencias where nome = 'de_sms'", $cnx);
	$data = mysql_fetch_assoc($res);
	$de = $data['valor'];
	file_get_contents($url."usr=".$usuario."&pwd=".$senha."&number=55".$contato."&sender=".$de."&msg=$mensagem");
}

function distance($lat1, $lon1, $lat2, $lon2, $unit) {

  $theta = $lon1 - $lon2;
  $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
  $dist = acos($dist);
  $dist = rad2deg($dist);
  $miles = $dist * 60 * 1.1515;
  $unit = strtoupper($unit);

  if ($unit == "K") {
    return ($miles * 1.609344);
  } else if ($unit == "N") {
      return ($miles * 0.8684);
    } else {
        return $miles;
      }
}

function strToHex($string){
    $hex = '';
    for ($i=0; $i<strlen($string); $i++){
        $ord = ord($string[$i]);
        $hexCode = dechex($ord);
        $hex .= substr('0'.$hexCode, -2);
    }
    return strToUpper($hex);
}
function hexToStr($hex){
    $string='';
    for ($i=0; $i < strlen($hex)-1; $i+=2){
        $string .= chr(hexdec($hex[$i].$hex[$i+1]));
    }
    return $string;
}

function hex2str($hex) {
    for($i=0;$i<strlen($hex);$i+=2) $str .= chr(hexdec(substr($hex,$i,2)));
    return $str;
}

function bin2string($bin) {
    $res = "";
    for($p=31; $p >= 0; $p--) {
      $res .= ($bin & (1 << $p)) ? "1" : "0";
    }
    return $res;
} 
function teste($input){
	$output = '';
	foreach( explode( "\n", $input) as $line) {
		if( preg_match( '/(?:[a-f0-9]{2}\s){1,16}/i', $line, $matches)) {
			$output .= ' ' . $matches[0];
		}
	}
	return $output;
}

function ascii2hex($ascii) {
$hex = '';
for ($i = 0; $i < strlen($ascii); $i++) {
$byte = strtoupper(dechex(ord($ascii{$i})));
$byte = str_repeat('0', 2 - strlen($byte)).$byte;
$hex.=$byte." ";
}
return $hex;
}


function hexStringToString($hex) {
    return pack('H*', $hex);
}

function hex_dump($data, $newline="\n")
{
  static $from = '';
  static $to = '';

  static $width = 50; # number of bytes per line

  static $pad = '.'; # padding for non-visible characters

  if ($from==='')
  {
    for ($i=0; $i<=0xFF; $i++)
    {
      $from .= chr($i);
      $to .= ($i >= 0x20 && $i <= 0x7E) ? chr($i) : $pad;
    }
  }

  $hex = str_split(bin2hex($data), $width*2);
  $chars = str_split(strtr($data, $from, $to), $width);

  $offset = 0;
  $retorno = '';
  foreach ($hex as $i => $line)
  {
    $retorno .= implode(' ', str_split($line,2));
    $offset += $width;
  }
  return $retorno;
  //sprintf($retorno);
}

function crcx25($data) {
   //i explode() $data and make $content array
   $content = explode(' ',$data) ;
   //i count() the array to get data length
   $len = count($content) ;
   $n = 0 ;
   
   $crc = 0xFFFF;   
   while ($len > 0)
   {
      $crc ^= hexdec($content[$n]);
      for ($i=0; $i<8; $i++) {
         if ($crc & 1) $crc = ($crc >> 1) ^ 0x8408;
         else $crc >>= 1;
      }
      $n++ ;
      $len-- ;
   }
   
   return(~$crc);
}

function tratarDados($dados){
	$con = mysql_connect("cloudservice.cgejdsdl842e.sa-east-1.rds.amazonaws.com", "gpstracker", "d1$1793689");
	if($con !== false){
		mysql_select_db('tracker', $con);
			
		$gpsSignalIndicator = 'F';
		$latitudeDecimalDegrees = $dados[1];
		$longitudeDecimalDegrees = $dados[2];
		$latitudeHemisphere = $dados[3];
		$longitudeHemisphere = $dados[4];
		$speed = $dados[5];
		$imei = $dados[6];
		$satelliteFixStatus = 'A';
		$phone = '';
		$infotext = $dados[8];
		$converte = $dados[9];
		$dataBem = null;
		$dataCliente = null;
		$ligado = count($dados > 9) ? $dados[9] : '';
		
		$resBem = mysql_query("select id, cliente, envia_sms, name, alerta_hodometro, alerta_hodometro_saldo from bem where imei = '$imei'", $con);

		$dataBem = mysql_fetch_assoc($resBem);

		if($resBem !== false){

			$resCliente = mysql_query("select id, celular, dt_ultm_sms, envia_sms, sms_acada, hour(timediff(now(), dt_ultm_sms)) horas, minute(timediff(now(), dt_ultm_sms)) minutos, nome from cliente where id = ".$dataBem['cliente'], $con);
			if($resCliente !== false){
				$dataCliente = mysql_fetch_assoc($resCliente);

				$texto_sms_localiza = "";
				$texto_sms_alerta_hodometro = "";
				$texto_sms_alerta = "";
				
				$result = mysql_query("select * from preferencias", $con);
				if(mysql_num_rows($result) > 0){
					while ($dataPref = mysql_fetch_assoc($result)){
						if($dataPref['nome'] == 'texto_sms_localiza')
							$texto_sms_localiza = $dataPref['valor'];
					
						if($dataPref['nome'] == 'texto_sms_alerta_hodometro')
							$texto_sms_alerta_hodometro = $dataPref['valor'];
						
						if($dataPref['nome'] == 'texto_sms_alerta')
							$texto_sms_alerta = $dataPref['valor'];
					}
				}
				# Write it to the database...
				if ($gpsSignalIndicator != 'L') {
					$movimento = '';
					if($speed > 0)
						$movimento = 'S';
					else
						$movimento = 'N';
				
					$gpsLat = gprsToGps($latitudeDecimalDegrees, $latitudeHemisphere);
					$gpsLon = gprsToGps($longitudeDecimalDegrees, $longitudeHemisphere);
					$gpsLatAnt = 0;
					$gpsLatHemAnt = '';
					$gpsLonAnt = 0;
					$gpsLonHemAnt = '';
					$alertaACadaSaldo = 0;
				
					$resLocAtual = mysql_query("select id, latitudeDecimalDegrees, latitudeHemisphere, longitudeDecimalDegrees, longitudeHemisphere from loc_atual where imei = '$imei' limit 1", $con);
					$numRows = mysql_num_rows($resLocAtual);
				
					if($numRows == 0){
						mysql_query("INSERT INTO loc_atual (date, imei, phone, satelliteFixStatus, latitudeDecimalDegrees, latitudeHemisphere, longitudeDecimalDegrees, longitudeHemisphere, speed, infotext, gpsSignalIndicator, converte, ligado) VALUES (now(), '$imei', '$phone', '$satelliteFixStatus', '$latitudeDecimalDegrees', '$latitudeHemisphere', '$longitudeDecimalDegrees', '$longitudeHemisphere', '$speed', '$infotext', '$gpsSignalIndicator', $converte, '$ligado')", $con);
					} else {
						mysql_query("UPDATE loc_atual set date = now(), phone = '$phone', satelliteFixStatus = '$satelliteFixStatus', latitudeDecimalDegrees = '$latitudeDecimalDegrees', latitudeHemisphere = '$latitudeHemisphere', longitudeDecimalDegrees = '$longitudeDecimalDegrees', longitudeHemisphere = '$longitudeHemisphere', speed = '$speed', infotext = '$infotext', gpsSignalIndicator = '$gpsSignalIndicator', converte = $converte, ligado = '$ligado' where imei = '$imei'", $con);
					}
				
					$distance = 0;
					try{
						$bemId = $dataBem[id];
						$countGeoDistance = mysql_query("select bem from geo_distance where bem = $bemId", $con);
						if($countGeoDistance === false || mysql_num_rows($countGeoDistance) == 0) {
							mysql_query("insert into geo_distance (bem, tipo) values($bemId, 'I')", $con);
							mysql_query("insert into geo_distance (bem, tipo) values($bemId, 'F')", $con);
						}
				
						/*envio de sms*/
						if($dataCliente['envia_sms'] == 'S' && $dataBem['envia_sms'] == 'S' && !empty($dataCliente['celular']) && !empty($dataCliente['sms_acada'])){
							if(empty($dataCliente['dt_ultm_sms'])){
								mysql_query("update cliente set dt_ultm_sms = convert_tz(now(), 'GMT', 'Brazil/East') where id = $dataCliente[id]",$con);
							} else {
								$horas = $dataCliente['horas'];
								$minutos = $dataCliente['minutos'];
								if(!empty($horas))
									$horas = $horas * 60;
								$tempoTotal = $horas+$minutos;
								if($tempoTotal > $dataCliente['sms_acada']){
									$json = json_decode(file_get_contents("http://maps.google.com/maps/api/geocode/json?sensor=false&latlng=$gpsLat,$gpsLon&language=es-ES"));
									if ( isset( $json->status ) && $json->status == 'OK' && isset($json->results[0]->formatted_address)) {
										$address = $json->results[0]->formatted_address;
										$address = utf8_decode($address);
										$aDataCliente = split(' ', $dataCliente['nome']);
										$msg = $texto_sms_localiza;
										$msg = str_replace("#CLIENTE", $aDataCliente[0], $msg);
										$msg = str_replace("#VEICULO", $dataBem['name'], $msg);
										$msg = str_replace("#LOCALIZACAO", $address, $msg);
										$msg = str_replace(' ', '+', $msg);
										sendSMS($dataCliente['celular'], $msg, '');
										if($retorno < 0)
											mysql_query("insert into controle(texto) values('envio de sms retorno: $retorno')",$con);
										else 
											mysql_query("update cliente set dt_ultm_sms = convert_tz(now(), 'GMT', 'Brazil/East') where id = $dataCliente[id]",$con);
									}
								}
							}
						}
				
						if($movimento == 'S'){
							$resGeoDistance = mysql_query("select parou from geo_distance where bem = $bemId and tipo = 'I'", $con);
							if($resGeoDistance !== false) {
								$dataGeoDistance = mysql_fetch_assoc($resGeoDistance);
								if($dataGeoDistance[parou] == 'S' || empty($dataGeoDistance[parou])){
									mysql_query("update geo_distance set latitudeDecimalDegrees = '$latitudeDecimalDegrees', latitudeHemisphere = '$latitudeHemisphere', longitudeDecimalDegrees = '$longitudeDecimalDegrees', longitudeHemisphere = '$longitudeHemisphere', parou = 'N' where bem =  $bemId and tipo = 'I'", $con);
								}
							}
						} else {
							$resGeoDistance = mysql_query("select latitudeDecimalDegrees, latitudeHemisphere, longitudeDecimalDegrees, longitudeHemisphere from geo_distance where bem = $bemId and tipo = 'I'", $con);
							if(mysql_num_rows($resGeoDistance) > 0){
								$update = mysql_query("update geo_distance set latitudeDecimalDegrees = '$latitudeDecimalDegrees', latitudeHemisphere = '$latitudeHemisphere', longitudeDecimalDegrees = '$longitudeDecimalDegrees', longitudeHemisphere = '$longitudeHemisphere', parou = 'S' where bem =  $bemId and tipo = 'I'", $con);
								$dataGeoDistance = mysql_fetch_assoc($resGeoDistance);
								$gpsLatAnt = gprsToGps($dataGeoDistance[latitudeDecimalDegrees], $dataGeoDistance[latitudeHemisphere]);
								$gpsLonAnt = gprsToGps($dataGeoDistance[longitudeDecimalDegrees], $dataGeoDistance[longitudeHemisphere]);
								if($gpsLatAnt != $gpsLat) {
									if($gpsLatAnt != 0 && $gpsLonAnt != 0){
										/*
										$json = json_decode(file_get_contents("http://maps.googleapis.com/maps/api/distancematrix/json?sensor=false&origins=$gpsLatAnt,$gpsLonAnt&destinations=$gpsLat,$gpsLon"));
										if(isset($json->rows[0]->elements[0]->distance)){
											$strDistance = $json->rows[0]->elements[0]->distance->value;
											$distance = $strDistance+0;
											
											$alertaACada = $dataBem['alerta_hodometro'];
											$alertaACadaSaldo = $dataBem['alerta_hodometro_saldo'];
											$alertaACadaSaldo = ($alertaACadaSaldo*1000) - $distance;
											if($alertaACadaSaldo <= 0 && $alertaACada > 0){
												$msg = $texto_sms_alerta_hodometro;
												$msg = str_replace("#CLIENTE", $aDataCliente[0], $msg);
												$msg = str_replace("#VEICULO", $dataBem['name'], $msg);
												$msg = str_replace("#HODOMETRO", $alertaACada, $msg);
												$msg = str_replace(' ', '+', $msg);
												sendSMS($dataCliente['celular'], $msg, '');
												$alertaACadaSaldo = $alertaACada;
											}
											$alertaACadaSaldo = (int)$alertaACadaSaldo/1000;
										}
										*/
										$geoDistance = distance($gpsLatAnt, $gpsLonAnt, $gpsLat, $gpsLon);
										//$strDistance = $json->rows[0]->elements[0]->distance->value;
										$distance = (int)($geoDistance*1000);
				
										$alertaACada = $dataBem['alerta_hodometro'];
										$alertaACadaSaldo = $dataBem['alerta_hodometro_saldo'];
										$alertaACadaSaldo = ($alertaACadaSaldo*1000) - $distance;
										if($alertaACadaSaldo <= 0 && $alertaACada > 0){
											$msg = $texto_sms_alerta_hodometro;
											$msg = str_replace("#CLIENTE", $aDataCliente[0], $msg);
											$msg = str_replace("#VEICULO", $dataBem['name'], $msg);
											$msg = str_replace("#HODOMETRO", $alertaACada, $msg);
											$msg = str_replace(' ', '+', $msg);
											//sendSMS($dataCliente['celular'], $msg, '');
											$alertaACadaSaldo = $alertaACada;
										}
										$alertaACadaSaldo = (int)$alertaACadaSaldo/1000;
									}
								}
							}
				
						}
					}catch(Exception $e){
						mysql_query("INSERT INTO controle (texto) VALUES ($e->getMessage())", $con);
					}
				
					mysql_query("INSERT INTO gprmc (date, imei, phone, satelliteFixStatus, latitudeDecimalDegrees, latitudeHemisphere, longitudeDecimalDegrees, longitudeHemisphere, speed, infotext, gpsSignalIndicator, km_rodado, converte, ligado) VALUES (now(), '$imei', '$phone', '$satelliteFixStatus', '$latitudeDecimalDegrees', '$latitudeHemisphere', '$longitudeDecimalDegrees', '$longitudeHemisphere', '$speed', '$infotext', '$gpsSignalIndicator', $distance, $converte, '$ligado')", $con);
				
					if($alertaACadaSaldo == 0) {
						mysql_query("UPDATE bem set date = now(), status_sinal = 'R', movimento = '$movimento', hodometro = hodometro+$distance WHERE imei = '$imei'", $con);
					} else {
						mysql_query("UPDATE bem set date = now(), status_sinal = 'R', movimento = '$movimento', hodometro = hodometro+$distance, alerta_hodometro_saldo = $alertaACadaSaldo WHERE imei = '$imei'", $con);
					}
					/*
					if($numRows == 0){
					mysql_query("INSERT INTO loc_atual (date, imei, phone, satelliteFixStatus, latitudeDecimalDegrees, latitudeHemisphere, longitudeDecimalDegrees, longitudeHemisphere, speed, infotext, gpsSignalIndicator) VALUES (now(), '$imei', '$phone', '$satelliteFixStatus', '$latitudeDecimalDegrees', '$latitudeHemisphere', '$longitudeDecimalDegrees', '$longitudeHemisphere', '$speed', '$infotext', '$gpsSignalIndicator')", $cnx);
					} else {
					mysql_query("UPDATE loc_atual set date = now(), phone = '$phone', satelliteFixStatus = '$satelliteFixStatus', latitudeDecimalDegrees = '$latitudeDecimalDegrees', latitudeHemisphere = '$latitudeHemisphere', longitudeDecimalDegrees = '$longitudeDecimalDegrees', longitudeHemisphere = '$longitudeHemisphere', speed = '$speed', infotext = '$infotext', gpsSignalIndicator = '$gpsSignalIndicator' where imei = '$imei'", $cnx);
					}
					*/
				} else {
					mysql_query("UPDATE bem set date = now(), status_sinal = 'S' WHERE imei = '$imei'", $con);
				}
				
				# Now check to see if we need to send any alerts.
				if ($infotext != "tracker") {
					$msg = $texto_sms_alerta;
					$msg = str_replace("#CLIENTE", $aDataCliente['nome'], $msg);
					$msg = str_replace("#VEICULO", $dataBem['name'], $msg);
				
					$res = mysql_query("SELECT responsible FROM bem WHERE imei='$imei'", $con);
					while($data = mysql_fetch_assoc($res)) {
						switch ($infotext) {
							case "dt":
							$body = "Disable Track OK";
							$msg = str_replace("#TIPOALERTA", "Rastreador Desabilitado", $msg);
							break;
							case "et":
							$body = "Stop Alarm OK";
							$msg = str_replace("#TIPOALERTA", "Alarme parado", $msg);
							break;
							case "gt";
							$body = "Move Alarm set OK";
							$msg = str_replace("#TIPOALERTA", "Alarme de Movimento ativado", $msg);
							break;
							case "help me":
							$body = "Help!";
							mysql_query("INSERT INTO message (imei, message) VALUES ('$$conn_imei', 'SOS!')", $con);
							$msg = str_replace("#TIPOALERTA", "SOS", $msg);
							//Envia comando de resposta: alerta recebido
							$send_cmd = "**,imei:". $conn_imei .",E";
							socket_send($socket, $send_cmd, strlen($send_cmd), 0);
							//printLog($fh, "Comando de resposta (help me): " . $send_cmd . " imei: " . $conn_imei);									
							break;
							case "ht":
							$body = "Speed alarm set OK";
							$msg = str_replace("#TIPOALERTA", "Alarme de velocidade ativado", $msg);
							break;
							case "it":
							$body = "Timezone set OK";
							break;
							case "low battery":
							$body = "Low battery!\nYou have about 2 minutes...";
							$msg = str_replace("#TIPOALERTA", "Bateria fraca, voce tem 2 minutos", $msg);
							mysql_query("INSERT INTO message (imei, message) VALUES ('$imei', 'Bat. Fraca')", $con);
							//Envia comando de resposta: alerta recebido
							$send_cmd = "**,imei:". $conn_imei .",E";
							socket_send($socket, $send_cmd, strlen($send_cmd), 0);
							//printLog($fh, "Comando de resposta (low battery): " . $send_cmd . " imei: " . $conn_imei);
							break;
							case "move":
							$body = "Move Alarm!";
							$msg = str_replace("#TIPOALERTA", "Seu veiculo esta em movimento", $msg);
							mysql_query("INSERT INTO message (imei, message) VALUES ('$imei', 'Movimento')", $on);
							//Envia comando de resposta: alerta recebido
							$send_cmd = "**,imei:". $conn_imei .",E";
							socket_send($socket, $send_cmd, strlen($send_cmd), 0);
							//printLog($fh, "Comando de resposta (move): " . $send_cmd . " imei: " . $conn_imei);									
							break;
							case "nt":
							$body = "Returned to SMS mode OK";
							break;
							case "speed":
							$body = "Speed alarm!";
							$msg = str_replace("#TIPOALERTA", "Seu veiculo ultrapassou o limite de velocidade", $msg);
							mysql_query("INSERT INTO message (imei, message) VALUES ('$imei', 'Velocidade')", $con);
							//Envia comando de resposta: alerta recebido
							$send_cmd = "**,imei:". $conn_imei .",E";
							socket_send($socket, $send_cmd, strlen($send_cmd), 0);
							//printLog($fh, "Comando de resposta (speed): " . $send_cmd . " imei: " . $conn_imei);
							break;
							case "stockade":
							$body = "Geofence Violation!";
							$msg = str_replace("#TIPOALERTA", "Seu veiculo saiu da cerca virtual", $msg);
							mysql_query("INSERT INTO message (imei, message) VALUES ('$imei', 'Cerca')", $con);
							//Envia comando de resposta: alerta recebido
							$send_cmd = "**,imei:". $conn_imei .",E";
							socket_send($socket, $send_cmd, strlen($send_cmd), 0);
							//printLog($fh, "Comando de resposta (stockade): " . $send_cmd . " imei: " . $conn_imei);
							break;
							case "door alarm":
							$body = "Open door!";
							mysql_query("INSERT INTO message (imei, message) VALUES ('$imei', 'Porta')", $con);
							//Envia comando de resposta: alerta recebido
							$send_cmd = "**,imei:". $conn_imei .",E";
							socket_send($socket, $send_cmd, strlen($send_cmd), 0);
							//printLog($fh, "Comando de resposta (door alarm): " . $send_cmd . " imei: " . $conn_imei);
							break;
							case "acc alarm":
							$body = "ACC alarm!";
							$msg = str_replace("#TIPOALERTA", "Seu veiculo esta com a chave ligada", $msg);
							mysql_query("INSERT INTO message (imei, message) VALUES ('$imei', 'Ignição')", $con);
							//Envia comando de resposta: alerta recebido
							$send_cmd = "**,imei:". $conn_imei .",E";
							socket_send($socket, $send_cmd, strlen($send_cmd), 0);
							//printLog($fh, "Comando de resposta (acc alarm): " . $send_cmd . " imei: " . $conn_imei);
							break;
							case "acc off":
							$body = "Ignicao Desligada!";
							$msg = str_replace("#TIPOALERTA", "Seu veiculo esta com a chave desligada", $msg);
							//mysql_query("INSERT INTO message (imei, message) VALUES ('$imei', 'Ignição')", $cnx);
							mysql_query("UPDATE bem SET ligado = 'N' where imei = '$imei'", $con);
							//Envia comando de resposta: alerta recebido
							$send_cmd = "**,imei:". $conn_imei .",E";
							socket_send($socket, $send_cmd, strlen($send_cmd), 0);
							//printLog($fh, "Comando de resposta (acc alarm): " . $send_cmd . " imei: " . $conn_imei);
							break;
							case "acc on":
							$body = "Ignicao Ligada!";
							$msg = str_replace("#TIPOALERTA", "Seu veiculo esta com a chave ligada", $msg);
							//mysql_query("INSERT INTO message (imei, message) VALUES ('$imei', 'Ignição')", $cnx);
							mysql_query("UPDATE bem SET ligado = 'S' where imei = '$imei'", $cnx);
							//Envia comando de resposta: alerta recebido
							$send_cmd = "**,imei:". $conn_imei .",E";
							socket_send($socket, $send_cmd, strlen($send_cmd), 0);
							//printLog($fh, "Comando de resposta (acc alarm): " . $send_cmd . " imei: " . $conn_imei);
							break;
						} //switch
						$msg = str_replace(' ', '+', $msg);
						//if($dataCliente['envia_sms'] == 'S' && $dataBem['envia_sms'] == 'S' && $infotext != 'acc on'&& $infotext != 'acc off' && $infotext != 'et')
						//	sendSMS($dataCliente['celular'], $msg, '');
						//Enviando e-mail de alerta
						$headers = "From: $email_from" . "\r\n" . "Reply-To: $email_from" . "\r\n";
						$responsible = $data['responsible'];
						$rv = mail($responsible, "Tracker - $imei", $body, $headers);
				
					} //while
				}
			}else {
				echo 'Cliente não encontrado. Erro: '.mysql_error($con);
			}
		} else {
			echo 'Veículo não encontrado. Erro: '.mysql_error($con);
		}
		mysql_close($con);
	} else {
		echo 'Não foi possivel conectar ao banco. Erro: '.mysql_error();
	}
}

function atualizarBemSerial($imei, $serial){
	$con = mysql_connect("cloudservice.cgejdsdl842e.sa-east-1.rds.amazonaws.com", "gpstracker", "d1$1793689");
	if($con !== false){
		mysql_select_db('tracker', $con);
		
		mysql_query("update bem set serial_tracker = '$serial' where imei = '$imei'", $con);
		
		mysql_close($con);
	} else {
		echo "Erro: ".mysql_error($con);
	}
}

function recuperaBemSerial($imei){
	$con = mysql_connect("cloudservice.cgejdsdl842e.sa-east-1.rds.amazonaws.com", "gpstracker", "d1$1793689");
	$serial = '';
	if($con !== false){
		mysql_select_db('tracker', $con);
		
		$res = mysql_query("select serial_tracker from bem where imei = '$imei'", $con);
		if($res !== false){
			$dataRes = mysql_fetch_assoc($res);
			$serial = $dataRes['serial_tracker'];
		}
		mysql_close($con);
	}
	return $serial;
}

function trataCommand($send_cmd, $conn_imei){
	$sizeData = 0;
	$serial = recuperaBemSerial($conn_imei);
	
	$serial = str_replace(' ', '', $serial);
	
	$decSerial = hexdec($serial);
	
	$decSerial = $decSerial+1;
	
	if($decSerial > 65535){
		$decSerial = 1;
	}
	
	$serial = dechex($decSerial);
	
	while(strlen($serial) < 4) $serial = '0'.$serial;
	
	$serial = substr($serial, 0, 2).' '.substr($serial, 2, 2);
	
	$sizeData = dechex(11 + strlen($send_cmd));
	
	while(strlen($sizeData) < 2) $sizeData = '0'.$sizeData;
	
	$lengthCommand = dechex(4+strlen($send_cmd));
	
	while(strlen($lengthCommand) < 2) $lengthCommand = '0'.$lengthCommand;
	
	$temp = $sizeData.' 80 '.$lengthCommand.' 00 00 00 00 '.$send_cmd.' '.$serial;
	
	$sendCommands = array();
	
	$crc = crcx25($temp);
	
	$crc = str_replace('ffff','',dechex($crc));
	
	$crc = strtoupper(substr($crc,0,2)).' '.strtoupper(substr($crc,2,2));
	
	$sendcmd = '78 78 '.$temp. ' ' . $crc . ' 0D 0A';
	
	$sendCommands = explode(' ', $sendcmd);
	
	$sendcmd = '';
	for($i=0; $i<count($sendCommands); $i++){
		if($i < 9 || $i >=10){
			$sendcmd .= chr(hexdec(trim($sendCommands[$i])));
		} else {
			$sendcmd .= trim($sendCommands[$i]);
		}
	}
	
	return $sendcmd;
}

?>
