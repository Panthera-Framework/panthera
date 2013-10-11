{$site_header}

<script type="text/javascript">

/**
  * Sends a POST message to regenerate autoloader cache
  *
  * @author Damian Kęska
  */

function regenerateAutoloader()
{
    panthera.jsonPOST({ url: '?display=autoloader&cat=admin', data: 'regenerate=True', messageBox: 'w2ui' });
}
</script>

{include="ui.titlebar"}

<div id="topContent" style="min-height: 50px;">
    <div class="searchBarButtonArea">
        <input type="button" value="{function="localize('Clear autoloader cache', 'autoloader')"}" onclick="regenerateAutoloader()">
    </div>
</div>

<div class="ajax-content" style="text-align: center;">
    <table style="display: inline-block;">
        <thead>
            <tr>
                <th>
                    <b>{function="localize('class', 'autoloader')"}</b>
                </th>
                <th>
                    <b>{function="localize('module', 'autoloader')"}</b>
                </th>
            </tr>
        </thead>
        <tbody>
            {loop="$autoloader"}
            <tr>
                <td><b>class</b> {$key}</td>
                <td>{$value}</td>
            </tr>
            {/loop}
        </tbody>
    </table>
</div>
