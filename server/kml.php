<?php 
if($_GET['sources'])
    show_source(__FILE__);
else
    header('Content-Type: application/vnd.google-earth.kml+xml');
echo '<?xml version="1.0" encoding="UTF-8"?>'."\n";

$cnx = mysql_connect('cloudservice.cgejdsdl842e.sa-east-1.rds.amazonaws.com', 'gpstracker', 'd1$1793689');
mysql_select_db('tracker', $cnx);

if($_GET['entries'] <> ""){
  $entries = $_GET['entries'];
  }
elseif ($_POST['entries'] <> "") {
  $entries = $_POST['entries'];
  }  
else {
  $entries = 50;
  }

$dataInicial = '';
$dataFinal = '';
if(isset($_GET['data_inicial']) && !empty($_GET['data_inicial'])){
$dataInicial = $_GET['data_inicial'];
$dataInicial = split(' ', $dataInicial);
$dataInicialHr = $dataInicial[1];
$dataInicial = split('/', $dataInicial[0]);
$dataSqlInicial = $dataInicial[2].'-'.$dataInicial[1].'-'.$dataInicial[0].' '.$dataInicialHr;
}
if(isset($_GET['data_final']) && !empty($_GET['data_final'])){
$dataFinal = $_GET['data_final'];
$dataFinal = split(' ', $dataFinal);
$dataFinalHr = $dataFinal[1];
$dataFinal = split('/', $dataFinal[0]);
$dataSqlFinal = $dataFinal[2].'-'.$dataFinal[1].'-'.$dataFinal[0].' '.$dataFinalHr;
}

  
$res1 = mysql_query("SELECT valor FROM preferencias WHERE nome = 'mostra_rastro_mapa'");
$pref = mysql_fetch_assoc($res1);

if($pref[valor] == 'N')
	$entries = 1;

$url = 'http://'.$_SERVER['HTTP_HOST'].'/';

if($_GET['imei'] <> "")
{
	$imei = $_GET['imei'];
}
elseif ($_POST['imei'] <> "") 
{
	$imei = $_POST['imei'];
} else {
	$imei = 0;
}

