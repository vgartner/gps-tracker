<?php
function make_header($title) { ?>
<html>
<head>
<title>website: <?php echo $title; ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>

<body bgcolor="#ffffff" text="#c0c0c0" leftmargin="0" topmargin="0" link="#CCCCCC" vlink="#CCCCCC" alink="#CCCCCC">
<div align="left"> 
  <p align="left">Advanced User Authentication System (PHP/mySQL) </p>
  <p align="left">&nbsp;</p>
  <p align="left"><br>
    [header]</p>
  <hr noshade>
<?php }

function make_footer() { ?>
  <hr noshade>
  <br>
  [footer] </div>
</body>
</html>
<?php } ?>