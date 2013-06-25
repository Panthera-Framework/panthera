<script type="text/javascript">
{include file="mce.tpl"}

/**
  * Jump to ajax page
  *
  * @author Mateusz Warzyński
  */

function jumpToAjaxPage(id)
{
    $.ajax({
            url: '{$AJAX_URL}?display=messages&action=display_list&cat={$category_name}&page='+id,
            data: '',
            async: false,
            success: function (response) {
                jQuery('#all_messages_window').html(response);
            },
            dataType: 'html'
           });
}


/**
  * Get message by id
  *
  * @author Mateusz Warzyński
  */

var windowLocks = new Array();

function editMessage(id)
{
    panthera.jsonPOST({ url: '{$AJAX_URL}?display=messages&action=get_msg&msgid='+id, data: '', success: function (response) {
            if (response.status == "success") {
                jQuery('#edit_msg_title').val(response.title);
                jQuery('#edit_msg_id').val(response.id);
                jQuery('#edit_msg_icon').val(response.icon);

                if (response.visibility == 0)
                        jQuery('#edit_msg_hidden').attr('checked', false);
                else
                        jQuery('#edit_msg_hidden').attr('checked', true);

                // init mce editor
                mceFocus("edit_msg_content");
                mceSetContent('edit_msg_content', response.message);

                jQuery('#message_window').hide('slow');
                jQuery('#edit_window').show('slow');
                windowLocks['message_window'] = true;
            }
        }
    });

    return false;

}

/**
  * Delete message by id
  *
  * @author Mateusz Warzyński
  */

function deleteMessage(id)
{
    panthera.jsonPOST({ url: '{$AJAX_URL}?display=messages&action=remove_msg&msgid='+id, data: '', messageBox: 'userinfoBox', success: function (response) {
            if (response.status == "success")
                jQuery('#msg_'+id+'_row').remove();
        }
    });

    return false;
}

/**
  * Init some functionality
  *
  * @author Mateusz Warzyński
  */

jQuery(document).ready(function($) {
    mceInit('edit_msg_content');
    mceInit('message_content');

    jQuery('#message_window_trigger').click(function () {
         if (windowLocks['message_window'] == true)
            return false;

        jQuery('#message_window').slideToggle('slow');
    });

    jQuery('#all_messages_window_trigger').click(function () {
         if (windowLocks['all_messages_window'] == true)
            return false;

        jQuery('#all_messages_window').slideToggle('slow');
    });

    $('#post_new').submit(function () {

        panthera.jsonPOST({ url: '{$AJAX_URL}?display=messages&action=new_msg&cat={$category_name}', success: function (response) {
                if (response.status == "success") {
                    jQuery('#message_window').hide('slow');
                    jumpToAjaxPage(0);
                }
            }
        });
       return false;
    });


    jQuery("#edit_msg_form").ajaxForm({
        url: '{$AJAX_URL}?display=messages&action=edit_msg&cat={$category_name}', type: 'post', dataType: 'json', success: function (response) {
            if (response.status == "success")
            {
                jQuery('#edit_msg_error_div').hide();

                // update table row
                if (jQuery('#msg_'+response.id+'_title'))
                {
                    jQuery('#msg_'+response.id+'_title').html(response.title);
                    jQuery('#msg_'+response.id+'_mod_time').html(response.mod_time);
                    jQuery('#msg_'+response.id+'_visibility').html(response.visibility);
                }
                windowLocks['message_window'] = false;
                jQuery('#edit_window').slideUp('slow');
            } else {
                jQuery('#edit_msg_error_div').slideDown('slow');
                jQuery('#edit_msg_error').html(response.error);
            }
        }
    });

    jQuery('#edit_msg_cancel').click(function() {
        jQuery('#edit_window').slideUp('slow');
        windowLocks['message_window'] = false;
    });

});

function upload_file_callback_edit(link, mime, type, directory, id, description, author)
{
    if(type != 'image')
    {
        alert('{"Selected file is not a image"|localize:gallery}');
        return false;
    }

    $('#edit_msg_icon').val(link);
}

function upload_file_callback_new(link, mime, type, directory, id, description, author)
{
    if(type != 'image')
    {
        alert('{"Selected file is not a image"|localize:gallery}');
        return false;
    }

    $('#message_icon').val(link);
}

</script>

