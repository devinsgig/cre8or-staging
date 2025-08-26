var adminUserPage = (function () {
    var loading = false;

    var initCategoriesList = function (orderCategoriesListUrl) {
        new Sortable(document.getElementById("user_page_categories_list"), {
            animation: 150,
            filter: ".admin_modal_ajax",
            onUpdate: function (e) {
                $.ajax({
                    type: "POST",
                    url: orderCategoriesListUrl,
                    data: { orders: this.toArray() },
                    dataType: "json",
                });
            },
        });

        $(".menu_child").each(function () {
            new Sortable(document.getElementById($(this).attr("id")), {
                animation: 150,
                filter: ".admin_modal_ajax",
                onUpdate: function (e) {
                    $.ajax({
                        type: "POST",
                        url: orderCategoriesListUrl,
                        data: { orders: this.toArray() },
                        dataType: "json",
                    });
                },
            });
        });
    };

    var initCreateCategory = function () {
        loading = false;
        $("#create_category_submit").unbind("click");
        $("#create_category_submit").click(function () {
            if (loading) {
                return;
            }
            loading = true;
            $.ajax({
                type: "POST",
                url: $("#create_category_form").attr("action"),
                data: $("#create_category_form").serialize(),
                dataType: "json",
                success: function (data) {
                    if (data.status) {
                        location.reload();
                    } else {
                        $("#create_category_form #errors").html("");
                        adminCore.setMessageError(
                            $("#create_category_form #errors"),
                            data.messages
                        );
                    }
                },
            }).done(function (data) {
                loading = false;
            });
        });
    };

    var initCreateFeaturePackage = function() {
        loading = false
        $('#package_submit').click(function(){        
            var form = $('#package_form');
            var formData = new FormData(form[0]);

            if (loading) {
                return
            }
            loading = true

            $.ajax({ 
                type: 'POST', 
                url: $('#package_form').attr('action'), 
                data: formData, 
                dataType: 'json',
                contentType: false,
                processData: false,
                cache: false,
                success: function (data) {
                    if (data.status){
                        location.reload();
                    }
                    else{
                    $('#package_form #errors').html('');
                        adminCore.setMessageError($('#package_form #errors'),data.messages);
                    }
                }
            }).done(function( data ) {
                loading = false
            });
        });
    }
    var initFeaturePackageListing = function(storeOrderUrl) {
        new Sortable(document.getElementById('package_list'), {
            animation: 150,
            filter: '.active_action',
            onUpdate: function (e) {
              $.ajax({ 
                  type: 'POST', 
                  url: storeOrderUrl, 
                  data: {orders: this.toArray()}, 
                  dataType: 'json',
                  success: function (data) {
    
                  }
              });
            }
        });
    }
    var initListing = function() {
        $('#delete_button').click(function(e) {
           if ($('.check_item:checked').length == 0) {            
             return;
           }
 
           adminCore.showConfirmModal(adminTranslate.__('confirm'), adminTranslate.__('confirm_delete_user'), function(){
             $('#action').val('delete');
             $('#user_form').submit();
           });
        });
        $('#active_button').click(function(e) {
           if ($('.check_item:checked').length == 0) {
             return;
           }
 
           adminCore.showConfirmModal(adminTranslate.__('confirm'), adminTranslate.__('confirm_active_user'), function(){
             $('#action').val('active');
             $('#user_form').submit();
           });
        });
     }
    return {
        initCategoriesList: initCategoriesList,
        initCreateCategory: initCreateCategory,
        initCreateFeaturePackage: initCreateFeaturePackage,
        initFeaturePackageListing: initFeaturePackageListing,
        initListing: initListing
    };
})();
