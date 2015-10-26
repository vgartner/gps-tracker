<?php include('../seguranca.php');

$codCliente=$_GET["codCliente"];
$imei=$_GET["imei"];
$nome=$_GET["nome"];
$ident=$_GET["ident"];
$cor=$_GET["cor"];
$ativo=$_GET["ativo"];

$con = mysql_connect('cloudservice.cgejdsdl842e.sa-east-1.rds.amazonaws.com', 'gpstracker', 'd1$1793689');
if (!$con)
  {
	die('Could not connect: ' . mysql_error());
  }

mysql_select_db("tracker", $con);

if ($codCliente != "")
{
	if ($imei != "" and $nome != "" and $ativo != "") 
	{
		if (!mysql_query("INSERT INTO bem (imei, name, identificacao, cliente, activated, porta, cor_grafico, liberado, id_admin) VALUES
										  ('$imei', '$nome', '$ident', $codCliente, '$ativo', '7095', '$cor', 'S', $id_admin)", $con))
		{
			if (mysql_error() == "Duplicate entry '". $imei ."' for key 'imei'" or mysql_error() == "Duplicate entry '". $imei ."' for key 2")
				echo "IMEI duplicado";
			else
				die('Error: ' . mysql_error());
		}
		else
		{
			//Gravado com sucesso
			echo "OK";
		}
	}
}

mysql_close($con);
?>
