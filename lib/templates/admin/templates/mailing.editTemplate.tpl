{include="ui.titlebar"}

<script type="text/javascript">
/**
 * Init MCE editor
 *
 * @author Mateusz Warzyński
 */

function initEditor()
{
    {loop="$versions"}
        //mceSetContent('#mailEditor{$key}', htmlspecialchars_decode("{$value|filterInput:wysiwyg}"));
    {/loop}
}
</script>

{function="uiMce::display()"}

<div id="newMail" style="display: none;">
      <form action="?display=mailing&cat=admin&action=send&template={$templateName}&language={$lang}" method="POST" id="mail_form"  style="margin: 0 auto; margin-bottom: 30px;">
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
                   <th>{function="localize('Recipients', 'mailing')"}:</th>
                   <th style="border-right: 0px;"><input type="text" name="recipients" id="recipients" value="{$last_recipients}" style="width: 445px;"></th>
                   <!-- <td style="width: 30px; border-left: 0px; padding-right: 10px;"> <input type="button" value="{function="localize('Select', 'messages')"}" onclick="createPopup('_ajax.php?display=mailing&cat=admin&action=select&callback=getContactData', 1024, 'upload_popup');"></td> -->
               </tr>
               
               <tr>
                   <th>{function="localize('From', 'mailing')"}:</th>
                   <th colspan="2"><input type="text" name="from" value="{$last_from}" style="width: 445px;"></th>
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
  
<form action="?display=mailing&cat=admin&action=editTemplate&tpl={$templateName}&language={$lang}" method="POST" id="saveForm">
    <div id="topContent">
        <div class="searchBarButtonArea">
            <div class="searchBarButtonAreaLeft">
                <input type="button" value="{function="localize('Back')"}" onclick="navigateTo('?display=mailing&cat=admin');">
            </div>
        
            <input type="button" value="{function="localize('Send test mail', 'mailing')"}" onclick="panthera.popup.toggle('element:#newMail')">
            <input type="submit" value="{function="localize('Save')"}">
        </div>
    </div>
    
    <div id="popupOverlay" style="text-align: center; padding-bottom: 0px;"></div>
    
    {$topicDisplayed=False}
    <div class="ajax-content" style="text-align: center;">
        {loop="$versions"}
        <div class="articleContainer mail{$key}Container">
                <div class="articleTitlebar mail{$key}Titlebar">
                    <a style="cursor: pointer;">
                        <i><b>{function="slocalize('%s version', 'mailing', ucfirst($key))"}</b></i>
                    </a>
                </div>
                <div class="articleContent mail{$key}Content" style="height: 340px; padding-top: 20px;">
                    {if="!$topicDisplayed"}
                        <div style="padding-bottom: 20px;">
                            {function="localize('Topic', 'mailing')"}: <input type="text" name="topic" value="{$topic}">
                            <span style="color: grey;"><small><i>{function="localize('Global topic tags', 'mailing')"}: {&#36;loggedUserName}, {&#36;loggedUserLogin}, {&#36;dateNow}</i></small></span>
                        </div>{$topicDisplayed=True}
                    {/if}
                    
                    <span style="color: grey;"><small><i>{function="localize('Global topic tags', 'mailing')"}: {&#36;user}, {&#36;PANTHERA_URL}</i></small></span><br><br>
                    <textarea name="content[{$key}]" id="mailEditor{$key}" style="width: 99%; height: 245px;">{$value|filterInput:wysiwyg_newline}</textarea>
                    {if="$key == 'html'"}<script type="text/javascript">mceInit('mailEditor{$key}');</script>{/if}
                </div>
            </div>
        {/loop}
    </div>
</form>

<script type="text/javascript">
$('#saveForm').submit(function () {
    panthera.jsonPOST({ data: '#saveForm', mce: 'tinymce_all'});
    return false;
});
</script>