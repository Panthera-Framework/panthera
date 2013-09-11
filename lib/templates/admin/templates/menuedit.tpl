<script type="text/javascript">
/**
  * Remove menu category
  *
  * @author Mateusz Warzyński
  */

function removeMenuCategory(id)
{
    panthera.jsonPOST({ url: '{$AJAX_URL}?display=menuedit&cat=admin&action=remove_category&category_id='+id, data: '', messageBox: 'w2ui', success: function (response) {
            if (response.status == "success")
                navigateTo('?display=menuedit&cat=admin');
        }
    });
    return false;
}

</script>

    {include="ui.titlebar"}

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
                       <a onclick="navigateTo('?display=menuedit&cat=admin&action=new_category');" style="cursor: pointer; float: right;"><img src="{$PANTHERA_URL}/images/admin/list-add.png" style="height: 15px;"></a>
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
