<script type="text/javascript">
$(document).ready(function(){
    /**
      * Check type of typed data
      *
      * @author Mateusz Warzyński
      */

/*    $('#value_mailing_server_port').w2field('int'); */
    
    $('#value_mailing_use_php').change(function () {
        if (!parseInt($('#value_mailing_use_php').val()))
        {
            $('.mailerSettings').show();
            return true;
        }
        
        $('.mailerSettings').hide();
    });
    
    /* $( document ).tooltip({
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
    }); */
    
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

function callback_getContactData(data)
{
    var postString = '';

    for (objectID in data)
    {
        if (postString != '')
            postString += ', ';

        postString += objectID;
    }
    
    $('#recipients').val(postString);
}
</script>

{include="ui.titlebar"}

{if="$canSendMails"}

  <div id="topContent" style="min-height: 50px;">
    <div class="searchBarButtonArea">
        <input type="button" value="{function="localize('Send an e-mail', 'mailing')"}" onclick="panthera.popup.toggle('element:#newMail')">
    </div>
  </div>

  <!-- Send new mail -->

  <div id="newMail" style="display: none;">
      <form action="?display=mailing&cat=admin&action=send" method="POST" id="mail_form"  style="margin: 0 auto; margin-bottom: 30px;">
      <table style="display: inline-block;" class="formTable">
         <thead>
                 <tr>
                     <td colspan="2" class="formTableHeader" style="padding-top: 0px; padding-bottom: 30px;">
                         <p style="color: #e5ebef; padding: 0px; margin: 0px; margin-left: 30px;">{function="localize('Send an e-mail', 'mailing')"}</p>
                     </td>
                 </tr>
         </thead>
         
         <tbody>
               <tr>
                   <th>{function="localize('Subject', 'mailing')"}</th>
                   <th colspan="2"><input type="text" name="subject" value="{$last_subject}" style="width: 445px;"></th>
               </tr>
               
               <tr>
                   <th>{function="localize('Recipients', 'mailing')"}</th>
                   <th style="border-right: 0px;"><input type="text" name="recipients" id="recipients" value="{$last_recipients}" style="width: 445px;"></th>
                   <!-- <td style="width: 30px; border-left: 0px; padding-right: 10px;"> <input type="button" value="{function="localize('Select', 'messages')"}" onclick="createPopup('_ajax.php?display=mailing&cat=admin&action=select&callback=getContactData', 1024, 'upload_popup');"></td> -->
               </tr>
               
               <tr>
                   <th>{function="localize('From', 'mailing')"}</th>
                   <th colspan="2"><input type="text" name="from" value="{$last_from}" style="width: 445px;"></th>
               </tr>
               
               <tr>
                   <th>{function="localize('Content', 'mailing')"}</th>
                   <th colspan="2"><textarea style="width: 450px; max-width: 450px; min-width: 450px; height: 150px; background-color: #3d4957; outline: 1px solid #3d4957; border: solid 1px #7c8a98;" name="body">{$last_body}</textarea></th>
               </tr>
          </tbody>
          
          <tfoot>
             <tr>
                 <td colspan="2">
                      <input type="button" value="{function="localize('Cancel')"}" onclick="panthera.popup.close()" style="float: left; margin-left: 30px;">
                      <!-- <a href="#" onclick="createPopup('_ajax.php?display=acl&cat=admin&popup=true&name=can_send_mails', 1024, 600);" title="{function="localize('Manage permissions')"}">
                        <img src="{$PANTHERA_URL}/images/admin/ui/permissions.png" style="max-height: 23px; margin-left: 3px; vertical-align: middle; padding-bottom: 5px;">
                      </a> -->
                        
                      <input type="button" value="{function="localize('Send', 'mailing')"}"  style="float: right; margin-right: 30px;" onclick="$('#mail_form').submit();">
                 </td>
             </tr>
         </tfoot>
      </table>
      </form>
      
      <script type="text/javascript">
      /**
        * Send mail (from form)
        *
        * @author Mateusz Warzyński
        */

        $('#mail_form').submit(function () {
            panthera.jsonPOST({ data: '#mail_form', messageBox: 'w2ui'});
            return false;
        });
    </script>
  </div>

  <div id="popupOverlay" style="text-align: center; padding-bottom: 0px;"></div>
{/if}

<!-- Ajax content -->

<div class="ajax-content" style="text-align: center;">
    
    {if="$canModifySettings"}
      <form action="?display=mailing&cat=admin" method="POST" id="mailingSettingsForm">

          <table style="position: relative; display: inline-block;" id="mailingSettingsTable">

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
            
            <tfoot style="background-color: transparent;">
               <tr>
                 <td colspan="5">
                    <span style="float: right;">
                            <!-- <a href="#" onclick="createPopup('_ajax.php?display=acl&cat=admin&popup=true&name=can_edit_mailing', 1024, 600);" title="{function="localize('Manage permissions')"}">
                                <img src="{$PANTHERA_URL}/images/admin/ui/permissions.png" style="max-height: 23px; margin-left: 3px; vertical-align: middle; padding-bottom: 5px;">
                            </a> -->
                            
                       <input type="submit" value="{function="localize('Save', 'messages')"}">
                    </span>
                 </td>
               </tr>
            </tfoot>
            
         </table>
      </form><br><br>
    {/if}
    
</div>
