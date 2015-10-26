<?php
include('../seguranca.php');
include('../usuario/config.php');
// date_default_timezone_set("America/Sao_Paulo");
// setlocale(LC_ALL, 'pt_BR');

$cnx = mysql_connect($DB_SERVER, $DB_USER, $DB_PASS) or die("Could not connect: " . mysql_error());
mysql_select_db($DB_NAME, $cnx);
if($cliente != 'master'){
	if($cliente != 'admin') $sqlCliente = " WHERE (b.cliente = $id_admin AND c.ativo = 'S')";
	else $sqlCliente = " WHERE (c.id_admin = $id_admin AND c.ativo = 'S')";
} else $sqlCliente = "";

$palavra = $_GET['query'];
$query = "";
$mostrarOff = (isset($_GET['off'])) ? true : false ;
if(!empty($palavra)){
	$query = " (b.name LIKE '$palavra%' OR b.imei LIKE '$palavra%') ";
	if(strpos($sqlCliente, 'where') == -1 || strpos($sqlCliente, 'where') === false){
		$query = " where ".$query;
	}
}
elseif ($mostrarOff) {
	$query = "WHERE (b.status_sinal = 'S' OR b.status_sinal = 'D' AND c.ativo = 'S')";
	if (strpos($sqlCliente, 'WHERE')) {
		$query = "AND b.status_sinal != 'R'";
	}
}
elseif ($sqlCliente == ""){
	$query = "WHERE c.ativo = 'S'";
}
else $query = "";

$pagina = $_GET['pagina'];
$start = 0;
if(!empty($pagina)){
	$start = (($pagina-1)*20)+1;
} else{
	$pagina = 1;
}

$consulta = "SELECT b.*, la.longitudeDecimalDegrees, la.longitudeHemisphere, la.latitudeDecimalDegrees,
			la.latitudeHemisphere, la.coordenada_antiga, la.speed, la.converte, la.address, DATE_FORMAT(la.date, '%d/%m/%y') AS ladata, DATE_FORMAT(la.date, '%H:%i:%s') AS lahora
			FROM bem b
			INNER JOIN cliente c ON c.id = b.cliente 
			LEFT JOIN loc_atual la ON la.imei = b.imei
			$sqlCliente $query ORDER BY la.date, lahora DESC LIMIT $start,20 ";

// echo "<hr>$consulta<br><br>$sqlCliente<br><br>$query<br>";
$resEquip = mysql_query($consulta, $cnx);
echo mysql_error();
// echo "SELECT count(*) total from bem b INNER JOIN cliente c ON c.id = b.cliente LEFT JOIN loc_atual la ON la.imei = b.imei $sqlCliente $query";
$resCountEquip = mysql_query("SELECT count(*) total from bem b INNER JOIN cliente c ON c.id = b.cliente LEFT JOIN loc_atual la ON la.imei = b.imei $sqlCliente $query", $cnx) or die(mysql_error());

if($resCountEquip !== false){
	$dataCount = mysql_fetch_assoc($resCountEquip);
	$count = $dataCount['total'];
	$count = $count/20;
	if(strpos($count, '.') > -1){
		$arCount = explode('.', $count);
		$count = $arCount[0]+1;
	}
}

/* CONSULTA DOS ALERTAS */
$alertas = mysql_query("SELECT * FROM message WHERE viewed_adm = 'N' ORDER BY date DESC LIMIT 0,5", $cnx) or die(mysql_error());
$printAlert = "";
while ($row = mysql_fetch_assoc($alertas)) {
	$dataAlerta = date_create($row['date']);
	$printAlert .= "<div class='alert-box warning' title='Clique para marcar como visualizado' data_imei='" . $row['imei'] . "' data_msg='" . $row['message'] . "'><span>Alerta: </span>". $row['message'] . " para o IMEI " . $row['imei'] . " em " . date_format($dataAlerta, 'd/m/Y') . " às " . date_format($dataAlerta, 'H:i:s') . "</div>";
}
?>
<script type="text/javascript">
	$("#alertas").html("<?php echo $printAlert; ?>");
</script>

