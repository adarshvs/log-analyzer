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
if(isset($_GET['edit'])){
  $title = "Edit Users";
}else{  
  $title = "Users";
}
include_once('includes/header.php');
if(isset($_GET['delete'])){
  $delete_user = $_GET['delete'];
  $delete = $conn->prepare("DELETE FROM users WHERE username=:username");
  $delete->bindValue(':username', $delete_user);
  $delete->execute();
  deleteDir("users/". $delete_user);
  $rem_id_col = "ALTER TABLE users DROP COLUMN id";
  $add_new_id_col = "ALTER TABLE users ADD id int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT FIRST";
  $conn->exec($rem_id_col);
  $conn->exec($add_new_id_col);
  echo '<script type="text/javascript">window.location.replace("users.php");</script>';
  exit();
 }elseif(isset($_GET['edit'])){
  $edit_id = cleaner($_GET['edit']);
  $edit_user = $conn->prepare('SELECT * FROM users WHERE id = :id');
  $edit_user->bindParam(':id', $edit_id);
  $edit_user->execute();
  $edit = $edit_user->fetch(PDO::FETCH_ASSOC);
  $edit_date = date("d F, Y", strtotime($edit['dob']));
    if(isset($_POST['edit_user'])){
      $first_name['db'] = $edit['f_name'];
      $last_name['db'] = $edit['l_name'];
      $email['db'] = $edit['email'];
      $dob['db'] = $edit['dob'];
      $role['db'] = $edit['role'];
      $username = $edit['username'];
      $first_name['post'] = $_POST['f_name'];
      $last_name['post'] = $_POST['l_name'];
      $email['post'] = $_POST['email'];
      $dob['post'] = $_POST['dob_submit'];
      $role['post'] = $_POST['role'];
      if(!empty($_FILES['profile_pic']['name'])){
        $picture['name'] = $_FILES['profile_pic']['name'];
        $picture['file'] = $_FILES['profile_pic']['tmp_name'];
      }
      $picture['db'] = $edit['profile_pic'];
      if(($first_name['db'] != $first_name['post']) || ($last_name['db'] != $last_name['post']) || ($email['db'] != $email['post']) || !empty($_FILES['profile_pic']['name'])){
        if(isset($picture['name'])){
          $picture_name = $picture['name'];
        }else{
          $picture_name = $picture['db'];
        }
        $new_profile = "UPDATE `users` SET f_name=:f_name, l_name=:l_name, email=:email, dob=:dob, role=:role, profile_pic=:profile_pic WHERE id=:id";
        $profile_edit = $conn->prepare($new_profile);
        $profile_edit->bindValue(':f_name', $first_name['post']);
        $profile_edit->bindValue(':l_name',$last_name['post']);
        $profile_edit->bindValue(':email', $email['post']);
        $profile_edit->bindValue(':dob', $dob['post']);
        $profile_edit->bindValue(':role', $role['post']);
        $profile_edit->bindValue(':profile_pic', $picture_name);
        $profile_edit->bindValue(':id', $edit_id);
        if($profile_edit->execute() == TRUE){
        if(isset($picture['name'])){
          if(move_uploaded_file( $picture['file'], "users/".$username."/".$picture['name'])){
            @unlink("users/".$username."/" . $picture['db']);
          }
        }
          echo '<script>window.location.replace("users.php?edit='.$edit_id.'&status=1");</script>';
        }else{
          echo '<script>window.location.replace("users.php?edit='.$edit_id.'&status=2");</script>';
        }
      }else{
        echo '<script>window.location.replace("users.php?edit='.$edit_id.'&status=3");</script>';
      }
    }
  ?>
<div class="card-panel">
  <form action="<?php echo htmlspecialchars(basename($_SERVER["REQUEST_URI"])); ?>" method="POST" enctype="multipart/form-data">
    <div class="row">
    <div class="section"></div>
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
        <div class="row">
          <input type="hidden" name="edit" value="<?php echo htmlspecialchars($_GET["edit"]); ?>">
          <div class="input-field col s12 m6">
            <input id="first_name" type="text" class="validate" value="<?php echo $edit['f_name']; ?>" name="f_name" required="">
            <label for="first_name">First Name</label>
          </div>
          <div class="input-field col s12 m6">
            <input id="last_name" type="text" class="validate" value="<?php echo $edit['l_name']; ?>" name="l_name" required="">
            <label for="last_name">Last Name</label>
          </div>
        </div>
        <div class="input-field">
          <input id="email" type="email" class="validate" value="<?php echo $edit['email']; ?>" name="email" required="">
          <label for="email" data-error="Please provide the proper email">Email</label>
        </div>
        <div class="row">
        <div class="input-field col s12 m6">
          <input id="dob" type="text" name="dob" class="datepicker" value="<?php echo $edit_date; ?>" class="validate" required=""><!-- name = "dob_submit" -->
          <label id="label-dob" for="dob">Date of Birth</label>
        </div>
        <div class="input-field col s12 m6">
          <select name="role" id="role" required="">
            <option value="" disabled>Select the role</option>
            <option value="admin" <?php if($edit['role'] == "admin"){ echo "selected"; } ?>>Admin</option>
            <option value="user" <?php if($edit['role'] == "user"){ echo "selected"; } ?>>User</option>
          </select>
          <label id="role-label">Role</label>
        </div>
        </div>
        <div class="row">
        <div class="input-field col s12 m6">
          <select disabled>
            <option value="" disabled>Select your gender</option>
            <option value="male" <?php if($edit['gender'] == "male"){ echo "selected"; } ?>>Male</option>
            <option value="female" <?php if($edit['gender'] == "female"){ echo "selected"; } ?>>Female</option>
          </select>
          <label>Gender</label>
        </div>
        <div class="input-field col s12 m6">
          <input id="username" type="text" class="validate" value="<?php echo $edit['username']; ?>" disabled required="">
          <label for="username">Username</label>
        </div>
        </div>
      </div>
      <div class="col s12 m4 pull-m8">
        <div class="form-pad center-align circle" style="cursor:pointer;">
          <img id="preview" class="responsive-img z-depth-1 circle hoimage" src="<?php if(!empty($edit['profile_pic'])){ echo "users/".$edit['username']."/".$edit['profile_pic']; }else{ echo "assets/images/person.png"; } ?>" style="width:200px;height:200px;background:#eee;">
        </div>
      </div>
      <div class="btn no-float hide">
        <input type="file" id="profile_pic" name="profile_pic">
      </div>
    </div>
    <div class="row">
      <div class="col s12 m8 push-m4 buttons">
        <button class="waves-effect waves-light btn blue right" type="submit" name="edit_user"><i class="material-icons right">done</i>Save changes</button>
      </div>
    </div>
  </form>
</div>
<?php }else{ ?>
<div class="card-panel">
  <table class="responsive-table">
    <thead>
      <tr>
        <th></th>
        <th>Name</th>
        <th>Username</th>
        <th>Email</th>
        <th>Role</th>
        <th>Registered On</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
    <?php
    $users_infos = $conn->prepare('SELECT * FROM `users` ORDER BY `f_name` ASC');
    $users_infos->execute();
    while($users_info = $users_infos->fetch(PDO::FETCH_ASSOC)) {
    ?>
      <tr>
        <td><img class="circle" src="<?php echo 'users/'.$users_info['username'].'/'.$users_info['profile_pic']; ?>" style="width: 30px;"></td>
        <td><?php echo $users_info['f_name'].' '.$users_info['l_name']; ?></td>
        <td><?php echo $users_info['username']; ?></td>
        <td><?php echo $users_info['email']; ?></td>
        <td><?php echo $users_info['role']; ?></td>
        <td><?php echo date("d F, Y h:i A", strtotime($users_info['register_time'])); ?></td>
        <?php if($users_info['id'] == $user['id']){ ?>
        <td><a class="btn-floating waves-effect waves-light-grey white btn-flat" href="account.php"><i class="material-icons grey-text text-darken-3">edit</i></a></td>
         <?php }else{ ?>
        <td><a class="btn-floating waves-effect waves-light-grey white btn-flat" href="?edit=<?php echo $users_info['id']; ?>"><i class="material-icons grey-text text-darken-3">edit</i></a>&nbsp;<a onclick="$('#modal<?php echo $users_info['id']; ?>').modal('open');" class="btn-floating waves-effect waves-light-grey white btn-flat"><i class="material-icons red-text">delete</i></a></td>
        <?php } ?>
      </tr>
		<div id="modal<?php echo $users_info['id']; ?>" class="modal">
			<div class="modal-content">
				<p>Do you really want to remove <b><?php echo $users_info['f_name'].' '.$users_info['l_name']; ?></b> permanently from this project?</p>
			</div>
			<div class="modal-footer">
				<a class="modal-action modal-close waves-effect btn-flat">Cancel</a>
				<a href="?delete=<?php echo $users_info['username']; ?>" class="modal-action waves-effect btn-flat red-text">YES</a>
			</div>
		</div>
      <?php } ?>
      <tr>
    </tbody>
  </table>
</div>
<?php } include_once('includes/footer.php');
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