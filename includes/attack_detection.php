<?php 

function scan_log($table_name, $case_no, $type) {
  $conn = connect_pdo();
  $scan = $conn->prepare("SELECT SQL_CALC_FOUND_ROWS * FROM `{$table_name}` WHERE case_no = :case_no");
  $scan->bindParam(':case_no', $case_no);
  $scan->execute();
  $result = array();
  $manual_injection = array("'");
  $auto_injection = array('--dbs','union','-u','--current-user','--current-db','--is-dba','--dump');
  $default_shell = array('wso.php','anon.php','c99','ZyklonShell','PhpSpy','b374k','DxShell','shell.php','r57','Rootshell');
  $bruteforce = array('Brute force');
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