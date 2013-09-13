<script type="text/javascript">

function removeMessage(id)
{
    w2confirm('{function="localize('Are you sure you want to delete this message?', 'pmessages')"}', function (responseText) {
        
        if (responseText == 'Yes') {
            panthera.jsonGET( { url: '{$AJAX_URL}?display=privatemessages&cat=admin&action=remove_message&messageid='+id, messageBox: 'w2ui', success: function (response) {
                    if (response.status == 'success') {
                        jQuery('#message_row_received_'+id).remove();
                    }
                
                }
            });
        }
        
    });
}

function seenMessage(id)
{
    panthera.jsonGET( { url: '{$AJAX_URL}?display=privatemessages&cat=admin&action=seen_message&messageid='+id, messageBox: 'w2ui'});
}

$(document).ready(function () {
    $('#send_message').submit(function () {
        panthera.jsonPOST( { data: '#send_message', success: function (response) {
                if (response.status == "success")
                {
                    navigateTo('?display=privatemessages&cat=admin');
                    jQuery("#new_pmessage").hide();
                    jQuery("#fade").hide();
                } else {
                    jQuery('#message_error').slideDown();
                    jQuery('#message_error').html(response.message);
                }
            }
        });
    });
});
</script>

{include="ui.titlebar"}
       
       <div class="grid-1">
        <table class="gridTable">
            <thead>
                <tr>
                    <th>{function="localize('Title', 'pmessages')"}</th>
                    <th>{function="localize('From', 'pmessages')"}</th>
                    <th>{function="localize('Sent', 'pmessages')"}</th>
                    <th>{function="localize('Options', 'pmessages')"}</th>
                </tr>
            </thead>
            
            <tfoot>
                <tr>
                    <td colspan="4" class="rounded-foot-left"><em>{function="localize('Received messages', 'pmessages')"}</em></td>
                </tr>
            </tfoot>
            <tbody>
             {if="count($received) < 1"}
                
                <tr>
                  <td colspan="4"><p style="text-align: center;">{function="localize('No messages in inbox', 'pmessages')"}</p></td>
                </tr>
                
             {else}
                
              {loop="$received"}
                <tr id="received_{$value.id}" {if="!$value.seen"}style="font-weight: bold;{/if}">
                  <td><a onclick="{if="!$value.seen"}seenMessage({$value.id});{/if}navigateTo('{$AJAX_URL}?display=privatemessages&cat=admin&action=show_message&messageid={$value.id}')" style="cursor: hand; cursor: pointer;">{$value.title}</a></td>
                  <td>{$value.sender}</td>
                  <td>{$value.sent}</td>
                  <td><input type="button" value="{function="localize('Remove', 'pmessages')"}" onclick="removeMessage({$value.id})"></td>
                </tr>
              {/loop}
              
             {/if}
            </tbody>
         </table>
       </div>
       
       <div class="grid-1">
        <table class="gridTable">
            <thead>
                <tr>
                    <th>{function="localize('Title', 'pmessages')"}</th>
                    <th>{function="localize('To', 'pmessages')"}</th>
                    <th>{function="localize('Sent', 'pmessages')"}</th>
                    <th>{function="localize('Options', 'pmessages')"}</th>
                </tr>
            </thead>
            
            <tfoot>
                <tr>
                    <td colspan="4" class="rounded-foot-left"><em>{function="localize('Sent messages', 'pmessages')"} <button onclick="createPopup('_ajax.php?display=privatemessages&cat=admin&action=new_message', 900, 'new_pmessage')">Compose new one</button></em></td>
                </tr>
            </tfoot>
            <tbody>
             {if="count($sent) < 1"}
                
                <tr>
                  <td colspan="4"><p style="text-align: center;">{function="localize('No messages in outbox', 'pmessages')"}</p></td>
                </tr>
                
             {else}
                
              {loop="$sent"}
                <tr id="sent_{$value.id}">
                    <td><a onclick="createPopup('_ajax.php?display=privatemessages&cat=admin&action=show_message&messageid={$value.id}', 900, 'show_pmessage')" style="cursor: hand; cursor: pointer;">{$value.title}</a></td>
                    <td>{$value.sender}</td>
                  <td>{$value.sent}</td>
                  <td><input type="button" value="{function="localize('Remove', 'pmessages')"}" onclick="removeMessage({$value.id})"></td>
                </tr>
              {/loop}
              
             {/if}
            </tbody>
         </table>
      </div>
      
<div class="grid-2">
        <form id="send_message" action="{$AJAX_URL}?display=privatemessages&cat=admin&action=send_message" method="POST">
        <div class="title-grid" style="height: 25px;">{function="localize('Title', 'pmessages')"}: <input type="text" name="title"><span></span></div>
        <div class="content-gird">
             <textarea name="content" style="width: 99%; height: 150px;"></textarea><br><br>
             <input type="text" name="recipient_login" placeholder="{function="localize('Recipient login', 'pmessages')"}"> <input type="submit" value="{function="localize('Send', 'pmessages')"}">
        </div>
        </form>
</div> 