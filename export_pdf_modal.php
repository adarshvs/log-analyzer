<a class="waves-effect waves-light btn modal-trigger" href="#modal1">Generate Report</a>
<!-- <div class="input-field col s6" style="margin-top: 30px;">
	<input id="dateRangePicker" type="text" class="date-range-picker" name="date" placeholder="Date Filter">
	<label for="date-range-picker">Filter by date.</label>
</div> -->
 <!-- Modal Structure -->
  <div id="modal1" class="modal">
  	<form action="log_detail_view.php" method="get" class="s12">
  		<div class="modal-content">
	      	<h4>Export to PDF</h4>
	      	<input type="hidden" name="show" value="<?php echo $_GET['show'] ?>">
			<input type="hidden" name="data" value="<?php echo $_GET['data'] ?>">
			<input type="hidden" name="export" value="pdf">
	       	<!-- <p>
			  <input type="checkbox" id="has_demographics" name="has_demographics" checked="checked">
			  <label for="has_demographics">Include Demographics</label>
			</p> -->
			<div class="input-field col s6" style="margin-top: 30px;">
				<input id="dateRangePicker" type="text" class="date-range-picker" name="date_range" placeholder="Date Filter">
				<label for="date-range-picker">Filter by date.</label>
			</div>
			
	    </div>
	    <div class="modal-footer">
	      <button type="submit" class="modal-close waves-effect waves-green btn-flat">Export</button>
	      <a href="#!" class="modal-close waves-effect waves-green btn-flat">Close</a>
	    </div>	
  	</form>
    
  </div>