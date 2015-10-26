<?php

$con = mysql_connect('cloudservice.cgejdsdl842e.sa-east-1.rds.amazonaws.com', 'gpstracker', 'd1$1793689');
if (!$con)
  {
	die('Could not connect: ' . mysql_error());
  }

mysql_select_db("tracker", $con);

if (!mysql_query("UPDATE bem set date=date, activated=activated, modo_operacao=modo_operacao, liberado=liberado, status_sinal = 'D'", $con))
{
	die('Error: ' . mysql_error());
}
else
{
	//Executado com sucesso
	echo "OK";
}

mysql_close($con);
?>
