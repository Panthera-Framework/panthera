<form action="?{function="Tools::getQueryString('GET', '', '_')"}" method="POST" id="adSubmitForm">
    <table class="formTable" style="margin: 0 auto; margin-bottom: 30px;">
        <thead>
            <tr>
                <td colspan="2" class="formTableHeader" style="padding-top: 0px; padding-bottom: 30px;">
                    <p style="color: #e5ebef; padding: 0px; margin: 0px; margin-left: 30px;">
                    	{if="$adItem"}{function="localize('Editing advertisement', 'advertisements')"}{else}{function="localize('Creating new advertisement', 'advertisements')"}{/if}
                    </p>
                </td>
            </tr>
        </thead>
        <tbody>
            <tr>
                <th>{function="localize('Description')"}:</th>
                <td><input type="text" name="title" style="width: 300px;" {if="$adItem"} value="{$adItem->name}"{/if}></td>
            </tr>
            
            <tr>
                <th>{function="localize('Position', 'advertisements')"}:</th>
                <td><input type="number" name="position" value="{if="$adItem"}{$adItem->position}{else}1{/if}" min="1" style="width: 300px;" {if="$adItem"} value="{$adItem->position}"{/if}></td>
            </tr>
            
            {if="$places"}
            <tr>
            	<th>{function="localize('Adveristiment block', 'adveristiments')"}:</th>
            	<td>
            		<div class="select" style="float: none; width: 150px;">
	            		<select name="placename">
	            		{loop="$places"}
	            			<option value="{$value->placename}">{$value->title}</option>
	            		{/loop}
	            		</select>
            		</div>
            	</td>
            </tr>
            {/if}
            
            <tr>
                <th>{function="localize('HTML code', 'advertisements')"}:</th>
                <td><textarea name="htmlcode" style="width: 300px; height: 100px;">{if="$adItem"}{$adItem->htmlcode|htmlspecialchars}{/if}</textarea></td>
            </tr>
            
            <tr>
            	<th>{function="localize('Expiration', 'advertisements')"}:</th>
            	<td>
            		<input type="text" name="expiration" {if="$adItem"}value="{$adItem->expires}"{/if} id="expiration" title="{function="localize('Examples: +30 days, 01:20 19.06.2090, +2 hours')"}" style="width: 142px; min-width: 142px;"> &nbsp;
            		<div class="select" style="float: none; position: absolute; width: 150px;">
				        <select onchange="$('#expiration').val($(this).val());">
				        	<option value="" selected></option>
				        	<option value="0">{function="localize('never expires', 'advertisements')"}</option>
				        	<option value="+2 years">2 {function="localize('years')"}</option>
				        	<option value="+1 year">1 {function="localize('year')"}</option>
				        	<option value="+6 months">6 {function="localize('months')"}</option>
				        	<option value="+2 months">2 {function="localize('months')"}</option>
				        	<option value="+30 days">30 {function="localize('days')"}</option>
				            <option value="+7 days">7 {function="localize('days')"}</option>
				            <option value="+12 hours">12 {function="localize('hours')"}</option>
				            <option value="+6 hours">6 {function="localize('hours')"}</option>
				            <option value="+15 minutes">15 {function="localize('minutes')"}</option>
				        </select>
        			</div>
            	</td>
            </tr>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2" style="padding-top: 35px;">
                    <input type="button" value="{function="localize('Cancel')"}" onclick="panthera.popup.close()" style="float: left; margin-left: 30px;">
                    {if="$adItem"}
                    <input type="hidden" name="placename" value="{$adItem->placename}">
                    <input type="hidden" name="adId" value="{$adItem->adid}">
                    <input type="submit" value="{function="localize('Save')"}" style="float: right; margin-right: 30px;">
                    {else}
                    <input type="submit" value="{function="localize('Add new advertisement', 'advertisements')"}" style="float: right; margin-right: 30px;">
                    {/if}
                </td>
            </tr>
        </tfoot>
    </table>
    </form>
        
    <script type="text/javascript">
    $('#adSubmitForm').submit (function () {
        panthera.jsonPOST( { data: '#adSubmitForm', success: function (response) {
                if (response.status == 'success')
                    navigateTo(window.location.href);
        
            } 
        });
        return false;
    });
    </script>