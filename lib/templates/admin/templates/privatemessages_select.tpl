<script type="text/javascript">
function uiTop_callback(address, query)
{
    panthera.popup.navigate(address+'&'+query);
}

function updateClickDatabase(objectName)
{
    if (!localStorage.getItem('privateMessagesSelection'))
    {
        panthera.logging.output('Creating empty array for mailing selection', 'privateMessagesSelection');
        tmp = {};
    } else {
        tmp = JSON.parse(localStorage.getItem('privateMessagesSelection'));
        panthera.logging.output('Loaded array from localStorage', 'privateMessagesSelection');
    }
    
    if (tmp[objectName] !== undefined)
    {
        panthera.logging.output('Removing an element "'+objectName+'" from storage', 'privateMessagesSelection');
        delete tmp[objectName];
    } else {
        panthera.logging.output('Adding an element "'+objectName+'" to storage', 'privateMessagesSelection');
        tmp[objectName] = true;
    }
    
    console.log(tmp);
    localStorage.setItem('privateMessagesSelection', JSON.stringify(tmp));
}

function popupReady () {
    if (localStorage.getItem('privateMessagesSelection'))
    {
        objects = JSON.parse(localStorage.getItem('privateMessagesSelection'));
        
        for (var obj in objects)
        {
            if(obj.substr(0, 5) == 'group')
            {
                groupName = obj.substr(6, obj.length);
                $('#checkgroup_'+groupName).attr('checked', true);
            } else if (obj.substr(0, 4) == 'user') {
                userName = obj.substr(5, obj.length);
                $('#checkuser_'+userName).attr('checked', true);
            }
        }    
    } else {
        localStorage.setItem('privateMessagesSelection', JSON.stringify({}));
    }
}

function popupExecuteCallback()
{
    if (typeof callback_{$callback} == "function")
    {
        callback_{$callback}(JSON.parse(localStorage.getItem('privateMessagesSelection')));
        panthera.popup.close();
    }
}
</script>    
    
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
                    <td><input type="checkbox" id="checkgroup_{$value.name}" onclick="updateClickDatabase('group:{$value.name}')"{if="$value.active"} checked{/if}></td>
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
                    <td colspan="3">
                    {$uiPagerName="users"}
                    {include="ui.pager"}
                    </td>
                </tr>
            </tfoot>
        
            <tbody>
             {if="count($users) == 0"}
                <tr id="user_{$value.login}">
                     <td colspan="3">{function="localize('No results', 'pmessages')"}</td>
                </tr>
             {else}
              {loop="$users"}
                <tr id="user_{$value.login}">
                     <td><input type="checkbox" id="checkuser_{$value.login}" onclick="updateClickDatabase('user:{$value.login}')"{if="$value.active"} checked{/if}></td>
                     <td style="border-right: 0;"><img src="{$value.avatar}" style="max-height: 30px; max-width: 23px;"></td>
                     <td>{if="$view_users == True"}<a href='?display=users&cat=admin&action=account&uid={$value.id}' class='ajax_link'>{$value.name}</a>{else}{$value.name}{/if}</td>
                </tr>
              {/loop}
             {/if}
            </tbody>
        </table>
    </div>
    
    <input type="button" value="{function="localize('Select', 'pmessages')"}" onclick="popupExecuteCallback()" style="float: right; margin-right: 25px; margin-top: 10px;">
    
<script>
popupReady();
</script>
