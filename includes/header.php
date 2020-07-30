<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php if(!empty($title)){ echo $title; } ?> | Log Analyzer</title>
    <link href="assets/fonts/icons/material-icons.css?v=3.0.1" rel="stylesheet">
    <link href="assets/css/materialize.min.css?v=0.100.2" rel="stylesheet">
    <link href="assets/css/materialdesignicons.css" rel="stylesheet">
    <link href="assets/css/styles.css?v=0.1.0" rel="stylesheet">
    <link rel="shortcut icon" href="assets/images/logo.png">
    <script src='http://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js'></script>
    <script src='https://www.google.com/jsapi'></script>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <!--[if IE]>
      <script type="text/javascript" src="assets/js/html5shiv.min.js?v=3.7.3"></script>
      <script type="text/javascript" src="assets/js/respond.min.js?v=1.4.2"></script>
    <![endif]-->
  </head>
  <style type="text/css">
  </style>
  <body class="grey lighten-4">
  <header>
  <nav class="hide-on-large-only white">
    <div class="nav-wrapper">
  <a href="#" data-activates="slide-out" class="button-collapse"><i class="material-icons grey-text text-darken-3">menu</i></a>
      <a href="./" class="brand-logo grey-text text-darken-3"><img class="brand-image" src="assets/images/logo.png"></a>
    </div>
  </nav>
  <ul id="slide-out" class="side-nav fixed z-depth-3">
    <li><div class="user-view">
      <div class="background">
        <img src="assets/images/drawer_bg.jpg" height="100%">
      </div>
      <a href="account.php">
        <img class="circle z-depth-1" src="<?php echo 'users/'.$user['username'].'/'.$user['profile_pic']; ?>">
        <span class="grey-text text-darken-3 name"><?php echo $user['f_name'].' '.$user['l_name']; ?></span>
        <span class="grey-text text-darken-3 email"><?php echo $user['email']; ?></span>
      </a>
    </div></li>
    <li><a class="waves-effect" href="./"><i class="material-icons">&#xE871;</i>Overview</a></li>
    <li><a class="waves-effect <?php if(strpos($_SERVER['REQUEST_URI'], "/analyze.php")){ echo "active"; } ?>" href="analyze.php"><i class="material-icons">&#xE89C;</i>Analyze</a></li>
    <?php if($user['role'] == 'admin'){ ?>
    <li><a class="waves-effect <?php if(strpos($_SERVER['REQUEST_URI'], "/case_overview.php")){ echo "active"; } ?>" href="case_overview.php"><i class="material-icons">&#xE8EF;</i>Case Overview</a></li>
    <li><div class="divider"></div></li>
    <?php
      $count = $conn->prepare('SELECT * FROM users');
      $count->execute();
    ?>
    <li><a class="waves-effect <?php if(strpos($_SERVER['REQUEST_URI'], "/users.php")){ echo "active"; } ?>" href="users.php"><i class="material-icons">&#xE7EF;</i><span class="badge"><?php echo $count->rowCount(); ?></span>Users</a></li>
    <li><a class="waves-effect <?php if(strpos($_SERVER['REQUEST_URI'], "/add_users.php")){ echo "active"; } ?>" href="add_users.php"><i class="material-icons">&#xE7FE;</i>Add Users</a></li>
	<li><a class="waves-effect <?php if(strpos($_SERVER['REQUEST_URI'], "/attack_vectors.php")){ echo "active"; } ?>" href="attack_vectors.php"><i class="material-icons">&#xe868</i>Attack Vectors</a></li>
    <?php }else{ ?>
    <li><a class="waves-effect" href="case_history.php"><i class="material-icons">&#xE8EF;</i>Case History</a></li>
    <?php } ?>
    <li><div class="divider"></div></li>
    <li><a class="waves-effect" href="logout.php"><i class="material-icons">&#xE879;</i>Logout</a></li>
    <li><a class="waves-effect" href=""><i class="material-icons">&#xE5D5;</i><span id="timer">30 m 00 s</span></a></li>
  </ul>
  </header>
  <main>
  <div id="timeout" class="modal">
      <div class="modal-content">
        <p>You will be logged out in <b id="modal_timer">60</b> seconds. Do you want to stay logged in?</p>
      </div>
      <div class="modal-footer">
        <a href="logout.php" class="modal-action waves-effect btn-flat red-text">no</a>
        <a href="./" class="modal-action waves-effect btn-flat">Yes</a>
      </div>
  </div>
    <div class="container">
      <h1 class="thin grey-text text-darken-1"><?php if(!empty($title)){ echo $title; } ?></h1>
