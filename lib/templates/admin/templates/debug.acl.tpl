{include="ui.titlebar"}

<div id="topContent">
    {$uiSearchbarName="uiTop"}
    {include="ui.searchbar"}
    
    <div class="searchBarButtonArea">
        <input type="button" value="{function="localize('Apply filter', 'qmessages')"}" onclick="$('#submitForm').submit();">
    </div>
</div>

<div class="ajax-content" style="text-align: center;">
	<form action="?display=debug.acl&cat=admin" method="GET" id="submitForm">
	<input type="hidden" name="display" value="debug.acl">
	<input type="hidden" name="cat" value="admin">
	
	<div class="tipBlock" style="width: 45%;">
        <div class="tipBlockInside">
            {function="localize('"Type/Group" can be for example "uploadedFile", "Object ID" it\'s id, and it have attribute of "Tag Name" and its value is "Value". Displaying only 0-1000 records', 'ajaxpages')"}
        </div> 
    </div>
	
	<table style="margin: 0px auto;">
		<thead>
			<tr>
				<th>{function="localize('Type/Group', 'debug')"}</th>
				<th>{function="localize('Object ID', 'debug')"}</th>
				<th>{function="localize('Tag name', 'debug')"}</th>
				<th>{function="localize('Value', 'debug')"}</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td><input type="text" name="type" value="{$filterType}"></td>
				<td><input type="text" name="user" value="{$filterUser}"></td>
				<td><input type="text" name="tag" value="{$filterTag}"></td>
				<td><input type="text" name="value" value="{$filterValue}"></td>
			</tr>
			
			{if="$tags"}
			{loop="$tags"}
			<tr>
				<td><a href="?display=debug.acl&cat=admin&tag={$value.name}">{$value.name}</a></td>
				<td><a href="?display=debug.acl&cat=admin&type={$value.type}">{$value.type}</a></td>
				<td>{$value.value|print_r_html:true}</td>
				<td><a href="?display=debug.acl&cat=admin&user={$value.userid}">{$value.userid}</a></td>
			</tr>
			{/loop}
			{/if}
		</tbody>
	</table>
	</form>
</div>