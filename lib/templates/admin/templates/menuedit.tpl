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


{if $action eq 'plugin_disabled'}

    <div class="titlebar">{"Error"|localize}{include file="_navigation_panel.tpl"}</div><br>

    <div class="msgSuccess" id="userinfoBox_success"></div>
    <div class="msgError" id="userinfoBox_failed"></div>


{elseif $action eq 'item'}
    {include 'menuedit_item.tpl'}
{elseif $action eq 'category'}
    {include 'menuedit_category.tpl'}
{elseif $action eq 'new_category'}
    {include 'menuedit_newcategory.tpl'}
{elseif $action eq 'new_item'}
    {include 'menuedit_newitem.tpl'}
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

    <div class="titlebar">{"Menu editor"|localize:menuedit} - {"Menu management for site and administration panel"|localize:menuedit}.{include file="_navigation_panel.tpl"}</div><br>

    <div class="msgSuccess" id="userinfoBox_success"></div>
    <div class="msgError" id="userinfoBox_failed"></div>

    <div class="grid-1">
      <table class="gridTable">
          <thead>
              <tr>
                  <th scope="col" class="rounded-company" style="width: 250px;">{"Name"|localize:menuedit}</th>
                  <th>{"Title"|localize:menuedit}</th>
                  <th>{"Description"|localize:menuedit}</th>
                  <th>{"Elements"|localize:menuedit}</th>
                  <th>{"Options"|localize:messages}</th>
              </tr>
          </thead>

          <tfoot>
              <tr>
                  <td colspan="5" class="rounded-foot-left"><em>Panthera menuedit - {"List of categories"|localize:menuedit}</em>
                       <input type="button" value="{"Add new menu category"|localize:menuedit}" style="float: right;" onclick="navigateTo('?display=menuedit&action=new_category')">
                  </td>
              </tr>
          </tfoot>

          <tbody>
            {foreach from=$menu_categories key=k item=i}
              <tr id="category_{$i.id}">
                  <td><a href="{$AJAX_URL}?display=menuedit&action=category&cat={$i.name}" class="ajax_link">{$i.name}</a></td>
                  <td>{$i.title}</td>
                  <td>{$i.description}</td>
                  <td>{$i.elements}</td>
                  <td><input type="button" value="{"Delete"|localize}" onclick="removeMenuCategory({$i.id});"></td>
              </tr>
            {/foreach}
          </tbody>
      </table>
    </div>

{/if}
