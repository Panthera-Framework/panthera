{$site_header}
{include="ui.titlebar"}

<div id="topContent">
    <div class="searchBarButtonArea">
        <input type="button" value="{function="localize('Manage backups', 'database')"}" onclick="navigateTo('?display=sqldump&cat=admin')">
        <input type="button" value="{function="localize('Manage permissions', 'messages')"}" onclick="panthera.popup.toggle('?display=acl&cat=admin&popup=true&name=can_manage_databases');">
    </div>
</div>

<div id="popupOverlay" style="text-align: center; padding-top: 20px; padding-bottom: 0px;"></div>
<!-- Content -->
<div class="ajax-content" style="text-align: center;">

    <table style="width: 80%; margin: 0 auto; margin-bottom: 30px;">
        <thead>
            <tr>
                <th colspan="5"><b>{function="localize('Connection informations', 'database')"}:</b></th>
            </tr>
        </thead>
        <tbody>
            {loop="$sql_attributes"}
            <tr>
                <td>{$value.name}
                <td>{$value.value}</td>
            </tr>
            {/loop}
        </tbody>
    </table>
    
    <table style="margin: 0 auto; width: 80%;">
        <thead>
            <tr>
                <th colspan="2"><b>Panthera - {function="localize('database driver configuration', 'database')"}:</b></th>
            </tr>
        </thead>
        <tbody>
            {loop="$panthera_attributes"}
            <tr>
                <td>{$value.name}</td>
                {if="$value.type == 'bool'"}
                {if="$value.value == true"}
                <td>{function="localize('True')"}</td>
                {else}
                <td>{function="localize('False')"}</td>
                {/if}
                {else}
                <td>{$value.value}</td>
                {/if}
            </tr>
            {/loop}
        </tbody>
    </table>
</div>
