<?php include('../seguranca.php');

$codCliente=$_GET["codCliente"];
$imei=$_GET["imei"];
$imeiAntigo=$_GET["imeiAntigo"];
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
		if (!mysql_query("UPDATE bem set 
							name 		  = '$nome',
							identificacao = '$ident',
							cor_grafico   = '$cor',
							activated	  = '$ativo',
							imei          = '$imei'
						  WHERE imei 	= '$imeiAntigo' and 
								cliente = $codCliente", $con))
		{
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
