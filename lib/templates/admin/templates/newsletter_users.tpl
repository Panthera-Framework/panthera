<script type="text/javascript">
function removeSubscriber(id, elementID)
{
    panthera.jsonPOST({ url: '{$AJAX_URL}?display=newsletter_users&cat=admin&nid={$nid}&action=removeSubscriber', data: 'id='+id, messageBox: 'w2ui', success: function (response) {
            if (response.status == "success")
            {
                $("#sub_"+elementID).remove();
            }
        }
    });
}

function addSubscriber()
{
    panthera.jsonPOST({ url: '{$AJAX_URL}?display=newsletter_users&cat=admin&nid={$nid}&action=addSubscriber', data: 'email='+$("#add_user_email").val()+'&type='+$("#add_user_type").val()+'&notes='+escape($("#add_user_notes").val()), messageBox: 'w2ui', success: function (response) {
            if (response.status == 'success')
            {
                $('#newsletterUsers').prepend('<tr id="sub_'+response.id+'"><td>'+response.type+'</td><td>'+response.address+'</td><td>'+response.added+'</td><td colspan="2">'+response.notes+'</td></tr>');
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
                <th>{function="localize('Options', 'newsletter')"}</th>
            </tr>
        </thead>
        <tbody id="newsletterUsers">
            {loop="$newsletter_users"}
            <tr id="sub_{$value.id}">
                <td>{$value.type}</td>
                <td>{$value.address}</td>
                <td>{$value.added}</td>
                <td>{$value.notes}</td>
                <td>
                    <a href="#" onclick="removeSubscriber('{$value.id}', '{$value.id}');">
                    <img src="{$PANTHERA_URL}/images/admin/ui/delete.png" style="max-height: 20px;" alt="{function="localize('Remove')"}">
                    </a>
                </td>
            </tr>
            {/loop}
            <tr>
                <td>
                    <select name="add_user_type">
                        {loop="$newsletter_types"}
                        <option value="{$value}">{$value}</option>
                        {/loop}
                    </select>
                </td>
                <td colspan="2"><input type="text" id="add_user_email" placeholder="{function="localize('Address', 'newsletter')"}" style="width: 95%;"></td>
                <td><input type="text" id="add_user_notes" placeholder="{function="localize('Notes', 'newsletter')"}" style="width: 95%;"></td>
                <td>
                    <a onclick="addSubscriber();" style="cursor: pointer;">
                    <img src="{$PANTHERA_URL}/images/admin/list-add.png" style="height: 20px;">
                    </a>
                </td>
            </tr>
        </tbody>
    </table>
    
    <div style="color: white; font-size: 12px; text-align: left; margin-top: 10px; margin-left: 10px;">
        {$uiPagerName="adminNewsletter"}{include="ui.pager"}
    </div>
</div>
