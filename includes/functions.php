<?php
function fetchAttackTags($type)
{
 $conning = connect_pdo();
 $scanMI = $conning->prepare("SELECT * FROM `attack_det` where tag_category = :tag_category");
  $scanMI->bindParam(':tag_category',$type );
  $scanMI->execute();
$mi=array();
 while($scans = $scanMI->fetch(PDO::FETCH_ASSOC)) {
array_push($mi,$scans['tag']);
 }
return $mi;
 print_r ($mi);
}
function deleteDir($dir) {
  if (is_dir($dir)) {
    $objects = scandir($dir);
    foreach ($objects as $object) {
      if ($object != "." && $object != "..") {
        if (filetype($dir."/".$object) == "dir") 
           rrmdir($dir."/".$object); 
        else unlink   ($dir."/".$object);
      }
    }
    reset($objects);
    rmdir($dir);
  }
}

function cleaner($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = strip_tags(trim($data));
  $data = htmlentities($data, ENT_QUOTES, 'UTF-8');
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}

function encrypt($data) { 
  return rtrim(strtr(base64_encode($data), '+/', '-_'), '='); 
}

function decrypt($data) { 
  return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT)); 
}

function total_rows($tablename) {
  $conn = connect_pdo();
  $total = $conn->prepare('SELECT COUNT(id) total FROM `'.$tablename.'`');
  $total->execute();
  $total = $total->fetch(PDO::FETCH_ASSOC);
  $total = $total['total'];
  return $total;
}

function case_total_rows($tablename ,$user) {
  $conn = connect_pdo();
  $total = $conn->prepare("SELECT COUNT(id) total FROM `case_details` WHERE `username`=:user");
  $total->bindParam(':user', $user);
  $total->execute();
  $total = $total->fetch(PDO::FETCH_ASSOC);
  $total = $total['total'];
  return $total;
}

function case_log_total_rows($log_table, $case_no) {
  $conn = connect_pdo();
  $total = $conn->prepare("SELECT COUNT(id) total FROM `{$log_table}` WHERE `case_no`=:case_no");
  $total->bindParam(':case_no', $case_no);
  $total->execute();
  $total = $total->fetch(PDO::FETCH_ASSOC);
  $total = $total['total'];
  return $total;
}

function logs_total_rows($tablename, $user) {
  $conn = connect_pdo();
  $case = $conn->prepare("SELECT `case_no` FROM `case_details` WHERE `username`=:user");
  $case->bindParam(':user', $user);
  $case->execute();
  $total_case = array();
  while ($total = $case->fetch(PDO::FETCH_ASSOC)) {
    $case_no = $total['case_no'];
    $cases = $conn->prepare("SELECT COUNT(*) TOTAL FROM `$tablename` WHERE `case_no`=$case_no");
    $cases->execute();
    $cases = $cases->fetch(PDO::FETCH_ASSOC);
    $total_case[] = $cases['TOTAL'];
  }
  $totals = array_sum($total_case);
  return $totals;
}

function check_matches($data, $arr) {
  foreach ($arr as $needle) {
    if (stripos($data, $needle) !== FALSE) {
      return true;
    }
  }
  return false;
}


