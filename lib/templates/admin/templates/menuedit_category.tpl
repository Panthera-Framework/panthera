<script type="text/javascript">

/**
  * Make our elements sortable... (?)
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

/**
  * Remove menu item from database
  *
  * @author Mateusz Warzyński
  */

function removeItem(id)
{
    panthera.jsonPOST({ url: '{$AJAX_URL}?display=menuedit&action=remove_item&item_id='+id, data: '', messageBox: 'userinfoBox', success: function (response) {
            if (response.status == "success")
                jQuery('#item_'+id).remove();
        }
    });

    return false;
}
</script>

    <div class="titlebar">{"Menu editor"|localize:menuedit} - {"Edit menu"|localize:menuedit} ({"To change sequence of items in the category, you can drag & drop them"|localize:menuedit}).</div><br>

    <div class="msgSuccess" id="userinfoBox_success"></div>
    <div class="msgError" id="userinfoBox_failed"></div>

    <div class="grid-1">
      <table class="gridTable">
            <thead>
                  <tr>
                      <th scope="col" class="rounded-company" style="width: 250px;">{"Title"|localize:menuedit}</th>
                      <th>{"Link"|localize:menuedit}</th>
                      <th>{"Language"|localize:menuedit}</th>
                      <th>{"SEO friendly name"|localize:menuedit}</th>
                      <th>{"Tooltip"|localize:menuedit}</th>
                      <th>{"Icon"|localize:menuedit}</th>
                      <th>{"Attributes"|localize:menuedit}</th>
                      <th>{"Options"|localize:messages}</th>
                  </tr>
            </thead>

            <tfoot>
                  <tr>
                      <td colspan="8" class="rounded-foot-left"><em>Panthera menuedit - {"List of items"|localize:menuedit}</em><span>
                        <input type="button" value="{"Add new link"|localize:menuedit}" style="float: right;" onclick="navigateTo('_ajax.php?display=menuedit&action=new_item&cat={$category}');">
                        <input type="button" value="{"Save order"|localize:menuedit}" style="float: right;" onclick="saveMenuOrder('{$category}');">
                        <input type="button" value="{"Back"|localize:messages}" onclick="navigateTo('{navigation::getBackButton()}');" style="float: right;"></td>
                      </td>
                  </tr>
            </tfoot>

            <tbody>
                {foreach from=$menus key=k item=i}
                  <tr id="item_{$i.id}">
                      <td><a href="{$AJAX_URL}?display=menuedit&action=item&id={$i.id}" class="ajax_link">{$i.title}</a><input type="hidden" id="sortable_{$i.id}" class="sortable_hidden" value="{$i.id}"></td>
                      <td><a href="{$i.link}" target="_blank">{$i.link_original}</a></td>
                      <td>{$i.language}</td>
                      <td>{$i.url_id}</td>
                      <td>{$i.tooltip}</td>
                      <td>{$i.icon}</td>
                      <td>{$i.attributes}</td>
                      <td>
                        <input type="button" value="{"Delete"|localize}" onclick="removeItem({$i.id})">
                      </td>
                  </tr>
                {/foreach}
            </tbody>
      </table>
    </div>