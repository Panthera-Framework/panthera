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

function editMessage(id, destLanguage)
{
    panthera.jsonPOST({ url: '{$AJAX_URL}?display=messages&cat=admin&action=getMessage&language={$language}&category={$category_name}&msgid='+id, data: 'destLanguage='+destLanguage, messageBox: false, success: function (response) {
            if (response.status == "success") {
                panthera.popup.create('element:#editMessagePopup');
                mceInit('edit_msg_content');
            
                $('#edit_msg_title').val(response.title);
                $('#edit_msg_id').val(response.id);
                $('#edit_msg_icon').val(response.icon);
                $('#edit_language').val(response.language);
                $('#edit_url_id').val(response.url_id);
                $('#edit_unique').val(response.unique);
                $('#edit_mode').val(response.mode);
                $('#message_icon').val(response.icon);
                
                if (response.operation)
                    $('#edit_msg_operation').html(response.operation);

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
            panthera.jsonPOST({ url: '{$AJAX_URL}?display=messages&cat=admin&action=removeMessage&category={$category_name}&msgid='+id, data: '', messageBox: 'userinfoBox', success: function (response) {
                    if (response.status == "success")
                        navigateTo(window.location.href);
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
    panthera.logging.output('upload_file_callback_new()');
    
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
        
        <div class="searchBarButtonAreaLeft">
            <input type="button" value="{function="localize('Back')"}" onclick="navigateTo('?display=messages&cat=admin');">
        </div>
    
        <span data-searchbardropdown="#qLangDropdown" id="qLangDropdownSpan" style="position: relative; cursor: pointer;">
             <input type="button" value="{function="localize('Switch language', 'custompages')"}">
        </span>

        <div id="qLangDropdown" class="searchBarDropdown searchBarDropdown-tip searchBarDropdown-relative">
            <ul class="searchBarDropdown-menu">
            {loop="$languages"}
                <li style="text-align: left;"><a href="#{$key}" onclick="navigateTo('?display=messages&cat=admin&action=displayCategory&category={$category_name}&language={$key}');">{$key}</a></li>
            {/loop}
            </ul>
        </div>
    
        <input type="button" value="{function="localize('Post a new message', 'qmessages')"}" onclick="panthera.popup.toggle('element:#createNewMessagePopup')">
    </div>
</div>

<div id="popupOverlay" style="text-align: center; padding-top: 20px; padding-bottom: 0px;"></div>

<!-- Editing a message -->
<div style="display: none;" id="editMessagePopup">
    <form action="{$AJAX_URL}?display=messages&cat=admin&action=editMessage&category={$category_id}" method="POST" id="edit_msg_form">
        <table class="formTable" style="margin: 0 auto; margin-bottom: 25px; width: 85%;">
            <thead>
                <tr>
                    <td colspan="2" class="formTableHeader" style="padding-top: 0px; padding-bottom: 30px;">
                        <p style="color: #e5ebef; padding: 0px; margin: 0px; margin-left: 30px;" id="edit_msg_operation">{function="localize('Editing a message', 'custompages')"}</p>
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
                    <td style="border-right: 0px;"><input type="text" name="message_icon" id="message_icon" style="width: 300px;"> &nbsp;<input type="button" value="{function="localize('Upload file', 'qmessages')"}" onclick="panthera.popup.create('_ajax.php?display=upload&cat=admin&popup=true&callback=upload_file_callback_new', 'upload');"></td>
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
                        <textarea name="edit_msg_content" id="edit_msg_content" style="width: 100%; height: 400px;"></textarea>
                        <input type="hidden" id="edit_msg_id" name="edit_msg_id">
                        <input type="hidden" id="edit_unique" name="unique">
                        <input type="hidden" id="edit_mode" name="mode">
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
                    navigateTo(window.location.href);
                }
            }
        });
        
        return false;
    });
    </script>
</div>

<!-- Creating new message -->
<div style="display: none;" id="createNewMessagePopup">
    <form action="?display=messages&cat=admin&action=createNewMessage&category={$category_id}&language={$language}" method="POST" id="post_new">
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
                    <input type="button" value="{function="localize('Upload file', 'qmessages')"}" onclick="panthera.popup.create('_ajax.php?display=upload&cat=admin&popup=true&callback=upload_file_callback_new', 'upload');"></td>
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
                    navigateTo(window.location.href);
                }
            }
        });
       return false;
    });
    </script>
