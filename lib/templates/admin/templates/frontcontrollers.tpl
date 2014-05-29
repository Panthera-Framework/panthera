{$site_header}
{include="ui.titlebar"}

<div id="topContent" style="min-height: 50px;">
    <div class="searchBarButtonArea">
    	<input type="button" value="{function="localize('List of page controllers', 'debug')"}" onclick="navigateTo('?display=ajaxpages&cat=admin');">
        <input type="button" value="{function="localize('Refresh')"}" onclick="navigateTo(window.location.href);">
    </div>
</div>

<div class="ajax-content" style="text-align: center;">
    <table style="display: inline-block;">
            <thead>
                <tr>
                    <th>
                        <b>{function="localize('Name', 'frontcontrollers')"}</b>
                    </th>
                    
                    <th>
                        <b>{function="localize('Linked from Panthera Lib', 'frontcontrollers')"}</b>
                    </th>
                    
                    <th>
                        <b>{function="localize('Modification time', 'ajaxpages')"}</b>
                    </th>
                </tr>
            </thead>
            <tbody>
                 {loop="$list"}
                    <tr>
                        <td>{$value.name}</td>
                        <td>{if="$value.linked"}{function="localize('Yes')"}{else}{function="localize('No')"}{/if}</td>
                        <td>{$value.modtime}</td>
                    </tr>
                 {/loop}
            </tbody>
    </table>
</div>
