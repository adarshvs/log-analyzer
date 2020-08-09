<?php foreach ($sys_log_datas as $key => $log_data) { ?>
      <div class="col s12 m12" style="padding-left: 0px !important;">
        <div class="card">
          <div class="card-content" style="word-wrap: break-word;">
            <span class="card-title"><?php echo $log_data['id']; ?></span>
            <p><i class="mdi mdi-page-layout-header"></i> <b>Timestamp : </b><?php echo $log_data['date_time']; ?></p>
            <p><i class="mdi mdi-page-layout-header"></i> <b>Process Name : </b><?php echo $log_data['process_name']; ?></p>
            <p><i class="mdi mdi-page-layout-header"></i> <b>Process ID : </b><?php echo $log_data['process_id']; ?></p>
            <p><i class="mdi mdi-page-layout-header"></i> <b>Raw Data : </b><?php echo getEncodedName($log_data['raw_data']); ?></p>
          </div>
        </div>
      </div>
      <?php } ?>