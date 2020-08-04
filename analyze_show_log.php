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
      if (!isset($_GET['public_ip'])){ 
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
  <h5 class="card-title"><?php echo $_GET['attack_date']; ?></h5><hr>
  <?php foreach ($attack_data as $log_data) { ?>
  <div class="col s12 m12 l10 offset-l1">
      <div class="card">
        <div class="card-content" style="word-wrap: break-word;">
          <span class="card-title"><?php echo $log_data['id']; ?></span>
          <p>
            <i class="mdi mdi-earth"></i> <b>Public IP : </b>
            <a target="_blank" href="?show=<?php echo $_GET['show']; ?>&data=<?php echo $_GET['data']; ?>&public_ip=<?php echo $log_data['public_ip']; ?>&href=#logTable"><?php echo $log_data['public_ip']; ?></a>
            <a class="btn-floating btn-flat white waves-effect waves-light" href="?show=<?php echo $_GET['show']; ?>&data=<?php echo $_GET['data']; ?>&page=&info=<?php echo encrypt($log_data['public_ip']); ?>"><i class="material-icons grey-text text-darken-3">&#xE8B6;</i></a>
          </p>
          <p>
            <i class="mdi mdi-clock"></i> <b>Time : </b> <a href="?show=<?php echo $_GET['show']; ?>&data=<?php echo $_GET['data']; ?>&date_time=<?php echo $log_data['date_time']; ?>"><?php echo date("d M Y h:i A", strtotime($log_data['date_time'])) ?></a>
          </p>
          <p><i class="mdi mdi-clock"></i> <b>Description : </b><span style="color: red;"><?php echo $log_data['attack_description']; ?></span></p>
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
            <span class="card-title"><?php echo $log_data['id']; ?></span>
            <p>
              <i class="mdi mdi-earth"></i> <b>Public IP : </b>
              <a href="?show=<?php echo $_GET['show']; ?>&data=<?php echo $_GET['data']; ?>&public_ip=<?php echo $log_data['public_ip']; ?>"><?php echo $log_data['public_ip']; ?></a>
              <a class="btn-floating btn-flat white waves-effect waves-light" href="?show=<?php echo $_GET['show']; ?>&data=<?php echo $_GET['data']; ?>&page=&info=<?php echo encrypt($log_data['public_ip']); ?>"><i class="material-icons grey-text text-darken-3">&#xE8B6;</i></a>
            </p>
            <p><i class="mdi mdi-clock"></i> <b>Time : </b> <?php echo date("d M Y h:i A", strtotime($log_data['date_time'])).' '.$log_data['timezone']; ?></p>
            <p><i class="mdi mdi-send" ></i> <b>Method : </b><?php echo $log_data['method']; ?></p>
            <p><i class="mdi mdi-page-layout-header"></i> <b>Header : </b><?php echo $log_data['http_header']; ?></p>
            <p><i class="mdi mdi-reply"></i> <b>Response : </b> Misdirected Request</p>
            <p><i class="mdi mdi-file"></i> <b>File Size : </b><?php echo $log_data['file_bytes']; ?> B</p>
            <p><i class="mdi mdi-link"></i> <b>Reference : </b><a href="<?php echo $log_data['link_ref']; ?>" target="_blank"><?php echo $log_data['link_ref']; ?></a></p>
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
            </p>
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