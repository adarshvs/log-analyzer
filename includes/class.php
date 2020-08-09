<?php 
class Logread {

	private $case_no;

 public function __construct($case_no) {
     $this->case_no = $case_no;
 }

	public function conn() {
		return connect_pdo();
	}

	function sys($filename) {
		ini_set('memory_limit', '-1');
		$file = file($filename);
		foreach($file as $line) {
			
            
			// Date
			$date_time = preg_match('/\w{3}  ?\d{1,2} \d{1,2}:\d\d:\d\d/', $line, $out) ? $out[0] : '-';

			// IP
		    $public_ip = "-";
			

			// Process name
			$pname_sys = preg_match('/ ([^ ]+)\[/', $line, $out) ? $out[1] : '-';
			

			// Process ID
			$pid_sys = preg_match('/\[(\d+)\]/', $line, $out) ? $out[0] : '-';

			//raw data
            $raw_data = $line;

			$case_no = $this->case_no;

			$log_sys_sql = "INSERT INTO `log_sys`( `case_no`, `public_ip`, `date_time`, `process_name`, `process_id`, `raw_data`) VALUES (:case_no, :public_ip, :date_time, :pname_sys, :pid_sys, :raw_data)";
			$conn = $this->conn();
			$log_sys = $conn->prepare($log_sys_sql);
			$log_sys->bindValue(':case_no', $case_no);
			$log_sys->bindValue(':public_ip', $public_ip);
			$log_sys->bindValue(':date_time', $date_time);
			$log_sys->bindValue(':pname_sys', $pname_sys);
			$log_sys->bindValue(':pid_sys', $pid_sys);
			$log_sys->bindValue(':raw_data', $raw_data);
			$log_sys->execute();
		}	
	}


	public function access($filename) {
		ini_set('memory_limit', '-1');
		$conn = $this->conn();
		$batch_max_lines = 500;
		$lines = [];
		$file = new \SplFileObject($filename);
		$inserted = true;
		$_inserted = true;
		

		$conn->beginTransaction();
		//Multiples of max lines
		while(!$file->eof()) {
	 		$lines[] = $file->fgets();

	 		if(count($lines) >= $batch_max_lines){
				$inserted = $this->processLines($lines,$conn);
				$lines = [];
	 		}
	 	}
	 	//Balance lines
	 	if(count($lines) > 0){
			$_inserted = $this->processLines($lines,$conn);
			$lines = [];
 		}

 		if ($inserted && $_inserted) {
 			$conn->commit();
 		}else{
 			$conn->rollBack();
 		}
	
	}
	public function processLines($lines,$conn){
		$values_parts = [];
		foreach($lines as $line) {
			$values_parts[] =  $this->accessLogParser($line);
		}
		$values_part = implode(',', $values_parts) ;
		$inserted = $this->insertIntoAccessLog($values_part,$conn);
		

		return $inserted;
	}
	
	public function insertIntoAccessLog($values_part, $conn){
		$log_access_sql = sprintf("INSERT INTO `log_access`(`case_no`, `public_ip`, `date_time`, `timezone`, `method`, `http_header`, `http_response`, `file_bytes`, `link_ref`, `useragent`, `browser`,`country`,`raw_data`) VALUES %s", $values_part) ;
			$sql_query_result = $conn->query($log_access_sql);
			return $sql_query_result;
	}

