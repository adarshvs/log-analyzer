    </div>
  </main>
	<footer class="page-footer grey lighten-3 z-depth-2">
		<div class="footer-copyright grey-text text-darken-3 center-align">
			<div class="container">&copy;<?php echo date("Y"); ?> - Scavar</div>
		</div>
	</footer>
    <script type="text/javascript" src="assets/js/materialize.min.js?v=0.100.2"></script>
    <script type="text/javascript" src="assets/js/main.js?v=0.4"></script>
    <?php 
      if(isset($_GET['info'])) {
       ?>
       <script type="text/javascript">
    $(document).ready(function(){ $('#ip_info').modal('open'); 
    });
	
	</script>
    <?php 
      }
     ?>

    <?php  if(isset($_GET['show'])) { ?>
    <script src='assets/plugins/full-calendar/lib/main.js'></script>
    <?php } ?>
    <script type="text/javascript">
      $(document).ready(function() {
          $('.date-range-picker').daterangepicker({
            timePicker: true,
            locale: {
              format: 'YYYY-MM-DD'
            }
          });
      });
    </script>
  </body>
</html>