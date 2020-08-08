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
$title = "Detailed View";
if(!isset($_GET['show'])) {
        header('Location: /');
        exit();}
if(!empty($_GET['show'] && $_GET['data'])) {
  //Show IP Details
  if(isset($_GET['info'])) {
    $ip = decrypt($_GET['info']);
    $url_get_options = array("ssl" => array("verify_peer" => false, "verify_peer_name" => false));
    $info = json_decode(file_get_contents("http://ip-api.com/json/".$ip, false, stream_context_create($url_get_options)));
    if($info->status == "success") {   
      include('ip_modal.php');
    }
  }
  if($_GET['show'] == 'access_log'){
    if(!isset($_GET['page'])) {
      header('Location: ?show='.$_GET['show'].'&data='.$_GET['data'].'&page=1');
      exit();
    }
    $p_data = new Page('log_access', $_GET['page'], $_GET['show'], $_GET['data'], 20);
    $data = $p_data->pageData();
    $pagination = $p_data->pagination();
    include_once('includes/header.php');
    include('access_log_table.php');
 
  }
}if($_GET['show'] == 'sys_log'){

			if(!isset($_GET['page'])) {
				header('Location: ?show='.$_GET['show'].'&data='.$_GET['data'].'&page=1');
			}
			$p_data = new Page('log_sys', $_GET['page'], $_GET['show'], $_GET['data'], 20);
			$data = $p_data->pageData();
			$pagination = $p_data->pagination();
            include_once('includes/header.php');
?><div class="row">
  <div class="col s12">
    <?php echo $pagination; ?>
    <div class="card-panel" style="
    overflow-x: scroll;
    overflow-y: scroll;
    max-height: 500px;
">
   <table class="responsive-table bordered">      
                  <thead>
                    <tr>
                      <th>#</th>
                      <th>Timestamp</th>
                      <th>Process Name</th>
                      <th>Process ID</th>
                      <th>Raw Data</th>
                    </tr>
                  <tbody><?php foreach($data as $log_data) { ?>
                      <tr>
                      <td><?php echo $log_data['id']; ?></td>                   
                      <td><?php echo $log_data['date_time']; ?></td>
                      <td><?php echo $log_data['process_name']; ?></td>
                      <td><?php echo $log_data['process_id']; ?></td>
                      <td><?php echo $log_data['raw_data']; ?></td>
                    </tr><?php } ?>
                    </tbody>
                  </thead>
                </table>
 </div>
</div><?php echo $pagination;?>
</div>

<?php		}  
 
 include_once('includes/footer.php'); 
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