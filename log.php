<?php
$fh = null;

function abrirArquivoLog() {
	GLOBAL $fh;
	
	//$fn = ".".dirname(__FILE__)."/sites/1/logs/Log_". trim($imeiLog) .".log";
	$fn = "sites/1/logs/Debug.log";
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

function printLog($mensagem ) {
	GLOBAL $fh;
	
    if ($fh != null)
		fwrite($fh, $mensagem.chr(13).chr(10));
}

?>