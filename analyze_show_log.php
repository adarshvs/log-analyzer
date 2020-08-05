<script type="text/javascript">
  $(document).ready(function(){
    $('input[name="split_window"]').on('click',function(){
      if($(this).is(':checked')) {
        if ($(this).val() == 'access_log_only') {
          $('#accessLogConatiner').addClass('m12').removeClass('m6');
          $('#sysLogConatiner').hide();
          $('#accessLogConatiner').show();

        }else if($(this).val() == 'sys_log_only'){
          $('#sysLogConatiner').addClass('m12').removeClass('m6');
          $('#accessLogConatiner').hide();
          $('#sysLogConatiner').show();
        }else if($(this).val() == 'split'){
          $('#sysLogConatiner').addClass('m6').removeClass('m12');
          $('#accessLogConatiner').addClass('m6').removeClass('m12');
          $('#sysLogConatiner').show();
          $('#accessLogConatiner').show();
        }
       }
    });
  });
</script>
<script>
  <?php  $obj = new Model(); ?>
  document.addEventListener('DOMContentLoaded', function() { 

    var parentUrl = 'http://localhost/log-analyzer/analyze.php?show=access_log&data=NDU2';
    var calendarEl = document.getElementById('calendar');
    var allEvents = <?= json_encode($obj->getAllAttacksForCalendar(decrypt($_GET['data'])))?>;
    var calendar = new FullCalendar.Calendar(calendarEl, {
    	eventStartEditable: false,
      displayEventTime: false,
    	height: 450,
      buttonText: {
        list: 'Show all attacks',
      },
    	aspectRatio: 1,
    	themeSystem: 'flatly',
    	eventColor: 'red',
    	eventTextColor: 'white',
      	headerToolbar: {
        left: 'prev,next today',
        center: 'title',
        right: 'dayGridMonth,listMonth'
      },
      initialDate: '2020-08-01',
      navLinks: true, // can click day/week names to navigate views
      businessHours: true, // display business hours
      editable: true,
      selectable: true,
      events: allEvents
    });

    calendar.render();

  });

</script>

  <div class="row">

    <div class="col s12 m12">
      <a href="?show=<?php echo $_GET['show']; ?>&data=<?php echo $_GET['data']; ?>&page=&print" class="right btn-floating waves-effect waves-light white z-depth-1"><i class="material-icons grey-text text-darken-3">&#xE8AD;</i></a>
    <h4 class="light grey-text text-darken-2">Case <?php echo decrypt($_GET['data']); ?></h4>
    <?php 
      if (!(isset($_GET['public_ip'])||isset($_GET['date_time']))){ 
        echo '<hr>';
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
    ?>
    </div>
  </div>
  <div class="card-panel">
    <h5 class="card-title">At a glance</h5><hr>
  
      <div id='calendar' style="margin-top: 20px"></div>
    
  </div>
  <?php } ?>

