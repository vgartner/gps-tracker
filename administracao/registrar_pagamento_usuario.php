<?php include('../seguranca.php');

$codigoCliente=$_GET["codigoCliente"];
$mesPagamento=$_GET["mesPagamento"];
$pagamento=$_GET["pagamento"]; //F=falta informar; N=Nao pagou;S=pagou

$con = mysql_connect('cloudservice.cgejdsdl842e.sa-east-1.rds.amazonaws.com', 'gpstracker', 'd1$1793689');
if (!$con)
  {
	die('Could not connect: ' . mysql_error());
  }

mysql_select_db("tracker", $con);

if ($codigoCliente != "")
{
	if ($mesPagamento != "" and pagamento != "")
	{
		/** Retorna o mes de pagamento*/
		function obterMesPagamento($nrMes)
		{
			$stMes = "";
			
			switch($nrMes)
			{
				case "1": $stMes = "jane"; break;
				case "2": $stMes = "feve"; break;
				case "3": $stMes = "marc"; break;
				case "4": $stMes = "abri"; break;
				case "5": $stMes = "maio"; break;
				case "6": $stMes = "junh"; break;
				case "7": $stMes = "julh"; break;
				case "8": $stMes = "agos"; break;
				case "9": $stMes = "sete"; break;
				case "10": $stMes = "outu"; break;
				case "11": $stMes = "nove"; break;
				case "12": $stMes = "deze"; break;
				
				//default: $imgPagamento = "registra_pgto.gif";
			}

			return $stMes;
		}
	
	
		$resPag = mysql_query("SELECT 1 FROM pagamento WHERE cliente = $codigoCliente and ano = EXTRACT(YEAR FROM CURDATE())");
		
		if (mysql_num_rows($resPag) == 0) {
			//Inclui na tabela de pagamentos do ano
			if (!mysql_query("INSERT INTO pagamento (cliente, ano, ". obterMesPagamento($mesPagamento) .") VALUES ($codigoCliente, EXTRACT(YEAR FROM CURDATE()), '$pagamento')", $con))
			{
				die('Error: ' . mysql_error());
			}
			else
			{
				//Gravado com sucesso
				echo "OK";
			}
		} else {
			//Altera a tabela de pagamentos do ano
			if (!mysql_query("UPDATE pagamento set ". obterMesPagamento($mesPagamento) ." = '$pagamento' WHERE cliente = $codigoCliente and ano = EXTRACT(YEAR FROM CURDATE())", $con))
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
}

mysql_close($con);
?>
