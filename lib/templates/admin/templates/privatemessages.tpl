<script type="text/javascript">
function removeSentPrivateMessage(id)
{
    $.msgBox({
        title: "{"Are you sure?"|localize}",
        content: "{"Do you really want to delete this message?"|localize}",
        type: "confirm",
        autoClose: true,
        opacity: 0.6,
        buttons: [{ value: "{"Yes"|localize:messages}" }, { value: "{"No"|localize:messages}" }, { value: "{"Cancel"|localize:messages}"}],
        success: function (result) {
            if (result == "{"Yes"|localize:messages}") {
                $.ajax({
                    url: '{$AJAX_URL}?display=privatemessages&cat=admin&action=remove_message_sent&messageid='+id,
                    data: '',
                    async: false,
                    success: function (response) { 

                        if (response.status == "success")
                        {
                            jQuery('#message_row_sent_'+id).remove();
                        }

                    },
                    dataType: 'json'
                   });
            }
        }
    });    
}

function removeReceivedPrivateMessage(id)
{
    $.msgBox({
        title: "{"Are you sure?"|localize}",
        content: "{"Do you really want to delete this message?"|localize}",
        type: "confirm",
        autoClose: true,
        opacity: 0.6,
        buttons: [{ value: "{"Yes"|localize:messages}" }, { value: "{"No"|localize:messages}" }, { value: "{"Cancel"|localize:messages}"}],
        success: function (result) {
            if (result == "{"Yes"|localize:messages}") {
                $.ajax({
                    url: '{$AJAX_URL}?display=privatemessages&cat=admin&action=remove_message_received&messageid='+id,
                    data: '',
                    async: false,
                    success: function (response) { 

                        if (response.status == "success")
                        {
                            jQuery('#message_row_received_'+id).remove();
                        }

                    },
                    dataType: 'json'
                   });
            }
        }
    });    
}

jQuery(document).ready(function($) {
    jQuery('#inbox_window_trigger').click(function () {
        jQuery('#inbox').slideToggle('slow');
    });
    
    jQuery('#outbox_window_trigger').click(function () {
        jQuery('#outbox').slideToggle('slow');
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
             {if="count($pmessages_list_received) < 1"}
                <tr>
                  <td colspan="4"><p style="text-align: center;">No messages in inbox</p></td>
                </tr>
             {else}
                
              {loop="$pmessages_list_received"}
                <tr id="message_row_received_{$i->id}">
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
             {if="count($pmessages_list_sent) < 1"}
                <tr>
                  <td colspan="4"><p style="text-align: center;">No messages in outbox</p></td>
                </tr>
             {else}
                
              {loop="$pmessages_list_sent"}
                <tr id="message_row_sent_{$i->id}">
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