{function="localizeDomain('qmessages')"}
<script type="text/javascript">
$(document).ready(function(){
    $('.ajax_link').click(function (event) { event.preventDefault(); navigateTo(jQuery(this).attr('href')); return false;});
});

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
    w2confirm('{function="localize('Are you sure you want delete this category?', 'qmessages')"}', function (responseText) {
        if (responseText == 'Yes')
        {
            panthera.jsonPOST( { url: '?{function="getQueryString('GET', 'action=deleteCategory', '_')"}', data: 'category_name='+categoryName, messageBox: 'w2ui', success: function (response) {
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
    
    {$uiSearchbarName="uiTop"}{include="ui.searchbar"}
    <div class="msgSuccess" id="userinfoBox_success"></div>
    <div class="msgError" id="userinfoBox_failed"></div>

    <div class="grid-1">
        <table class="gridTable">
            <thead>
                <tr>
                    <th style="width: 250px;">{function="localize('Categories', 'qmessages')"}</th>
                    <th>{function="localize('Description')"}</th>
                    <th>{function="localize('Details')"}</th>
                    <th>{function="localize('Options')"}</th>
                </tr>
            </thead>

            <tfoot>
                <tr>
                    <td colspan="5">
                        {$uiPagerName="quickMessages"}{include="ui.pager"}
                    </td>
                </tr>
            </tfoot>

            <tbody>
              {if="count($categories) > 0"}
              {loop="$categories"}
                <tr id="category_{$value.category_name}">
                    <td>
                        <a href="{$AJAX_URL}?display=messages&cat=admin&action=display_category&category={$value.category_name}" class="ajax_link">{$value.title|localize}</a>
                    </td>
                    
                    <td>
                        {if="strlen($value.description) > 0"}<i>{$value.description}</i>{else}{function="localize('no description', 'qmessages')"}{/if}
                    </td>
                    
                    <td>
                        {$value.category_name}, id: {$value.category_id}
                    </td>
                    
                    <td style="width: 15%;">
                        <a href="#" onclick="removeCategory('{$value.category_name}');">
                            <img src="{$PANTHERA_URL}/images/admin/ui/delete.png" style="max-height: 22px;" alt="{function="localize('Remove')"}">
                        </a>
                        
                        <a href="#" onclick="createPopup('?display=acl&amp;cat=admin&amp;popup=true&amp;name=can_qmsg_manage_{$value.category_name}', 1300, 550);" alt="{function="localize('Manage permissions')"}">
                            <img src="{$PANTHERA_URL}/images/admin/ui/permissions.png" style="max-height: 22px; margin-left: 3px;" alt="{function="localize('Manage permissions')"}">
                        </a>
                        
                        <a href="#" onclick="createPopup('?display=menuedit&cat=admin&popup=true&action=quickAddFromPopup&link=data:text/plain;base64,{function="base64_encode('?' .getQueryString('display=messages&cat=admin&action=display_category&category=' .$value.category_name))"}&title={$value.title}', 1300, 840);" alt="{function="localize('Add to menu', 'menuedit')"}">
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
   </div>
   
   <div class="grid-2" style="width: 40%;">
        <table class="gridTable">
            <thead id="newCategoryForm_header">
                <tr><th colspan="2">{function="localize('Add new category', 'qmessages')"}</th></tr>
            </thead>
            
            <form action="?{function="getQueryString('GET', 'action=newCategory', '_')"}" method="POST" id="newCategoryForm">
            <tbody>
                <tr id="newCategoryForm_title">
                    <td>{function="localize('Title', 'qmessages')"}</td>
                    <td><input type="text" name="title" style="width: 90%;"></td>
                </tr>
                
                <tr id="newCategoryForm_description">
                    <td>{function="localize('Description')"}<br><small>({function="localize('optional', 'qmessages')"})</small></td>
                    <td><input type="text" name="description" style="width: 90%;"></td>
                </tr>
                
                <tr id="newCategoryForm_id">
                    <td>{function="localize('ID')"}<br><small>({function="localize('optional', 'qmessages')"}, {function="localize('category name', 'qmessages')"})</small></td>
                    <td><input type="text" name="category_name" style="width: 90%;"></td>
                </tr>
            </tbody>
            
            <tfoot id="newCategoryForm_footer">
                <tr>
                    <td colspan="2" style="text-align: right; padding-right: 12px;"><input type="submit" value="{function="localize('Submit')"}"></td>
                </tr>
            </tfoot>
            </form>
        </table>
   </div>
