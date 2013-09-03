{if="$action == ''"}
<script>
$('.ajax_link').click(function (event) { event.preventDefault(); navigateTo(jQuery(this).attr('href')); return false;});

function jumpToAjaxPage(id)
{
    $.ajax({
            url: '{$AJAX_URL}?display=newsletter_users&cat=admin&action=show_table&nid={$nid}&pagenum='+id,
            data: '',
            async: false,
            success: function (response) { 
                jQuery('#newsletter_users_window').html(response);
            },
            dataType: 'html'
           });
}

function removeSubscriber(id)
{
    panthera.jsonPOST({ url: '{$AJAX_URL}?display=newsletter_users&cat=admin&nid={$nid}&action=remove_subscriber', data: 'id='+id, success: function (response) {
            if (response.status == "success")
            {
                $("#sub_"+id).remove();
            }
        }
    });
}
</script>

<div class="text-section" id="newsletter_users_window">
{/if}
{if="$action == '' OR $action == 'show_table'"}
          <br>
          <table class="gridTable">
            <thead>
                <tr><th>{function="localize('Type')"}</th><th>{function="localize('Address')"}</th><th>{function="localize('Added')"}</th><th>{function="localize('Options')"}</th></tr>
             </thead>            
            
            <tfoot>
                <tr>
                    <td colspan="3" class="rounded-foot-left"><em>Panthera newsletter, {function="localize('pages')"}:
                    {loop="$pager"}
                            {if="$value == True"}
                            <a href="#" onclick="jumpToAjaxPage({$key}); return false;"><b>{$key+1}</b></a>
                            {else}
                            <a href="#" onclick="jumpToAjaxPage({$key}); return false;">{$key+1}</a>
                            {/if}
                    {/loop}
                    </em></td>
                </tr>
            </tfoot>
            
            <tbody>
                {loop="$newsletter_users"}
                <tr id="sub_{$value.id}"><td>{$value.type}</td><td>{$value.address}</td><td>{$value.added}</td><td><input type="button" value="{function="localize('Delete')"}" onclick="removeSubscriber('{$value.id}');"></td></tr>
                {/loop}
            </tbody>
           </table>
{/if}
{if="$action == ''"}
         </div>
{/if}
