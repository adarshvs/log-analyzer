<?php 
$user_agent     =   $_SERVER['HTTP_USER_AGENT'];
function getOS() { 
    global $user_agent;
    $os_platform    =   "Unknown OS Platform";
    $os_array       =   array(
                            '/windows nt 6.3/i'     =>  'Windows 8.1',
                            '/windows nt 6.2/i'     =>  'Windows 8',
                            '/windows nt 6.1/i'     =>  'Windows 7',
                            '/windows nt 6.0/i'     =>  'Windows Vista',
                            '/windows nt 5.2/i'     =>  'Windows Server 2003/XP x64',
                            '/windows nt 5.1/i'     =>  'Windows XP',
                            '/windows xp/i'         =>  'Windows XP',
                            '/windows nt 5.0/i'     =>  'Windows 2000',
                            '/windows me/i'         =>  'Windows ME',
                            '/win98/i'              =>  'Windows 98',
                            '/win95/i'              =>  'Windows 95',
                            '/win16/i'              =>  'Windows 3.11',
                            '/macintosh|mac os x/i' =>  'Mac OS X',
                            '/mac_powerpc/i'        =>  'Mac OS 9',
                            '/linux/i'              =>  'Linux',
                            '/ubuntu/i'             =>  'Ubuntu',
                            '/iphone/i'             =>  'iPhone',
                            '/ipod/i'               =>  'iPod',
                            '/ipad/i'               =>  'iPad',
                            '/android/i'            =>  'Android',
                            '/blackberry/i'         =>  'BlackBerry',
                            '/webos/i'              =>  'Mobile'
                        );
    foreach ($os_array as $regex => $value) { 
        if (preg_match($regex, $user_agent)) {
            $os_platform    =   $value;
        }
    }   
    return $os_platform;
}
$result = new WhichBrowser\Parser($user_agent);

		if(!empty($result->browser->name)) {
			$user_browser = $result->browser->name;
		}else{
			$user_browser = '-';
		}
$test_url['link'] = "google.com";
    $test_url['port'] = 80;
    $connection = @fsockopen($test_url['link'], $test_url['port']);
    if($connection){
      $public_ip = file_get_contents("http://checkip.amazonaws.com");
    }else{
      $public_ip = getHostByName(getHostName()); 
    }
$user_os        =   getOS(); //get os name
date_default_timezone_set("Asia/Calcutta");
$date_time=date("y-m-d H:i:s");
//echo $date;
//$pieces = explode(" ", $date);
//$date= $pieces[0];
//$time = $pieces[1];
$referer =$_SERVER["REQUEST_URI"];
$conn = connect_pdo();
$sql = "INSERT INTO user_activity_logs (IP, Browser, Platform, date_time, user_agent, url)
  VALUES ('$public_ip', '$user_browser', '$user_os', '$date_time', '$user_agent', '$referer')";
 // print_r($sql);die();
  $sql = $conn->query($sql);


?>