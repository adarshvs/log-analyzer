
<?php
class PageUserActivityLogs {
	
	private $page_data=1; 
	private $limit;
	private $table;
	private $data_log;

	public function __construct( $table,$page_data, $limit) {
		$this->table = $table;
		$this->page_data = $page_data;
		$this->limit = $limit;
		$this->table = $this->table;
	}

	public function pageData() {

			$adjacents = 2;
			$limit = $this->limit;
			$tablename = $this->table;
			$page = $this->page_data;
			
			
	
			if(!empty($page))
				$start = ($page - 1) * $limit;
			else 
				$start = 0;
			$conn = connect_pdo();
			$log_datas = $conn->prepare("SELECT SQL_CALC_FOUND_ROWS * FROM `{$tablename}`  ORDER BY `id` DESC LIMIT {$start}, {$limit}");
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
				$pagination.= '<li><a href="?page='.$prev.'"><i class="material-icons">chevron_left</i></a></li>';
			else
				$pagination.= '<li class="disabled"><a><i class="material-icons">chevron_left</i></a></li>';  

			if($lastpage < 7 + ($adjacents * 2)) { 
			  for ($counter = 1; $counter <= $lastpage; $counter++) {
			    if ($counter == $page)
			      $pagination.= '<li class="active blue"><a href="?page='.$counter.'">'.$counter.'</a></li>';
			    else
			      $pagination.= '<li class="waves-effect"><a href="?page='.$counter.'">'.$counter.'</a></li>';
			  }
			}
			elseif($lastpage > 5 + ($adjacents * 2)) {
			  if($page < 1 + ($adjacents * 2)) {
				for ($counter = 1; $counter < 4 + ($adjacents * 2); $counter++) {
					if ($counter == $page)
			      $pagination.= '<li class="active blue"><a href="?page='.$counter.'">'.$counter.'</a></li>';
			    else
			      $pagination.= '<li class="waves-effect"><a href="?page='.$counter.'">'.$counter.'</a></li>';
			    }
			    $pagination.= "...";
			      $pagination.= '<li class="waves-effect"><a href="?page='.$lpm1.'">'.$lpm1.'</a></li>';
			      $pagination.= '<li class="waves-effect"><a href="?page='.$lastpage.'">'.$lastpage.'</a></li>';
			  }

			  elseif($lastpage - ($adjacents * 2) > $page && $page > ($adjacents * 2)) {
			      $pagination.= '<li class="waves-effect"><a href="?page=1">1</a></li>';
			      $pagination.= '<li class="waves-effect"><a href="?page=2">2</a></li>';
			    $pagination.= "...";
			    for ($counter = $page - $adjacents; $counter <= $page + $adjacents; $counter++) {
			      if ($counter == $page)
			      $pagination.= '<li class="active blue"><a href="?page='.$counter.'">'.$counter.'</a></li>';
			    else
			      $pagination.= '<li class="waves-effect"><a href="?page='.$counter.'">'.$counter.'</a></li>';
			    }
			    $pagination.= "...";
			      $pagination.= '<li class="waves-effect"><a href="?page='.$lpm1.'">'.$lpm1.'</a></li>';
			      $pagination.= '<li class="waves-effect"><a href="?page='.$lastpage.'">'.$lastpage.'</a></li>';
			}else{
			      $pagination.= '<li class="waves-effect"><a href="?page=1">1</a></li>';
				$pagination.= '<li class="waves-effect"><a href="?page=2">2</a></li>';
				$pagination.= "...";
			    for ($counter = $lastpage - (2 + ($adjacents * 2)); $counter <= $lastpage; $counter++) {
				if ($counter == $page)
					$pagination.= '<li class="active blue"><a href="?page='.$counter.'">'.$counter.'</a></li>';
				else
					$pagination.= '<li class="waves-effect"><a href="?page='.$counter.'">'.$counter.'</a></li>';
				}
			}
		}

			// next button
			if ($page < $counter - 1) 
				$pagination.= '<li class="waves-effect"><a href="?page='.$next.'"><i class="material-icons">chevron_right</i></a></li>';
			else
				$pagination.= '<li class="disabled"><a><i class="material-icons">chevron_right</i></a></li>';

			$pagination.= '</ul>';    
			$pagination.= '</div>';    
		}
		$this->data_log = $pagination;
	return $results;
	}



	public function pagination() {
		$pagination = $this->data_log;
		return $pagination;
	}

}
?>