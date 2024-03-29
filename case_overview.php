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
$title = "Case Overview";
include_once('includes/header.php');
?>

<div class="card-panel">
  <table class="responsive-table bordered">
    <thead>
      <tr>
        <th>#</th>
        <th>Case</th>
        <th>Reference</th>
        <th>Evidence File</th>
        <th>Website</th>
        <th>Case Date</th>
        <th>Registered by</th>
        <th>Registered on</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
    <?php
    $case_overviews = $conn->prepare('SELECT * FROM `case_details`');
    $case_overviews->execute();
    while($case_overview = $case_overviews->fetch(PDO::FETCH_ASSOC)) {
    ?>
      <tr>
        <td><?php echo $case_overview['id']; ?></td>
        <td><?php echo $case_overview['case_no']; ?></td>
        <td><?php echo $case_overview['ref_no']; ?></td>
        <td><?php echo $case_overview['evidence_file']; ?></td>
        <td><?php echo $case_overview['site_url']; ?></td>
        <td><?php echo date("d F, Y", strtotime($case_overview['date'])); ?></td>
        <td><?php echo $case_overview['username']; ?></td>
        <td><?php echo date("d F, Y h:i A", strtotime($case_overview['registered_date'])); ?></td>
        <td><a class="btn-floating waves-effect waves-light-grey white btn-flat" href="analyze.php?log=edit&data=<?php echo encrypt($case_overview['case_no']); ?>"><i class="material-icons grey-text text-darken-3"  >edit</i></a><a id="<?php echo encrypt($case_overview['case_no']); ?>" class="btn-floating waves-effect waves-light-grey white btn-flat" onclick="$('#<?php echo encrypt($case_overview['case_no']); ?>').modal('open');"><i class="material-icons grey-text text-darken-3">&#xE417;</i></a></td>
		<div id="<?php echo encrypt($case_overview['case_no']); ?>" class="modal">
			<div class="modal-content">
        <div class="row">
        <?php 
          $set_btn_access = $set_btn_sys = 0;
          $count_access = case_log_total_rows("log_access", $case_overview['case_no']);
          if(!$count_access >= 1) {
            $set_btn_access = "disabled";
          }else{
            $set_btn_access = "";
          }
          $count_access = case_log_total_rows("log_sys", $case_overview['case_no']);
          if(!$count_access >= 1) {
            $set_btn_sys = "disabled";
          }else{
            $set_btn_sys = "";
          }

        ?>
				<a <?php echo $set_btn_access; ?> class="blue waves-effect waves-light btn" href="analyze.php?show=access_log&data=<?php echo encrypt($case_overview['case_no']); ?>">Access Log</a>&nbsp;&nbsp;&nbsp;<a <?php echo $set_btn_sys; ?> class="blue waves-effect waves-light btn" href="analyze.php?show=sys_log&data=<?php echo encrypt($case_overview['case_no']); ?>&page=1">Sys Log</a>
        </div>
			</div>
		</div>
      </tr>
      <?php } ?>
      <tr>
    </tbody>
  </table>
</div>
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