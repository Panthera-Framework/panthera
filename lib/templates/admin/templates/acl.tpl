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
                  $('#user_list_tbody').html('<tr id="permission_'+response.name+'_'+response.uid+'"><td><i>'+response.full_name+'</i></td><td>"<b><i>'+login+'</i></b>" ({"group"|localize}: '+response.group+')</td><td><input type="button" value="{"Remove"|localize}" onclick="removePermission( \''+acl+'\', \''+response.uid+'\', \''+response.name+'_'+response.uid+'\' );"></td>'+$('#user_list_tbody').html());
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
                  $('#user_list_tbody').html('<tr id="group_permission_'+response.name+'_'+response.aclName+'"><td><i>'+response.name+'</i></td><td>"<b><i>'+response.description+'</i></b>"</td><td><input type="button" value="{"Remove group"|localize}" onclick="removePermission(\''+response.aclName+'\', \''+response.name+'\', \''+response.name+'_'+response.aclName+'\', \'group\');"></td></tr>'+$('#user_list_tbody').html());
              } else {
                  $('#acl_error').html(response.message);
                  $('#acl_error').slideDown();
              }
            }
        });
}

</script>

<h2 class="popupHeading">{"Manage permissions"|localize} - {$action_title|localize}</h2>

<br>

<div class="msgSuccess" id="aclBox_success"></div>
<div class="msgError" id="aclBox_failed"></div>

{if $action == "manage_variable"}
<table class="gridTable">
    <thead>
        <tr>
            <th scope="col" colspan="5" style="width: 250px;"><b>{"Editing permissions"|localize}:</b> &nbsp;<i>{$acl_title}</i></th>
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
    {foreach from=$user_list key=k item=i}
        <tr id="permission_{$k}">
            <td>
                <i>{$i.full_name}</i>
            </td>
            <td>
                "<b><i>{$i.login}</i></b>" ({"group"|localize}: {$i.group})
            </td>
            <td>
                <input type="button" value="{"Remove"|localize}" onclick="removePermission('{$acl_name}', '{$i.userid}', '{$k}', 'user');">
            </td>
        </tr>
    {/foreach}

    <!--{foreach from=$group_list key=k item=i}
        <tr id="group_permission_{$k}">
            <td>
                <i>{$i.name}</i>
            </td>
            <td>
                "<b><i>{$i.description}</i></b>"
            </td>
            <td>
                <input type="button" value="{"Remove group"|localize}" onclick="removePermission('{$acl_name}', '{$i.name}', '{$k}', 'group');">
            </td>
        </tr>
    {/foreach}-->
    <tr>
        <td><b>{"Login"|localize}:</b></td><td><input type="text" id="new_user_login" style="width: 80%;"></td><td><input type="button" value="{"Add user"|localize}" onclick="addUser('{$acl_name}');"></td>
    </tr>

    <!--<tr>
        <td><b>{"Group name"|localize}:</b></td><td><input type="text" id="new_group_name" style="width: 80%;"></td><td><input type="button" value="{"Add group"|localize}" onclick="addGroup('{$acl_name}');"></td>
    </tr>-->

    </tbody>
</table>
{/if}
