<script>

/**
  * Add item to category
  *
  * @author Mateusz Warzyński
  */

$('#add_item_form').submit(function () {
    panthera.jsonPOST({ data: '#add_item_form', messageBox: 'userinfoBox', success: function (response) {
            if (response.status == "success")
                navigateTo('?display=menuedit&action=category&cat='+jQuery('#cat_type').val());
        }
    });

    return false;

});
</script>

    <div class="titlebar">{"Menu editor"|localize:menuedit} - {"Adding item"|localize:menuedit}</div><br>

    <div class="msgSuccess" id="userinfoBox_success"></div>
    <div class="msgError" id="userinfoBox_failed"></div>

    <div class="grid-1">
      <form id="add_item_form" method="POST" action="?display=menuedit&action=add_item">
       <table class="gridTable">
        <thead>
            <tr>
                <th scope="col" class="rounded-company" style="width: 250px;">&nbsp;</th>
                <th>&nbsp;</th>
            </tr>
        </thead>

        <tfoot>
            <tr>
                <td colspan="7" class="rounded-foot-left"><em>Panthera menuedit - {"Adding item"|localize:menuedit}</em><span>
                <input type="submit" value="{"Save"|localize:messages}" style="float: right;"> <input type="button" value="{"Back"|localize}" onclick="navigateTo('{navigation::getBackButton()}');" style="float: right;">
            </tr>
        </tfoot>

        <tbody>
            <tr>
                <td>{"Title"|localize:menuedit}</td>
                <td><input type="text" name="item_title" style="width: 99%;"></td>
            </tr>
            <tr>
                <td>{"Link"|localize:menuedit}</td>
                <td><input type="text" name="item_link" style="width: 99%;"></td>
            </tr>
            <tr>
                <td>{"Language"|localize:menuedit}</td>
                <td>
                <select name="item_language">
                {foreach from=$item_language key=k item=i}
                    <option value="{$k}">{$k}</option>
                {/foreach}
                </select>

                </td>
            </tr>
            <tr>
                <td>{"SEO friendly name"|localize:menuedit} <small>({"Optional"|localize})</small></td>
                <td><input type="text" name="item_url_id" style="width: 99%;"></td>
            </tr>
            <tr>
                <td>{"Tooltip"|localize:menuedit} <small>({"Optional"|localize:menuedit})</small></td>
                <td><input type="text" name="item_tooltip" style="width: 99%;"></td>
            </tr>
            <tr>
                <td>{"Icon"|localize:menuedit} <small>({"Optional"|localize:menuedit})</small></td>
                <td><input type="text" name="item_icon" style="width: 99%;"></td>
            </tr>
            <tr>
                <td>{"Attributes"|localize:menuedit} <small>({"Optional"|localize:menuedit})</small></td>
                <td><input type="text" name="item_attributes" style="width: 99%;"></td>
            </tr>
        </tbody>

       </table>
       <input type="hidden" id="cat_type" name="cat_type" value="{$cat_type}">

      </form>
    </div>