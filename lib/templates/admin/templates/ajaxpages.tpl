{include="ui.titlebar"}
<br>
<table class="gridTable">
    <thead>
        <tr>
            <th colspan="2"><b>{function="localize('Pages index', 'ajaxpages')"}:</b></th>
        </tr>
    </thead>
    <tfoot>
        <tr>
            <td colspan="2" class="rounded-foot-left">
                <input type="button" value="{function="localize('Manage permissions', 'messages')"}" onclick="createPopup('{$AJAX_URL}?display=acl&cat=admin&popup=true&name=can_see_ajax_pages');" style="float: right; margin-right: 7px;">
            </td>
        </tr>
    </tfoot>
    <tbody>
        {loop="$pages"}
        <tr>
            <td>{$value.location} / <a href="{$value.link}" class="ajax_link">{$value.name}</a></td>
        </tr>
        {/loop}
    </tbody>
</table>
