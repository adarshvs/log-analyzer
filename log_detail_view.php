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
    <?php } }

    if($_GET['show'] == 'access_log'){
      if(!isset($_GET['page'])) {
        header('Location: ?show='.$_GET['show'].'&data='.$_GET['data'].'&page=1');
        exit();
      }
      $p_data = new Page('log_access', $_GET['page'], $_GET['show'], $_GET['data'], 20);
      $data = $p_data->pageData();
      $pagination = $p_data->pagination();
      include_once('includes/header.php');
?>

<div class="row">
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
        <th>IP</th>
        <th>Timestamp </th>
        <th>Method</th>
        <th>Header</th>
        <th>Response</th>
        <th>Content Length</th>
        <th>Referrer</th>
        <th>Browser</th>
        <th>Raw Data</th>
        </tr>
      </thead>
      <tbody>
        <?php  foreach($data as $log_data) { ?>
        <tr>
          <td><?php echo $log_data['id']; ?></td>
          <td><a target="_blank" href="analyze.php?show=<?php echo $_GET['show']; ?>&data=<?php echo $_GET['data']; ?>&public_ip=<?php echo $log_data['public_ip']; ?>&href=#logTable"><?php echo $log_data['public_ip']; ?></a><a class="btn-floating btn-flat white waves-effect waves-light" href="?show=<?php echo $_GET['show']; ?>&data=<?php echo $_GET['data']; ?>&page=<?php echo $_GET['page']; ?>&info=<?php echo encrypt($log_data['public_ip']); ?>"><i class="material-icons grey-text text-darken-3">&#xE8B6;</i></a></td>
          <td><a target="_blank" href="analyze.php?show=<?php echo $_GET['show']; ?>&data=<?php echo $_GET['data']; ?>&date_time=<?php echo $log_data['date_time']; ?>"><?php echo date("d M Y h:i A", strtotime($log_data['date_time'])) ?></a></td>
          <td><?php echo $log_data['method']; ?></td>
          <td><?php echo $log_data['http_header']; ?></td>
          <td><?php 
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
                     ?><a class="tooltipped" data-position="top" data-delay="10" data-tooltip="<?php echo   $message; ?>"><font color ="blue"><?php echo $log_data['http_response']; ?> </font></a></td>
          <td><?php echo number_format($log_data['file_bytes']); ?> bytes</td>
          <td><a href="<?php echo $log_data['link_ref']; ?>" target="_blank"><?php echo $log_data['link_ref']; ?></a></td>
          <td><?php 
                  $result = new WhichBrowser\Parser($log_data['useragent']);
                  if(!empty($result->browser->name)) {
                    echo $result->browser->name;
                  }else{
                    echo $log_data['useragent'];
                  }
                  if(!empty($result->browser->version->value)){
                    echo ' '.$result->browser->version->value;
                  }
                  ?></td>
          <td style="
    font-size: 12px;
    font-family: Lucida Console;
"><?php echo $log_data['raw_data']; ?></td>
        </tr><?php }?>
      </tbody>
      </table> 
    </div><?php echo $pagination; ?>
  </div>
</div>
<?php 
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