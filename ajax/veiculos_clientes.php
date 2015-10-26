<?php
$cnx = mysql_connect("cloudservice.cgejdsdl842e.sa-east-1.rds.amazonaws.com", "gpstracker", "d1$1793689")
  or die("Could not connect: " . mysql_error());
mysql_select_db('tracker', $cnx);

$cliente = strip_tags($_GET['cliente']);

if(!empty($id)){
	$resCliente = mysql_query("select * from bem where cliente = '$cliente'", $cnx);
}
?>
<table>

<?php while($dataBem = mysql_fetch_assoc($resCliente)):?>
<tr>
	<td>IMEI</td>
    <td>Nome</td>
    <td>Identificação</td>
    <td>Ativo</td>
    <td>Liberado</td>
    <td>Status</td>
    <td>Ligado</td>
    <td>Tipo</td>
</tr>
<tr>
	<td><?=$dataBem['imei']?></td>
    <td><?=$dataBem['name']?></td>
    <td><?=$dataBem['identificacao']?></td>
    <td><?=$dataBem['activated']?></td>
    <td><?=$dataBem['liberado']?></td>
    <td><?=$dataBem['status_sinal']?></td>
    <td><?=$dataBem['ligado']?></td>
    <td><?=$dataBem['tipo']?></td>
</tr>
<?php endwhile;?>

</table>
<?
mysql_close($cnx);
?>