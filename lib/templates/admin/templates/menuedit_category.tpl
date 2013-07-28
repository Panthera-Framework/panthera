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
    panthera.jsonPOST({ url: "?display=menuedit&action=save_order", data: 'id='+id+'&order='+getTableOrder(), spinner: spinner});

    return false;
}

/**
  * Remove menu item from database
  *
  * @author Mateusz Warzyński
  */

function removeItem(id)
{
    panthera.jsonPOST({ url: '{$AJAX_URL}?display=menuedit&action=remove_item&item_id='+id, data: '', messageBox: 'userinfoBox', spinner: spinner, success: function (response) {
            if (response.status == "success") {
                jQuery('#item_'+id).slideUp();
                jQuery('#item_'+id).remove();
            }
        }
    });

    return false;
}
</script>

    <div class="titlebar">{function="localize('Menu editor', 'menuedit')"} - {function="localize('Edit menu', 'menuedit')"} ({function="localize('To change sequence of items in the category, you can drag & drop them', 'menuedit')"}).</div><br>

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
                        <input type="button" value="{function="localize('Add new link', 'menuedit')"}" style="float: right;" onclick="navigateTo('_ajax.php?display=menuedit&action=new_item&cat={$category}');">
                        <input type="button" value="{function="localize('Save order', 'menuedit')"}" style="float: right;" onclick="saveMenuOrder('{$category}');">
                        <input type="button" value="{function="localize('Back', 'messages')"}" onclick="navigateTo('?display=menuedit');" style="float: right;"></td>
                      </td>
                  </tr>
            </tfoot>

            <tbody>
                {loop="$menus"}
                  <tr id="item_{$value.id}">
                      <td><a href="{$AJAX_URL}?display=menuedit&action=item&id={$value.id}" class="ajax_link">{$value.title}</a><input type="hidden" id="sortable_{$value.id}" class="sortable_hidden" value="{$value.id}"></td>
                      <td><a href="{$value.link}" target="_blank">{$value.link_original}</a></td>
                      <td>{$value.language}</td>
                      <td>{$value.url_id}</td>
                      <td>{$value.tooltip}</td>
                      <td>{$value.icon}</td>
                      <td>{$value.attributes}</td>
                      <td>
                        <input type="button" value="{function="localize('Delete')"}" onclick="removeItem({$value.id})">
                      </td>
                  </tr>
                {/loop}
            </tbody>
      </table>
    </div>
