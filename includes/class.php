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
			//$line;

			//echo strlen($line); continue ;

      $list = explode(" ", $line);
      $count = count($list);

      // IP
      $public_ip = $this->check_ip($list[0]);

//echo strlen($line); continue ; good

      // Date
      $date_time = $list[3];
      $date_time = substr($date_time, 1, 11)." ".substr($date_time, 13, 9);
      $date_time = str_replace('/', '-', $date_time);
      $date_time = date('Y-m-d H:i:s', strtotime($date_time));

      // Timezone
      $timezone = $list[4];
      $timezone = substr($timezone, 0, 5);

      // Method
      $method = $list[5];
      $method = substr($method, 1);

      // HTTP Header
      $http_header = $list[6].' '.$list[7];
      $http_header = substr($http_header, 0, -1);

      // HTTP Code
      $http_response = $list[8]; 

      // File bytes
      $file_bytes = $list[9];
      if($file_bytes == '-'){
        $file_bytes = 0;
      }

      // Reference
      $link_ref = substr($list[10], 1, -1);

      // Useragent
      $useragent = $list[11];
      $co = count($list) - 1;
      $userage = array();
      for ($i = 11; $i <= $co; $i) {
          $userage[] = $list[$i++];
      }
      $useragent = implode(' ', $userage);
      $useragent = substr($useragent, 1, -2);
	$useragent = str_replace('"','', $useragent);

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
			$this->url_data;
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
?>