$step = 255 / $entries;
//echo $step;
$loop = 0;
$res1 = mysql_query("SELECT b.name as nomeBem, b.identificacao, b.tipo, b.status_sinal, b.bloqueado, b.ligado FROM bem b WHERE b.imei = $imei LIMIT 1");
$tipoBem = '';
$ativo = '';
$ligado = '';
$bloqueado = '';
if($res1 !== false){
	while($data1 = mysql_fetch_assoc($res1))
	{
		$nomeBem = $data1['nomeBem'];
		$identificacao = $data1['identificacao'];
		$tipoBem = $data1['tipo'];
		$ativo = $data1['status_sinal'];
		$ligado = $data1['ligado'];
		$bloqueado = $data1['bloqueado'];
	}
}
$res = null;
if(empty($dataInicial)){
if($pref[valor] == 'N')
$res = mysql_query("SELECT g.* FROM loc_atual g WHERE g.gpsSignalIndicator = 'F' and g.imei = $imei ORDER BY g.date DESC LIMIT $entries");
else
$res = mysql_query("SELECT g.* FROM gprmc g WHERE g.gpsSignalIndicator = 'F' and g.imei = $imei ORDER BY g.date DESC LIMIT $entries");
} else {
	$res = mysql_query("SELECT g.* FROM gprmc g WHERE g.gpsSignalIndicator = 'F' and g.imei = '$imei' and date between '$dataSqlInicial' and '$dataSqlFinal' ORDER BY g.date DESC");
}
$line_coordinates = "";
$ballons = "";
while($data = mysql_fetch_assoc($res))
{
    $trackerdate = ereg_replace("^(..)(..)(..)(..)(..)$","\\3/\\2/\\1 \\4:\\5",$data['date']);
    if($data['converte'] == 1){
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
	
		//$longitudeDecimalDegrees = $longitudeDecimalDegrees * -1;
	} else {
		$latitudeDecimalDegrees = $data['latitudeDecimalDegrees'];
		$longitudeDecimalDegrees = $data['longitudeDecimalDegrees'];
	}

    $speed = $data['speed'] * 1.609;
	
	$line_coordinates .= "$longitudeDecimalDegrees,$latitudeDecimalDegrees,0\n";
	$line_coordinates_green = "$longitudeDecimalDegrees,$latitudeDecimalDegrees,0\n";
	
	//$style = trim($tipoBem)!=''?'#highlightPlacemark'.$tipoBem.($ativo=="R"?'':'Inativo'):'#highlightPlacemark';
	$style = '';
	if($bloqueado == 'S')
		$style = '#highlightPlacemark'.$tipoBem.'Bloqueado';
	else if ($ligado == 'S')
		$style = '#highlightPlacemark'.$tipoBem;
	else
		$style = '#highlightPlacemark'.$tipoBem.'Inativo';
		
		
	if ($loop != 0) {
		$ballons .= '
		<Placemark>
			<name>'.$nomeBem.' - '.$identificacao.'</name>
			<styleUrl>highlightPlacemark</styleUrl>
			<description>Velocidade : '.floor($speed).'Km/h - Data : '.date('d/m/Y H:i:s', strtotime($data['date'])).' &lt;br/&gt; Lat: '.$longitudeDecimalDegrees.', Long:'.$latitudeDecimalDegrees. '</description>
			<Point>
			  <coordinates>'."$longitudeDecimalDegrees,$latitudeDecimalDegrees,0".'</coordinates>
			</Point>
		</Placemark>
		';
	} else {
		//O ultimo registro obtido pelo gps fica verde; o ultimo é o primeiro da lista. ORDER BY DESC.
		if ($loop == 0) {
			$greenBallons = '
			<Placemark>
				<name>'.$nomeBem.' - '.$identificacao.'</name>
				<styleUrl>'.$style.'</styleUrl>
				<description>Velocidade : '.floor($speed).'Km/h - Data : '.date('d/m/Y H:i:s', strtotime($data['date'])).' &lt;br/&gt; Lat: '.$longitudeDecimalDegrees.', Long:'.$latitudeDecimalDegrees. '</description>
				<Point>
				  <coordinates>'."$longitudeDecimalDegrees,$latitudeDecimalDegrees,0".'</coordinates>
				</Point>
			</Placemark>
		';
		}
	}
	
	$loop++;
    
}
mysql_close($cnx);
?>
<kml xmlns="http://www.opengis.net/kml/2.2">
  <Document>
    <name>Tracker Map</name>
    <description>Tracker</description>

    <Style id="highlightPlacemark">
      <IconStyle>
        <Icon>
          <href><?=$url;?>imagens/marker_carro_ativo.png</href>
        </Icon>
      </IconStyle>
    </Style>
    
    <Style id="highlightPlacemarkMOTO">
      <IconStyle>
        <Icon>
          <href><?= $url;?>imagens/marker_moto_ativo.png</href>
        </Icon>
      </IconStyle>
    </Style>
    
    <Style id="highlightPlacemarkMOTOInativo">
      <IconStyle>
        <Icon>
          <href><?= $url;?>imagens/marker_moto_inativo.png</href>
        </Icon>
      </IconStyle>
    </Style>
    
    <Style id="highlightPlacemarkMOTOBloqueado">
      <IconStyle>
        <Icon>
          <href><?= $url;?>imagens/marker_moto_bloqueado.png</href>
        </Icon>
      </IconStyle>
    </Style>
    
    <Style id="highlightPlacemarkCARRO">
      <IconStyle>
        <Icon>
          <href><?=$url;?>imagens/marker_carro_ativo.png</href>
        </Icon>
      </IconStyle>
    </Style>
    
    <Style id="highlightPlacemarkCARROInativo">
      <IconStyle>
        <Icon>
          <href><?=$url;?>imagens/marker_carro_inativo.png</href>
        </Icon>
      </IconStyle>
    </Style>
    
    <Style id="highlightPlacemarkCARROBloqueado">
      <IconStyle>
        <Icon>
          <href><?=$url;?>imagens/marker_carro_bloqueado.png</href>
        </Icon>
      </IconStyle>
    </Style>
    
    <Style id="highlightPlacemarkJET">
      <IconStyle>
        <Icon>
          <href><?=$url;?>imagens/marker_jet_ativo.png</href>
        </Icon>
      </IconStyle>
    </Style>
    
    <Style id="highlightPlacemarkJETInativo">
      <IconStyle>
        <Icon>
          <href><?=$url;?>imagens/marker_jet_inativo.png</href>
        </Icon>
      </IconStyle>
    </Style>
    
    <Style id="highlightPlacemarkJETBloqueado">
      <IconStyle>
        <Icon>
          <href><?=$url;?>imagens/marker_jet_bloqueado.png</href>
        </Icon>
      </IconStyle>
    </Style>
    
    <Style id="highlightPlacemarkCAMINHAO">
      <IconStyle>
        <Icon>
          <href><?=$url;?>imagens/marker_caminhao_ativo.png</href>
        </Icon>
      </IconStyle>
    </Style>
    
    <Style id="highlightPlacemarkCAMINHAOInativo">
      <IconStyle>
        <Icon>
          <href><?=$url;?>imagens/marker_caminhao_inativo.png</href>
        </Icon>
      </IconStyle>
    </Style>
    
    <Style id="highlightPlacemarkCAMINHAOBloquado">
      <IconStyle>
        <Icon>
          <href><?=$url;?>imagens/marker_truck_bloqueado.png</href>
        </Icon>
      </IconStyle>
    </Style>
    
    <Style id="highlightPlacemarkVAN">
      <IconStyle>
        <Icon>
          <href><?=$url;?>imagens/marker_van_ativo.png</href>
        </Icon>
      </IconStyle>
    </Style>
    
    <Style id="highlightPlacemarkVANInativo">
      <IconStyle>
        <Icon>
          <href><?=$url;?>imagens/marker_van_inativo.png</href>
        </Icon>
      </IconStyle>
    </Style>
    
    <Style id="highlightPlacemarkVANBloqueado">
      <IconStyle>
        <Icon>
          <href><?=$url;?>imagens/marker_van_bloqueado.png</href>
        </Icon>
      </IconStyle>
    </Style>
    
    <Style id="highlightPlacemarkPICKUP">
      <IconStyle>
        <Icon>
          <href><?=$url;?>imagens/marker_pickup_ativo.png</href>
        </Icon>
      </IconStyle>
    </Style>
    
    <Style id="highlightPlacemarkPICKUPInativo">
      <IconStyle>
        <Icon>
          <href><?=$url;?>imagens/marker_pickup_inativo.png</href>
        </Icon>
      </IconStyle>
    </Style>
    
    <Style id="highlightPlacemarkPICKUPBloqueado">
      <IconStyle>
        <Icon>
          <href><?=$url;?>imagens/marker_pickup_bloqueado.png</href>
        </Icon>
      </IconStyle>
    </Style>
    
    <Style id="highlightPlacemarkONIBUS">
      <IconStyle>
        <Icon>
          <href><?=$url;?>imagens/marker_onibus_ativo.png</href>
        </Icon>
      </IconStyle>
    </Style>
    
    <Style id="highlightPlacemarkONIBUSInativo">
      <IconStyle>
        <Icon>
          <href><?=$url;?>imagens/marker_onibus_inativo.png</href>
        </Icon>
      </IconStyle>
    </Style>
    
    <Style id="highlightPlacemarkONIBUSBloqueado">
      <IconStyle>
        <Icon>
          <href><?=$url;?>imagens/marker_bus_bloqueado.png</href>
        </Icon>
      </IconStyle>
    </Style>

    <Style id="highlightPlacemarkGreen">
      <IconStyle>
        <Icon>
          <href>http://google-maps-icons.googlecode.com/files/amphitheater-tourism.png</href>
        </Icon>
      </IconStyle>
    </Style>

    <Style id="redLine">
      <LineStyle>
        <color>ff0000ff</color>
        <width>4</width>
      </LineStyle>
    </Style>

    <Style id="BalloonStyle">
      <BalloonStyle>
        <!-- a background color for the balloon -->
        <bgColor>ffffffbb</bgColor>
        <!-- styling of the balloon text -->
        <text><![CDATA[
        <b><font color="#CC0000" size="+3">$[name]</font></b>
        <br/><br/>
        <font face="Courier">$[description]</font>
        <br/><br/>
        Extra text that will appear in the description balloon
        <br/><br/>
        <!-- insert the to/from hyperlinks -->
        $[geDirections]
        ]]></text>
      </BalloonStyle>
    </Style>

    <Style id="greenPoint">
      <LineStyle>
        <color>ff009900</color>
        <width>4</width>
      </LineStyle>
    </Style>

    <Placemark>
      <name>Red Line</name>
      <styleUrl>#redLine</styleUrl>
      <LineString>
        <altitudeMode>relative</altitudeMode>
        <coordinates>
			<?php echo $line_coordinates; ?>
        </coordinates>
      </LineString>
    </Placemark>
	
	<?php echo $greenBallons; ?>

    <?php echo $ballons; ?>

  </Document>
</kml>