<div class="row">
<?php
	if ($cliente != 'master') {
		$consultaTodos = mysql_query("SELECT count(*) todos from bem b INNER JOIN cliente c ON c.id = b.cliente LEFT JOIN loc_atual la ON la.imei = b.imei WHERE c.id_admin = $id_admin AND c.ativo = 'S' ");
		$consultaAtivos = mysql_query("SELECT count(*) ativos from bem b INNER JOIN cliente c ON c.id = b.cliente LEFT JOIN loc_atual la ON la.imei = b.imei WHERE c.id_admin = $id_admin AND (b.status_sinal = 'S' OR b.status_sinal = 'R') AND c.ativo = 'S'");
		$consultaOff = mysql_query("SELECT count(*) offline from bem b INNER JOIN cliente c ON c.id = b.cliente LEFT JOIN loc_atual la ON la.imei = b.imei WHERE (b.status_sinal = 'D') AND c.ativo='S' AND c.id_admin = $id_admin");
	}
	else {
		$consultaTodos = mysql_query("SELECT count(*) todos from bem b INNER JOIN cliente c ON c.id = b.cliente LEFT JOIN loc_atual la ON la.imei = b.imei WHERE c.ativo = 'S' ");
		$consultaAtivos = mysql_query("SELECT count(*) ativos from bem b INNER JOIN cliente c ON c.id = b.cliente LEFT JOIN loc_atual la ON la.imei = b.imei WHERE (b.status_sinal = 'R' OR b.status_sinal = 'S') AND c.ativo = 'S'");
		$consultaOff = mysql_query("SELECT count(*) offline from bem b INNER JOIN cliente c ON c.id = b.cliente LEFT JOIN loc_atual la ON la.imei = b.imei WHERE (b.status_sinal = 'D') AND c.ativo='S'");
	}

	$qntAtivos = mysql_fetch_assoc($consultaAtivos);
	$qntAtivos = $qntAtivos['ativos'];

	$qntOff = mysql_fetch_assoc($consultaOff);
	$qntOff = $qntOff['offline'];

	$qntAll = mysql_fetch_assoc($consultaTodos);
	$qntAll = $qntAll['todos'];

	echo "<div class='col-sm-12 col-lg-6'><b>Total de Veículos:</b> $qntAll | <b>Online:</b> $qntAtivos | <b>Offline:</b> $qntOff</div>";
?>
	<div class="col-sm-12 col-lg-6"><span style="cursor:help;" title="A hora mostrada é referente ao horário do servidor."><b>Última atualização:</b> <?php echo date("d/m/Y H:i:s"); ?>.</span></div>
</div>
<!-- <hr> -->
<div class="row">
	<div class="col-lg-12">
		<button type="button" class="btn btn-default" onclick="mostraOff();">Mostrar Offline</button>
		<button type="button" class="btn btn-default" onclick="paginacao(0);">Mostrar Todos</button>
		<button type="button" class="btn btn-default" onclick="reprogReload(1000);">1s</button>
		<button type="button" class="btn btn-default" onclick="reprogReload(5000);">5s</button>
		<button type="button" class="btn btn-default" onclick="reprogReload(10000);">10s</button>
		<button type="button" class="btn btn-default" onclick="reprogReload(30000);">30s</button>
		<button type="button" class="btn btn-default" onclick="reprogReload(60000);;">60s</button>
	</div>
</div>

<div class="row">
	<div class="col-lg-8">
		<form class="form-inline" role="form" onsubmit="javascript:return(false);">
			<div class="form-group">
				<label class="sr-only" for="pesquisa">Pesquisar</label>
				<input type="text" class="form-control" id="pesquisa" name="pesquisa" placeholder="Buscar por PLACA ou IMEI">
			</div>
			<a href="javascript:pesquisa();" class="btn btn-primary">Pesquisar</a>
		</form>
	</div>
	<div class="col-lg-4">
		<ul class="pager">
			<li><a href="javascript:paginacao(<?php echo $pagina>1?$pagina-1:$pagina?>)">&larr; Anterior</a></li>
			<li><a href="javascript:paginacao(<?php echo $pagina<$count?$pagina+1:$pagina?>)">Próximo &rarr;</a></li>
		</ul>
	</div>
</div>

