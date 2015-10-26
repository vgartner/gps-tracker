<?php
include("config.php");

$cnx = mysql_connect($DB_SERVER, $DB_USER, $DB_PASS) or die("Could not connect: " . mysql_error());
mysql_select_db($DB_NAME, $cnx);

$consulta = "select id 
               from cliente 
  			  where email = '".$_POST["email"]."'";
$ans = mysql_query($consulta);

while ($rows = mysql_fetch_assoc($ans)) {
	echo $rows['id'];
}
?>
