{$site_header}
{include="ui.titlebar"}
{include="ui.dataModelManagementController.js"}

<div id="topContent">
    <div class="searchBarButtonArea">
    	{if="$object and $object -> exists()"}
    	<input type="button" value="{function="localize('Save')"}" onclick="$('#editForm').submit();">
    	<input type="button" value="{function="localize('Remove')"}" onclick="dataModelManagementRemove('{$object->categoryid}');">
    	{/if}
    	{if="$categoriesTree"}<input type="button" value="{function="localize('Create a new category', 'categories')"}" onclick="panthera.popup.toggle('?{function="Tools::getQueryString('GET', 'action=edit', '_')"}');">{/if}
    </div>
</div>

<?php
$_SERVER['print'] = True;
include getContentDir('templates/admin/helpers/categories.php');
?>

<div class="blockMenu">
	{if="$categoriesTree"}
		{loop="$categoriesTree"}
        	{$depth=0}
            {$z=$value}
                 
            {if="!$z.item"}
                {continue}
            {/if}
                 
            {function="categoriesTpl_getCategory($z)"}
            {$categoriesLi}
        {/loop}
	{else}
		<li>{function="localize('There are no any categories defined for this module', 'categories')"}</li>
	{/if}
</div>

<div class="ajax-content" style="text-align: center;">
	<form action="?{function="Tools::getQueryString('GET', 'action=edit', '_')"}" method="POST" id="editForm">
		{if="$object and $object -> exists()"}
	    <table style="min-width: 460px;">
	   		<thead>
	   			<tr>
	   				<th colspan="2">
	   					{function="localize('Editing category', 'categories')"}
	   				</th>
	   			</tr>
	   		</thead>
	   		
	   		<tbody>
	   			<tr>
	   				<td>{function="localize('Title', 'categories')"}:</td>
	   				<td><input type="text" name="object_title" value="{$object->title}"></td>
	   			</tr>
	   			
	   			<tr>
	   				<td>{function="localize('Priority', 'categories')"}:</td>
	   				<td><input type="number" name="object_priority" value="{$object->priority}"></td>
	   			</tr>
	   			
	   			<tr>
	   				<td>{function="localize('Public', 'categories')"}:</td>
	   				<td><input type="radio" name="object___public" value="1" {if="$object->__public"}checked{/if}> {function="localize('Yes')"} <input type="radio" name="object___public" value="0" {if="!$object->__public"}checked{/if}> {function="localize('No')"}</td>
	   			</tr>
	   			
	   			<tr>
	   				<td>{function="localize('Parent category', 'categories')"}:</td>
	   				<td>
	   					<select name="object_parentid">
	   						<option value=""></option>
	   						{loop="$GLOBALS['categoriesSelect']"}
	   							<option value="{$key}" {if="$object->parentid == $key"}selected{/if}>{$value}</option>
	   						{/loop}
	   					</select>
	   				</td>
	   			</tr>
	   		</tbody>
	    </table>
	    
	    <input type="hidden" name="postData" value="True">
	    <input type="hidden" name="object_categoryType" value="{$object->categoryType}">
	</form>
	
	<script type="text/javascript">
	$('#editForm').submit(function () {
		panthera.jsonPOST({data: '#editForm', success: function(response) {
			if (response.status == 'success') { navigateTo(window.location.href); }
		}});
		return false;
	});
	</script>
	
	{/if}
</div>