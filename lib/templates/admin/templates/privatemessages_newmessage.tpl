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

    <h1>{function="localize('Send message', 'pmessages')"}</h1>


      <div id="message_window">
       <form action="?display=privatemessages&cat=admin&action=send_message" method="POST" id="send_message">
          <h1><input type="text" name="title" value="{function="localize('Title', 'pmessages')"}"></h1>
          <textarea name="content" id="message_content"></textarea>
            <br><br>
          {function="localize('Recipient', 'pmessages')"}:<input type="text" name="recipient_login" value="  login" style="width: 500px;"><input type="submit" value="{function="localize('Send', 'pmessages')"}" style="float: right;">
          
          <input type="text" style="display: none;" name="sender_id" value="{$sender_id}">
       </form>
      </div>