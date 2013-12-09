<?php
// sleep(20); // test client side timeout

ob_start();
echo "== POST\n";
var_dump($_POST);
/*
echo "\n== GET\n";
var_dump($_GET);
echo "\n== ENV\n";
var_dump($_ENV);
echo "\n== SERVER\n";
var_dump($_SERVER);
*/

$c = ob_get_contents();
ob_end_clean();

$fp = @fopen('post.txt', 'w');

if ($fp) {
	fwrite($fp, $c);
	fclose($fp);
} else {
	$protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');
	header("$protocol 500 Internal Server Error");
	header("Content-type: text/plain");
	echo "ERROR: FAILED TO WRITE FILE. check permissions\n\n";
	echo $c;
}
	
	

?>