</div>

<div class="ajax-content" style="text-align: center;">
    <div style="display: inline-block; text-align: center;">
        
    {*}
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
                            <img src="{$PANTHERA_URL}/images/admin/ui/edit.png" style="max-height: 22px;" alt="{function="localize('Edit', 'qmessages')"}" title="{function="localize('Edit', 'qmessages')"}">
                        </a>
                        
                        <a style="cursor: pointer;" onclick="deleteMessage({$value.id})">
                            <img src="{$PANTHERA_URL}/images/admin/ui/delete.png" style="max-height: 22px;" alt="{function="localize('Remove')"}" title="{function="localize('Remove')"}">
                        </a>
                        
                        {if="$isAdmin"}
                        <a style="cursor: pointer;" onclick="panthera.popup.toggle('_ajax.php?display=acl&cat=admin&popup=true&name=can_qmsg_edit_{$value.id}')">
                            <img src="{$PANTHERA_URL}/images/admin/ui/permissions.png" style="max-height: 22px;" alt="{function="localize('Manage permissions')"}" title="{function="localize('Manage permissions')"}">
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
    {/*}
    
    {loop="$messages"}
        <div class="articleContainer{if="!$value->visibility"} articleUnpublished{/if}">
            <div class="articleTitlebar">
                <a onclick="$('#msg_{$value->id}_content').toggle();" style="cursor: pointer;">
                    {if="!$value->visibility"}<i>{/if}<b {if="!$value->visibility"}class="inaviteArticle"{/if}>{$value->title}</b>{if="!$value->visibility"} <small>({function="localize('Not published', 'messages')"})</small></i>{/if}
                </a>
                
                <span class="articleIcons">
                    <a style="cursor: pointer;" onclick="editMessage({$value->id})">
                        <img src="{$PANTHERA_URL}/images/admin/pantheraUI/transparent.png" class="pantheraIcon icon-Edit" alt="Icon" title="{function="localize('Edit', 'qmessages')"}">
                    </a>
                    
                    <span data-searchbardropdown="#articleLang_{$value->id}" id="articleLang_{$value->id}_span" style="position: relative; cursor: pointer;">
                         <img src="{$PANTHERA_URL}/images/admin/pantheraUI/transparent.png" class="pantheraIcon icon-Flag" title="{function="localize('Create a translation in other language', 'qmessages')"}">
                    </span>
            
                    <div id="articleLang_{$value->id}" class="searchBarDropdown searchBarDropdown-tip searchBarDropdown-relative">
                        <ul class="searchBarDropdown-menu">
                        {$entry=$value}
                        {loop="$languages"}
                            <li style="text-align: left;"><a href="#{$key}" onclick="editMessage('{$entry->id}', '{$key}')">{$key}</a></li>
                        {/loop}
                        </ul>
                    </div>
                                
                    {if="$isAdmin"}
                    <a style="cursor: pointer;" onclick="panthera.popup.toggle('_ajax.php?display=acl&cat=admin&popup=true&name=can_qmsg_edit_{$value->id}')">
                        <img src="{$PANTHERA_URL}/images/admin/pantheraUI/transparent.png" class="pantheraIcon icon-Users" title="{function="localize('Manage permissions')"}">
                    </a>
                    {/if}
                    
                    <a style="cursor: pointer;" onclick="deleteMessage({$value->id})">
                        <img src="{$PANTHERA_URL}/images/admin/pantheraUI/transparent.png" class="pantheraIcon icon-Delete" title="{function="localize('Remove')"}">
                    </a>
                </span>
            </div>
            <div class="articleContent" id="msg_{$value->id}_content">
                {$value->getScrap(1400)}
                
                <br><br>
                <div class="articleFooter">
                    <a><i><small>{function="slocalize('Last modified %s ago by %s', 'qmessages', elapsedTime($value->mod_time), $value->getAuthorName())"}</small></i></a>
                </div>
                <br>
            </div>
        </div>
    {/loop}
    
        <div style="position: relative; text-align: left; margin: 0 auto; width: 75%; margin-top: 10px;" class="pager">{$uiPagerName="adminQuickMessages"}{include="ui.pager"}</div>
    </div>
</div>
