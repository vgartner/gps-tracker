<?php include('../seguranca.php');

$codCliente=$_GET["codCliente"];
$imei=$_GET["imei"];
$idBem=$_GET["idBem"];

$con = mysql_connect('cloudservice.cgejdsdl842e.sa-east-1.rds.amazonaws.com', 'gpstracker', 'd1$1793689');
if (!$con)
  {
	die('Could not connect: ' . mysql_error());
  }

mysql_select_db("tracker", $con);

$result = "OK";

if ($codCliente != "" and $imei != "" and $idBem != "")
{
	if (!mysql_query("DELETE from bem WHERE cliente = $codCliente and imei = $imei and id = $idBem", $con))
	{
		$result = 'Error: ' . mysql_error();
	}
	else
	{
		$result = "OK";
	}
}

echo $result;

mysql_close($con);
?>
