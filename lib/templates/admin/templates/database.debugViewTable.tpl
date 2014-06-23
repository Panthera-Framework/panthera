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

<script type="text/javascript">
function mergeChanges()
{
	panthera.jsonPOST({url: '?display=database&cat=admin&action=debugViewTable&table={$tableName}', 'data': 'mergeTable={$tableName}', success: function (response) {
			if (response.status == 'success')
				navigateTo('?display=database&cat=admin&action=debugViewTable&table={$tableName}&forceUpdateCache');
		}
	});
}
</script>

<div id="topContent">
    <div class="searchBarButtonArea">
    	<div class="searchBarButtonAreaLeft">
    		<input type="button" value="{function="localize('Back')"}" onclick="navigateTo('?display=database&cat=admin&action=debugTables')">
    	</div>
    
    	{if="$diff.diff"}<input type="button" value="{function="localize('Merge changes to database')"}" onclick="mergeChanges();">{/if}
        <input type="button" value="{function="localize('Refresh')"}" onclick="navigateTo('?display=database&cat=admin&action=debugViewTable&table={$tableName}&forceUpdateCache')">
    </div>
</div>

<div class="ajax-content" style="text-align: center;">
	{if="$MySQLPatch"}
    <table style="margin: 0 auto; width: 80%; margin-bottom: 30px;">
    	<thead>
    		<tr>
    			<th class="tableTitleHeader">{function="localize('MySQL database patch', 'database')"}</th>
    		</tr>
    	</thead>
    	
    	<tbody>
    		<tr>
    			<td>{function="print_r_html($MySQLPatch, true)"}</td>
    		</tr>
    	</tbody>
    </table>
    {/if}

	{if="$columns"}
    <table style="margin: 0 auto; width: 80%;">
        <thead>
        	<tr>
    			<th class="tableTitleHeader" colspan="9">{function="localize('Table columns', 'database')"} - {$tableName}</th>
    		</tr>
        
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
            {loop="$columns"}
	            {if="substr($key, 0, 7) == '__meta_'"}
	            	{continue}
	            {/if}
	            
            	<tr class="diff diff_{function="getMetaValue($key, $diff.diff.columns)"}">
            		<td>{$key}</td>
            		<td class="diff diff_{function="getMetaValue('type', $value)"}">{function="Tools::fallbackValue($value.type, $diff['b']['columns'][$key]['type'])"}</td>
            		<td class="diff diff_{function="getMetaValue('length', $value)"}">{function="Tools::fallbackValue($value.length, $diff['b']['columns'][$key]['length'])"}</td>
            		<td class="diff diff_{function="getMetaValue('default', $value)"}">{function="Tools::fallbackValue($value.default, $diff['b']['columns'][$key]['default'])"}</td>
            		<td class="diff diff_{function="getMetaValue('null', $value)"}">{if="isset($value.nukk)"}{function="Tools::toString($value.null)"}{else}{function="Tools::toString($diff['b']['columns'][$key]['null'])"}{/if}</td>
            		<td class="diff diff_{function="getMetaValue('autoIncrement', $value)"}">{if="isset($value.autoIncrement)"}{function="Tools::toString($value.autoIncrement)"}{else}{function="Tools::toString($diff['b']['columns'][$key]['autoIncrement'])"}{/if}</td>
            		<td class="diff diff_{function="getMetaValue('primaryKey', $value)"}">{if="isset($value.primaryKey)"}{function="Tools::toString($value.primaryKey)"}{else}{function="Tools::toString($diff['b']['columns'][$key]['primaryKey'])"}{/if}</td>
            		<td class="diff diff_{function="getMetaValue('uniqueKey', $value)"}">{if="isset($value.uniqueKey)"}{function="Tools::toString($value.uniqueKey)"}{else}{function="Tools::toString($diff['b']['columns'][$key]['uniqueKey'])"}{/if}</td>
            		<td class="diff diff_{function="getMetaValue('foreignKey', $value)"}">{if="isset($value.foreignKey)"}{function="Tools::toString($value.foreignKey)"}{else}{function="Tools::toString($diff['b']['columns'][$key]['foreignKey'])"}{/if}</td>
            	</tr>
            {/loop}
        </tbody>
    </table>
    {/if}
    
    {if="$MySQLAttributes"}
    <table style="margin: 0 auto; width: 80%; margin-top: 30px;">
    	<thead>
    		<tr>
    			<th class="tableTitleHeader" colspan="2">{function="localize('MySQL attributes', 'database')"}</th>
    		</tr>
    		
    		<tr>
    			<th>{function="localize('Name')"}</th>
    			<th>{function="localize('Value')"}</th>
    		</tr>
    	</thead>
    	
    	<tbody>
    		{loop="$MySQLAttributes"}
    			<tr class="diff diff_{function="getMetaValue($key, $MySQLAttributes)"}">
    				<td>{$key}</td>
    				<td>{$value}</td>
    			</tr>
    		{/loop}
    	</tbody>
    </table>
    {/if}
    
    {if="$diff.diff"}
    <table style="margin: 0 auto; width: 80%; margin-top: 30px;">
    	<thead>
    		<tr>
    			<th class="tableTitleHeader">{function="localize('Raw diff', 'database')"}</th>
    		</tr>
    	</thead>
    	
    	<tbody>
    		<tr>
    			<td>{function="print_r_html($diff.diff, true)"}</td>
    		</tr>
    	</tbody>
    </table>
    {/if}
</div>
