<script>

/**
  * Add item to category
  *
  * @author Mateusz Warzyński
  */

$('#add_item_form').submit(function () {
    panthera.jsonPOST({ data: '#add_item_form', messageBox: 'userinfoBox', success: function (response) {
            if (response.status == "success")
                navigateTo('?display=menuedit&cat=admin&action=category&category={$cat_type}');
        }
    });

    return false;

});
</script>

    <div class="titlebar">{function="localize('Menu editor', 'menuedit')"} - {function="localize('Adding item', 'menuedit')"}{include="_navigation_panel"}</div><br>

    <div class="msgSuccess" id="userinfoBox_success"></div>
    <div class="msgError" id="userinfoBox_failed"></div>

    <div class="grid-1">
      <form id="add_item_form" method="POST" action="?display=menuedit&cat=admin&action=add_item">
       <table class="gridTable">
        <thead>
            <tr>
                <th scope="col" class="rounded-company" style="width: 250px;">&nbsp;</th>
                <th>&nbsp;</th>
            </tr>
        </thead>

        <tfoot>
            <tr>
                <td colspan="7" class="rounded-foot-left"><em>Panthera menuedit - {function="localize('Adding item', 'menuedit')"}</em><span>
                <div style="float: right;">
                    <input type="submit" value="{function="localize('Save', 'messages')"}" style="float: right;"> <input type="button" value="{function="localize('Back')"}" onclick="navigateTo('?display=menuedit&cat=admin&action=category&cat={$cat_type}');">
                </div>
            </tr>
        </tfoot>

        <tbody>
            <tr>
                <td>{function="localize('Title', 'menuedit')"}</td>
                <td><input type="text" name="item_title" style="width: 99%;"></td>
            </tr>
            <tr>
                <td>{function="localize('Link', 'menuedit')"}</td>
                <td><input type="text" name="item_link" style="width: 99%;"></td>
            </tr>
            <tr>
                <td>{function="localize('Language', 'menuedit')"}</td>
                <td>
                <select name="item_language">
                {loop="$item_language"}
                    <option value="{$key}">{$key}</option>
                {/loop}
                </select>

                </td>
            </tr>
            <tr>
                <td>{function="localize('SEO friendly name', 'menuedit')"} <small>({function="localize('Optional', 'menuedit')"})</small></td>
                <td><input type="text" name="item_url_id" style="width: 99%;"></td>
            </tr>
            <tr>
                <td>{function="localize('Tooltip', 'menuedit')"} <small>({function="localize('Optional', 'menuedit')"})</small></td>
                <td><input type="text" name="item_tooltip" style="width: 99%;"></td>
            </tr>
            <tr>
                <td>{function="localize('Icon', 'menuedit')"} <small>({function="localize('Optional', 'menuedit')"})</small></td>
                <td><input type="text" name="item_icon" style="width: 99%;"></td>
            </tr>
            <tr>
                <td>{function="localize('Attributes', 'menuedit')"} <small>({function="localize('Optional', 'menuedit')"})</small></td>
                <td><input type="text" name="item_attributes" style="width: 99%;"></td>
            </tr>
        </tbody>

       </table>
       <input type="hidden" id="cat_type" name="cat_type" value="{$cat_type}">

      </form>
    </div>
