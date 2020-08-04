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
			if(strpos($line, "syslogd") || strpos($line, "saslauthd") || strpos($line, "init") || strpos($line, "last message") || strpos($line, "xinet") || strpos($line, "exiting") || strpos($line, "shutdown[") || strpos($line, "Microsoft") || strpos($line, "named")){
				continue;
			}
			$list = explode(" ", $line);
			$count = count($list);
			if($count <= 6) {
				continue;
			}

			// Date
			$date_time = $list[0].' '.$list[1].' '.$list[2];
			$date_time = date('Y-m-d H:i:s', strtotime($date_time));


			// IP
			if(strpos($list[4], 'pure-ftpd') !== false) {
				$public_ip = explode('@', $list[5]);
				$public_ip = substr($public_ip[1], 0, -1);
				$public_ip = $this->check_ip($public_ip);
			}elseif(strpos($list[4], 'named') !== false) {
				$public_ip = explode('#', $list[6]);
				$public_ip = $public_ip[0];
				$public_ip = $this->check_ip($public_ip);
			}else{
				$public_ip = "-";
			}

			// Protocol
				$protocol = substr($list[4], 0, -1);
			

			// Notification
			if(strpos($list[4], 'pure-ftpd') !== false) {
				if(preg_match_all("/\[[^\]]*\]/", $line, $notif)){
					$remove = array('[', ']');
					$noti = str_replace($remove, '', $notif[0]);
					$notification = ucwords(strtolower($noti[0]));
				}else{
					$notification = '-';
				}
			}elseif(strpos($list[4], 'PAM-hulk') !== false) {
				$notification = 'Attack';
			}else{
				$notification = '-';
			}

			// Message
			if(strpos($list[4], 'pure-ftpd') !== false) {
				$message = explode(']', $line); 
				$message = $message[1];
				if(strpos($message, '__cpanel') !== false) {
					$message = explode(' ', $message);
					$message[1] = "User ";
					$message = implode(" ", $message);
				}
			}elseif(strpos($list[4], 'PAM-hulk') !== false) {
				$message = explode(']:', $line); 
				$message = $message[1];
			}

			$case_no = $this->case_no;

			$log_sys_sql = "INSERT INTO `log_sys`( `case_no`, `public_ip`, `date_time`, `protocol`, `notification`, `message`) VALUES (:case_no, :public_ip, :date_time, :protocol, :notification, :message)";
			$conn = $this->conn();
			$log_sys = $conn->prepare($log_sys_sql);
			$log_sys->bindValue(':case_no', $case_no);
			$log_sys->bindValue(':public_ip', $public_ip);
			$log_sys->bindValue(':date_time', $date_time);
			$log_sys->bindValue(':protocol', $protocol);
			$log_sys->bindValue(':notification', $notification);
			$log_sys->bindValue(':message', $message);
			$log_sys->execute();
		}	
	}

function access($filename) {
	ini_set('memory_limit', '-1');
	
	$file = file($filename);
	$values = array();

	foreach($file as $line) {
		
		// IP
		$public_ip = preg_match('/^(\S+) /', $line, $out) ? $out[1] : 'no match';//$this->check_ip($list[0]);
		// Date
		$date_time = $date = date('Y-m-d H:i:s', strtotime(preg_match('/\[(.+)\]/', $line, $out) ? $out[1] : 'no match'));
		// Timezone
		$timezone = preg_match('/\[(.+)\]/', $line, $out) ? $out[1] : 'no match';
		// Method
		$method = preg_match('/(GET|POST|DELETE|PUT).+?/', $line, $out) ? $out[0] : 'no match';
		// HTTP Header
		$http_header = str_replace($method,"",str_replace('"', "", (preg_match('/"(.*?)"/', $line, $out) ? $out[0] : 'no match')));
		// HTTP Code
		$http_response = preg_match('/ \d+ /', $line, $out) ? $out[0] : 'no match'; 
		// File bytes
		$file_bytes = preg_match('/ \d{4,8}/', $line, $out) ? $out[0] : 'no match';
		// Reference
		$link_ref = preg_match('/"(http.+?)"/', $line, $out) ? $out[1] : 'no match';
		// Useragent
		$useragent = preg_match('/"(\w{3,}\/\d.{5,}?)"/', $line, $out) ? $out[1] : 'no match';
	  	// Browser
		$result = new WhichBrowser\Parser($useragent);

		if(!empty($result->browser->name)) {
			$browsername = $result->browser->name;
		}else{
			$browsername = '-';
		}
		$case_no = $this->case_no;
	    $country= getCountryFromIP($public_ip, " NamE ");

		$new_values_string = "('$case_no', '$public_ip', '$date_time', '$timezone', '$method', '$http_header', '$http_response', '$file_bytes', '$link_ref', '$useragent', '$browsername', '$country' ) " ;
		$values_parts[] = $new_values_string ;
		$values[] = $new_values_string ;
		
	}

	$conn = $this->conn();
	$values_part = implode(',', $values_parts) ;

	$log_access_sql = sprintf("INSERT INTO `log_access`(`case_no`, `public_ip`, `date_time`, `timezone`, `method`, `http_header`, `http_response`, `file_bytes`, `link_ref`, `useragent`, `browser`,`country`) VALUES %s", $values_part) ;

	$sql_query_result = $conn->query($log_access_sql);


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
	    	 	$scans['attack_description'] = 'Manual SQL injection on referal url';
		      	$result[] = $scans;
		    }
		
		    if(check_matches($scans['link_ref'], $auto_injection)) { 
		    	$scans['attack_type'] = 'AUTO_SQL_INJECTION';
	    	 	$scans['attack_description'] = 'Auto SQL injection on referal url';
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
	    	 	$scans['attack_description'] = 'Default shell on referal url';
		      	$result[] = $scans;
		    }
		
		    if(check_matches($scans['link_ref'], $xss)) {
		    	$scans['attack_type'] = 'XSS_DET';
	    	 	$scans['attack_description'] = 'XSS attack on referal url'; 
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

	private function isValidateDate($date,$format)
	{
	    $d = DateTime::createFromFormat($format, $date);
	    return $d && $d->format($format) === $date;
	}
}
	
?>