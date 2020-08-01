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
if(!empty($user)&&$user['role'] == 'admin'):
$title = "Insight";
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
    <div class="col s12">
      <ul class="tabs tabs-fixed-width z-depth-1">
        <li class="tab col s3"><a href="#user_log" class="active">USER LOGS </a></li>
        <li class="tab col s3"><a href="#login_attempt" class="">Failed Login Attempts</a></li>
      <li class="indicator" style="right: 468px; left: 0px;"></li></ul>
    </div>
    <div id="user_log" class="col s12 active" style="">
      <div class="card">
        <div class="card-content">
          <table class="responsive-table bordered">
			<thead>
			  <tr>
				<th>#</th>
				<th>IP</th>
				<th>Time</th>
				<th>Date</th>
				<th>Referrer URL</th>
				<th>User Agent</th>
			  </tr>
			</thead>
            <tbody>
			<?php
    $user_log_infos = $conn->prepare('SELECT * FROM user_activity_logs order by date_time desc ');
    $user_log_infos->execute();
    $user_log_data = $user_log_infos->fetchAll();
    foreach($user_log_data as $user_log_infos) {
		$pieces = explode(" ", $user_log_infos['date_time']);
		$date= $pieces[0];
		$time = $pieces[1];
    ?>
              <tr>
			    <td><?php echo $user_log_infos['id']; ?></td>
				<td><?php echo $user_log_infos['IP']; ?></td>
				<td><?php echo $time; ?></td>
				<td><?php echo $date; ?></td>
				<td><?php echo $user_log_infos['url']; ?></td>
                <td><a class="tooltipped" data-position="top" data-tooltip="<?php echo 'Browser : '.$user_log_infos['Browser'];  echo ' os : '.$user_log_infos['Platform']; ?>"><?php echo $user_log_infos['user_agent']; ?></a>
                </td>
              </tr><?php } ?>
	       </tbody>
		  </table>
        </div>
      </div>
    </div>
    <div id="login_attempt" class="col s12" style="display: none;">
      <div class="card">
        <div class="card-content">
         <table class="responsive-table bordered">
			<thead>
			  <tr>
				<th>#</th>
				<th>IP</th>
				<th>Time</th>
				<th>Date</th>        
			  </tr>
			</thead>
            <tbody><?php
    $user_log_infos = $conn->prepare('SELECT * FROM login_attempts order by fail_time desc ');
    $user_log_infos->execute();
    $user_log_data = $user_log_infos->fetchAll();
    foreach($user_log_data as $user_log_infos) {
		$pieces = explode(" ", $user_log_infos['fail_time']);
		$date= $pieces[0];
		$time = $pieces[1];
    ?>
              <tr>
			    <td><?php echo $user_log_infos['id']; ?></td>
				<td><?php echo $user_log_infos['public_ip']; ?></td>
				<td><?php echo $time; ?></td>
				<td><?php echo $date; ?></td>
              </tr><?php } ?>
	       </tbody>
		  </table> 
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