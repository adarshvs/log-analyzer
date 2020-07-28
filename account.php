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
$title = "Account";
include_once('includes/header.php');
if(isset($_POST['profile_update'])){
  $first_name['db'] = $user['f_name'];
  $last_name['db'] = $user['l_name'];
  $email['db'] = $user['email'];
  $first_name['post'] = $_POST['f_name'];
  $last_name['post'] = $_POST['l_name'];
  $email['post'] = $_POST['email'];
  if(!empty($_FILES['profile_pic']['name'])){
    $picture['name'] = $_FILES['profile_pic']['name'];
    $picture['file'] = $_FILES['profile_pic']['tmp_name'];
  }
  $picture['db'] = $user['profile_pic'];
  if(($first_name['db'] != $first_name['post']) || ($last_name['db'] != $last_name['post']) || ($email['db'] != $email['post']) || !empty($_FILES['profile_pic']['name'])){
    $username = $user['username'];
    if(isset($picture['name'])){
      $picture_name = $picture['name'];
    }else{
      $picture_name = $picture['db'];
    }
    $new_profile = "UPDATE `users` SET f_name=:f_name, l_name=:l_name, email=:email, profile_pic=:profile_pic WHERE username=:username";
    $profile_change = $conn->prepare($new_profile);
    $profile_change->bindValue(':f_name', $first_name['post']);
    $profile_change->bindValue(':l_name',$last_name['post']);
    $profile_change->bindValue(':email', $email['post']);
    $profile_change->bindValue(':profile_pic', $picture_name);
    $profile_change->bindValue(':username', $username);
    if($profile_change->execute() == TRUE){
    if(isset($picture['name'])){
      if(move_uploaded_file( $picture['file'], "users/".$username."/" . $picture['name'])){
        @unlink("users/$username/" . $picture['db']);
      }
    }
      echo '<script>window.location.replace("account.php?status=1");</script>';
    }else{
      echo '<script>window.location.replace("account.php?status=2");</script>';
    }
  }else{
    echo '<script>window.location.replace("account.php?status=3");</script>';
  }
}
if(isset($_POST['change_password'])){
  $current = $_POST['current'];
  $new = $_POST['new'];
  $confirm = $_POST['confirm'];
  if($new == $confirm){
    $username = $user['username'];
    if(password_verify($current, $user['password'])){
        $new = password_hash($new, PASSWORD_BCRYPT);
      $new_pass = "UPDATE `users` SET password=:new WHERE username=:username";
      $pass_change = $conn->prepare($new_pass);
      $pass_change->bindValue(':new', $new);
      $pass_change->bindValue(':username', $username);
      if($pass_change->execute() == TRUE){
        $password_message = '<div id="message" class="card col s12 m10 l8 offset-m1 offset-l2 green white-text"><div class="card-content center-align">Password changed</div></div>';
      }else{
        $password_message = '<div id="message" class="card col s12 m10 l8 offset-m1 offset-l2 red white-text"><div class="card-content center-align">Error occured while changing the password. Please report to the developer</div></div>';
      }
    }else{
      $password_message = '<div id="message" class="card col s12 m10 l8 offset-m1 offset-l2 red white-text"><div class="card-content center-align">Incorrect current password</div></div>';
    }
  }else{
    $password_message = '<div id="message" class="card col s12 m10 l8 offset-m1 offset-l2 red white-text"><div class="card-content center-align">Password do not match</div></div>';
  }
}
?>
  <div class="row">
    <div class="col s12">
      <ul class="tabs tabs-fixed-width z-depth-1">
        <li class="tab col s3"><a href="#profile">Profile</a></li>
        <li class="tab col s3"><a href="#password">Password</a></li>
      </ul>
    </div>
    <div id="profile" class="col s12">
      <div class="card">
        <div class="card-content">
          <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" enctype="multipart/form-data">
            <div class="row">
              <?php
                if(isset($_GET['status'])){
                  if($_GET['status'] == 1){
                    $profile_message = '<div id="message" class="card col s12 m10 l8 offset-m1 offset-l2 green white-text"><div class="card-content center-align">Profile updated</div></div>';
                  }elseif($_GET['status'] == 2){
                    $profile_message = '<div id="message" class="card col s12 m10 l8 offset-m1 offset-l2 red white-text"><div class="card-content center-align">Error occured while updating the profile. Please report to the developer</div></div>';
                  }elseif($_GET['status'] == 3){
                    $profile_message = '<div id="message" class="card col s12 m10 l8 offset-m1 offset-l2 red white-text"><div class="card-content center-align">No changes in currect profile</div></div>';
                  }
                  echo $profile_message;
                }
              ?>
              <div class="col s12 m8 push-m4">
                <div class="input-field">
                  <input id="first_name" type="text" class="validate" name="f_name" value="<?php echo $user['f_name']; ?>">
                  <label for="first_name">First Name</label>
                </div>
                <div class="input-field">
                  <input id="last_name" type="text" class="validate" name="l_name" value="<?php echo $user['l_name']; ?>">
                  <label for="last_name">Last Name</label>
                </div>
                <div class="input-field">
                  <input id="email" type="email" class="validate" name="email" value="<?php echo $user['email']; ?>">
                  <label for="email" data-error="Please provide the email.">Email</label>
                </div>
                <div class="input-field">
                  <input id="username" type="text" class="validate" value="<?php echo $user['username']; ?>" disabled>
                  <label for="username">Username</label>
                </div>
                <div class="input-field">
                  <input id="registered" type="text" class="validate" value="<?php $dob = date("d F, Y", strtotime($user['dob'])); echo $dob; ?>" disabled>
                  <label for="registered">Date of Birth</label>
                </div>
                <div class="input-field">
                  <input id="registered" type="text" class="validate" value="<?php $registered = date("d F, Y h:i A", strtotime($user['register_time'])); echo $registered; ?>" disabled>
                  <label for="registered">Registered on</label>
                </div>
              </div>
              <div class="col s12 m4 pull-m8">
                <div class="form-pad center-align circle" style="cursor:pointer;">
                  <img id="preview" class="responsive-img z-depth-1 circle hoimage" src="<?php echo "users/".$user['username']."/".$user['profile_pic']; ?>" style="width: 200px;height: 200px;">
                </div>
              </div>
              <div class="btn no-float primary-color hide">
                <input type="file" id="profile_pic" name="profile_pic">
              </div>
            </div>
            <div class="row">
              <div class="col s12 m8 push-m4 buttons">
                <button class="waves-effect waves-light btn blue right" type="submit" name="profile_update"><i class="material-icons right">done</i>Save changes</button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
    <div id="password" class="col s12">
      <div class="card">
        <div class="card-content">
          <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
            <?php
              if(!empty($password_message)){
                echo $password_message;
                echo '<script>window.location.href = "#password";</script>';
              }
            ?>
              <div class="row">
                <div class="col s12">
                  <div class="input-field">
                    <input id="current" type="password" name="current" class="validate">
                    <label for="current">Current Password</label>
                  </div>
                  <div class="input-field">
                    <input id="new" type="password" name="new" class="validate">
                    <label for="new">New Password</label>
                  </div>
                  <div class="input-field">
                    <input id="confirm" type="password" name="confirm" class="validate">
                    <label for="confirm">Confirm New Password</label>
                  </div>
                    <div class="buttons"><br>
                      <button class="waves-effect waves-light btn blue right" type="submit" name="change_password"><i class="material-icons right">done</i>Save changes</button>
                  </div>
                </div>
              </div>
          </form>
        </div>
      </div>
    </div>
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