<script type="text/javascript">
/**
  * Save group attributes
  *
  * @param string key
  * @param string groupName
  * @param string action
  * @return void 
  * @author Damian Kęska
  */

function saveGroupAttribute(key, groupName, action, hash)
{
    data = 'group='+groupName+'&do='+action;
    
    if (action == 'save')
    {
        data += '&value='+$('#'+hash+'_value').val();
    } else if (action == 'create') {
        data += '&value='+$('#newMetaValue').val();
        
        // get value from select tag if avaliable
        if ($('#newMetaSelect').val() != '')
            key = $('#newMetaSelect').val();
        else
            key = $('#newMetaText').val();
    }
    
    data += '&key='+key;
    
    panthera.jsonPOST({ url: '?display=acl&cat=admin&action=groupMetaSave', data: data, success: function (response) {
            if (response.status == "success")
            {
                rebuildMetaList(response.metaList);
                panthera.popup.close();
            } 
        }
    });
}

/**
  * Rebuild meta tags list
  *
  * @param string metas
  * @return void 
  * @author Damian Kęska
  */

function rebuildMetaList(metas)
{
    /*if (metas.length == 0)
        return false;

    $('.metas').remove();
    
    panthera.logging.output('Rebuilidng meta tags list', 'page');
    i=0;

    for (meta in metas)
    {
        i=i+1;
    
        if (metas[meta].value == true)
            options = '<option value="1" selected>True</option><option value="0">False</option>';
        else
            options = '<option value="1">True</option><option value="0" selected>False</option>';
    
        $('#metasList').prepend('<tr class="metas" id="meta_'+meta+'"><td>'+metas[meta].name+'</td><td><select id="'+meta+'_value" style="width: 95%;">'+options+'</select></td><td><input type="button" value="&nbsp;{function="localize('Save', 'acl')"}&nbsp;" onclick="saveGroupAttribute(\''+meta+'\', \'{$groupName}\', \'save\');">&nbsp;<input type="button" value="&nbsp;{function="localize('Remove', 'acl')"}&nbsp;" onclick="saveGroupAttribute(\''+meta+'\', \'{$groupName}\', \'remove\');"></td></tr>');
    }
    
    if (i > 0)
    {
        $('#noMetaTags').hide();
    } else {
        $('#noMetaTags').show();
    }*/
    
    navigateTo(window.location.href);
}

/**
  * Add or remove user in a group
  *
  * @param string action
  * @param string user
  * @return void
  * @author Damian Kęska
  */

function saveGroupUser(action, group, user)
{
    if (action == 'add')
        user = $('#newGroupUserLogin').val();
        
    if (user.length < 2)
        return false;

    panthera.jsonPOST({ url: '?display=acl&cat=admin&action=groupUsers', data: 'user='+user+'&subaction='+action+'&group='+group, success: function (response) {
            if (response.status == "success")
            {
                rebuildUserList();
                panthera.popup.close();
            }
        }
    });
}

/**
  * Rebuild users list
  *
  * @param json users
  * @return void
  * @author Damian Kęska
  */

function rebuildUserList(users)
{
    $('.groupUsers').remove();
    
    i=0;
    
    for (user in users)
    {
        i=i+1;
        $('#groupUsersBody').prepend('<tr id="user_'+users[user].login+'" class="groupUsers"><td>'+users[user].login+'</td><td style="width: 10%; padding-right: 10px;"><input type="button" value="{function="localize('Remove', 'acl')"}" onclick="saveGroupUser(\'remove\', \'{$groupName}\', \''+users[user].login+'\');"></td></tr>');
    }
    
    if (i > 0)
    {
        $('#noGroupUsers').hide();
    } else {
        $('#noGroupUsers').show();
    }
}

</script>

{include="ui.titlebar"}

<style>
.tipBlockDark {
    border: solid 1px #6e8093;
    background-color: #404c5a;
    color: white;
    margin-left: 20px;
    margin-top: 20px;
    position: absolute;
}
</style>

