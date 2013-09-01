{if $action == ''}
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

{include="ui.titlebar"}

<div class="text-section" id="newsletter_users_window">
{/if}
{if $action == '' or $action == 'show_table'}
          <br>
          <table class="gridTable">
            <thead>
                <tr><th>{"Type"|localize}</th><th>{"Address"|localize}</th><th>{"Added"|localize}</th><th>{"Options"|localize}</th></tr>
             </thead>            
            
            <tfoot>
                <tr>
                    <td colspan="3" class="rounded-foot-left"><em>Panthera newsletter, {"pages"|localize}:
                    {foreach from=$pager key=page item=active}
                            {if $active == true}
                            <a href="#" onclick="jumpToAjaxPage({$page}); return false;"><b>{$page+1}</b></a>
                            {else}
                            <a href="#" onclick="jumpToAjaxPage({$page}); return false;">{$page+1}</a>
                            {/if}
                        {/foreach}
                    </em></td>
                </tr>
            </tfoot>
            
            <tbody>
                {foreach from=$newsletter_users key=k item=v}
                <tr id="sub_{$v.id}"><td>{$v.type}</td><td>{$v.address}</td><td>{$v.added}</td><td><input type="button" value="{"Delete"|localize}" onclick="removeSubscriber('{$v.id}');"></td></tr>
                {/foreach}
            </tbody>
           </table>
{/if}
{if $action == ''}
         </div>
{/if}
