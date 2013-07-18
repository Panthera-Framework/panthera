<script type="text/javascript">
function removePermission(id, login, k, type)
{
    panthera.jsonPOST({ url: '{$AJAX_URL}?display=acl&popup=true&action=delete', messageBox: 'aclBox', data: 'id='+id+'&login='+login+'&type='+type, success: function (response) {
              if (response.status == "success")
              {
                  if (type == "user")
                      $('#permission_'+k).remove();
                  else
                      $('#group_permission_'+k).remove();

              }
        }
    });
}

function addUser(acl)
{
    login = $('#new_user_login').val();

    if (login.length < 2)
        return false;

    panthera.jsonPOST({ url: '{$AJAX_URL}?display=acl&popup=true&action=add', messageBox: 'aclBox', data: 'login='+login+'&acl='+acl+'&type=user', success: function (response) {
              if (response.status == "success")
              {
                    // \"'+acl+'\", \"'+response.uid+'\", \"'+response.name+'_'+response.uid+'\"
                  $('#user_list_tbody').html('<tr id="permission_'+response.name+'_'+response.uid+'"><td><i>'+response.full_name+'</i></td><td>"<b><i>'+login+'</i></b>" ({function="localize('group')"}: '+response.group+')</td><td><input type="button" value="{function="localize('Remove')"}" onclick="removePermission( \''+acl+'\', \''+response.uid+'\', \''+response.name+'_'+response.uid+'\' );"></td>'+$('#user_list_tbody').html());
              }
          }
    });
}

function addGroup(acl)
{
    login = $('#new_group_name').val();

    if (login.length < 2)
        return false;

    panthera.jsonPOST({ url: '{$AJAX_URL}?display=acl&popup=true&action=add', messageBox: 'aclBox', data: 'login='+login+'&acl='+acl+'&type=group', success: function (response) {
              if (response.status == "success")
              {
                  $('#acl_error').hide();
                    // \"'+acl+'\", \"'+response.uid+'\", \"'+response.name+'_'+response.uid+'\"
                  $('#user_list_tbody').html('<tr id="group_permission_'+response.name+'_'+response.aclName+'"><td><i>'+response.name+'</i></td><td>"<b><i>'+response.description+'</i></b>"</td><td><input type="button" value="{function="localize('Remove group')"}" onclick="removePermission(\''+response.aclName+'\', \''+response.name+'\', \''+response.name+'_'+response.aclName+'\', \'group\');"></td></tr>'+$('#user_list_tbody').html());
              } else {
                  $('#acl_error').html(response.message);
                  $('#acl_error').slideDown();
              }
            }
        });
}

</script>

<h2 class="popupHeading">{function="localize('Manage permissions')"} - {$action_title|localize}</h2>

<br>

<div class="msgSuccess" id="aclBox_success"></div>
<div class="msgError" id="aclBox_failed"></div>

{if="$action == 'manage_variable'"}
<table class="gridTable">
    <thead>
        <tr>
            <th scope="col" colspan="5" style="width: 250px;"><b>{function="localize('Editing permissions')"}:</b> &nbsp;<i>{$acl_title}</i></th>
        </tr>
    </thead>

    <tfoot>
        <tr>
            <td colspan="5" class="rounded-foot-left">
                <em>Panthera - Access Control Lists</em>
            </td>
        </tr>
    </tfoot>

    <tbody id="user_list_tbody">
    {loop="$user_list"}
        <tr id="permission_{$key}">
            <td>
                <i>{$value.full_name}</i>
            </td>
            <td>
                "<b><i>{$value.login}</i></b>" ({function="localize('group')"}: {$value.group})
            </td>
            <td>
                <input type="button" value="{function="localize('Remove')"}" onclick="removePermission('{$acl_name}', '{$value.userid}', '{$key}', 'user');">
            </td>
        </tr>
    {/loop}

    <!--{loop="$group_list"}
        <tr id="group_permission_{$key}">
            <td>
                <i>{$value.name}</i>
            </td>
            <td>
                "<b><i>{$value.description}</i></b>"
            </td>
            <td>
                <input type="button" value="{function="localize('Remove group')"}" onclick="removePermission('{$acl_name}', '{$value.name}', '{$key}', 'group');">
            </td>
        </tr>
    {/loop}-->
    <tr>
        <td><b>{function="localize('Login')"}:</b></td><td><input type="text" id="new_user_login" style="width: 80%;"></td><td><input type="button" value="{function="localize('Add user')"}" onclick="addUser('{$acl_name}');"></td>
    </tr>

    <!--<tr>
        <td><b>{function="localize('Group name')"}:</b></td><td><input type="text" id="new_group_name" style="width: 80%;"></td><td><input type="button" value="{function="localize('Add group')"}" onclick="addGroup('{$acl_name}');"></td>
    </tr>-->

    </tbody>
</table>
{elseif="$action == 'disabled'"}
<h2>{function="localize('Permissions management is currently disabled in website settings')"}</h2>
{/if}
