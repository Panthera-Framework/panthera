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
            panthera.jsonPOST({ url: '{$AJAX_URL}?display=menuedit&cat=admin&action=remove_category&category_id='+id, data: '', messageBox: 'w2ui', success: function (response) {
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
        <input type="button" value="{function="localize('Add category', 'menuedit')"}" onclick="panthera.popup.toggle('element:#newCategory')">
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

    <form id="add_category_form" method="POST" action="?display=menuedit&cat=admin&action=add_category">
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
                    <th>{function="localize('Type name', 'menuedit')"}</th>
                    <th><input type="text" name="category_type_name" style="width: 99%;"></th>
                </tr>
                <tr>
                    <th>{function="localize('Title', 'menuedit')"}</th>
                    <th><input type="text" name="category_title" style="width: 99%;"></th>
                </tr>
                <tr>
                    <th>{function="localize('Description', 'menuedit')"}</th>
                    <th><input type="text" name="category_description" style="width: 99%;"></th>
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
                  <th scope="col" class="rounded-company" style="width: 250px;">{function="localize('Name', 'menuedit')"}</th>
                  <th>{function="localize('Title', 'menuedit')"}</th>
                  <th>{function="localize('Description', 'menuedit')"}</th>
                  <th>{function="localize('Elements', 'menuedit')"}</th>
                  <th>{function="localize('Options', 'messages')"}</th>
              </tr>
          </thead>

          <tbody>
            {if="$menu_categories == False"}
              <tr>
                  <td colspan="5">{function="localize('No any categories found, use button below to create one', 'menuedit')"}.</td>
              </tr>
            {else}
             {loop="$menu_categories"}
              <tr id="category_{$value.id}">
                  <td><a href="#" onclick="navigateTo('?display=menuedit&cat=admin&action=category&category={$value.name}');" class="ajax_link">{$value.name}</a></td>
                  <td>{$value.title}</td>
                  <td>{$value.description}</td>
                  <td>{$value.elements}</td>
                  <td>
                      <a href="#" onclick="removeMenuCategory({$value.id});">
                            <img src="{$PANTHERA_URL}/images/admin/ui/delete.png" style="max-height: 22px;" alt="{function="localize('Remove')"}">
                      </a>
                  </td>
              </tr>
             {/loop}
            {/if}
          </tbody>
      </table>
</div>