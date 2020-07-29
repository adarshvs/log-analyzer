<?php
session_set_cookie_params(0);
session_start();
require("includes/connection.php");
if(isset($_SESSION['user_id'])) {
  $conn = connect_pdo();
  $records = $conn->prepare('SELECT * FROM users WHERE id = :id');
  $records->bindParam(':id',$_SESSION['user_id']);
  $records->execute();
  $results = $records->fetch(PDO::FETCH_ASSOC);
  $user = NULL;
  if(count($results) > 0) {
    $user = $results;
  }
}
if(!empty($user)):
$title = "Add Users";
include_once('includes/header.php');
  if(isset($_POST['add_user'])){
  if($_POST["password"] == $_POST["confirm_password"]) {
    $first_name = cleaner($_POST['f_name']);
    $last_name = cleaner($_POST['l_name']);
    $email = cleaner($_POST['email']);
    $gender = cleaner($_POST['gender']);
    $dob = cleaner($_POST['dob_submit']);
    $role = cleaner($_POST['role']);
    $username = cleaner($_POST['username']);
    $password = cleaner($_POST['password']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    if(!empty($_FILES['profile_pic']['tmp_name'])){
      $profile_pic = $_FILES['profile_pic']['tmp_name'];
      $profile_name = $_FILES['profile_pic']['name'];
    }
    $register_time = date_default_timezone_set("Indian/Mahe");
    $register_time = date("Y-m-d H:i:s");
    $conn = connect_pdo();
    $sql_check_username = "SELECT `username` FROM `users` WHERE `username` = :username";
    $check_username = $conn->prepare($sql_check_username);
    $check_username->bindValue(':username', $username);
    $check_username->execute();
    $sql_check_email = "SELECT `email` FROM `users` WHERE `email` = :email";
    $check_email = $conn->prepare($sql_check_email);
    $check_email->bindValue(':email', $email);
    $check_email->execute();
    if($check_username->rowCount() > 0 || $check_email->rowCount() > 0){
      $message = '<div id="message" class="card col s12 m10 l8 offset-m1 offset-l2 red white-text"><div class="card-content center-align">Sorry user already exist!</div></div>';
    }else{
      if(exif_imagetype($profile_pic)){
        if(!file_exists("users/$username")) {
          mkdir("users/$username", 0777, true);
        }
        move_uploaded_file($profile_pic, "users/$username/" . $profile_name);
      $sql = "INSERT INTO `users`(`f_name`, `l_name`, `email`, `gender`, `dob`, `role`, `username`, `password`, `profile_pic`, `register_time`) VALUES (:first_name, :last_name, :email, :gender, :dob, :role, :username, :password, :profile_pic, :register_time)";
      $insert = $conn->prepare($sql);
      $insert->bindValue(':first_name', $first_name);
      $insert->bindValue(':last_name', $last_name);
      $insert->bindValue(':email', $email);
      $insert->bindValue(':gender', $gender);
      $insert->bindValue(':dob', $dob);
      $insert->bindValue(':role', $role);
      $insert->bindValue(':username', $username);
      $insert->bindValue(':password', $password);
      $insert->bindValue(':profile_pic', $profile_name);
      $insert->bindValue(':register_time', $register_time);
      if($insert->execute() == TRUE){
        $message = '<div id="message" class="card col s12 m10 l8 offset-m1 offset-l2 green white-text"><div class="card-content center-align">'.$first_name.' '.$last_name.' is registered.</div></div>';
      }else{
        $message = '<div id="message" class="card col s12 m10 l8 offset-m1 offset-l2 red white-text"><div class="card-content center-align">Error Occured</div></div>';
      }
    }else{
      $message = '<div id="message" class="card col s12 m10 l8 offset-m1 offset-l2 red white-text"><div class="card-content center-align">Please upload a valid image.</div></div>';
      }
    }
  }else{
      $message = '<div id="message" class="card col s12 m10 l8 offset-m1 offset-l2 red white-text"><div class="card-content center-align">Passwords do not match.</div></div>';
  }
}
?>
<div class="card-panel">
<?php
if(!empty($message)){
  echo $message;
  //echo '<script>window.location.replace("add_users.php");</script>';
}
?>
  <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" enctype="multipart/form-data">
    <div class="row">
    <div class="section"></div>
      <div class="col s12 m8 push-m4">
        <div class="row">
          <div class="input-field col s12 m6">
            <input id="first_name" type="text" class="validate" name="f_name" required="">
            <label for="first_name">First Name</label>
          </div>
          <div class="input-field col s12 m6">
            <input id="last_name" type="text" class="validate" name="l_name" required="">
            <label for="last_name">Last Name</label>
          </div>
        </div>
        <div class="input-field">
          <input id="email" type="email" class="validate" name="email" required="">
          <label for="email" data-error="Please provide the proper email">Email</label>
        </div>
        <div class="row">
        <div class="input-field col s12 m6">
          <select name="gender" id="gender" required="">
            <option value="" disabled selected>Select your gender</option>
            <option value="male">Male</option>
            <option value="female">Female</option>
          </select>
          <label id="gender-label">Gender</label>
        </div>
        <div class="input-field col s12 m6">
          <input id="dob" type="text" name="dob" class="datepicker" class="validate" required=""><!-- name = "dob_submit" -->
          <label id="label-dob" for="dob">Date of Birth</label>
        </div>
        </div>
        <div class="row">
        <div class="input-field col s12 m6">
          <select name="role" id="role" required="">
            <option value="" disabled selected>Select the role</option>
            <option value="admin">Admin</option>
            <option value="user">User</option>
          </select>
          <label id="role-label">Role</label>
        </div>
        <div class="input-field col s12 m6">
          <input id="username" type="text" class="validate" name="username" required="">
          <label for="username">Username</label>
        </div>
        </div>
        <div class="input-field">
          <input id="password" type="password" class="validate" name="password" required="">
          <label for="password">Password</label>
        </div>
        <div class="input-field">
          <input id="confirm_password" type="password" class="validate" name="confirm_password" required="">
          <label for="confirm_password">Confirm password</label>
        </div>
      </div>
      <div class="col s12 m4 pull-m8">
        <div class="form-pad center-align circle" style="cursor:pointer;">
          <img id="preview" class="responsive-img z-depth-1 circle hoimage" src="assets/images/person.png" style="width:200px;height:200px;background:#eee;">
        </div>
      </div>
      <div class="btn no-float hide">
        <input type="file" id="profile_pic" name="profile_pic" required="">
      </div>
    </div>
    <div class="row">
      <div class="col s12 m8 push-m4 buttons">
        <button class="waves-effect waves-light btn blue right" type="submit" name="add_user">Add User</button>
      </div>
    </div>
  </form>
</div>
<?php include_once('includes/footer.php');
 else: ?>
<!DOCTYPE html>
<html>
  <head>
    <title>Login</title>
  </head>
  <body onload=window.location='login.php'>
  </body>
</html>
<?php endif; ?>