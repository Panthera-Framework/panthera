<script type="text/javascript">
var metaSpinner = new panthera.ajaxLoader($('#groupMetaDiv'));
var userSpinner = new panthera.ajaxLoader($('#groupUsersDiv'));

/**
  * Save group attributes
  *
  * @param string key
  * @param string groupName
  * @param string action
  * @return void 
  * @author Damian Kęska
  */

function saveGroupAttribute(key, groupName, action)
{
    data = 'group='+groupName+'&do='+action;
    
    if (action == 'save')
    {
        data += '&value='+$('#'+key+'_value').val();
    } else if (action == 'create') {
        data += '&value='+$('#newMetaValue').val();
        
        // get value from select tag if avaliable
        if ($('#newMetaSelect').val() != '')
            key = $('#newMetaSelect').val();
        else
            key = $('#newMetaText').val();
    }
    
    data += '&key='+key;
    
    panthera.jsonPOST({ url: '?display=acl&cat=admin&action=groupMetaSave', data: data, spinner: metaSpinner, success: function (response) {
            if (response.status == "success")
            {
                if (action == 'remove')
                {
                    $('#meta_'+key).remove();
                }
                
                // rebuild list of meta tags
                if (response.metaList != undefined)
                {
                    rebuildMetaList(response.metaList);
                }
            } else {
                if (response.message != undefined)
                {
                    w2alert(response.message, '{function="localize('Warning', 'acl')"}');
                }
            
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
    if (metas.length == 0)
        return false;

    $('.metas').remove();
    
    panthera.logging.output('Rebuilidng meta tags list', 'page');

    for (meta in metas)
    {
        if (metas[meta].value == true)
            options = '<option value="1" selected>True</option><option value="0">False</option>';
        else
            options = '<option value="1">True</option><option value="0" selected>False</option>';
    
        $('#metasList').prepend('<tr class="metas" id="meta_'+meta+'"><td>'+metas[meta].name+'</td><td><select id="'+meta+'_value" style="width: 95%;">'+options+'</select></td><td><input type="button" value="&nbsp;{function="localize('Save', 'acl')"}&nbsp;" onclick="saveGroupAttribute(\''+meta+'\', \'{$groupName}\', \'save\');">&nbsp;<input type="button" value="&nbsp;{function="localize('Remove', 'acl')"}&nbsp;" onclick="saveGroupAttribute(\''+meta+'\', \'{$groupName}\', \'remove\');"></td></tr>');
    }
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

    panthera.jsonPOST({ url: '?display=acl&cat=admin&action=groupUsers', data: 'user='+user+'&subaction='+action+'&group='+group, spinner: userSpinner, success: function (response) {
            if (response.status == "success")
            {
                // rebuild list of meta tags
                if (response.userList != undefined)
                {
                    rebuildUserList(response.userList);
                }
            } else {
                if (response.message != undefined)
                {
                    w2alert(response.message, '{function="localize('Warning', 'acl')"}');
                }
            
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
    
    for (user in users)
    {
        $('#groupUsersBody').prepend('<tr id="user_'+users[user].login+'" class="groupUsers"><td>'+users[user].login+'</td><td style="width: 10%; padding-right: 10px;"><input type="button" value="{function="localize('Remove', 'acl')"}" onclick="saveGroupUser(\'remove\', \'{$groupName}\', \''+users[user].login+'\');"></td></tr>');
    }
}

</script>

<div class="titlebar"><span class="titleBarIcons"><img src="{$PANTHERA_URL}/images/admin/menu/users.png" style="width: 25px"></a></span>{function="localize('Groups management', 'acl')"} - <b><i>"{$groupName}" {if="strlen($groupDescription) > 0"}({$groupDescription}){/if}</i></b>{include="_navigation_panel.tpl"}</div>

<br>

<div class="msgSuccess" id="aclBox_success"></div>
<div class="msgError" id="aclBox_failed"></div>

    <div class="grid-2" style="position: relative; float: left;" id="groupUsersDiv">
      <div class="title-grid">{function="localize('Users in this group', 'acl')"}<span></span></div>
      <div class="content-table-grid">
          <table class="insideGridTable">
            <tbody id="groupUsersBody">
                {loop="$groupUsers"}
                <tr id="user_{$value.login}" class="groupUsers">
                    <td>{$value.login}</td>
                    <td style="width: 10%; padding-right: 10px;"><input type="button" value="{function="localize('Remove', 'acl')"}" onclick="saveGroupUser('remove', '{$groupName}', '{$value.login}');"></td>
                </tr>
                {/loop}
                
                <tr>
                    <td><input type="text" id="newGroupUserLogin" style="width: 95%;"></td>
                    <td style="padding-right: 10px;"><input type="submit" value="&nbsp;{function="localize('Add new user', 'acl')"}&nbsp;" onclick="saveGroupUser('add', '{$groupName}');"></td>
                </tr>
            </tbody>
         </table>
     </div>
   </div>

    <div class="grid-2" style="position: relative; float: left;" id="groupMetaDiv">
      <div class="title-grid">{function="localize('Meta attributes of this group', 'acl')"}<span></span></div>
      <div class="content-table-grid">
          <table class="insideGridTable">
            <tbody id="metasList">
                {loop="$metas"}
                <tr class="metas" id="meta_{$key}">
                    <td>{$value.name}</td>
                    <td><select id="{$key}_value" style="width: 95%;"><option value="1"{if="$value.value == True"} selected{/if}>True</option><option value="0"{if="$value.value == False"} selected{/if}>False</option></select></td>
                    <td><input type="button" value="&nbsp;{function="localize('Save', 'acl')"}&nbsp;" onclick="saveGroupAttribute('{$key}', '{$groupName}', 'save');">&nbsp;<input type="button" value="&nbsp;{function="localize('Remove', 'acl')"}&nbsp;" onclick="saveGroupAttribute('{$key}', '{$groupName}', 'remove');"></td>
                </tr>
                {/loop}
                
                <tr>
                    <td><select id="newMetaSelect">
                            <option value=""></option>
                            {loop="$metasAvaliable"}
                            <option value="{$key}">{$value.desc|strCut:25}</option>
                            {/loop}
                        </select>
                        <br><br>
                        <input type="text" id="newMetaText">
                    </td>
                    <td><select id="newMetaValue" style="width: 95%;">
                            <option value="1">True</option>
                            <option value="0">False</option>
                        </select>
                    </td>
                    <td><input type="submit" value="&nbsp;{function="localize('Add new attribute', 'acl')"}&nbsp;" onclick="saveGroupAttribute('', '{$groupName}', 'create');"></td>
                </tr>
            </tbody>
         </table>
     </div>
   </div>
