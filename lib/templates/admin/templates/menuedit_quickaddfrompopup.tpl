<script>

/**
  * Add item to category
  *
  * @author Mateusz Warzyński
  */

$('#add_item_form').submit(function () {
    panthera.jsonPOST({ data: '#add_item_form', messageBox: 'menuInfoBox', success: function (response) {
            if (response.status == "success")
                closePopup();
        }
    });

    return false;

});
</script>

    <h2 class="popupHeading">{"Adding item"|localize:menuedit}</h2>
    <div class="msgSuccess" id="menuInfoBox_success"></div>
    <div class="msgError" id="menuInfoBox_failed"></div>

    <div class="grid-1">
      <form id="add_item_form" method="POST" action="?display=menuedit&action=add_item">
       <table class="gridTable">
        <thead>
            <tr>
                <th>&nbsp;</th>
                <th>&nbsp;</th>
            </tr>
        </thead>

        <tfoot>
            <tr>
                <td colspan="7" class="rounded-foot-left"><em>Panthera menuedit - {"Adding item"|localize:menuedit}</em><span>
                <span style="float: right;">
                    <input type="button" value="{"Back"|localize}" onclick="closePopup();">&nbsp;&nbsp;
                    <input type="submit" value="{"Save"|localize:messages}">
                </span>
            </tr>
        </tfoot>

        <tbody>
            <tr>
                <td>{"Title"|localize:menuedit}</td>
                <td><input type="text" name="item_title" style="width: 99%;" value="{$title}"></td>
            </tr>
            <tr>
                <td>{"Link"|localize:menuedit}</td>
                <td><input type="text" name="item_link" style="width: 99%;" value="{$link}"></td>
            </tr>
            <tr>
                <td>{"Language"|localize:menuedit}</td>
                <td>
                    <select name="item_language">
                    {foreach from=$languages key=k item=i}
                        <option value="{$k}" {if $currentLanguage == $k}selected{/if}>{$k}</option>
                    {/foreach}
                    </select>
                </td>
            </tr>
            
            <tr>
                <td>{"Category"|localize:menuedit}</td>
                <td>
                    <select name="cat_type">
                    {foreach from=$categories key=k item=i}
                        <option value="{$i->type_name}">{$i->title}</option>
                    {/foreach}
                    </select>
                </td>
            </tr>
            
            <tr>
                <td>{"SEO friendly name"|localize:menuedit} <small>({"Optional"|localize:menuedit})</small></td>
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
      </form>
    </div>
