{$site_header}

<script type="text/javascript">
function initEditor () 
{
    mceSetContent('content_textarea', htmlspecialchars_decode("{$mailFooter}"));
}
</script>

{function="uiMce::display()"}
<script type="text/javascript">
$(document).ready(function($) {
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

function saveAsDraft()
{
    $('#saveasdraft').val(1);
    $('#newsletter_form').submit();
    $('#saveasdraft').val(0);
}
</script>

{include="ui.titlebar"}

<div id="topContent">
    <div class="searchBarButtonArea">
        <input type="button" value="{function="localize('Saved drafts', 'editor')"}" onclick="panthera.popup.toggle('?display=editor.drafts&cat=admin&popup=true&callback=mceInsertContent')">
        {if="!$specialCategory"}
        <input type="button" value="{function="localize('Edit footer', 'newsletter')"}" onclick="panthera.popup.toggle('?{function="Tools::getQueryString('GET', 'action=editFooter', '_')"}')">
        {/if}
        <input type="button" value="{function="localize('New message', 'newsletter')"}" onclick="navigateTo('?{function="Tools::getQueryString('GET', 'display=newsletter.compose', '_')"}')">
        <input type="button" value="{function="localize('Messages queue', 'newsletter')"}" onclick="panthera.popup.toggle('element:#messagesQueue')">
        {if="!$specialCategory"}
        	<input type="button" value="{function="localize('Manage subscribers', 'newsletter')"}" onclick="panthera.popup.toggle('?{function="Tools::getQueryString('GET', 'display=newsletter.users', '_')"}')">
        	<input type="button" value="{function="localize('Recent subscribers', 'newsletter')"}" onclick="panthera.popup.toggle('element:#lastSubscribed')">
    	{else}
    		<input type="button" value="{function="localize('Recipients', 'newsletter')"}" onclick="panthera.popup.toggle('?{function="Tools::getQueryString('GET', 'display=newsletter.recipients', '_')"}')">
    	{/if}
    </div>
</div>

<div id="popupOverlay" style="text-align: center; padding-top: 20px;"></div>

<!-- Messages queue popup -->
<div style="display: none;" id="messagesQueue">
    <table style="margin: 0 auto; min-width: 50%;">
            <thead>
                <tr>
                    <th colspan="3">{function="localize('Queued messages to send', 'newsletter')"}</th>
                </tr>
            </thead>
            
            <tbody class="bgTable">
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
    <table style="margin: 0 auto; min-width: 50%;">
            <thead>
                <tr>
                    <th colspan="2">{function="localize('Recently subscribed by', 'newsletter')"}</th>
                </tr>
            </thead>
            
            <tbody class="bgTable">
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
<form id="newsletter_form" action="?{function="Tools::getQueryString('GET', '', '_')"}" method="POST">
<div class="ajax-content centeredObject" style="text-align: center; padding-left: 0px;">
    <div style="display: inline-block; margin: 0 auto;">
        <table style="width: 100%; min-width: 800px; margin-bottom: 25px;">
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
            </tbody>
        </table>
        
        <table style="margin-top: 25px; margin-bottom: 25px; width: 100%;">
            <thead>
                <tr>
                    <th>{function="localize('Tags', 'newsletter')"}</th>
                </tr>
            </thead>
            
            <tbody>
            	<tr>
            		<td><div style="max-width: 650px; word-spacing: 13px; line-height: 25px;"><i>
            			{$i=0}
            			{loop="$tags"}
            				{$i=$i+1}
            				<a onclick="mceAppend('{$key}');" style="cursor: pointer;" {if="$value"}title="{$value}"{/if}>{$key}</a>{if="$i != count($tags)"},{/if}
            			{/loop}
            		</i></div></td>
            	</tr>
            </tbody>
        </table>
        
        <textarea name="content" id="content_textarea" style="height: 400px; width: 100%;"></textarea>
        
        {if="$specialCategory"}
        <input type="hidden" name="recipientsData" id="recipientsData">
        {/if}
        
        <!-- Options -->
        
        <table style="margin-top: 25px; width: 100%;">
            <thead>
                <tr>
                    <th colspan="2">{function="localize('Options', 'newsletter')"}</th>
                </tr>
            </thead>
            
            <tbody>
            	{if="!$specialCategory"}
                <tr>
                    <td colspan="2"><input type="checkbox" name="sendToAllUsers" value="1"> {function="localize('Send to all users in database', 'newsletter')"}</td>
                </tr>
                {/if}
                
                <tr>
                    <td colspan="2"><input type="checkbox" name="putToDrafts" value="1"> {function="localize('Save message copy into message drafts', 'newsletter')"}</td>
                </tr>
            </tbody>
        </table>
        
        <div style="text-align: right; margin-top: 10px;">
            <input type="hidden" name="saveasdraft" id="saveasdraft" value="0">
            <input type="button" value="{function="localize('Save as draft', 'newsletter')"}" onclick="saveAsDraft()">
            <input type="submit" value="{function="localize('Send', 'newsletter')"}">
        </div>
    </div>
</div>
</form>
