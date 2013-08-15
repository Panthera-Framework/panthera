<script type="text/javascript">
$(document).ready(function(){
    $('.ajax_link').click(function (event) { event.preventDefault(); navigateTo(jQuery(this).attr('href')); return false;});
});

/**
  * Remove menu category
  *
  * @author Mateusz Warzyński
  */

function removeMenuCategory(id)
{
    panthera.jsonPOST({ url: '{$AJAX_URL}?display=menuedit&cat=admin&action=remove_category&category_id='+id, data: '', messageBox: 'userinfoBox', success: function (response) {
            if (response.status == "success")
                navigateTo('?display=menuedit&cat=admin');
        }
    });
    return false;
}

</script>

    <div class="titlebar">{function="localize('Menu editor', 'menuedit')"} - {function="localize('Menu management for site and administration panel', 'menuedit')"}.{include="_navigation_panel"}</div><br>

    <div class="msgSuccess" id="userinfoBox_success"></div>
    <div class="msgError" id="userinfoBox_failed"></div>

    <div class="grid-1">
      <table class="gridTable">
          <thead>
              <tr>
                  <th scope="col" class="rounded-company" style="width: 250px;">{function="localize('Name', 'menuedit')"}</th>
                  <th>{function="localize('Title', 'menuedit')"}</th>
                  <th>{function="localize('Description', 'menuedit')"}</th>
                  <th>{function="localize('Elements', 'menuedit')"}</th>
                  <th>{function="localize('Options', 'messages')"}</th>
              </tr>
          </thead>

          <tfoot>
              <tr>
                  <td colspan="5" class="rounded-foot-left"><em>Panthera menuedit - {function="localize('List of categories', 'menuedit')"}</em>
                       <input type="button" value="{function="localize('Add new menu category', 'menuedit')"}" style="float: right;" onclick="navigateTo('?display=menuedit&cat=admin&action=new_category')">
                  </td>
              </tr>
          </tfoot>

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
                  <td><input type="button" value="{function="localize('Delete')"}" onclick="removeMenuCategory({$value.id});"></td>
              </tr>
             {/loop}
            {/if}
          </tbody>
      </table>
    </div>
