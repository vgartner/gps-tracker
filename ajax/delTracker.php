<?php
include('../seguranca.php');

if ($cliente != "master")
{
	header("location: ../nova_index.php");
}
else
{
	$cnx = mysql_connect("cloudservice.cgejdsdl842e.sa-east-1.rds.amazonaws.com", "gpstracker", "d1$1793689")  or die("Could not connect: " . mysql_error());
	mysql_select_db('tracker', $cnx);
	
	$tracker = $_GET['rastreador'];
	
	$Qy = "
		delete from rastreadores
		 where rastreador = '".$tracker."'
	";
	$ans = mysql_query($Qy) or die(mysql_error());
	if ($ans)
	{
		header('location: ../nova_index.php?message=OK');
	}
	else
	{
		header('location: ../nova_index.php?message=Desculpe não foi possível apagar o rastreador. Primeiro certifique-se que nenhum cliente está utilizando o rastreador antes de retirá-lo do sistema. ');
	}
}
?>