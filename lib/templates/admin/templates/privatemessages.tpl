<script type="text/javascript">

function seenMessage(id)
{
    panthera.jsonGET( { url: '{$AJAX_URL}?display=privatemessages&cat=admin&action=seen_message&messageid='+id, messageBox: 'w2ui'});
}

function removeMessages(id)
{
    w2confirm('{function="localize('Are you sure you want delete this group of messages?', 'pmessages')"}', function (responseText) {
        if (responseText == 'Yes')
        {
            panthera.jsonPOST( { url: '?{function="getQueryString('GET', 'action=remove_messages', '_')"}', data: 'messageid='+id, messageBox: 'w2ui', success: function (response) {
                    if (response.status == 'success')
                    {
                        navigateTo('?display=privatemessages&cat=admin');
                    }
                
                } 
            });
        }
    });
}

$(document).ready(function () {
    $('#send_message').submit(function () {
        panthera.jsonPOST( { data: '#send_message', messageBox: 'w2ui', success: function (response) {
                if (response.status == "success")
                {
                    navigateTo('{$AJAX_URL}?display=privatemessages&cat=admin');
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
                    <th>{function="localize('With', 'pmessages')"}</th>
                    <th>{function="localize('Date', 'pmessages')"}</th>
                    <th>{function="localize('Options', 'pmessages')"}</th>
                </tr>
            </thead>
            
            <tbody>
             {if="count($messages) < 1"}
                
                <tr>
                  <td colspan="4"><p style="text-align: center;">{function="localize('No messages in inbox', 'pmessages')"}</p></td>
                </tr>
                
             {else}
                
              {loop="$messages"}
                <tr id="messages_{$value.id}" {if="!$value.seen"}style="font-weight: bold;{/if}">
                  <td><a onclick="{if="!$value.seen"}seenMessage({$value.id});{/if}navigateTo('{$AJAX_URL}?display=privatemessages&cat=admin&action=show_message&messageid={$value.id}')" style="cursor: hand; cursor: pointer;">{$value.title} ({$value.count})</a></td>
                  <td>{$value.interlocutor}</td>
                  <td>{$value.sent} {function="localize('ago')"}</td>
                  <td>
                      <a href="#" onclick="removeMessages({$value.id})">
                         <img src="{$PANTHERA_URL}/images/admin/ui/delete.png" style="max-height: 22px;" alt="{function="localize('Remove')"}">
                      </a>
                  </td>
                </tr>
              {/loop}
              
             {/if}
            </tbody>
         </table>
       </div>
      
<div class="grid-1">
        <form id="send_message" action="{$AJAX_URL}?display=privatemessages&cat=admin&action=send_message" method="POST">
        <div class="title-grid" style="height: 25px;">{function="localize('Title', 'pmessages')"}: <input type="text" name="title"><span></span></div>
        <div class="content-gird">
             <textarea name="content" style="width: 99%; height: 150px;"></textarea><br><br>
             <input type="text" name="recipient_login" placeholder="{function="localize('Recipient login', 'pmessages')"}">
             <input type="button" value="{function="localize('Search', 'pmessages')"}">
             <input type="submit" value="{function="localize('Send', 'pmessages')"}" style="float: right; margin-right: 7px;">
        </div>
        </form>
</div> 