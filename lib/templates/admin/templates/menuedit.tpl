<script type="text/javascript">
$(document).ready(function(){
    $('.ajax_link').click(function (event) { event.preventDefault(); navigateTo(jQuery(this).attr('href')); return false;});
});

/**
  * Save menu category
  *
  * @author Mateusz Warzyński
  */

$('#save_form').submit(function () {
    panthera.jsonPOST({ data: '#save_form', messageBox: 'userinfoBox', success: function (response) {
            if (response.status == "success")
                navigateTo('?display=menuedit&action=category&cat='+jQuery('#cat_type').val());
        }
    });

    return false;

});


/**
  * Order table
  *
  * @author Mateusz Warzyński
  */

function getTableOrder()
{
    var items = $(".sortable_hidden");
    var linkIDs = [items.size()];
    var index = 0;

    items.each(
        function(intIndex) {
            linkIDs[index] = $(this).val();
            index++;
        });

    linkIDs.reverse();

    return JSON.stringify(linkIDs);
}

/**
  * Save ordered menu
  *
  * @author Mateusz Warzyński
  */

function saveMenuOrder(id)
{
    panthera.jsonPOST({ url: "?display=menuedit&action=save_order", data: 'id='+id+'&order='+getTableOrder(), messageBox: 'userinfoBox'});

    return false;
}
</script>


{if="$action == 'plugin_disabled'"}

    <div class="titlebar">{function="localize('Error')"}{include="_navigation_panel.tpl"}</div><br>

    <div class="msgSuccess" id="userinfoBox_success"></div>
    <div class="msgError" id="userinfoBox_failed"></div>


{elseif="$action == 'item'"}
    {include="menuedit_item.tpl"}
{elseif="$action == 'category'"}
    {include="menuedit_category.tpl"}
{elseif="$action == 'new_category'"}
    {include="menuedit_newcategory.tpl"}
{elseif="$action == 'new_item'"}
    {include="menuedit_newitem.tpl"}
{else}
<script type="text/javascript">
/**
  * Remove menu category
  *
  * @author Mateusz Warzyński
  */

function removeMenuCategory(id)
{
    panthera.jsonPOST({ url: '{$AJAX_URL}?display=menuedit&action=remove_category&category_id='+id, data: '', messageBox: 'userinfoBox', success: function (response) {
            if (response.status == "success")
                navigateTo('?display=menuedit');
        }
    });
    return false;
}

</script>

    <div class="titlebar">{function="localize('Menu editor', 'menuedit')"} - {function="localize('Menu management for site and administration panel', 'menuedit')"}.{include="_navigation_panel.tpl"}</div><br>

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
                       <input type="button" value="{function="localize('Add new menu category', 'menuedit')"}" style="float: right;" onclick="navigateTo('?display=menuedit&action=new_category')">
                  </td>
              </tr>
          </tfoot>

          <tbody>
            {loop="$menu_categories"}
              <tr id="category_{$value.id}">
                  <td><a href="{$AJAX_URL}?display=menuedit&action=category&cat={$value.name}" class="ajax_link">{$value.name}</a></td>
                  <td>{$value.title}</td>
                  <td>{$value.description}</td>
                  <td>{$value.elements}</td>
                  <td><input type="button" value="{function="localize('Delete')"}" onclick="removeMenuCategory({$value.id});"></td>
              </tr>
            {/loop}
          </tbody>
      </table>
    </div>

{/if}
