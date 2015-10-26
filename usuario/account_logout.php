<?php
	include("config.php");

	// setting cookie back to neutral without $vars:
	//setcookie("authacc");
?>
<?php
require("account_inc.php");

// making header and title:
make_header("account: logout");
?>
Succesfully logged out!<br>
<a href="<?php

// as defined in the config.php
echo $logout_url; 

?>">click here</a> to continue
<?php
// making footerrr:
make_footer(); 
?>