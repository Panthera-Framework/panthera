<script type="text/javascript">
$('.ajax_link').click(function (event) { event.preventDefault(); navigateTo(jQuery(this).attr('href')); return false;});


/**
  * Submit change password form
  *
  * @author Damian Kęska
  */

$('#changepasswd_form').submit(function () {
    panthera.jsonPOST({ data: '#changepasswd_form', messageBox: 'userinfoBox', success: function (response) {
            if (response.status == "success")
            {
                jQuery('#change_success').slideDown();
                setTimeout('jQuery(\'#change_success\').slideUp();', 5000);
                jQuery('#password_window').hide();
            }
        }
    });

    return false;

});


/**
  * Submit language form
  *
  * @author Damian Kęska
  */

$('#changelanguage_form').submit(function () {
    panthera.jsonPOST({ data: '#changelanguage_form', messageBox: 'userinfoBox', success: function (response) {
            if (response.status == "success")
                navigateTo('?display=settings&action=my_account');
        }
    });

    return false;

});

/**
  * Jump to other page in a table
  *
  * @author Damian Kęska
  */

function jumpToAjaxPage(id)
{
    panthera.htmlGET({ url: '?display=settings&action=users&subaction=show_table&usersPage='+id, success: '#all_users_window' });
}

function aclModify(id, name)
{
    panthera.jsonPOST({ url: '?display=settings&action=my_account{$user_uid}', data: 'aclname='+name+'&value='+$('#'+id).val(), success: function (response) {
          if (response.status == "success")
          {
          } else {
              jQuery('#change_error').slideDown();
              jQuery('#change_error').html(response.message);
          }
        }
    });
}

</script>

{if $action eq 'system_info'}
    {include 'settings_systeminfo.tpl'}
{elseif $action eq 'show_table'}
    {include 'settings_showtable.tpl'}
{elseif $action eq 'users'}
    {include 'settings_users.tpl'}
{elseif $action eq 'my_account'}
    {include 'settings_myaccount.tpl'}

{else}
<div class="titlebar">{"Settings"|localize}</div>
<div class="grid-1">
            <h2>&nbsp;&nbsp;<a href="?display=settings&action=users" class="ajax_link">{"Users"|localize}</a></h2>
            <h2>&nbsp;&nbsp;<a href="?display=settings&action=my_account" class="ajax_link">{"My account"|localize:settings}</a></h2>
            <!-- <h2>&nbsp;&nbsp;<a href="?display=general_settings" class="ajax_link">{"Generic"|localize}</a></h2> -->
            <h2>&nbsp;&nbsp;<a href="?display=settings&action=system_info" class="ajax_link">{"System"|localize}</a></h2>

        {if isDebugging}
            <h2>&nbsp;&nbsp;<a href="?display=ajaxpages" class="ajax_link">{"Pages index"|localize:settings}</a></h2>
            <input type="text" id="debug_page"> <input type="button" onclick="navigateTo(jQuery('#debug_page').val()); return false;" value="Przejdź do strony">
        {/if}
</div>
{/if}