<div id="topContent">{*}
    <div class="tipBlock tipBlockDark" style="width: 65%;">
        <div class="tipBlockInside">
            {function="localize('Did you know?', 'acl')"}
        </div>
    </div>
    
    {/*}<div class="searchBarButtonArea">
        <input type="button" value="{function="localize('Add user to group', 'acl')"}" onclick="panthera.popup.toggle('element:#addUserPopup')">
        <input type="button" value="{function="localize('Add new attribute', 'acl')"}" onclick="panthera.popup.toggle('element:#addMetaTag')">
    </div>
</div>

<!-- Adding new user popup -->
<div id="addUserPopup" style="display: none;">
    <table class="formTable" style="width: 300px; margin: 0 auto;">
        <thead>
            <th colspan="2"><p style="color: #e5ebef; padding: 0px; margin: 0px; margin-left: 30px;">{function="localize('Add user to group', 'acl')"}</p></th>
        </thead>
        <tbody>
            <tr>
                <th>{function="localize('Login', 'acl')"}:</th>
                <td><div class="ui-widget"><input type="text" id="newGroupUserLogin" style="width: 95%;"></div></td>
            </tr>
        </tbody>
        
        <tfoot>
            <tr>
                <td colspan="2" style="padding-top: 35px;">
                    <input type="button" value="{function="localize('Cancel')"}" onclick="panthera.popup.close()" style="float: left; margin-left: 30px;">
                    <input type="button" value="&nbsp;{function="localize('Add new user', 'acl')"}&nbsp;" onclick="saveGroupUser('add', '{$groupName}');" style="float: right;">
                </td>
            </tr>
        </tfoot>
    </table>
    
    <script type="text/javascript">
        $(document).ready(function () {
        $( "#newGroupUserLogin").click(function() {
            $( "#newGroupUserLogin" ).autocomplete({
              source: function (request, uiResponse) {
                //query = request.term;
                
                panthera.jsonPOST({url: '?display=users&cat=admin&action=getUsersAPI', data: 'query='+$('#newGroupUserLogin').val(), success: function (response) 
                {
                    if (response.status == 'success')
                        uiResponse(response.result);
                }});
              }
            });
        
        });
    });
    
    </script>
</div>

<!-- Adding new meta tag -->
<div id="addMetaTag" style="display: none;">
    <table class="formTable" style="width: 300px; margin: 0 auto;">
        <thead>
            <th colspan="2"><p style="color: #e5ebef; padding: 0px; margin: 0px; margin-left: 30px;">{function="localize('Add user to group', 'acl')"}</p></th>
        </thead>
        <tbody>
            <tr>
                <th>{function="localize('Select tag', 'acl')"}:</th>
                <td><select id="newMetaSelect">
                        <option value=""></option>
                        {loop="$metasAvaliable"}
                        <option value="{$key}">{$value|strCut:60}</option>
                        {/loop}
                    </select>
                </td>
            </tr>
            
            <tr>
                <th>{function="localize('Or enter tag name manually', 'acl')"}:</th>
                <td><input type="text" id="newMetaText"></td>
            </tr>
            
            <tr>
                <th>{function="localize('Value')"}</th>
                <td><select id="newMetaValue" style="width: 95%;">
                        <option value="1">True</option>
                        <option value="0">False</option>
                    </select>
                </td>
            </tr>
        </tbody>
        
        <tfoot>
            <tr>
                <td colspan="2" style="padding-top: 35px;">
                    <input type="button" value="{function="localize('Cancel')"}" onclick="panthera.popup.close()" style="float: left; margin-left: 30px;">
                    <input type="button" value="&nbsp;{function="localize('Add new attribute', 'acl')"}&nbsp;" onclick="saveGroupAttribute('', '{$groupName}', 'create', '');" style="float: right;">
                </td>
            </tr>
        </tfoot>
    </table>
</div>


<!-- Content -->
<div class="ajax-content" style="text-align: center;">
    <table style="display: inline-table; width: 350px; margin-bottom: 30px;">
        <thead>
            <tr>
                <th colspan="2">{function="localize('Users in this group', 'acl')"}</th>
            </tr>
        </thead>
        
        <tbody id="groupUsersBody" class="hovered">
            {loop="$groupUsers"}
            <tr id="user_{$value.login}" class="groupUsers">
                <td><a href="?display=users&cat=admin&action=account&uid={$value.id}" class="ajax_link">{$value.login}</a></td>
                <td style="width: 10%; padding-right: 10px;"><input type="button" value="{function="localize('Remove', 'acl')"}" onclick="saveGroupUser('remove', '{$groupName}', '{$value.login}');"></td>
            </tr>
            {/loop}
            
            {if="!$groupUsers"}
            <tr id="noGroupUsers"><td colspan="2">{function="localize('No any users found in this group', 'acl')"}</td></tr>
            {/if}
        </tbody>
        
        <tfoot style="background-color: transparent;">
           <tr>
             <td colspan="7" class="pager">{$uiPagerName="adminACLGroups"}
               {include="ui.pager"}
             </td>
           </tr>
        </tfoot>
    </table>
    
    <table style="display: inline-block;">
        <thead>
            <tr>
                <th colspan="3">{function="localize('Meta attributes of this group', 'acl')"}</th>
            </tr>
        </thead>
    
        <tbody id="metasList" class="hovered">
            {loop="$metas"}
            <tr class="metas" id="meta_{$key|md5}">
                <td>{$value.name}</td>
                <td><select id="{$key|md5}_value" style="width: 95%;"><option value="1"{if="$value.value == True"} selected{/if}>True</option><option value="0"{if="$value.value == False"} selected{/if}>False</option></select></td>
                <td><input type="button" value="&nbsp;{function="localize('Save', 'acl')"}&nbsp;" onclick="saveGroupAttribute('{$key}', '{$groupName}', 'save', '{$key|md5}');">&nbsp;<input type="button" value="&nbsp;{function="localize('Remove', 'acl')"}&nbsp;" onclick="saveGroupAttribute('{$key}', '{$groupName}', 'remove', '{$key|md5}');"></td>
            </tr>
            {/loop}
            
            {if="!$metas"}
            <tr id="noMetaTags"><td colspan="2">{function="localize('No meta tags found to be assigned to this group', 'acl')"}</td></tr>
            {/if}
        </tbody>
    </table>
</div>
