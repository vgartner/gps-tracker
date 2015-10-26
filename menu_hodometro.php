<?php
include('seguranca.php');
include('usuario/config.php');

$con = mysql_connect($DB_SERVER, $DB_USER, $DB_PASS);
mysql_select_db($DB_NAME);
$imei = $_GET['imei'];
$acao = $_GET['acao'];
$hodomentro = $_GET['hodometro'];
$alerta_hodometro = $_GET['alerta_hodometro'];

if($acao == 'hodometro_atual'){
	if($imei != '' && $cliente!=''){
		$res = mysql_query("SELECT hodometro, alerta_hodometro FROM bem WHERE imei = '$imei'", $con);
		$dataRes = mysql_fetch_assoc($res);
		// if($dataRes['hodometro'] > 0)
			// echo "[".(int)($dataRes['hodometro']/1000).",".$dataRes['alerta_hodometro']."]";
			// else
		// echo "[".$dataRes['hodometro'].",".$dataRes['alerta_hodometro']."]";
		echo json_encode($dataRes);
	}
	return;
}

if($acao == 'salvar_hodometro'){
	if($imei != '' && $cliente != '' && !empty($hodomentro)){
		// if($hodomentro > 0) $hodomentro = $hodomentro * 1000;
		if(mysql_query("UPDATE bem SET hodometro = $hodomentro, alerta_hodometro = $alerta_hodometro, alerta_hodometro_saldo = $alerta_hodometro WHERE imei = '$imei'")){
			echo 'OK';
		} else {
			echo 'Ops! Algo deu errado: '. mysql_error();
		}
	}
	return;
}
?>