{$site_header}

<script type="text/javascript">
$(document).ready(function () {
    $('#newCategoryForm').submit (function () {
        panthera.jsonPOST( { data: '#newCategoryForm', messageBox: 'w2ui', success: function (response) {
                if (response.status == 'success')
                {
                    navigateTo('?display=messages&cat=admin');
                }
        
            } 
        });
        return false;
    });
});

function removeCategory(categoryName)
{
    panthera.confirmBox.create('{function="localize('Are you sure you want delete this category?', 'qmessages')"}', function (responseText) {
       if (responseText == 'Yes')
        {
            panthera.jsonPOST( { url: '?{function="getQueryString('GET', 'action=removeCategory', '_')"}', data: 'category='+categoryName, messageBox: 'w2ui', success: function (response) {
                    if (response.status == 'success')
                    {
                        $('#category_'+categoryName).remove();
                    }
                
                } 
            });
        }
    });
}
</script>

{include="ui.titlebar"}

<div id="topContent">
    {$uiSearchbarName="uiTop"}
    {include="ui.searchbar"}
    
    <div class="separatorHorizontal"></div>
    
    <div class="searchBarButtonArea">
        <input type="button" value="{function="localize('Add new category', 'qmessages')"}" onclick="panthera.popup.toggle('element:#newCategoryPopup')">
    </div>
</div>

<div id="popupOverlay" style="text-align: center; padding-top: 20px; padding-bottom: 0px;"></div>

<!-- Content -->
<div class="ajax-content" style="text-align: center;">
    <div style="display: inline-block;">
    <table style="margin: 0px;">
        <thead>
            <tr>
                <th>{function="localize('Category', 'qmessages')"}</th>
                <th>{function="localize('Description')"}</th>
                <th>{function="localize('Details')"}</th>
                <th>{function="localize('Options')"}</th>
            </tr>
        </thead>
        
        <tbody>
        {if="count($categories) > 0"}
            {loop="$categories"}
            <tr id="category_{$value->category_name}">
                <td>
                    <a href="{$AJAX_URL}?display=messages&cat=admin&action=displayCategory&category={$value->category_name}" class="ajax_link">{$value->title}</a>
                </td>
                    
                <td>
                    {if="strlen($value->description) > 0"}<i>{$value->description}</i>{else}{function="localize('no description', 'qmessages')"}{/if}
                </td>
                    
                <td>
                    {$value->category_name}, id: {$value->category_id}
                </td>
                    
                <td style="width: 20%;">
                    <a href="#" onclick="removeCategory('{$value->category_name}');">
                        <img src="{$PANTHERA_URL}/images/admin/ui/delete.png" style="max-height: 22px;" alt="{function="localize('Remove')"}">
                    </a>
                        
                    <a href="#" onclick="panthera.popup.toggle('?display=acl&amp;cat=admin&amp;popup=true&amp;name=can_qmsg_manage_{$value->category_name}', 1300, 550);" alt="{function="localize('Manage permissions')"}">
                        <img src="{$PANTHERA_URL}/images/admin/ui/permissions.png" style="max-height: 22px; margin-left: 3px;" alt="{function="localize('Manage permissions')"}">
                    </a>
                        
                    <a href="#" onclick="panthera.popup.toggle('?display=menuedit&cat=admin&popup=true&action=quickAddFromPopup&link=data:text/plain;base64,{function="base64_encode('?' .getQueryString('display=messages&cat=admin&action=display_category&category=' .$value->category_name))"}&title={$value->title}');" alt="{function="localize('Add to menu', 'menuedit')"}">
                        <img src="{$PANTHERA_URL}/images/admin/menu/Actions-transform-move-icon.png" style="max-height: 22px; margin-left: 3px;" alt="{function="localize('Manage permissions')"}">
                    </a>
                </td>
            </tr>
            {/loop}
            {else}
            <tr>
                <td colspan="4" style="text-align: center;">{function="localize('No categories found', 'qmessages')"}</td>
            </tr>
            {/if}
        </tbody>
    </table>
    
        <div style="position: relative; text-align: left;" class="pager">{$uiPagerName="quickMessages"}{include="ui.pager"}</div>
    </div>
</div>

<!-- New category popup -->

<div id="newCategoryPopup" style="display: none;">
    <form action="?{function="getQueryString('GET', 'action=newCategory', '_')"}" method="POST" id="newCategoryForm">
    <table class="formTable" style="margin: 0 auto; margin-bottom: 30px;">
        <thead>
            <tr>
                <td colspan="2" class="formTableHeader" style="padding-top: 0px; padding-bottom: 30px;">
                    <p style="color: #e5ebef; padding: 0px; margin: 0px; margin-left: 30px;">{function="localize('Add new category', 'qmessages')"}</p>
                </td>
            </tr>
        </thead>
        <tbody>
            <tr>
                <th>{function="localize('Title', 'qmessages')"}:</th>
                <td><input type="text" name="title"></td>
            </tr>
            <tr>
                <th>{function="localize('Description')"}:<br><small>({function="localize('optional', 'qmessages')"})</small>:</th>
                <td><input type="text" name="description"></td>
            </tr>
            <tr>
                <th>{function="localize('ID')"}:<br><small>({function="localize('optional', 'qmessages')"}, {function="localize('category name', 'qmessages')"})</small></th>
                <td><input type="text" name="category_name"></td>
            </tr>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2" style="padding-top: 35px;">
                    <input type="button" value="{function="localize('Cancel')"}" onclick="panthera.popup.close()" style="float: left; margin-left: 30px;">
                    <input type="submit" value="{function="localize('Add new category', 'qmessages')"}" style="float: right; margin-right: 30px;">
                </td>
            </tr>
        </tfoot>
    </table>
    </form>
        
    <script type="text/javascript">
    $('#newCategoryForm').submit (function () {
        panthera.jsonPOST( { data: '#newCategoryForm', success: function (response) {
                if (response.status == 'success')
                {
                    navigateTo(window.location.href);
                }
        
            } 
        });
        return false;
    });
    </script>
</div>
