{include="ui.titlebar"}

<script type="text/javascript">
/**
  * Remove menu category
  *
  * @author Mateusz Warzyński
  */

function removeMenuCategory(id)
{
    panthera.confirmBox.create('{function="localize('Are you sure you want delete this category?', 'menuedit')"}', function (responseText) {
       if (responseText == 'Yes')
        {
            panthera.jsonPOST({ url: '{$AJAX_URL}?display=menuedit&cat=admin&action=categoryRemove&category='+id, data: '', messageBox: 'w2ui', success: function (response) {
                    if (response.status == "success")
                        navigateTo('?display=menuedit&cat=admin');
                }
            });
            return false;
        }
   });
}

</script>

<div id="topContent" style="min-height: 50px;">
    <div class="searchBarButtonArea">
        {if="$newCategoryButton"}
        <input type="button" value="{function="localize('Add category', 'menuedit')"}" onclick="panthera.popup.toggle('element:#newCategory')">
        {/if}
    </div>
</div>


<!-- New menu category -->

<div id="newCategory" style="display: none;">
    
    <script type="text/javascript">
    
    /**
      * Add menu category
      *
      * @author Mateusz Warzyński
      */
    
    $('#add_category_form').submit(function () {
        panthera.jsonPOST({ data: '#add_category_form', messageBox: 'w2ui', success: function (response) {
                if (response.status == "success")
                    navigateTo('?display=menuedit&cat=admin');
            }
        });
    
        return false;
    
    });
    
    </script>

    <form id="add_category_form" method="POST" action="?display=menuedit&cat=admin&action=createCategory">
        <table class="formTable" style="margin: 0 auto; margin-bottom: 30px;">
            <thead>
                 <tr>
                     <td colspan="2" class="formTableHeader" style="padding-top: 0px; padding-bottom: 30px;">
                         <p style="color: #e5ebef; padding: 0px; margin: 0px; margin-left: 30px;">{function="localize('Create new category', 'menuedit')"}</p>
                     </td>
                 </tr>
            </thead>
            
            <tfoot>
                <tr>
                    <td colspan="3" style="padding-top: 35px;">
                        <input type="button" value="{function="localize('Cancel')"}" onclick="panthera.popup.close()" style="float: left; margin-left: 30px;">
                        <input type="submit" value="{function="localize('Add', 'users')"}" style="float: right; margin-right: 30px;">
                    </td>
                </tr>
            </tfoot>
            <tbody>
                <tr>
                    <th>{function="localize('Title', 'menuedit')"}</th>
                    <th><input type="text" name="category_title" style="width: 99%;"></th>
                </tr>
                
                <tr>
                    <th>{function="localize('Description', 'menuedit')"}</th>
                    <th><input type="text" name="category_description" style="width: 99%;"></th>
                </tr>
                
                <tr>
                    <th><small>{function="localize('ID', 'menuedit')"} ({function="localize('Optional')"})</small></th>
                    <th><input type="text" name="category_type_name" style="width: 99%;"></th>
                </tr>
            </tbody>
        </table>
        <input type="hidden" name="category_parent" value="0">
        <input type="hidden" name="category_elements" value="0">
    </form>
</div>
    
<div id="popupOverlay" style="text-align: center; padding-top: 20px; padding-bottom: 0px;"></div>

<!-- Ajax content -->

<div class="ajax-content" style="text-align: center;">
      <table style="display: inline-block;">
          <thead>
              <tr>
                  <th style="width: 250px;">{function="localize('Title', 'menuedit')"}</th>
                  <th>{function="localize('ID', 'menuedit')"}</th>
                  <th>{function="localize('Description', 'menuedit')"}</th>
                  <th>{function="localize('Elements', 'menuedit')"}</th>
                  <th>{function="localize('Options', 'messages')"}</th>
              </tr>
          </thead>

          <tbody class="hovered">
            {if="$menu_categories == False"}
              <tr>
                  <td colspan="5">{function="localize('No any categories found, use above button to create one', 'menuedit')"}.</td>
              </tr>
            {else}
             {loop="$menu_categories"}
                 {$depth=0}
                 {$z=$value}
                 
                 {*} This would happen if current user don't have enought rights to view this category so it shouldn't be listed{/*}
                 {if="!$z.item"}
                    {continue}
                 {/if}
                 
                 {include="menuedit.categoryrow.tpl"}
             {/loop}
            {/if}
          </tbody>
      </table>
</div>