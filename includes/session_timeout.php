<?php
$now = time();
if(isset($_SESSION['timeout']) && $now > $_SESSION['timeout']) {
	session_unset();
	session_destroy();
	session_start();
}
$_SESSION['timeout'] = $now + 1800;
?>