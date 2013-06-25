<script>
$('.ajax_link').click(function (event) { event.preventDefault(); navigateTo(jQuery(this).attr('href')); return false;});


$(document).ready(function(){

    /**
      * Send mail (from form)
      *
      * @author Mateusz Warzyński
      */

    $('#mail_form').submit(function () {
        panthera.jsonPOST({ data: '#mail_form', messageBox: 'userinfoBox'});
        return false;
    });

    /**
      * Check type of typed data
      *
      * @author Mateusz Warzyński
      */

    $('#value_mailing_server_port').w2field('int');
});

/**
  * Save variable to configuration
  *
  * @author Mateusz Warzyński
  */

function saveVariable(id)
{
    value = jQuery('#value_'+id).val();

    panthera.jsonPOST({ url: '?display=conftool&action=change', data: 'id='+id+'&value='+value, messageBox: 'userinfoBox', success: function (response) {
            if (response.status == "success") {
                jQuery('#button_'+id).attr("disabled", "disabled");
                jQuery('#button_'+id).animate({ height:'toggle'});
                setTimeout("jQuery('#button_"+id+"').removeAttr('disabled');", 2500);
                setTimeout("jQuery('#button_"+id+"').animate({ height:'toggle' });", 2500);
            }
        }
    });

    return false;

}

</script>

    <div class="titlebar">{"Mail management"|localize:mailing} - {"Mail server settings, mass mailing, single mail sending"|localize:mailing}</div>

    <br>
    <div class="msgSuccess" id="userinfoBox_success"></div>
    <div class="msgError" id="userinfoBox_failed"></div>

    <div class="grid-1">

          <table class="gridTable">

            <thead>
                <tr><th colspan="5"><b>{"Mailing settings"|localize:mailing}:</b></th></tr>
             </thead>

            <tfoot>
                <tr>
                    <td colspan="5" class="rounded-foot-left"><em>Panthera - {"mailing"|localize:mailing} <input type="button" value="{"Manage permissions"|localize:messages}" onclick="createPopup('_ajax.php?display=acl&popup=true&name=can_view_mailing', 1024, 'upload_popup');" style="float: right;">
                    </em></td>
                </tr>
            </tfoot>

            <tbody>
                {foreach from=$mail_attributes key=k item=v}
                <tr>
                  <td>{"$v.name"|localize:mailing}</td>

                  <td>
                    {if is_bool($v.value)}
                     <select id="value_{$v.record_name}" style="width: 500px;">
                        <option value="0">{"No"|localize}</option>
                        <option value="1"{if $v.value eq "1"} selected{/if}>
                        {"Yes"|localize}</option>
                     </select>
                    {elseif $v.record_name eq "mailing_password"}
                     <input type="password" value='{$v.value}' id="value_{$v.record_name}" onfocus="this.value = ''" style="width: 500px;">
                    {else}
                     <input type="text" value='{$v.value}' id="value_{$v.record_name}" style="width: 500px;">
                    {/if}
                     <input type="button" value="{"Save"|localize:messages}" id="button_{$v.record_name}" onclick="saveVariable('{$v.record_name}');">
                     <span style="font-color: red;"><div id="errmsg_{$v.record_name}" style="display: none;"></div></span>
                  </td>

                </tr>
                {/foreach}
            </tbody>
           </table>

        <br>

        <table class="gridTable">

            <thead>
                <tr><th colspan="5"><b>{"Send an e-mail"|localize:mailing}:</b></th></tr>
             </thead>

            <form action="{$AJAX_URL}?display=mailing&action=send" method="POST" id="mail_form">
            <tfoot>
                <tr>
                    <td colspan="5" class="rounded-foot-left"><em>Panthera - {"mailing"|localize:mailing} <input type="submit" value="{"Send"|localize:mailing}" style="float: right;">&nbsp;<input type="button" value="{"Manage permissions"|localize:messages}" onclick="createPopup('_ajax.php?display=acl&popup=true&name=can_send_mails', 1024, 'upload_popup');" style="float: right; margin-right: 7px;">
                    </em></td>
                </tr>
            </tfoot>

            <tbody>
               <tr><td>{"Subject"|localize:mailing}:</td><td colspan="2"><input type="text" style="width: 98%;" name="subject" value="{$last_subject}"></td></tr>
               <tr><td>{"Recipients"|localize:mailing}:</td><td><input type="text" style="width: 100%;" name="recipients" value="{$last_recipients}"></td><td style="width: 30px;"> <input type="button" value="{"Select"|localize:messages}" onclick="createPopup('_ajax.php?display=mailing&action=select', 1024, 'upload_popup');"></td></tr>
               <tr><td>{"From"|localize:mailing}:</td><td colspan="2"><input type="text" style="width: 98%;" name="from" value="{$last_from}"></td></tr>
               <tr><td>{"Content"|localize:mailing}:</td><td colspan="2"><textarea style="width: 98%;" name="body">{$last_body}</textarea></td></tr>
               </form>
            </tbody>
           </table>

         </div>
