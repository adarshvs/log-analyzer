<?php
include_once('includes/connection.php');
error_reporting(0);
$case_no = $_GET['data'];
$date_range = $_GET['date_range'];
$table_name = 'log_access';//$_GET['show'];
$dates = explode(' - ', $date_range);
$start_date_time = date('Y-m-d H:i:s', strtotime($dates[0].' 00:00:00'));
$end_date_time = date('Y-m-d H:i:s', strtotime($dates[1].' 24:00:00'));
$caseObj = new CaseDetailLogModel();
$caseRow = $caseObj->getCaseByNo($case_no); 
$access_log_obj = new AccessLogModel();
$log_datas = $access_log_obj->getAccessLogByDateRange($case_no,$start_date_time,$end_date_time);
//print_r($caseRow);die();
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Log Analyzer</title>
   
    <style type="text/css">
      .material-icons{
        font-size: 12px !important;
      }
      .summary-items {
        list-style: circle !important;
        margin-bottom: 10px;
      }
      .head-cell{
        width: 60px;
      }

     body{
      border: 1px solid;
      margin: 10px;
     }
    </style>
  </head>
  <body>

    <script type="text/php">
    if ( isset($pdf) ) { 
        $pdf->page_script('
            if ($PAGE_COUNT > 1) {
                $font = $fontMetrics->get_font("Arial, Helvetica, sans-serif", "normal");
                $size = 12;
                $pageText = "Page " . $PAGE_NUM . " of " . $PAGE_COUNT;
                $y = 15;
                $x = 520;
                $pdf->text($x, $y, $pageText, $font, $size);
            } 
        ');
    }
  </script>
  	<div style="margin: 10px;">
  		<div class="col s12 m12">
  		<h4>Case <?php echo $caseRow[0]['case_no']; ?></h4><hr>
  		</div>
      <div class="col s12 m12">
        <p>Exported Date: <?php echo date('Y-m-d H:i:s'); ?></p>
        <p>Reference no.: <?php echo $caseRow[0]['ref_no']; ?></p>
        <p>Site URL: <?php echo $caseRow[0]['site_url']; ?></p>
        <p>Registered date: <?php echo $caseRow[0]['registered_date']; ?></p>
        <p>Time period: <?php echo date('Y-m-d' ,strtotime($dates[0])); ?> to <?php echo date('Y-m-d' ,strtotime($dates[1])); ?></p>
      </div>
      <div class="col s12 m12">
        <h5>Summary</h5><hr>
        <ul>
         <?php 
         
          $manual_injection = scan_log_by_date_range("log_access", $caseRow[0]['case_no'], "MANUAL_SQL_INJECTION",$start_date_time,$end_date_time); 
          $manual_injection = count($manual_injection); 
          if($manual_injection == 0) {
              echo '<li class="summary-items">No Manual SQL Injection</li>';
          } else {
              echo '<li class="summary-items">'.$manual_injection.' Manual SQL Injection</li>';
          }

          $auto_injection = scan_log_by_date_range("log_access", $caseRow[0]['case_no'], "AUTO_SQL_INJECTION",$start_date_time,$end_date_time); 
          $auto_injection = count($auto_injection); 
          if($auto_injection == 0) {
              echo '<li class="summary-items">No Auto SQL Injection</li>';
          } else {
              echo '<li class="summary-items">'.$auto_injection.' Auto SQL Injection</li>';
          }

          $default_shell = scan_log_by_date_range("log_access", $caseRow[0]['case_no'], "DEFAULT_SHELL",$start_date_time,$end_date_time); 
          $default_shell = count($default_shell); 
          if($default_shell == 0) {
              echo '<li class="summary-items">No Default Shell</li>';
          } else {
              echo '<li class="summary-items">'.$default_shell.' Default Shell</li>';
          }
          $xss = scan_log_by_date_range("log_access", $caseRow[0]['case_no'], "XSS_DET",$start_date_time,$end_date_time); 
          $xss = count($xss); 
          if($xss == 0) {
              echo '<li class="summary-items">No XSS</li>';
          } else {
              echo '<li class="summary-items">'.$xss.' XSS FOUND</li>';
          }
        ?>
      </ul>
      </div>
	</div>

<div style="margin: 10px;">
  <h5>Logs</h5><hr>

<?php 
if (empty($log_datas)) {
  echo "<p>No data found during this period.</p>";
}else{
  foreach ($log_datas as $log_data) { 
?>
<table>
  <tbody>
    <tr>
      <td class="head-cell">Id</td>
      <td>#<?php echo $log_data['id']; ?></td>
    </tr>
    <tr>
      <td class="head-cell">IP</td>
      <td>: <?php echo $log_data['public_ip']; ?></td>
    </tr>
    <tr>
      <td class="head-cell">Time</td>
      <td>: <?php echo date("h:i A", strtotime($log_data['date_time'])) ?></td>
    </tr>
    <tr>
      <td class="head-cell">Method</td>
      <td>: <?php echo $log_data['method']; ?></td>
    </tr>
    <tr>
      <td class="head-cell">Response</td>
      <td>: Continue</td>
    </tr>
    <tr>
      <td class="head-cell">Content</td>
      <td>: <?php echo number_format($log_data['file_bytes']); ?></td>
    </tr>
    <tr>
      <td class="head-cell">Header</td>
      <td>: <?php echo $log_data['http_header']; ?></td>
    </tr>
    <tr>
      <td class="head-cell">Reference</td>
      <td>: <?php echo $log_data['link_ref']; ?></td>
    </tr>
  </tbody>
</table>
<div><hr></div>
<?php } }?>  
</div>
  </body>
</html>