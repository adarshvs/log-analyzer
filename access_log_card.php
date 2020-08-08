<div class="card-panel" id="attackDate">
  
  <?php foreach ($log_datas as $log_data) { ?>
  <div class="col s12 m12 l10 offset-l1">
      <div class="card" style="
    background-color: #fbfbfb;
    box-shadow: 1px 2px 3px 0px black;
    ">
        <div class="card-content" style="word-wrap: break-word;">
          <span class="card-title">#<?php echo $log_data['id']; ?></span>
          <p>
            <i class="mdi mdi-earth"></i> <b>IP : </b>
            <a target="_blank" href="?show=<?php echo $_GET['show']; ?>&data=<?php echo $_GET['data']; ?>&public_ip=<?php echo $log_data['public_ip']; ?>&href=#logTable"><?php echo $log_data['public_ip']; ?></a>
            <a class="btn-floating btn-flat white waves-effect waves-light" href="?show=<?php echo $_GET['show']; ?>&data=<?php echo $_GET['data']; ?>&page=&info=<?php echo encrypt($log_data['public_ip']); ?>"><i class="material-icons grey-text text-darken-3" style="
    background-color: #fbfbfb;">&#xE8B6;</i></a>
          
            <i class="mdi mdi-clock"></i> <b>Time : </b> <a target="_blank" href="?show=<?php echo $_GET['show']; ?>&data=<?php echo $_GET['data']; ?>&date_time=<?php echo $log_data['date_time']; ?>"><?php echo date("h:i A", strtotime($log_data['date_time'])) ?></a>
          </p>
          <p><i class="mdi mdi-send" ></i> <b>Method : </b><?php echo $log_data['method']; ?>&nbsp;&nbsp;
             <i class="mdi mdi-reply"></i> <b>Response : </b> <?php
            $message = showMessageResponse($log_data['http_response']);
             ?>

        
        <a class="tooltipped" data-position="top" data-delay="10" data-tooltip="<?php echo   $message; ?>"><font color ="blue"><?php echo $log_data['http_response']; ?> &nbsp;&nbsp; </font></a>
          <i class="mdi mdi-file"></i> <b>Content Size : </b><?php echo number_format($log_data['file_bytes']); ?> bytes
        </p>
          <p><i class="mdi mdi-page-layout-header"></i> <b>Header : </b><?php echo $log_data['http_header']; ?></p>
          <p><i class="mdi mdi-link"></i> <b>Reference : </b><a href="<?php echo $log_data['link_ref']; ?>" target="_blank"><?php echo $log_data['link_ref']; ?></a></p>
          <p><i class="mdi mdi-note-text"></i> <b>Note : </b><span style="color: red;"><?php echo $log_data['attack_description']; ?></span></p><br/>
          <p style="
    color: #4d4949;
    font-size: 12px;
    box-shadow: 0 0 2px 0px black;
    /* box-shadow: -1px 2px 5px 0px black; */
    /* background-color: white; */
    background-color: #f1f1f1;
    "><?php echo $log_data['raw_data']; ?></p>

          
        </div>
      </div>
    </div>
    
  <?php }  ?>

</div>