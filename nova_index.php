<?php
include('seguranca.php');
require_once 'config.php';
include_once 'usuario/config.php';

$cnx = mysql_connect($DB_SERVER, $DB_USER, $DB_PASS);
mysql_select_db($DB_NAME);
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>

  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title>BarukSAT - Sistema Administrativo</title>
  <?php
     $QyTemas = "
	 	SELECT estilo
		  FROM preferencias
	 ";
	 $rsTemas = mysql_query($QyTemas) or die(mysql_error());
	 $rowTemas = mysql_fetch_assoc($rsTemas);
  ?>
  <!-- Bootstrap core CSS -->
  <link href="css/<?php echo $rowTemas["estilo"];?>.css" rel="stylesheet">

  <link href="/css/jquery-ui.css" type="text/css" rel="stylesheet" />
  <link href="/css/magnific-popup.css" type="text/css" rel="stylesheet" />
  <link href="/css/nova.css" type="text/css" rel="stylesheet" />
  <link rel="stylesheet" href="font-awesome/css/font-awesome.min.css">

  <script type="text/javascript" src="js/jquery-1.7.min.js"></script>
  <script type="text/javascript" src="js/jquery-ui.js"></script>
  <script type="text/javascript" src="js/jquery.form.min.js"></script>
  <script type="text/javascript" src="js/jquery.validate.min.js"></script>
  <script type="text/javascript" src="js/painelAdmin.js"></script>
  <script type="text/javascript" src="js/jquery.magnific-popup.min.js"></script>
  <script type="text/javascript" src="js/spin.min.js"></script>
  <script type="text/javascript" src="js/bootstrap.min.js"></script>
  <script src="http://maps.google.com/maps/api/js?sensor=false"></script>
  <script src="js/polygon.min.js" type="text/javascript"></script>
  <script src="js/latlong.js" type="text/javascript"></script>
  <script src="js/geo.js" type="text/javascript"></script>

  <link rel="icon" href="http://www.baruksat.com.br/favicon-25x25.png">

  <script type="text/javascript">
   $(function() {
     $( "#tabs" ).tabs({
      beforeLoad: function( event, ui ) {
       ui.jqXHR.error(function() {
        ui.panel.html(
          "Não foi possível carregar esta aba. Atualizar a página pode solucionar este problema." +
          "Caso o  problema persista, entre em contato com o administrador." );
      });
     }
   });
   });
 </script>

 <style>

 body {
   font-size:12px;
 }

 a {
   color:#BA4845;
 }

 header {
   padding:5px;
 }

 .logo {
   width:200px;
   height:auto;
   padding:25px 15px;
 }

 #tabs, body {
   background: white;
 }

 .ui-widget-header {
   background-color:#f7f7f7;
   border:1px solid white !important;
   border-top-color:#dedede !important;
   padding: 0 !important;
   -webkit-box-shadow: 0px 2px 5px -2px rgba(0,0,0,0.46);
  -moz-box-shadow: 0px 2px 5px -2px rgba(0,0,0,0.46);
  box-shadow: 0px 2px 5px -2px rgba(0,0,0,0.46);
  border-radius: 0 !important;
 }

 .ui-tabs .ui-tabs-nav li {
    margin:0;
    background-color:#f7f7f7;
    border:1px solid white;
 }

 .ui-state-default, .ui-widget-content .ui-state-default, .ui-widget-header .ui-state-default {
   background: white;
   border-bottom: 0 !important;
 }
   .ui-state-default a {
     color:grey !important;
     font-size:14px;
   }

   .ui-state-active {

   }
   .ui-state-active a {
     color:#14305D !important;
     border-bottom: 2px solid #BA4845 !important;
   }

   #tabs ul {
     /*display: inline-block;*/
     width: 100%;
    height: auto;

   }

   .btn-primary {
     background: #14305D;
     color:white !important;
     border-color:#dedede !important;
   }

    .btn-primary:hover {
      background: #BA4845!important;
    }
 </style>
</head>


<body>


<header>
  <img class="logo" src="baruk-logo.jpg" title="BarukSat"/>
  <div class='pull-right' style="margin-top:34px; margin-right:30px">
  Sistema Administrativo |

  <a href='logout.php' title='Sair do sistema'>Deslogar</a>

  </div>
</header>


<div id="corpo">
  <div id="tabs">
    <?php

		//echo "<a href='logout.php' title='Sair do sistema' class='logout'><img src='imagens/logout.png' alt='Sair'></a>";
	?>
    <ul>
      <li><a href="#boas-vindas">Home</a></li>
      <li><a href="ajax/usuarios.php">Usuários</a></li>
      <?php if ($representante == 'N') echo "<li><a href='ajax/equipamentos.php'>Equipamentos</a></li>"; ?>
      <?php if ($representante == 'N') echo "<li><a href='ajax/preferencias_form.php'>Preferências</a></li>"; ?>
      <?php if ($cliente == 'master') echo "<li><a href='ajax/usuarios_adm.php?id=".$id_admin."'>Trocar Senha Admin</a></li>"; ?>
      <?php if ($cliente == 'master') echo "<li><a href='ajax/preferencias_smtp.php?id=".$id_admin."'>SMTP</a></li>"; ?>
      <?php if ($cliente == 'master') echo "<li><a href='ajax/preferencias_rastreadores.php?id=".$id_admin."'>Rastreadores</a></li>"; ?>
      <?php if ($cliente == 'master') echo "<li><a href='ajax/preferencias_logos.php'>Logos</a></li>"; ?>
      <?php if ($cliente == 'master') echo "<li><a href='ajax/usuarios_master.php'>Dados</a></li>"; ?>
      <?php /* if ($cliente == 'master') echo "<li><a href='ajax/preferencias_cores.php'>Temas</a></li>"; */ ?>
      <?php //echo "<div id='logo'><a href='nova_index.php'><img src='" . LOGO_ADMIN . "'></a></div>"; ?>
    </ul>
    <div id="boas-vindas">
      Seja bem vindo
   </div>
 </div>
</div>

<footer class="text-center">
  <small>BarukSat.com.br</small>
</footer>

</body>
</html>
