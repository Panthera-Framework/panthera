{$site_header}
{include="ui.titlebar"}

<div id="topContent">
    <div class="searchBarButtonArea">
        <input type="button" value="{function="localize('Update')"}" onclick="navigateTo('?display=sqldump&cat=admin')">
    </div>
</div>

<div class="ajax-content" style="text-align: center;">
    <table style="margin: 0 auto; width: 80%;">
        <thead>
            <tr>
                <th>{function="localize('Table', 'database')"}</th>
                <th>{function="localize('MySQL template', 'database')"}</th>
                <th>{function="localize('SQLite3 template', 'database')"}</th>
                <th>{function="localize('Imported to database', 'database')"}</th>
                <th>{function="localize('Integrity check', 'database')"}</th>
            </tr>
        </thead>
        
        <tbody>
            {loop="$tables"}
            <tr>
                <td>{$key}</td>
                <td>{if="$value.hasTemplate_mysql"}<span title="{$value.hasTemplate_mysql}">{function="localize('Yes')"}</span>{else}<span style="color: red;" title="{function="localize('Warning! Missing MySQL template file for this table!', 'database')"}"><b>{function="localize('No')"}</b></span>{/if}</td>
                <td>{if="$value.hasTemplate_sqlite3"}<span title="{$value.hasTemplate_sqlite3}">{function="localize('Yes')"}</span>{else}<span style="color: red;" title="{function="localize('Warning! Missing SQLite3 template file for this table!', 'database')"}"><b>{function="localize('No')"}</b></span>{/if}</td>
                <td>{if="$value.isInDB"}{function="localize('Yes')"}{else}<span style="color: red;" title="{function="localize('Warning! Table is not imported to database, but template is avaliable.', 'database')"}"><b>{function="localize('No')"}</b></span>{/if}</td>
                <td>{if="!isset($value.integrityCheck)"}?{else}
                
                {/if}</td>
            </tr>
            {/loop}
        </tbody>
    </table>
</div>
