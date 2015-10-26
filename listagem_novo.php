<?php 
include('seguranca.php');
include('log.php');
error_reporting(0);

//abrirArquivoLog();
//printLog("Inicio:".date('d/m/Y H:i:s'));

header("Content-Type: text/html; charset=utf-8");

$q=$_GET["imei"];
$cliente=$_SESSION["clienteSession"];
$grupo=$_SESSION["grupoSession"];

$con = mysql_connect('cloudservice.cgejdsdl842e.sa-east-1.rds.amazonaws.com', 'gpstracker', 'd1$1793689');
if (!$con)
  {
	die('Could not connect: ' . mysql_error());
  }

mysql_select_db("tracker", $con);

echo "<table class='stripeMe'>
	<thead>
		<tr class='alt'>
			<th>Identif.</th>
			<th>Data/Hora</th>
			<th>Local</th>
			<th>Status</th>
			<th>Velocidade</th>
			<th>Alertas</th>
			<th>Lat.</th>
			<th>Long.</th>
			<th>Ver</th>
		</tr>
	</thead>
	<tbody>";
//printLog("If strpos:".date('d/m/Y H:i:s'));
if(strpos($q, 'grupo') !== false || ($grupo != '' && $q == 'ALL')){
	$resGrupo = "";
	if($q == 'ALL' && $grupo != ''){
		$resGrupo = mysql_query("SELECT bem.imei, bem.status_sinal, bem.name, bem.modo_operacao, la.id, la.infotext, la.date, la.latitudeDecimalDegrees, la.longitudeDecimalDegrees, la.latitudeHemisphere, la.longitudeHemisphere, la.speed, la.address 
			FROM bem 
			JOIN grupo_bem gb on gb.bem = bem.id 
			JOIN loc_atual la on la.imei = bem.imei
			where gb.grupo = $grupo and bem.cliente = $cliente and gpsSignalIndicator in ('F', 'L')", $con);
	} else {
		$aGrupo = split('_', $q);
		$resGrupo = mysql_query("SELECT bem.imei, bem.status_sinal, bem.name, bem.modo_operacao, la.id, la.infotext, la.date, la.latitudeDecimalDegrees, la.longitudeDecimalDegrees, la.latitudeHemisphere, la.longitudeHemisphere, la.speed, la.address
			FROM bem 
			JOIN grupo_bem gb on gb.bem = bem.id 
			JOIN loc_atual la on la.imei = bem.imei
			where gb.grupo = $aGrupo[1] and bem.cliente = $cliente and gpsSignalIndicator in ('F', 'L')", $con);
	}
	while($grupoBem = mysql_fetch_assoc($resGrupo)){
		//printLog("Inicio retornaLista:".date('d/m/Y H:i:s'));
		retornaLista($grupoBem[imei], $cliente, $con, $grupoBem);
		//printLog("Fim retornaLista:".date('d/m/Y H:i:s'));
	}
} else {
	$aGrupo = split('_', $q);
	$resGrupo = mysql_query("SELECT bem.imei, bem.status_sinal, bem.name, bem.modo_operacao, la.id, la.infotext, la.date, la.latitudeDecimalDegrees, la.longitudeDecimalDegrees, la.latitudeHemisphere, la.longitudeHemisphere, la.speed, la.address
	FROM bem 
	JOIN loc_atual la on la.imei = bem.imei
	where cliente = $cliente and gpsSignalIndicator in ('F', 'L')", $con);
	while($grupoBem = mysql_fetch_assoc($resGrupo)){
		retornaLista($grupoBem[imei], $cliente, $con, $grupoBem);
	}
}
//printLog("Fim If strpos:".date('d/m/Y H:i:s'));
function retornaLista($imei, $cliente, $con, $bem){
	//Checando se está no modo SMS
	//$res = mysql_query("SELECT tipo, status_sinal, identificacao, name, modo_operacao FROM bem where imei = '$imei' and cliente = $cliente", $con);
	//$dataBem = mysql_fetch_assoc($res);
	if ($bem[modo_operacao] == 'SMS') {
	
	  echo "<tr class=''>";
		  echo "<td colspan='7' align='center'><b style='padding:3px'>Atenção:</b>Este gps está operando em modo <b style='padding:0px'>SMS</b>. Para o rastreamento, ative o modo GPRS. Para os últimos registros, ver em histórico.</td>";
	  echo "</tr>";
	  
	} else {
		
		//$bem = mysql_fetch_assoc($res);
		
		$loopcount = 0;
		$class = "";
		
		//$sql="SELECT id, infotext, date, latitudeDecimalDegrees, longitudeDecimalDegrees, latitudeHemisphere, longitudeHemisphere, speed, address
		//	  FROM loc_atual WHERE gpsSignalIndicator in ('F', 'L') and imei = '". $imei ."' LIMIT 1";
		//$result = mysql_query($sql, $con);
		
		//while($data = mysql_fetch_assoc($result))
		//{
			$idRota = $bem['id'];
			
			// Calculo das coordenadas. Convertendo coordenadas do modo GPRS para GPS
			$trackerdate = ereg_replace("^(..)(..)(..)(..)(..)$","\\3/\\2/\\1 \\4:\\5",$bem['date']);
			strlen($bem['latitudeDecimalDegrees']) == 9 && $data['latitudeDecimalDegrees'] = '0'.$bem['latitudeDecimalDegrees'];
			$g = substr($bem['latitudeDecimalDegrees'],0,3);
			$d = substr($bem['latitudeDecimalDegrees'],3);
			$latitudeDecimalDegrees = $g + ($d/60);
			$bem['latitudeHemisphere'] == "S" && $latitudeDecimalDegrees = $latitudeDecimalDegrees * -1;
	
	
			strlen($bem['longitudeDecimalDegrees']) == 9 && $bem['longitudeDecimalDegrees'] = '0'.$bem['longitudeDecimalDegrees'];
			$g = substr($bem['longitudeDecimalDegrees'],0,3);
			$d = substr($bem['longitudeDecimalDegrees'],3);
			$longitudeDecimalDegrees = $g + ($d/60);
			$bem['longitudeHemisphere'] == "S" && $longitudeDecimalDegrees = $longitudeDecimalDegrees * -1;
	
			$longitudeDecimalDegrees = $longitudeDecimalDegrees * -1;
			
			$speed = $bem['speed'] * 1.609;
	
			$infotext = $bem['infotext'];
	
			$address = utf8_encode($bem['address']);
			//Testa se tem endereço, se nao tiver obtem do google geocode e grava
			if ($address == null or $address == "")
			{
				$class = 'rowAddr nAdrr';
				# Convert the GPS coordinates to a human readable address
				/*
				$tempstr = "http://maps.google.com/maps/geo?q=$latitudeDecimalDegrees,$longitudeDecimalDegrees&oe=utf-8&sensor=true&key=ABQIAAAA1eJDkrS8CWf72GG_ja1iwxSLJm6jKhYDoeEX4bsUreFa6O2tyhQPU8qD2qyn2VXX7cI0kF5yfrOKjw&output=csv"; //output = csv, xml, kml, json
				$rev_geo_str = file_get_contents($tempstr);
				$rev_geo_str = ereg_replace("\"","", $rev_geo_str);
				$rev_geo = explode(',', $rev_geo_str);
				$address = $rev_geo[2] .",". $rev_geo[3] ;
				*/
	
				$json = json_decode(file_get_contents("http://maps.google.com/maps/api/geocode/json?sensor=false&latlng=$latitudeDecimalDegrees,$longitudeDecimalDegrees&language=es-ES"));
				if ( isset( $json->status ) && $json->status == 'OK') {
					$address = $json->results[0]->formatted_address;
	
					if (!mysql_query("UPDATE gprmc set address = '". utf8_decode($address) ."', date = date where id = $idRota", $con))
					{
						//die('Error: ' . mysql_error());
					}
				}
			}else{
				$class = 'rowAddr wAdrr';
			}
			$img = ""; //adiciona imagens de alerta na grid
			switch($infotext)
			{
				case "low battery": $img = "<img src='imagens/battery-low.png' style='margin-left:2px;margin-bottom:-4px; padding:0px;height:16px;width:16px' title='Bat. Fraca' alt='Bat. Fraca' />"; break;
				case "help me": $img = "<img src='imagens/help.png' style='margin-left:2px;margin-bottom:-4px; padding:0px;height:16px;width:16px' title='SOS!' alt='SOS!' />"; break;
				case "acc alarm": $img = "<img src='imagens/ignicao.png' style='margin-left:2px;margin-bottom:-4px; padding:0px;height:16px;width:16px' title='Ignição' alt='Ignição' />"; break;
				
				default: $img = "";
			}
			
			if (($loopcount % 2) == 0) 
				$class .= " alt";
		  
			echo "<tr id='rota$idRota' class='". $class ."' onmouseover=\"this.className='alt over'\" onmouseout=\"this.className='". $class ."'\">";
				  echo "<td>".$bem[name]."</td>";
				  echo "<td>" . date('d/m/Y', strtotime($bem['date'])). " " . date('H:i:s', strtotime($bem['date'])) . "</td>";
				  echo "<td>" . $address . " </td>";
				  
				  echo "<td>" ;
				  
				  echo $bem['status_sinal']=='R'?"<img src='imagens/chave_ligada2.png' style='margin-left:2px;margin-bottom:-4px; padding:0px;height:16px;width:16px' title='Ingnicao' alt='Ingnicao' />":"<img src='imagens/chave_desligada.png' style='margin-left:2px;margin-bottom:-4px; padding:0px;height:16px;width:16px' title='Ingnicao' alt='Ingnicao' />";
				  
				  echo ($infotext=='low battery'?"<img src='imagens/batery.png' style='margin-left:2px;margin-bottom:-4px; padding:0px;height:16px;width:16px' title='Bat. Fraca' alt='Bat. Fraca' />":"<img src='imagens/batery_cheia.png' style='margin-left:2px;margin-bottom:-4px; padding:0px;height:16px;width:16px' title='Bat. Normal' alt='Bat. Normal' />");
				  
				  echo ($bem['status_sinal']=="S"?"&nbsp;<img src='imagens/signal_3.png' style='margin-left:2px;margin-bottom:-4px; padding:0px;height:16px;width:16px' title='Sem Sinal' alt='Sem Sinal' />":"&nbsp;<img src='imagens/signal_1.png' style='margin-left:2px;margin-bottom:-4px; padding:0px;height:16px;width:16px' title='Com Sinal' alt='Com Sinal' />");
				  
				  echo " </td>";
				  
				  echo "<td>" . floor($speed). " Km/h" . " </td>";
				  
				  echo "<td> ".$img."</td>";
				  echo "<td> ".$latitudeDecimalDegrees."</td>";
				  echo "<td> ".$longitudeDecimalDegrees."</td>";
				  echo "<td> <input type=\"submit\" value=\"Ver\" class=\"botaoBranco\" onclick=\"parent.main.verNoMapa(" . $latitudeDecimalDegrees . "," . $longitudeDecimalDegrees . "); this.style.color='#c0c0c0'; \" />$img</td>";
			  echo "</tr>";
			$loopcount++;
		//}
		/*
		if ($loopcount == 0) {
			if ($q == "ALL") {
				echo "<tr class=''>";
				echo "<td colspan='7' align='center'> Visualizando toda a frota. Cada cor indica as últimas 20 posições.</td>";
				echo "</tr>";
			} else {
				echo "<tr class=''>";
				echo "<td colspan='7' align='center'> Nenhum registro foi encontrado! Aguarde o sinal do GPS. </td>";
				echo "</tr>";
			}
		}
		*/
	}
}
echo "</tbody>";
echo "</table>";

mysql_close($con);
//printLog("Fim:".date('d/m/Y H:i:s'));
//fecharArquivoLog();
?>