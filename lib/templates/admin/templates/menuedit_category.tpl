<script type="text/javascript">

/**
  * Make our elements sortable...
  *
  * @author Mateusz Warzyński
  */

$(document).ready(function(){
    var fixHelper = function(e, ui) {
        ui.children().each(function() {
            $(this).width($(this).width());
        });
        return ui;
    };

    $('.gridTable tbody').sortable({ helper: fixHelper });
    $('.gridTable tbody').disableSelection();
});

var spinner = new panthera.ajaxLoader($('#menu_category'));

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
    panthera.jsonPOST({ url: "?display=menuedit&cat=admin&action=save_order", data: 'id='+id+'&order='+getTableOrder(), spinner: spinner});

    return false;
}

/**
  * Remove menu item from database
  *
  * @author Mateusz Warzyński
  */

function removeItem(id)
{
    w2confirm('{function="localize('Are you sure you want delete this item?', 'menuedit')"}', function (responseText) {
        if (responseText == 'Yes')
        {
            panthera.jsonPOST({ url: '{$AJAX_URL}?display=menuedit&cat=admin&action=remove_item&item_id='+id, data: '', messageBox: 'userinfoBox', spinner: spinner, success: function (response) {
                    if (response.status == "success") {
                        jQuery('#item_'+id).slideUp();
                        jQuery('#item_'+id).remove();
                    }
                }
            });
        }
    });
}
</script>

	{include="ui.titlebar"}

    <div class="msgError" id="userinfoBox_failed"></div>

    <div class="grid-1" style="position: relative;" id="menu_category">
      <table class="gridTable">
            <thead>
                  <tr>
                      <th scope="col" class="rounded-company" style="width: 250px;">{function="localize('Title', 'menuedit')"}</th>
                      <th>{function="localize('Link', 'menuedit')"}</th>
                      <th>{function="localize('Language', 'menuedit')"}</th>
                      <th>{function="localize('SEO friendly name', 'menuedit')"}</th>
                      <th>{function="localize('Tooltip', 'menuedit')"}</th>
                      <th>{function="localize('Icon', 'menuedit')"}</th>
                      <th>{function="localize('Attributes', 'menuedit')"}</th>
                      <th>{function="localize('Options', 'messages')"}</th>
                  </tr>
            </thead>

            <tfoot>
                  <tr>
                      <td colspan="8" class="rounded-foot-left"><em>Panthera menuedit - {function="localize('List of items', 'menuedit')"}</em><span>
                          <span style="float: right;">
                          		<a href="#add" onclick="navigateTo('_ajax.php?display=menuedit&cat=admin&action=new_item&category={$category}');" style="cursor: pointer; float: right;"><img src="{$PANTHERA_URL}/images/admin/list-add.png" style="max-height: 22px;" alt="{function="localize('Add new link', 'menuedit')"}"></a>
                          		<a href="#save_order" onclick="saveMenuOrder('{$category}');" style="cursor: pointer; float: right; margin-right: 7px;"><img src="{$PANTHERA_URL}/images/admin/ui/save.png" style="max-height: 22px;" alt="{function="localize('Save order', 'menuedit')"}"></a>
                          </span>
                      </td>
                  </tr>
            </tfoot>

            <tbody>
                {if="count($menus) > 0"}
                {loop="$menus"}
                  <tr id="item_{$value.id}">
                      <td><a href="{$AJAX_URL}?display=menuedit&cat=admin&action=item&id={$value.id}" class="ajax_link">{$value.title}</a><input type="hidden" id="sortable_{$value.id}" class="sortable_hidden" value="{$value.id}"></td>
                      <td><a href="{$value.link}" target="_blank">{$value.link_original}</a></td>
                      <td>{$value.language}</td>
                      <td>{$value.url_id}</td>
                      <td>{$value.tooltip}</td>
                      <td>{$value.icon}</td>
                      <td>{$value.attributes}</td>
                      <td>
                        <a href="#" onclick="removeItem({$value.id})">
                            <img src="{$PANTHERA_URL}/images/admin/ui/delete.png" style="max-height: 22px;" alt="{function="localize('Delete')"}">
                        </a>
                      </td>
                  </tr>
                {/loop}
                {else}
                <tr><td colspan="8" style="text-align: center;">{function="localize('No any menu items found, you can add new links using button below', 'menuedit')"}</td></tr>
                {/if}
            </tbody>
      </table>
    </div>
