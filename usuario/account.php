<?php

$auth = false;

//if (isset($HTTP_COOKIE_VARS["authacc"])) {
if (isset($_COOKIE["authacc"])) {
echo "achei cookie";
	//$twovar = $HTTP_COOKIE_VARS["authacc"];
	$twovar = $_COOKIE["authacc"];
	list($gotuser,$gotpass) = explode(":",$twovar);

    include("config.php");
    mysql_connect($DB_SERVER, $DB_USER, $DB_PASS) or die ("Não foi possivel conectar ao Mysql") ;
    mysql_select_db($DB_NAME);
    $sql = "SELECT * FROM alerts WHERE 
            responsible = '$gotuser' AND 
            password = '$gotpass'"; 

    $result = mysql_query( $sql ) 
        or die ( 'Unable to execute query.' ); 

    // Get number of rows in $result. 
    $num = mysql_numrows( $result ); 
    if ( $num != 0 ) { 
        // vars found in db, auth=true:
        $auth = true; 
    }

	if ( ! $auth ) {
		// message when cookie is cheated:
		echo 'Invalid cookie!';

		exit;
    } 
} else {
echo "nao achei cookie";
	// the stuff that happens when cookie is not set:

	//requiring the layout file:
	require("account_inc.php");

	// this makes the header, and fills up the header title
	make_header("account: main title");

	// short login message
	echo "Please <a href=\"account_login.php\">login</a>";

	// making the footer:
	make_footer();

	exit;

}
?>
<?php
	require("account_inc.php");

	// this makes the header, and fills up the header title
	make_header("account: main <--= title");
?>

ACCOUNT CONTENT
<a href="account_logout.php">logout</a>

<?php
	// making the footer:
	make_footer(); 
?>