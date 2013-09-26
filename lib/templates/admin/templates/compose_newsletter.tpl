<script type="text/javascript">
function initEditor () 
{
    mceSetContent('content_textarea', htmlspecialchars_decode("{$mailFooter}"));
}
</script>

{function="uiMce::display()"}
<script type="text/javascript">
jQuery(document).ready(function($) {
    //{include file="mce.tpl"}
    mceInit('content_textarea');
    
    $('#newsletter_form').submit(function(event){
        event.preventDefault();
        panthera.jsonPOST({ data: '#newsletter_form', messageBox: 'w2ui', mce: 'tinymce_all', success: function (response) {
                if (response.status == 'success')
                {
                    $('#messagesQueueNoMessages').hide();
                }
            } 
        });
    });
});
</script>

{include="ui.titlebar"}

<div class="grid-1">
    <!-- messages box -->
        <form id="newsletter_form" action="{$AJAX_URL}?display=compose_newsletter&cat=admin&nid={$nid}" method="POST">
        <div class="title-grid">{function="localize('Compose a new message', 'newsletter')"}</div>
        <div class="content-gird">
             <table style="border: 0px; width: 100%;">
                <tr>
                    <td style="width: 60px;">{function="localize('Title', 'newsletter')"}:</td><td><input type="text" value="" name="title"></td>
                </tr>
                
                <tr>
                    <td style="width: 60px; padding-bottom: 20px;">{function="localize('Sender', 'newsletter')"}:</td><td style="padding-bottom: 20px;"><input type="text" value="" name="from"></td>
                </tr>
                
                 <tr>
                    <td colspan="2">
                        <textarea name="content" id="content_textarea" style="width: 99%; height: 400px;"></textarea><br><br>
                    </td>
                 </tr>
                 
                 <tr>
                    <td colspan="2">
                        <input type="checkbox" name="sendToAllUsers" value="1"> {function="localize('Send to all users in database', 'newsletter')"}
                    </td>
                 </tr>
                 
                 <tr>
                    <td colspan="2">
                        <input type="checkbox" name="putToDrafts" value="1"> {function="localize('Save message copy into message drafts', 'newsletter')"}
                    </td>
                 </tr>
                 
                 <tr>
                    <td colspan="2" style="padding-top: 15px;">
                        <input type="button" value="{function="localize('Subscribers', 'newsletter')"}" onclick="createPopup('?display=newsletter_users&cat=admin&nid={$nid}', 1024);"> 
                        <input type="button" value="{function="localize('Edit footer', 'newsletter')"}" onclick="navigateTo('?display=compose_newsletter&cat=admin&nid={$nid}&action=editFooter');"> 
                        <input type="submit" value="{function="localize('Send', 'newsletter')"}" style="float: right;">
                    </td>
                 </tr>
             </table>
        </div>
        </form>
</div>

<div class="grid-2">
    <div class="title-grid">{function="localize('Recently subscribed by', 'newsletter')"}</div>
    
     <div class="content-gird">
        {if="count($recent_subscribers) > 0"}
            <table class="gridTable" style="border: 0px">
            <tbody>
                {loop="$recent_subscribers"}
                    <tr><td>{$value.address}</td><td>{$value.added}</td></tr>
                {/loop}
                </tbody>
            </table>
        
        {else}
            {function="localize('There no any users subscribing this newsletter', 'newsletter')"}
        {/if}
     </div>
</div>

<div class="grid-2">
    <div class="title-grid">{function="localize('Queued messages to send', 'newsletter')"}</div>
    
     <div class="content-gird">
        <table class="gridTable" style="border: 0px">
            <tbody id="messagesQueue">
            {if="!count($messages_queue)"}
                <tr id="messagesQueueNoMessages"><td colspan="3">{function="localize('No messages queued to send', 'newsletter')"}</td></tr>
                {else}
                {loop="$messages_queue"}
                <tr><td>{$value.title|strCut:20}</td><td>{$value.created}</td><td>{$value.position}/{$value.count}</td></tr>
                {/loop}
            {/if}
            </tbody>
        </table>
     </div>
</div>
