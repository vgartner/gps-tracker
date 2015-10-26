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
$ip = '45.55.129.87';
// Port
//$port = $dataPorta['valor'];
$port = 7099;
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
			
			$isGt06 = false;
			$tempString = $rec."";
			//verifica se é gt06
			$retTracker = hex_dump($rec."");
			$arCommands = explode(' ',trim($retTracker));
			if(count($arCommands) > 0){
				if($arCommands[0].$arCommands[1] == '7878'){
					$isGt06 = true;
				}
			}
			
			if($isGt06){
				$arCommands = explode(' ',$retTracker);
				$tmpArray = array_count_values($arCommands);
				
				$count = $tmpArray[78];
				$count =  $count / 2;
				
				$tmpArCommand = array();
				if($count >= 1){
					$ar = array();
					for($i=0;$i<count($arCommands);$i++){
						if(strtoupper(trim($arCommands[$i]))=="78" && isset($arCommands[$i+1]) && strtoupper(trim($arCommands[$i+1])) == "78"){
							$ar = array();
							if(strlen($arCommands[$i]) == 4){
								$ar[] = substr($arCommands[$i],0,2);
								$ar[] = substr($arCommands[$i],2,2);
							} else {
								$ar[] = $arCommands[$i];
							}
						} elseif(isset($arCommands[$i+1]) && strtoupper(trim($arCommands[$i+1]))=="78" && strtoupper(trim($arCommands[$i]))!="78" && isset($arCommands[$i+2]) && strtoupper(trim($arCommands[$i+2]))=="78"){
							if(strlen($arCommands[$i]) == 4){
								$ar[] = substr($arCommands[$i],0,2);
								$ar[] = substr($arCommands[$i],2,2);
							} else {
								$ar[] = $arCommands[$i];
							}
							$tmpArCommand[] = $ar;
						} elseif($i == count($arCommands)-1){
							if(strlen($arCommands[$i]) == 4){
								$ar[] = substr($arCommands[$i],0,2);
								$ar[] = substr($arCommands[$i],2,2);
							} else {
								$ar[] = $arCommands[$i];
							}
							$tmpArCommand[] = $ar;
						} else {
							if(strlen($arCommands[$i]) == 4){
								$ar[] = substr($arCommands[$i],0,2);
								$ar[] = substr($arCommands[$i],2,2);
							} else {
								$ar[] = $arCommands[$i];
							}
						}
					}
				}
				for($i=0;$i<count($tmpArCommand);$i++) {
					$arCommands = $tmpArCommand[$i];
					$sizeData = $arCommands[2];
					
					$protocolNumber = strtoupper(trim($arCommands[3]));
					
					if($protocolNumber == '01'){
						$imei = '';
						
						for($i=4; $i<12; $i++){
							$imei = $imei.$arCommands[$i];
						}
						$imei = substr($imei,1,15);
						$conn_imei = $imei;
						
						abrirArquivoLog($imei);
						
						$sendCommands = array();
						
						$send_cmd = '78 78 05 01 '.strtoupper($arCommands[12]).' '.strtoupper($arCommands[13]);
						
						atualizarBemSerial($conn_imei, strtoupper($arCommands[12]).' '.strtoupper($arCommands[13]));
						
						$newString = '';
						$newString = chr(0x05).chr(0x01).$rec[12].$rec[13];
						$crc16 = GetCrc16($newString,strlen($newString));
						$crc16h = floor($crc16/256);
						$crc16l = $crc16 - $crc16h*256;
						
						$crc = dechex($crc16h).' '.dechex($crc16l);
						
						//$crc = crcx25('05 '.$protocolNumber.' '.strtoupper($arCommands[12]).' '.strtoupper($arCommands[13]));
						
						//$crc = str_replace('ffff','',dechex($crc));
						
						//$crc = strtoupper(substr($crc,0,2)).' '.strtoupper(substr($crc,2,2));
						
						$send_cmd = $send_cmd. ' ' . $crc . ' 0D 0A';
						
						$sendCommands = explode(' ', $send_cmd);
						
						printLog($fh, date("d-m-y h:i:sa") . " Imei: $imei Got: ".implode(" ",$arCommands));
						printLog($fh, date("d-m-y h:i:sa") . " Imei: $imei Sent: $send_cmd Length: ".strlen($send_cmd));
						
						$send_cmd = '';
						for($i=0; $i<count($sendCommands); $i++){
							$send_cmd .= chr(hexdec(trim($sendCommands[$i])));
						}
						socket_send($socket, $send_cmd, strlen($send_cmd), 0);
					} else if ($protocolNumber == '12') {
						printLog($fh, date("d-m-y h:i:sa") . " Imei: $imei Got: ".implode(" ",$arCommands));
						$dataPosition = hexdec($arCommands[4]).'-'.hexdec($arCommands[5]).'-'.hexdec($arCommands[6]).' '.hexdec($arCommands[7]).':'.hexdec($arCommands[8]).':'.hexdec($arCommands[9]);
						$gpsQuantity = $arCommands[10];
						$lengthGps = hexdec(substr($gpsQuantity,0,1));
						$satellitesGps = hexdec(substr($gpsQuantity,1,1));
						$latitudeHemisphere = '';
						$longitudeHemisphere = '';
						$speed = hexdec($arCommands[19]);
						
						
						
						//78 78 1f 12 0e 05 1e 10 19 05 c4 01 2c 74 31 03 fa b2 b2 07 18 ab 02 d4 0b 00 b3 00 24 73 00 07 5b 59 0d 0a
						//18 ab
						//0001100010101011
						//01 2b af f6
						//03 fa 37 88
						if(isset($arCommands[20]) && isset($arCommands[21])){
							$course = decbin(hexdec($arCommands[20]));
							while(strlen($course) < 8) $course = '0'.$course;
							
							$status = decbin(hexdec($arCommands[21]));
							while(strlen($status) < 8) $status = '0'.$status;
							$courseStatus = $course.$status;
							
							$gpsRealTime = substr($courseStatus, 2,1) == '0' ? 'F':'D';
							$gpsPosition = substr($courseStatus, 3,1) == '0' ? 'F':'L';
							//$gpsPosition = 'S';
							$gpsPosition == 'F' ? 'S' : 'N';							
							$latitudeHemisphere = substr($courseStatus, 5,1) == '0' ? 'S' : 'N';
							$longitudeHemisphere = substr($courseStatus, 4,1) == '0' ? 'E' : 'W';
						}
						$latHex = hexdec($arCommands[11].$arCommands[12].$arCommands[13].$arCommands[14]);
						$lonHex = hexdec($arCommands[15].$arCommands[16].$arCommands[17].$arCommands[18]);
						
						$latitudeDecimalDegrees = ($latHex*90)/162000000;
						$longitudeDecimalDegrees = ($lonHex*180)/324000000;
						
						$latitudeHemisphere == 'S' && $latitudeDecimalDegrees = $latitudeDecimalDegrees*-1;
						$longitudeHemisphere == 'W' && $longitudeDecimalDegrees = $longitudeDecimalDegrees*-1;
						if(isset($arCommands[30]) && isset($arCommands[30])){
							atualizarBemSerial($conn_imei, strtoupper($arCommands[30]).' '.strtoupper($arCommands[31]));
						} else {
							echo 'Imei: '.$imei.' Got:'.$retTracker;
						}
						$dados = array($gpsPosition, 
										$latitudeDecimalDegrees, 
										$longitudeDecimalDegrees, 
										$latitudeHemisphere, 
										$longitudeHemisphere, 
										$speed, 
										$imei,
										$dataPosition,
										'tracker',
										'',
										'S',
										$gpsRealTime);
						
						tratarDados($dados);
						// echo "[PROTOCOLO 12] Lat/Long: ".$latitudeDecimalDegrees.", ".$longitudeDecimalDegrees."****************";
					} else if ($protocolNumber == '13') { //heatbeat
						$terminalInformation = decbin(hexdec($arCommands[4]));
						while(strlen($terminalInformation) < 8) $terminalInformation = '0'.$terminalInformation; //00101110
						$gasOil = substr($terminalInformation,0,1) == '0' ? 'S' : 'N';
						
						$gpsTrack = substr($terminalInformation,1,1) == '1' ? 'S' : 'N';
						$alarm = '';
						//78 78 0a 13 05 06 04 00 02 00 18 68 47 0d 0a
						//76543210
						//00000101
						//0d1$17936897
						switch(substr($terminalInformation,2,3)){
							case '100': $alarm = 'help me'; break;
							case '011': $alarm = 'low battery'; break;
							case '010': $alarm = 'dt'; break;
							case '001': $alarm = 'move'; break;
							case '000': $alarm = 'tracker'; break;
						}
						
						$ativo = substr($terminalInformation,7,1) == '1' ? 'S' : 'S';
						$charge = substr($terminalInformation,5,1) == '1' ? 'S' : 'N';
						$acc = substr($terminalInformation,6,1) == '1' ? 'S' : 'N';
						//$defense = substr($terminalInformation,7,1) == '1' ? 'S' : 'N';
						$voltageLevel = hexdec($arCommands[5]);
						$gsmSignal = hexdec($arCommands[6]);
						
						$alarmLanguage = hexdec($arCommands[7]);
						
						switch($alarmLanguage){
							case 0: $alarm = 'tracker'; break;
							case 1: $alarm = 'help me'; break;
							case 2: $alarm = 'dt'; break;
							case 3: $alarm = 'move'; break;
							case 4: $alarm = 'stockade'; break;
							case 5: $alarm = 'stockade'; break;
						}
					
						$sendCommands = array();
						if(strlen($arCommands[9]) == 4 && count($arCommands) == 10){
							$arCommands[9] = substr($terminalInformation,0,2);
							$arCommands[] = substr($terminalInformation,2,2);
						}
						
						$send_cmd = '78 78 05 13 '.strtoupper($arCommands[9]).' '.strtoupper($arCommands[10]);
						
						$newString = '';
						$newString = chr(0x05).chr(0x13).$rec[9].$rec[10];
						$crc16 = GetCrc16($newString,strlen($newString));
						$crc16h = floor($crc16/256);
						$crc16l = $crc16 - $crc16h*256;
						
						$crc = dechex($crc16h).' '.dechex($crc16l);
						
						//$crc = crcx25('05 13 '.strtoupper($arCommands[9]).' '.strtoupper($arCommands[10]));
						
						//$crc = str_replace('ffff','',dechex($crc));
						
						//$crc = strtoupper(substr($crc,0,2)).' '.strtoupper(substr($crc,2,2));
						
						$send_cmd = $send_cmd. ' ' . $crc . ' 0D 0A';
						
						$sendCommands = explode(' ', $send_cmd);
						
						atualizarBemSerial($conn_imei, strtoupper($arCommands[9]).' '.strtoupper($arCommands[10]));
						
						printLog($fh, date("d-m-y h:i:sa") . " Imei: $imei Got: ".implode(" ",$arCommands));
						printLog($fh, date("d-m-y h:i:sa") . " Imei: $imei Sent: $send_cmd Length: ".strlen($send_cmd));
						$send_cmd = '';
						for($i=0; $i<count($sendCommands); $i++){
							$send_cmd .= chr(hexdec(trim($sendCommands[$i])));
						}
						socket_send($socket, $send_cmd, strlen($send_cmd), 0);
						
						$con = mysql_connect("cloudservice.cgejdsdl842e.sa-east-1.rds.amazonaws.com", "gpstracker", "d1$1793689");
						if($con !== false){
							mysql_select_db('tracker', $con);
							$res = mysql_query("SELECT * FROM loc_atual WHERE imei = '$imei'", $con);
							if($res !== false){
								$data = mysql_fetch_assoc($res);
								mysql_close($con);
								$dados = array($gpsTrack, 
											$data['latitudeDecimalDegrees'], 
											$data['longitudeDecimalDegrees'], 
											$data['latitudeHemisphere'], 
											$data['longitudeHemisphere'], 
											0, 
											$imei,
											date('Y-m-d'),
											$alarm,
											$acc,
											$ativo);
							
								tratarDados($dados);
								// echo "[PROTOCOLO 13] Lat/Long: ".$data['latitudeDecimalDegrees'].", ".$data['longitudeDecimalDegrees']."****************";
							}
						}
					} else if ($protocolNumber == '15') {
						printLog($fh, date("d-m-y h:i:sa") . " Got: $retTracker");
						$msg = '';
						for($i=9; $i<count($arCommands)-8; $i++){
							$msg .= chr(hexdec($arCommands[$i]));
						}
						$con = mysql_connect("cloudservice.cgejdsdl842e.sa-east-1.rds.amazonaws.com", "gpstracker", "d1$1793689");
						if($con !== false){
							mysql_select_db('tracker', $con);
							
							$alerta = '';
							if(strpos($msg, 'Already') > -1){
								$alerta = 'Bloqueio já efetuado!';
							}
							
							if(strpos($msg, 'DYD=Suc') > -1){
								$alerta = 'Bloqueio efetuado!';
							}
							
							if(strpos($msg, 'HFYD=Su') > -1){
								$alerta = 'Desbloqueio efetuado!';
							}
							
							
							mysql_query("INSERT INTO message (imei, message) VALUES ('$conn_imei', '$alerta')", $con);
							mysql_close($con);
						}
					} else if ($protocolNumber == '16') {
						printLog($fh, date("d-m-y h:i:sa") . " Got: ".implode(" ",$arCommands));
						$dataPosition = hexdec($arCommands[4]).'-'.hexdec($arCommands[5]).'-'.hexdec($arCommands[6]).' '.hexdec($arCommands[7]).':'.hexdec($arCommands[8]).':'.hexdec($arCommands[9]);
						$gpsQuantity = $arCommands[10];
						$lengthGps = hexdec(substr($gpsQuantity,0,1));
						$satellitesGps = hexdec(substr($gpsQuantity,1,1));
						$latitudeHemisphere = '';
						$longitudeHemisphere = '';
						$speed = hexdec($arCommands[19]);
						$course = decbin(hexdec($arCommands[20]));
	
						while(strlen($course) < 8) $course = '0'.$course;
						$status = decbin(hexdec($arCommands[21]));
						while(strlen($status) < 8) $status = '0'.$status;
						$courseStatus = $course.$status;
						
						$gpsRealTime = substr($courseStatus, 2,1);
						$gpsPosition = substr($courseStatus, 3,1) == '0' ? 'F':'L';
						$gpsPosition = 'S';
						$latitudeHemisphere = substr($courseStatus, 5,1) == '0' ? 'S' : 'N';
						$longitudeHemisphere = substr($courseStatus, 4,1) == '0' ? 'E' : 'W';
						
						$latHex = hexdec($arCommands[11].$arCommands[12].$arCommands[13].$arCommands[14]);
						$lonHex = hexdec($arCommands[15].$arCommands[16].$arCommands[17].$arCommands[18]);
						
						$latitudeDecimalDegrees = ($latHex*90)/162000000;
						$longitudeDecimalDegrees = ($lonHex*180)/324000000;
						
						$latitudeHemisphere == 'S' && $latitudeDecimalDegrees = $latitudeDecimalDegrees*-1;
						$longitudeHemisphere == 'W' && $longitudeDecimalDegrees = $longitudeDecimalDegrees*-1;
						
						//78 78 25 16 0e 02 1b 11 11 26 c3 02 73 a8 0c 04 a6 5c 77 02 18 11 09 02 d4 0b 15 91 00 1e 0b 66 01 04 01 02 00 10 fe 67 0d 0a
						//66 01100110
						$terminalInformation = decbin(hexdec($arCommands[31]));
						while(strlen($terminalInformation) < 8) $terminalInformation = '0'.$terminalInformation;
						$gasOil = substr($terminalInformation,0,1) == '0' ? 'S' : 'N';
						$gpsTrack = substr($terminalInformation,1,1) == '1' ? 'S' : 'N';
						$alarm = '';
						switch(substr($terminalInformation,2,3)){
							case '100': $alarm = 'help me'; break;
							case '011': $alarm = 'low battery'; break;
							case '010': $alarm = 'dt'; break;
							case '001': $alarm = 'move'; break;
							case '000': $alarm = 'tracker'; break;
						}
						
						$con = mysql_connect("cloudservice.cgejdsdl842e.sa-east-1.rds.amazonaws.com", "gpstracker", "d1$1793689");
						if($con !== false){
							mysql_select_db('tracker', $con);
							if($alarm == "help me")
								mysql_query("INSERT INTO message (imei, message) VALUES ('$conn_imei', 'SOS!')", $con);
							mysql_close($con);
						}
						$charge = substr($terminalInformation,5,1) == '1' ? 'S' : 'N';
						$acc = substr($terminalInformation,6,1) == '1' ? 'acc on' : 'acc off';
						$defense = substr($terminalInformation,7,1) == '1' ? 'S' : 'N';
						$voltageLevel = hexdec($arCommands[32]);
						$gsmSignal = hexdec($arCommands[33]);
						
						$alarmLanguage = hexdec($arCommands[34]);
						/*
						switch($alarmLanguage){
							case 0: $alarm = 'normal'; break;
							case 1: $alarm = 'help me'; break;
							case 2: $alarm = 'dt'; break;
							case 3: $alarm = 'move'; break;
							case 4: $alarm = 'stockade'; break;
							case 5: $alarm = 'stockade'; break;
						}
						*/
						$dados = array($gpsPosition, 
										$latitudeDecimalDegrees, 
										$longitudeDecimalDegrees, 
										$latitudeHemisphere, 
										$longitudeHemisphere, 
										$speed, 
										$imei,
										$dataPosition,
										$alarm, 
										$acc);
						
						tratarDados($dados);
						// echo "[PROTOCOLO 16] Lat/Long: ".$latitudeDecimalDegrees.", ".$longitudeDecimalDegrees."****************";
						
						$send_cmd = '78 78 05 16 '.strtoupper($arCommands[36]).' '.strtoupper($arCommands[37]);
						
						//$crc = crcx25('05 16 '.strtoupper($arCommands[36]).' '.strtoupper($arCommands[37]));
						
						atualizarBemSerial($conn_imei, strtoupper($arCommands[36]).' '.strtoupper($arCommands[37]));
						
						//$crc = str_replace('ffff','',dechex($crc));
						
						//$crc = strtoupper(substr($crc,0,2)).' '.strtoupper(substr($crc,2,2));
						
						$newString = '';
						$newString = chr(0x05).chr(0x16).$rec[36].$rec[37];
						$crc16 = GetCrc16($newString,strlen($newString));
						$crc16h = floor($crc16/256);
						$crc16l = $crc16 - $crc16h*256;
						
						$crc = dechex($crc16h).' '.dechex($crc16l);
						
						$send_cmd = $send_cmd. ' ' . $crc . ' 0D 0A';
						
						$sendCommands = explode(' ', $send_cmd);
						
						printLog($fh, date("d-m-y h:i:sa") . " Imei: $imei Sent: $send_cmd Length: ".strlen($send_cmd));
						$send_cmd = '';
						for($i=0; $i<count($sendCommands); $i++){
							$send_cmd .= chr(hexdec(trim($sendCommands[$i])));
						}
						socket_send($socket, $send_cmd, strlen($send_cmd), 0);
					} else if ($protocolNumber == '1A') {
						printLog($fh, date("d-m-y h:i:sa") . " Got: ".implode(" ",$arCommands));
					} else if ($protocolNumber == '80') {
						printLog($fh, date("d-m-y h:i:sa") . " Got: ".implode(" ",$arCommands));
					}
				}
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
	$novaCord = ($hemisphere == "S" || $hemisphere == "W") ? $novaCord * -1 : $novaCord ;
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

function enviaEmail($destinatario, $assunto, $msgConteudo){
	require_once '/var/www/phpmailer/class.phpmailer.php';
	require_once '/var/www/config.php';
	// Inicia a classe PHPMailer
	$mail = new PHPMailer();

	// Define os dados do servidor e tipo de conexão
	// =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
	$mail->IsSMTP(); // Define que a mensagem será SMTP
	$mail->Host = HOST_SMTP; // Endereço do servidor SMTP
	$mail->SMTPAuth = AUTENT_SMTP; // Usa autenticação SMTP? (opcional)
	$mail->Username = USER_SMTP; // Usuário do servidor SMTP
	$mail->Password = SENHA_SMTP; // Senha do servidor SMTP

	// Define o remetente
	// =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
	$mail->From = USER_SMTP; // Seu e-mail
	$mail->FromName = "InovarSat"; // Seu nome

	// Define os destinatário(s)
	// =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
	$mail->AddAddress($destinatario);
	$mail->AddAddress('jesaias@mmconsulting.com.br');
	//$mail->AddCC('ciclano@site.net', 'Ciclano'); // Copia
	//$mail->AddBCC('fulano@dominio.com.br', 'Fulano da Silva'); // Cópia Oculta

	// Define os dados técnicos da Mensagem
	// =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
	$mail->IsHTML(true); // Define que o e-mail será enviado como HTML
	$mail->CharSet = 'utf-8'; // Charset da mensagem (opcional)
	$mail->setLanguage('br', '/var/www/phpmailer/language/');

	// Define a mensagem (Texto e Assunto)
	// =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
	$mail->Subject  = $assunto; // Assunto da mensagem
	$mail->Body = $msgConteudo;
	$mail->AltBody = strip_tags($msgConteudo);

	// Define os anexos (opcional)
	// =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
	//$mail->AddAttachment("c:/temp/documento.pdf", "novo_nome.pdf");  // Insere um anexo

	// Envia o e-mail
	$enviado = $mail->Send();

	// Limpa os destinatários e os anexos
	$mail->ClearAllRecipients();
	$mail->ClearAttachments();

	// Exibe uma mensagem de resultado
	if ($enviado) {
		echo "E-mail enviado com sucesso! (" . $msgConteudo . ")";
	} else {
		echo "Não foi possível enviar o e-mail ($assunto). " . $mail->ErrorInfo;
	}
}

/**
 * [checkAlerta Verifica se já foi emitido um alerta do mesmo tipo para aquele dia]
 * @param  [int] $imei     [IMEI do Rastreador]
 * @param  [string] $mensagem [Título total ou parcial do nome ao qual se deseja buscar]
 * @return [boolean]           [TRUE - se já houver alertas | FALSE - se não houver alertas]
 */
function checkAlerta($imei, $mensagem){
	$cnx = mysql_connect("cloudservice.cgejdsdl842e.sa-east-1.rds.amazonaws.com", "gpstracker", "d1$1793689") or die("Could not connect: " . mysql_error());
	mysql_select_db('tracker', $cnx);
	$res = mysql_query("SELECT message, DATE_FORMAT(date, '%d/%m/%Y') AS data FROM message WHERE imei = $imei AND viewed = 'N' ", $cnx);

	if (mysql_num_rows($res) > 0) {
		$data = date("d/m/Y");
		while ($alerta = mysql_fetch_array($res)) {
			if ( $alerta['data'] == $data && (stripos($mensagem, $alerta['message']) !== false) ) return true;
		}
	}
	else return false;
}

/*function distance($lat1, $lon1, $lat2, $lon2, $unit) {

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
}*/

function distance($lat1, $lng1, $lat2, $lng2, $miles = false){
	$pi80 = M_PI / 180;
	$lat1 *= $pi80;
	$lng1 *= $pi80;
	$lat2 *= $pi80;
	$lng2 *= $pi80;
 
	$r = 6372.797; // mean radius of Earth in km
	$dlat = $lat2 - $lat1;
	$dlng = $lng2 - $lng1;
	$a = sin($dlat / 2) * sin($dlat / 2) + cos($lat1) * cos($lat2) * sin($dlng / 2) * sin($dlng / 2);
	$c = 2 * atan2(sqrt($a), sqrt(1 - $a));
	$km = $r * $c;
 
	return ($miles ? ($km * 0.621371192) : $km);
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
		$dataBem = null;
		$dataCliente = null;
		$ligado = (count($dados) > 9) ? $dados[9] : 'N';
		$ativo = (count($dados) > 10) ? $dados[10] : 'N';
		$realTime = (count($dados) > 11) ? $dados[11] : '';
		//D para diferencial
		
		$gpsSignalIndicator = $dados[0] == 'S' ? 'F' : 'L';
		
		//Otavio Gomes - 200120152137 
		// Estava aqui o problema do crx1 so enviar o sinal S.
		if($realTime == 'D')
			$gpsSignalIndicator = 'D';
		else
			$gpsSignalIndicator = 'R';
		
		$resBem = mysql_query("SELECT id, cliente, envia_sms, name, hodometro, alerta_hodometro, alerta_hodometro_saldo, limite_velocidade FROM bem WHERE imei = '$imei'", $con);
		//echo 'Ligado: '.$ligado;
		$dataBem = mysql_fetch_assoc($resBem);

		if($resBem !== false){

			$resCliente = mysql_query("SELECT id, celular, dt_ultm_sms, envia_sms, sms_acada, hour(TIMEDIFF(now(), dt_ultm_sms)) horas, minute(TIMEDIFF(now(), dt_ultm_sms)) minutos, nome, email FROM cliente WHERE id = ".$dataBem['cliente'], $con);
			if($resCliente !== false){
				$dataCliente = mysql_fetch_assoc($resCliente);

				$texto_sms_localiza = "";
				$texto_sms_alerta_hodometro = "";
				$texto_sms_alerta = "";
				
				$result = mysql_query("SELECT * FROM preferencias", $con);
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

				$movimento = '';
				if ($speed > 0){
					$movimento = 'S';
					if ($dataBem['limite_velocidade'] != "0" && $dataBem['limite_velocidade'] != null && $speed > $dataBem['limite_velocidade']) {
						if (!checkAlerta($imei, 'Limite Velocidade')) {
							mysql_query("INSERT INTO message (imei, message) VALUES ('$imei', 'Limite Velocidade')", $con);
							$msgEmail = "<p><b>Alerta de Limite de Velocidade: </b></p><br><p>O veículo ". $dataBem['name'] ." está trafegando com velocidade de ". $speed ." Km/h, ultrapassando o limite de velocidade definido (".$dataBem['limite_velocidade']." Km/h) em ". date("d/m/Y") . " às " . date("H:i:s") .".</p><br><i>Equipe InovarSat</i>";
							enviaEmail($dataCliente['email'], "Alerta de Limite de Velocidade", $msgEmail);
						}
					}
				}
				else $movimento = 'N';
				
				// CERCA VIRTUAL
				if ( $imei != "" ) {
					$consulta = mysql_query("SELECT * FROM geo_fence WHERE imei = '$imei'", $con);
					while($data = mysql_fetch_assoc($consulta)) {
						$idCerca = $data['id'];
						$imeiCerca = $data['imei'];
						$nomeCerca = $data['nome'];
						$coordenadasCerca = $data['coordenadas'];
						$resultCerca = $data['tipo'];
						$tipoEnvio = $data['tipoEnvio'];
						
						$lat_point = $latitudeDecimalDegrees;
						$lng_point = $longitudeDecimalDegrees;

						$exp = explode("|", $coordenadasCerca);

						if( count($exp) < 5 ) {
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

						if ( $lat_vertice_1 < $lat_point Or $lat_point < $lat_vertice_2 And $lng_point < $lng_vertice_1 Or $lng_vertice_2 < $lng_point ) {
							$result = '0';
							$situacao = 'fora';
						} else {
							$result = '1';
							$situacao = 'dentro';
						}

						if ( $result == 0 And $movimento == 'S' ) {
							
							if (!checkAlerta($imei, 'Cerca Violada')){
								mysql_query("INSERT INTO message (imei, message) VALUES ('$imei', 'Cerca Violada')", $con);

								if ( $tipoEnvio == 0 ) {
									# Convert the GPS coordinates to a human readable address
									/*$tempstr = "http://maps.google.com/maps/geo?q=$lat_point,$lng_point&oe=utf-8&sensor=true&key=ABQIAAAAFd56B-wCWVpooPPO7LR3ihTz-K-sFZ2BISbybur6B4OYOOGbdRShvXwdlYvbnwC38zgCx2up86CqEg&output=csv"; //output = csv, xml, kml, json
									$rev_geo_str = file_get_contents($tempstr);
									$rev_geo_str = preg_replace("/\"/","", $rev_geo_str);
									$rev_geo = explode(',', $rev_geo_str);
									$logradouro = $rev_geo[2] .",". $rev_geo[3] ;*/

									/*$consulta1 = mysql_query("SELECT a.*, b.* FROM cliente a INNER JOIN bem b ON a.id = b.cliente WHERE b.imei = '$imei'", $con);
									
									while($data = mysql_fetch_assoc($consulta1)) {
										$emailDestino = $data['email'];
										$nameBem = $data['name'];
										$msgEmail = "<p><b>Alerta de Violação de Perímetro:</b></p><br><p>O veículo ". $nameBem .", está ". $situacao ." do perímetro ". $nomeCerca .", as ". date("H:i:s") ." do dia ". date("d/m/Y") . ".</p><br><i>Equipe InovarSat</i>";
										enviaEmail($emailDestino, "Alerta de Violação de Perímetro", $msgEmail);
									}*/
									$json = json_decode(file_get_contents("http://maps.googleapis.com/maps/api/geocode/json?latlng=".$lat_point.",".$lng_point."&sensor=false"));
									if ( isset( $json->status ) && $json->status == 'OK') {
										$address = $json->results[0]->formatted_address;
										$address = utf8_decode($address);
									}
									//else echo $json->status;

									$emailDestino = $dataCliente['email'];
									$nameBem = $dataBem['name'];
									$msgEmail = "<p><b>Alerta de Violação de Perímetro: </b></p><br><p>O veículo ". $nameBem .", está ". $situacao ." do perímetro ". $nomeCerca .", transitando na " . $address . " às ". date("H:i:s") ." do dia ". date("d/m/Y") . ".</p><br><i>Equipe InovarSat</i>";
									enviaEmail($emailDestino, "Alerta de Violação de Perímetro", $msgEmail);
								}
							}
						}
					}
				}//FIM CERCA VIRTUAL


				# Write it to the database...
				if ($gpsSignalIndicator != 'L' && !empty($latitudeDecimalDegrees)) {
									
					$gpsLat = $latitudeDecimalDegrees;//gprsToGps($latitudeDecimalDegrees, $latitudeHemisphere)
					$gpsLon = $longitudeDecimalDegrees;//gprsToGps($longitudeDecimalDegrees, $longitudeHemisphere)
					$gpsLatAnt = 0;
					$gpsLatHemAnt = '';
					$gpsLonAnt = 0;
					$gpsLonHemAnt = '';
					$alertaACadaSaldo = 0;
				
					$resLocAtual = mysql_query("SELECT id, latitudeDecimalDegrees, latitudeHemisphere, longitudeDecimalDegrees, longitudeHemisphere FROM loc_atual WHERE imei = '$imei' LIMIT 1", $con);
					$numRows = mysql_num_rows($resLocAtual);
					
					if($numRows == 0){
						mysql_query("INSERT INTO loc_atual (date, imei, phone, satelliteFixStatus, latitudeDecimalDegrees, latitudeHemisphere, longitudeDecimalDegrees, longitudeHemisphere, speed, infotext, gpsSignalIndicator, converte, ligado) VALUES (now(), '$imei', '$phone', '$satelliteFixStatus', '$latitudeDecimalDegrees', '$latitudeHemisphere', '$longitudeDecimalDegrees', '$longitudeHemisphere', '$speed', '$infotext', '$gpsSignalIndicator', 0, '$ligado')", $con);
					} else {
						mysql_query("UPDATE loc_atual SET date = now(), phone = '$phone', satelliteFixStatus = '$satelliteFixStatus', latitudeDecimalDegrees = '$latitudeDecimalDegrees', latitudeHemisphere = '$latitudeHemisphere', longitudeDecimalDegrees = '$longitudeDecimalDegrees', longitudeHemisphere = '$longitudeHemisphere', speed = '$speed', infotext = '$infotext', gpsSignalIndicator = '$gpsSignalIndicator', converte = 0, ligado = '$ligado' WHERE imei = '$imei'", $con);
					}
					
					$distance = 0;
					try{
						$bemId = $dataBem['id'];
						$countGeoDistance = mysql_query("SELECT bem FROM geo_distance WHERE bem = $bemId", $con);
						if($countGeoDistance === false || mysql_num_rows($countGeoDistance) == 0) {
							mysql_query("INSERT INTO geo_distance (bem, tipo) VALUES($bemId, 'I')", $con);
							mysql_query("INSERT INTO geo_distance (bem, tipo) VALUES($bemId, 'F')", $con);
						}
				
						/*envio de sms*/
						if($dataCliente['envia_sms'] == 'S' && $dataBem['envia_sms'] == 'S' && !empty($dataCliente['celular']) && !empty($dataCliente['sms_acada'])){
							if(empty($dataCliente['dt_ultm_sms'])){
								mysql_query("UPDATE cliente SET dt_ultm_sms = convert_tz(now(), 'GMT', 'Brazil/East') WHERE id = " . $dataCliente['id'],$con);
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
										if($retorno < 0) mysql_query("INSERT INTO controle(texto) VALUES('envio de sms retorno: $retorno')",$con);
										else mysql_query("UPDATE cliente SET dt_ultm_sms = convert_tz(now(), 'GMT', 'Brazil/East') WHERE id = " . $dataCliente['id'],$con);
									}
								}
							}
						}
				
						if($movimento == 'S'){
							$resGeoDistance = mysql_query("SELECT parou FROM geo_distance WHERE bem = $bemId AND tipo = 'I'", $con);
							if($resGeoDistance !== false) {
								$dataGeoDistance = mysql_fetch_assoc($resGeoDistance);
								if($dataGeoDistance['parou'] == 'S' || empty($dataGeoDistance['parou'])){
									mysql_query("UPDATE geo_distance SET latitudeDecimalDegrees = '$latitudeDecimalDegrees', latitudeHemisphere = '$latitudeHemisphere', longitudeDecimalDegrees = '$longitudeDecimalDegrees', longitudeHemisphere = '$longitudeHemisphere', parou = 'N' WHERE bem = $bemId AND tipo = 'I'", $con);
								}
							}
						} else {
							$resGeoDistance = mysql_query("SELECT latitudeDecimalDegrees, latitudeHemisphere, longitudeDecimalDegrees, longitudeHemisphere FROM geo_distance WHERE bem = $bemId AND tipo = 'I'", $con);
							if(mysql_num_rows($resGeoDistance) > 0){
								$update = mysql_query("UPDATE geo_distance SET latitudeDecimalDegrees = '$latitudeDecimalDegrees', latitudeHemisphere = '$latitudeHemisphere', longitudeDecimalDegrees = '$longitudeDecimalDegrees', longitudeHemisphere = '$longitudeHemisphere', parou = 'S' WHERE bem =  $bemId AND tipo = 'I'", $con);
								$dataGeoDistance = mysql_fetch_assoc($resGeoDistance);
								$gpsLatAnt = (!strstr($dataGeoDistance['latitudeDecimalDegrees'], "-")) ? gprsToGps($dataGeoDistance['latitudeDecimalDegrees'], $dataGeoDistance['latitudeHemisphere']) : $dataGeoDistance['latitudeDecimalDegrees'] ;
								$gpsLonAnt = (!strstr($dataGeoDistance['longitudeDecimalDegrees'], "-")) ? gprsToGps($dataGeoDistance['longitudeDecimalDegrees'], $dataGeoDistance['longitudeHemisphere']) : $dataGeoDistance['longitudeDecimalDegrees'] ;

								// $gpsLatAnt = gprsToGps($dataGeoDistance['latitudeDecimalDegrees'], $dataGeoDistance['latitudeHemisphere']);	//$dataGeoDistance['latitudeDecimalDegrees'];
								// $gpsLonAnt = gprsToGps($dataGeoDistance['longitudeDecimalDegrees'], $dataGeoDistance['longitudeHemisphere']);	//$dataGeoDistance['longitudeDecimalDegrees'];
								if($gpsLatAnt != $gpsLat) {
									if($gpsLatAnt != 0 && $gpsLonAnt != 0){
										
										$geoDistance = distance($gpsLatAnt, $gpsLonAnt, $gpsLat, $gpsLon);
										//$strDistance = $json->rows[0]->elements[0]->distance->value;
										$distance = (int) $geoDistance;//(int)($geoDistance*1000)
										//echo "******************************IMEI: ". $imei ." | Lat/Long Antiga: ". $gpsLatAnt . ", ". $gpsLonAnt ." | Lat/Long Atual: ". $gpsLat .", ". $gpsLon ." | Geodistance : ". $geoDistance ." | Inteiro: ". $distance ."<br>";
				
										$alertaACada = $dataBem['alerta_hodometro'];
										$alertaACadaSaldo = $dataBem['alerta_hodometro_saldo'];
										$alertaACadaSaldo = $alertaACadaSaldo - $distance;
										
										if($alertaACadaSaldo <= 0 && $alertaACada > 0){
											$msg = $texto_sms_alerta_hodometro;
											$msg = str_replace("#CLIENTE", $dataCliente['nome'], $msg);
											$msg = str_replace("#VEICULO", $dataBem['name'], $msg);
											$msg = str_replace("#HODOMETRO", $alertaACada, $msg);
											$msg = str_replace(' ', '+', $msg);
											//sendSMS($dataCliente['celular'], $msg, '');
											$msgEmail = "<p><b>Quilometragem atingida: </b></p><br><p>O veículo ". $dataBem['name'] ." atingiu a quilometragem de ". $dataBem['hodometro'] ."Km rodados às ". date("H:i:s") ." do dia ". date("d/m/Y") . ".</p><br><i>Equipe InovarSat</i>";
											//echo $msgEmail;
											// enviaEmail($dataCliente['email'], 'Hodômetro - Quilometragem Atingida', $msgEmail);
											$alertaACadaSaldo = $alertaACada;
										}
										// $alertaACadaSaldo = (int)$alertaACadaSaldo;//(int)$alertaACadaSaldo/1000
									}
								}
							}
						}
					}catch(Exception $e){
						mysql_query("INSERT INTO controle (texto) VALUES ($e->getMessage())", $con);
					}
					if(!empty($latitudeDecimalDegrees)){
						mysql_query("INSERT INTO gprmc (date, imei, phone, satelliteFixStatus, latitudeDecimalDegrees, latitudeHemisphere, longitudeDecimalDegrees, longitudeHemisphere, speed, infotext, gpsSignalIndicator, km_rodado, converte, ligado) VALUES (now(), '$imei', '$phone', '$satelliteFixStatus', '$latitudeDecimalDegrees', '$latitudeHemisphere', '$longitudeDecimalDegrees', '$longitudeHemisphere', '$speed', '$infotext', '$gpsSignalIndicator', $distance, 0, '$ligado')", $con);
					}
				
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
					$resLocAtual = mysql_query("SELECT id, latitudeDecimalDegrees, latitudeHemisphere, longitudeDecimalDegrees, longitudeHemisphere FROM loc_atual WHERE imei = '$imei' LIMIT 1", $con);
					$numRows = mysql_num_rows($resLocAtual);
					
					if($numRows == 0){
						mysql_query("INSERT INTO loc_atual (date, imei, phone, satelliteFixStatus, latitudeDecimalDegrees, latitudeHemisphere, longitudeDecimalDegrees, longitudeHemisphere, speed, infotext, gpsSignalIndicator, converte, ligado) VALUES (now(), '$imei', '$phone', '$satelliteFixStatus', '$latitudeDecimalDegrees', '$latitudeHemisphere', '$longitudeDecimalDegrees', '$longitudeHemisphere', '$speed', '$infotext', '$gpsSignalIndicator', 0, '$ligado')", $con);
					} else {
						mysql_query("UPDATE loc_atual SET date = now(), phone = '$phone', satelliteFixStatus = '$satelliteFixStatus', latitudeDecimalDegrees = '$latitudeDecimalDegrees', latitudeHemisphere = '$latitudeHemisphere', longitudeDecimalDegrees = '$longitudeDecimalDegrees', longitudeHemisphere = '$longitudeHemisphere', speed = '$speed', infotext = '$infotext', gpsSignalIndicator = '$gpsSignalIndicator', converte = 0, ligado = '$ligado' WHERE imei = '$imei'", $con);
					}
					if(!empty($latitudeDecimalDegrees)){
						mysql_query("INSERT INTO gprmc (date, imei, phone, satelliteFixStatus, latitudeDecimalDegrees, latitudeHemisphere, longitudeDecimalDegrees, longitudeHemisphere, speed, infotext, gpsSignalIndicator, km_rodado, converte, ligado) VALUES (now(), '$imei', '$phone', '$satelliteFixStatus', '$latitudeDecimalDegrees', '$latitudeHemisphere', '$longitudeDecimalDegrees', '$longitudeHemisphere', '$speed', '$infotext', '$gpsSignalIndicator', 0, 0, '$ligado')", $con);
					}
					mysql_query("UPDATE bem set date = now(), status_sinal = 'S' WHERE imei = '$imei'", $con);
				}
				
				if(!empty($ligado)){
					mysql_query("UPDATE bem SET ligado = '$ligado' where imei = '$imei'", $con);
				}
				
				
				# Now check to see if we need to send any alerts.
				if ($infotext != "tracker") {
					$msg = $texto_sms_alerta;
					$msg = str_replace("#CLIENTE", $dataCliente['nome'], $msg);
					$msg = str_replace("#VEICULO", $dataBem['name'], $msg);
				
					$res = mysql_query("SELECT responsible FROM bem WHERE imei='$imei'", $con);
					while($data = mysql_fetch_assoc($res)) {
						switch ($infotext) {
							case "dt":
								if (!checkAlerta($imei, 'Rastreador Desat.')) {
									$body = "Disable Track OK";
									$msg = str_replace("#TIPOALERTA", "Rastreador Desabilitado", $msg);
									mysql_query("INSERT INTO message (imei, message) VALUES ('$imei', 'Rastreador Desat.')", $con);
								}
							break;
							
							case "et":
								if (!checkAlerta($imei, 'Alarme Parado')) {
									$body = "Stop Alarm OK";
									$msg = str_replace("#TIPOALERTA", "Alarme parado", $msg);
									mysql_query("INSERT INTO message (imei, message) VALUES ('$imei', 'Alarme Parado')", $con);
								}
							break;

							case "gt";
								if (!checkAlerta($imei, 'Alarme Movimento')) {
									$body = "Move Alarm set OK";
									$msg = str_replace("#TIPOALERTA", "Alarme de Movimento ativado", $msg);
									mysql_query("INSERT INTO message (imei, message) VALUES ('$imei', 'Alarme Movimento')", $con);	
								}
							break;
							
							case "help me":
								if (!checkAlerta($imei, 'SOS!')) {
									$body = "Help!";
									mysql_query("INSERT INTO message (imei, message) VALUES ('$imei', 'SOS!')", $con);
									$msg = str_replace("#TIPOALERTA", "SOS", $msg);
								}
								
								//Envia comando de resposta: alerta recebido
								//$send_cmd = "**,imei:". $conn_imei .",E";
								//socket_send($socket, $send_cmd, strlen($send_cmd), 0);
								//printLog($fh, "Comando de resposta (help me): " . $send_cmd . " imei: " . $conn_imei);									
							break;
							
							case "ht":
								if (!checkAlerta($imei, 'Alarme Velocidade')) {
									$body = "Speed alarm set OK";
									$msg = str_replace("#TIPOALERTA", "Alarme de velocidade ativado", $msg);
									mysql_query("INSERT INTO message (imei, message) VALUES ('$imei', 'Alarme Velocidade')", $con);
								}
							break;

							case "it":
								$body = "Timezone set OK";
							break;
							
							case "low battery":
								if (!checkAlerta($imei, 'Bateria Fraca')) {
									$body = "Low battery!\nYou have about 2 minutes...";
									$msg = str_replace("#TIPOALERTA", "Bateria fraca, voce tem 2 minutos", $msg);
									mysql_query("INSERT INTO message (imei, message) VALUES ('$imei', 'Bateria Fraca')", $con);
								}
								//Envia comando de resposta: alerta recebido
								$send_cmd = "**,imei:". $conn_imei .",E";
								socket_send($socket, $send_cmd, strlen($send_cmd), 0);
								//printLog($fh, "Comando de resposta (low battery): " . $send_cmd . " imei: " . $conn_imei);
							break;

							case "move":
								if (!checkAlerta($imei, 'Movimento')) {
									$body = "Move Alarm!";
									$msg = str_replace("#TIPOALERTA", "Seu veiculo esta em movimento", $msg);
									mysql_query("INSERT INTO message (imei, message) VALUES ('$imei', 'Movimento')", $con);
								}
								//Envia comando de resposta: alerta recebido
								$send_cmd = "**,imei:". $conn_imei .",E";
								socket_send($socket, $send_cmd, strlen($send_cmd), 0);
								//printLog($fh, "Comando de resposta (move): " . $send_cmd . " imei: " . $conn_imei);
							break;

							case "nt":
								$body = "Returned to SMS mode OK";
							break;

							case "speed":
								if (!checkAlerta($imei, 'Velocidade')) {
									$body = "Speed alarm!";
									$msg = str_replace("#TIPOALERTA", "Seu veiculo ultrapassou o limite de velocidade", $msg);
									mysql_query("INSERT INTO message (imei, message) VALUES ('$imei', 'Velocidade')", $con);
								}
								//Envia comando de resposta: alerta recebido
								$send_cmd = "**,imei:". $conn_imei .",E";
								socket_send($socket, $send_cmd, strlen($send_cmd), 0);
								//printLog($fh, "Comando de resposta (speed): " . $send_cmd . " imei: " . $conn_imei);
							break;
							
							case "stockade":
								if (!checkAlerta($imei, 'Cerca')) {
									$body = "Geofence Violation!";
									$msg = str_replace("#TIPOALERTA", "Seu veiculo saiu da cerca virtual", $msg);
									mysql_query("INSERT INTO message (imei, message) VALUES ('$imei', 'Cerca')", $con);
								}
								//Envia comando de resposta: alerta recebido
								$send_cmd = "**,imei:". $conn_imei .",E";
								socket_send($socket, $send_cmd, strlen($send_cmd), 0);
								//printLog($fh, "Comando de resposta (stockade): " . $send_cmd . " imei: " . $conn_imei);
							break;

							case "door alarm":
								if (!checkAlerta($imei, 'Porta')) {
									$body = "Open door!";
									mysql_query("INSERT INTO message (imei, message) VALUES ('$imei', 'Porta')", $con);
								}
								//Envia comando de resposta: alerta recebido
								$send_cmd = "**,imei:". $conn_imei .",E";
								socket_send($socket, $send_cmd, strlen($send_cmd), 0);
								//printLog($fh, "Comando de resposta (door alarm): " . $send_cmd . " imei: " . $conn_imei);
							break;

							case "acc alarm":
								if (!checkAlerta($imei, 'Alarme Disparado')) {
									$body = "ACC alarm!";
									$msg = str_replace("#TIPOALERTA", "Alarme disparado", $msg);
									mysql_query("INSERT INTO message (imei, message) VALUES ('$imei', 'Alarme Disparado')", $con);
								}
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

						/*$headers = "From: $email_from" . "\r\n" . "Reply-To: $email_from" . "\r\n";
						$responsible = $data['responsible'];
						$rv = mail($responsible, "Tracker - $imei", $body, $headers);*/

						if (isset($body)) {
							enviaEmail($dataCliente['email'], "Rastreador - $imei", $body);
						}
				
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
		
		mysql_query("UPDATE bem SET serial_tracker = '$serial' WHERE imei = '$imei'", $con);
		
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


function GetCrc16($pData, $nLength) {
  $crctab16 = array(
    0X0000, 0X1189, 0X2312, 0X329B, 0X4624, 0X57AD, 0X6536, 0X74BF,
    0X8C48, 0X9DC1, 0XAF5A, 0XBED3, 0XCA6C, 0XDBE5, 0XE97E, 0XF8F7,
    0X1081, 0X0108, 0X3393, 0X221A, 0X56A5, 0X472C, 0X75B7, 0X643E,
    0X9CC9, 0X8D40, 0XBFDB, 0XAE52, 0XDAED, 0XCB64, 0XF9FF, 0XE876,
    0X2102, 0X308B, 0X0210, 0X1399, 0X6726, 0X76AF, 0X4434, 0X55BD,
    0XAD4A, 0XBCC3, 0X8E58, 0X9FD1, 0XEB6E, 0XFAE7, 0XC87C, 0XD9F5,
    0X3183, 0X200A, 0X1291, 0X0318, 0X77A7, 0X662E, 0X54B5, 0X453C,
    0XBDCB, 0XAC42, 0X9ED9, 0X8F50, 0XFBEF, 0XEA66, 0XD8FD, 0XC974,
    0X4204, 0X538D, 0X6116, 0X709F, 0X0420, 0X15A9, 0X2732, 0X36BB,
    0XCE4C, 0XDFC5, 0XED5E, 0XFCD7, 0X8868, 0X99E1, 0XAB7A, 0XBAF3,
    0X5285, 0X430C, 0X7197, 0X601E, 0X14A1, 0X0528, 0X37B3, 0X263A,
    0XDECD, 0XCF44, 0XFDDF, 0XEC56, 0X98E9, 0X8960, 0XBBFB, 0XAA72,
    0X6306, 0X728F, 0X4014, 0X519D, 0X2522, 0X34AB, 0X0630, 0X17B9,
    0XEF4E, 0XFEC7, 0XCC5C, 0XDDD5, 0XA96A, 0XB8E3, 0X8A78, 0X9BF1,
    0X7387, 0X620E, 0X5095, 0X411C, 0X35A3, 0X242A, 0X16B1, 0X0738,
    0XFFCF, 0XEE46, 0XDCDD, 0XCD54, 0XB9EB, 0XA862, 0X9AF9, 0X8B70,
    0X8408, 0X9581, 0XA71A, 0XB693, 0XC22C, 0XD3A5, 0XE13E, 0XF0B7,
    0X0840, 0X19C9, 0X2B52, 0X3ADB, 0X4E64, 0X5FED, 0X6D76, 0X7CFF,
    0X9489, 0X8500, 0XB79B, 0XA612, 0XD2AD, 0XC324, 0XF1BF, 0XE036,
    0X18C1, 0X0948, 0X3BD3, 0X2A5A, 0X5EE5, 0X4F6C, 0X7DF7, 0X6C7E,
    0XA50A, 0XB483, 0X8618, 0X9791, 0XE32E, 0XF2A7, 0XC03C, 0XD1B5,
    0X2942, 0X38CB, 0X0A50, 0X1BD9, 0X6F66, 0X7EEF, 0X4C74, 0X5DFD,
    0XB58B, 0XA402, 0X9699, 0X8710, 0XF3AF, 0XE226, 0XD0BD, 0XC134,
    0X39C3, 0X284A, 0X1AD1, 0X0B58, 0X7FE7, 0X6E6E, 0X5CF5, 0X4D7C,
    0XC60C, 0XD785, 0XE51E, 0XF497, 0X8028, 0X91A1, 0XA33A, 0XB2B3,
    0X4A44, 0X5BCD, 0X6956, 0X78DF, 0X0C60, 0X1DE9, 0X2F72, 0X3EFB,
    0XD68D, 0XC704, 0XF59F, 0XE416, 0X90A9, 0X8120, 0XB3BB, 0XA232,
    0X5AC5, 0X4B4C, 0X79D7, 0X685E, 0X1CE1, 0X0D68, 0X3FF3, 0X2E7A,
    0XE70E, 0XF687, 0XC41C, 0XD595, 0XA12A, 0XB0A3, 0X8238, 0X93B1,
    0X6B46, 0X7ACF, 0X4854, 0X59DD, 0X2D62, 0X3CEB, 0X0E70, 0X1FF9,
    0XF78F, 0XE606, 0XD49D, 0XC514, 0XB1AB, 0XA022, 0X92B9, 0X8330,
    0X7BC7, 0X6A4E, 0X58D5, 0X495C, 0X3DE3, 0X2C6A, 0X1EF1, 0X0F78,
  );
  $fcs = 0xffff;
  $i = 0;
  while($nLength>0){
    $fcs = ($fcs >> 8) ^ $crctab16[($fcs ^ ord($pData{$i})) & 0xff];
    $nLength--;
    $i++;
  }
  return ~$fcs & 0xffff;
}
?>