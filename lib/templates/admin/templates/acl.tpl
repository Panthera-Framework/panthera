<script type="text/javascript">
function removePermission(id, login, k, type)
{
    panthera.jsonPOST({ url: '{$AJAX_URL}?display=acl&cat=admin&popup=true&action=delete', data: 'id='+id+'&login='+login+'&type='+type, success: function (response) {
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

    panthera.jsonPOST({ url: '{$AJAX_URL}?display=acl&cat=admin&popup=true&action=add', data: 'login='+login+'&acl='+acl+'&type=user', success: function (response) {
              if (response.status == "success")
              {
                    // \"'+acl+'\", \"'+response.uid+'\", \"'+response.name+'_'+response.uid+'\"
                  $('#user_list_tbody').html('<tr id="permission_'+response.name+'_'+response.uid+'"><th><i>'+response.full_name+'</i></th><td style="color: white;">"<b><i>'+login+'</i></b>" ({function="localize('group', 'acl')"}: '+response.group+')</td><td><input type="button" value="{function="localize('Remove', 'acl')"}" onclick="removePermission( \''+acl+'\', \''+login+'\', \''+response.name+'_'+response.uid+'\' );"></td>'+$('#user_list_tbody').html());
              }
          }
    });
}

function addGroup(acl)
{
    login = $('#new_group_name').val();

    if (login.length < 2)
        return false;

    panthera.jsonPOST({ url: '{$AJAX_URL}?display=acl&cat=admin&popup=true&action=add', data: 'login='+login+'&acl='+acl+'&type=group', success: function (response) {
              if (response.status == "success")
              {
                  $('#acl_error').hide();
                    // \"'+acl+'\", \"'+response.uid+'\", \"'+response.name+'_'+response.uid+'\"
                  $('#user_list_tbody').html('<tr id="group_permission_'+response.name+'_'+response.aclName+'"><th><i>'+response.name+'</i></th><td style="color: white;">"<b><i>'+response.description+'</i></b>"</td><td><input type="button" value="{function="localize('Remove group', 'acl')"}" onclick="removePermission(\''+response.aclName+'\', \''+response.name+'\', \''+response.name+'_'+response.aclName+'\', \'group\');"></td></tr>'+$('#user_list_tbody').html());
              }
            }
        });
}

function selectPermission()
{
    panthera.popup.create('?{function="Tools::getQueryString('GET', 'current=$target', '_')"}'.replace('%24target', $('#permissionSelect').val()));
}

$(document).ready(function () {
    $( "#new_user_login").click(function() {
        $( "#new_user_login" ).autocomplete({
          source: function (request, uiResponse) {
            //query = request.term;
            
            panthera.jsonPOST({url: '?display=users&cat=admin&action=getUsersAPI', data: 'query='+$('#new_user_login').val(), success: function (response) 
            {
                if (response.status == 'success')
                    uiResponse(response.result);
            }});
          }
        });
    
    });
    
    $( "#new_group_name").click(function() {
        $( "#new_group_name" ).autocomplete({
          source: function (request, uiResponse) {
            //query = request.term;
            
            panthera.jsonPOST({url: '?display=users&cat=admin&group&action=getUsersAPI', data: 'query='+$('#new_group_name').val(), success: function (response) 
            {
                if (response.status == 'success')
                    uiResponse(response.result);
            }});
          }
        });
    
    });
});

</script>

{if="$action == 'manage_variable'"}
<table class="formTable" style="margin: 0 auto; margin-bottom: 30px; margin-top: 25px;">
    <thead>
        <tr>
            <th colspan="2" style="width: 250px;">
                <p style="color: #e5ebef; padding: 0px; margin: 0px; margin-left: 30px;"><b>{function="localize('Editing permissions', 'acl')"}:</b> &nbsp;<i>{if="count($multiplePermissions) > 0"}
{$i=0}
<select id="permissionSelect" onchange="selectPermission();">
{loop="$multiplePermissions"}
    {$i=$i+1}
    
    {if="!$key or !$value"}
    {continue}
    {/if}
    <option value="{$key}" {if="$key == $acl_name"}selected{/if}>{$value}</option>
{/loop}
</select>
{else}
{$acl_title}
{/if}</i></p>
            </th>
        </tr>
    </thead>

    <tbody id="user_list_tbody">
    {loop="$user_list"}
        <tr id="permission_{$key}">
            <th>
                <i>{$value.full_name}</i>
            </th>
            <td style="color: white;">
                "<b><i>{$value.login}</i></b>" ({function="localize('group', 'acl')"}: {$value.group})
            </td>
            <td>
                <input type="button" value="{function="localize('Remove', 'acl')"}" onclick="removePermission('{$acl_name}', '{$value.login}', '{$key}', 'user');">
            </td>
        </tr>
    {/loop}

    {loop="$group_list"}
        <tr id="group_permission_{$key}">
            <th>
                <i>{$value.name}</i>
            </th>
            <td style="color: white;">
                "<b><i>{$value.description}</i></b>"
            </td>
            <td>
                <input type="button" value="{function="localize('Remove group', 'acl')"}" onclick="removePermission('{$acl_name}', '{$value.name}', '{$key}', 'group');">
            </td>
        </tr>
    {/loop}
    <tr>
        <th><b>{function="localize('Login', 'acl')"}:</b></th>
        <td><div class="ui-widget"><input type="text" id="new_user_login" style="width: 80%;"></div></td>
        <td><input type="button" value="{function="localize('Add user', 'acl')"}" onclick="addUser('{$acl_name}');"></td>
    </tr>

    <tr>
        <th><b>{function="localize('Group name', 'acl')"}:</b></th>
        <td><div class="ui-widget"><input type="text" id="new_group_name" style="width: 80%;"></div></td>
        <td><input type="button" value="{function="localize('Add group', 'acl')"}" onclick="addGroup('{$acl_name}');"></td>
    </tr>

    </tbody>
    
    <tfoot>
    <tr>
      <td colspan="2" style="padding-top: 35px;">
         <input type="button" value="{function="localize('Cancel')"}" onclick="panthera.popup.close()" style="float: right;">
      </td>
    </tr>
    </tfoot>
</table>
{elseif="$action == 'disabled'"}
<h2>{function="localize('Permissions management is currently disabled in website settings', 'acl')"}</h2>
{/if}
