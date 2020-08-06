
  <div id="ip_info" class="modal">
      <div class="modal-content">
        <table>
          <tr>
            <td><b>IP</b></td><td><?php echo $info->query; ?></td>
          </tr>
          <tr>
            <td><b>Country</b></td><td><?php echo $info->country; ?></td>
          </tr>
          <tr>
            <td><b>Country code</b></td><td><?php echo $info->countryCode; ?></td>
          </tr>
          <tr>
            <td><b>Region</b></td><td><?php echo $info->regionName; ?></td>
          </tr>
          <tr>
            <td><b>Region code</b></td><td><?php echo $info->region; ?></td>
          </tr>
          <tr>
            <td><b>Zip Code</b></td><td><?php echo $info->zip; ?></td>
          </tr>
          <tr>
            <td><b>Latitude</b></td><td><?php echo $info->lat; ?></td>
          </tr>
          <tr>
            <td><b>Longitude</b></td><td><?php echo $info->lon; ?></td>
          </tr>
          <tr>
            <td><b>Timezone</b></td><td><?php echo $info->timezone; ?></td>
          </tr>
          <tr>
            <td><b>ISP</b></td><td><?php echo $info->isp; ?></td>
          </tr>
          <tr>
            <td><b>Organization</b></td><td><?php echo $info->org; ?></td>
          </tr>
          <tr>
            <td><b>AS number/name</b></td><td><?php echo $info->as; ?></td>
          </tr>
        </table>
      </div>
      <div class="modal-footer">
        <a href="#" class="modal-action modal-close waves-effect btn-flat red-text">Okay</a>
      </div>
  </div>
  