<script type="text/javascript">
function uiTop_callback(address, query)
{
    panthera.popup.navigate(address+'&'+query);
}

function updateClickDatabase(objectName)
{
    if (!localStorage.getItem('mailingSelection'))
    {
        panthera.logging.output('Creating empty array for mailing selection', 'mailingSelection');
        tmp = {};
    } else {
        tmp = JSON.parse(localStorage.getItem('mailingSelection'));
        panthera.logging.output('Loaded array from localStorage', 'mailingSelection');
    }
    
    if (tmp[objectName] !== undefined)
    {
        panthera.logging.output('Removing an element "'+objectName+'" from storage', 'mailingSelection');
        delete tmp[objectName];
    } else {
        panthera.logging.output('Adding an element "'+objectName+'" to storage', 'mailingSelection');
        tmp[objectName] = true;
    }
    
    console.log(tmp);
    localStorage.setItem('mailingSelection', JSON.stringify(tmp));
}

function popupReady () {
    if (localStorage.getItem('mailingSelection'))
    {
        objects = JSON.parse(localStorage.getItem('mailingSelection'));
        
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
        localStorage.setItem('mailingSelection', JSON.stringify({}));
    }
}

function popupExecuteCallback()
{
    if (typeof callback_{$callback} == "function")
    {
        callback_{$callback}(JSON.parse(localStorage.getItem('mailingSelection')));
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
                     <td colspan="3">{function="localize('No results', 'mailing')"}</td>
                </tr>
             {else}
              {loop="$users"}
                <tr id="user_{$value.login}">
                     <td><input type="radio" id="checkuser_{$value.login}" onclick="updateClickDatabase('user:{$value.login}')"{if="$value.active"} checked{/if}></td>
                     <td style="border-right: 0;"><img src="{$value.avatar}" style="max-height: 30px; max-width: 23px;"></td>
                     <td>{if="$view_users == True"}<a href='?display=users&cat=admin&action=account&uid={$value.id}' class='ajax_link'>{$value.name}</a>{else}{$value.name}{/if}</td>
                </tr>
              {/loop}
             {/if}
            </tbody>
        </table>
    </div>
    
    <input type="button" value="{function="localize('Select', 'mailing')"}" onclick="popupExecuteCallback()" style="float: right; margin-right: 25px; margin-top: 10px;">
    
<script>
popupReady();
</script>
