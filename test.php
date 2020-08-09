<html>
<head>
<script>
function display()
{
document.getElementById("displayarea").innerHTML = document.getElementById("myinput").value;    
document.getElementById("myinput").value = "";
}


$.getJSON('https://ipapi.co/json/', function(display){
  console.log(data)
})
</script>
</head>

<body>
<input type="text" name="myinput" id="myinput">
<input type="button" value="Submit" name="submit" id="submit" onClick="display()"/>
<div id="displayarea">Sample Text. This data will disappear if you press button</div>





<?php
require("includes/connection.php");

$conn = connect_pdo();
    $sql_check_username = "SELECT 8 FROM `log_analyzer`";
    $check_username = $conn->prepare($sql_check_username);
    $check_username->bindValue(':http_header', $http_header);
    $check_username->execute();
    $sql_check_email = "SELECT `email` FROM `users` WHERE `email` = :email";
    $check_email = $conn->prepare($sql_check_email);
    $check_email->bindValue(':email', $email);
    $check_email->execute();
    if($check_username->rowCount() > 0 || $check_email->rowCount() > 0){
      $message = '<div id="message" class="card col s12 m10 l8 offset-m1 offset-l2 red white-text"><div class="card-content center-align">Sorry user already exist!</div></div>';
    }else{
    
        }   ?>
</body>
</html>