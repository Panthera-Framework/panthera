{$site_header}
{include="ui.titlebar"}

<div id="topContent">
    <div class="searchBarButtonArea">
    	{if="$inactive"}
    		{$inactiveArgs=''}
    		{$removeArgs='_,inactive'}
    	{else}
    		{$inactiveArgs='inactive=True'}
    		{$removeArgs='_'}
    	{/if}
    
    	{if="!isset($_GET['freeUsers'])"}
    	<input type="button" value="{function="localize('Sort by expiration', 'premium')"}" style="float: right;" onclick="navigateTo('?{function="Tools::getQueryString(null, 'sortBy=Expires', '_')"}');">
    	<input type="button" value="{function="localize('Sort alphabeticaly', 'premium')"}" style="float: right;" onclick="navigateTo('?{function="Tools::getQueryString(null, 'sortBy=A-Z', '_')"}');">
    	<input type="button" value="{if="$inactive"}{function="localize('Premium users', 'premium')"}{else}{function="localize('Users not extended premium', 'premium')"}{/if}" style="float: right;" onclick="navigateTo('?{function="Tools::getQueryString(null, $inactiveArgs, $removeArgs)"}');">
    	<input type="button" value="{function="localize('Free users', 'premium')"}" style="float: right;" onclick="navigateTo('?{function="Tools::getQueryString(null, 'freeUsers=True', '_,page')"}');">
    	{else}
    	<input type="button" value="{function="localize('Premium users', 'premium')"}" style="float: right;" onclick="navigateTo('?{function="Tools::getQueryString(null, '', '_,freeUsers')"}');">
    	{/if}
    </div>
</div>

<div class="ajax-content" style="text-align: center;">
	<div style="margin: 0 auto; display: inline-block;">
	    <table style="min-width: 360px; margin: 0 auto;">
	    	<thead>
	        	<tr>
	        		{if="!isset($_GET['freeUsers'])"}
	        		<th>
	        			<b>{function="localize('User', 'premium')"}</b>
	        		</th>
	        	
	        		<th>
	        		    <b>{function="localize('Premium type', 'premium')"}</b>
	        		</th>
	        		
	                <th>
	                	<b>{function="localize('Expires', 'premium')"}</b>
	                </th>
	                
	                <th>
	                	<b>{function="localize('Card number', 'premium')"}</b>
	                </th>
	                
	                <th>
	                	<b>{function="localize('Started', 'premium')"}</b>
	                </th>
	                
	                <th>
	                	<b>{function="localize('Options')"}</b>
	                </th>
	                {else}
	                <th>
	        			<b>{function="localize('User', 'premium')"}</b>
	        		</th>
	        		
	        		<th>
	        			<b>{function="localize('Account type', 'premium')"}</b>
	        		</th>
	        		
	        		<th>
	        			<b>{function="localize('Joined', 'users')"}</b>
	        		</th>
	                {/if}
	            </tr>
	        </thead>
	        
	        <tbody class="hovered">
	        	{if="!isset($_GET['freeUsers'])"}
	        	{if="$foundElements"}
	           	{loop="$foundElements"}
	           	<tr>
	           		<td>
	           			<a style="cursor: pointer;" onclick="navigateTo('?display=users&cat=admin&action=account&uid={$value->getUser()->id}');">
	           				{$value->getUser()->getName()}
	           			</a>
	           		</td>
	           		
	           		<td>
	           			<small>{$value->premiumTitle}</small>
	           		</td>
	           		
	           		<td>
	           			{if="$value->active"}
	           				<a title="{$value->expires}">{$value->getExpirationTime()}</a>
	           			{else}
	           				{function="localize('Not activated yet', 'premium')"}
	           			{/if}
	           		</td>
	           		
	           		<td>
	           			{if="$value->additionalfield1"}
	           				<i><b>{$value->additionalfield1}</b></i>
	           			{else}
	           				{function="localize('No card number assigned', 'premium')"}
	           			{/if}
	           		</td>
	           		
	           		<td>
	           			{if="$value->requiresstart"}
	           				{function="localize('Waiting to start', 'premium')"}
	           			{else}
	           				{$value->starts}
	           			{/if}
	           		</td>
	           			
	           		<td>
	           			<img src="{$PANTHERA_URL}/images/admin/ui/edit.png" onclick="panthera.popup.toggle('?display=premium&cat=admin&action=edit&objectID={$value->id}', 'premiumAccountEdit');" style="max-height: 22px; cursor: pointer;" alt="{function="localize('Edit')"}" title="{function="localize('Edit')"}">
	           			<img src="{$PANTHERA_URL}/images/admin/ui/delete.png" onclick="dataModelManagementRemove('{$value->id}')" style="max-height: 22px; cursor: pointer;" alt="{function="localize('Remove')"}" title="{function="localize('Remove')"}">
	           		</td>
	           	</tr>
	           	{/loop}
	           	{else}
	           	<tr>
	           		<td colspan="8" style="text-align: center;">{function="localize('There are no any results found matching search criteria', 'dataModelManagementController')"}</td>
	           	</tr>
	           	{/if}
	           	{else}
	           	{loop="$freeUsers"}
	           		<tr>
	           			<td><a href="?display=users&cat=admin&action=account&uid={$value.id}" target="_blank" {if="$value.full_name"}title="{$value.login}"{/if}>{if="$value.full_name"}{$value.full_name}{else}{$value.login}{/if}</a></td>
	           			<td>{function="localize('Free account', 'premium')"}</td>
	           			<td>{$value.joined}</td>
	           		</tr>
	           	{/loop}
	           	{/if}
	        </tbody>
	    </table>
	    
	    <div style="position: relative; text-align: left;" class="pager">{include="ui.pager"} <div style="float: right;">{if="isset($_GET['freeUsers'])"}{function="slocalize('Total: %s', 'premium', $totalFreeUsers)"}{else}{function="slocalize('Premium: %s, regular users: %s', 'premium', $statsPremium, $statsFree)"}{/if}</div></div>
    </div>
</div>

{include="ui.dataModelManagementController.js"}