	public function accessLogParser($line){
		// IP
		$public_ip = preg_match('/^(\S+) /', $line, $out) ? $out[1] : '-';//$this->check_ip($list[0]);
		// Date
		$date_time = $date = date('Y-m-d H:i:s', strtotime(preg_match('/\[(.+)\]/', $line, $out) ? $out[1] : '-'));
		// Timezone
		$timezone = preg_match('/\[(.+)\]/', $line, $out) ? $out[1] : '-';
		// Method
		$method = preg_match('/(GET|POST|DELETE|PUT).+?/', $line, $out) ? $out[0] : '-';
		// HTTP Header
		$http_header = str_replace($method,"",str_replace('"', "", (preg_match('/"(.*?)"/', $line, $out) ? $out[0] : '-')));
		// HTTP Code
		preg_match_all('/ (\d{3,8})/', $line, $rdt) ;
		$http_codes = array('100', '101', '102', '103', '200', '201', '202', '203', '204', '205', '206', '207', '208', '226', '300', '301', '302', '303', '304', '305', '306', '307', '308', '400', '401', '402', '403', '404', '405', '406', '407', '408', '409', '410', '411', '412', '413', '414', '415', '416', '417', '421', '422', '423', '424', '425', '426', '427', '428', '429', '430', '431', '451', '500', '501', '502', '503', '504', '505', '506', '507', '508', '509', '510', '511');
		$result_byte  = $rdt[1];
		$http_response = "N/A";
		// File bytes
		$file_bytes = "0";
		foreach($result_byte as $res){
			if(in_array($res,$http_codes)){
				$http_response = $res;
			}
			if(!in_array($res,$http_codes)){
				$file_bytes = $res;
			
			}
		}
		// Reference
		$link_ref = preg_match('/"(http.+?)"/', $line, $out) ? $out[1] : '-';
		// Useragent
		$useragent = preg_match('/"(\w{3,}\/\d.{5,}?)"/', $line, $out) ? $out[1] : '-';
		//raw data line
	  	$raw_data = $line;
	  	// Browser
		$result = new WhichBrowser\Parser($useragent);

		if(!empty($result->browser->name)) {
			$browsername = $result->browser->name;
		}else{
			$browsername = '-';
		}
		$case_no = $this->case_no;
	    $country= getCountryFromIP($public_ip, " NamE ");

		$new_values_string = "('$case_no', '$public_ip', '$date_time', '$timezone', '$method', '$http_header', '$http_response', '$file_bytes', '$link_ref', '$useragent', '$browsername', '$country', '$raw_data' ) " ;

		return $new_values_string;
	}

	public function check_ip($ip) {
		if(filter_var($ip, FILTER_VALIDATE_IP)) {
			return $ip;
		}else{
			$ip = '- | ';
			return $ip;
		}
	}

}

class CountAndRow {

	function getRows($tablename, $columnname, $user, $limit) {
		$conn = connect_pdo();
		$rows = $conn->prepare("SELECT `case_no` FROM `case_details` WHERE `username`=:user");
		$rows->bindParam(':user', $user);
		$rows->execute();
		$unions = array();
		while ($distinct = $rows->fetch(PDO::FETCH_ASSOC)) {
			$case_no = $distinct['case_no'];
			$unions[] = "SELECT ".$columnname.", COUNT(".$columnname.") AS county FROM `$tablename` WHERE `case_no`=$case_no GROUP BY ".$columnname;
		}
		$sql = "SELECT DISTINCT ".$columnname.", county FROM (";
		$command = "";
		foreach ($unions as $union) {
			$command .= $union. ' UNION ';
		}
		$sql .= rtrim($command, ' UNION ');
		$sql .= ") t ORDER BY county DESC LIMIT ".$limit;
		$getvalues = $conn->prepare($sql);
		$getvalues->execute();
		$values = array();
		while ($getvalue = $getvalues->fetch(PDO::FETCH_ASSOC)) {
			if($getvalue[$columnname] != "-"){
				$values[] = $getvalue[$columnname];	
			}
		}
		return $values;
	}

	function getCount($tablename, $columnname, $user, $limit) {
		$conn = connect_pdo();
		$rows = $conn->prepare("SELECT `case_no` FROM `case_details` WHERE `username`=:user");
		$rows->bindParam(':user', $user);
		$rows->execute();
		$unions = array();
		while ($distinct = $rows->fetch(PDO::FETCH_ASSOC)) {
			$case_no = $distinct['case_no'];
			$unions[] = "SELECT ".$columnname.", COUNT(".$columnname.") AS county FROM `$tablename` WHERE `case_no`=$case_no GROUP BY ".$columnname;
		}
		$sql = "SELECT DISTINCT ".$columnname.", county FROM (";
		$command = "";
		foreach ($unions as $union) {
			$command .= $union. ' UNION ';
		}
		$sql .= rtrim($command, ' UNION ');
		$sql .= ") t ORDER BY county DESC LIMIT ".$limit;
		$getcounts = $conn->prepare($sql);
		$getcounts->execute();
		$counters = array();
		while ($getcount = $getcounts->fetch(PDO::FETCH_ASSOC)) {
			if($getcount[$columnname] != "-"){
				$counters[] = $getcount['county'];
			}
		}
		return $counters;
	}

}



class Page {
	
	private $table; 
	private $page_data=1; 
	private $show_data; 
	private $url_data; 
	private $limit;
	private $data;

	public function __construct($table, $page_data, $show_data, $url_get, $limit) {
		$this->table = $table;
		$this->page_data = $page_data;
		$this->show_data = $show_data;
		$this->url_data = $url_get;
		$this->limit = $limit;
	}