<!-- Attacked dates -->
<?php  
if(isset($_GET['attack_date']) && !empty($_GET['attack_date'])){
  $obj = new Model();
  $case_no = decrypt($_GET['data']);
  try {
    $attack_data = $obj->getAllAttacksbyDateForAccessLog($_GET['attack_date'],$case_no); 
  } catch (Exception $e) {
   // print_r($e->getMessage());
  }

?>
<div class="card-panel" id="attackDate">
  
  <?php foreach ($attack_data as $log_data) { ?>
  <div class="col s12 m12 l10 offset-l1">
      <div class="card" style="
    background-color: #fbfbfb;
    box-shadow: 1px 2px 3px 0px black;
    ">
        <div class="card-content" style="word-wrap: break-word;">
          <span class="card-title">#<?php echo $log_data['id']; ?></span>
          <p>
            <i class="mdi mdi-earth"></i> <b>IP : </b>
            <a target="_blank" href="?show=<?php echo $_GET['show']; ?>&data=<?php echo $_GET['data']; ?>&public_ip=<?php echo $log_data['public_ip']; ?>&href=#logTable"><?php echo $log_data['public_ip']; ?></a>
            <a class="btn-floating btn-flat white waves-effect waves-light" href="?show=<?php echo $_GET['show']; ?>&data=<?php echo $_GET['data']; ?>&page=&info=<?php echo encrypt($log_data['public_ip']); ?>"><i class="material-icons grey-text text-darken-3" style="
    background-color: #fbfbfb;">&#xE8B6;</i></a>
          
            <i class="mdi mdi-clock"></i> <b>Time : </b> <a target="_blank" href="?show=<?php echo $_GET['show']; ?>&data=<?php echo $_GET['data']; ?>&date_time=<?php echo $log_data['date_time']; ?>"><?php echo date("h:i A", strtotime($log_data['date_time'])) ?></a>
          </p>
          <p><i class="mdi mdi-send" ></i> <b>Method : </b><?php echo $log_data['method']; ?>&nbsp;&nbsp;
             <i class="mdi mdi-reply"></i> <b>Response : </b><?php 
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


        
        <a class="tooltipped" data-position="top" data-delay="10" data-tooltip="<?php echo   $message; ?>"><font color ="blue"><?php echo $log_data['http_response']; ?> &nbsp;&nbsp; </font></a>
          <i class="mdi mdi-file"></i> <b>Content Size : </b><?php echo number_format($log_data['file_bytes']); ?> bytes
        </p>
          <p><i class="mdi mdi-page-layout-header"></i> <b>Header : </b><?php echo $log_data['http_header']; ?></p>
          <p><i class="mdi mdi-link"></i> <b>Reference : </b><a href="<?php echo $log_data['link_ref']; ?>" target="_blank"><?php echo $log_data['link_ref']; ?></a></p>
          <p><i class="mdi mdi-note-text"></i> <b>Note : </b><span style="color: red;"><?php echo $log_data['attack_description']; ?></span></p><br/>
          <p style="
    color: #4d4949;
    font-size: 12px;
    box-shadow: 0 0 2px 0px black;
    /* box-shadow: -1px 2px 5px 0px black; */
    /* background-color: white; */
    background-color: #f1f1f1;
    "><?php echo $log_data['raw_data']; ?></p>

          
        </div>
      </div>
    </div>
    
  <?php }  ?>

</div>
<?php } ?>

