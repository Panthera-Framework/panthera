<script type="text/javascript">

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

    <h2 class="popupHeading">{function="localize('Adding item', 'menuedit')"}</h2>
    <div class="msgSuccess" id="menuInfoBox_success"></div>
    <div class="msgError" id="menuInfoBox_failed"></div>

    <div class="grid-1">
      <form id="add_item_form" method="POST" action="?display=menuedit&cat=admin&action=add_item">
       <table class="gridTable">
        <thead>
            <tr>
                <th>&nbsp;</th>
                <th>&nbsp;</th>
            </tr>
        </thead>

        <tfoot>
            <tr>
                <td colspan="7" class="rounded-foot-left"><em>Panthera menuedit - {function="localize('Adding item', 'menuedit')"}</em><span>
                <span style="float: right;">
                    <input type="submit" value="" style="background-image: url({$PANTHERA_URL}/images/admin/list-add.png); background-position:  0px 0px; background-repeat: no-repeat; width: 50px; height: 50px; float: right;">
                </span>
            </tr>
        </tfoot>

        <tbody>
            <tr>
                <td>{function="localize('Title', 'menuedit')"}</td>
                <td><input type="text" name="item_title" style="width: 99%;" value="{$title}"></td>
            </tr>
            <tr>
                <td>{function="localize('Link', 'menuedit')"}</td>
                <td><input type="text" name="item_link" style="width: 99%;" value="{$link}"></td>
            </tr>
            <tr>
                <td>{function="localize('Language', 'menuedit')"}</td>
                <td>
                    <select name="item_language">
                    {loop="$languages"}
                        <option value="{$key}" {if="$currentLanguage == $key"}selected{/if}>{$key}</option>
                    {/loop}
                    </select>
                </td>
            </tr>
            
            <tr>
                <td>{function="localize('Category', 'menuedit')"}</td>
                <td>
                    <select name="cat_type">
                    {loop="$categories"}
                        <option value="{$value->type_name}">{$value->title}</option>
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
      </form>
    </div>
