#!/usr/bin/php -q
<?php
###################################################################
# tracker is developped with GPL Licence 2.0
#
# GPL License: http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
#
# Developped by : Cyril Feraudeit
# Changes by: Andrew Huxtable (andrew@hux.net.au)
# Changes by: Eric Alvim (eric.alvim@gmail.com)
#
###################################################################
# This program is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License
# as published by the Free Software Foundation; either version 2
# of the License, or (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
#    For information : cyril@feraudet.com
####################################################################
# Code has been changed to allow for a stateful connection as well
# as give the ability to send commands to the tracker via web page
###################################################################

//**************************************SUNTECH*******************************
//waiting for system startup
//crontab: @reboot php -q /var/www/server/tracker.php
sleep (30);

/**
  * Listens for requests and forks on each connection
  */

$tipoLog = "arquivo"; // tela //debug log, escreve na tela ou no arquivo de log.

$fh = null;

$remip = null;
$remport = null;

/*if ($tipoLog == "arquivo") {
	//Criando arquivo de log
	$fn = $_SERVER['DOCUMENT_ROOT']."/sites/1/logs/" . "Log_". date("dmyhis") .".log";
	$fh = fopen($fn, 'w') or die ("Can not create file");
	$tempstr = "Log Inicio".chr(13).chr(10); 
	fwrite($fh, $tempstr);
}*/

function abrirArquivoLog($imeiLog)
{
	GLOBAL $fh;
	
	//$fn = ".".dirname(__FILE__)."/sites/1/logs/Log_". trim($imeiLog) .".log";
	$fn = "./var/www/sites/1/logs/Log_". trim($imeiLog) .".log";
	$fn = trim($fn);
	$fh = fopen($fn, 'a') or die ("Can not create file");
	$tempstr = "Log Inicio".chr(13).chr(10); 
	fwrite($fh, $tempstr);	
}

