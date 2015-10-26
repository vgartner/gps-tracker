<?php
$rec = $_GET['rec'];
$cnx = mysql_connect("cloudservice.cgejdsdl842e.sa-east-1.rds.amazonaws.com", "gpstracker", "d1$1793689") ;
if (strpos($rec, "GPRMC") === false) {
			
				/* M�DULO IMEI GEN�RICO */
				
				$isGIMEI = true;
			
				$loopcount = 0;
			  
				if ($fh != null)
					printLog($fh, date("d-m-y h:i:sa") . " Got : $rec");
			    
				$parts = explode(',',$rec);
				if (strpos($parts[0], "#") === FALSE) {
					
					/* Andrew's tracker is different....
					Array
					(       
						[0] => imei:354779030525274
						[1] => tracker
						[2] => 0909221022
						[3] => +61417801658
						[4] => F
						[5] => 022234.000
						[6] => A
						[7] => 3506.5232
						[8] => S
						[9] => 13829.5988
						[10] => E
						[11] => 0.00
						[12] => 
					)
					*/
					# $imei                       = substr($parts[0],0, -1);
					# Only worry about the rest if there is data to get
					if (count($parts) > 1) {
					  $imei			  			  = substr($parts[0],5);
					  $infotext			  		  = mysql_real_escape_string($parts[1]);
					  $trackerdate                = mysql_real_escape_string($parts[2]);
					  $gpsSignalIndicator         = mysql_real_escape_string($parts[4]);
					  $speed = 0;
					  
					  //Se gpsSignalIndicator <> L, pega o outros dados
					  if ($gpsSignalIndicator != 'L') {
						  $phone                      = mysql_real_escape_string($parts[3]);
						  $satelliteFixStatus         = mysql_real_escape_string($parts[6]);					  
						  $latitudeDecimalDegrees     = mysql_real_escape_string($parts[7]);
						  $latitudeHemisphere         = mysql_real_escape_string($parts[8]);
						  $longitudeDecimalDegrees    = mysql_real_escape_string($parts[9]);
						  $longitudeHemisphere        = mysql_real_escape_string($parts[10]);
						  $speed                      = mysql_real_escape_string($parts[11]);
					  }
					  
					  //inicio verifica geofence
					  mysql_select_db('tracker', $cnx);
					  
						if ( $imei != "" ) {
					  
						$consulta = mysql_query("SELECT * FROM geo_fence WHERE imei = '$imei'", $cnx);
							while($data = mysql_fetch_assoc($consulta)) {

								$idCerca = $data['id'];
								$imeiCerca = $data['imei'];
								$nomeCerca = $data['nome'];
								$coordenadasCerca = $data['coordenadas'];
								$resultCerca = $data['tipo'];
								$tipoEnvio = $data['tipoEnvio'];
								
								strlen($latitudeDecimalDegrees) == 9 && $latitudeDecimalDegrees = '0'.$latitudeDecimalDegrees;
								$g = substr($latitudeDecimalDegrees,0,3);
								$d = substr($latitudeDecimalDegrees,3);
								$strLatitudeDecimalDegrees = $g + ($d/60);
								$latitudeHemisphere == "S" && $strLatitudeDecimalDegrees = $strLatitudeDecimalDegrees * -1;

								strlen($longitudeDecimalDegrees) == 9 && $longitudeDecimalDegrees = '0'.$longitudeDecimalDegrees;
								$g = substr($longitudeDecimalDegrees,0,3);
								$d = substr($longitudeDecimalDegrees,3);
								$strLongitudeDecimalDegrees = $g + ($d/60);
								$longitudeHemisphere == "S" && $strLongitudeDecimalDegrees = $strLongitudeDecimalDegrees * -1;

								$strLongitudeDecimalDegrees = $strLongitudeDecimalDegrees * -1;

								$lat_point = $strLatitudeDecimalDegrees;
								$lng_point = $strLongitudeDecimalDegrees;

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

									if ( $tipoEnvio == 0 ) {

										# Convert the GPS coordinates to a human readable address
										$tempstr = "http://maps.google.com/maps/geo?q=$lat_point,$lng_point&oe=utf-8&sensor=true&key=ABQIAAAAFd56B-wCWVpooPPO7LR3ihTz-K-sFZ2BISbybur6B4OYOOGbdRShvXwdlYvbnwC38zgCx2up86CqEg&output=csv"; //output = csv, xml, kml, json
										$rev_geo_str = file_get_contents($tempstr);
										$rev_geo_str = preg_replace("/\"/","", $rev_geo_str);
										$rev_geo = explode(',', $rev_geo_str);
										$logradouro = $rev_geo[2] .",". $rev_geo[3] ;

										require "lib/class.phpmailer.php";

										$consulta1 = mysql_query("SELECT a.*, b.* FROM cliente a INNER JOIN bem b ON (a.id = b.cliente) WHERE b.imei = '$imei'", $cnx);
										while($data = mysql_fetch_assoc($consulta1)) {

											$emailDestino = $data['email'];
											$nameBem = $data['name'];
											$mensagem = "O veiculo ". $nameBem .", esta ". $situacao ." do perimetro ". $nomeCerca .", as ". date("H:i:s") ." do dia ". date("d/m/Y") .", no local ". $logradouro ." e trafegando a ". round($speed * 1.852, 0) ." km/h.";
													
											$msg = "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.0 Transitional//EN\">";
											$msg .= "<html>";
											$msg .= "<head></head>";
											$msg .= "<body style=\"background-color:#fff;\" >";
											$msg .= "<p><strong>Alerta de Violacao de Perimetro:</strong><br /><br />";
											$msg .= $mensagem ."<br /><br />";
											$msg .= "Equipe BarukSat<br />";
											$msg .= "(85)88462069<br />";
											$msg .= "<a href=\"http://www.systemtracker.com.br\">www.systemtracker.com.br</a></p>";
											$msg .= "</body>";
											$msg .= "</html>";

											$mail = new PHPMailer();
											$mail->Mailer = "smtp";
											$mail->IsHTML(true);
											$mail->CharSet = "utf-8";
											$mail->SMTPSecure = "tls";
											$mail->Host = "smtp.gmail.com";
											$mail->Port = "587";
											$mail->SMTPAuth = "true";
											$mail->Username = "paccelli.rocha";
											$mail->Password = "sua_senha";
											$mail->From = "paccelli.rocha@gmail.com";
											$mail->FromName = "BarukSat";
											$mail->AddAddress($emailDestino);
											$mail->AddReplyTo($mail->From, $mail->FromName);
											$mail->Subject = "BarukSat - Alerta de Violacao de Perimetro";
											$mail->Body = $msg;

											if (!$mail->Send()) {
												echo "Erro de envio: ". $mail->ErrorInfo;
											} else {
												echo "Mensagem enviada com sucesso!";
											}
										}
									}
								}
							}
						} // if imei
					//final verifica geofence
					
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
						
						$resLocAtual = mysql_query("select id, latitudeDecimalDegrees, latitudeHemisphere, longitudeDecimalDegrees, longitudeHemisphere from loc_atual where imei = '$imei' limit 1", $cnx);
						$numRows = mysql_num_rows($resLocAtual);
						
						if($numRows == 0){
							mysql_query("INSERT INTO loc_atual (date, imei, phone, satelliteFixStatus, latitudeDecimalDegrees, latitudeHemisphere, longitudeDecimalDegrees, longitudeHemisphere, speed, infotext, gpsSignalIndicator) VALUES (now(), '$imei', '$phone', '$satelliteFixStatus', '$latitudeDecimalDegrees', '$latitudeHemisphere', '$longitudeDecimalDegrees', '$longitudeHemisphere', '$speed', '$infotext', '$gpsSignalIndicator')", $cnx);
						} else {
							mysql_query("UPDATE loc_atual set date = now(), phone = '$phone', satelliteFixStatus = '$satelliteFixStatus', latitudeDecimalDegrees = '$latitudeDecimalDegrees', latitudeHemisphere = '$latitudeHemisphere', longitudeDecimalDegrees = '$longitudeDecimalDegrees', longitudeHemisphere = '$longitudeHemisphere', speed = '$speed', infotext = '$infotext', gpsSignalIndicator = '$gpsSignalIndicator' where imei = '$imei'", $cnx);
						}
						
						
						$resBem = mysql_query("select id from bem where imei = '$imei'", $cnx);
						$dataBem = mysql_fetch_assoc($resBem);
						$bemId = $dataBem[id];
						$countGeoDistance = mysql_query("select bem from geo_distance where bem = $bemId", $cnx);
						if($countGeoDistance === false || mysql_num_rows($countGeoDistance) == 0) {
							mysql_query("insert into geo_distance (bem, tipo) values($bemId, 'I')", $cnx);
							mysql_query("insert into geo_distance (bem, tipo) values($bemId, 'F')", $cnx);
						}
						
						$distance = 0;
						
						if($movimento == 'S'){
							$resGeoDistance = mysql_query("select parou from geo_distance where bem = $bemId and tipo = 'I'", $cnx);
							$dataGeoDistance = mysql_fetch_assoc($resGeoDistance);
							if($dataGeoDistance[parou] == 'S' || empty($dataGeoDistance[parou])){
								mysql_query("update geo_distance set latitudeDecimalDegrees = '$latitudeDecimalDegrees', latitudeHemisphere = '$latitudeHemisphere', longitudeDecimalDegrees = '$longitudeDecimalDegrees', longitudeHemisphere = '$longitudeHemisphere', parou = 'N' where bem =  $bemId and tipo = 'I'", $cnx);
							}
						} else {
							$resGeoDistance = mysql_query("select latitudeDecimalDegrees, latitudeHemisphere, longitudeDecimalDegrees, longitudeHemisphere from geo_distance where bem = $bemId and tipo = 'I'", $cnx);
							echo mysql_error($cnx);
							if(mysql_num_rows($resGeoDistance) > 0){
								mysql_query("update geo_distance set latitudeDecimalDegrees = '$latitudeDecimalDegrees', latitudeHemisphere = '$latitudeHemisphere', longitudeDecimalDegrees = '$longitudeDecimalDegrees', longitudeHemisphere = '$longitudeHemisphere', parou = 'S' where bem =  $bemId and tipo = 'I'", $cnx);
								$dataGeoDistance = mysql_fetch_assoc($resGeoDistance);
								$gpsLatAnt = gprsToGps($dataGeoDistance[latitudeDecimalDegrees], $dataGeoDistance[latitudeHemisphere]);
								$gpsLonAnt = gprsToGps($dataGeoDistance[longitudeDecimalDegrees], $dataGeoDistance[longitudeHemisphere]);
							
								$json = json_decode(file_get_contents("http://maps.googleapis.com/maps/api/distancematrix/json?sensor=false&origins=$gpsLatAnt,$gpsLonAnt&destinations=$gpsLat,$gpsLon"));
						
								if(isset($json->rows)){
									$strDistance = $json->rows[0]->elements[0]->distance->value;
									$distance = $strDistance/1000;
								}
							}
						}
						
						mysql_query("UPDATE bem set date = now(), status_sinal = 'R', movimento = '$movimento', hodometro = hodometro+$distance WHERE imei = '$imei'", $cnx);
						mysql_query("INSERT INTO gprmc (date, imei, phone, satelliteFixStatus, latitudeDecimalDegrees, latitudeHemisphere, longitudeDecimalDegrees, longitudeHemisphere, speed, infotext, gpsSignalIndicator, km_rodado) VALUES (now(), '$imei', '$phone', '$satelliteFixStatus', '$latitudeDecimalDegrees', '$latitudeHemisphere', '$longitudeDecimalDegrees', '$longitudeHemisphere', '$speed', '$infotext', '$gpsSignalIndicator', $distance)", $cnx);
						
						if($numRows == 0){
							mysql_query("INSERT INTO loc_atual (date, imei, phone, satelliteFixStatus, latitudeDecimalDegrees, latitudeHemisphere, longitudeDecimalDegrees, longitudeHemisphere, speed, infotext, gpsSignalIndicator) VALUES (now(), '$imei', '$phone', '$satelliteFixStatus', '$latitudeDecimalDegrees', '$latitudeHemisphere', '$longitudeDecimalDegrees', '$longitudeHemisphere', '$speed', '$infotext', '$gpsSignalIndicator')", $cnx);
						} else {
							mysql_query("UPDATE loc_atual set date = now(), phone = '$phone', satelliteFixStatus = '$satelliteFixStatus', latitudeDecimalDegrees = '$latitudeDecimalDegrees', latitudeHemisphere = '$latitudeHemisphere', longitudeDecimalDegrees = '$longitudeDecimalDegrees', longitudeHemisphere = '$longitudeHemisphere', speed = '$speed', infotext = '$infotext', gpsSignalIndicator = '$gpsSignalIndicator' where imei = '$imei'", $cnx);
						}
						
					  } else {
					    mysql_query("UPDATE bem set date = now(), status_sinal = 'S' WHERE imei = '$imei'", $cnx);
					  }
					  
					 # Now check to see if we need to send any alerts.
						if ($infotext != "tracker") {
						   $res = mysql_query("SELECT responsible FROM bem WHERE imei='$imei'", $cnx);
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
									mysql_query("INSERT INTO message (imei, message) VALUES ('$$conn_imei', 'SOS!')", $cnx);
									//Envia comando de resposta: alerta recebido
									$send_cmd = "**,imei:". $conn_imei .",E";
									socket_send($socket, $send_cmd, strlen($send_cmd), 0);
									//printLog($fh, "Comando de resposta (help me): " . $send_cmd . " imei: " . $conn_imei);									
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
									//Envia comando de resposta: alerta recebido
									$send_cmd = "**,imei:". $conn_imei .",E";
									socket_send($socket, $send_cmd, strlen($send_cmd), 0);
									//printLog($fh, "Comando de resposta (low battery): " . $send_cmd . " imei: " . $conn_imei);
									break;
								  case "move":
									$body = "Move Alarm!";
									mysql_query("INSERT INTO message (imei, message) VALUES ('$imei', 'Movimento')", $cnx);
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
									mysql_query("INSERT INTO message (imei, message) VALUES ('$imei', 'Velocidade')", $cnx);
									//Envia comando de resposta: alerta recebido
									$send_cmd = "**,imei:". $conn_imei .",E";
									socket_send($socket, $send_cmd, strlen($send_cmd), 0);
									//printLog($fh, "Comando de resposta (speed): " . $send_cmd . " imei: " . $conn_imei);
									break;
								  case "stockade":
									$body = "Geofence Violation!";
									mysql_query("INSERT INTO message (imei, message) VALUES ('$imei', 'Cerca')", $cnx);
									//Envia comando de resposta: alerta recebido
									$send_cmd = "**,imei:". $conn_imei .",E";
									socket_send($socket, $send_cmd, strlen($send_cmd), 0);
									//printLog($fh, "Comando de resposta (stockade): " . $send_cmd . " imei: " . $conn_imei);
									break;
								  case "door alarm":
									$body = "Open door!";
									mysql_query("INSERT INTO message (imei, message) VALUES ('$imei', 'Porta')", $cnx);
									//Envia comando de resposta: alerta recebido
									$send_cmd = "**,imei:". $conn_imei .",E";
									socket_send($socket, $send_cmd, strlen($send_cmd), 0);
									//printLog($fh, "Comando de resposta (door alarm): " . $send_cmd . " imei: " . $conn_imei);
									break;
								  case "acc alarm":
									$body = "ACC alarm!";
									mysql_query("INSERT INTO message (imei, message) VALUES ('$imei', 'Ignição')", $cnx);
									//Envia comando de resposta: alerta recebido
									$send_cmd = "**,imei:". $conn_imei .",E";
									socket_send($socket, $send_cmd, strlen($send_cmd), 0);
									//printLog($fh, "Comando de resposta (acc alarm): " . $send_cmd . " imei: " . $conn_imei);
									break;
								case "acc aff":
									$body = "ACC alarm!";
									mysql_query("INSERT INTO message (imei, message) VALUES ('$imei', 'Ignição')", $cnx);
									//Envia comando de resposta: alerta recebido
									$send_cmd = "**,imei:". $conn_imei .",E";
									socket_send($socket, $send_cmd, strlen($send_cmd), 0);
									//printLog($fh, "Comando de resposta (acc alarm): " . $send_cmd . " imei: " . $conn_imei);
									break;
								} //switch

								//Enviando e-mail de alerta
								$headers = "From: $email_from" . "\r\n" . "Reply-To: $email_from" . "\r\n";
								$responsible = $data['responsible'];
								$rv = mail($responsible, "Tracker - $imei", $body, $headers);

							} //while
						}
					  mysql_close($cnx);
					} else {
					 /* If we got here, we got an imei ONLY - not even 'imei:' first
						This seems to be some sort of 'keepalive' packet
						The TK-201 is not stateless like the TK-102, it
						needs to retain a session.  Basically, we just reply with 'ON'
						anything else seems to cause the device to reset the connection.
					 */
						@socket_send($socket, "ON", 2, 0);
						
						printLog($fh, date("d-m-y h:i:sa") . " Sent: ON");
					}
				}
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
?>