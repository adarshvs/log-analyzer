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
        list: 'Show attack list',
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
      navLinks: false, // can click day/week names to navigate views
      businessHours: true, // display business hours
      editable: true,
      selectable: true,
      events: allEvents,

    });

    calendar.render();


  });

</script>

  <div class="row">

    <div class="col s12 m12">
      
      &nbsp;&nbsp;&nbsp;

    <h4 class="light grey-text text-darken-2">Case <?php echo decrypt($_GET['data']); ?>
    <a href="?show=<?php echo $_GET['show']; ?>&data=<?php echo $_GET['data']; ?>&page=&print" class="right btn-floating waves-effect waves-light white z-depth-1"><i class="material-icons grey-text text-darken-3">file_download</i></a>
      <a target="_blank" class="waves-effect waves-light btn right" href="log_detail_view.php?show=access_log&data=<?php echo $_GET['data']; ?>"><i class="large material-icons" style="
    font-size: 12px;
">arrow_forward</i>Show All</a> &nbsp;&nbsp;&nbsp;

    </h4>
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
  <?php 
      $graph = new CountAndRowByID;
      $values = $graph->getRows('log_access', 'public_ip', $_GET['data'], 10);
      $counts = $graph->getCount('log_access', 'public_ip', $_GET['data'], 10);
      $browser_name = $graph->getRows('log_access', 'http_response', $_GET['data'], 10);
      $browser_count = $graph->getCount('log_access', 'http_response', $_GET['data'], 10);
   //print_r($values);die();

      ?>

<div class="row">
  <div class="col s12 m6 l6"> 
          <div class="card">
            <div class="card-content ersr">
              <span class="purple-text card-title">Top 10 Public IPs</span>
              <div class="divider"></div>
              <?php if(!empty($values)): ?>
              <canvas id="log_chart" height="300px" />
              <?php else: ?>
                <div class="center">
                  <p>
                    <h1 class="flow-text">NO DATA</h1>
                  </p>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <div class="col s12 m6 l6">
          <div class="card">
            <div class="card-content ersr">
              <span class="purple-text card-title">Top HTTP Responses</span>
              <div class="divider"></div>
              <?php if(!empty($browser_name)): ?>
              <canvas id="crawler_chart" height="300px" />
              <?php else: ?>
                <div class="center">
                  <p>
                    <h1 class="flow-text">NO DATA</h1>
                  </p>
                </div>
              <?php endif; ?>
            </div>
          </div>
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
    $log_datas = $obj->getAllAttacksbyDateForAccessLog($_GET['attack_date'],$case_no); 
  } catch (Exception $e) {
  }
  include('access_log_card.php');
} ?>

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
             <?php
            $message = showMessageResponse($log_data['http_response']);
             ?>
            <p><i class="mdi mdi-page-layout-header"></i> <b>Header : </b><?php echo $log_data['http_header']; ?></p>
            <p><i class="mdi mdi-reply"></i> <b>Response : </b> <?php
            $message = showMessageResponse($log_data['http_response']);
             ?>
        
        <a class="tooltipped" data-position="top" data-delay="10" data-tooltip="<?php echo   $message; ?>"><font color ="blue"><?php echo $log_data['http_response']; ?> &nbsp;&nbsp; </font></a></p>
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
       <?php include('sys_show_log.php')?>
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
      <?php include('sys_show_log.php')?>
    </div>
  </div>
</div>
<?php } ?>

<?php  
if(isset($_GET['date'])){
  
  $case_no = decrypt($_GET['data']);
  try {
    $p_data = new AccessLogModel();
    $data = $p_data->pageData('log_access', $_GET['page'], 'access_log', $_GET['data'], 20, $_GET['date']);
   // print_r($data);die();
    $pagination = $p_data->pagination();
  
    include('access_log_table.php');
  } catch (Exception $e) {
   // print_r($e->getMessage());
  }
 
} ?>

 <script type="text/javascript" src="assets/js/chart.min.js?v=2.6.0"></script>
    <script type="text/javascript"> 
     var ctx = document.getElementById('log_chart').getContext('2d');
      var IpsCount = [<?php
                    $county = "";
                    foreach($counts as $count) {
                      $county .= '"'.$count.'",';
                    }
                    $county = rtrim($county, ",");
                    echo $county;
                ?>
        ];

      var chart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: [
            <?php
              $ips = "";
              foreach($values as $value) {
                $ips .= '"'.$value.'",';
              }
              $ips = rtrim($ips, ",");
              echo $ips;
            ?>],
            datasets: [{
                label: "Public IP",
                data: IpsCount,
                backgroundColor: ['#ffb74d','#e57373','#ba68c8','#7986cb','#64b5f6','#4dd0e1','#4db6ac','#81c784','#dce775', '#ffd54f'],
            }]
          },
        options: {
          responsive: true,
          legend: {
            display: true
          }
        }
      }); 
      var ctx = document.getElementById('crawler_chart').getContext('2d');
      var IpsCount = [<?php
                    $county = "";
                    foreach($browser_count as $count) {
                      $county .= '"'.$count.'",';
                    }
                    $county = rtrim($county, ",");
                    echo $county;
                ?>];

      var chart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: [
            <?php
              $ips = "";
              foreach($browser_name as $value) {
                $ips .= '"'.$value.'",';
              }
              $ips = rtrim($ips, ",");
              echo $ips;
            ?>],
            datasets: [{
                label: "Browsers",
                data: IpsCount,
                backgroundColor: ['#4db6ac', '#ffb74d','#e57373','#ba68c8','#7986cb','#64b5f6','#4dd0e1','#81c784','#dce775', '#ffd54f'],
            }]
          },
        options: {
          responsive: true,
          legend: {
            display: false
          }
        }
      }); </script>