<script type="text/javascript">
function regenerateAutoloader()
{
    panthera.jsonPOST({ url: '?display=autoloader&cat=admin', data: 'regenerate=True', messageBox: 'w2ui' });
}
</script>

{include="ui.titlebar"}
<br>
<table class="gridTable">
    <tfoot>
        <tr>
            <td colspan="2" class="rounded-foot-left">
                <input type="button" value="{function="localize('Regenerate autoloader cache', 'messages')"}" onclick="regenerateAutoloader();" style="float: right; margin-right: 7px;">
            </td>
        </tr>
    </tfoot>
    
    <thead>
        <tr>
            <th>{function="localize('class')"}</th>
            <th>{function="localize('module')"}</th>
        </tr>
    </thead>
    
    <tbody>
        {loop="$autoloader"}
        <tr>
            <td><b>class</b> {$key}</td><td>{$value}</td>
        </tr>
        {/loop}
    </tbody>
</table>
