<?php

include('seguranca.php');
include('usuario/config.php');

$dataInicio = $_POST["txtDataInicio"];
$dataFinal = $_POST["txtDataFinal"];
$imei = $_POST["nrImeiConsulta"];
$nome = $_POST["nomeVeiculo"];
$hrDataInicio = $_POST["hrDataInicio"];
$hrDataFinal = $_POST["hrDataFinal"];
$mnDataInicio = $_POST["mnDataInicio"];
$mnDataFinal = $_POST["mnDataFinal"];

/** Retorna a hora no formato 00:00:00*/
function formataHora($hrEntrada, $mnEntrada)
{
    $hrSaida;
    $mnSaida;
    
    switch($hrEntrada)
    {
        case "0": $hrSaida = "00"; break;
        case "1": $hrSaida = "01"; break;
        case "2": $hrSaida = "02"; break;
        case "3": $hrSaida = "03"; break;
        case "4": $hrSaida = "04"; break;
        case "5": $hrSaida = "05"; break;
        case "6": $hrSaida = "06"; break;
        case "7": $hrSaida = "07"; break;
        case "8": $hrSaida = "08"; break;
        case "9": $hrSaida = "09"; break;
        case "10": $hrSaida = "10"; break;
        case "11": $hrSaida = "11"; break;
        case "12": $hrSaida = "12"; break;
        case "13": $hrSaida = "13"; break;
        case "14": $hrSaida = "14"; break;
        case "15": $hrSaida = "15"; break;
        case "16": $hrSaida = "16"; break;
        case "17": $hrSaida = "17"; break;
        case "18": $hrSaida = "18"; break;
        case "19": $hrSaida = "19"; break;
        case "20": $hrSaida = "20"; break;
        case "21": $hrSaida = "21"; break;
        case "22": $hrSaida = "22"; break;
        case "23": $hrSaida = "23"; break;
    }

    switch($mnEntrada)
    {
        case "00": $mnSaida = ":00:00"; break;
        case "10": $mnSaida = ":10:00"; break;
        case "15": $mnSaida = ":15:00"; break;
        case "20": $mnSaida = ":20:00"; break;
        case "25": $mnSaida = ":25:00"; break;
        case "30": $mnSaida = ":30:00"; break;
        case "35": $mnSaida = ":35:00"; break;
        case "40": $mnSaida = ":40:00"; break;
        case "45": $mnSaida = ":45:00"; break;
        case "50": $mnSaida = ":50:00"; break;
        case "55": $mnSaida = ":55:00"; break;
        case "59": $mnSaida = ":59:59"; break;
    }   
    

    return $hrSaida . $mnSaida;
}

$con = mysql_connect($DB_SERVER, $DB_USER, $DB_PASS);
mysql_select_db($DB_NAME, $con);

$query = "SELECT date as data, imei, latitudeDecimalDegrees, latitudeHemisphere, longitudeDecimalDegrees, longitudeHemisphere, km_rodado, address, speed as velocidade, converte, ligado
						from gprmc where imei = '$imei' 
						and date between '$dataInicio ". formataHora($hrDataInicio, $mnDataInicio) ."' and '$dataFinal ". formataHora($hrDataFinal, $mnDataFinal) ."'
						order by date desc";
$result = mysql_query($query, $con) or die(mysql_error());
?>
<div class="row" id="tracar" style="margin: 0 0 15px 0;">
	<div class="col-xs-12 text-right">
		<button type="button" class="btn btn-default" onclick="imprimirHistorico();"><i class="fa fa-print"></i> Imprimir</button>
		<button type="button" class="btn btn-success" onclick="tracarHistorico();"><i class="fa fa-map-marker"></i> Traçar no Mapa</button>
	</div>
</div>
<div id="areaImpressa">
	<div class="row">
		<div class="col-lg-4"><strong>Veículo: </strong><?=$nome?></div>
		<div class="col-lg-4"><strong>Data Inicial: </strong><?=$dataInicio.' '.formataHora($hrDataInicio, $mnDataInicio)?></div>
		<div class="col-lg-4"><strong>Data Final: </strong><?=$dataFinal.' '.formataHora($hrDataFinal, $mnDataFinal)?></div>
	</div>

	<table class="table table-striped table-hover">
		<thead>
			<tr>
				<th>Data</th>
				<th>Latitude</th>
				<th>Longitude</th>
				<th>Endereço</th>
				<th>Velocidade</th>
				<th>Ligado</th>
			</tr>
		</thead>
		<tbody>
			<?php
				$latAnt = '';
				$lonAnt = '';
				$ligadoAnt = "";

				if (mysql_num_rows($result) !== 0) {
									
					while($data=mysql_fetch_assoc($result)){ 
// CONVERTER GPRS PARA GPS					
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
							}
							else {
								$latitudeDecimalDegrees = $data['latitudeDecimalDegrees'];
								$longitudeDecimalDegrees = $data['longitudeDecimalDegrees'];
							}
							
							$speed = $data['speed'];
							
							// PREVINE ERROS DE COORDENADAS QUE ESTÃO SEM O SINAL NEGATIVO
							if ($latitudeDecimalDegrees > 0) $latitudeDecimalDegrees = $latitudeDecimalDegrees * -1;
							if ($longitudeDecimalDegrees > 0) $longitudeDecimalDegrees = $longitudeDecimalDegrees * -1;
							
					
							$address = utf8_encode($data['address']);
							if ($address == null or $address == ""){
								# Convert the GPS coordinates to a human readable address
								$json = json_decode(file_get_contents("http://maps.googleapis.com/maps/api/geocode/json?latlng=".$latitudeDecimalDegrees.",".$longitudeDecimalDegrees."&sensor=false"));
								if ( isset( $json->status ) && $json->status == 'OK') {
									$address = $json->results[0]->formatted_address;

									$query = "UPDATE gprmc set address = '". utf8_decode(mysql_real_escape_string($address)) ."' WHERE imei = '$imei' AND latitudeDecimalDegrees = ". $data['latitudeDecimalDegrees'] ." AND longitudeDecimalDegrees = ". $data['longitudeDecimalDegrees'] ." AND date = '". $data['data'] ."'";
									$atualiza = mysql_query($query, $con);
									if (!$atualiza) {
										echo "<script>console.log('$atualiza *************** $query ');</script>";
									}
								}
								else echo "<script>console.log('".$json->status ."');</script>";
							} 													
		
					$escreveSN = $data['ligado'] =='S' || $data['velocidade'] > 0 ? 'Sim' : 'Não' ;					
					echo "<tr>
					  <td>" . $data[data] . "</td>
					  <td>" . $data['latitudeDecimalDegrees'] . "</td>
					  <td>" . $data['longitudeDecimalDegrees'] . "</td>
					  <td>" . $data['address'] . "</td>
					  <td>" . $data['velocidade'] .  "</td>
					  <td>" . $escreveSN . "</td>
					</tr>";
					}
					echo "<tr><td colspan='6' class='text-right' id='km-rodado'></td></tr>"; 
				}else {
					echo "<tr><td colspan='6' class='text-center'>Nenhum resultado encontrado</td></tr>";
				}
			?>
		</tbody>
	</table>
</div>