<div class="table-responsive">
	<table id="tableEquip" class="equip table table-bordered table-striped table-hover">
	<thead><tr>
		<td>Mapa</td>
		<td style="display:none">IMEI</td>
	    <td>Placa</td>
	    <td>Identificação</td>
	    <td style="display:none">Chip</td>
	    <td style="display:none">Apelido</td>
	    <td>Modelo</td>
	    <td>Situação</td>
	    <td style="display:none">Movimento</td>
	    <td style="display:none">Velocidade</td>
	    <td style="display:none">Endereço</td>
	    <td style="display:none">Latitude</td>
	    <td style="display:none">Longitude</td>
	    <td>Data</td>
	    <td>Hora</td>
	    <td>Status</td>
	    <td style="display:none">Tipo</td>
	    <td style="display:none">Sinal</td>
	    <td style="display:none">Bloqueado</td>
	    <td>Comandos</td>
	</tr></thead>
	<tbody>
	<?php
		/** Retorna a imagem do status do sinal */
		function imagenStatusSinal($sgSinal, $delay){
			$imgSinal;		
			switch($sgSinal) {
				case "R": $imgSinal = "status_rastreando.png"; break;
				case "S": $imgSinal = "status_sem_sinal.png"; break;
				case "D": $imgSinal = "status_desligado.png"; break;
			}
			if ($delay) $imgSinal = "status_desligado.png";	
			return $imgSinal;
		}
		while($data = mysql_fetch_assoc($resEquip)):?>
			<tr>
				<?php 
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
				
				/* TRATAMENTO DA OBTENÇÃO DO ENDEREÇO */
				$address = "";
				/* $address = utf8_encode($data['address']);
				$posicaoAntiga = explode("|", $data['coordenada_antiga']);

				// echo "<script>console.log('End.: $address | LAT: $latitudeDecimalDegrees | LONG: $longitudeDecimalDegrees | LATA: $posicaoAntiga[0] | LONGA: $posicaoAntiga[1] | IMEI: $data[imei]');</script>";

				if ($posicaoAntiga[0] != $latitudeDecimalDegrees || $posicaoAntiga[1] != $longitudeDecimalDegrees) {
					$coordenada = $latitudeDecimalDegrees ."|". $longitudeDecimalDegrees;
					if (!mysql_query("UPDATE loc_atual SET coordenada_antiga = '$coordenada' WHERE imei = $data[imei]", $cnx)){
						echo "<script>console.log('UPDATE coordenada: ". mysql_error() . "');</script>";
					}
					
					# Convert the GPS coordinates to a human readable address
					$json = json_decode(file_get_contents("http://maps.googleapis.com/maps/api/geocode/json?latlng=".$latitudeDecimalDegrees.",".$longitudeDecimalDegrees."&sensor=false"));
					if ( isset( $json->status ) && $json->status == 'OK') {
						$address = $json->results[0]->formatted_address;
						// $address = utf8_decode($address);
						if (!mysql_query("UPDATE loc_atual SET address = '$address' WHERE imei = $data[imei]", $cnx)) {
							echo "<script>console.log('UPDATE address: ". mysql_error() . "');</script>";
						}
					}
					else echo "<script>console.log('GET address $data[imei]: ". $json->status . "');</script>";
				}*/

				/*VERIFICA SE HÁ ATRASO NO TRÁFEGO DO RASTREADOR (10min)*/
				if ($data['ladata'] == date("d/m/y")) {
					// date_default_timezone_set('America/Maceio');	 
					$inicio = $data['lahora'];
					$fim = date("H:i:s");
					$inicio = DateTime::createFromFormat('H:i:s', $inicio);
					$fim = DateTime::createFromFormat('H:i:s', $fim);
					 
					$intervalo = $inicio->diff($fim);
					// echo "Inicial: " . $data['lahora'] . " | Final: " . date("H:i:s") . " | Int: " . $intervalo->format('%H:%I:%S') . "<br>";
					if ($intervalo->format('%H:%I:%S') > '00:10:00') $atraso = true;
					else $atraso = false;
				}
				else $atraso = true;

				if ($atraso) {
					// $atualizaBem = mysql_query("UPDATE bem SET status_sinal = 'D', ligado = 'N' WHERE imei = ". $data['imei'], $cnx) or die(mysql_error());
					$atualizaBem = mysql_query("UPDATE bem SET status_sinal = 'D' WHERE imei = ". $data['imei'], $cnx) or die(mysql_error());
					$atualizaLoc = mysql_query("UPDATE loc_atual SET speed = 0 WHERE imei = ". $data['imei'], $cnx) or die(mysql_error());
				}
				?>
				<td class="text-center"><input type="checkbox" id="mapa<?php echo $data['imei']?>" onchange="javascript:carregarMapa('<?=$data['imei']?>');" value="<?=$data['imei']?>"/></td>
			    <td style="display:none"><?php echo $data['imei']?></td>
			    <td id="name_<?php echo $data['imei']?>"><?php echo "<a href='javascript:void(0);' onclick=\"javascript:showConfirm('show'," . $data['imei'] . ");\">" . $data['name'] . "</a>"; ?></td>
			    <td id="apelido_<?php echo $data['imei']?>"><?php echo $data['apelido'] . " / <b>" . $data['identificacao'] . "</b>" ?></td>
			    <td style="display:none" id="identificacao_<?php echo $data['imei']?>"><?php echo $data['identificacao']?></td>
			    <td style="display:none" id="apelido_<?php echo $data['imei']?>"><?php echo $data['apelido']?></td>
			    <td id="modelo_<?php echo $data['imei']?>"><?php echo $data['modelo_rastreador']?></td>
			    <td><?php
			    	// if ($atraso || $data['status_sinal'] != 'R') echo "<img src='imagens/ignicao.png' alt='Desligado' title='Veículo desligado'> ";
					if ($data['ligado'] == 'S') echo "<img src='imagens/chave1.png' alt='Ligado' title='Veiculo Ligado'> ";
					else echo "<img src='imagens/ignicao.png' alt='Desligado' title='Veículo desligado'> ";
			    	if ($data['bloqueado'] == 'N') echo " <img src='imagens/unlock.png' alt='Veículo Desbloqueado' title='Veículo Desbloqueado'>"; else echo " <img src='imagens/locked.png' alt='Bloqueado' title='Veículo Bloqueado'>";
			    	echo " <b>".$data['speed']." Km/h</b>";
			    	?></td>
			    <td style="display:none"><?php echo $data['movimento']?></td>
			    <td style="display:none"><?php echo $data['speed']?></td>
			    <td style="display:none" id="endereco_<?php echo $data['imei']?>"><?php echo $address?></td>
			    <td style="display:none" id="veiculoLatitude<?php echo $data['imei']?>"><?php echo $latitudeDecimalDegrees?></td>
			    <td style="display:none" id="veiculoLongitude<?php echo $data['imei']?>"><?php echo $longitudeDecimalDegrees?></td>
			    <td><?php echo $data['ladata']?></td>
			    <td><?php echo $data['lahora']?></td>
			    <td><?php echo "<img src='../imagens/". imagenStatusSinal($data['status_sinal'], $atraso). "'>" ?></td>
			    <td style="display:none" id="tipo_<?php echo $data['imei']?>"><?php echo $data['tipo']?></td>
			    <td style="display:none" id="sinal_<?php echo $data['imei']?>"><?php if ($atraso) echo "D"; else echo $data['status_sinal']; ?></td>
			    <td style="display:none" id="block_<?php echo $data['imei']?>"><?php echo $data['bloqueado']?></td>
			    <td>
			    <?php
			    	if ($data['modelo_rastreador'] != 'tk103') {
			    		// $estilo 	= "style='opacity:0.25'";
			    		// $titleVel	= "O modelo do rastreador não possui esse comando";
			    		// $titleRast	= "O modelo do rastreador não possui esse comando";
			    		$clickVel	= "";
			    		$clickRast	= "";
			    	}
			    	else {
			    		// $estilo		= "";
			    		// $titleVel	= "Velocidade Limite";
			    		// $titleRast	= "Rastrear a Cada";
			    		// $clickVel	= "onclick=\"combustivel(". $data['imei'] .",',H,060');\"";
			    		// $clickRast	= "onclick=\"combustivel(". $data['imei'] .",',C,30s');\"";
			    		$clickVel	= "
			    			<li><a href=\"javascript:combustivel(". $data['imei'] .",',H,060');\">Velocidade Limite</a></li>
		    				<li class='divider'></li>
	    				";
	    				$clickRast	= "
							<li><a href=\"javascript:combustivel(". $data['imei'] .",',C,30s');\">Rastrear a Cada</a></li>
		    				<li class='divider'></li>
	    				";
			    	}
			    	// echo "<a href='javascript:void(0);' title='Liberar Combústivel' onclick=\"combustivel(" . $data['imei'] . ",',K');\"><img src='imagens/gas_on.png' alt='Liberar Combustível'></a> ";
			    	// echo "<a href='javascript:void(0);' title='Bloquear Combustível' onclick=\"combustivel(". $data['imei'] .",',J');\"><img src='imagens/gas_off.png' alt='Bloquear Combustível'></a> ";
			    	// echo "<a href='javascript:void(0);' title='$titleVel' $clickVel $estilo><img src='imagens/limit_vel.png' alt='Velocidade Limite'></a> ";
			    	// echo "<a href='javascript:void(0);' title='$titleRast' $clickRast $estilo><img src='imagens/rastrear.png' alt='Rastrear a Cada'></a>";
			    	// echo "<a href='javascript:void(0);' title='Criar Cerca Virtual' onclick='modalCerca(".$data['imei'].");'><img src='imagens/fence.png' alt='Cerca Virtual'></a>";
			    	$cerca = mysql_query("SELECT id FROM geo_fence WHERE imei = ". $data['imei']);
			    	if (mysql_num_rows($cerca)) {
			    		while ($row = mysql_fetch_assoc($cerca)) {
			    			$idCerca = $row['id'];
			    		}
			    		$idCerca = (int)$idCerca;
			    		$opcoesCerca = "
			    			<li><a href='javascript:editaCerca(".$idCerca.",".$data['imei'].");'>Editar Cerca</a></li>
			    			<li><a href='javascript:removeCerca(".$data['imei'].");'>Remover Cerca</a></li>
		    			";
			    	}
			    	else {
			    		$opcoesCerca = "<li><a href='javascript:modalCerca(".$data['imei'].");'>Criar Cerca</a></li>";
			    	}
			    	echo "
			    	<div class=\"btn-group\">
			    		<button type=\"button\" class=\"btn btn-default dropdown-toggle cerca\" data-toggle=\"dropdown\">Comandos <span class=\"caret\"></span></button>
		    			<ul class=\"dropdown-menu\" role=\"menu\">
		    				<li><a data-toggle=\"modal\" style=\" cursor: pointer;\" onclick=\"javascript:modalHistorico(".$data['imei'].",'".$data['name']."');\" >Histórico</a></li>
		    				<li class=\"divider\"></li>
		    				$clickRast
		    				$clickVel
		    				<li><a href=\"javascript:combustivel(". $data['imei'] .",',J');\">Bloquear Combústivel</a></li>
		    				<li><a href=\"javascript:combustivel(" . $data['imei'] . ",',K');\">Liberar Combústivel</a></li>
		    				<li class=\"divider\"></li>
		    				$opcoesCerca
	    				</ul>
    				</div>";
			    	$i++;
			    ?>
				</td>
			</tr>
		<?php endwhile;?>
		</tbody>
	</table>
