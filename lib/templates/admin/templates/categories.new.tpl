<?php
$_SERVER['print'] = False;
include getContentDir('templates/admin/helpers/categories.php');
$_SERVER['print'] = False;
?>

{loop="$categoriesTree"}
        	{$depth=0}
            {$z=$value}
                 
            {if="!$z.item"}
                {continue}
            {/if}
                 
            {function="categoriesTpl_getCategory($z)"}
            {$categoriesLi}
        {/loop}

<form action="?{function="Tools::getQueryString('GET', '', '_,objectID')"}" method="POST" id="objectSubmitForm">
    <table class="formTable" style="margin: 0 auto; margin-bottom: 30px;">
        <thead>
            <tr>
                <td colspan="2" class="formTableHeader" style="padding-top: 0px; padding-bottom: 30px;">
                    <p style="color: #e5ebef; padding: 0px; margin: 0px; margin-left: 30px;">
                    	{function="localize('New category', 'categories')"}
                    </p>
                </td>
            </tr>
        </thead>
        <tbody>
            <tr>
	   			<th><span style="color: red;"><b>*</b></span> {function="localize('Title', 'categories')"}:</th>
	   			<td><input type="text" name="object_title"></td>
	   		</tr>
	   			
	   		<tr>
	   			<th>{function="localize('Priority', 'categories')"}:</th>
	   			<td><input type="number" name="object_priority"></td>
	   		</tr>
	   			
	   		<tr>
	   			<th><span style="color: red;"><b>*</b></span> {function="localize('Public', 'categories')"}:</th>
	   			<td><input type="radio" name="object___public" value="1"> {function="localize('Yes')"} <input type="radio" name="object___public" value="0" checked> {function="localize('No')"}</td>
	   		</tr>
	   			
	   		<tr>
	   			<th>{function="localize('Parent category', 'categories')"}:</th>
	   			<td>
	   				<select name="object_parentid">
	   					<option value=""></option>
	   					{loop="$GLOBALS['categoriesSelect']"}
	   						<option value="{$key}">{$value}</option>
	   					{/loop}
	   				</select>
	   			</td>
	   		</tr>
        </tbody>
        
        <tfoot>
            <tr>
                <td colspan="2" style="padding-top: 35px;">
                	<input type="hidden" name="object_categoryType" value="{$categoryType}">
                	<input type="hidden" name="postData" value="saveRequest">
                    <input type="button" value="{function="localize('Cancel')"}" onclick="panthera.popup.close()" style="float: left; margin-left: 30px;">
                    <input type="submit" value="{function="localize('Add category', 'categories')"}" style="float: right; margin-right: 30px;">
                </td>
            </tr>
        </tfoot>
    </table>
    </form>
        
    <script type="text/javascript">
    {include="ui.dataModelManagementControllerPopup.js"}
    </script>
