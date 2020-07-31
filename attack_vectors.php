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
$title = "Update Database";
include_once('includes/header.php');
  if(isset($_POST['add_attack'])){
  
    $tag = $_POST['tag'];
    $tag_category = $_POST['tag_category'];
    
         
    $conn = connect_pdo();
    $sql_check_tag = "SELECT `tag` FROM `attack_det` WHERE `tag` = :tag";
    $check_tag = $conn->prepare($sql_check_tag);
    $check_tag->bindValue(':tag', $tag);
    $check_tag->execute();


    if($check_tag->rowCount() > 0){
      $message = '<div id="message" class="card col s12 m10 l8 offset-m1 offset-l2 red white-text"><div class="card-content center-align">Sorry Attack Tag already exist!</div></div>';
    }else{
      
      $sql = "INSERT INTO `attack_det`(`tag`, `tag_category`) VALUES (:tag, :tag_category)";
      $insert = $conn->prepare($sql);
      $insert->bindValue(':tag', $tag);
      $insert->bindValue(':tag_category', $tag_category);
      if($insert->execute() == TRUE){
        $message = '<div id="message" class="card col s12 m10 l8 offset-m1 offset-l2 green white-text"><div class="card-content center-align">"'.htmlentities($tag, ENT_HTML5  , 'UTF-8').'" has been inserted under "'.$tag_category.'"</div></div>';
          $_POST = array();
      }else{
        $message = '<div id="message" class="card col s12 m10 l8 offset-m1 offset-l2 red white-text"><div class="card-content center-align">Please make a Valid Entry</div></div>';
      }
    
    }
  

}
?>

<div class="row">
<?php if(!empty($message)){
          echo $message;}
?>
 <div class="col s12 m6">
      <div class="card darken-1">
                  
         <div class="card-content ">
		 <form id="my_form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" enctype="multipart/form-data">
          <span class="card-title">ADD DATA</span>
          <div class="input-field ">
            <input id="tag" type="text" class="validate" name="tag" required="">
            <label for="tag">Tag</label>
			 
          <select name="tag_category" id="tag_category" required="">
            <option value="" disabled selected>Select Attack Type</option>
            <option value="mansqli">Manual Injection</option>
            <option value="autosqli">Auto Injection</option>
            <option value="xss">xss</option>
            <option value="backdoor">Backdoors</option>
          </select><div class="row">
          <div class="col s12 m8 push-m4 buttons">
        <button class="waves-effect waves-light btn blue right" type="submit" name="add_attack">Add Attacks</button>
      </div>
        </div>
			</form>
          </div>
        </div> 
		</div>         
 </div>	 
 <div class="col s12 m6">
 <ul class="collapsible">
    <li>
      <div class="collapsible-header"><i class="material-icons">adb</i>MANUAL INJECTION</div>
      <div class="collapsible-body"><span>Make sure that you are adding only manual injection queries here. Incorrect entries will result in false positive outputs .
	  <a href="https://www.exploit-db.com/papers/13045">Read more about manual SQLi</a> </span></div>
    </li>
    <li>
      <div class="collapsible-header"><i class="material-icons">av_timer</i>AUTO INJECTION</div>
      <div class="collapsible-body"><span>This category is reserved for entering automated SQL Injection queries</span></div>
    </li>
    <li>
      <div class="collapsible-header active"><i class="material-icons">report</i>XSS</div>
      <div class="collapsible-body"><span>Add XSS queries in this category</span></div>
    </li>
	<li>
      <div class="collapsible-header"><i class="material-icons">link</i>BACKDOORS</div>
      <div class="collapsible-body"><span>Add possible backdoor script's names.</span></div>
    </li>
  </ul>  
  </div>
</div>
 
          