	public function pageData() {

			$adjacents = 2;
			$limit = $this->limit;
			$tablename = $this->table;
			$page = $this->page_data;
			$url_data_decrypt = decrypt($this->url_data);
			$page = stripslashes($page);
			$page = htmlspecialchars($page);
			$page = strip_tags(trim($page));
			$page = htmlentities($page, ENT_QUOTES, 'UTF-8');
			$page = str_replace("&amp;", "&", $page);
			$page = str_replace("amp;amp;", "", $page);
			$page = str_replace("&amp;", "&", $page);
			$page = htmlspecialchars($page);
	
			if(!empty($page))
				$start = ($page - 1) * $limit;
			else 
				$start = 0;
			$conn = connect_pdo();
			$log_datas = $conn->prepare("SELECT SQL_CALC_FOUND_ROWS * FROM `{$tablename}` WHERE case_no = :url_data ORDER BY `id` DESC LIMIT {$start}, {$limit}");
			$log_datas->bindParam(':url_data', $url_data_decrypt);
			$log_datas->execute();
			$results = array();
			while($log_data = $log_datas->fetch(PDO::FETCH_ASSOC)){
				$results[] = $log_data;
			}
			$total = $conn->prepare("SELECT FOUND_ROWS() as total");
			$total->execute();
			$total_id = $total->fetch(PDO::FETCH_ASSOC);
			$id = $total_id['total'];

			if(empty($page)) $page = 1;
			$prev = $page - 1;
			$next = $page + 1;
			$lastpage = ceil($id / $limit);
			$lpm1 = $lastpage - 1;   

			$pagination = "";
			if($lastpage > 1) { 
			$pagination .= '<div class="center">';
			$pagination .= '<ul class="pagination">';

			if($page > 1) 
				$pagination.= '<li><a href="?show='.$this->show_data.'&data='.$this->url_data.'&page='.$prev.'"><i class="material-icons">chevron_left</i></a></li>';
			else
				$pagination.= '<li class="disabled"><a><i class="material-icons">chevron_left</i></a></li>';  

			if($lastpage < 7 + ($adjacents * 2)) { 
			  for ($counter = 1; $counter <= $lastpage; $counter++) {
			    if ($counter == $page)
			      $pagination.= '<li class="active blue"><a href="?show='.$this->show_data.'&data='.$this->url_data.'&page='.$counter.'">'.$counter.'</a></li>';
			    else
			      $pagination.= '<li class="waves-effect"><a href="?show='.$this->show_data.'&data='.$this->url_data.'&page='.$counter.'">'.$counter.'</a></li>';
			  }
			}
			elseif($lastpage > 5 + ($adjacents * 2)) {
			  if($page < 1 + ($adjacents * 2)) {
				for ($counter = 1; $counter < 4 + ($adjacents * 2); $counter++) {
					if ($counter == $page)
			      $pagination.= '<li class="active blue"><a href="?show='.$this->show_data.'&data='.$this->url_data.'&page='.$counter.'">'.$counter.'</a></li>';
			    else
			      $pagination.= '<li class="waves-effect"><a href="?show='.$this->show_data.'&data='.$this->url_data.'&page='.$counter.'">'.$counter.'</a></li>';
			    }
			    $pagination.= "...";
			      $pagination.= '<li class="waves-effect"><a href="?show='.$this->show_data.'&data='.$this->url_data.'&page='.$lpm1.'">'.$lpm1.'</a></li>';
			      $pagination.= '<li class="waves-effect"><a href="?show='.$this->show_data.'&data='.$this->url_data.'&page='.$lastpage.'">'.$lastpage.'</a></li>';
			  }

			  elseif($lastpage - ($adjacents * 2) > $page && $page > ($adjacents * 2)) {
			      $pagination.= '<li class="waves-effect"><a href="?show='.$this->show_data.'&data='.$this->url_data.'&page=1">1</a></li>';
			      $pagination.= '<li class="waves-effect"><a href="?show='.$this->show_data.'&data='.$this->url_data.'&page=2">2</a></li>';
			    $pagination.= "...";
			    for ($counter = $page - $adjacents; $counter <= $page + $adjacents; $counter++) {
			      if ($counter == $page)
			      $pagination.= '<li class="active blue"><a href="?show='.$this->show_data.'&data='.$this->url_data.'&page='.$counter.'">'.$counter.'</a></li>';
			    else
			      $pagination.= '<li class="waves-effect"><a href="?show='.$this->show_data.'&data='.$this->url_data.'&page='.$counter.'">'.$counter.'</a></li>';
			    }
			    $pagination.= "...";
			      $pagination.= '<li class="waves-effect"><a href="?show='.$this->show_data.'&data='.$this->url_data.'&page='.$lpm1.'">'.$lpm1.'</a></li>';
			      $pagination.= '<li class="waves-effect"><a href="?show='.$this->show_data.'&data='.$this->url_data.'&page='.$lastpage.'">'.$lastpage.'</a></li>';
			}else{
			      $pagination.= '<li class="waves-effect"><a href="?show='.$this->show_data.'&data='.$this->url_data.'&page=1">1</a></li>';
				$pagination.= '<li class="waves-effect"><a href="?show='.$this->show_data.'&data='.$this->url_data.'&page=2">2</a></li>';
				$pagination.= "...";
			    for ($counter = $lastpage - (2 + ($adjacents * 2)); $counter <= $lastpage; $counter++) {
				if ($counter == $page)
					$pagination.= '<li class="active blue"><a href="?show='.$this->show_data.'&data='.$this->url_data.'&page='.$counter.'">'.$counter.'</a></li>';
				else
					$pagination.= '<li class="waves-effect"><a href="?show='.$this->show_data.'&data='.$this->url_data.'&page='.$counter.'">'.$counter.'</a></li>';
				}
			}
		}

			// next button
			if ($page < $counter - 1) 
				$pagination.= '<li class="waves-effect"><a href="?show='.$this->show_data.'&data='.$this->url_data.'&page='.$next.'"><i class="material-icons">chevron_right</i></a></li>';
			else
				$pagination.= '<li class="disabled"><a><i class="material-icons">chevron_right</i></a></li>';

			$pagination.= '</ul>';    
			$pagination.= '</div>';    
		}
		$this->data = $pagination;
	return $results;
	}



