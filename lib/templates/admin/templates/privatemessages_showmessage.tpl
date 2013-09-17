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

{loop="$messages"}
    <div class="grid-1">
         <div class="title-grid"><h3><b><a href="#{$value.sender}" onclick="navigateTo('?display=users&cat=admin&action=account&uid={$interlocutor}');">{$value.sender}</a></b>&nbsp;-&nbsp;{function="elapsedTime($value.sent)"}{function="localize('ago')"}</h3></div>
         
         <div class="content-gird" {if="$value.sender_id == $user_id"} style="background-color: #F3F3F3;" {/if}>
             <div class="message-content" style="font-family: Helvetica; font-size: 13px;">
                 {$value.content}
             </div>
         </div>
    </div>
{/loop}

<div class="grid-1">
        <form id="reply_message" action="{$AJAX_URL}?display=privatemessages&cat=admin&action=send_message" method="POST">
        <div class="title-grid">{function="localize('Response', 'pmessages')"}</div>
      <input type="text" name="title" value="{$message->title}" style="display: none;">
        <div class="content-gird">
             <textarea name="content" style="width: 99%; height: 150px; font-family: Helvetica;"></textarea><br><br>
             <input type="text" name="recipient_id" value="{$interlocutor}" style="display: none;">
             <input type="submit" onclick="" value="{function="localize('Send', 'pmessages')"}" style="float: right; margin-right: 7px;"><br>
        </div>
        </form>
</div>