</div>
<!-- MODAL CONSULTA HISTÓRICO -->
<div class="modal fade" id="modal-historico">
  <div class="modal-dialog modal-lg">        
  <div class="modal-content"> 
    <div class="modal-header"> 
      <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
      <h3 class="modal-title">Histórico por Período</h3>
    </div>
    <div class="modal-body"> 
      <form method="POST" action="listagem_historico_novo.php" class="form-inline" role="form" id="consultarHistorico">
        <input type="hidden" name="nrImeiConsulta" id="nrImeiConsulta">
        <input type="hidden" name="nomeVeiculo" id="nomeVeiculo">
        <input type="hidden" name="mnDataInicio" value="00">
        <input type="hidden" name="mnDataFinal" value="00">
        <div class="row"> 
          <div class="col-lg-6"> 
            <div class="form-group"> 
              <label for="commandDateIni">Início: </label>
              <input type="date" class="form-control" id="commandDateIni" name="txtDataInicio" value="<?php echo date("Y-m-d") ?>" max="<?php echo date("Y-m-d") ?>">
            </div>
            <div class="form-group"> 
              <label class="sr-only" for="commandHourTimeIni">Hora</label>
              <select name="hrDataInicio" id="commandHourTimeIni" class="form-control">
                <option value="0">00h</option>
                <option value="1">01h</option>
                <option value="2">02h</option>
                <option value="3">03h</option>
                <option value="4">04h</option>
                <option value="5">05h</option>
                <option value="6">06h</option>
                <option value="7">07h</option>
                <option value="8">08h</option>
                <option value="9">09h</option>
                <option value="10">10h</option>
                <option value="11">11h</option>
                <option value="12">12h</option>
                <option value="13">13h</option>
                <option value="14">14h</option>
                <option value="15">15h</option>
                <option value="16">16h</option>
                <option value="17">17h</option>
                <option value="18">18h</option>
                <option value="19">19h</option>
                <option value="20">20h</option>
                <option value="21">21h</option>
                <option value="22">22h</option>
                <option value="23">23h</option>
              </select>
            </div>
          </div>
          <div class="col-lg-6"> 
            <div class="form-group"> 
              <label for="commandDateFim">Fim: </label>
              <input type="date" class="form-control" id="commandDateFim" name="txtDataFinal" value="<?php echo date("Y-m-d") ?>" max="<?php echo date("Y-m-d") ?>">
            </div>
            <div class="form-group"> 
              <label class="sr-only" for="commandHourTimeFim">Hora</label>
              <select name="hrDataFinal" id="commandHourTimeFim" class="form-control">
                <option value="0">00h</option>
                <option value="1">01h</option>
                <option value="2">02h</option>
                <option value="3">03h</option>
                <option value="4">04h</option>
                <option value="5">05h</option>
                <option value="6">06h</option>
                <option value="7">07h</option>
                <option value="8">08h</option>
                <option value="9">09h</option>
                <option value="10">10h</option>
                <option value="11">11h</option>
                <option value="12">12h</option>
                <option value="13">13h</option>
                <option value="14">14h</option>
                <option value="15">15h</option>
                <option value="16">16h</option>
                <option value="17">17h</option>
                <option value="18">18h</option>
                <option value="19">19h</option>
                <option value="20">20h</option>
                <option value="21">21h</option>
                <option value="22">22h</option>
                <option value="23">23h</option>
              </select>
            </div>
          </div>
        </div>
        <br>
        <div class="row"> 
          <div class="col-lg-9"> 
            <div class="form-group">
            <select name="cmbLimit" id="cmbLimit" class="form-control">
                <option value="20">20 Recentes</option>
                <option value="40">40 Recentes</option>
                <option value="60">60 Recentes</option>
                <option value="80">80 Recentes</option>
                <option value="100">100 Recentes</option>                
              </select> 
              <button type="button" class="btn btn-default" onClick="acertaHistorico(4);">Últimas 
              4 horas</button>
              <button type="button" class="btn btn-default" onClick="acertaHistorico(12);">Últimas 
              12 horas</button>
              <button type="button" class="btn btn-default" onClick="acertaHistorico(24);">Últimas 
              24 horas</button>
              <button type="button" class="btn btn-default" onClick="acertaHistorico(48);">Últimas 
              48 horas</button>
            </div>
          </div>
          <div class="col-lg-3 text-center"> 
            <div class="form-group"> 
              <button type="submit" class="btn btn-primary">
                <i class="fa fa-book"></i> 
                Consultar Histórico
              </button>
            </div>
          </div>
        </div>
      </form>
      <hr>
      <div id="relatorio"></div>
    </div>
    <div class="modal-footer"> 
      <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
    </div>
  </div>
  <!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
