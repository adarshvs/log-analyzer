<?php 
session_start();
require("includes/connection.php");
session_unset();
session_destroy();
session_start();
header('Location: ./');