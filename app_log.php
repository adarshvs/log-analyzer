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
      if(!isset($_GET['page'])) {
        header('Location: ?page=1');
        exit();}
        $obj = new PageUserActivityLogs('user_activity_logs',$_GET['page'],10);     
      $obj->pageData();
      $data_log = $obj->pageData();
      $pagination = $obj->pagination();
           
 ?>
<div class="row">
    <div class="col s12"><?php //echo $pagination; ?>
      <ul class="tabs tabs-fixed-width z-depth-1">
        <li class="tab col s3"><a href="#user_log" class="active">USER LOGS </a></li>
        <li class="tab col s3"><a href="#login_attempt" class="">Failed Login Attempts</a></li>
      <li class="indicator" style="right: 468px; left: 0px;"></li></ul>
    </div>
    <div id="user_log" class="col s12 active" style=""> <?php //echo $pagination; ?>
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
   
    foreach($data_log as $user_log_infos) {
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
      </div><?php echo $pagination; ?>
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