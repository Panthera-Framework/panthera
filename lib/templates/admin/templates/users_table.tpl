<script type="text/javascript">
$('.ajax_link').click(function (event) { event.preventDefault(); navigateTo(jQuery(this).attr('href')); return false;});
</script>

<table class="gridTable">
    <thead>
        <tr>
            <th>{function="localize('Login', 'settings')"}</th>
            <th>{function="localize('Full name', 'settings')"}</th>
            <th>{function="localize('Primary group', 'settings')"}</th>
            <th>{function="localize('Joined', 'settings')"}</th>
            <th>{function="localize('Default language', 'settings')"}</th>
        </tr>
    </thead>
        <tfoot>
            <tr>
            <td colspan="6"><em>{function="localize('Users')"} {$users_from}-{$users_to},
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
            <tr>
                <td>{if="$view_users == True"}<a href='?display=settings&cat=admin&action=my_account&uid={$value.id}' class='ajax_link'>{$value.login}</a>{else}{$value.login}{/if}</td>
                <td>{$value.full_name}</td>
                <td><a href="?display=acl&cat=admin&action=listGroup&group={$value.primary_group}" class="ajax_link">{$value.primary_group}</a></td>
                <td>{$value.joined}</td>
                <td>{$value.language|ucfirst}</td>
            </tr>
        {/loop}
        </tbody>
</table>
