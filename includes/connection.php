<?php
require_once('session_timeout.php');
require_once('define.php');
require_once('functions.php');
require_once('class.php');
require_once('includes/UAParser/UAParser.php');
require_once('includes/geoiploc.php');
require_once('includes/attack_detection.php');
require_once('includes/iplogger.php');
// PDO connection
function connect_pdo(){
    $dsn = 'mysql:host='.HOST.';dbname='.DBNAME.';charset=utf8';
    $conn = new PDO($dsn, USERNAME, PASSWORD);
    return $conn;
}

// MySQLi connection
function connect_mysqli(){
	$conn = mysqli_connect(HOST, USERNAME, PASSWORD, DBNAME);
	if (!$conn) {
		echo "Connection failed: " . mysqli_connect_error();
	}
	return $conn;
}



?>
