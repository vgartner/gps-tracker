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
	$fn = ROOT_URL."/sites/1/logs/" . "Log_". date("dmyhis") .".log";
	$fh = fopen($fn, 'w') or die ("Can not create file");
	$tempstr = "Log Inicio".chr(13).chr(10); 
	fwrite($fh, $tempstr);
}*/

function abrirArquivoLog($imeiLog)
{
	GLOBAL $fh;
	
	//$fn = ".".dirname(__FILE__)."/sites/1/logs/Log_". trim($imeiLog) .".log";
	$fn = "./var/www/html/gps-tracker/sites/1/logs/Log_". trim($imeiLog) .".log";
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

$ip = '172.31.27.135';
// Port
$port =9090;

$command_path = "./var/www/html/gps-tracker/sites/1/";
$from_email = 'cesar@mmasterinformatica.com.br';
$__server_listening = true;

error_reporting(E_ALL);
set_time_limit(0);
ob_implicit_flush();
declare(ticks = 1);
ini_set('sendmail_from', $from_email);

become_daemon();

pcntl_signal(SIGTERM, 'sig_handler');
pcntl_signal(SIGINT, 'sig_handler');
pcntl_signal(SIGCHLD, 'sig_handler');

server_loop($ip, $port);

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

function server_loop($address, $port)
{
    GLOBAL $fh;
    GLOBAL $__server_listening;
	
    if(($sock = socket_create(AF_INET, SOCK_STREAM, 0)) < 0)
    {
        exit();
    }

	if(($ret = socket_bind($sock, $address, $port)) < 0)
	{
		exit();
	}

	if( ( $ret = socket_listen( $sock, 0 ) ) < 0 )
	{
		exit();
	}

	socket_set_nonblock($sock);

	while ($__server_listening)
	{
		$connection = @socket_accept($sock);
		if ($connection === false)
		{
			usleep(100);
		} elseif ($connection > 0) {
			handle_client($sock, $connection);
		} else {
			die;
		}
	}
}

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
	// Variavel que indica se comando está em banco ou arquivo.
	$tipoComando = "banco"; //"arquivo";
	
	//Checando o protocolo
	$isGIMEI = false;
	$isGPRMC = false;

	$send_cmd = "";

	# Read the socket but don't wait for data..
	while (@socket_recv($socket, $rec, 2048, 0x40) !== 0) {
		sleep (1);

		# Timeout the socket if it's not talking...
		# Prevents duplicate connections, confusing the send commands
		$loopcount++;
		if ($loopcount > 120) return;
		$rec = trim($rec);
		//Conecta e pega o comando pendente
		$cnx = mysql_connect("cloudservice.cgejdsdl842e.sa-east-1.rds.amazonaws.com", "gpstracker", "d1$1793689") 
		or die("Could not connect: " . mysql_error());
		mysql_select_db('tracker', $cnx);
		
		if ($rec != "") 
		{
			mysql_close($cnx);
			 /* MODULO GPRMC - COMANDOS A SEREM EXECUTADDOS PARA SUNTECH*/
				if (strpos($rec, "SA200") === true)
	
				$isGPRMC = true;				
				$loopcount = 0;			  
				$parts = explode(';',$rec);
				$cnx = mysql_connect("cloudservice.cgejdsdl842e.sa-east-1.rds.amazonaws.com", "gpstracker", "d1$1793689");

				if ($parts[1] != 'Res' and count($parts) >= 17) 
				{					
					$model 						= $parts[0];
					$imei						= $parts[1];
					$satelliteFixStatus 		= 'A';
					$latitudeDecimalDegrees 	= $parts[6];
					$latitudeHemisphere 		= 'S';		
					
					$lgt 						= substr($parts[7],2);
					$lgt1 						= '-';
					 
					$longitudeDecimalDegrees	= "$lgt1$lgt";						
					$longitudeHemisphere 		= 'W';
					$speed 						= $parts[8];
					$gpsSignalIndicator 		= 'F';
					$hodometro 					= $parts[12];
					$ignicao 					= $parts[14];
					$alerta 					= $parts[15];
					
					if( (int)$speed <= 0)
					  $movimento = 'N';
					  else
					  $movimento = 'S';							
					
					$infotext = "gprmc";

					if ($infotext == "")
						$infotext = "gprmc";
					
					$conn_imei = $imei;
					
					abrirArquivoLog($conn_imei);
					printLog($fh, date("d-m-y h:i:sa") . " Connection from $remip:$remport");
					printLog($fh, date("d-m-y h:i:sa") . " Got : $rec");

					mysql_select_db('tracker', $cnx);
					if($gpsSignalIndicator != 'L') {													
						$address = null;
						$phone = '7097';
						
						// hodonetro
						$resBem = mysql_query("SELECT * FROM bem WHERE imei=$imei ORDER BY date DESC LIMIT 1");
						$dataBem = mysql_fetch_assoc($resBem);					
						$resGprmc = mysql_query("SELECT * FROM gprmc WHERE imei=$imei ORDER BY date DESC LIMIT 1");
						$dataGprmc = mysql_fetch_assoc($resGprmc);	
												 			
						$geoDistance = $hodometro - $dataBem['hodometro'];
						$alertaACada = $dataBem['alerta_hodometro'];
						$alertaACadaSaldo = $dataBem['alerta_hodometro_saldo'];
						$alertaACadaSaldo = ($alertaACadaSaldo - $geoDistance);
						$hodometro = $geoDistance + $dataBem['hodometro'];
						$kmRodado = $geoDistance + $dataGprmc['km_rodado'];
						 
						if($alertaACadaSaldo > 0) {
						  mysql_query("UPDATE bem set date = now(), status_sinal = 'R', movimento = '$movimento', hodometro = '$hodometro', alerta_hodometro_saldo = $alertaACadaSaldo WHERE imei = '$imei'", $cnx); 
						} else {
						  mysql_query("UPDATE bem set date = now(), status_sinal = 'R', movimento = '$movimento', hodometro = '$hodometro', alerta_hodometro_saldo = $alertaACada WHERE imei = '$imei'", $cnx);
						  mysql_query("INSERT INTO message (imei, message) VALUES ('$imei', 'Hodometro:')", $cnx);	
						}
						// fim hodometro
						
						// LOC_ATUAL						
						$resLocAtual = mysql_query("select id, latitudeDecimalDegrees, latitudeHemisphere, longitudeDecimalDegrees, longitudeHemisphere from loc_atual where imei = '$imei' limit 1", $cnx);
						$numRows = mysql_num_rows($resLocAtual);
						
						if($numRows == 0){
							mysql_query("INSERT INTO loc_atual (date, imei, phone, satelliteFixStatus, latitudeDecimalDegrees, latitudeHemisphere, longitudeDecimalDegrees, longitudeHemisphere, speed, infotext, gpsSignalIndicator, converte) VALUES (now(), '$imei', '$phone', '$satelliteFixStatus', '$latitudeDecimalDegrees', '$latitudeHemisphere', '$longitudeDecimalDegrees', '$longitudeHemisphere', '$speed', '$infotext', '$gpsSignalIndicator', 0)", $cnx);
						} else {
							mysql_query("UPDATE loc_atual set date = now(), phone = '$phone', satelliteFixStatus = '$satelliteFixStatus', latitudeDecimalDegrees = '$latitudeDecimalDegrees', latitudeHemisphere = '$latitudeHemisphere', longitudeDecimalDegrees = '$longitudeDecimalDegrees', longitudeHemisphere = '$longitudeHemisphere', speed = '$speed', infotext = '$infotext', gpsSignalIndicator = '$gpsSignalIndicator', converte = 0 where imei = '$imei'", $cnx);
						}
						
					   // FIM LOC_ATUAL						
                      if ($ignicao == '000000') {
               	        mysql_query("UPDATE bem set date = now(), ligado = 'N' WHERE imei = '$imei'",$cnx);	
                      } else { 
               	        mysql_query("UPDATE bem set date = now(), ligado = 'S' WHERE imei = '$imei'",$cnx);
                      }
                                          			
						mysql_query("UPDATE bem set date = now(), status_sinal = 'R' WHERE imei = '$imei'",$cnx);
						mysql_query("INSERT INTO gprmc (date, imei, phone, satelliteFixStatus, latitudeDecimalDegrees, latitudeHemisphere, longitudeDecimalDegrees, longitudeHemisphere, speed, infotext, gpsSignalIndicator, address, km_rodado) VALUES (now(), '$imei', '$phone', '$satelliteFixStatus', '$latitudeDecimalDegrees', '$latitudeHemisphere', '$longitudeDecimalDegrees', '$longitudeHemisphere', '$speed', '$infotext', '$gpsSignalIndicator', '$address','$kmRodado')", $cnx);
					} 
					else
						mysql_query("UPDATE bem set date = now(), status_sinal = 'S' WHERE imei = '$imei'",$cnx);
				
					if ($parts[0] === 'SA200ALT')
					{
					   $res = mysql_query("SELECT * FROM bem WHERE imei='$imei'", $cnx);
					   while($data = mysql_fetch_assoc($res)) {
						  switch ($alerta) {
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
							  case "1":
								mysql_query("INSERT INTO message (imei, message) VALUES ('$imei', 'Velocidade: Fora do Limite')", $cnx);
								break;
							  case "2":
								mysql_query("INSERT INTO message (imei, message) VALUES ('$imei', 'Velocidade: Dentro do Limite')", $cnx);
								break;
							  case "5":
								mysql_query("INSERT INTO message (imei, message) VALUES ('$imei', 'Cerca: Fora do Limite')", $cnx);
								break;								
							} //switch
						} //while
					}
					
				} else 
				{
					//GRPMC nao precisa reter a sessao
				}
				
				//No protocolo GPRMC cada nova conexão é um IP. Enviando comando no fim da conexao, após obter os dados.

	if ($conn_imei != "")
		{
			//CERCA
			$consulta = mysql_query("SELECT * FROM geo_fence WHERE imei = '$imei'", $cnx);
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
	
									if( ( count($exp) ) < 5 ) {
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
	
									if ( $result <> $resultCerca And round($speed * 1.852,0) > 0 ) {
										mysql_query("INSERT INTO message (imei, message) VALUES ('$imei', 'Cerca ". $nomeCerca ." Violada!')", $cnx);
									}
								}        //FIM CERCA						
			
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
					//$send_cmd = trim($send_cmd);
					//unlink("$command_path/$conn_imei");
					mysql_query("DELETE FROM command WHERE imei = $conn_imei");
					mysql_query("insert into teste(id,string) values(null, '$send_cmd')", $cnx);
					mysql_close($cnx);					
					printLog($fh, "Comandos do arquivo apagado: " . $send_cmd . " imei: " . $conn_imei);
				}
			// Comando enviado
			//printLog($fh, date("d-m-y h:i:sa") . " Sent: $send_cmd");

				if (file_exists("$command_path$conn_imei")) 
				{
					$send_cmd = file_get_contents("$command_path$conn_imei");
					socket_send($socket, $send_cmd, strlen($send_cmd), 0);
					mysql_query("DELETE FROM command WHERE imei = $conn_imei");
					unlink("$command_path$conn_imei");
					printLog($fh, "Comandos do Banco e Arquivo apagados: " . $send_cmd . " imei: " . $conn_imei);				
				}				
				break;			
			}
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
