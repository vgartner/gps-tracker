<?php include('seguranca.php');

//Variavel $cliente setada na sessão no include de segurança
//$imei=isset($_GET['imei']) ? $_GET['imei'] : "";

$con = mysql_connect('cloudservice.cgejdsdl842e.sa-east-1.rds.amazonaws.com', 'gpstracker', 'd1$1793689');
if (!$con)
  {
	die('Could not connect: ' . mysql_error());
  }

mysql_select_db("tracker", $con);

if ($cliente != "")
{
	$sql="select imei, status_sinal from bem where cliente = $cliente";

	$result = mysql_query($sql);

	while($data = mysql_fetch_assoc($result))
	{
		echo "<img id='img_status_sinal". $data[imei] ."' src='imagens/". imagenStatusSinal($data[status_sinal]) ."' border='0' />";
	}
}

/** Retorna a imagem do status do sinal */
function imagenStatusSinal($sgSinal)
{
	$imgSinal;
	
	switch($sgSinal)
	{
		case "R": $imgSinal = "status_rastreando.png"; break;
		case "S": $imgSinal = "status_sem_sinal.png"; break;
		case "D": $imgSinal = "status_desligado.png"; break;
	}

	return $imgSinal;
}

mysql_close($con);
?>