	public function pagination() {
		$pagination = $this->data;
		return $pagination;
	}
}

/**
 * For Individual Access log and Sys log
 */
class Model 
{
	
	public function getAllAttacksbyDateForAccessLog($date,$case_no){
		//Check if the date format is valid
		$is_valid = $this->isValidateDate($date,'Y-m-d');
		if(!$is_valid){
			return false;
		}
		
		$start_date_time = date('Y-m-d H:i:s', strtotime($date.' 00:00:00'));
		$end_date_time = date('Y-m-d H:i:s', strtotime($date.' 24:00:00'));

		$conn = connect_pdo();
		$log_datas = $conn->prepare("SELECT * FROM `log_access` WHERE `case_no` = :case_no AND `date_time` BETWEEN :start_date_time AND :end_date_time ORDER BY `date_time`");
		
		$log_datas->bindParam(':case_no', $case_no);
		$log_datas->bindParam(':start_date_time', $start_date_time);
		$log_datas->bindParam(':end_date_time', $end_date_time);
		$log_datas->execute();
		//Check if any row was attacked
		$result = array();
		$manual_injection = fetchAttackTags("mansqli");
		$auto_injection = fetchAttackTags("autosqli");
		$default_shell = fetchAttackTags("backdoor");
		$xss = fetchAttackTags("xss");
		while($scans = $log_datas->fetch(PDO::FETCH_ASSOC)) {
			
		    if(check_matches($scans['link_ref'], $manual_injection)) { 
	    	 	$scans['attack_type'] = 'MANUAL_SQL_INJECTION';
	    	 	$scans['attack_description'] = 'Manual SQL injection on Referrer URL';
		      	$result[] = $scans;
		    }
		
		    if(check_matches($scans['link_ref'], $auto_injection)) { 
		    	$scans['attack_type'] = 'AUTO_SQL_INJECTION';
	    	 	$scans['attack_description'] = 'Auto SQL injection on Referrer URL';
		      	$result[] = $scans;
		    }
		
		    if(check_matches($scans['http_header'], $manual_injection)) { 
		    	$scans['attack_type'] = 'MANUAL_SQL_INJECTION';
	    	 	$scans['attack_description'] = 'Manual SQL injection on header';
		      	$result[] = $scans;
		    }
		
		    if(check_matches($scans['http_header'], $auto_injection)) { 
		    	$scans['attack_type'] = 'AUTO_SQL_INJECTION';
	    	 	$scans['attack_description'] = 'Auto SQL injection on header';
		      	$result[] = $scans;
		    }
		
		    if(check_matches($scans['link_ref'], $default_shell)) { 
		    	$scans['attack_type'] = 'DEFAULT_SHELL';
	    	 	$scans['attack_description'] = 'Default shell on Referrer URL';
		      	$result[] = $scans;
		    }
		
		    if(check_matches($scans['link_ref'], $xss)) {
		    	$scans['attack_type'] = 'XSS_DET';
	    	 	$scans['attack_description'] = 'XSS attack on Referrer URL'; 
		      	$result[] = $scans;
		    }
		
		    if(check_matches($scans['http_header'], $xss)) { 
		    	$scans['attack_type'] = 'XSS_DET';
	    	 	$scans['attack_description'] = 'XSS attack on header';
		      	$result[] = $scans;
		    }
			
		}

		return $result;

	}

