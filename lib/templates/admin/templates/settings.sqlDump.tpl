<script type="text/javascript">
    $(document).ready(function () {
        $('#settingsFormSave').submit(function () {
            panthera.jsonPOST( { data: '#settingsFormSave', messageBox: 'w2ui'});
            return false; 
        });
    });
</script>

<form action="?{function="Tools::getQueryString('GET', '', '_')"}" method="POST" id="settingsFormSave">
    <table style="margin: 0 auto; margin-top: 50px; margin-bottom: 50px;" class="formTable">
        <thead>
            <tr>
                <th colspan="2" class="formTableHeader" style="padding-top: 0px; padding-bottom: 30px;">
                    <p style="color: #e5ebef; padding: 0px; margin: 0px; margin-left: 30px;">{$uiTitlebar.title}</p>
                </th>
            </tr>
        </thead>

        <tbody>
            <th>{function="localize('Time interval')"}:</th>
            <td>
                <select name="timeInterval">
                    {loop="$cronIntervals"}
                        <option value="{$key}"{if="trim($value.expression) == trim($jobInterval)"} selected{/if}>{$value.title}</option>
                    {/loop}
                </select>
            </td>
        </tbody>
        
        <tfoot>
            <td colspan="2" style="padding-top: 35px;">
                <input type="button" value="{function="localize('Cancel')"}" onclick="panthera.popup.close()" style="float: left; margin-left: 30px;">
                <input type="submit" value="{function="localize('Save')"}" style="float: right; margin-right: 30px;">
            </td>
        </tfoot>
    </table>
</form>
