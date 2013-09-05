{if="$action == ''"}
<script>
$('.ajax_link').click(function (event) { event.preventDefault(); navigateTo(jQuery(this).attr('href')); return false;});

function removeSubscriber(id)
{
    panthera.jsonPOST({ url: '{$AJAX_URL}?display=newsletter_users&cat=admin&nid={$nid}&action=remove_subscriber', data: 'id='+id, messageBox: 'w2ui', success: function (response) {
            if (response.status == "success")
            {
                $("#sub_"+id).remove();
            }
        }
    });
}

function addSubscriber()
{
	email = $("#add_user_email").val();
	
    panthera.jsonPOST({ url: '{$AJAX_URL}?display=newsletter_users&cat=admin&nid={$nid}&action=add_subscriber&email='+email, data: '', messageBox: 'w2ui'});
}
</script>

<div class="text-section" id="newsletter_users_window">
{/if}
{if="$action == '' OR $action == 'show_table'"}
          <br>
          <table class="gridTable">
            <thead>
                <tr><th>{function="localize('Type')"}</th><th>{function="localize('Address', 'newsletter')"}</th><th>{function="localize('Added', 'newsletter')"}</th><th>{function="localize('Options', 'newsletter')"}</th></tr>
             </thead>            
            
            <tfoot>
                <tr>
<<<<<<< HEAD
                    <td colspan="3" class="rounded-foot-left">{$uiPagerName="adminNewsletter"}{include="ui.pager"}
                    </td>
=======
                    <td colspan="4" class="rounded-foot-left"><em>Panthera newsletter, {function="localize('pages')"}:
                    {loop="$pager"}
                            {if="$value == True"}
                            <a href="#" onclick="jumpToAjaxPage({$key}); return false;"><b>{$key+1}</b></a>
                            {else}
                            <a href="#" onclick="jumpToAjaxPage({$key}); return false;">{$key+1}</a>
                            {/if}
                    {/loop}
                    </em></td>
>>>>>>> 07e8622114de2af78fda02ce2933a35eaaf777a6
                </tr>
            </tfoot>
            
            <tbody>
              {loop="$newsletter_users"}
                <tr id="sub_{$value.id}">
                	<td>{$value.type}</td>
                	<td>{$value.address}</td>
                	<td>{$value.added}</td>
                	<td>
	                	<a href="#" onclick="removeSubscriber('{$value.id}');">
	                        	<img src="{$PANTHERA_URL}/images/admin/ui/delete.png" style="max-height: 20px;" alt="{function="localize('Remove')"}">
	                    </a>
	                </td>
                </tr>
              {/loop}
                
				<tr>
					<td colspan="3"><input type="text" id="add_user_email" placeholder="{function="localize('Email', 'newsletter')"}" style="width: 90%;"></td>	                
	                <td>
	                   	<a onclick="addSubscriber();" style="cursor: pointer;">
	                   		<img src="{$PANTHERA_URL}/images/admin/list-add.png" style="height: 20px;">
	                  	</a>
					</td>
                </tr>
            </tbody>
           </table>
{/if}
{if="$action == ''"}
         </div>
{/if}