	public function getAllAttacksForCalendar($case_no){

		$conn = connect_pdo();
		$log_datas = $conn->prepare("SELECT * FROM `log_access` WHERE `case_no` = :case_no ORDER BY `date_time`");
		
		$log_datas->bindParam(':case_no', $case_no);
		$log_datas->execute();
		//Check if any row was attacked
		$result = array();
		$manual_injection = fetchAttackTags("mansqli");
		$auto_injection = fetchAttackTags("autosqli");
		$default_shell = fetchAttackTags("backdoor");
		$xss = fetchAttackTags("xss");
		while($scans = $log_datas->fetch(PDO::FETCH_ASSOC)) {
			
		    if(check_matches($scans['link_ref'], $manual_injection)) { 

	    	 	$attack_date_time = date('Y-m-d H:i:s', strtotime($scans['date_time']));
	    	 	$attack_date = date('Y-m-d', strtotime($scans['date_time']));
		      	$result[] = [

		      		'id'	=> $scans['id'],
		      		'title'	=> 'Manual SQL injection on referal url',
		      		'start' => $attack_date_time,
		      		'color' => 'red',
		      		'url' => getBaseUrl().'analyze.php?show=access_log&data='.encrypt($case_no).'&attack_date='.$attack_date.'&href=#attackDate',
		      		'description' => 'Manual SQL injection on referal url'
		      	];
		    }
		
		    if(check_matches($scans['link_ref'], $auto_injection)) { 
		    
		      	$attack_date_time = date('Y-m-d H:i:s', strtotime($scans['date_time']));
	    	 	$attack_date = date('Y-m-d', strtotime($scans['date_time']));
		      	$result[] = [
		      		
		      		'id'	=> $scans['id'],
		      		'title'	=> 'Auto SQL injection on referal url',
		      		'start' => $attack_date_time,
		      		'color' => 'red',
		      		'url' => getBaseUrl().'analyze.php?show=access_log&data='.encrypt($case_no).'&attack_date='.$attack_date.'&href=#attackDate',
		      		'description' => 'Auto SQL injection on referal url'
		      	];
		    }
		
		    if(check_matches($scans['http_header'], $manual_injection)) { 
		    	
		      	$attack_date_time = date('Y-m-d H:i:s', strtotime($scans['date_time']));
	    	 	$attack_date = date('Y-m-d', strtotime($scans['date_time']));
		      	$result[] = [
		      		
		      		'id'	=> $scans['id'],
		      		'title'	=> 'Manual SQL injection on header',
		      		'start' => $attack_date_time,
		      		'color' => 'red',
		      		'url' => getBaseUrl().'analyze.php?show=access_log&data='.encrypt($case_no).'&attack_date='.$attack_date.'&href=#attackDate',
		      		'description' => 'Manual SQL injection on header'
		      	];
		    }
		
		    if(check_matches($scans['http_header'], $auto_injection)) { 
		    	
		      	$attack_date_time = date('Y-m-d H:i:s', strtotime($scans['date_time']));
	    	 	$attack_date = date('Y-m-d', strtotime($scans['date_time']));
		      	$result[] = [
		      		
		      		'id'	=> $scans['id'],
		      		'title'	=> 'Auto SQL injection on header',
		      		'start' => $attack_date_time,
		      		'color' => 'red',
		      		'url' => getBaseUrl().'analyze.php?show=access_log&data='.encrypt($case_no).'&attack_date='.$attack_date.'&href=#attackDate',
		      		'description' => 'Auto SQL injection on header'
		      	];
		    }
		
		    if(check_matches($scans['link_ref'], $default_shell)) { 
		   
		      	$attack_date_time = date('Y-m-d H:i:s', strtotime($scans['date_time']));
	    	 	$attack_date = date('Y-m-d', strtotime($scans['date_time']));
		      	$result[] = [
		      		
		      		'id'	=> $scans['id'],
		      		'title'	=> 'Default shell on referal url',
		      		'start' => $attack_date_time,
		      		'color' => 'red',
		      		'url' => getBaseUrl().'analyze.php?show=access_log&data='.encrypt($case_no).'&attack_date='.$attack_date.'&href=#attackDate',
		      		'description' => 'Default shell on referal url'
		      	];
		    }
		
		    if(check_matches($scans['link_ref'], $xss)) {
		    	
		      	$attack_date_time = date('Y-m-d H:i:s', strtotime($scans['date_time']));
	    	 	$attack_date = date('Y-m-d', strtotime($scans['date_time']));
		      	$result[] = [
		      		
		      		'id'	=> $scans['id'],
		      		'title'	=> 'XSS attack on referal url',
		      		'start' => $attack_date_time,
		      		'color' => 'red',
		      		'url' => getBaseUrl().'analyze.php?show=access_log&data='.encrypt($case_no).'&attack_date='.$attack_date.'&href=#attackDate',
		      		'description' => 'XSS attack on referal url'
		      	];
		    }
		
		    if(check_matches($scans['http_header'], $xss)) { 
		    	
		      	$attack_date_time = date('Y-m-d H:i:s', strtotime($scans['date_time']));
	    	 	$attack_date = date('Y-m-d', strtotime($scans['date_time']));
		      	$result[] = [
		      		
		      		'id'	=> $scans['id'],
		      		'title'	=> 'XSS attack on header',
		      		'start' => $attack_date_time,
		      		'color' => 'red',
		      		'url' => getBaseUrl().'analyze.php?show=access_log&data='.encrypt($case_no).'&attack_date='.$attack_date.'&href=#attackDate',
		      		'description' => 'XSS attack on header'
		      	];
		    }
			
		}

		return $result;

	}

