<?php
include('seguranca.php');
include('usuario/config.php');
error_reporting(0);

$con = mysql_connect($DB_SERVER, $DB_USER, $DB_PASS);
mysql_select_db($DB_NAME);

$idCliente = $_GET['cliente'];
$dataCliente = null;
$dataBem = null;
if(!empty($idCliente)):
$result = mysql_query("SELECT * FROM cliente WHERE id = $idCliente", $con);
$dataCliente = mysql_fetch_assoc($result);
$resBem = mysql_query("SELECT * FROM bem WHERE cliente = $idCliente AND identificacao = ''", $con);

function valorPorExtenso($valor=0, $complemento=true) {
	$singular = array("centavo", "real", "mil", "milhão", "bilhão", "trilhão", "quatrilhão");
	$plural = array("centavos", "reais", "mil", "milhões", "bilhões", "trilhões","quatrilhões");
 
	$c = array("", "cem", "duzentos", "trezentos", "quatrocentos","quinhentos", "seiscentos", "setecentos", "oitocentos", "novecentos");
	$d = array("", "dez", "vinte", "trinta", "quarenta", "cinquenta","sessenta", "setenta", "oitenta", "noventa");
	$d10 = array("dez", "onze", "doze", "treze", "quatorze", "quinze","dezesseis", "dezesete", "dezoito", "dezenove");
	$u = array("", "um", "dois", "três", "quatro", "cinco", "seis","sete", "oito", "nove");
 
	$z=0;
 
	$valor = number_format($valor, 2, ".", ".");
	$inteiro = explode(".", $valor);
	for($i=0;$i<count($inteiro);$i++)
		for($ii=strlen($inteiro[$i]);$ii<3;$ii++)
			$inteiro[$i] = "0".$inteiro[$i];
 
	// $fim identifica onde que deve se dar junção de centenas por "e" ou por "," ;) 
	$fim = count($inteiro) - ($inteiro[count($inteiro)-1] > 0 ? 1 : 2);
	for ($i=0;$i<count($inteiro);$i++) {
		$valor = $inteiro[$i];
		$rc = (($valor > 100) && ($valor < 200)) ? "cento" : $c[$valor[0]];
		$rd = ($valor[1] < 2) ? "" : $d[$valor[1]];
		$ru = ($valor > 0) ? (($valor[1] == 1) ? $d10[$valor[2]] : $u[$valor[2]]) : "";
	
		$r = $rc.(($rc && ($rd || $ru)) ? " e " : "").$rd.(($rd && $ru) ? " e " : "").$ru;
		$t = count($inteiro)-1-$i;
		if ($complemento == true) {
			$r .= $r ? " ".($valor > 1 ? $plural[$t] : $singular[$t]) : "";
			if ($valor == "000")$z++; elseif ($z > 0) $z--;
			if (($t==1) && ($z>0) && ($inteiro[0] > 0)) $r .= (($z>1) ? " de " : "").$plural[$t]; 
		}
		if ($r) $rt = $rt . ((($i > 0) && ($i <= $fim) && ($inteiro[0] > 0) && ($z < 1)) ? ( ($i < $fim) ? ", " : " e ") : " ") . $r;
	}
 
	return($rt ? $rt : "zero");
}
?>

