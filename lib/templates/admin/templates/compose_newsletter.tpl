<script>
jQuery(document).ready(function($) {
    {include file="mce.tpl"}
    mceInit('content_textarea');
    
    $('#newsletter_form').submit(function(event){
        event.preventDefault();
        panthera.jsonPOST({ data: '#newsletter_form', messageBox: 'userinfoBox'});
    });
});


</script>

{include="ui.titlebar"}

<div class="grid-1">
    <!-- messages box -->
    <div class="msgSuccess" id="userinfoBox_success"></div>
    <div class="msgError" id="userinfoBox_failed"></div>

        <form id="newsletter_form" action="{$AJAX_URL}?display=compose_newsletter&cat=admin&nid={$nid}" method="POST">
        <div class="title-grid" style="height: 25px;">{function="localize('Title', 'newsletter')"}: <input type="text" value="" name="title"><span></span></div>
        <div class="content-gird">
             <textarea name="content" id="content_textarea"></textarea><br><br>
             <input type="button" value="{function="localize('Subscribers', 'newsletter')"}" onclick="createPopup('?display=newsletter_users&cat=admin&nid={$nid}', 1024);"> <input type="submit" value="{function="localize('Send', 'newsletter')"}">
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
     
        {if="count($messages_queue) > 0"}
                <table class="gridTable" style="border: 0px">
                    <tbody>
                    {loop="$messages_queue"}
                        <tr><td>{$value.title|strCut:20}</td><td>{$value.created}</td></tr>
                    {/loop}
                    </tbody>
                </table>
        {else}
            {function="localize('No messages queued to send', 'newsletter')"}
        {/if}
     
     </div>
</div>

