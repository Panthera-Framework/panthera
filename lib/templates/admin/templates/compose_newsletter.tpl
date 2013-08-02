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

<div class="titlebar">{"Newsletter"|localize} - {"Compose a new message"|localize}</div>

<div class="grid-1">
    <!-- messages box -->
    <div class="msgSuccess" id="userinfoBox_success"></div>
    <div class="msgError" id="userinfoBox_failed"></div>

        <form id="newsletter_form" action="{$AJAX_URL}?display=compose_newsletter&cat=admin&nid={$nid}" method="POST">
        <div class="title-grid">{"Title"|localize}: <input type="text" value="" name="title"><span></span></div>
        <div class="content-gird">
             <textarea name="content" id="content_textarea"></textarea><br><br>
             <input type="button" value="{"Subscribers"|localize}" onclick="createPopup('?display=newsletter_users&cat=admin&nid={$nid}', 1024);"> <input type="submit" value="{"Send"|localize}">
        </div>
        </form>
</div>

<div class="grid-2">
    <div class="title-grid">{"Recently subscribed by"|localize}</div>
    
     <div class="content-gird">
        {if count($recent_subscribers) > 0}
            <table class="gridTable" style="border: 0px">
            <tbody>
                {foreach from=$recent_subscribers key=k item=v}
                    <tr><td>{$v.address}</td><td>{$v.added}</td></tr>
                {/foreach}
                </tbody>
            </table>
        
        {else}
            {"There no any users subscribing this newsletter"|localize}
        {/if}
     </div>
</div>

<div class="grid-2">
    <div class="title-grid">{"Queued messages to send"|localize}</div>
    
     <div class="content-gird">
     
        {if count($messages_queue) > 0}
                <table class="gridTable" style="border: 0px">
                    <tbody>
                    {foreach from=$messages_queue key=k item=v}
                        <tr><td>{$v.title|strCut:20}</td><td>{$v.created}</td></tr>
                    {/foreach}
                    </tbody>
                </table>
        {else}
            {"No messages queued to send"|localize}
        {/if}
     
     </div>
</div>

