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
$title = "Overview";
include_once('includes/header.php');

$total_case = number_format(case_total_rows('case_details', $user['username']));
$total_access = number_format(logs_total_rows('log_access', $user['username']));
$total_sys = number_format(logs_total_rows('log_sys', $user['username']));

?>
      <div class="row"> 
        <div class="col s12 m6 l3">
          <div class="card blue lighten-1 hoverable">
            <div class="card-content center-align" style="word-wrap: break-word;">
              <a href="case_overview.php"><span class="white-text thin" style="font-size: 50px;"><?php echo $total_case; ?></span>
              <p class="white-text" style="font-size: 20px;">Cases</p></a>
            </div>
          </div>
        </div>
        <div class="col s12 m6 l3">
          <div class="card purple lighten-1 hoverable">
            <div class="card-content center-align" style="word-wrap: break-word;">
              <span class="white-text thin" style="font-size: 50px;"><?php echo $total_access; ?></span>
              <p class="white-text" style="font-size: 20px;">Access Logs</p>
            </div>
          </div>
        </div>
        <div class="col s12 m6 l3">
          <div class="card orange lighten-1 hoverable">
            <div class="card-content center-align" style="word-wrap: break-word;">
              <span class="white-text thin" style="font-size: 50px;"><?php echo $total_sys; ?></span>
              <p class="white-text" style="font-size: 20px;">System Logs</p>
            </div>
          </div>
        </div>
        <div class="col s12 m6 l3">
          <div class="card green lighten-1 hoverable">
            <div class="card-content center-align" style="word-wrap: break-word;">
              <span class="white-text thin" style="font-size: 50px;">3</span>
              <p class="white-text" style="font-size: 20px;">Users</p>
            </div>
          </div>
        </div>
      </div>
      <div class="row"> 
      <?php	
      $graph = new CountAndRow;
      $values = $graph->getRows('log_access', 'public_ip', $user['username'], 10);
      $counts = $graph->getCount('log_access', 'public_ip', $user['username'], 10);
      $browser_name = $graph->getRows('log_access', 'browser', $user['username'], 10);
      $browser_count = $graph->getCount('log_access', 'browser', $user['username'], 10);
	  $http_name = $graph->getRows('log_access', 'http_response', $user['username'], 10);
      $http_count = $graph->getCount('log_access', 'http_response', $user['username'], 10);
	  $method_name = $graph->getRows('log_access', 'method', $user['username'], 10);
      $method_count = $graph->getCount('log_access', 'method', $user['username'], 10);
	  $data_name = $graph->getRows('log_access', 'file_bytes', $user['username'], 50);
      $data_count = $graph->getCount('log_access', 'file_bytes', $user['username'], 50);
	  $protocol_name = $graph->getRows('log_sys', 'protocol', $user['username'], 10);
      $protocol_count = $graph->getCount('log_sys', 'protocol', $user['username'], 10);
	  $notification_name = $graph->getRows('log_sys', 'notification', $user['username'], 10);
      $notification_count = $graph->getCount('log_sys', 'notification', $user['username'], 10);

      ?>

  <br><br>
  <h4 class="thin grey-text text-darken-2">Access Log</h4><hr>
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
              <span class="purple-text card-title">Top User Agents</span>
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
		
		<div class="col s12 m6 l6">
          <div class="card">
            <div class="card-content ersr">
              <span class="purple-text card-title">HTTP Responses</span>
              <div class="divider"></div>
              <?php if(!empty($browser_name)): ?>
              <canvas id="response_chart" height="300px" />
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
              <span class="purple-text card-title">HTTP Methods</span>
              <div class="divider"></div>
              <?php if(!empty($browser_name)): ?>
              <canvas id="method_chart" height="300px" />
              <?php else: ?>
                <div class="center">
                  <p>
                    <h1 class="flow-text">NO DATA</h1>
                  </p>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div> </div>
		
		<div class="col s12 m6 l6">
          <div class="card">
            <div class="card-content ersr">
              <span class="purple-text card-title">Data Transferred </span>
              <div class="divider"></div>
              <?php if(!empty($browser_name)): ?>
              <canvas id="data_chart" height="100px" />
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
  <br><br>
	<h4 class="thin grey-text text-darken-2">System Log</h4><hr>
	<div class="row"> 

		<div class="col s12 m6 l6">
          <div class="card">
            <div class="card-content ersr">
              <span class="orange-text card-title">Protocols </span>
              <div class="divider"></div>
              <?php if(!empty($browser_name)): ?>
              <canvas id="protocol_chart" height="300px" />
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
              <span class="orange-text card-title">Notifications </span>
              <div class="divider"></div>
              <?php if(!empty($browser_name)): ?>
              <canvas id="notification_chart" height="300px" />
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
		   <div class="col s12 m6 l6">
          <div class="card">
            <div class="card-content ersr">
              <span class="purple-text card-title">Top Countries 
     		  </span>
              <div class="divider" ></div>
			          <div id="regions_div" style=" height: 350px;"></div>
             
            </div>
          </div>
        </div>
		    
		
		
           

    <script type="text/javascript" src="assets/js/chart.min.js?v=2.6.0"></script>
    <script type="text/javascript">  
	google.charts.load('current', {
        'packages':['geochart'],
        
        'mapsApiKey': 'AIzaSyD-9tSrke72PouQMnMX-a7eZSW0jkFMBWY'
      });
      google.charts.setOnLoadCallback(drawRegionsMap);

      function drawRegionsMap() {
        var data = google.visualization.arrayToDataTable([
         ['Country', 'Visits'],
		        <?php
                   $csel = $conn->query("SELECT country, COUNT(*) AS cnt FROM log_access GROUP BY country ORDER BY cnt");
						while ($c = $csel->fetch())
							{
								$country_name=$c[0];
								$name_count=$c[1];
								echo "['".$country_name."',",$name_count."],";
							}
				?>
     ]);

        var options = {};

        var chart = new google.visualization.GeoChart(document.getElementById('regions_div'));

        chart.draw(data, options);
      }
      $(window).resize(function(){
       drawRegionsMap();
 
});
	
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
	  
	 var ctx = document.getElementById('response_chart').getContext('2d');
      var IpsCount = [<?php
                    $county = "";
                    foreach($http_count as $count) {
                      $county .= '"'.$count.'",';
                    }
                    $county = rtrim($county, ",");
                    echo $county;
                ?>];

      var chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: [
            <?php
              $ips = "";
              foreach($http_name as $value) {
                $ips .= '"'.$value.'",';
              }
              $ips = rtrim($ips, ",");
              echo $ips;
            ?>],
            datasets: [{
                label: "count",
                data: IpsCount,
				
                backgroundColor: [ '#4DB6AC'],
            }]
          },
        options: {
          responsive: true,
          legend: {
            display: false
          }
        }
      });
	  
	   var ctx = document.getElementById('method_chart').getContext('2d');
      var IpsCount = [<?php
                    $county = "";
                    foreach($method_count as $count) {
                      $county .= '"'.$count.'",';
                    }
                    $county = rtrim($county, ",");
                    echo $county;
                ?>];

      var chart = new Chart(ctx, {
        type: 'horizontalBar',
        data: {
            labels: [
            <?php
              $ips = "";
              foreach($method_name as $value) {
                $ips .= '"'.$value.'",';
              }
              $ips = rtrim($ips, ",");
              echo $ips;
            ?>],
            datasets: [{
                label: "count",
                data: IpsCount,
				
               backgroundColor: ['#ffb74d','#e57373','#ba68c8','#7986cb','#64b5f6','#4dd0e1','#4db6ac','#81c784','#dce775', '#ffd54f'],
            }]
          },
        options: {
          responsive: true,
          legend: {
            display: false
          }
        }
      });
	  
	  
	  
	  
	     var ctx = document.getElementById('data_chart').getContext('2d');
      var IpsCount = [<?php
                    $county = "";
                    foreach($data_count as $count) {
                      $county .= '"'.$count.'",';
                    }
                    $county = rtrim($county, ",");
                    echo $county;
                ?>];

      var chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: [
            <?php
              $ips = "";
              foreach($data_name as $value) {
                $ips .= '"'.$value.'",';
              }
              $ips = rtrim($ips, ",");
              echo $ips;
            ?>],
            datasets: [{
                label: "file_bytes",
                data: IpsCount,
				 borderColor: ['#FA2226'],
				 backgroundColor: ['#2033EB'],

				 fill: false
				 
            }]
          },
        options: {
          responsive: true,
          legend: {
            display: false
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
      });
	  
	    var ctx = document.getElementById('protocol_chart').getContext('2d');
      var IpsCount = [<?php
                    $county = "";
                    foreach($protocol_count as $count) {
                      $county .= '"'.$count.'",';
                    }
                    $county = rtrim($county, ",");
                    echo $county;
                ?>];

      var chart = new Chart(ctx, {
        type: 'horizontalBar',
        data: {
            labels: [
            <?php
              $ips = "";
              foreach($protocol_name as $value) {
                $ips .= '"'.$value.'",';
              }
              $ips = rtrim($ips, ",");
              echo $ips;
            ?>],
            datasets: [{
                label: "count",
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
      });
	  
	      var ctx = document.getElementById('notification_chart').getContext('2d');
      var IpsCount = [<?php
                    $county = "";
                    foreach($notification_count as $count) {
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
              foreach($notification_name as $value) {
                $ips .= '"'.$value.'",';
              }
              $ips = rtrim($ips, ",");
              echo $ips;
            ?>],
            datasets: [{
                label: "number of notifications",
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
      });
	  
	      
	  

    </script>
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