<!DOCTYPE html>
<html lang="pt-br">
	<head>
		<meta charset="utf-8">
		<link rel="stylesheet" href="css/normalize.css" type="text/css">
		<link rel="stylesheet" href="css/bootstrap.min.css" type="text/css">
		<title>Relatório de Instalação</title>
	</head>
	<style>
		@media all {
			.break { display: none; }
		}

		@media print {
			.break { display: block; page-break-before: always; }
		}
	</style>
	<body>
		<h4 class="text-center"><strong>FORMULÁRIO DE INSTALAÇÃO DE EQUIPAMENTOS</strong></h4>
		<br>
		<p class="lead text-center">DADOS DO CLIENTE</p>
		<br>

		<div class="row">
			<div class="col-xs-8"><strong>Nome: </strong><?=strtoupper($dataCliente['nome'])?></div>
			<div class="col-xs-4"><strong>Nacionalidade: </strong><?=strtoupper($dataCliente['nacionalidade'])?></div>
		</div>
		<div class="row">
			<div class="col-xs-5"><strong>Tipo de Pessoa: </strong><?php echo ($dataCliente['tipo_pessoa'] == 'F' || $dataCliente['tipo_pessoa'] == '') ? 'FÍSICA' : 'JURÍDICA'; ?></div>
			<div class="col-xs-7"><strong>Endereço: </strong><?=strtoupper($dataCliente['endereco'])?></div>
		</div>
		<div class="row">
			<div class="col-xs-4"><strong>RG: </strong><?=$dataCliente['rg']?></div>
			<div class="col-xs-4"><strong>CPF: </strong><?=$dataCliente['cpf']?></div>
			<div class="col-xs-4"><strong>Telefone: </strong><?=$dataCliente['telefone1']?></div>
		</div>
		<br><br>

		<p class="lead text-center">DADOS DO VEÍCULO</p>
		<?php 
			$en_pt = array(
				"Dom"=>"Sun",
				"Seg"=>"Mon",
				"Ter"=>"Tue",
				"Qua"=>"Wed",
				"Qui"=>"Thu",
				"Sex"=>"Fri",
				"Sab"=>"Sat",
				"Janeiro"=>"January",
				"Fevereiro"=>"February",
				"Março"=>"March",
				"Abril"=>"April",
				"Maio"=>"May",
				"Junho"=>"June",
				"Julho"=>"July",
				"Agosto"=>"August",
				"Setembro"=>"September",
				"Outubro"=>"October",
				"Novembro"=>"November",
				"Dezembro"=>"December"
			);
			$mes_en = date("F");
			$mes_en = array_search($mes_en,$en_pt);
		?>
		<br>

		<?php $totalBem = mysql_num_rows($resBem); ?>
		<p><strong>Total de Rastreadores Instalados: </strong><?=$totalBem?>  Unidade(s).</p>
		<br>
		<p>
		<?php while($dataBem = mysql_fetch_assoc($resBem)):?>
		<div class="row">
			<div class="col-xs-3"><strong>Tipo: </strong><?=$dataBem['tipo']?></div>
			<div class="col-xs-3"><strong>Modelo: </strong><?=$dataBem['marca']?></div>
			<div class="col-xs-3"><strong>Cor: </strong><?=$dataBem['cor']?></div>
			<div class="col-xs-3"><strong>Ano: </strong><?=$dataBem['ano']?></div>
		</div>
		<div class="row">
			<div class="col-xs-4"><strong>Placa: </strong><?=$dataBem['name']?></div>
			<div class="col-xs-4"><strong>Modelo Rastreador: </strong><?=$dataBem['modelo_rastreador']?></div>
			<div class="col-xs-4"><strong>IMEI: </strong><?=$dataBem['imei']?></div>
		</div>
		<div class="row">
			<div class="col-xs-6"><strong>Nº do CHIP: </strong>____________________________</div>
			<div class="col-xs-6"><strong>Operadora: </strong>____________________________</div>
		</div>
		<br><br><br>
		<?php endwhile;?>
		</p>

		<div class="row">
			<?php 
				$en_pt = array(
					"Dom"=>"Sun",
					"Seg"=>"Mon",
					"Ter"=>"Tue",
					"Qua"=>"Wed",
					"Qui"=>"Thu",
					"Sex"=>"Fri",
					"Sab"=>"Sat",
					"Janeiro"=>"January",
					"Fevereiro"=>"February",
					"Março"=>"March",
					"Abril"=>"April",
					"Maio"=>"May",
					"Junho"=>"June",
					"Julho"=>"July",
					"Agosto"=>"August",
					"Setembro"=>"September",
					"Outubro"=>"October",
					"Novembro"=>"November",
					"Dezembro"=>"December"
				);
				$mes_en = date("F");
				$mes_en = array_search($mes_en,$en_pt);
			?>
			<div class="col-xs-12"><p class="text-center">#CIDADE# - #ESTADO#, <?php echo date("d") ." de ". $mes_en ." de ". date("Y") ."."; ?></p></div>
			<br> <br> <br> <br>

			<div class="col-xs-12">
				<p class="text-center">________________________________________</p>
				<p class="text-center"><?php echo strtoupper($dataCliente['nome']); ?></p>
			</div>
		</div>
		<br>
	</body>
</html>
<?php endif; mysql_close($con); ?>