{if="count($lastLogged) > 0"}
    <table class="dashWidget" style="padding-top: 30px;">
        <thead>
            <th colspan="3">
                {function="localize('Recently logged in users', 'dash')"}
                <span id="widgetRemoveButtons" class="widgetRemoveButtons">
                    <a href="#" onclick="removeWidget('lastLogged')">
                        <img src="{$PANTHERA_URL}/images/admin/list-remove.png" style="height: 15px; float: right; margin-right: 5px;">
                    </a>
                </span>
            </th>
        </thead>
                
        <tbody class="hovered">
            {loop="$lastLogged"}
            <tr>
                <td id="dashAvatar">
                    <img src="{$value.avatar}" style="max-height: 90%; max-width:90%;" alt="Avatar">
                </td>
                <td>
                    <a href="?display=users&cat=admin&action=account&uid={$value.uid}" class="ajax_link">{$value.login}</a>
                </td>
                <td> {$value.time} {function="localize('ago', 'dash')"}</td>
            </tr>
            {/loop}
                           
        </tbody>
    </table>
{/if}