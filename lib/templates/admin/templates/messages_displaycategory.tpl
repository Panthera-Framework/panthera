<script type="text/javascript">
{include file="mce.tpl"}

/**
  * Jump to ajax page
  *
  * @author Mateusz Warzyński
  */

function jumpToAjaxPage(id)
{
    panthera.jsonGET({ url: '{$AJAX_URL}?display=messages&action=display_category&language={$language}&type=ajax&cat={$category_name}&page='+id, data: '', success: function (response) {
    
            $('#all_messages_window').html('');
    
            for (item in response.response)
            {
                icon = "";
                i = response.response[item];
                
                if (i.icon != undefined && i.icon != "")
                    icon = '<img src="'+i.icon+'" class="quickMsgIcon">';
               
                $('#all_messages_window').append('<tr><tr id="msg_'+i.id+'_row"><td style="width: 28px;">'+i.id+'</td><td style="width: 60px;">'+icon+'</td><td id="msg_'+i.id+'_title"><a href="#" onclick="editMessage('+i.id+'); return false;">'+i.title+'</a></td><td>'+i.author_login+'</td><td id="msg_'+i.id+'_mod_time">'+i.mod_time+'</td><td id="msg_'+i.id+'_visibility">'+i.visibility+'</td><td><input type="button" value="{"Delete"|localize:messages}" onclick="deleteMessage('+i.id+'); return false;"> <input type="button" value="{"Edit"|localize:qmessages}" onclick="editMessage('+i.id+'); return false;"> <input type="button" value="{"Manage permissions"|localize:messages}" onclick="createPopup(\'_ajax.php?display=acl&popup=true&name=can_qmsg_edit_'+i.id+'\', 1024, 550);"></td></tr>');
            }
        }
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
    panthera.jsonPOST({ url: '{$AJAX_URL}?display=messages&action=get_msg&language={$language}&msgid='+id, data: '', success: function (response) {
            if (response.status == "success") {
                $('#edit_msg_title').val(response.title);
                $('#edit_msg_id').val(response.id);
                $('#edit_msg_icon').val(response.icon);
                $('#edit_language').val(response.language);

                if (response.visibility == 0)
                    $('#edit_msg_hidden').attr('checked', false);
                else
                    $('#edit_msg_hidden').attr('checked', true);

                // init mce editor
                mceFocus("edit_msg_content");
                mceSetContent('edit_msg_content', response.message);

                $('#message_window').hide('slow');
                $('#edit_window').show('slow');
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

    $('#post_new').submit(function () {
        panthera.jsonPOST({ data: '#post_new', mce: 'tinymce_all', messageBox: 'userinfoBox', success: function (response) {
                if (response.status == "success") {
                    jQuery('#message_window').hide('slow');
                    jumpToAjaxPage(0);
                }
            }
        });
       return false;
    });

    
    $("#edit_msg_form").submit(function () {
       
       panthera.jsonPOST({ url: '{$AJAX_URL}?display=messages&action=edit_msg&cat={$category_name}&language={$language}', messageBox: 'userinfoBox', success: function (response) {
                if (response.status == "success")
                {
                    jumpToAjaxPage(0);
                    $('#edit_window').slideUp('slow');
                }
            }
        });
        
        return false;
    });

    $('#edit_msg_cancel').click(function() {
        $('#edit_window').slideUp('slow');
        $('#message_window').slideDown('slow');
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
    
    <div class="grid-1" id="languagesList" style="position: relative;">
          <div class="title-grid">{"Messages in other languages in this category"|localize:qmessages}<span></span></div>
          <div class="content-table-grid">
              <table class="insideGridTable">
                <tfoot>
                    <tr>
                        <td colspan="3"><small>{"Select language from above list to get list of messages in other language"|localize:qmessages}</small></td>
                    </tr>
                </tfoot>
            
                <tbody>
                    {foreach from=$languages key=k item=i}
                        <tr>
                            <td style="padding: 10px; border-right: 0px; width: 1%;"><a href="#{$k}" onclick="navigateTo('?display=messages&action=display_category&cat={$category_name}&language={$k}');">{$k}</a></td>
                            <td style="width: 60px; padding: 10px; border-right: 0px;"></td>
                        </tr>
                    {/foreach}
                </tbody>
            </table>
         </div>
       </div>
       <br>
    
    <div id="message_window">
       <form action="{$AJAX_URL}?display=messages&action=new_msg&cat={$category_id}" method="POST" id="post_new">
        <div class="grid-1">
            <div class="title-grid" style="height: 30px;">{"Title of a new message"|localize:qmessages}: &nbsp;<input type="text" name="message_title" style="height: 20px; width: 250px; margin-top: 3px;"></div>
            <div class="content-table-grid" style="padding: 0px;">
                <textarea name="message_content" id="message_content" style="width: 100%; height: 450px;"></textarea>
            </div>
        </div>
        
        <div class="grid-1" style="height: 160px; margin-bottom: 40px;">
               <div class="title-grid">{"Options"|localize:messages}</div>

               <div class="content-table-grid">
                    <table class="insideGridTable" style="border: 0px">
                        <tbody>
                            <tr>
                                <td>{"Icon"|localize:qmessages}<br><small>{"Optional icon, depends on if your website module support this function"|localize:qmessages}</small></td>
                                <td style="border-right: 0px;"><input type="text" name="message_icon" id="message_icon" style="width: 300px;"> &nbsp;<input type="button" value="{"Upload file"|localize:qmessages}" onclick="createPopup('_ajax.php?display=upload&popup=true&callback=upload_file_callback_new', 1024, 550);"></td>
                            </tr>
                            <tr>
                                <td>{"Is not hidden"|localize:qmessages}<br><small>{"If checked, this message will not be published on your website"|localize:qmessages}</small></td>
                                <td style="border-right: 0px;"><input type="checkbox" name="message_hidden" value="1"></td>
                            </tr>
                            <tr>
                                <td>{"SEO name"|localize:qmessages}<br><small>{"A name friendly to remember, and friendly for search engines"|localize:qmessages}</small></td>
                                <td style="border-right: 0px;"><input type="text" name="url_id" style="width: 300px;"></td>
                            </tr>
                            <tr>
                                <td style="border-bottom: 0px;">&nbsp;</td>
                                <td style="border-right: 0px; border-bottom: 0px;"><input type="submit" value="{"Add as"|localize:qmessages} {$username}"></td>
                            </tr>
                        </tbody>
                    </table>
               </div>
         </div>
        
       </form>
    </div>
    
    <div id="edit_window" style="display: none;">
       <form action="{$AJAX_URL}?display=messages&action=edit_msg&cat={$category_id}" method="POST" id="edit_msg_form">
        <div class="grid-1">
            <div class="title-grid" style="height: 30px;">{"Edit a message"|localize:qmessages}: <input type="text" name="edit_msg_title" id="edit_msg_title" value="" style="width: 300px; height: 20px; margin-top: 3px;"></div>
            <div class="content-gird" style="padding: 0px;">
                <textarea name="edit_msg_content" id="edit_msg_content" style="width: 100%; height: 350px;"></textarea>
                <input type="hidden" id="edit_msg_id" name="edit_msg_id"><br>
            </div>
        </div>

       <div class="grid-1" style="height: 160px; margin-bottom: 40px;">
               <div class="title-grid">{"Options"|localize:messages}</div>
               <div class="content-table-grid">
                    <table class="insideGridTable" style="border: 0px">
                        <tbody>
                            <tr>
                                <td>{"Icon"|localize:qmessages}<br><small>{"Optional icon, depends on if your website module support this function"|localize:qmessages}</small></td>
                                <td style="border-right: 0px;"><input type="text" name="message_icon" id="message_icon" style="width: 300px;"> &nbsp;<input type="button" value="{"Upload file"|localize:qmessages}" onclick="createPopup('_ajax.php?display=upload&popup=true&callback=upload_file_callback_new', 1024, 550);"></td>
                            </tr>
                            <tr>
                                <td>{"Is not hidden"|localize:qmessages}<br><small>{"If checked, this message will not be published on your website"|localize:qmessages}</small></td>
                                <td style="border-right: 0px;"><input type="checkbox" name="edit_msg_hidden" id="edit_msg_hidden" value="1"></td>
                            </tr>
                            <tr>
                                <td>{"SEO name"|localize:qmessages}<br><small>{"A name friendly to remember, and friendly for search engines"|localize:qmessages}</small></td>
                                <td style="border-right: 0px;"><input type="text" name="edit_url_id" style="width: 300px;"></td>
                            </tr>
                            <tr>
                                <td>{"Language"|localize:qmessages}<br><small>{"Save this message in selected language"|localize:qmessages}</small></td>
                                <td style="border-right: 0px;">
                                    <select name="edit_language" id="edit_language"">
                                    {foreach from=$languages key=k item=i}
                                        <option value="{$k}">{$k}</option>
                                    {/foreach}
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td style="border-bottom: 0px;">&nbsp;</td>
                                <td style="border-right: 0px; border-bottom: 0px;"><input type="submit" value="{"Edit as"|localize:qmessages} {$username}"> <input type="button" value="{"Cancel"|localize:qmessages}" id="edit_msg_cancel"></td>
                            </tr>
                        </tbody>
                    </table>
               </div>
         </div>

       </form>
    </div>

        <div style="margin-top: 120px;">
          <div class="grid-1">
            <table class="gridTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>{"Thumbnail"|localize:qmessages}</td>
                    <th>{"Title"|localize:qmessages}</th>
                    <th>{"Author"|localize:qmessages}</th>
                    <th>{"Last modification"|localize:qmessages}</th>
                    <th>{"Visibility"|localize:qmessages}</th>
                    <th>{"Options"|localize:messages}</th>
                </tr>
            </thead>
                <tfoot>
                <tr>
                    <td colspan="8" class="rounded-foot-left"><em>{"Messages"|localize:qmessages} <span id="page_from">{$page_from}</span>-<span id="page_to">{$page_to}</span>,
                        {foreach from=$pager key=page item=active}
                            {if $active == true}
                            <a href="#" onclick="jumpToAjaxPage({$page}); return false;" id="page_{$page+1}"><b>{$page+1}</b></a>
                            {else}
                            <a href="#" onclick="jumpToAjaxPage({$page}); return false;" id="page_{$page+1}">{$page+1}</a>
                            {/if}
                        {/foreach}

                    <input type="button" value="{"Manage permissions"|localize:messages}" onclick="createPopup('_ajax.php?display=acl&popup=true&name=can_qmsg_manage_{$category_name}', 1024, 550);" style="float: right;">

                    </em></td>
                </tr>
            </tfoot>
            <tbody id="all_messages_window">
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
