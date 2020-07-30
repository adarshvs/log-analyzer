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
		fputcsv($output, array('id', 'case_no', 'public_ip', 'date_time', 'timezone', 'method', 'http_header', 'http_response', 'file_bytes', 'link_ref', 'useragent', 'browser', 'country'));
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

			if(!isset($_GET['page'])) {
				header('Location: ?show='.$_GET['show'].'&data='.$_GET['data'].'&page=1');
				exit();
			}

			$p_data = new Page('log_access', $_GET['page'], $_GET['show'], $_GET['data'], 20);
			$data = $p_data->pageData();
			$pagination = $p_data->pagination();
  ?>
				<div class="row">
					<div class="col s12 m12 l10 offset-l1">
					<a href="?show=<?php echo $_GET['show']; ?>&data=<?php echo $_GET['data']; ?>&page=<?php echo $_GET['page']; ?>&print" class="right btn-floating waves-effect waves-light white z-depth-1">
						<i class="material-icons grey-text text-darken-3">&#xE8AD;</i></a>
					<h4 class="light grey-text text-darken-2">Case <?php echo decrypt($_GET['data']); ?></h4><hr>
					<?php 
					$manual_injection = scan_log("log_access", decrypt($_GET['data']), "MANUAL_SQL_INJECTION"); 
					$manual_injection = count($manual_injection); 
					if($manual_injection == 0) {
							echo '<div class="chip green white-text">No Manual SQL Injection</div>';
					} else {
							echo '<div class="chip red white-text">'.$manual_injection.' Manual SQL Injection</div>';
					}

					$auto_injection = scan_log("log_access", decrypt($_GET['data']), "AUTO_SQL_INJECTION"); 
					$auto_injection = count($auto_injection); 
					if($auto_injection == 0) {
							echo '<div class="chip green white-text">No Auto SQL Injection</div>';
					} else {
							echo '<div class="chip red white-text">'.$auto_injection.' Auto SQL Injection</div>';
					}

					$default_shell = scan_log("log_access", decrypt($_GET['data']), "DEFAULT_SHELL"); 
					$default_shell = count($default_shell); 
					if($default_shell == 0) {
							echo '<div class="chip green white-text">No Default Shell</div>';
					} else {
							echo '<div class="chip red white-text">'.$default_shell.' Default Shell</div>';
					}
					$xss = scan_log("log_access", decrypt($_GET['data']), "XSS_DET"); 
					$xss = count($xss); 
					if($xss == 0) {
							echo '<div class="chip green white-text">No XSS</div>';
					} else {
							echo '<div class="chip red white-text">'.$xss.' XSS FOUND</div>';
					}
					echo $pagination;?>
				</div>
		<?php  foreach($data as $log_data) { ?>

				<div class="row">
					<div class="col s12 m12 l10 offset-l1">
						<div class="card">
							<div class="card-content" style="word-wrap: break-word;">
							<span class="card-title"><?php echo $log_data['id']; ?></span>
								<p><i class="mdi mdi-earth"></i> <b>Public IP : </b><?php echo $log_data['public_ip']; ?><a class="btn-floating btn-flat white waves-effect waves-light" href="?show=<?php echo $_GET['show']; ?>&data=<?php echo $_GET['data']; ?>&page=<?php echo $_GET['page']; ?>&info=<?php echo encrypt($log_data['public_ip']); ?>"><i class="material-icons grey-text text-darken-3">&#xE8B6;</i></a></p>
								<p><i class="mdi mdi-clock"></i> <b>Time : </b> <?php echo date("d M Y h:i A", strtotime($log_data['date_time'])).' '.$log_data['timezone']; ?></p>
								<p><i class="mdi mdi-send" ></i> <b>Method : </b><?php echo $log_data['method']; ?></p>
								<p><i class="mdi mdi-page-layout-header"></i> <b>Header : </b><?php echo $log_data['http_header']; ?></p>
								<p><i class="mdi mdi-reply"></i> <b>Response : </b><?php 
								$status_code1 = "100";
						$status_code2 = "101";
						$status_code3 = "102";
						$status_code4 = "200";
						$status_code5 = "201";
						$status_code6 = "202";
						$status_code7 = "203";
						$status_code8 = "204";
						$status_code9 = "205";
						$status_code10 = "206";
						$status_code11 = "207";
						$status_code12 = "208";
						$status_code13 = "226";
						$status_code14 = "300";
						$status_code15 = "301";
						$status_code16 = "302";
						$status_code17 = "303";
						$status_code18 = "304";
						$status_code19 = "305";
						$status_code20 = "307";
						$status_code21 = "308";
						$status_code22 = "400";
						$status_code23 = "401";
						$status_code24 = "402";
						$status_code25 = "403";
						$status_code26 = "404";
						$status_code27 = "405";
						$status_code28 = "406";
						$status_code29 = "407";
						$status_code30 = "408";
						$status_code31 = "409";
						$status_code32 = "410";
						$status_code33 = "411";
						$status_code34 = "412";
						$status_code35 = "413";
						$status_code36 = "414";
						$status_code37 = "415";
						$status_code38 = "416";
						$status_code39 = "417";
						$status_code40 = "418";
						$status_code41 = "421";
						$status_code42 = "422";
						$status_code43 = "423";
						$status_code44 = "424";
						$status_code45 = "426";
						$status_code46 = "428";
						$status_code47 = "429";
						$status_code48 = "431";
						$status_code49 = "444";
						$status_code50 = "451";
						$status_code51 = "499";
						$status_code52 = "500";
						$status_code53 = "501";
						$status_code54 = "502";
						$status_code55 = "503";
						$status_code56 = "504";
						$status_code57 = "505";
						$status_code58 = "506";
						$status_code59 = "507";
						$status_code60 = "508";
						$status_code61 = "510";
						$status_code62 = "511";
						$status_code63 = "599";
						
                        if(strpos($log_data['http_response'], $status_code1) !== false) {
                            $message="Continue";
                       
                        }elseif(strpos($log_data['http_response'], $status_code2) !== false) {
                           $message="Switching Protocols";
                        }elseif(strpos($log_data['http_response'], $status_code3) !== false) {
                           $message="Processing";
                        }elseif(strpos($log_data['http_response'], $status_code4) !== false) {
                           $message="OK";
                        }elseif(strpos($log_data['http_response'], $status_code5) !== false) {
                           $message="Created";
                        }elseif(strpos($log_data['http_response'], $status_code6) !== false) {
                           $message="Accepted";
                        }elseif(strpos($log_data['http_response'], $status_code7) !== false) {
                           $message="Non-authoritative Information";
                        }elseif(strpos($log_data['http_response'], $status_code8) !== false) {
                           $message=" No Content";
                        }elseif(strpos($log_data['http_response'], $status_code9) !== false) {
                           $message="Reset Content";
                        }elseif(strpos($log_data['http_response'], $status_code10) !== false) {
                           $message="Partial Content";
                        }elseif(strpos($log_data['http_response'], $status_code11) !== false) {
                           $message="Multi-Status";
                        }elseif(strpos($log_data['http_response'], $status_code12) !== false) {
                           $message="Already Reported";
                        }elseif(strpos($log_data['http_response'], $status_code13) !== false) {
                           $message="IM Used";
                        }elseif(strpos($log_data['http_response'], $status_code14) !== false) {
                           $message="Multiple Choices";
                        }elseif(strpos($log_data['http_response'], $status_code15) !== false) {
                           $message="Moved Permanently";
                        }elseif(strpos($log_data['http_response'], $status_code16) !== false) {
                           $message="Found";
                        }elseif(strpos($log_data['http_response'], $status_code17) !== false) {
                           $message="See Other";
                        }elseif(strpos($log_data['http_response'], $status_code18) !== false) {
                           $message="Not Modified";
                        }elseif(strpos($log_data['http_response'], $status_code19) !== false) {
                           $message="Use Proxy";
                        }elseif(strpos($log_data['http_response'], $status_code20) !== false) {
                           $message="Temporary Redirect";
                        }elseif(strpos($log_data['http_response'], $status_code21) !== false) {
                           $message="Permanent Redirect";
                        }elseif(strpos($log_data['http_response'], $status_code22) !== false) {
                           $message="Bad Request";
                        }elseif(strpos($log_data['http_response'], $status_code23) !== false) {
                           $message="Unauthorized";
                        }elseif(strpos($log_data['http_response'], $status_code24) !== false) {
                           $message="Payment Required";
                        }elseif(strpos($log_data['http_response'], $status_code25) !== false) {
                           $message="Forbidden";
                        }elseif(strpos($log_data['http_response'], $status_code26) !== false) {
                           $message="Not Found";
                        }elseif(strpos($log_data['http_response'], $status_code27) !== false) {
                           $message="Method Not Allowed";
                        }elseif(strpos($log_data['http_response'], $status_code28) !== false) {
                           $message="Not Acceptable";
                        }elseif(strpos($log_data['http_response'], $status_code29) !== false) {
                           $message="Proxy Authentication Required";
                        }elseif(strpos($log_data['http_response'], $status_code30) !== false) {
                           $message="Request Timeout";
                        }elseif(strpos($log_data['http_response'], $status_code31) !== false) {
                           $message="Conflict";
                        }elseif(strpos($log_data['http_response'], $status_code32) !== false) {
                           $message="Gone";
                        }elseif(strpos($log_data['http_response'], $status_code33) !== false) {
                           $message="Length Required";
                        }elseif(strpos($log_data['http_response'], $status_code34) !== false) {
                           $message="Precondition Failed";
                        }elseif(strpos($log_data['http_response'], $status_code35) !== false) {
                           $message="Payload Too Large";
                        }elseif(strpos($log_data['http_response'], $status_code36) !== false) {
                           $message="Request-URI Too Long";
                        }elseif(strpos($log_data['http_response'], $status_code37) !== false) {
                           $message="Unsupported Media Type";
                        }elseif(strpos($log_data['http_response'], $status_code38) !== false) {
                           $message="Requested Range Not Satisfiable";
                        }elseif(strpos($log_data['http_response'], $status_code39) !== false) {
                           $message="Expectation Failed";
                        }elseif(strpos($log_data['http_response'], $status_code40) !== false) {
                           $message=" I'm a teapot";
                        }elseif(strpos($log_data['http_response'], $status_code41) !== false) {
                           $message="Misdirected Request";
                        }elseif(strpos($log_data['http_response'], $status_code42) !== false) {
                           $message="Unprocessable Entity";
                        }elseif(strpos($log_data['http_response'], $status_code43) !== false) {
                           $message="Locked";
                        }elseif(strpos($log_data['http_response'], $status_code44) !== false) {
                           $message="Failed Dependency";
                        }elseif(strpos($log_data['http_response'], $status_code45) !== false) {
                           $message="Upgrade Required";
                        }elseif(strpos($log_data['http_response'], $status_code46) !== false) {
                           $message="Precondition Required";
                        }elseif(strpos($log_data['http_response'], $status_code47) !== false) {
                           $message="Too Many Requests";
                        }elseif(strpos($log_data['http_response'], $status_code48) !== false) {
                           $message="Request Header Fields Too Large";
                        }elseif(strpos($log_data['http_response'], $status_code49) !== false) {
                           $message="Connection Closed Without Response";
                        }elseif(strpos($log_data['http_response'], $status_code50) !== false) {
                           $message="Unavailable For Legal Reasons";
                        }elseif(strpos($log_data['http_response'], $status_code51) !== false) {
                           $message="Client Closed Request";
                        }elseif(strpos($log_data['http_response'], $status_code52) !== false) {
                           $message="Internal Server Error";
                        }elseif(strpos($log_data['http_response'], $status_code53) !== false) {
                           $message=" Not Implemented";
                        }elseif(strpos($log_data['http_response'], $status_code54) !== false) {
                           $message="Bad Gateway";
                        }elseif(strpos($log_data['http_response'], $status_code55) !== false) {
                           $message="Service Unavailable";
                        }elseif(strpos($log_data['http_response'], $status_code56) !== false) {
                           $message="Gateway Timeout";
                        }elseif(strpos($log_data['http_response'], $status_code57) !== false) {
                           $message="HTTP Version Not Supported";
                        }elseif(strpos($log_data['http_response'], $status_code58) !== false) {
                           $message="Variant Also Negotiates";
                        }elseif(strpos($log_data['http_response'], $status_code59) !== false) {
                           $message="Insufficient Storage";
                        }elseif(strpos($log_data['http_response'], $status_code60) !== false) {
                           $message="Loop Detected";
                        }elseif(strpos($log_data['http_response'], $status_code61) !== false) {
                           $message="Not Extended";
                        }elseif(strpos($log_data['http_response'], $status_code62) !== false) {
                           $message="Network Authentication Required";
                        }elseif(strpos($log_data['http_response'], $status_code63) !== false) {
                           $message="Network Connect Timeout Error";
                        }else{
                            $message="N/A";
                        }
                     ?>


				
				<a class="tooltipped" data-position="top" data-delay="10" data-tooltip="<?php echo   $message; ?>"><font color ="blue"><?php echo $log_data['http_response']; ?>	</font></a></p>
								<p><i class="mdi mdi-file"></i> <b>File Size : </b><?php echo $log_data['file_bytes']; ?> B</p>
								<p><i class="mdi mdi-link"></i> <b>Reference : </b><a href="<?php echo $log_data['link_ref']; ?>" target="_blank"><?php echo $log_data['link_ref']; ?></a></p>
								<p><i class="mdi mdi-earth"></i> <b>Browser : </b><?php 
									$result = new WhichBrowser\Parser($log_data['useragent']);
									if(!empty($result->browser->name)) {
										echo $result->browser->name;
									}else{
										echo $log_data['useragent'];
									}
									if(!empty($result->browser->version->value)){
										echo ' '.$result->browser->version->value;
									}
									?></p>
							</div>
						</div>
					</div>
				</div>

				<br>
		<?php } 
		echo $pagination; 
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
		<?php foreach($data as $log_data) { ?>

				<div class="row">
					<div class="col s12 m12 l10 offset-l1">
						<div class="card">
							<div class="card-content" style="word-wrap: break-word;">
							<span class="card-title"><?php echo $log_data['id']; ?></span>
								<p><i class="mdi mdi-earth"></i> <b>Public IP : </b> <?php echo $log_data['public_ip']; ?><a class="btn-floating btn-flat white waves-effect waves-light" href="?show=<?php echo $_GET['show']; ?>&data=<?php echo $_GET['data']; ?>&page=<?php echo $_GET['page']; ?>&info=<?php echo encrypt($log_data['public_ip']); ?>"><i class="material-icons grey-text text-darken-3">&#xE8B6;</i></a></p>
								<p><i class="mdi mdi-clock"></i> <b>Time : </b> <?php echo date("d M Y h:i A", strtotime($log_data['date_time'])); ?></p>
								<p><i class="mdi mdi-page-layout-header"></i> <b>Protocol : </b><?php echo $log_data['protocol']; ?></p>
								<p><i class="mdi mdi-bell"></i> <b>Notification : </b><?php echo $log_data['notification']; ?></p>
								<p><i class="mdi mdi-message-text"></i> <b>Message : </b><?php echo $log_data['message']; ?></p>
							</div>
						</div>
					</div>
				</div>
		<?php } 
		echo $pagination;
		}
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