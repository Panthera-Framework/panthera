<script type="text/javascript">
/**
 * Add a new subscriber
 *
 * @author Damian Kęska
 */

function addSubscriber()
{
    panthera.jsonPOST({ data: '#addSubscriberForm', messageBox: 'w2ui', success: function (response) {
            if (response.status == 'success')
            {
                $('#newsletterUsers').prepend('<tr id="sub_'+response.id+'"><td>'+response.type+'</td><td>'+response.address+'</td><td>'+response.added+'</td><td colspan="99">'+response.notes+'</td></tr>');
            }
        }
    });
}

/**
 * Remove old one
 *
 * @param string name
 * @return mixed 
 * @author Damian Kęska
 */

function removeSubscriber(id, elementID)
{
    panthera.jsonPOST({ url: '?display=newsletter.users&cat=admin&nid={$nid}&action=removeSubscriber', data: 'id='+id, messageBox: 'w2ui', success: function (response) {
            if (response.status == "success")
            {
                $("#sub_"+elementID).remove();
            }
        }
    });
}
</script>

<div style="display: inline-block;">
    <table style="margin: 0 auto;">
        <thead>
            <tr>
                <th>{function="localize('Type', 'newsletter')"}</th>
                <th>{function="localize('Address', 'newsletter')"}</th>
                <th>{function="localize('Added', 'newsletter')"}</th>
                <th>{function="localize('Notes', 'newsletter')"}</th>
                {loop="$additionalFields"}
                <th>{function="localize($value[0], $value[1])"}</th>
                {/loop}
                <th>{function="localize('Options', 'newsletter')"}</th>
            </tr>
        </thead>
        <tbody id="newsletterUsers" class="bgTable">
            {loop="$newsletter_users"}
            <tr id="sub_{$value.id}">
                <td>{$value.type}</td>
                <td>{$value.address}</td>
                <td>{$value.added}</td>
                <td>{$value.notes}</td>
                {$user=$value}
                {loop="$additionalFields"}
                <td>{$user.metas[$key]}</td>
                {/loop}
                <td>
                    <a href="#" onclick="removeSubscriber('{$value.id}', '{$value.id}');">
                    <img src="{$PANTHERA_URL}/images/admin/ui/delete.png" style="max-height: 20px;" alt="{function="localize('Remove')"}">
                    </a>
                </td>
            </tr>
            {/loop}
            
            <tr>
                <td colspan="5">
                	<form action="?display=newsletter.users&cat=admin&nid={$nid}&action=addSubscriber" method="POST" id="addSubscriberForm">
                    <select name="add_user_type" name="type">
                        {loop="$newsletter_types"}
                        <option value="{$value}">{$value}</option>
                        {/loop}
                    </select>
                <input type="text" id="add_user_email" name="email" placeholder="{function="localize('Address', 'newsletter')"}">
                <input type="text" id="add_user_notes" name="notes" placeholder="{function="localize('Notes', 'newsletter')"}">
                {loop="$additionalFields"}
                <input type="text" id="add_user_extrafield_{$key}" name="extrafield_{$key}" placeholder="{function="localize($value[0], $value[1])"}">
                {/loop}
                    <input type="button" value="{function="localize('Add')"}" onclick="addSubscriber();">
                    </form>
                </td>
            </tr>
        </tbody>
    </table>
    
    <div style="color: white; font-size: 12px; text-align: left; margin-top: 10px; margin-left: 10px;">
        {$uiPagerName="adminNewsletter"}{include="ui.pager"}
    </div>
</div>
