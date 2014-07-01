<script type="text/javascript">
/**
  * Remove newsletter category from database
  *
  * @author Mateusz Warzyński
  */

function removeCategory(id)
{
            panthera.jsonPOST({ url: '{$AJAX_URL}?display=newsletter&cat=admin&action=removeCategory&nid='+id, data: '', success: function (response) {
                    if (response.status == "success")
                    {
                       $('#nid_'+id).remove();
                    }
                }
            });
}
</script>

{include="ui.titlebar"}

<div id="topContent">
    {$uiSearchbarName="uiTop"}
    {include="ui.searchbar"}
    
    <div class="separatorHorizontal"></div>
    
    <div class="searchBarButtonArea">
        <input type="button" value="{function="localize('Create a new category', 'newsletter')"}" onclick="panthera.popup.toggle('element:#newCategoryPopup')">
    </div>
</div>

<!-- New category popup -->

<div id="newCategoryPopup" style="display: none;">
    <script type="text/javascript">
    /**
      * Add newsletter category to database
      *
      * @author Mateusz Warzyński
      */

    function createCategory()
    {
	    title = $('#new_category_title').val();
	    type = $('#new_category_type').val();
	
	    panthera.jsonPOST({ url: '{$AJAX_URL}?display=newsletter&cat=admin&action=createCategory', data: 'title='+title+'&type='+type, success: function (response) {
                if (response.status == "success")
                {
                   navigateTo("{$AJAX_URL}?display=newsletter&cat=admin");
            	}
            }
        });
    }
    </script>

        <table class="formTable" style="margin: 0 auto; margin-bottom: 30px;">
            <thead>
                <tr>
                    <td colspan="2" class="formTableHeader" style="padding-top: 0px; padding-bottom: 30px;">
                        <p style="color: #e5ebef; padding: 0px; margin: 0px; margin-left: 30px;">{function="localize('Create a new category', 'newsletter')"}</p>
                    </td>
                </tr>
            </thead>
        
            <tbody>
                <tr>
                    <th>{function="localize('Create Category', 'newsletter')"}:</th><td><input type="text" id="new_category_title"></td>
                </tr>
                
                <tr style="background-color: transparent;">
                    <th>{function="localize('Default type', 'newsletter')"}:</th>
                    <td>
                        <select id="new_category_type">
                            {loop="$mailingTypes"}
                            <option value="{$value}">{$value}</option>
                            {/loop}
                        </select>
                    </td>
                </tr>
            </tbody>
            
            <tfoot>
                <tr>
                    <td colspan="2" style="padding-top: 35px;">
                        <input type="button" value="{function="localize('Cancel')"}" onclick="panthera.popup.close()" style="float: left; margin-left: 30px;">
                        <input type="button" value="{function="localize('Create', 'newsletter')"}" style="float: right; margin-right: 30px;" onclick="createCategory()">
                    </td>
                </tr>
            </tfoot>
        </table>
</div>


<div id="popupOverlay" style="text-align: center; padding-top: 20px; padding-bottom: 0px;"></div>

<div class="ajax-content" style="text-align: center;">
    <div style="display: inline-block;">
    <table style="margin: 0px;">
        <thead>
            <tr>
                 <th style="min-width: 200px;">{function="localize('Category', 'newsletter')"}</th>
	             <th style="min-width: 80px;">{function="localize('Users', 'newsletter')"}</th>
	             <th style="width: 90px;">{function="localize('Default type', 'newsletter')"}</th>
	             <th style="width: 170px;">{function="localize('Created', 'newsletter')"}</th>
	             <th style="width: 30px;">{function="localize('Options', 'newsletter')"}</th>
            </tr>
        </thead>
        
        <tbody>
        {loop="$categories"}
            <tr id="nid_{$value.nid}">
	            <td><a href="?display=newsletter.compose&cat=admin&nid={$value.nid}" class="ajax_link">{$value.title}</a></td>
	            <td>{$value.users}</td>
	            <td>{$value.default_type}</td>
	            <td>{$value.created}</td>
	            <td style="width: 50px;">
	                <a href="#" onclick="removeCategory('{$value.nid}');">
	                    <img src="{$PANTHERA_URL}/images/admin/ui/delete.png" style="max-height: 22px;" alt="{function="localize('Remove')"}">
	                </a>
	            </td>
	        </tr>
        {/loop}
        
        {if="!count($categories)"}
            <tr>
                <td colspan="6" style="text-align: center;">{function="localize('No newsletter categories found', 'newsletter')"}</td>
            </tr>
        {/if}
        </tbody>
    </table>
    
        <div style="position: relative; text-align: left;" class="pager">{$uiPagerName="adminNewsletterCategories"}{include="ui.pager"}</div>
    </div>
</div>
