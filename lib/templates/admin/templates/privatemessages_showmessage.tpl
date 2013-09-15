<script type="text/javascript">
$(document).ready(function () {
    $('#reply_message').submit(function () {
        panthera.jsonPOST( { data: '#reply_message', success: function (response) {
                if (response.status == "success")
                {
                    navigateTo('?display=privatemessages&cat=admin');
                }
            }
        });
    });
});
</script>

{include="ui.titlebar"}

<div class="grid-1">
     <div class="title-grid"><h3><b>{$message->sender}</b></h3></div>
     
     <div class="content-gird">
         <div class="message-content" style="font-family: Helvetica; font-size: 13px;">
             {$message_content}
         </div>
     </div>
</div>

<div class="grid-1">
        <form id="reply_message" action="{$AJAX_URL}?display=privatemessages&cat=admin&action=send_message" method="POST">
        <div class="title-grid">{if="$reply == 1"}{function="localize('Reply', 'pmessages')"}{else}{function="localize('Send more', 'pmessages')"}{/if}</div>
      <input type="text" name="title" value="{$message->title}" style="display: none;">
        <div class="content-gird">
             <textarea name="content" style="width: 99%; height: 150px;"></textarea><br><br>
             <input type="text" name="recipient_id" value="{if="$reply == 1"}{$message->sender_id}{else}{$message->recipient_id}{/if}" style="display: none;">
             <input type="submit" value="{function="localize('Send', 'pmessages')"}" style="float: right; margin-right: 7px;"><br>
        </div>
        </form>
</div> 