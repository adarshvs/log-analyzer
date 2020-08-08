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

if(isset($_GET['print'])) {
	$data = decrypt($_GET['data']); 
	$query = "SELECT * FROM `log_access` WHERE case_no=:data";
	$conn = connect_pdo();
	$records = $conn->prepare($query);
	$records->bindValue(':data', $data);
	if($records->execute()) {
		$access = array();
		if($records->rowCount() > 0) {
			while($row = $records->fetch(PDO::FETCH_ASSOC)) {
				$access[] = $row;
			}
		}
		header('Content-Type: text/csv; charset=utf-8');
		header('Content-Disposition: attachment; filename=access.csv');
		$output = fopen('php://output', 'w');
		fputcsv($output, array('id', 'case_no', 'public_ip', 'date_time', 'timezone', 'method', 'http_header', 'http_response', 'file_bytes', 'link_ref', 'useragent', 'browser', 'country','raw_data'));
		if(count($access) > 0) {
			foreach ($access as $row) {
				fputcsv($output, $row);
			}
		}
	}
}
if(isset($_GET['print_sys'])) {
	$data = decrypt($_GET['data']); 
	$query = "SELECT * FROM `log_sys` WHERE case_no=:data";
	$conn = connect_pdo();
	$records = $conn->prepare($query);
	$records->bindValue(':data', $data);
	if($records->execute()) {
		$sys = array();
		if($records->rowCount() > 0) {
			while($row = $records->fetch(PDO::FETCH_ASSOC)) {
				$sys[] = $row;
			}
		}
		header('Content-Type: text/csv; charset=utf-8');
		header('Content-Disposition: attachment; filename=sys.csv');
		$output = fopen('php://output', 'w');
		fputcsv($output, array('id', 'case_no', 'public_ip', 'date_time', 'protocol', 'notification', 'message'));
		if(count($sys) > 0) {
			foreach ($sys as $row) {
				fputcsv($output, $row);
			}
		}
	}
}
$title = "Analyze";
include_once('includes/header.php');