function pagination($table, $get_page, $get_url_data, $limit) {

      $adjacents = 2;
      $limit = 20;
      $page = stripslashes($page);
      $page = htmlspecialchars($page);
      $page = strip_tags(trim($page));
      $page = htmlentities($page, ENT_QUOTES, 'UTF-8');
      $page = str_replace("&amp;", "&", $page);
      $page = str_replace("amp;amp;", "", $page);
      $page = str_replace("&amp;", "&", $page);
      $page = htmlspecialchars($page);
  
      if(!isset($_GET["page"])) {
        header('Location: ?show='.$get_page.'&data='.$get_url_data.'&page=1');
      }
      if(isset($page)) {
        $start = ($page - 1) * $limit;
      }else{
        $start = 0;
      }
  
      $log_datas = $conn->prepare("SELECT SQL_CALC_FOUND_ROWS * FROM `{$table}` WHERE case_no = :url_data ORDER BY `id` DESC LIMIT {$start}, {$limit}");
      $log_datas->bindParam(':url_data', $get_url_data);
      $log_datas->execute();
      $total = $conn->prepare("SELECT FOUND_ROWS() as total");
      $total->execute();
      $total_id = $total->fetch(PDO::FETCH_ASSOC);
      $id = $total_id['total'];

      if(empty($page)) $page = 1;
      $prev = $page - 1;
      $next = $page + 1;
      $lastpage = ceil($id/$limit);
      $lpm1 = $lastpage - 1;   

      $pagination = "";
      if($lastpage > 1) { 
      $pagination .= '<div class="center">';
      $pagination .= '<ul class="pagination">';

      if($page > 1) {
        $pagination.= '<li><a href="?show='.$get_page.'&data='.$get_url_data.'&page='.$prev.'"><i class="material-icons">chevron_left</i></a></li>';
      }else{
        $pagination.= '<li class="disabled"><a><i class="material-icons">chevron_left</i></a></li>';  
      }

      if($lastpage < 7 + ($adjacents * 2)) { 
        for ($counter = 1; $counter <= $lastpage; $counter++) {
          if ($counter == $page)
            $pagination.= '<li class="active blue"><a href="?show='.$get_page.'&data='.$get_url_data.'&page='.$counter.'">'.$counter.'</a></li>';
          else
            $pagination.= '<li class="waves-effect"><a href="?show='.$get_page.'&data='.$get_url_data.'&page='.$counter.'">'.$counter.'</a></li>';
        }
      }
      elseif($lastpage > 5 + ($adjacents * 2)) {
        if($page < 1 + ($adjacents * 2)) {
        for ($counter = 1; $counter < 4 + ($adjacents * 2); $counter++) {
          if ($counter == $page)
            $pagination.= '<li class="active blue"><a href="?show='.$get_page.'&data='.$get_url_data.'&page='.$counter.'">'.$counter.'</a></li>';
          else
            $pagination.= '<li class="waves-effect"><a href="?show='.$get_page.'&data='.$get_url_data.'&page='.$counter.'">'.$counter.'</a></li>';
          }
          $pagination.= "...";
            $pagination.= '<li class="waves-effect"><a href="?show='.$get_page.'&data='.$get_url_data.'&page='.$lpm1.'">'.$lpm1.'</a></li>';
            $pagination.= '<li class="waves-effect"><a href="?show='.$get_page.'&data='.$get_url_data.'&page='.$lastpage.'">'.$lastpage.'</a></li>';
        }

        elseif($lastpage - ($adjacents * 2) > $page && $page > ($adjacents * 2)) {
            $pagination.= '<li class="waves-effect"><a href="?show='.$get_page.'&data='.$get_url_data.'&page=1">1</a></li>';
            $pagination.= '<li class="waves-effect"><a href="?show='.$get_page.'&data='.$get_url_data.'&page=2">2</a></li>';
          $pagination.= "...";
          for ($counter = $page - $adjacents; $counter <= $page + $adjacents; $counter++) {
            if ($counter == $page)
            $pagination.= '<li class="active blue"><a href="?show='.$get_page.'&data='.$get_url_data.'&page='.$counter.'">'.$counter.'</a></li>';
          else
            $pagination.= '<li class="waves-effect"><a href="?show='.$get_page.'&data='.$get_url_data.'&page='.$counter.'">'.$counter.'</a></li>';
          }
          $pagination.= "...";
            $pagination.= '<li class="waves-effect"><a href="?show='.$get_page.'&data='.$get_url_data.'&page='.$lpm1.'">'.$lpm1.'</a></li>';
            $pagination.= '<li class="waves-effect"><a href="?show='.$get_page.'&data='.$get_url_data.'&page='.$lastpage.'">'.$lastpage.'</a></li>';
      }else{
            $pagination.= '<li class="waves-effect"><a href="?show='.$get_page.'&data='.$get_url_data.'&page=1">1</a></li>';
        $pagination.= '<li class="waves-effect"><a href="?show='.$get_page.'&data='.$get_url_data.'&page=2">2</a></li>';
        $pagination.= "...";
          for ($counter = $lastpage - (2 + ($adjacents * 2)); $counter <= $lastpage; $counter++) {
        if ($counter == $page)
          $pagination.= '<li class="active blue"><a href="?show='.$get_page.'&data='.$get_url_data.'&page='.$counter.'">'.$counter.'</a></li>';
        else
          $pagination.= '<li class="waves-effect"><a href="?show='.$get_page.'&data='.$get_url_data.'&page='.$counter.'">'.$counter.'</a></li>';
        }
      }
    }

      // next button
      if ($page < $counter - 1) {
        $pagination.= '<li class="waves-effect"><a href="?show='.$get_page.'&data='.$get_url_data.'&page='.$next.'"><i class="material-icons">chevron_right</i></a></li>';
      }else{
        $pagination.= '<li class="disabled"><a><i class="material-icons">chevron_right</i></a></li>';
      }

      $pagination.= '</ul>';    
      $pagination.= '</div>';

      return $pagination;
} }

function shuffle_array($list) { 
  if (!is_array($list)) return $list; 
  $keys = array_keys($list); 
  shuffle($keys); 
  $random = array(); 
  foreach ($keys as $key) { 
    $random[$key] = $list[$key]; 
  }
  return $random; 
} 

function getBaseUrl(){
  return 'http://localhost/log-analyzer/';
}

?>