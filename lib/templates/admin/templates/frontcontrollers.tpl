{$site_header}

{include="ui.titlebar"}
<div class="ajax-content" style="text-align: center;">
    <div style="margin: 0 auto; display: inline-block; width: 80%;">
        <h1>{function="localize('Front controllers', 'ajaxpages')"}</h1>
    </div><br>
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
