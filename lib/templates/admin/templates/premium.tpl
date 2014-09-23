{$site_header}
{include="ui.titlebar"}

<div id="topContent">
    {$uiSearchbarName="uiTop"}
    {include="ui.searchbar"}
    <div class="searchBarButtonArea">
    	<input type="button" value="{function="localize('Validate a card', 'premium')"}" style="float: right;" onclick="panthera.popup.toggle('element:#validateCardPopup');">
    	<input type="button" value="{function="localize('Add premium to user', 'premium')"}" style="float: right;" onclick="panthera.popup.toggle('?{function="Tools::getQueryString(null, 'action=edit&objectGroupID='.$objectGroupID, '_')"}');">
    </div>
</div>

<div id="validateCardPopup" style="display: none;">
	<form action="?{function="Tools::getQueryString('GET', 'action=validateUserCard', '_')"}" method="POST" id="objectSubmitForm">
    <table class="formTable" style="margin: 0 auto; margin-bottom: 30px;">
        <thead>
            <tr>
                <td colspan="2" class="formTableHeader" style="padding-top: 0px; padding-bottom: 30px;">
                    <p style="color: #e5ebef; padding: 0px; margin: 0px; margin-left: 30px;">
                    	{function="localize('Validate a card', 'premium')"}
                    </p>
                </td>
            </tr>
        </thead>
        <tbody>
            <tr>
                <th>{function="localize('User login', 'premium')"}:</th>
                <td class="tableRightColumn">
                	<div class="ui-widget">
                		<input type="text" name="username" id="card_username" style="width: 300px;" placeholder="admin">
                	</div>
                </td>
            </tr>
            
            <tr>
                <th>{function="localize('Card number', 'premium')"}:</th>
                <td class="tableRightColumn">
                	<input type="text" name="card" id="card_card" style="width: 300px;" placeholder="123456789">
                </td>
            </tr>
            
        <tfoot>
            <tr>
                <td colspan="2" style="padding-top: 35px;">
                	<input type="hidden" name="postData" value="saveRequest">
                    <input type="button" value="{function="localize('Cancel')"}" onclick="panthera.popup.close()" style="float: left; margin-left: 30px;">
                    <input type="submit" value="{function="localize('Submit')"}" style="float: right; margin-right: 30px;">
                </td>
            </tr>
        </tfoot>
    </table>
    </form>
    
    <script type="text/javascript">
    	{include="ui.dataModelManagementControllerPopup.js"}
	</script>
</div>

<div class="ajax-content" style="text-align: center;">
	<div style="margin: 0 auto; display: inline-block;">
	    <table style="min-width: 360px; margin: 0 auto;">
	    	<thead>
	        	<tr>
	        		<th>
	        			<b>{function="localize('User', 'premium')"}</b>
	        		</th>
	        	
	        		<th>
	        		    <b>{function="localize('Premium type', 'premium')"}</b>
	        		</th>
	        		
	        		<th>
	        		    <b>{function="localize('Requested', 'premium')"}</b>
	        		</th>
	        	
	            	<th>
	                	<b>{function="localize('Activated', 'premium')"}</b>
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
                        <b>{function="localize('Notes', 'premium')"}</b>
                    </th>
	                
	                <th>
	                	<b>{function="localize('Options')"}</b>
	                </th>
	            </tr>
	        </thead>
	        
	        <tbody class="hovered">
	        	{if="$foundElements"}
	           	{loop="$foundElements"}
	           	<tr>
	           		<td>
	           			<a style="cursor: pointer;" onclick="panthera.popup.toggle('?display=premium&cat=admin&action=edit&objectID={$value->id}', 'premiumAccountEdit');">
	           				{$value->getUser()->getName()}
	           			</a> {if="$value->awaiting_activation"}<span style="color: red;"><b>(!)</b></span>{/if}
	           		</td>
	           		
	           		<td>
	           			<small>{$value->premiumTitle}</small>
	           		</td>
	           		
	           		<td>
	           			{$value->activationdate}
	           		</td>
	           		
	           		<td>
	           			{if="$value->active"}
	           				{$value->activationdate}
	           			{else}
	           				{function="localize('Not activated yet', 'premium')"}
	           			{/if}
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
	           				<span style="color: red;">{function="localize('No card number assigned', 'premium')"}</span>
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
	           		   {if="$value->awaiting_activation"}<b>{$value->notes|nl2br}</b>{else}{$value->notes|nl2br}{/if} 
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
	        </tbody>
	    </table>
	    
	    <div style="position: relative; text-align: left;" class="pager">{include="ui.pager"}</div>
    </div>
</div>

{include="ui.dataModelManagementController.js"}
