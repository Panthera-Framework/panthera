<script>
$('.ajax_link').click(function (event) { event.preventDefault(); navigateTo(jQuery(this).attr('href')); return false;});


$(document).ready(function(){
    /**
      * Send mail (from form)
      *
      * @author Mateusz Warzyński
      */

    $('#mail_form').submit(function () {
        panthera.jsonPOST({ data: '#mail_form', messageBox: 'w2ui'});
        return false;
    });

    /**
      * Check type of typed data
      *
      * @author Mateusz Warzyński
      */

    $('#value_mailing_server_port').w2field('int');
    
    $('#value_mailing_use_php').change(function () {
        if (!parseInt($('#value_mailing_use_php').val()))
        {
            $('.mailerSettings').show();
            return true;
        }
        
        $('.mailerSettings').hide();
    });
    
    $( document ).tooltip({
      position: {
        my: "center top",
        at: "center bottom+5",
      },
      show: {
        duration: "fast"
      },
      hide: {
        effect: "hide"
      }
    });
    
});

/**
  * Save configuration
  *
  * @author Mateusz Warzyński
  */

$(document).ready(function () {
    $('#mailingSettingsForm').submit(function () {
        panthera.jsonPOST({ data: '#mailingSettingsForm', messageBox: 'w2ui', spinner: new panthera.ajaxLoader($('#mailingSettingsTable')) });
        return false;
    });
});

function setServerDetails(server, port, ssl)
{
    if(ssl)
        ssl = 1;
    else
        ssl = 0;

    $('#value_mailing_server').val(server);
    $('#value_mailing_server_port').val(port);
    $('#value_mailing_smtp_ssl').val(ssl.toString());
}
</script>

    <div class="titlebar">{function="localize('Mail management', 'mailing')"} - {function="localize('Mail server settings, mass mailing, single mail sending', 'mailing')"}</div>

    {if="$canModifySettings"}
    <div class="grid-1">
          <form action="?display=mailing&cat=admin" method="POST" id="mailingSettingsForm">

          <table class="gridTable" style="position: relative;" id="mailingSettingsTable">

            <thead>
                <tr><th colspan="5"><b>{function="localize('Mailing settings', 'mailing')"}:</b></th></tr>
             </thead>

            <tbody>
                <tr>
                  <td>{function="localize('Use PHP mail() function', 'mailing')"}</td>
                  <td>
                     <select id="value_mailing_use_php" name="mailing_use_php" style="width: 500px;">
                        <option value="0">{function="localize('No')"}</option>
                        <option value="1"{if="$mail_attributes.mailing_use_php.value"} selected{/if}>
                        {function="localize('Yes')"}</option>
                     </select>
                     <span style="font-color: red;"><div id="errmsg_mailing_use_php" style="display: none;"></div></span>
                  </td>
                </tr>
                
                <tr>
                  <td>{function="localize('Default sender', 'mailing')"}</td>
                  <td>
                     <input type="text" name="mailing_from" id="value_mailing_from" style="width: 500px;" value="{$mail_attributes.mailing_from.value}">
                     <span style="font-color: red;"><div id="errmsg_mailing_from" style="display: none;"></div></span>
                  </td>
                </tr>
                
                {loop="$mail_attributes"}
                {if="$value.name"}
                <tr class="mailerSettings" {if="$mail_attributes.mailing_use_php.value"}style="display: none;"{/if}>
                  <td>{function="localize($value.name, 'mailing')"}</td>

                  <td>
                    {if="is_bool($value.value)"}
                     <select id="value_{$key}" name="{$key}" style="width: 500px;">
                        <option value="0">{function="localize('No')"}</option>
                        <option value="1"{if="$value.value"} selected{/if}>
                        {function="localize('Yes')"}</option>
                     </select>
                    {elseif="$key == 'mailing_password'"}
                     <input type="password" value='{$value.value}' id="value_{$key}" name="{$key}" onfocus="this.value = ''" style="width: 500px;">
                    {else}
                     <input type="text" value='{$value.value}' id="value_{$key}" name="{$key}" style="width: 500px;">
                    {/if}
                     
                     {if="$key == 'mailing_server'"}
                     <a style="cursor: pointer;" onclick="setServerDetails('smtp.gmail.com', '465', true)" title="{function="localize('Use settings for GMail account', 'mailing')"}">
                        <img src="{$PANTHERA_URL}/images/admin/gmail-icon.png" style="width: 20px; vertical-align: middle; padding-bottom: 5px; margin-left: 10px;">
                     </a>
                     {/if}
                     <span style="font-color: red;"><div id="errmsg_{$key}" style="display: none;"></div></span>
                  </td>

                </tr>
                {/if}
                {/loop}
            </tbody>
            
            <tfoot>
                <tr>
                    <td colspan="5" class="rounded-foot-left">
                        <span style="float: right;">
                            <a href="#" onclick="createPopup('_ajax.php?display=acl&cat=admin&popup=true&name=can_edit_mailing', 1024, 600);" title="{function="localize('Manage permissions')"}">
                                <img src="{$PANTHERA_URL}/images/admin/ui/permissions.png" style="max-height: 23px; margin-left: 3px; vertical-align: middle; padding-bottom: 5px;">
                            </a>
                            
                            <input type="submit" value="{function="localize('Save', 'messages')"}">
                        </span>
                    </td>
                </tr>
            </tfoot>
            
           </table>
           
           </form>

        <br>{/if}

        {if="$canSendMails"}
        <table class="gridTable">

            <thead>
                <tr><th colspan="5"><b>{function="localize('Send an e-mail', 'mailing')"}:</b></th></tr>
             </thead>

            <form action="{$AJAX_URL}?display=mailing&cat=admin&action=send" method="POST" id="mail_form">
            <tfoot>
                <tr>
                    <td colspan="5">
                        <span style="float: right;">
                        <a href="#" onclick="createPopup('_ajax.php?display=acl&cat=admin&popup=true&name=can_send_mails', 1024, 600);" title="{function="localize('Manage permissions')"}">
                                <img src="{$PANTHERA_URL}/images/admin/ui/permissions.png" style="max-height: 23px; margin-left: 3px; vertical-align: middle; padding-bottom: 5px;">
                        </a>
                        
                        <input type="submit" value="{function="localize('Send', 'mailing')"}">
                        </span>
                    </td>
                </tr>
            </tfoot>

            <tbody>
               <tr><td>{function="localize('Subject', 'mailing')"}:</td><td colspan="2"><input type="text" style="width: 98%;" name="subject" value="{$last_subject}"></td></tr>
               <tr><td>{function="localize('Recipients', 'mailing')"}:</td><td><input type="text" style="width: 100%;" name="recipients" value="{$last_recipients}"></td><td style="width: 30px;"> <input type="button" value="{function="localize('Select', 'messages')"}" onclick="createPopup('_ajax.php?display=mailing&cat=admin&action=select', 1024, 'upload_popup');"></td></tr>
               <tr><td>{function="localize('From', 'mailing')"}:</td><td colspan="2"><input type="text" style="width: 98%;" name="from" value="{$last_from}"></td></tr>
               <tr><td>{function="localize('Content', 'mailing')"}:</td><td colspan="2"><textarea style="width: 98%;" name="body">{$last_body}</textarea></td></tr>
               </form>
            </tbody>
           </table>
        {/if}
         </div>
