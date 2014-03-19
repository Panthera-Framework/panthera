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
    {include="menuedit_edit_category.tpl"}
</div>
    
<!-- Ajax content -->

<div class="ajax-content" style="text-align: center;">
      <table style="display: inline-block;">
          <thead>
              <tr>
                  <th style="width: 250px;">{function="localize('Menu category', 'menuedit')"}</th>
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
                 
                 <tr>
                    <td colspan="5">&nbsp;</td>
                 </tr>
             {/loop}
            {/if}
          </tbody>
      </table>
</div>