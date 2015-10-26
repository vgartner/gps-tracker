<?php
include("config.php");

$cnx = mysql_connect($DB_SERVER, $DB_USER, $DB_PASS) or die("Could not connect: " . mysql_error());
mysql_select_db($DB_NAME, $cnx);

$consulta = "update cliente set senha = ".$senha."
  			  where id = '".$_POST["id"]."'";
$ans = mysql_query($consulta);

while ($rows = mysql_fetch_assoc($ans)) {
	echo $rows['id'];
}
?>
