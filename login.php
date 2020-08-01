<?php
session_start();
if(isset($_SESSION['user_id'])) {
  header("Location: ./");
}
$message = '';
$ip = '';
require("includes/connection.php");
if(isset($_GET['another'])){
  setcookie("name", "", time()-3);
  setcookie("username", "", time()-3);
  setcookie("profile_pic", "", time()-3);
  echo "<script>window.location.replace('login.php');</script>";
}
if(isset($_POST['login'])){
  if(!empty($_POST['username_email']) || !empty($_POST['password'])){
    $conn = connect_pdo();
    $records = $conn->prepare('SELECT * FROM users WHERE `username` = :username_email OR `email` = :username_email');
    $records->bindParam(':username_email', $_POST['username_email']);
    $records->execute();
    $results = $records->fetch(PDO::FETCH_ASSOC);
	if(empty($results)){
		$message = '<div id="message" class="card-panel red white-text center-align">Username or password is empty.</div>';
	}
   //print_r($results);die();
    if(password_verify($_POST['password'], $results['password'])){
      $_SESSION['user_id'] = $results['id'];
      $c_name = $results['f_name']. ' ' .$results['l_name'];
      $c_username = $results['username'];
      $c_profile_pic = $results['profile_pic'];
      setcookie("name", $c_name, time()+31556926);
      setcookie("username", $c_username, time()+31556926);
      setcookie("profile_pic", $c_profile_pic, time()+31556926);
      header("location: ./");
      } else {
    $username = $_POST['username_email'];
    $password = $_POST['password'];
    $test_url['link'] = "google.com";
    $test_url['port'] = 80;
    $connection = @fsockopen($test_url['link'], $test_url['port']);
    if($connection){
      $public_ip = file_get_contents("http://checkip.amazonaws.com");
    }else{
      $public_ip = "0.0.0.0";
    }
    $fail_time = date("Y-m-d H:i:s");
    $conn = connect_pdo();
    $login_attempts = $conn->prepare("INSERT INTO login_attempts (`username`, `password`, `public_ip`, `fail_time`) VALUES(?, ?, ?, ?)");
    $login_attempts->execute(array($username, $password, $public_ip, $fail_time));
      $message = '<div id="message" class="card-panel red white-text center-align">Incorrect username or password. Please try again</div>';
    }
  }else{
    $message = '<div id="message" class="card-panel red white-text center-align">Username or password is empty.</div>';
  }
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Log Analyzer</title>
    <link href="assets/fonts/icons/material-icons.css?v=3.0.1" rel="stylesheet">
    <link href="assets/css/materialize.min.css?v=0.100.1" rel="stylesheet">
    <link href="assets/css/login.css?v=1.0" rel="stylesheet">
    <!--[if lt IE 9]>
      <script type="text/javascript" src="assets/js/html5shiv.min.js?v=3.7.3"></script>
      <script type="text/javascript" src="assets/js/respond.min.js?v=1.4.2"></script>
    <![endif]-->
  </head>
  </head>
  <body class="grey lighten-4">
  <main>  
  <div class="section"></div>
  <div class="section"></div>
  <div class="section"></div>
    <div class="container">
          <div class="row">
          <div class="col s12 m8 l6 offset-m2 offset-l3">
          <div class="card">
            <div class="card-content grey-text text-darken-3">
              <span class="card-title">Login<img class="right resposive-img" src="assets/images/logo.png" width="40px"></span>
              <p><br>
                <div class="row">
                  <form class="col s12" method="POST" action="">
                    <div class="row">
                      <div class="input-field col s12">
                      <?php if(!isset($_COOKIE['username'])) { ?>
                        <input id="username" name="username_email" type="text" class="validate" required>
                        <label for="username">Username or Email</label>
                      <?php }else{ ?>
                      <div class="center">
                        <img class="responsive-img z-depth-1 circle hoimage" src="<?php if(isset($_COOKIE['profile_pic'])){ echo "users/".$_COOKIE['username']."/".$_COOKIE['profile_pic']; }else{ echo "assets/images/person.png"; } ?>" style="width:50px;height:50px;background:#eee;">
                      </div>
                        <h5 class="center light" style="font-size:20px;"><?php echo $_COOKIE['name']; ?></h5>
                        <input id="username" name="username_email" type="hidden" value="<?php echo $_COOKIE['username']; ?>">
                        <?php $another = '<a class="left" href="?another">Use another account</a>'; ?>
                      <?php } ?>
                      </div>
                      <div class="input-field col s12">
                        <input id="password" name="password" type="password" class="validate" required>
                        <label for="password">Password</label>
                      </div>
                    </div>
                      <?php
                        if(!empty($message)){
                          echo $message;
                        }
                        if(!empty($another)){
                          echo $another;
                        }
                      ?>
                    <button type="submit" name="login" class="waves-effect waves-light btn blue right">Login</button>
                    <br><br><br><br>
                  </form>
                </div>
              </p>
            </div>
          </div>
          </div>
          </div>
    </div>
  </main>
    <script type="text/javascript" src="assets/js/jquery.min.js?v=3.2.1"></script>
    <script type="text/javascript" src="assets/js/materialize.min.js?v=0.100.1"></script>
    <script type="text/javascript">
      $('#message').delay(2000).fadeOut(1500);
    </script>
  </body>
</html>