function fecharArquivoLog()
{
	GLOBAL $fh;
	if ($fh != null)
		fclose($fh);
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

function hexToStr($hex)
{
    $string='';
    for ($i=0; $i < strlen($hex)-1; $i+=2)
    {
        $string .= chr(hexdec($hex[$i].$hex[$i+1]));
    }
    return $string;
}

function printLog( $fh, $mensagem )
{
	GLOBAL $tipoLog;
	GLOBAL $fh;
	
    if ($tipoLog == "arquivo")
    {
		//escreve no arquivo
		if ($fh != null)
			fwrite($fh, $mensagem.chr(13).chr(10));
    }
	else 
	{
		//escreve na tela
		echo $mensagem."<br />";
	}
}

// IP Local
$ip = '172.31.27.135';
// Port
$port =9094;
// Path to look for files with commands to send
$command_path = "./var/www/html/gps-tracker/sites/1/";
$from_email = 'brenowd@gmail.com';

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
function change_identity( $uid, $gid )
{
    if( !posix_setgid( $gid ) )
    {
        print "Unable to setgid to " . $gid . "!\n";
        exit;
    }

    if( !posix_setuid( $uid ) )
    {
        print "Unable to setuid to " . $uid . "!\n";
        exit;
    }
}

/*
  * Creates a server socket and listens for incoming client connections
  * @param string $address The address to listen on
  * @param int $port The port to listen on
  */
function server_loop($address, $port)
{
    GLOBAL $fh;
    GLOBAL $__server_listening;
	
	//printLog($fh, "server_looping...");

    if(($sock = socket_create(AF_INET, SOCK_STREAM, 0)) < 0)
    {
		//printLog($fh, "failed to create socket: ".socket_strerror($sock));
        exit();
    }

	if(($ret = socket_bind($sock, $address, $port)) < 0)
	{
		//printLog($fh, "failed to bind socket: ".socket_strerror($ret));
		exit();
	}

	if( ( $ret = socket_listen( $sock, 0 ) ) < 0 )
	{
		//printLog($fh, "failed to listen to socket: ".socket_strerror($ret));
		exit();
	}

	socket_set_nonblock($sock);

	//printLog($fh, "waiting for clients to connect...");

	while ($__server_listening)
	{
		$connection = @socket_accept($sock);
		if ($connection === false)
		{
			usleep(100);
		} elseif ($connection > 0) {
			handle_client($sock, $connection);
		} else {
			//printLog($fh, "error: ".socket_strerror($connection));
			die;
		}
	}
}

/**
* Signal handler
*/
function sig_handler($sig)
{
	switch($sig)
	{
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
function handle_client($ssock, $csock)
{
	GLOBAL $__server_listening;
	GLOBAL $fh;
	GLOBAL $firstInteraction;
	
	GLOBAL $remip;
	GLOBAL $remport;

	$pid = pcntl_fork();

	if ($pid == -1)
	{
		/* fork failed */
		//printLog($fh, "fork failure!");
		die;
	} elseif ($pid == 0)
	{
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
		
	} else
	{
		socket_close($csock);
	}
}

function interact($socket)
{
	GLOBAL $fh;
	GLOBAL $command_path;
	GLOBAL $firstInteraction;
	
	GLOBAL $remip;
	GLOBAL $remport;	

	$loopcount = 0;
	$conn_imei = "";
	/* TALK TO YOUR CLIENT */
	$rec = "";
	// Variavel que indica se comando estÃ¡ em banco ou arquivo.
	$tipoComando = "banco"; //"arquivo";
	
	//Checando o protocolo
	$isGIMEI = false;
	$isGPRMC = false;
	
	$send_cmd = "";

	# Read the socket but don't wait for data..
	while (@socket_recv($socket, $rec, 2048, 0x40) !== 0) {
	# If we know the imei of the phone and there is a pending command send it.
   
	# Some pacing to ensure we don't split any incoming data.
		sleep (1);

		# Timeout the socket if it's not talking...
		# Prevents duplicate connections, confusing the send commands
		$loopcount++;
		if ($loopcount > 120) return;

		#remove any whitespace from ends of string.
		$rec = trim($rec);
		
		//Conecta e pega o comando pendente
		$cnx = mysql_connect("cloudservice.cgejdsdl842e.sa-east-1.rds.amazonaws.com", "gpstracker", "d1$1793689") 
		or die("Could not connect: " . mysql_error());
		mysql_select_db('tracker', $cnx);
		
		//mysql_select_db('tracker', $cnx);
		if ($rec != "" and $rec != '?') 
		{
			mysql_close($cnx);
			if (strpos($rec, "$") === true)
				$isGPRMC = true;
				$loopcount = 0;
				$parts = explode(',',$rec);			
				$cnx = mysql_connect("localhost", "gpstracker", "d1$1793689");
				
				if ($parts[1] !== "" and $parts[0] !== "?") 
				{	
				  if(strpos($rec, 'CEL') == true) {
				  	$imei										= substr($parts[0],1);
				  	$satelliteFixStatus			= 'A';
				  	$latitude 							= substr($parts[2],1);
				  	$latitudeHemisphere			= 'S';
				  	$longitude 							=  substr($parts[3],2);
				  	$longitudeHemisphere		= 'W';
				  	$speed 									= $parts[4];
				  	$gpsSignalIndicator 		= 'F';
				  	$infotext           		= "tracker";
				  	$ignicao 								=	$parts[14];

				  	$latitudeDecimalDegrees 	= "-$latitude";
				  	$longitudeDecimalDegrees	= "-$longitude";
				  } else {
				  	$imei									= substr($parts[0],1);
				  	$satelliteFixStatus 	= 'A';
				  	$latitude 						= substr($parts[1],1);
				  	$latitudeHemisphere 	= 'S';
				  	$longitude 						=  substr($parts[2],1);
				  	$longitudeHemisphere 	= 'W';
				  	$speed 								= $parts[3];
				  	$gpsSignalIndicator 	= 'F';
				  	$infotext           	= "tracker";
				  	$ignicao 							=	$parts[13];

				  	$latitudeDecimalDegrees  	= "$latitude";
				  	$longitudeDecimalDegrees 	= "$longitude";

				  	// Calculo das coordenadas. Convertendo coordenadas do modo GPRS para GPS
				  	strlen($latitudeDecimalDegrees) == 9 && $latitudeDecimalDegrees = '0'.$latitudeDecimalDegrees;
				  	$g = substr($latitudeDecimalDegrees,0,3);
				  	$d = substr($latitudeDecimalDegrees,3);
				  	$latitudeDecimalDegrees = $g + ($d/60);
				  	$latitudeHemisphere == "S" && $latitudeDecimalDegrees = $latitudeDecimalDegrees * -1;
				  	strlen($longitudeDecimalDegrees) == 9 && $longitudeDecimalDegrees = '0'.$longitudeDecimalDegrees;
				  	$g = substr($longitudeDecimalDegrees,0,3);
				  	$d = substr($longitudeDecimalDegrees,3);
				  	$longitudeDecimalDegrees = $g + ($d/60);
				  	$longitudeHemisphere == "W" && $longitudeDecimalDegrees = $longitudeDecimalDegrees * -1;
				  }
				
				if ($ignicao == 0){
					  $ligado = 'N';
					  } else {
					  	$ligado = 'S';
					}
					
				if ($infotext == "")
				  $infotext = "tracker";	
				  $conn_imei = $imei;
				  	
				  	abrirArquivoLog($conn_imei);
				   printLog($fh, date("d-m-y h:i:sa") . " Connection from $remip:$remport");
					printLog($fh, date("d-m-y h:i:sa") . " Got : $rec");

					mysql_select_db('tracker', $cnx);
					if($gpsSignalIndicator != 'L') {
						$address = null;
						$phone = '7097';

						$resLocAtual = mysql_query("select id, latitudeDecimalDegrees, latitudeHemisphere, longitudeDecimalDegrees, longitudeHemisphere from loc_atual where imei = '$imei' limit 1", $cnx);
						$numRows = mysql_num_rows($resLocAtual);
						
						if($numRows == 0){
							mysql_query("INSERT INTO loc_atual (date, imei, phone, satelliteFixStatus, latitudeDecimalDegrees, latitudeHemisphere, longitudeDecimalDegrees, longitudeHemisphere, speed, infotext, gpsSignalIndicator, converte) VALUES (now(), '$imei', '$phone', '$satelliteFixStatus', '$latitudeDecimalDegrees', '$latitudeHemisphere', '$longitudeDecimalDegrees', '$longitudeHemisphere', '$speed', '$infotext', '$gpsSignalIndicator', 0)", $cnx);
						} else {
							mysql_query("UPDATE loc_atual set date = now(), phone = '$phone', satelliteFixStatus = '$satelliteFixStatus', latitudeDecimalDegrees = '$latitudeDecimalDegrees', latitudeHemisphere = '$latitudeHemisphere', longitudeDecimalDegrees = '$longitudeDecimalDegrees', longitudeHemisphere = '$longitudeHemisphere', speed = '$speed', infotext = '$infotext', gpsSignalIndicator = '$gpsSignalIndicator', converte = 0 where imei = '$imei'", $cnx);
						}
						/* MUDA O STATUS LIGADO / DESLIGADO*/						
                  if ($ignicao == '0') {
                  	mysql_query("UPDATE bem set date = now(), ligado = 'N' WHERE imei = '$imei'",$cnx);	
                  } else { 
               	   mysql_query("UPDATE bem set date = now(), ligado = 'S' WHERE imei = '$imei'",$cnx);
                  }
                                          			
						mysql_query("UPDATE bem set date = now(), status_sinal = 'R' WHERE imei = '$imei'",$cnx);
						mysql_query("INSERT INTO gprmc (date, imei, phone, satelliteFixStatus, latitudeDecimalDegrees, latitudeHemisphere, longitudeDecimalDegrees, longitudeHemisphere, speed, infotext, gpsSignalIndicator, address, ligado) VALUES (now(), '$imei', '$phone', '$satelliteFixStatus', '$latitudeDecimalDegrees', '$latitudeHemisphere', '$longitudeDecimalDegrees', '$longitudeHemisphere', '$speed', '$infotext', '$gpsSignalIndicator', '$address', '$ligado')", $cnx);
					} 
					else
						mysql_query("UPDATE bem set date = now(), status_sinal = 'S' WHERE imei = '$imei'",$cnx);
				
					# Now check to see if we need to send any alerts.
					if (trim($infotext) != "gprmc")
					{
					   $res = mysql_query("SELECT * FROM bem WHERE imei='$imei'", $cnx);
					   while($data = mysql_fetch_assoc($res)) {
						  switch ($infotext) {
							  case "dt":
								$body = "Disable Track OK";
								break;
							  case "et":
								$body = "Stop Alarm OK";
								break;
							  case "gt";
								$body = "Move Alarm set OK";
								break;
							  case "help me":
								$body = "Help!";
								mysql_query("INSERT INTO message (imei, message) VALUES ('$imei', 'SOS!')", $cnx);
								break;
							  case "ht":
								$body = "Speed alarm set OK";
								break;
							  case "it":
								$body = "Timezone set OK";
								break;
							  case "low battery":
								$body = "Low battery!\nYou have about 2 minutes...";
								mysql_query("INSERT INTO message (imei, message) VALUES ('$imei', 'Bat. Fraca')", $cnx);
								break;
							  case " bat:":
								$body = "Low battery!\nYou have about 2 minutes...";
								mysql_query("INSERT INTO message (imei, message) VALUES ('$imei', 'Bat. Fraca')", $cnx);
								break;
							  case "Low batt":
								$body = "Low battery!\nYou have about 2 minutes...";
								mysql_query("INSERT INTO message (imei, message) VALUES ('$imei', 'Bat. Fraca')", $cnx);
								break;
							  case "move":
								$body = "Move Alarm!";
								mysql_query("INSERT INTO message (imei, message) VALUES ('$imei', 'Movimento')", $cnx);
								break;
							  case "nt":
								$body = "Returned to SMS mode OK";
								break;
							  case "speed":
								$body = "Speed alarm!";
								mysql_query("INSERT INTO message (imei, message) VALUES ('$imei', 'Velocidade')", $cnx);
								break;
							  case "stockade":
								$body = "Geofence Violation!";
								mysql_query("INSERT INTO message (imei, message) VALUES ('$imei', 'Cerca')", $cnx);
								break;
							} //switch
														
							//Enviando e-mail de alerta
							//$headers = "From: $email_from" . "\r\n" . "Reply-To: $email_from" . "\r\n";
							//$responsible = $data['responsible'];
							//$rv = mail($responsible, "Tracker - $imei", $body, $headers);

						} //while
					}
				} else {
					//GRPMC nao precisa reter a sessao
				}
				
				//No protocolo GPRMC cada nova conexÃ£o Ã© um IP. Enviando comando no fim da conexao, apÃ³s obter os dados.
				
				if ($conn_imei != "")
				{
					if ($tipoComando == "banco")
					{
						//Conecta e pega o comando pendente
						$cnx = mysql_connect("cloudservice.cgejdsdl842e.sa-east-1.rds.amazonaws.com", "gpstracker", "d1$1793689")
						or die("Could not connect: " . mysql_error());
						mysql_select_db('tracker', $cnx);
						$res = mysql_query("SELECT c.command FROM command c WHERE c.imei = '$conn_imei' ORDER BY date DESC LIMIT 1");
						while($data = mysql_fetch_assoc($res))
						{
							$send_cmd = $data['command'];
							socket_send($socket, $send_cmd, strlen($send_cmd), 0);
						}
						// Deletando comando
						//$send_cmd = trim($send_cmd);
						//unlink("$command_path/$conn_imei");
						mysql_query("DELETE FROM command WHERE imei = $conn_imei");
						mysql_query("insert into teste(id,string) values(null, '$send_cmd')", $cnx);
						mysql_close($cnx);
						printLog($fh, "Comandos do arquivo apagado: " . $send_cmd . " imei: " . $conn_imei);
					}
					// Comando enviado
					//printLog($fh, date("d-m-y h:i:sa") . " Sent: $send_cmd");
				}

//				if (file_exists("$command_path$conn_imei")) 
//				{
//					$send_cmd = file_get_contents("$command_path$conn_imei");
//					socket_send($socket, $send_cmd, strlen($send_cmd), 0);
//					//mysql_query("DELETE FROM command WHERE imei = $conn_imei");
//					unlink("$command_path$conn_imei");
//					printLog($fh, "Comandos do Banco e Arquivo apagados: " . $send_cmd . " imei: " . $conn_imei);				
//				}
				

/*
				$cnx = mysql_connect("cloudservice.cgejdsdl842e.sa-east-1.rds.amazonaws.com", "gpstracker", "d1$1793689") 
						  or die("Could not connect: " . mysql_error());
				mysql_select_db('tracker', $cnx);
				//$res = mysql_query("SELECT c.command FROM command c WHERE c.command like '**,imei:". $conn_imei .",C,%' and c.imei = $conn_imei ORDER BY date DESC LIMIT 1");
				$res = mysql_query("SELECT c.command FROM command c WHERE c.imei = '$conn_imei' ORDER BY date DESC LIMIT 1");
				while($data = mysql_fetch_assoc($res))
					{
						$send_cmd = $data['command'];
					
						
						
						socket_send($socket, $send_cmd, strlen($send_cmd), 0);
						mysql_query("DELETE FROM command WHERE imei = $conn_imei");
					
					}


				mysql_close($cnx);

*/

				
				break;

		}
		
		//Checando se utilizou os dois protocolos para uma escuta
		if ($isGIMEI == true and $isGPRMC == true) 
		{
			//printLog($fh, "ATENCAO: falha na obtencao do protocolo. Kill pid.");
		}
		

		$rec = "";
	} //while

} //fim interact

/**
  * Become a daemon by forking and closing the parent
  */
function become_daemon()
{
    GLOBAL $fh;

	//printLog($fh, "pcntl_fork() in");
    $pid = pcntl_fork();
	//printLog($fh, "pcntl_fork() out");

    if ($pid == -1)
    {
        /* fork failed */
		//printLog($fh, "fork failure!");
        exit();
    } elseif ($pid)
    {
		//printLog($fh, "pid: " . $pid);
        /* close the parent */
        exit();
    } else
    {
        /* child becomes our daemon */
        posix_setsid();
        chdir('/');
        umask(0);
        return posix_getpid();
    }

	//printLog($fh, "become_daemon() fim");
}

?>
