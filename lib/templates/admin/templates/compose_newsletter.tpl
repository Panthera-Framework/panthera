{$site_header}

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
        panthera.jsonPOST({ data: '#newsletter_form', mce: 'tinymce_all', success: function (response) {
                if (response.status == 'success')
                {
                    panthera.alertBox.create('{function="localize('Sent', 'newsletter')"}');
                    $('#messagesQueueNoMessages').hide();
                }
            } 
        });
    });
});
</script>

{include="ui.titlebar"}

<div id="topContent">
    <div class="searchBarButtonArea">
        <input type="button" value="{function="localize('Edit footer', 'newsletter')"}" onclick="panthera.popup.toggle('?display=compose_newsletter&cat=admin&nid={$nid}&action=editFooter')">
        <input type="button" value="{function="localize('New message', 'newsletter')"}" onclick="navigateTo('?display=compose_newsletter&cat=admin&nid={$nid}')">
        <input type="button" value="{function="localize('Messages queue', 'newsletter')"}" onclick="panthera.popup.toggle('element:#messagesQueue')">
        <input type="button" value="{function="localize('Manage subscribers', 'newsletter')"}" onclick="panthera.popup.toggle('?display=newsletter_users&cat=admin&nid={$nid}')">
        <input type="button" value="{function="localize('Recent subscribers', 'newsletter')"}" onclick="panthera.popup.toggle('element:#lastSubscribed')">
    </div>
</div>

<div id="popupOverlay" style="text-align: center; padding-top: 20px;"></div>

<!-- Messages queue popup -->
<div style="display: none;" id="messagesQueue">
    <table style="margin: 0 auto;">
            <thead>
                <tr>
                    <th colspan="3">{function="localize('Queued messages to send', 'newsletter')"}</th>
                </tr>
            </thead>
            
            <tbody>
                {if="count($messages_queue)"}
                {loop="$messages_queue"}
                    <tr>
                        <td>{$value.title|strCut:20}</td>
                        <td>{$value.created}</td>
                        <td>{$value.position}/{$value.count}</td>
                    </tr>
                {/loop}
                {else}
                    <tr><td colspan="3">{function="localize('No messages queued to send', 'newsletter')"}</td></tr>
                {/if}
            </tbody>
        </table>
</div>

<!-- Last subscriptions popup -->
<div style="display: none;" id="lastSubscribed">
    <table style="margin: 0 auto;">
            <thead>
                <tr>
                    <th colspan="2">{function="localize('Recently subscribed by', 'newsletter')"}</th>
                </tr>
            </thead>
            
            <tbody>
                {if="count($recent_subscribers) > 0"}
                {loop="$recent_subscribers"}
                    <tr><td>{$value.address}</td><td>{$value.added}</td></tr>
                {/loop}
                {else}
                    <tr><td colspan="2">{function="localize('There no any users subscribing this newsletter', 'newsletter')"}</td></tr>
                {/if}
            </tbody>
        </table>
</div>

<div class="ajax-content centeredObject" style="text-align: center; padding-left: 0px;">
    <form id="newsletter_form" action="{$AJAX_URL}?display=compose_newsletter&cat=admin&nid={$nid}" method="POST">
    <div style="display: inline-block;">
        <table style="width: 100%;">
            <thead>
                <tr>
                    <th colspan="2">{function="localize('Create a new message', 'newsletter')"}</th>
                </tr>
            </thead>
            
            <tbody>
                <tr>
                    <td style="width: 60px;">{function="localize('Title', 'newsletter')"}:</td>
                    <td><input type="text" value="" name="title"></td>
                </tr>
                
                <tr>
                    <td>{function="localize('Sender', 'newsletter')"}:</td>
                    <td><input type="text" value="" name="from"></td>
                </tr>
                
                <tr>
                    <td colspan="2" style="width: 800px; padding: 0px;"><textarea name="content" id="content_textarea" style="height: 400px; width: 100%;"></textarea></td>
                </tr>
            </tbody>
        </table>
        
        <!-- Options -->
        
        <table style="margin-top: 25px; width: 100%;">
            <thead>
                <tr>
                    <th colspan="2">{function="localize('Options', 'newsletter')"}</th>
                </tr>
            </thead>
            
            <tbody>
                <tr>
                    <td colspan="2"><input type="checkbox" name="sendToAllUsers" value="1"> {function="localize('Send to all users in database', 'newsletter')"}</td>
                </tr>
                
                <tr>
                    <td colspan="2"><input type="checkbox" name="putToDrafts" value="1"> {function="localize('Save message copy into message drafts', 'newsletter')"}</td>
                </tr>
            </tbody>
        </table>
        
        <div style="text-align: right; margin-top: 10px;"><input type="submit" value="{function="localize('Send', 'newsletter')"}"></div>
    </div>
    </form>
</div>
