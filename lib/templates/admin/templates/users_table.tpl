<script type="text/javascript">
$('.ajax_link').click(function (event) { event.preventDefault(); navigateTo(jQuery(this).attr('href')); return false;});
</script>

<table class="gridTable">
    <thead>
        <tr>
            <th></th>
            <th>{function="localize('Name', 'users')"}</th>
            <th>{function="localize('Primary group', 'users')"}</th>
            <th>{function="localize('Joined', 'users')"}</th>
            <th>{function="localize('Default language', 'users')"}</th>
            <th><span style="float: right;"><a onclick="navigateTo('?display=users&cat=admin&action=new_user');" style="cursor: pointer;"><img src="{$PANTHERA_URL}/images/admin/list-add.png" style="height: 15px;"></a></span></th>
        </tr>
    </thead>
        <tfoot>
            <tr>
            <td colspan="7"><em>{function="localize('Users')"} {$users_from}-{$users_to},
            {loop="$pager"}
                {if="$value == true"}
                <a href="#" onclick="jumpToAjaxPage({$key}); return false;"><b>{$key+1}</b></a>
                {else}
                <a href="#" onclick="jumpToAjaxPage({$key}); return false;">{$key+1}</a>
                {/if}
            {/loop}
            </em></td>
            </tr>
        </tfoot>

        <tbody>
        {loop="$users_list"}
            <tr id="user_{$value.login}">
                <td style="width: 32px;"><img src="{$value.avatar}" style="max-height: 30px; max-width: 23px;"></td>
                <td>{if="$view_users == True"}<a href='?display=users&cat=admin&action=account&uid={$value.id}' class='ajax_link'>{$value.name}</a>{else}{$value.name}{/if}</td>
                <td><a href="?display=acl&cat=admin&action=listGroup&group={$value.primary_group}" class="ajax_link">{$value.primary_group}</a></td>
                <td>{$value.joined}</td>
                <td>{$value.language|ucfirst}</td>
                <td><input type="button" value="{function="localize('Edit', 'users')"}" onclick="navigateTo('?display=users&cat=admin&action=editUser&uid={$value.id}')">&nbsp;<input type="button" value="{function="localize('Remove')"}" onclick="removeUser('{$value.login}');"></td>
            </tr>
        {/loop}
        </tbody>
</table>