<div class="card-panel ">    
<div class="row">
<h4 class="light grey-text text-darken-2">Attack Vectors</h4>
<hr>
    <div class="col s12">
      <ul class="tabs">
        <li class="tab col s3"><a href="#test1">SQL MANUAL QUERIES</a></li>
        <li class="tab col s3"><a class="active" href="#test2">SQL AUTOMATED QUERIES</a></li>
        <li class="tab col s3"><a href="#test3">XSS QUERIES</a></li>
        <li class="tab col s3"><a href="#test4">BACKDOOR  NAMES</a></li>
      </ul>
    </div>
    <div id="test1" class="col s12">
	  <table class="responsive-table">
           <thead>
            <tr>
              <th>#</th>
              <th>Queries</th>
              <th>Action</th>
            </tr>
           </thead>
		
        <tbody> <?php
    $attack_infos = $conn->prepare('SELECT * FROM attack_det WHERE tag_category="mansqli"');
    $attack_infos->execute();
    $attack_det = $attack_infos->fetch(PDO::FETCH_ASSOC);
    $attack = NULL;
    while($attack_info = $attack_infos->fetch(PDO::FETCH_ASSOC)) {
    ?>
          <tr>
            <td><?php echo $attack_info['id']; ?></td>
            <td><?php echo htmlentities($attack_info['tag'], ENT_HTML5  , 'UTF-8'); ?></td>
            <td><a class="btn-floating waves-effect waves-light-grey white btn-flat" href="?edit="><i class="material-icons grey-text text-darken-3">edit</i></a>&nbsp;<a onclick="$('#modal').modal('open');" class="btn-floating waves-effect waves-light-grey white btn-flat"><i class="material-icons red-text">delete</i></a></td>
          </tr><?php } ?>
         
        </tbody>
      </table>	
	</div>
    <div id="test2" class="col s12">
	  <table class="responsive-table bordered">
           <thead>
            <tr>
              <th>#</th>
              <th>Queries</th>
              <th>Action</th>
            </tr>
           </thead>
		
        <tbody><?php
    $attack_infos = $conn->prepare('SELECT * FROM attack_det WHERE tag_category="autosqli"');
    $attack_infos->execute();
    $attack_det = $attack_infos->fetch(PDO::FETCH_ASSOC);
    $attack = NULL;
    while($attack_info = $attack_infos->fetch(PDO::FETCH_ASSOC)) {
    ?>
          <tr>
            <td><?php echo $attack_info['id']; ?></td>
            <td><?php echo htmlentities($attack_info['tag'], ENT_HTML5  , 'UTF-8'); ?></td>
            <td><a class="btn-floating waves-effect waves-light-grey white btn-flat" href="?edit="><i class="material-icons grey-text text-darken-3">edit</i></a>&nbsp;<a onclick="$('#modal').modal('open');" class="btn-floating waves-effect waves-light-grey white btn-flat"><i class="material-icons red-text">delete</i></a></td>
          </tr><?php } ?>
        </tbody>
      </table>		</div>
    <div id="test3" class="col s12">
	  <table class="responsive-table">
           <thead>
            <tr>
              <th>#</th>
              <th>Queries</th>
              <th>Action</th>
            </tr>
           </thead>
		
        <tbody><?php
    $attack_infos = $conn->prepare('SELECT * FROM attack_det WHERE tag_category="xss"');
    $attack_infos->execute();
    $attack_det = $attack_infos->fetch(PDO::FETCH_ASSOC);
    $attack = NULL;
    while($attack_info = $attack_infos->fetch(PDO::FETCH_ASSOC)) {
    ?>
          <tr>
            <td><?php echo $attack_info['id']; ?></td>
            <td><?php echo htmlentities($attack_info['tag'], ENT_HTML5  , 'UTF-8'); ?></td>
            <td><a class="btn-floating waves-effect waves-light-grey white btn-flat" href="?edit="><i class="material-icons grey-text text-darken-3">edit</i></a>&nbsp;<a onclick="$('#modal').modal('open');" class="btn-floating waves-effect waves-light-grey white btn-flat"><i class="material-icons red-text">delete</i></a></td>
          </tr><?php } ?>
        </tbody>
      </table>		</div>
    <div id="test4" class="col s12">
	  <table class="responsive-table">
           <thead>
            <tr>
              <th>#</th>
              <th>Queries</th>
              <th>Action</th>
            </tr>
           </thead>
		
        <tbody><?php
    $attack_infos = $conn->prepare('SELECT * FROM attack_det WHERE tag_category="backdoor"');
    $attack_infos->execute();
    $attack_det = $attack_infos->fetch(PDO::FETCH_ASSOC);
    $attack = NULL;
    while($attack_info = $attack_infos->fetch(PDO::FETCH_ASSOC)) {
    ?>
          <tr>
            <td><?php echo $attack_info['id']; ?></td>
            <td><?php echo htmlentities($attack_info['tag'], ENT_HTML5  , 'UTF-8'); ?></td>
            <td><a class="btn-floating waves-effect waves-light-grey white btn-flat" href="?edit="><i class="material-icons grey-text text-darken-3">edit</i></a>&nbsp;<a onclick="$('#modal').modal('open');" class="btn-floating waves-effect waves-light-grey white btn-flat"><i class="material-icons red-text">delete</i></a></td>
          </tr><?php } ?>
        </tbody>
      </table>		</div>
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