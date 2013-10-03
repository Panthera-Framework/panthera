{$site_header}

<script type="text/javascript">
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

    $('#value_mailing_use_php').change(function () {
        if (!parseInt($('#value_mailing_use_php').val()))
        {
            $('.mailerSettings').show();
            return true;
        }
        
        $('.mailerSettings').hide();
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

<!-- Sending a new mail -->
<div style="display: none;" id="sendEmailPopup">
    {if="$canSendMails"}
    <table class="formTable" style="margin: 0 auto;">
        <thead>
            <tr>
                <th colspan="5"><b>{function="localize('Send an e-mail', 'mailing')"}:</b></th>
            </tr>
        </thead>
        <form action="{$AJAX_URL}?display=mailing&cat=admin&action=send" method="POST" id="mail_form">
            <tbody>
                <tr>
                    <th>{function="localize('Subject', 'mailing')"}:</th>
                    <td colspan="2"><input type="text" style="width: 98%;" name="subject" value="{$last_subject}"></td>
                </tr>
                <tr>
                    <th>{function="localize('Recipients', 'mailing')"}:</th>
                    <td style="border-right: 0px;"><input type="text" style="width: 60%;" name="recipients" id="recipients" value="{$last_recipients}">
                    <input type="button" value="{function="localize('Select', 'messages')"}" onclick="createPopup('_ajax.php?display=mailing&cat=admin&action=select&callback=getContactData', 1024, 'upload_popup');"></td>
                </tr>
                <tr>
                    <th>{function="localize('From', 'mailing')"}:</th>
                    <td colspan="2"><input type="text" style="width: 98%;" name="from" value="{$last_from}"></td>
                </tr>
                <tr>
                    <th>{function="localize('Content', 'mailing')"}:</th>
                    <td colspan="2"><textarea style="width: 98%;" name="body">{$last_body}</textarea></td>
                </tr>
        </form>
        </tbody>
        
        
        <tfoot>
            <tr>
                <td colspan="2" style="padding-top: 35px;">
                    <input type="button" value="{function="localize('Cancel')"}" onclick="panthera.popup.close()" style="float: left; margin-left: 30px;">
                    <input type="submit" value="{function="localize('Send', 'mailing')"}" style="float: right; margin-right: 30px;">
                </td>
            </tr>
        </tfoot>
    </table>
    {/if}
</div>

<!-- Settings form -->
<form action="?display=mailing&cat=admin" method="POST" id="mailingSettingsForm">
<div id="topContent">
    <div class="searchBarButtonArea">
        <input type="button" value="{function="localize('Send an e-mail', 'mailing')"}" onclick="panthera.popup.toggle('element:#sendEmailPopup')">
        <input type="button" value="{function="localize('Newsletter', 'settings')"}" onclick="navigateTo('?display=newsletter&cat=admin')">
        <input type="submit" value="{function="localize('Save', 'messages')"}">
    </div>
</div>

<div id="popupOverlay" style="text-align: center; padding-top: 20px;"></div>

<div class="ajax-content centeredObject" style="text-align: center; padding-left: 0px;">
    <table class="gridTable" style="position: relative; margin: 0 auto;" id="mailingSettingsTable">
        <thead>
            <tr>
                <th colspan="5"><b>{function="localize('Mailing settings', 'mailing')"}:</b></th>
            </tr>
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
                    <span style="font-color: red;">
                        <div id="errmsg_mailing_use_php" style="display: none;"></div>
                    </span>
                </td>
            </tr>
            <tr>
                <td>{function="localize('Default sender', 'mailing')"}</td>
                <td>
                    <input type="text" name="mailing_from" id="value_mailing_from" style="width: 500px;" value="{$mail_attributes.mailing_from.value}">
                    <span style="font-color: red;">
                        <div id="errmsg_mailing_from" style="display: none;"></div>
                    </span>
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
                <span style="font-color: red;">
                    <div id="errmsg_{$key}" style="display: none;"></div>
                </span>
            </td>
            </tr>
            {/if}
            {/loop}
        </tbody>
    </table>
</form>
</div>