</div><!-- /#wrapper -->
<script>

function modalHistorico(p1,p2)
{	
	console.log(p1);
	console.log(p2);
	$("#nrImeiConsulta").val(p1);
	$("#nomeVeiculo").val(p2);
	$('#modal-historico').modal({show:true});	
}
// VALIDAÇÃO DO FORMULÁRIO DE CONSULTA DE HISTÓRICO
$("#consultarHistorico").validate({
	submitHandler: function (form) {
	  // Desabilita o botão de submit para evitar várias requisições
	  $("#consultarHistorico button[type=submit]").prop('disabled', true);
	  $("#consultarHistorico button[type=submit] i").removeClass('fa-book').addClass('fa-refresh fa-spin');
	  // Adiciona o IMEI e o NOME do veiculo selecionado aos inputs do formulário
	  //$("#nrImeiConsulta").val($('#bens').val());
	  //$("#nomeVeiculo").val($("#bens").find(":selected").text());
	  $.ajax({
		url: "listagem_historico_novo.php",
		type: "POST",
		data: $(form).serialize(),
		success: function (lista) {
		  console.log(lista);
		  // Exibe a tabela de resultados e põe o botão no estado normal.
		  $("#relatorio").html(lista);
		  $("#consultarHistorico button[type=submit]").prop('disabled', false);
		  $("#consultarHistorico button[type=submit] i").removeClass('fa-refresh fa-spin').addClass('fa-book');
		  // Inicializa as variáveis que serão utilizadas para ler os dados da tabela
		  var latAnt = 0;
		  var latAtu = 0;
		  var lonAtu = 0;
		  var lonAnt = 0;
		  var distance = 0.00;
		  var tabRel = $('#relatorio table').find('tbody').find('tr');
		  var latAtu = $(tabRel[0]).find('td:eq(1)').html();
		  var lonAtu = $(tabRel[0]).find('td:eq(2)').html();
		  // Checa se houve mais de um resultado retornado
		  if (tabRel.length > 2) {
			// Limpa o array com as posições de latitude e longitude da tabela
			while(posicoes.length > 0) {
			  posicoes.pop();
			}
	
			for (var i = 1; i < tabRel.length - 1; i++) {
			  // Cria um array com as latitudes e longitudes para ser usado no traçado da rota
			  posicoes.push({
				'lat': latAtu,
				'lng': lonAtu
			  });
	
			  // Calcula a quilometragem rodada
			  var latAnt = $(tabRel[i]).find('td:eq(1)').html();
			  var lonAnt = $(tabRel[i]).find('td:eq(2)').html();
	
			  var p1 = new LatLon(latAtu, lonAtu);
			  var p2 = new LatLon(latAnt, lonAnt);
	
			  distance += parseFloat(p1.distanceTo(p2));
			  //console.log('Distancia calculada ' + i + ' ' + distance);
			  
			  latAtu = latAnt;
			  lonAtu = lonAnt;
			}
	
			$('#km-rodado').html(parseInt(distance) + ' Km rodados');
			$('#tracar').removeClass('hide');
		  }
		  else {
			//console.log('Apenas um registro na tabela');
			$('#tracar').addClass('hide');
		  }
		  
		  // document.getElementById('total_km').innerHTML = parseInt(distance)+' km';
		},
		error: function (XMLHttpRequest, textStatus, errorThrown) {
		  //console.log(XMLHttpRequest);
		  //console.log(textStatus);
		  //s.log(errorThrown);
		  $("#consultarHistorico button[type=submit]").prop('disabled', false);
		  $("#consultarHistorico button[type=submit] i").removeClass('fa-refresh fa-spin').addClass('fa-book');
		}
	  });
	  return false;
	}
});

