    $('#objectSubmitForm').submit (function () {
        panthera.jsonPOST( { data: '#objectSubmitForm', mce: 'tinymce_all', success: function (response) {
                if (response.status == 'success')
                    navigateTo(window.location.href);
        
            } 
        });
        return false;
    });
