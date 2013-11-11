{$site_header}

<!-- CSS styles -->
<style type="text/css">
.formTable tbody td {
    padding-right: 0px;
}
</style>

<script type="text/javascript">
function initEditor() {}

/**
  * Get message by id
  *
  * @author Mateusz Warzyński
  */

function editMessage(id)
{
    panthera.jsonPOST({ url: '{$AJAX_URL}?display=messages&cat=admin&action=get_msg&language={$language}&msgid='+id, data: '', messageBox: false, success: function (response) {
            if (response.status == "success") {
                panthera.popup.create('element:#editMessagePopup');
                mceInit('edit_msg_content');
            
                $('#edit_msg_title').val(response.title);
                $('#edit_msg_id').val(response.id);
                $('#edit_msg_icon').val(response.icon);
                $('#edit_language').val(response.language);
                $('#edit_url_id').val(response.url_id);

                if (response.visibility == 0)
                    $('#edit_msg_hidden').attr('checked', false);
                else
                    $('#edit_msg_hidden').attr('checked', true);

                // init mce editor
                mceFocus("edit_msg_content");
                mceSetContent('edit_msg_content', response.message);
                
                $('#message_window').hide('slow');
                $('#edit_window').show('slow');
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
    panthera.confirmBox.create('{function="localize('Are you sure you want delete this message?', 'qmessages')"}', function (responseText) {
        if (responseText == 'Yes')
        {
            panthera.jsonPOST({ url: '{$AJAX_URL}?display=messages&cat=admin&action=remove_msg&msgid='+id, data: '', messageBox: 'userinfoBox', success: function (response) {
                    if (response.status == "success")
                        jQuery('#msg_'+id+'_row').remove();
                }
            });
        }
    });

    return false;
}
</script>
{function="uiMce::display()"}
<script type="text/javascript">
function upload_file_callback_edit(link, mime, type, directory, id, description, author)
{
    if(type != 'image')
    {
        alert('{function="localize('Selected file is not a image', 'gallery')"}');
        return false;
    }

    $('#edit_msg_icon').val(link);
}

function upload_file_callback_new(link, mime, type, directory, id, description, author)
{
    if(type != 'image')
    {
        alert('{function="localize('Selected file is not a image', 'gallery')"}');
        return false;
    }

    $('#message_icon').val(link);
}

</script>

{include="ui.titlebar"}

<div id="topContent">
    {$uiSearchbarName="uiTop"}
    {include="ui.searchbar"}
    
    <div class="separatorHorizontal"></div>
    
    <div class="searchBarButtonArea">
    
        <span data-searchbardropdown="#searchDropdown" id="searchDropdownSpan" style="position: relative; cursor: pointer;">
             <input type="button" value="{function="localize('Switch language', 'custompages')"}">
        </span>

        <div id="searchDropdown" class="searchBarDropdown searchBarDropdown-tip searchBarDropdown-relative">
            <ul class="searchBarDropdown-menu">
            {loop="$languages"}
                <li style="text-align: left;"><a href="#{$key}" onclick="navigateTo('?display=messages&cat=admin&action=display_category&category={$category_name}&language={$key}');">{$key}</a></li>
            {/loop}
            </ul>
        </div>
    
        <input type="button" value="{function="localize('Post a new message', 'custompages')"}" onclick="panthera.popup.toggle('element:#createNewMessagePopup')">
    </div>
</div>

<div id="popupOverlay" style="text-align: center; padding-top: 20px; padding-bottom: 0px;"></div>

<!-- Editing a message -->
<div style="display: none;" id="editMessagePopup">
    <form action="{$AJAX_URL}?display=messages&cat=admin&action=edit_msg&category={$category_id}" method="POST" id="edit_msg_form">
        <table class="formTable" style="margin: 0 auto; margin-bottom: 25px;">
            <thead>
                <tr>
                    <td colspan="2" class="formTableHeader" style="padding-top: 0px; padding-bottom: 30px;">
                        <p style="color: #e5ebef; padding: 0px; margin: 0px; margin-left: 30px;">{function="localize('Editing a message', 'custompages')"}</p>
                    </td>
                </tr>
            </thead>
            
            <tbody>
                <tr>
                    <th>{function="localize('Title', 'qmessages')"}:</th>
                    <td><input type="text" name="edit_msg_title" id="edit_msg_title"></td>
                </tr>
            
                <tr>
                    <th style="font-weight: 100;"><b>{function="localize('Icon', 'qmessages')"}:</b><br><small>{function="localize('Optional icon, depends on if your website module support this function', 'qmessages')"}</small></th>
                    <td style="border-right: 0px;"><input type="text" name="message_icon" id="message_icon" style="width: 300px;"> &nbsp;<input type="button" value="{function="localize('Upload file', 'qmessages')"}" onclick="createPopup('_ajax.php?display=upload&cat=admin&popup=true&callback=upload_file_callback_new', 1024, 550);"></td>
                </tr>
                <tr>
                    <th style="font-weight: 100;"><b>{function="localize('Is not hidden', 'qmessages')"}:</b><br><small>{function="localize('If checked, this message will not be published on your website', 'qmessages')"}</small></th>
                    <td style="border-right: 0px;"><input type="checkbox" name="edit_msg_hidden" id="edit_msg_hidden" value="1"></td>
                </tr>
                <tr>
                    <th style="font-weight: 100;"><b>{function="localize('SEO name', 'qmessages')"}:</b><br><small>{function="localize('A name friendly to remember, and friendly for search engines', 'qmessages')"}</small></th>
                    <td style="border-right: 0px;"><input type="text" name="edit_url_id" id="edit_url_id" style="width: 300px;"></td>
                </tr>
                <tr>
                    <th style="font-weight: 100;"><b>{function="localize('Language', 'qmessages')"}:</b><br><small>{function="localize('Save this message in selected language', 'qmessages')"}</small></th>
                    <td style="border-right: 0px;">
                        <select name="edit_language" id="edit_language"">
                            {loop="$languages"}
                            <option value="{$key}">{$key}</option>
                            {/loop}
                        </select>
                    </td>
                </tr>
                
                <tr>
                    <th colspan="2">
                        <textarea name="edit_msg_content" id="edit_msg_content" style="width: 100%; height: 350px;"></textarea>
                        <input type="hidden" id="edit_msg_id" name="edit_msg_id">
                    </th>
                </tr>
            </tbody>
            
            <tfoot>
                <tr>
                    <td colspan="2" style="padding-top: 35px;">
                        <input type="button" value="{function="localize('Cancel')"}" onclick="panthera.popup.close()" style="float: left; margin-left: 30px;">
                        <input type="submit" value="{function="localize('Save')"}" style="float: right; margin-right: 30px;">
                    </td>
                </tr>
            </tfoot>
        </table>
    </form>
    
    <script type="text/javascript">
    $("#edit_msg_form").submit(function () {
       
       panthera.jsonPOST({ data: '#edit_msg_form', mce: 'tinymce_all', success: function (response) {
                if (response.status == "success")
                {
                    navigateTo('?display=messages&cat=admin&action=display_category&category={$category_name}&language={$language}');
                }
            }
        });
        
        return false;
    });
    </script>
</div>

<!-- Creating new message -->
<div style="display: none;" id="createNewMessagePopup">
    <form action="?display=messages&cat=admin&action=new_msg&category={$category_id}&language={$language}" method="POST" id="post_new">
        <table class="formTable" style="margin: 0 auto; margin-bottom: 25px;">
            <thead>
                <tr>
                    <td colspan="2" class="formTableHeader" style="padding-top: 0px; padding-bottom: 30px;">
                        <p style="color: #e5ebef; padding: 0px; margin: 0px; margin-left: 30px;">{function="localize('Posting a new message', 'custompages')"}</p>
                    </td>
                </tr>
            </thead>
            
            <tbody>
                <tr>
                    <th style="font-weight: 100;"><b>{function="localize('Title', 'qmessages')"}:</b></th>
                    <td><input type="text" name="message_title" style="width: 310px;"></td>
                </tr>
            
                <tr>
                    <th style="font-weight: 100;"><b>{function="localize('Icon', 'qmessages')"}:</b>
                    <br><small>{function="localize('Optional icon, depends on if your website module support this function', 'qmessages')"}</small></th>
                    <td style="border-right: 0px;"><input type="text" name="message_icon" id="message_icon" style="width: 120px;"> &nbsp;
                    <input type="button" value="{function="localize('Upload file', 'qmessages')"}" onclick="createPopup('_ajax.php?display=upload&cat=admin&popup=true&callback=upload_file_callback_new');"></td>
                </tr>
                <tr>
                    <th style="font-weight: 100;"><b>{function="localize('Publish later', 'qmessages')"}:</b>
                    <br><small>{function="localize('If checked, this message will not be published on your website', 'qmessages')"}</small></th>
                    <td style="border-right: 0px;"><input type="checkbox" name="message_hidden" value="1"></td>
                </tr>
                <tr>
                    <th style="font-weight: 100;"><b>{function="localize('SEO name', 'qmessages')"}:</b><br><small>{function="localize('A name friendly to remember, and friendly for search engines', 'qmessages')"}</small></th>
                    <td style="border-right: 0px;"><input type="text" name="url_id" style="width: 310px;"></td>
                </tr>
            
                <tr>
                    <th colspan="2"">
                        <textarea name="message_content" id="message_content" style="width: 80%; height: 300px;"></textarea>
                    </th>
                </tr>
            </tbody>
            
            <tfoot>
                <tr>
                    <td colspan="2" style="padding-top: 35px;">
                        <input type="button" value="{function="localize('Cancel')"}" onclick="panthera.popup.close()" style="float: left; margin-left: 30px;">
                        <input type="submit" value="{function="localize('Post', 'webcatalog')"}" style="float: right; margin-right: 30px;">
                    </td>
                </tr>
            </tfoot>
        </table>
    </form>
    
    <script type="text/javascript">
    mceInit('message_content');
    
    $('#post_new').submit(function () {
        panthera.jsonPOST({ data: '#post_new', mce: 'tinymce_all', success: function (response) {
                if (response.status == "success") {
                    navigateTo('?display=messages&cat=admin&action=display_category&category={$category_name}&language={$language}');
                }
            }
        });
       return false;
    });
    </script>
</div>

<div class="ajax-content" style="text-align: center;">
    <div style="display: inline-block;">
    <table style="margin: 0px;">
        <thead>
            <tr>
                <th colspan="2">id</th>
                <th>{function="localize('Title', 'qmessages')"}</th>
                <th>{function="localize('Created', 'qmessages')"}</th>
                <th>{function="localize('Visibility', 'qmessages')"}</th>
                <th>{function="localize('Options', 'messages')"}</th>
            </tr>
        </thead>
        
        <tbody>
            {if="count($messages_list) > 0"}
                {loop="$messages_list"}
                <tr id="msg_{$value.id}_row">
                    <td>{$value.id}</td>
                    <td style="border-right: 0px;">{if="!empty($value.icon)"}<img src='{$value.icon}' class='quickMsgIcon'>{/if}</td>
                    <td id="msg_{$value.id}_title"><a href="#" onclick="editMessage({$value.id}); return false;">{$value.title}</a></td>
                    <td>
                        <a>{function="slocalize('Posted %s ago by %s', 'qmessages', elapsedTime($value['mod_time']), $value['author_login'])"}</a>
                    </td>
                    <td id="msg_{$value.id}_visibility">{$value.visibility}</td>
                    <td>
                        <a style="cursor: pointer;" onclick="editMessage({$value.id})">
                            <img src="{$PANTHERA_URL}/images/admin/ui/edit.png" style="max-height: 22px;" alt="{function="localize('Edit', 'qmessages')"}">
                        </a>
                        
                        <a style="cursor: pointer;" onclick="deleteMessage({$value.id})">
                            <img src="{$PANTHERA_URL}/images/admin/ui/delete.png" style="max-height: 22px;" alt="{function="localize('Remove')"}">
                        </a>
                        
                        {if="$isAdmin"}
                        <a style="cursor: pointer;" onclick="panthera.popup.toggle('_ajax.php?display=acl&cat=admin&popup=true&name=can_qmsg_edit_{$value.id}')">
                            <img src="{$PANTHERA_URL}/images/admin/ui/permissions.png" style="max-height: 22px;" alt="{function="localize('Manage permissions')"}">
                        </a>
                        {/if}
                    </td>
                </tr>
                {/loop}
                {else}
                <tr><td colspan="7" style="text-align: center;">{function="localize('No items to display in this category and language', 'qmessages')"}</td></tr>
            {/if}
        </tbody>
    </table>
    
        <div style="position: relative; text-align: left;" class="pager">{$uiPagerName="adminQuickMessages"}{include="ui.pager"}</div>
    </div>
</div>