if(isset($_POST['add_case'])) {
  $username = $user['username'];
  $case_no = $_POST['case_no'];
  $evd_name = $_POST['evd_name'];
  $ref_no = $_POST['ref_no'];
  $web_url = $_POST['web_url'];
  $att_date_submit = $_POST['att_date_submit'];
  $registered_date = date_default_timezone_set("Indian/Mahe");
  $registered_date = date("Y-m-d H:i:s");
  $case_sql = "INSERT INTO `case_details`(`username`, `case_no`, `ref_no`,`site_url`, `evidence_file`, `date`, `registered_date`) VALUES (:username, :case_no, :ref_no, :site_url, :evidence_file, :attack_date, :registered_date)";
  $case_detail = $conn->prepare($case_sql);
  $case_detail->bindValue(':username', $username);
  $case_detail->bindValue(':case_no', $case_no);
  $case_detail->bindValue(':ref_no', $ref_no);
  $case_detail->bindValue(':site_url', $web_url);
  $case_detail->bindValue(':evidence_file', $evd_name);
  $case_detail->bindValue(':attack_date', $att_date_submit);
  $case_detail->bindValue(':registered_date', $registered_date);
  if($case_detail->execute() == TRUE){
	$url_data = encrypt($case_no);
    echo '<script>window.location.replace("?log=add&data='.$url_data.'");</script>';
  }else{
    echo '<script>window.location.replace("'.$_SERVER["PHP_SELF"].'?error");</script>';
  }
}elseif(isset($_GET['log'])) {
  if($_GET['log'] == "edit" || $_GET['log'] == "add") {
  	if(isset($_POST['upload_log'])) {
  		if(isset($_POST['log_type'])) {
  			if($_POST['log_type'] == "access_log") {
				if(file_exists($_FILES["log"]["tmp_name"])){
					$log_file = $_FILES["log"]["tmp_name"];
					$case_no = decrypt($_GET['data']);
					if(!file_exists("logs/$case_no")) {
						mkdir("logs/$case_no", 0777, true);
					}
					$now = date('Y-m-d_h-i-s');
					move_uploaded_file($_FILES["log"]["tmp_name"], "logs/".$case_no."/".$now."_access.log");
				}else{
					die("File does not exist");
				}
				$log = new Logread($case_no);
				if($log->access("logs/".$case_no."/".$now."_access.log") == TRUE) {
				}
			}elseif($_POST['log_type'] == "sys_log"){
				if(file_exists($_FILES["log"]["tmp_name"])){
					$log_file = $_FILES["log"]["tmp_name"];
					$case_no = decrypt($_GET['data']);
					if(!file_exists("logs/$case_no")) {
						mkdir("logs/$case_no", 0777, true);
					}
					$now = date('Y-m-d_h-i-s');
					move_uploaded_file($_FILES["log"]["tmp_name"], "logs/".$case_no."/".$now."_sys.log");
				}else{
					die("File does not exist");
				}
				$log = new Logread($case_no);
				if($log->sys("logs/".$case_no."/".$now."_sys.log") == TRUE) {
					echo '<script>window.location.replace("?show='.$_GET['log'].'&data='.$_GET['data'].'");</script>';
				}
			}
		}
	}

?>
<div class="card-panel">
	<div class="row">
		<?php
		if($_GET['log'] == "add"){
		echo '<div id="message" class="card col s12 m6 l4 offset-m3 offset-l4 green white-text"><div class="card-content center-align">Case added. Add your Log</div></div>';
		}else{
		echo '<div id="message" class="card col s12 m6 l4 offset-m3 offset-l4 green white-text"><div class="card-content center-align">Add Log for the existing case</div></div>';
		}
		?>
		<div class="col s12">
			<form method="POST" action="<?php echo htmlspecialchars(basename($_SERVER["REQUEST_URI"])); ?>" enctype="multipart/form-data">
				<div class="row">
						<div class="input-field col s12">
						<select id="log_type" name="log_type" required="">
							<option value="" disabled selected>Choose your Log type</option>
							<option value="access_log">Access Log</option>
							<option value="sys_log">Sys Log</option>
						</select>
						<label id="log_label">Log Type</label>
					</div>
					<div class="input-field col s12">
						<div class="file-field input-field">
							<div class="blue waves-effect waves-light btn">
							<span>Add Log</span>
								<input type="file" name="log" id="log"  accept=".txt,.log" required>
							</div>
							<div class="file-path-wrapper">
								<input class="file-path validate" type="text">
							</div>
						</div>
					</div>
					<div class="col s12 m8 push-m4 buttons">
						<button class="waves-effect waves-light btn blue right" type="submit" name="upload_log">Upload Log</button>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>
<?php
	}else{
		echo '<script>window.location.replace("'.$_SERVER["PHP_SELF"].'");</script>';
	}
}elseif(isset($_GET['show'])) {

			if(isset($_GET['info'])) {
				$ip = decrypt($_GET['info']);
				$url_get_options = array("ssl" => array("verify_peer" => false, "verify_peer_name" => false));
				$info = json_decode(file_get_contents("http://ip-api.com/json/".$ip, false, stream_context_create($url_get_options)));
				if($info->status == "success") {
			 ?>
  <div id="ip_info" class="modal">
      <div class="modal-content">
      	<table>
      		<tr>
      			<td><b>IP</b></td><td><?php echo $info->query; ?></td>
      		</tr>
      		<tr>
      			<td><b>Country</b></td><td><?php echo $info->country; ?></td>
      		</tr>
      		<tr>
      			<td><b>Country code</b></td><td><?php echo $info->countryCode; ?></td>
      		</tr>
      		<tr>
      			<td><b>Region</b></td><td><?php echo $info->regionName; ?></td>
      		</tr>
      		<tr>
      			<td><b>Region code</b></td><td><?php echo $info->region; ?></td>
      		</tr>
      		<tr>
      			<td><b>Zip Code</b></td><td><?php echo $info->zip; ?></td>
      		</tr>
      		<tr>
      			<td><b>Latitude</b></td><td><?php echo $info->lat; ?></td>
      		</tr>
      		<tr>
      			<td><b>Longitude</b></td><td><?php echo $info->lon; ?></td>
      		</tr>
      		<tr>
      			<td><b>Timezone</b></td><td><?php echo $info->timezone; ?></td>
      		</tr>
      		<tr>
      			<td><b>ISP</b></td><td><?php echo $info->isp; ?></td>
      		</tr>
      		<tr>
      			<td><b>Organization</b></td><td><?php echo $info->org; ?></td>
      		</tr>
      		<tr>
      			<td><b>AS number/name</b></td><td><?php echo $info->as; ?></td>
      		</tr>
      	</table>
      </div>
      <div class="modal-footer">
        <a href="#" class="modal-action modal-close waves-effect btn-flat red-text">Okay</a>
      </div>
  </div>
		<?php	} }
	if(!empty($_GET['show'] && $_GET['data'])) {
		
		if($_GET['show'] == 'access_log'){

			include('analyze_show_log.php'); 

		}elseif($_GET['show'] == 'sys_log'){

			if(!isset($_GET['page'])) {
				header('Location: ?show='.$_GET['show'].'&data='.$_GET['data'].'&page=1');
			}
			$p_data = new Page('log_sys', $_GET['page'], $_GET['show'], $_GET['data'], 20);
			$data = $p_data->pageData();
			$pagination = $p_data->pagination();

		
		?>

				<div class="row">
					<div class="col s12 m12 l10 offset-l1">
					<a href="?show=<?php echo $_GET['show']; ?>&data=<?php echo $_GET['data']; ?>&page=<?php echo $_GET['page']; ?>&print_sys" class="right btn-floating waves-effect waves-light white z-depth-1">
						<i class="material-icons grey-text text-darken-3">&#xE8AD;</i></a>
					       <h4 class="light grey-text text-darken-2">Case <?php echo decrypt($_GET['data']); ?></h4><hr>
					         <?php
					         $bruteforce = scan_log("log_sys", decrypt($_GET['data']), "BRUTE_FORCE"); 
					         $bruteforce = count($bruteforce); 
					         if($bruteforce == 0) {
							   echo '<div class="chip green white-text">No Brute Force Attack</div>';
					                               } else {
							                echo '<div class="chip orange white-text">'.$bruteforce.' Brute Force Attempts detected</div>';
				                                        	}

					        echo $pagination; ?>
			        </div>
			</div>
		

				<div class="row">
					<div class="col s12 m12 l10 offset-l1">
						<div class="card">
							<div class="card-content">
							<table class="responsive-table bordered">
    
								<thead>
							    <tr>
							      <th>#</th>
							  		<th>IP</th>
							  		<th>Timestamp</th>
							  		<th>Protocol</th>
							  		<th>Notification</th>
							  		<th>Message</th>
									</tr>
							  <tbody><?php foreach($data as $log_data) { ?>
							      <tr>
							      <td><?php echo $log_data['id']; ?></td>
							      <td><?php echo $log_data['public_ip']; ?><a class="btn-floating btn-flat white waves-effect waves-light" href="?show=<?php echo $_GET['show']; ?>&data=<?php echo $_GET['data']; ?>&page=<?php echo $_GET['page']; ?>&info=<?php echo encrypt($log_data['public_ip']); ?>"><i class="material-icons grey-text text-darken-3">&#xE8B6;</i></a></td>
							      <td><?php echo date("d M Y h:i A", strtotime($log_data['date_time'])); ?></td>
							      <td><?php echo $log_data['protocol']; ?></td>
							      <td><?php echo $log_data['notification']; ?></td>
							      <td><?php echo $log_data['message']; ?></td>
							    </tr><?php } ?>
							    </tbody>
								</thead>
							   </table>
						</div>
						</div>
					</div>
				</div>
		<?php } 
		echo $pagination;
		
	}
}else{
?>
<div class="card-panel">
	<form method="POST" action="">
		<div class="row">
			<div class="input-field col s6">
				<input id="case_no" type="number" name="case_no" class="validate" required>
				<label for="case_no">Case No.</label>
			</div>
			<div class="input-field col s6">
				<input id="ref_no" type="number" name="ref_no" class="validate" required>
				<label for="ref_no">Reference No.</label>
			</div>
			<div class="input-field col s12">
				<input id="web_url" type="url" name="web_url" value="http://" class="validate" required>
				<label for="web_url">Website URL</label>
			</div>
			<div class="input-field col s6">
				<input id="evd_name" type="text" name="evd_name" class="validate" required>
				<label for="evd_name">Evidence File</label>
			</div>
			<div class="input-field col s6">
				<input id="att_date" type="text" name="att_date" class="datepicker" class="validate" required=""><!-- name = "att_date_submit" -->
				<label id="label-att_date" for="att_date">Date</label>
			</div>
		</div>
		<div class="row">
			<div class="col s12 m8 push-m4 buttons">
				<button class="waves-effect waves-light btn blue right" type="submit" name="add_case">Add Case</button>
			</div>
		</div>
	</form>
</div>
<?php }
 include_once('includes/footer.php'); ?>
 <?php else: ?>
<!DOCTYPE html>
<html>
	<head>
	<title>Login</title>
	</head>
	<body onload=window.location='login.php'>
	</body>
</html>
<?php endif; ?>