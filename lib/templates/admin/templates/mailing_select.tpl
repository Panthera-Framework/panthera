    {$uiSearchbarName="uiTop"}
    {include="ui.searchbar"}
    
    <div class="grid-2" id="groupTable" style="position: relative;">
        <table class="gridTable">
        <thead>
            <tr>
            	<th style="width: 35px;"></th>
                <th>{function="localize('Group name', 'mailing')"}</th>
            </tr>
        </thead>
            <tbody id="groupTableBody">
           {if="count($groups) == 0"}
           		<tr class="groupTableItem">
                	<td colspan="2">No results</td>
                </tr>
           {else}
            {loop="$groups"}
                <tr id="group_{$value.name}" class="groupTableItem">
                	<td><input type="checkbox" id="checkgroup_{$value.name}"></td>
                    <td><a href="?display=acl&cat=admin&action=listGroup&group={$value.name}" class="ajax_link">{$value.name}</a></td>
                </tr>
            {/loop}
		   {/if}
                <form action="?display=users&cat=admin&action=createGroup" method="POST" id="createGroupForm">
                <tr id="groupsAddTr" style="display: none;">
                    <td><input type="text" name="name" style="width: 95%;"></td><td><input type="text" name="description" style="width: 95%;"></td><td><input type="submit" value="{function="localize('Add new group', 'acl')"}"></td>
                </tr>
                </form>
            </tbody>
    </table>
    </div>
    
    <div class="grid-2" id="groupTable" style="position: relative;">
    	<table class="gridTable">
		    <thead>
		        <tr>
		        	<th style="width: 35px;"></th>
		        	<th style="width: 45px;"></th>
		            <th>{function="localize('User', 'users')"}</th>
		        </tr>
		    </thead>
		    
		    <tfoot>
	            <tr>
		            <td colspan="3"><em>{$uiPagerName="users"}
		            {include="ui.pager"}
		            </em></td>
	            </tr>
        	</tfoot>
		
		    <tbody>
		     {if="count($users) == 0"}
		        <tr id="user_{$value.login}">
		             <td colspan="3">No results</td>
		        </tr>
		     {else}
		      {loop="$users"}
		        <tr id="user_{$value.login}">
		             <td><input type="checkbox" id="checkuser_{$value.login}"></td>
		             <td style="border-right: 0;"><img src="{$value.avatar}" style="max-height: 30px; max-width: 23px;"></td>
		             <td>{if="$view_users == True"}<a href='?display=users&cat=admin&action=account&uid={$value.id}' class='ajax_link'>{$value.name}</a>{else}{$value.name}{/if}</td>
		        </tr>
		      {/loop}
		     {/if}
		    </tbody>
		</table>
    </div>