<!-- Public IP -->
<?php  
if(isset($_GET['public_ip'])){
  $obj = new Model();
  $case_no = decrypt($_GET['data']);
  try {
    $log_datas = $obj->getAllAccessLogByIp($_GET['public_ip'],$case_no,1,10); 
    $sys_log_datas = $obj->getAllSysLogByIp($_GET['public_ip'],$case_no,1,10); 
  } catch (Exception $e) {
   // print_r($e->getMessage());
  }
 
?>

<div class="" id="logTable" style="margin-top: 50px;margin-bottom: 50px;
">
    <span class="right" style="margin-right: 10px;">
      <input name="split_window" type="radio" id="split" value="split" />
      <label for="split">Split</label>
    </span>
     <span class="right" style="margin-right: 10px;">
      <input name="split_window" type="radio" id="access_log_only" value="access_log_only" />
      <label for="access_log_only">Show Access log only</label>
    </span>
     <span class="right" style="margin-right: 10px;">
      <input name="split_window" type="radio" id="sys_log_only" value="sys_log_only" />
      <label for="sys_log_only">Show Sys log only</label>
    </span>
    <h5 class="card-title"><?php echo $_GET['public_ip']; ?></h5>
  <hr>
  <div class="col m6 s12" id="accessLogConatiner" style="padding-left: 0px !important;">
    <h5>Access log</h5>
    <div style="max-height: 600px; overflow-y: scroll;">
      <?php foreach ($log_datas as $key => $log_data) { ?>
      <div class="col s12 m12"  style="padding-left: 0px !important;">
        <div class="card">
          <div class="card-content" style="word-wrap: break-word;">
            <span class="card-title">#<?php echo $log_data['id']; ?></span>
            <p>
              <i class="mdi mdi-earth"></i> <b>IP : </b>
              <a href="?show=<?php echo $_GET['show']; ?>&data=<?php echo $_GET['data']; ?>&public_ip=<?php echo $log_data['public_ip']; ?>"><?php echo $log_data['public_ip']; ?></a>
              <a class="btn-floating btn-flat white waves-effect waves-light" href="?show=<?php echo $_GET['show']; ?>&data=<?php echo $_GET['data']; ?>&page=&info=<?php echo encrypt($log_data['public_ip']); ?>"><i class="material-icons grey-text text-darken-3">&#xE8B6;</i></a>
            </p>
            <p><i class="mdi mdi-clock"></i> <b>Timestamp : </b> <?php echo date("d M Y h:i A", strtotime($log_data['date_time'])); ?></p>
            <p><i class="mdi mdi-send" ></i> <b>Method : </b><?php echo $log_data['method']; ?></p>
            <p><i class="mdi mdi-page-layout-header"></i> <b>Header : </b><?php echo $log_data['http_header']; ?></p>
            <p><i class="mdi mdi-reply"></i> <b>Response : </b> Misdirected Request</p>
            <p><i class="mdi mdi-file"></i> <b>File Size : </b><?php echo number_format($log_data['file_bytes']); ?> bytes</p>
            <p><i class="mdi mdi-link"></i> <b>Referrer : </b><a href="<?php echo $log_data['link_ref']; ?>" target="_blank"><?php echo $log_data['link_ref']; ?></a></p>
            <p>
              <i class="mdi mdi-earth"></i> <b>Browser : </b><?php 
              $result = new WhichBrowser\Parser($log_data['useragent']);
              if(!empty($result->browser->name)) {
                echo $result->browser->name;
              }else{
                echo $log_data['useragent'];
              }
              if(!empty($result->browser->version->value)){
                echo ' '.$result->browser->version->value;
              }
              ?>
            </p><p style="
    color: #978d8d;
    font-size: 11px;"><?php echo $log_data['raw_data']; ?></p>
          </div>
        </div>
      </div>
      <?php } ?>
    </div>
  </div>
  <div class="col m6 s12" id="sysLogConatiner"  style="padding-left: 0px !important;">
    <h5>Sys log</h5>
    <div style="max-height: 600px; overflow-y: scroll;">
      <?php foreach ($sys_log_datas as $key => $log_data) { ?>
      <div class="col s12 m12" style="padding-left: 0px !important;">
        <div class="card">
          <div class="card-content" style="word-wrap: break-word;">
            <span class="card-title"><?php echo $log_data['id']; ?></span>
            <p>
              <i class="mdi mdi-earth"></i> <b>Public IP : </b>
              <a href="?show=<?php echo $_GET['show']; ?>&data=<?php echo $_GET['data']; ?>&public_ip=<?php echo $log_data['public_ip']; ?>"><?php echo $log_data['public_ip']; ?></a>
              <a class="btn-floating btn-flat white waves-effect waves-light" href="?show=<?php echo $_GET['show']; ?>&data=<?php echo $_GET['data']; ?>&page=&info=<?php echo encrypt($log_data['public_ip']); ?>"><i class="material-icons grey-text text-darken-3">&#xE8B6;</i></a>
            </p>
            <p><i class="mdi mdi-clock"></i> <b>Time : </b> <?php echo date("d M Y h:i A", strtotime($log_data['date_time']))?></p>
            <p><i class="mdi mdi-page-layout-header"></i> <b>Protocol : </b><?php echo $log_data['protocol']; ?></p>
            <p><i class="mdi mdi-bell"></i> <b>Notification : </b><?php echo $log_data['notification']; ?></p>
            <p><i class="mdi mdi-message-text"></i> <b>Message : </b><?php echo $log_data['message']; ?></p>
          </div>
        </div>
      </div>
      <?php } ?>
    </div>
  </div>
</div>


<?php } ?>
<?php  
if(isset($_GET['date_time'])){
  $obj = new Model();
  $case_no = decrypt($_GET['data']);
  try {
    $log_datas = $obj->getAllAccessLogByDate($_GET['date_time'],$case_no,1,100); 
    $sys_log_datas = $obj->getAllSysLogByDate($_GET['date_time'],$case_no,1,100); 
  } catch (Exception $e) {
   // print_r($e->getMessage());
  }
 
?>

<div class="" id="logTable" style="margin-top: 50px;margin-bottom: 50px;
">
    <span class="right" style="margin-right: 10px;">
      <input name="split_window" type="radio" id="split" value="split" />
      <label for="split">Split</label>
    </span>
     <span class="right" style="margin-right: 10px;">
      <input name="split_window" type="radio" id="access_log_only" value="access_log_only" />
      <label for="access_log_only">Show Access log only</label>
    </span>
     <span class="right" style="margin-right: 10px;">
      <input name="split_window" type="radio" id="sys_log_only" value="sys_log_only" />
      <label for="sys_log_only">Show Sys log only</label>
    </span>
    <h5 class="card-title"><?php echo $_GET['date_time']; ?></h5>
  <hr>
  <div class="col m6 s12" id="accessLogConatiner" style="padding-left: 0px !important;">
    <h5>Access log</h5>
    <div style="max-height: 600px; overflow-y: scroll;">
      <?php foreach ($log_datas as $key => $log_data) { ?>
      <div class="col s12 m12"  style="padding-left: 0px !important;">
        <div class="card">
          <div class="card-content" style="word-wrap: break-word;">
            <span class="card-title">#<?php echo $log_data['id']; ?></span>
            <p>
              <i class="mdi mdi-earth"></i> <b>IP : </b>
              <a href="?show=<?php echo $_GET['show']; ?>&data=<?php echo $_GET['data']; ?>&public_ip=<?php echo $log_data['public_ip']; ?>"><?php echo $log_data['public_ip']; ?></a>
              <a class="btn-floating btn-flat white waves-effect waves-light" href="?show=<?php echo $_GET['show']; ?>&data=<?php echo $_GET['data']; ?>&page=&info=<?php echo encrypt($log_data['public_ip']); ?>"><i class="material-icons grey-text text-darken-3">&#xE8B6;</i></a>
            </p>
            <p><i class="mdi mdi-clock"></i> <b>Timestamp : </b> <?php echo date("d M Y h:i A", strtotime($log_data['date_time'])); ?></p>
            <p><i class="mdi mdi-send" ></i> <b>Method : </b><?php echo $log_data['method']; ?></p>
            <p><i class="mdi mdi-page-layout-header"></i> <b>Header : </b><?php echo $log_data['http_header']; ?></p>
            <p><i class="mdi mdi-reply"></i> <b>Response : </b> Misdirected Request</p>
            <p><i class="mdi mdi-file"></i> <b>File Size : </b><?php echo number_format($log_data['file_bytes']); ?> bytes</p>
            <p><i class="mdi mdi-link"></i> <b>Referrer : </b><a href="<?php echo $log_data['link_ref']; ?>" target="_blank"><?php echo $log_data['link_ref']; ?></a></p>
            <p>
              <i class="mdi mdi-earth"></i> <b>Browser : </b><?php 
              $result = new WhichBrowser\Parser($log_data['useragent']);
              if(!empty($result->browser->name)) {
                echo $result->browser->name;
              }else{
                echo $log_data['useragent'];
              }
              if(!empty($result->browser->version->value)){
                echo ' '.$result->browser->version->value;
              }
              ?>
            </p><p style="
    color: #978d8d;
    font-size: 11px;"><?php echo $log_data['raw_data']; ?></p>
          </div>
        </div>
      </div>
      <?php } ?>
    </div>
  </div>
  <div class="col m6 s12" id="sysLogConatiner"  style="padding-left: 0px !important;">
    <h5>Sys log</h5>
    <div style="max-height: 600px; overflow-y: scroll;">
      <?php foreach ($sys_log_datas as $key => $log_data) { ?>
      <div class="col s12 m12" style="padding-left: 0px !important;">
        <div class="card">
          <div class="card-content" style="word-wrap: break-word;">
            <span class="card-title"><?php echo $log_data['id']; ?></span>
            <p>
              <i class="mdi mdi-earth"></i> <b>Public IP : </b>
              <a href="?show=<?php echo $_GET['show']; ?>&data=<?php echo $_GET['data']; ?>&public_ip=<?php echo $log_data['public_ip']; ?>"><?php echo $log_data['public_ip']; ?></a>
              <a class="btn-floating btn-flat white waves-effect waves-light" href="?show=<?php echo $_GET['show']; ?>&data=<?php echo $_GET['data']; ?>&page=&info=<?php echo encrypt($log_data['public_ip']); ?>"><i class="material-icons grey-text text-darken-3">&#xE8B6;</i></a>
            </p>
            <p><i class="mdi mdi-clock"></i> <b>Time : </b> <?php echo date("d M Y h:i A", strtotime($log_data['date_time']))?></p>
            <p><i class="mdi mdi-page-layout-header"></i> <b>Protocol : </b><?php echo $log_data['protocol']; ?></p>
            <p><i class="mdi mdi-bell"></i> <b>Notification : </b><?php echo $log_data['notification']; ?></p>
            <p><i class="mdi mdi-message-text"></i> <b>Message : </b><?php echo $log_data['message']; ?></p>
          </div>
        </div>
      </div>
      <?php } ?>
    </div>
  </div>
</div>


<?php } ?>