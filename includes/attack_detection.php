<?php 
function scan_log($table_name, $case_no, $type) {
  $conn = connect_pdo();
  $scan = $conn->prepare("SELECT SQL_CALC_FOUND_ROWS * FROM `{$table_name}` WHERE case_no = :case_no");
  $scan->bindParam(':case_no', $case_no);
  $scan->execute();
  $result = array();
  $manual_injection = fetchAttackTags("mansqli");
  $auto_injection = fetchAttackTags("autosqli");
  $default_shell = fetchAttackTags("backdoor");
  $xss = fetchAttackTags("xss");
  $bruteforce = array('Brute Force');
  while($scans = $scan->fetch(PDO::FETCH_ASSOC)) {
    if($table_name == "log_access") {
      if($type == "MANUAL_SQL_INJECTION") {
        if(check_matches($scans['link_ref'], $manual_injection)) { 
          $result[] = $scans['link_ref'];
        }
      }elseif($type == "AUTO_SQL_INJECTION") {
        if(check_matches($scans['link_ref'], $auto_injection)) { 
          $result[] = $scans['link_ref'];
        }
      }if($type == "MANUAL_SQL_INJECTION") {
        if(check_matches($scans['http_header'], $manual_injection)) { 
          $result[] = $scans['http_header'];
        }
      }elseif($type == "AUTO_SQL_INJECTION") {
        if(check_matches($scans['http_header'], $auto_injection)) { 
          $result[] = $scans['http_header'];
        }
      }
	  elseif($type == "DEFAULT_SHELL") {
        if(check_matches($scans['link_ref'], $default_shell)) { 
          $result[] = $scans['link_ref'];
        }
      }elseif($type == "XSS_DET") {
        if(check_matches($scans['link_ref'], $xss)) { 
          $result[] = $scans['link_ref'];
        }
      }
	  if($type == "XSS_DET") {
        if(check_matches($scans['http_header'], $xss)) { 
          $result[] = $scans['http_header'];
        }
      }
    }elseif($table_name == "log_sys") {
      if($type == "BRUTE_FORCE") {
        if(check_matches($scans['raw_data'], $bruteforce)) { 
          $result[] = $scans['raw_data'];
        }
      }
    }
  }
return $result;
}
function scan_log_by_date_range($table_name, $case_no, $type,$start_date_time,$end_date_time) {
  $conn = connect_pdo();
  $scan = $conn->prepare("SELECT SQL_CALC_FOUND_ROWS * FROM `{$table_name}` WHERE case_no = :case_no AND `date_time` BETWEEN :start_date_time AND :end_date_time");
  $scan->bindParam(':case_no', $case_no);
  $scan->bindParam(':start_date_time', $start_date_time);
  $scan->bindParam(':end_date_time', $end_date_time);
  $scan->execute();
  $result = array();
  $manual_injection = fetchAttackTags("mansqli");
  $auto_injection = fetchAttackTags("autosqli");
  $default_shell = fetchAttackTags("backdoor");
  $xss = fetchAttackTags("xss");
  $bruteforce = array('Brute Force');
  while($scans = $scan->fetch(PDO::FETCH_ASSOC)) {
    if($table_name == "log_access") {
      if($type == "MANUAL_SQL_INJECTION") {
        if(check_matches($scans['link_ref'], $manual_injection)) { 
          $result[] = $scans['link_ref'];
        }
      }elseif($type == "AUTO_SQL_INJECTION") {
        if(check_matches($scans['link_ref'], $auto_injection)) { 
          $result[] = $scans['link_ref'];
        }
      }if($type == "MANUAL_SQL_INJECTION") {
        if(check_matches($scans['http_header'], $manual_injection)) { 
          $result[] = $scans['http_header'];
        }
      }elseif($type == "AUTO_SQL_INJECTION") {
        if(check_matches($scans['http_header'], $auto_injection)) { 
          $result[] = $scans['http_header'];
        }
      }
    elseif($type == "DEFAULT_SHELL") {
        if(check_matches($scans['link_ref'], $default_shell)) { 
          $result[] = $scans['link_ref'];
        }
      }elseif($type == "XSS_DET") {
        if(check_matches($scans['link_ref'], $xss)) { 
          $result[] = $scans['link_ref'];
        }
      }
    if($type == "XSS_DET") {
        if(check_matches($scans['http_header'], $xss)) { 
          $result[] = $scans['http_header'];
        }
      }
    }elseif($table_name == "log_sys") {
      if($type == "BRUTE_FORCE") {
        if(check_matches($scans['message'], $bruteforce)) { 
          $result[] = $scans['message'];
        }
      }
    }
  }
return $result;
}
?>