	public function getAllAccessLogByIp($public_ip, $case_no,$page,$limit){
		$conn = connect_pdo();
		if(!empty($page)){
				$start = ($page - 1) * $limit;
		}else{ 
			$start = 0;
		}
		$log_datas = $conn->prepare("SELECT * FROM `log_access` WHERE `case_no` = :case_no AND `public_ip` = :public_ip ORDER BY `id` DESC LIMIT {$start}, {$limit}");
		
		$log_datas->bindParam(':case_no', $case_no);
		$log_datas->bindParam(':public_ip', $public_ip);
		$log_datas->execute();
		return $log_datas->fetchAll();
	}

	public function getAllSysLogByIp($public_ip, $case_no,$page,$limit){
		$conn = connect_pdo();
		if(!empty($page)){
				$start = ($page - 1) * $limit;
		}else{ 
			$start = 0;
		}
		$log_datas = $conn->prepare("SELECT * FROM `log_sys` WHERE `case_no` = :case_no AND `public_ip` = :public_ip ORDER BY `id` DESC LIMIT {$start}, {$limit}");
		
		$log_datas->bindParam(':case_no', $case_no);
		$log_datas->bindParam(':public_ip', $public_ip);
		$log_datas->execute();
		return $log_datas->fetchAll();
	}
	public function getAllAccessLogByDate($date_time, $case_no,$page,$limit){
		$conn = connect_pdo();
		$date_log = date('Y-m-d', strtotime($date_time));
		
		if(!empty($page)){
				$start = ($page - 1) * $limit;
		}else{ 
			$start = 0;
		}
		$log_datas = $conn->prepare("SELECT * FROM `log_access` WHERE `case_no` = :case_no AND DATE(date_time) = :date_log ORDER BY `id` DESC LIMIT {$start}, {$limit}");
		
		$log_datas->bindParam(':case_no', $case_no);
		$log_datas->bindParam(':date_log', $date_log);
		$log_datas->execute();
		return $log_datas->fetchAll();
	}

	public function getAllSysLogByDate($date_time, $case_no,$page,$limit){
		$conn = connect_pdo();
		$date_log = date('Y-m-d', strtotime($date_time));
		if(!empty($page)){
				$start = ($page - 1) * $limit;
		}else{ 
			$start = 0;
		}
		$log_datas = $conn->prepare("SELECT * FROM `log_sys` WHERE `case_no` = :case_no AND DATE(date_time) = :date_log ORDER BY `id` DESC LIMIT {$start}, {$limit}");
		
		$log_datas->bindParam(':case_no', $case_no);
		$log_datas->bindParam(':date_log', $date_log);
		$log_datas->execute();
		return $log_datas->fetchAll();
	}
	public function getAllAccessLogByDateForCalendar($date, $case_no,$page,$limit){
		$conn = connect_pdo();
		$date_log = date('Y-m-d', strtotime($date_time));
		
		if(!empty($page)){
				$start = ($page - 1) * $limit;
		}else{ 
			$start = 0;
		}
		$log_datas = $conn->prepare("SELECT * FROM `log_access` WHERE `case_no` = :case_no AND DATE(date_time) = :date_log ORDER BY `id` DESC LIMIT {$start}, {$limit}");
		
		$log_datas->bindParam(':case_no', $case_no);
		$log_datas->bindParam(':date_log', $date_log);
		$log_datas->execute();
		return $log_datas->fetchAll();
	}

