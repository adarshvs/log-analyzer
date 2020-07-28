var countdown = 30 * 60 * 1000;
var timerId = setInterval(function(){
  countdown -= 1000;
  var min = Math.floor(countdown / (60 * 1000));
  var sec = Math.floor((countdown - (min * 60 * 1000)) / 1000);

  if (min == 0) {
     clearInterval(timerId);
      $('#timeout').modal({dismissible: false});
      if($('#timeout').modal('open')){
        var modal_countdown = 1 * 60 * 1000;
        var modal_timerId = setInterval(function(){
          modal_countdown -= 1000;
          var modal_min = Math.floor(modal_countdown / (60 * 1000));
          var modal_sec = Math.floor((modal_countdown - (modal_min * 60 * 1000)) / 1000);

        if (modal_countdown <= 0) {
           clearInterval(timerId);
           window.location.replace("logout.php");
         }else{
          $("#modal_timer").html(modal_sec);
        }
        }, 1000);
      }
  } else {
     $("#timer").html(min + " m " + sec + " s");
  }

}, 1000);
$(document).ready(function() {
  $('.button-collapse').sideNav({
      menuWidth: 250
  });
  $('select').material_select();
  $('#gender').change(function() {
      if($("#gender").val() !== ""){ 
        $('#gender-label').css('color','#2196f3');
        $(':input[value="Select your gender"]').css({"color": "#424242", "border-bottom": "1px solid #2196f3", "-webkit-box-shadow": "0 1px 0 0 #2196f3", "box-shadow": "0 1px 0 0 #2196f3"});
        $(':input[value="Female"]').css({"color": "#424242", "border-bottom": "1px solid #2196f3", "-webkit-box-shadow": "0 1px 0 0 #2196f3", "box-shadow": "0 1px 0 0 #2196f3"});
        $(':input[value="Male"]').css({"color": "#424242", "border-bottom": "1px solid #2196f3", "-webkit-box-shadow": "0 1px 0 0 #2196f3", "box-shadow": "0 1px 0 0 #2196f3"});
      }
  });
  $('#role').change(function() {
      if($("#role").val() !== ""){ 
        $('#role-label').css('color','#2196f3');
        $(':input[value="Select the role"]').css({"color": "#424242", "border-bottom": "1px solid #2196f3", "-webkit-box-shadow": "0 1px 0 0 #2196f3", "box-shadow": "0 1px 0 0 #2196f3"});
        $(':input[value="Admin"]').css({"color": "#424242", "border-bottom": "1px solid #2196f3", "-webkit-box-shadow": "0 1px 0 0 #2196f3", "box-shadow": "0 1px 0 0 #2196f3"});
        $(':input[value="User"]').css({"color": "#424242", "border-bottom": "1px solid #2196f3", "-webkit-box-shadow": "0 1px 0 0 #2196f3", "box-shadow": "0 1px 0 0 #2196f3"});
      }
  });
  $('#log_type').change(function() {
      if($("#log_type").val() !== ""){ 
        $('#log_label').css('color','#2196f3');
        $(':input[value="Choose your Log type"]').css({"color": "#424242", "border-bottom": "1px solid #2196f3", "-webkit-box-shadow": "0 1px 0 0 #2196f3", "box-shadow": "0 1px 0 0 #2196f3"});
      }
  });
  $('.datepicker').pickadate({
    selectMonths: true,
    selectYears: 50,
    max: true,
    today: '',
    clear: 'Clear',
    close: 'Ok',
    formatSubmit: 'yyyy-mm-dd',
    closeOnSelect: false,
    onSet: function() {
      $('#label-dob').css('color','#2196f3');
      $('#dob').css({"border-bottom": "1px solid #2196f3", "-webkit-box-shadow": "0 1px 0 0 #2196f3", "box-shadow": "0 1px 0 0 #2196f3"});
      $('#label-att_date').css('color','#2196f3');
      $('#att_date').css({"border-bottom": "1px solid #2196f3", "-webkit-box-shadow": "0 1px 0 0 #2196f3", "box-shadow": "0 1px 0 0 #2196f3"});
    }
  });
   $('.modal').modal();
  $('#message').delay(2000).fadeOut(1500);
  $('#preview').click(function() {
    $('#profile_pic').click();
  });
  document.getElementById("profile_pic").onchange = function () {
    var reader = new FileReader();
    reader.onload = function (e) {
      document.getElementById("preview").src = e.target.result;
    };
    reader.readAsDataURL(this.files[0]);
  };
});