<script type="text/javascript">
$('.ajax_link').click(function (event) { event.preventDefault(); navigateTo(jQuery(this).attr('href')); return false;});

/**
  * Jump to other page in a table
  *
  * @author Damian Kęska
  */

function jumpToAjaxPage(id)
{
    panthera.htmlGET({ url: '?display=users&cat=admin&subaction=show_table&usersPage='+id, success: '#usersDiv' });
}

var groupSpinner = new panthera.ajaxLoader($('#groupTable'));
var userSpinner = new panthera.ajaxLoader($('#usersDiv'));

// when page becomes ready
$(document).ready(function () {

    /**
      * Add a new group
      *
      * @author Damian Kęska
      */

    $('#createGroupForm').submit(function () {
        panthera.jsonPOST( { data: '#createGroupForm', spinner: groupSpinner, success: function (response) {

                if (response.status == "success")
                {
                    //$('.groupTableItem').remove();
                    $('#groupTableBody').prepend('<tr id="group_'+response.name+'" class="groupTableItem"><td><a href="?display=acl&cat=admin&action=listGroup&group='+response.name+'" class="ajax_link">'+response.name+'</a></td><td>'+response.description+'</td><td><input type="button" value="{function="localize('Remove', 'acl')"}" onclick="removeGroup(\''+response.name+'\');"></td>');
                } else {
                    if (response.message != undefined)
                    {
                        w2alert(response.message, '{function="localize('Warning', 'acl')"}');
                    }

                }
            }
        });
        return false;
    });
});

/**
  * Remove group
  *
  * @author Damian Kęska
  */

function removeGroup(name)
{
    w2confirm('{function="localize('Are you sure you want delete this group?', 'acl')"}', function (responseText) {
        if (responseText == 'Yes')
        {
            panthera.jsonPOST( { url: '?display=users&cat=admin&action=removeGroup', data: 'group='+name, spinner: groupSpinner, success: function (response) {

                    if (response.status == "success")
                        $('#group_'+response.name).remove();
                }
            });
        }

    });
}

/**
  * Remove user
  *
  * @author Mateusz Warzyński
  */

function removeUser(id)
{
    w2confirm('{function="localize('Are you sure you want delete this user?', 'users')"}', function (responseText) {
        if (responseText == 'Yes')
        {
            panthera.jsonPOST( { url: '?display=users&cat=admin&action=removeUser', data: 'id='+id, spinner: userSpinner, success: function (response) {

                    if (response.status == "success")
                        $('#user_'+id).remove();
                }
            });
        }

    });
}

</script>

<div class="titlebar"><span class="titleBarIcons"><img src="{$PANTHERA_URL}/images/admin/menu/users.png" style="width: 25px"></a></span>{function="localize('Users')"} - {function="localize('All registered users on this website', 'users')"}.{include="_navigation_panel"}</div>

        {$uiSearchbarName="uiTop"}
        {include="ui.searchbar"}

        <div class="grid-1">
            <div id="usersDiv" style="position: relative;">
            {include="users_table"}
             </div>
        </div>

        <div class="grid-2" id="groupTable" style="position: relative;">
        <table class="gridTable">
        <thead>
            <tr>
                <th>{function="localize('Group name', 'acl')"}</th>
                <th>{function="localize('Description', 'acl')"}</th>
                <th><span style="float: right;"><a onclick="$('#groupsAddTr').show('slow');" style="cursor: pointer;"><img src="{$PANTHERA_URL}/images/admin/list-add.png" style="height: 15px;"></a></span></th>
            </tr>
        </thead>
            <tbody id="groupTableBody">
            {loop="$groups"}
                <tr id="group_{$value.name}" class="groupTableItem">
                    <td><a href="?display=acl&cat=admin&action=listGroup&group={$value.name}" class="ajax_link">{$value.name}</a></td>
                    <td>{$value.description}</td>
                    <td><input type="button" value="{function="localize('Remove', 'acl')"}" onclick="removeGroup('{$value.name}');"></td>
                </tr>
            {/loop}

                <form action="?display=users&cat=admin&action=createGroup" method="POST" id="createGroupForm">
                <tr id="groupsAddTr" style="display: none;">
                    <td><input type="text" name="name" style="width: 95%;"></td><td><input type="text" name="description" style="width: 95%;"></td><td><input type="submit" value="{function="localize('Add new group', 'acl')"}"></td>
                </tr>
                </form>
            </tbody>
    </table>
    </div>
