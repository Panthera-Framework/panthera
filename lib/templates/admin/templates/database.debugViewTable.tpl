{$site_header}
{include="ui.titlebar"}

<style>
.diff_created {
	background-color: rgb(203, 255, 203) !important;
}

.diff_modified {
	background-color: rgb(255, 213, 160) !important;
}

.diff_removed {
	background-color: rgb(255, 194, 194) !important;
}
</style>

<div id="topContent">
    <div class="searchBarButtonArea">
        <input type="button" value="{function="localize('Update')"}" onclick="navigateTo('?display=database&cat=admin&action=debugTables&forceUpdateCache')">
    </div>
</div>

<div class="ajax-content" style="text-align: center;">
    <table style="margin: 0 auto; width: 80%;">
        <thead>
            <tr>
                <th>{function="localize('Column', 'database')"}</th>
                <th>{function="localize('Type', 'database')"}</th>
                <th>{function="localize('Length', 'database')"}</th>
                <th>{function="localize('Default', 'database')"}</th>
                <th>{function="localize('Null', 'database')"}</th>
                <th>{function="localize('Autoincrement', 'database')"}</th>
                <th>{function="localize('Primary key', 'database')"}</th>
                <th>{function="localize('Unique key', 'database')"}</th>
                <th>{function="localize('Foreign key', 'database')"}</th>
            </tr>
        </thead>
        
        <tbody>
            {loop="array_merge($diff.a.columns, $diff.b.columns, $diff.diff.columns)"}
	            {if="substr($key, 0, 7) == '__meta_'"}
	            	{continue}
	            {/if}
	            
            	<tr class="diff diff_{function="getMetaValue($key, $diff.diff.columns)"}">
            		<td>{$key}</td>
            		<td class="diff diff_{function="getMetaValue('type', $value)"}">{function="fallbackValue($value.type, $diff['b']['columns'][$key]['type'])"}</td>
            		<td class="diff diff_{function="getMetaValue('length', $value)"}">{function="fallbackValue($value.length, $diff['b']['columns'][$key]['length'])"}</td>
            		<td class="diff diff_{function="getMetaValue('default', $value)"}">{function="fallbackValue($value.default, $diff['b']['columns'][$key]['default'])"}</td>
            		<td class="diff diff_{function="getMetaValue('null', $value)"}">{if="isset($value.nukk)"}{function="toString($value.null)"}{else}{function="toString($diff['b']['columns'][$key]['null'])"}{/if}</td>
            		<td class="diff diff_{function="getMetaValue('autoIncrement', $value)"}">{if="isset($value.autoIncrement)"}{function="toString($value.autoIncrement)"}{else}{function="toString($diff['b']['columns'][$key]['autoIncrement'])"}{/if}</td>
            		<td class="diff diff_{function="getMetaValue('primaryKey', $value)"}">{if="isset($value.primaryKey)"}{function="toString($value.primaryKey)"}{else}{function="toString($diff['b']['columns'][$key]['primaryKey'])"}{/if}</td>
            		<td class="diff diff_{function="getMetaValue('uniqueKey', $value)"}">{if="isset($value.uniqueKey)"}{function="toString($value.uniqueKey)"}{else}{function="toString($diff['b']['columns'][$key]['uniqueKey'])"}{/if}</td>
            		<td class="diff diff_{function="getMetaValue('foreignKey', $value)"}">{if="isset($value.foreignKey)"}{function="toString($value.foreignKey)"}{else}{function="toString($diff['b']['columns'][$key]['foreignKey'])"}{/if}</td>
            	</tr>
            {/loop}
        </tbody>
    </table>
</div>
