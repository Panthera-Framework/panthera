{"pmessages"|localizeDomain}
{if $action eq 'show_message'}
<style>
#message_window {
      width: 91%;
      background-color: rgb(221, 243, 255);
      padding: 5px;
      border: 1px solid #d4d4d4;
      font-size: 11px;
      font-family: 'lucida grande',tahoma,verdana,arial,sans-serif;
      padding: 20px;
      margin: 20px;
}
</style>

<article>
<div class="paHeader">
      <div class="paTitle">{"Private Message"|localize:pmessages}</div>
      <div class="paDescription">{$message->sender} {" sent a message to "|localize:pmessages} {$message->recipient}.</div>
</div>

<div class="paLine"></div>

<article>
    <div class="text-section">
        <center><h2>{$message->title}</h2></center>
          <div id="message_window">
           <table>
            {foreach from=$message_content key=k item=i}
             <tr> 
                 <td>{$i}</td>
             </tr>
            {/foreach}
           </table> 
          </div>
    </div>
</article>

{elseif $action eq 'new_message'}
<script type="text/javascript">
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
</script>

<style>
#message_content {
      width: 558px;
      height: 150px;
}
</style>

<article>
<div class="paHeader">
      <div class="paTitle">{"Send message"|localize:pmessages}</div>
      <div class="paDescription">{"Send a new message to your friend."|localize:pmessages}</div>
</div>

<div class="paLine"></div>

<article>
    <div class="text-section">
      <ul class="states">
        <li class="error" style="display: none;" id="message_error"></li>
      </ul>
      <div id="message_window">
       <form action="?display=privatemessages&cat=admin&action=send_message" method="POST" id="send_message">
          <h1><input type="text" name="title" value="{"Title"|localize:pmessages}"></h1>
          <textarea name="content" id="message_content"></textarea>
            <br><br>
          {"Recipient"|localize:pmessages}:<input type="text" name="recipient_login" value="  login" style="width: 500px;"><input type="submit" value="{"Send"|localize:pmessages}" style="float: right;">
          
          <input type="text" style="display: none;" name="sender_id" value="{$sender_id}">
       </form>
      </div>
    </div>
</article>

{else}
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

<article>
<div class="paHeader">
      <div class="paTitle">{"Private Messages"|localize:pmessages}</div>
      <div class="paDescription">{"Your Private Messages"|localize:pmessages}</div>
</div>

<div class="paLine"></div>

<article>
    <div class="text-section">
       <h1 style="cursor: hand; cursor: pointer;" onclick="createPopup('_ajax.php?display=privatemessages&cat=admin&action=new_message', 900, 'new_pmessage')">{"Send new message"|localize:pmessages}</h1>
       <h1 id="inbox_window_trigger" style="cursor: hand; cursor: pointer;">{"Inbox"|localize:pmessages}</h1>
        <div id="inbox" style="display: none;">
         <table id="rounded-corner" summary="" style="width: 95%;">
            <thead>
                <tr>
                    <th scope="col" class="rounded-company" style="width: 300px;">{"Title"|localize:pmessages}</th>
                    <th scope="col" class="rounded-q1" style="width: 300px;">{"From"|localize:pmessages}</th>
                    <th scope="col" class="rounded-q1">{"Sent"|localize:pmessages}</th>
                    <th scope="col" class="rounded-q1">{"Options"|localize:pmessages}</th>
                </tr>
            </thead>
            
            <tfoot>
                <tr>
                	<td colspan="4" class="rounded-foot-left"><em>{"Received messages"|localize:pmessages}</em></td>
                </tr>
            </tfoot>
            <tbody>
            {foreach from=$pmessages_list_received key=k item=i}
              {if $i->visibility_recipient eq 1}
                <tr id="message_row_received_{$i->id}">
                	<td><a onclick="createPopup('_ajax.php?display=privatemessages&cat=admin&action=show_message&messageid={$i->id}', 900, 'show_pmessage')" style="cursor: hand; cursor: pointer;">{$i->title}</a></td>
                	<td>{$i->sender}</td>
                  <td>{$i->sent}</td>
                  <td><input type="button" value="{"Remove"|localize:pmessages}" onclick="removeReceivedPrivateMessage({$i->id})"></td>
                </tr>
              {/if}
            {/foreach}
            </tbody>
         </table>
        </div>
        
       <h1 id="outbox_window_trigger" style="cursor: hand; cursor: pointer;">{"Outbox"|localize:pmessages}</h1>
        <div id="outbox" style="display: none;">
         <table id="rounded-corner" summary="" style="width: 95%;">
            <thead>
                <tr>
                    <th scope="col" class="rounded-company" style="width: 300px;">{"Title"|localize:pmessages}</th>
                    <th scope="col" class="rounded-q1" style="width: 300px;">{"To"|localize:pmessages}</th>
                    <th scope="col" class="rounded-q1">{"Sent"|localize:pmessages}</th>
                    <th scope="col" class="rounded-q1">{"Options"|localize:pmessages}</th>
                </tr>
            </thead>
            
            <tfoot>
                <tr>
                	<td colspan="4" class="rounded-foot-left"><em>{"Sent messages"|localize:pmessages}</em></td>
                </tr>
            </tfoot>
            <tbody>
            {foreach from=$pmessages_list_sent key=k item=i}
              {if $i->visibility_sender eq 1}
                <tr id="message_row_sent_{$i->id}">
                	<td><a onclick="createPopup('_ajax.php?display=privatemessages&cat=admin&action=show_message&messageid={$i->id}', 900, 'show_pmessage')" style="cursor: hand; cursor: pointer;">{$i->title}</a></td>
                	<td>{$i->recipient}</td>
                  <td>{$i->sent}</td>
                  <td><input type="button" value="{"Remove"|localize:pmessages}" onclick="removeSentPrivateMessage({$i->id})"></td>
                </tr>
              {/if}
            {/foreach}
            </tbody>
        </table>
        </div>
    </div>
</article> 
{/if}
