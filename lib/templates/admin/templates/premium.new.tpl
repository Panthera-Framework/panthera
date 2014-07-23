<link rel="stylesheet" type="text/css" media="all" href="{$PANTHERA_URL}/css/admin/jquery-ui-timepicker-addon.css">
<script type="text/javascript" src="{$PANTHERA_URL}/js/admin/jquery-ui-timepicker-addon.js"></script>

<style>
/*.formTable tbody td {
    padding-right: 0px;
}

.tableRightColumn {
    padding-right: 90px;
}*/

.ui-datepicker-trigger {
	margin-left: 9px;
}
</style>

<form action="?{function="Tools::getQueryString('GET', '', '_')"}" method="POST" id="objectSubmitForm">
    <table class="formTable" style="margin: 0 auto; margin-bottom: 30px;">
        <thead>
            <tr>
                <td colspan="2" class="formTableHeader" style="padding-top: 0px; padding-bottom: 30px;">
                    <p style="color: #e5ebef; padding: 0px; margin: 0px; margin-left: 30px;">
                    	{if="$dataObject"}{function="localize('Editing premium account', 'premium')"}{else}{function="localize('Add premium account', 'premium')"}{/if}
                    </p>
                </td>
            </tr>
        </thead>
        <tbody>
            <tr>
                <th>{function="localize('User login', 'premium')"}:</th>
                <td class="tableRightColumn">
                	<div class="ui-widget">
                		<input type="text" name="object_userlogin" id="object_userlogin" style="width: 300px;" {if="$dataObject"} value="{$dataObject->getUser()->login}"{/if}>
                	</div>
                </td>
            </tr>
            
            <tr>
                <th>{function="localize('Premium type', 'premium')"}:</th>
                <td class="tableRightColumn">
                	<select name="object_premiumid">
                		{loop="$premiumTypes"}
                			<option value="{$value->premiumid}">{$value->title}</option>
                		{/loop}
                	</select>
                </td>
            </tr>
            
            <tr>
                <th>{function="localize('Starts', 'premium')"}:</th>
                <td class="tableRightColumn">
                	<input type="text" name="object_starts" id="object_starts" style="width: 300px;" value="{if="$dataObject"}{$dataObject->starts}{else}{function="date('Y-m-d H:i:s')"}{/if}">
                </td>
            </tr>
            
            <tr>
                <th>{function="localize('Expires', 'premium')"}:</th>
                <td class="tableRightColumn">
                	<input type="text" name="object_expires" id="object_expires" style="width: 149px; min-width: 149px;" {if="$dataObject"} value="{$dataObject->expires}"{/if}>
                	<select id="increaseDate" style="width: 120px;" onchange="updateExpiresField();">
                		<option value=""></option>
                		<option value="+30 days">+30 {function="localize('days')"}</option>
                		<option value="+3 months">+3 {function="localize('months')"}</option>
                		<option value="+6 months">+6 {function="localize('months')"}</option>
                		<option value="+9 months">+9 {function="localize('months')"}</option>
                		<option value="+1 year">+1 {function="localize('years')"}</option>
                		<option value="+2 years">+2 {function="localize('years')"}</option>
                	</select>
                </td>
            </tr>
            
            {if="$activatePermissions"}
            <tr>
                <th title="{function="localize('The premium is active or not', 'premium')"}">{function="localize('Activated', 'premium')"}:</th>
                <td class="tableRightColumn">
                	<input type="radio" name="object_active" value="1" {if="($dataObject and $dataObject->active) or !$dataObject"}checked{/if}> {function="localize('Yes')"} <input type="radio" name="object_active" value="0" {if="$dataObject and !$dataObject->active"}checked{/if}> {function="localize('No')"} 
                </td>
            </tr>
            {/if}
            
            <tr>
                <th title="{function="localize('Physical card number', 'premium')"}">{function="localize('Card number', 'premium')"}:</th>
                <td class="tableRightColumn">
                	<input type="text" name="object_additionalfield1" id="object_additionalfield1" style="width: 300px;" {if="$dataObject"} value="{$dataObject->additionalfield1}"{/if}>
                </td>
            </tr>
        </tbody>
        
        <tfoot>
            <tr>
                <td colspan="2" style="padding-top: 35px;">
                	<input type="hidden" name="postData" value="saveRequest">
                    <input type="button" value="{function="localize('Cancel')"}" onclick="panthera.popup.close()" style="float: left; margin-left: 30px;">
                    {if="$dataObject"}
                    <input type="hidden" name="placeid" value="{$dataObject->placeid}">
                    <input type="hidden" name="obiectID" value="{$dataObject->eventid}">
                    <input type="submit" value="{function="localize('Save')"}" style="float: right; margin-right: 30px;">
                    {else}
                    <input type="submit" value="{function="localize('Add premium to user', 'premium')"}" style="float: right; margin-right: 30px;">
                    {/if}
                </td>
            </tr>
        </tfoot>
    </table>
    </form>
        
    <script type="text/javascript">
    {include="ui.dataModelManagementControllerPopup.js"}
    
    function updateExpiresField()
    {
    	if (!$('#increaseDate').val())
    		return false;
    
    	panthera.jsonPOST({url: '?display=premium&cat=admin&action=modifyDate', data: 'date='+encodeURIComponent($('#object_expires').val())+'&modifier='+encodeURIComponent($('#increaseDate').val()), success: function (response) {
	                if (response.status == 'success')
	                	$('#object_expires').val(response.newDate);
	    }});
    }
    
    $(function() {
		$( "#object_expires" ).datetimepicker({
			showOn: "button",
		    buttonImage: "images/admin/calendar.gif",
		    buttonImageOnly: true
		});
		
		$( "#object_expires" ).datetimepicker("option", "dateFormat", 'yy-mm-dd');
		$( "#object_expires" ).val("{if="$dataObject"}{$dataObject->expires}{/if}");
		
		$( "#object_starts" ).datetimepicker({
			showOn: "button",
		    buttonImage: "images/admin/calendar.gif",
		    buttonImageOnly: true
		});
		
		$( "#object_starts" ).datetimepicker("option", "dateFormat", 'yy-mm-dd');
		$( "#object_starts" ).val("{if="$dataObject"}{$dataObject->starts}{else}{function="date('Y-m-d H:i:s')"}{/if}");
		
		$( "#object_userlogin").click(function() {
	        $( "#object_userlogin" ).autocomplete({
	          source: function (request, uiResponse) {
	            //query = request.term;
	            
	            panthera.jsonPOST({url: '?display=users&cat=admin&action=getUsersAPI', data: 'query='+$('#object_userlogin').val(), success: function (response) {
	                if (response.status == 'success')
	                    uiResponse(response.result);
	            }});
			}});
		});
	});
    </script>
