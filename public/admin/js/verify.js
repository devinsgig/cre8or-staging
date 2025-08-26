var adminVerify = function () {   
    var initListing = function() {
       $('#verify_button').click(function(e) {
          if ($('.check_item:checked').length == 0) {            
            return;
          }

          adminCore.showConfirmModal(adminTranslate.__('confirm'), adminTranslate.__('confirm_verify_user'), function(){
            $('#action').val('verify');
            $('#user_form').submit();
          });
       });
       $('#reject_button').click(function(e) {
          if ($('.check_item:checked').length == 0) {
            return;
          }

          adminCore.showConfirmModal(adminTranslate.__('confirm'), adminTranslate.__('confirm_reject_user'), function(){
            $('#action').val('reject');
            $('#user_form').submit();
          });
       });
    }
    return {
      initListing: initListing
    };
}();