<?php
include('seguranca.php');
header("Content-Type: text/html; charset=utf-8");
if ( !isset($_POST['id']) or !isset($_POST['addr']) ) {
    exit();
}
$idRota = (int) $_POST['id'];
$address = $_POST['addr'];
$address = addslashes($address);

$con = mysql_connect('cloudservice.cgejdsdl842e.sa-east-1.rds.amazonaws.com', 'gpstracker', 'd1$1793689');
if (!$con) { die('Could not connect: ' . mysql_error()); }

mysql_select_db("tracker", $con);

if (!mysql_query("UPDATE gprmc set address = '". utf8_decode($address) ."', date = date where id = $idRota", $con))
{
    //die('Error: ' . mysql_error());
}
mysql_close($con);
?>