<style>
.quickMsgIcon {
    width: 50px;
    height: 50px;
}
</style>
    <div class="titlebar">{$category_title|localize} - {$category_description|localize}</div><br>

    <div class="msgSuccess" id="userinfoBox_success"></div>
    <div class="msgError" id="userinfoBox_failed"></div>

    <h1 id="message_window_trigger" style="cursor: hand; cursor: pointer;">{"Create new"|localize:qmessages}</h1>
    <div id="message_window">
       <form action="{$AJAX_URL}?display=messages&action=new_msg" method="POST" id="post_new">
        <div class="grid-1">
            <div class="title-grid" style="height: 30px;">{"Title"|localize:qmessages}: <input type="text" name="message_title"></div>
            <div class="content-gird">
                <textarea name="message_content" id="message_content"></textarea>
            </div>
        </div>
        <div class="grid-2">
          <div class="title-grid">{"Icon"|localize:qmessages}</div>
          <div class="content-gird">
                <input type="text" name="message_icon" id="message_icon" style="width: 100%;"> <input type="button" value="{"Upload file"|localize:qmessages}" onclick="createPopup('_ajax.php?display=upload&popup=true&callback=upload_file_callback_new', 1024, 'upload_popup');" style="float: right;"><br><br>
           </div>
        </div>

        <div class="grid-2" style="height: 160px;">
               <div class="title-grid">{"Options"|localize:messages}</div>

               <div class="content-gird">
                    <table class="gridTable" style="border: 0px">
                        <tbody>
                            <tr>
                                <td>{"Is not hidden"|localize:qmessages}</td>
                                <td><input type="checkbox" name="message_hidden" value="1"></td>
                            </tr>
                            <tr>
                                <td>{"Add"|localize:messages}</td>
                                <td><input type="submit" value="{"Add as"|localize:qmessages} {$username}"></td>
                            </tr>
                        </tbody>
                    </table>
               </div>
         </div>

       </form>
    </div>

    <br><br>

    <div id="edit_window" style="display: none;">
       <form action="{$AJAX_URL}?display=messages&action=edit_msg" method="POST" id="edit_msg_form">
       <h2>{"Edit a message"|localize:qmessages}</h2>
        <div class="grid-1">
            <div class="title-grid" style="height: 30px;">{"Title"|localize:qmessages}: <input type="text" name="edit_msg_title" id="edit_msg_title" value="" style="width: 300px;"></div>
            <div class="content-gird">
                <textarea name="edit_msg_content" id="edit_msg_content" style="width: 100%;"></textarea>
                <input type="hidden" id="edit_msg_id" name="edit_msg_id"><br>
            </div>
        </div>
        <div class="grid-2">
          <div class="title-grid">{"Icon"|localize:qmessages}</div>
          <div class="content-gird">
                <input type="text" name="edit_msg_icon" id="edit_msg_icon" style="width: 100%;">
                <input type="button" value="{"Upload file"|localize}" onclick="createPopup('_ajax.php?display=upload&popup=true&callback=upload_file_callback_edit', 1024, 'upload_popup');" style="float: right;"><br><br>
           </div>
        </div>

        <div class="grid-2" style="height: 160px;">
               <div class="title-grid">{"Options"|localize:messages}</div>

               <div class="content-gird">
                    <table class="gridTable" style="border: 0px">
                        <tbody>
                            <tr>
                                <td>{"Is not hidden"|localize:qmessages}</td>
                                <td><input type="checkbox" name="edit_msg_hidden" value="1" id="edit_msg_hidden"></td>
                            </tr>
                            <tr>
                                <td>{"Edit"|localize:qmessages}</td>
                                <td><input type="submit" value="{"Edit as"|localize} {$username}"> <input type="button" value="{"Cancel"|localize:messages}" id="edit_msg_cancel"></td>
                            </tr>
                        </tbody>
                    </table>
               </div>
         </div>

       </form>
    </div>

        <br><br>
        <h1 id="all_messages_window_trigger" style="cursor: hand; cursor: pointer;">{"All elements"|localize:qmessages}</h1>
        <div id="all_messages_window" style="display: none;">
          <div class="grid-1">
            <table class="gridTable">
            <thead>
                <tr>
                    <th scope="col" class="rounded-company">ID</th>
                    <th scole="col" class="rounded-q1">{"Thumbnail"|localize:qmessages}</td>
                    <th scope="col" class="rounded-q1">{"Title"|localize:qmessages}</th>
                    <th scope="col" class="rounded-q1">{"Author"|localize:qmessages}</th>
                    <th scope="col" class="rounded-q1">{"Last modification"|localize:qmessages}</th>
                    <th scope="col" class="rounded-q1">{"Visibility"|localize:qmessages}</th>
                    <th scope="col" class="rounded-q1">{"Options"|localize:messages}</th>
                </tr>
            </thead>
                <tfoot>
                <tr>
                    <td colspan="8" class="rounded-foot-left"><em>{"Messages"|localize:qmessages} {$page_from}-{$page_to},
                        {foreach from=$pager key=page item=active}
                            {if $active == true}
                            <a href="#" onclick="jumpToAjaxPage({$page}); return false;"><b>{$page+1}</b></a>
                            {else}
                            <a href="#" onclick="jumpToAjaxPage({$page}); return false;">{$page+1}</a>
                            {/if}
                        {/foreach}

                    <input type="button" value="{"Manage permissions"|localize:messages}" onclick="createPopup('_ajax.php?display=acl&popup=true&name=can_qmsg_manage_{$category_name}', 1024, 'upload_popup');" style="float: right;">

                    </em></td>
                </tr>
            </tfoot>
            <tbody>
                {foreach from=$messages_list key=k item=i}
                <tr id="msg_{$i.id}_row"{if $i.special == True} class="message_special"{/if}>
                    <td style="width: 28px;">{$i.id}</td>
                    <td style="width: 60px;">{if !empty($i.icon)}<img src="{$i.icon}" class="quickMsgIcon">{/if}</td>
                    <td id="msg_{$i.id}_title"><a href="#" onclick="editMessage({$i.id}); return false;">{$i.title}</a></td>
                    <td>{$i.author_login}</td>
                    <td id="msg_{$i.id}_mod_time">{$i.mod_time}</td>
                    <td id="msg_{$i.id}_visibility">{$i.visibility}</td>
                    <td><input type="button" value="{"Delete"|localize:messages}" onclick="deleteMessage({$i.id}); return false;"> <input type="button" value="{"Edit"|localize:qmessages}" onclick="editMessage({$i.id}); return false;"> <input type="button" value="{"Manage permissions"|localize:messages}" onclick="createPopup('_ajax.php?display=acl&popup=true&name=can_qmsg_edit_{$i.id}', 1024, 'upload_popup');"></td>
                </tr>
                {/foreach}
            </tbody>
        </table>
      </div>
     </div>
