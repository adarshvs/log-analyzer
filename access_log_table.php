<div class="row" id="accessLogTable">
  <div class="col s12">
    <?php echo $pagination; ?>
    <div class="card-panel" style="
    overflow-x: scroll;
    overflow-y: scroll;
    max-height: 500px;
">
      <table class="responsive-table bordered">
        <thead>
        <tr>
        <th>#</th>
        <th>IP</th>
        <th>Timestamp </th>
        <th>Method</th>
        <th>Header</th>
        <th>Response</th>
        <th>Content Length</th>
        <th>Referrer</th>
        <th>Browser</th>
        <th>Raw Data</th>
        </tr>
      </thead>
      <tbody>
        <?php  
        if (empty($data)) {
          echo "<h5>No data found</h5>";
        }else{
        foreach($data as $log_data) { ?>
        <tr>
          <td><?php echo $log_data['id']; ?></td>
          <td><a target="_blank" href="analyze.php?show=<?php echo $_GET['show']; ?>&data=<?php echo $_GET['data']; ?>&public_ip=<?php echo $log_data['public_ip']; ?>&href=#logTable"><?php echo $log_data['public_ip']; ?></a><a class="btn-floating btn-flat white waves-effect waves-light" href="?show=<?php echo $_GET['show']; ?>&data=<?php echo $_GET['data']; ?>&page=<?php echo $_GET['page']; ?>&info=<?php echo encrypt($log_data['public_ip']); ?>"><i class="material-icons grey-text text-darken-3">&#xE8B6;</i></a></td>
          <td><a target="_blank" href="analyze.php?show=<?php echo $_GET['show']; ?>&data=<?php echo $_GET['data']; ?>&date_time=<?php echo $log_data['date_time']; ?>"><?php echo date("d M Y h:i A", strtotime($log_data['date_time'])) ?></a></td>
          <td><?php echo $log_data['method']; ?></td>
          <td><?php echo $log_data['http_header']; ?></td>
          <td>
            <?php
            $message = showMessageResponse($log_data['http_response']);
             ?>
          <a class="tooltipped" data-position="top" data-delay="10" data-tooltip="<?php echo $message; ?>"><font color ="blue"><?php echo $log_data['http_response']; ?> </font></a></td>
          <td><?php echo number_format($log_data['file_bytes']); ?> bytes</td>
          <td><a href="<?php echo $log_data['link_ref']; ?>" target="_blank"><?php echo $log_data['link_ref']; ?></a></td>
          <td><?php 
                  $result = new WhichBrowser\Parser($log_data['useragent']);
                  if(!empty($result->browser->name)) {
                    echo $result->browser->name;
                  }else{
                    echo $log_data['useragent'];
                  }
                  if(!empty($result->browser->version->value)){
                    echo ' '.$result->browser->version->value;
                  }
                  ?></td>
          <td style="
    font-size: 12px;
    font-family: Lucida Console;
"><?php echo $log_data['raw_data']; ?></td>
        </tr><?php }}?>
      </tbody>
      </table> 
    </div><?php echo $pagination; ?>
  </div>
</div>