/**
 * [acertaHistorico Atualiza os campos de data e hora de acordo com o intervalo escolhido]
 * @param  {int} horas Intervalo que deseja ser visto
 */
function acertaHistorico (horas) {
  horaIni = $('#commandHourTimeIni');
  horaFim = $('#commandHourTimeFim');
  dataIni = $('#commandDateIni');

  horaAtual = new Date();
  horaFim.val(horaAtual.getHours());

  if (horaAtual.getHours() - horas < 0) {
	horaIni.val(24 - (horas - horaAtual.getHours()));
	dia = horaAtual.getDate() - 1;
	mes = horaAtual.getMonth() + 1;
	if (dia < 0 || dia === 0) {
	  dia = 1;
	  mes = mes - 1;
	}
	dataIni.val(horaAtual.getFullYear() + '-' + ('0' + mes).slice(-2) + '-' + ('0' + dia).slice(-2));
	if (horas == 48) {
	  dataIni.val(horaAtual.getFullYear() + '-' + ('0' + mes).slice(-2) + '-' + ('0' + (dia - 1)).slice(-2));
	  horaIni.val(horaAtual.getHours());
	}
  } else {
	dataIni.val(horaAtual.getFullYear() + '-' + ('0' + (horaAtual.getMonth() + 1)).slice(-2) + '-' + ('0' + horaAtual.getDate()).slice(-2));
	horaIni.val(horaAtual.getHours() - horas);
  }
}

</script>