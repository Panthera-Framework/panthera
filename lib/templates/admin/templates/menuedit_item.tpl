{$site_header}

<script type="text/javascript">

    /**
      * Save changes to database (item)
      *
      * @author Mateusz Warzyński
      */

    $('#save_form').submit(function () {
        panthera.jsonPOST({ data: '#save_form', messageBox: 'w2ui'});

        return false;

    });
    
</script>

{include="ui.titlebar"}

<form id="save_form" method="POST" action="?display=menuedit&cat=admin&action=save_item">

<div id="topContent" style="min-height: 50px;">
    <div class="searchBarButtonArea">
        <input type="submit" value="{function="localize('Save')"}" onclick="">
    </div>
</div>

<!-- Ajax content -->

<div class="ajax-content" style="text-align: center;">
        <table style="display: inline-block;">
            <thead>
                <tr>
                    <th scope="col" class="rounded-company" style="width: 250px;">&nbsp;</th>
                    <th>&nbsp;</th>
                </tr>
            </thead>

            <tbody>
                <tr>
                    <td>{function="localize('Title', 'menuedit')"}</td>
                    <td><input type="text" name="item_title" value="{$item_title}" style="width: 99%;"></td>
                </tr>
                <tr>
                    <td>{function="localize('Link', 'messages')"}</td>
                    <td><input type="text" name="item_link" value="{$item_link}" style="width: 99%;"></td>
                </tr>
                <tr>
                    <td>{function="localize('Language', 'menuedit')"}</td>
                    <td>
                        <select name="item_language">
                        {loop="$item_language"}
                        <option value="{$key}"{if="$value == True"} selected{/if}>{$key}</option>
                        {/loop}
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>{function="localize('SEO friendly name', 'menuedit')"} <small>({function="localize('Optional', 'menuedit')"})</small></td>
                    <td><input type="text" name="item_url_id" value="{$item_url_id}" style="width: 99%;"></td>
                </tr>
                <tr>
                    <td>{function="localize('Tooltip', 'menuedit')"} <small>({function="localize('Optional', 'menuedit')"})</small></td>
                    <td><input type="text" name="item_tooltip" value="{$item_tooltip}" style="width: 99%;"></td>
                </tr>
                <tr>
                    <td>{function="localize('Icon', 'menuedit')"} <small>({function="localize('Optional', 'menuedit')"})</small></td>
                    <td><input type="text" name="item_icon" value="{$item_icon}" style="width: 99%;"></td>
                </tr>
                <tr>
                    <td>{function="localize('Attributes', 'menuedit')"} <small>({function="localize('Optional', 'menuedit')"})</small></td>
                    <td><input type="text" name="item_attributes" value='{$item_attributes}' style="width: 99%;"></td>
                </tr>
            </tbody>
        </table>
        <input type="hidden" name="item_id" value="{$item_id}">
        <input type="hidden" id="cat_type" name="cat_type" value="{$cat_type}">
</div>

</form>