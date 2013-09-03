<script type="text/javascript">
var spinner = new panthera.ajaxLoader($('#newsletterWindow'));

/**
  * Remove newsletter category from database
  *
  * @author Mateusz Warzyński
  */

function removeCategory(id)
{
    w2confirm('{function="localize('Are you sure you want to delete this category?', 'newsletter')"}', function callbackBtn(btn) { 
        if (btn == "Yes")
        {
            panthera.jsonPOST({ url: '{$AJAX_URL}?display=newsletter&cat=admin&action=removeCategory&nid='+id, data: '', messageBox: 'w2ui', success: function (response) {
                    if (response.status == "success")
                    {
                       $('#nid_'+id).remove();
                    }
                }
            });
        }
     });
}

/**
  * Add newsletter category to database
  *
  * @author Mateusz Warzyński
  */

function createCategory()
{
	title = $('#new_category').val();
	
	panthera.jsonPOST({ url: '{$AJAX_URL}?display=newsletter&cat=admin&action=createCategory&title='+title, data: '', messageBox: 'w2ui', success: function (response) {
            if (response.status == "success")
            {
               navigateTo("{$AJAX_URL}?display=newsletter&cat=admin");
        	}
        }
    });
}

</script>

{include="ui.titlebar"}

{$uiSearchbarName="uiTop"}
{include="ui.searchbar"}

<div class="grid-1">
        	
	    <table class="gridTable">
	         	<thead>
	                <tr>
	                    <th scope="col" class="rounded-company">{function="localize('Category')"}</th>
	                    <th>{function="localize('Users')"}</th>
	                    <th style="width: 70px;">{function="localize('Options')"}</th>
	                </tr>
	            </thead>
	            
	            <tfoot>
		            <tr>
		            <td colspan="3"><em>{$uiPagerName="newsletters"}
		            {include="ui.pager"}
		            </em></td>
		            </tr>
		        </tfoot>
	            
	            <tbody>
	              {loop="$categories"}
	                <tr id="nid_{$value.nid}">
	                    <td><a href="#{$value.title}" onclick="navigateTo('?display=compose_newsletter&cat=admin&nid={$value.nid}')">{$value.title}</a></td>
	                    <td>{$value.users}</td>
	                    <td>
	                    	<a href="#" onclick="removeCategory('{$value.nid}');">
	                        	<img src="{$PANTHERA_URL}/images/admin/ui/delete.png" style="max-height: 22px;" alt="{function="localize('Remove')"}">
	                    	</a>
	                    </td>
	                </tr>
	              {/loop}
	              	<tr>
	                    <td colspan="2"><input type="text" id="new_category" placeholder="{function="localize('New newsletter category', 'newsletter')"}" style="width: 90%;"></td>
	                    <td>
	                    	<a onclick="createCategory();" style="cursor: pointer;">
	                    		<img src="{$PANTHERA_URL}/images/admin/list-add.png" style="height: 22px;">
	                    	</a>
	                    </td>
	                </tr>
	            </tbody>
	    </table>
</div>