	private function isValidateDate($date,$format)
	{
	    $d = DateTime::createFromFormat($format, $date);
	    return $d && $d->format($format) === $date;
	}
}

/* AccessLogModel*/

class AccessLogModel {
	
	private $page_data=1; 
	public $data;

	public function pageData( $table,$page_data, $show_data, $url_get, $limit,$date) {

			$start_date_time = date('Y-m-d H:i:s', strtotime($date.' 00:00:00'));
			$end_date_time = date('Y-m-d H:i:s', strtotime($date.' 24:00:00'));
			$adjacents = 2;
			$limit = $limit;
			$tablename = $table;
			$page = $page_data;
			$url_data = decrypt($url_get);
			$page = stripslashes($page);
			$page = htmlspecialchars($page);
			$page = strip_tags(trim($page));
			$page = htmlentities($page, ENT_QUOTES, 'UTF-8');
			$page = str_replace("&amp;", "&", $page);
			$page = str_replace("amp;amp;", "", $page);
			$page = str_replace("&amp;", "&", $page);
			$page = htmlspecialchars($page);
	
			if(!empty($page))
				$start = ($page - 1) * $limit;
			else 
				$start = 0;
			$conn = connect_pdo();
			$log_datas = $conn->prepare("SELECT SQL_CALC_FOUND_ROWS * FROM `{$tablename}` WHERE case_no = :url_data AND `date_time` BETWEEN :start_date_time AND :end_date_time ORDER BY `id` ASC LIMIT {$start}, {$limit}");
			$log_datas->bindParam(':url_data', $url_data);
			$log_datas->bindParam(':start_date_time', $start_date_time);
			$log_datas->bindParam(':end_date_time', $end_date_time);
			$log_datas->execute();
			

			$results = array();
			while($log_data = $log_datas->fetch(PDO::FETCH_ASSOC)){
				$results[] = $log_data;
			}
			$total = $conn->prepare("SELECT FOUND_ROWS() as total");
			$total->execute();
			$total_id = $total->fetch(PDO::FETCH_ASSOC);
			$id = $total_id['total'];

			if(empty($page)) $page = 1;
			$prev = $page - 1;
			$next = $page + 1;
			$lastpage = ceil($id / $limit);
			$lpm1 = $lastpage - 1;   

			$pagination = "";
			if($lastpage > 1) { 
			$pagination .= '<div class="center">';
			$pagination .= '<ul class="pagination">';

			if($page > 1) 
				$pagination.= '<li><a href="?show='.$show_data.'&data='.$url_get.'&date='.$date.'&page='.$prev.'&href=#accessLogTable"><i class="material-icons">chevron_left</i></a></li>';
			else
				$pagination.= '<li class="disabled"><a><i class="material-icons">chevron_left</i></a></li>';  

			if($lastpage < 7 + ($adjacents * 2)) { 
			  for ($counter = 1; $counter <= $lastpage; $counter++) {
			    if ($counter == $page)
			      $pagination.= '<li class="active blue"><a href="?show='.$show_data.'&data='.$url_get.'&date='.$date.'&page='.$counter.'&href=#accessLogTable">'.$counter.'</a></li>';
			    else
			      $pagination.= '<li class="waves-effect"><a href="?show='.$show_data.'&data='.$url_get.'&date='.$date.'&page='.$counter.'&href=#accessLogTable">'.$counter.'</a></li>';
			  }
			}
			elseif($lastpage > 5 + ($adjacents * 2)) {
			  if($page < 1 + ($adjacents * 2)) {
				for ($counter = 1; $counter < 4 + ($adjacents * 2); $counter++) {
					if ($counter == $page)
			      $pagination.= '<li class="active blue"><a href="?show='.$show_data.'&data='.$url_get.'&date='.$date.'&page='.$counter.'&href=#accessLogTable">'.$counter.'</a></li>';
			    else
			      $pagination.= '<li class="waves-effect"><a href="?show='.$show_data.'&data='.$url_get.'&date='.$date.'&page='.$counter.'&href=#accessLogTable">'.$counter.'</a></li>';
			    }
			    $pagination.= "...";
			      $pagination.= '<li class="waves-effect"><a href="?show='.$show_data.'&data='.$url_get.'&date='.$date.'&page='.$lpm1.'&href=#accessLogTable">'.$lpm1.'</a></li>';
			      $pagination.= '<li class="waves-effect"><a href="?show='.$show_data.'&data='.$url_get.'&date='.$date.'&page='.$lastpage.'&href=#accessLogTable">'.$lastpage.'</a></li>';
			  }

			  elseif($lastpage - ($adjacents * 2) > $page && $page > ($adjacents * 2)) {
			      $pagination.= '<li class="waves-effect"><a href="?show='.$show_data.'&data='.$url_get.'&date='.$date.'&page=1&href=#accessLogTable">1</a></li>';
			      $pagination.= '<li class="waves-effect"><a href="?show='.$show_data.'&data='.$url_get.'&date='.$date.'&page=2&href=#accessLogTable">2</a></li>';
			    $pagination.= "...";
			    for ($counter = $page - $adjacents; $counter <= $page + $adjacents; $counter++) {
			      if ($counter == $page)
			      $pagination.= '<li class="active blue"><a href="?show='.$show_data.'&data='.$url_get.'&date='.$date.'&page='.$counter.'&href=#accessLogTable">'.$counter.'</a></li>';
			    else
			      $pagination.= '<li class="waves-effect"><a href="?show='.$show_data.'&data='.$url_get.'&date='.$date.'&page='.$counter.'&href=#accessLogTable">'.$counter.'</a></li>';
			    }
			    $pagination.= "...";
			      $pagination.= '<li class="waves-effect"><a href="?show='.$show_data.'&data='.$url_get.'&date='.$date.'&page='.$lpm1.'&href=#accessLogTable">'.$lpm1.'</a></li>';
			      $pagination.= '<li class="waves-effect"><a href="?show='.$show_data.'&data='.$url_get.'&date='.$date.'&page='.$lastpage.'&href=#accessLogTable">'.$lastpage.'</a></li>';
			}else{
			      $pagination.= '<li class="waves-effect"><a href="?show='.$show_data.'&data='.$url_get.'&date='.$date.'&page=1&href=#accessLogTable">1</a></li>';
				$pagination.= '<li class="waves-effect"><a href="?show='.$show_data.'&data='.$url_get.'&date='.$date.'&page=2&href=#accessLogTable">2</a></li>';
				$pagination.= "...";
			    for ($counter = $lastpage - (2 + ($adjacents * 2)); $counter <= $lastpage; $counter++) {
				if ($counter == $page)
					$pagination.= '<li class="active blue"><a href="?show='.$show_data.'&data='.$url_get.'&date='.$date.'&page='.$counter.'&href=#accessLogTable">'.$counter.'</a></li>';
				else
					$pagination.= '<li class="waves-effect"><a href="?show='.$show_data.'&data='.$url_get.'&date='.$date.'&page='.$counter.'&href=#accessLogTable">'.$counter.'</a></li>';
				}
			}
		}

			// next button
			if ($page < $counter - 1) 
				$pagination.= '<li class="waves-effect"><a href="?show='.$show_data.'&data='.$url_get.'&page='.$next.'"><i class="material-icons">chevron_right</i></a></li>';
			else
				$pagination.= '<li class="disabled"><a><i class="material-icons">chevron_right</i></a></li>';

			$pagination.= '</ul>';    
			$pagination.= '</div>';    
		}
		$this->data = $pagination;
	return $results;
	}



	public function pagination() {
		$pagination = $this->data;
		return $pagination;
	}
	public function getAccessLogByDateRange($case_no,$start_date_time,$end_date_time){
		$conn = connect_pdo();
		$log_datas = $conn->prepare("SELECT * FROM `log_access` WHERE case_no = :case_no AND `date_time` BETWEEN :start_date_time AND :end_date_time ORDER BY `id` ASC");
		$log_datas->bindParam(':case_no', $case_no);
		$log_datas->bindParam(':start_date_time', $start_date_time);
		$log_datas->bindParam(':end_date_time', $end_date_time);
		$log_datas->execute();
		return $log_datas->fetchAll();
	}
}
/* CasedetailModel*/

class CaseDetailLogModel {
	
	public function getCaseByNo($case_no){
		$conn = connect_pdo();
	
		$log_datas = $conn->prepare("SELECT * FROM `case_details` WHERE `case_no` = :case_no LIMIT 1");
		
		$log_datas->bindParam(':case_no', $case_no);
		$log_datas->execute();
		return $log_datas->fetchAll();
	}
	
}
	
?>