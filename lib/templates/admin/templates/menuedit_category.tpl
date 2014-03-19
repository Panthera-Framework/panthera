{$site_header}

<script type="text/javascript">

var menuID = "";

/**
  * Make our elements sortable...
  *
  * @author Mateusz Warzyński
  */

$(document).ready(function(){
    var fixHelper = function(e, ui) {
        ui.children().each(function() {
            $(this).width($(this).width());
        });
        return ui;
    };

    $('.categoryTable tbody').sortable({
        helper: fixHelper, 
        cancel: ".ui-state-disabled",
        update: function (e, ui) {
            panthera.jsonPOST({ url: "?display=menuedit&cat=admin&action=saveOrder&category={$cat_type}", data: 'order='+getTableOrder()});
        }
    });
    $('.categoryTable tbody').disableSelection();
});

/**
  * Order table
  *
  * @author Mateusz Warzyński
  */

function getTableOrder()
{
    var items = $(".sortable_hidden");
    var linkIDs = [items.size()];
    var index = 0;

    items.each(
        function(intIndex) {
            linkIDs[index] = $(this).val();
            index++;
        });

    linkIDs.reverse();

    return JSON.stringify(linkIDs);
}

/**
  * Remove menu item from database
  *
  * @author Mateusz Warzyński
  */

function removeItem(id)
{
    w2confirm('{function="localize('Are you sure you want delete this item?', 'menuedit')"}', function (responseText) {
        if (responseText == 'Yes')
        {
            panthera.jsonPOST({ url: '{$AJAX_URL}?display=menuedit&cat=admin&action=itemRemove&item_id='+id, data: '', success: function (response) {
                    if (response.status == "success") {
                        navigateTo('?display=menuedit&cat=admin&action=getCategory&category={$category}');
                    }
                }
            });
        }
    });
}
</script>

{include="ui.titlebar"}

<div id="topContent" style="min-height: 50px;">
    <div class="searchBarButtonArea">
        <div style="float: left; display: inline-block; margin-left: 10px;">
            <input type="button" value="{function="localize('Back')"}" onclick="navigateTo('?display=menuedit&cat=admin');">
        </div>
        
        {if="$editCategoryButton"}
            <input type="button" value="{function="localize('Edit category', 'menuedit')"}" onclick="panthera.popup.toggle('?display=menuedit&cat=admin&action=getCategory&subaction=edit&category={$category}&ref=getCategory')">
        {/if}
        
        {if="$newItemButton"}
            <input type="button" value="{function="localize('Add item', 'menuedit')"}" onclick="panthera.popup.toggle('element:#newItem')">
        {/if}
    </div>
</div>

<!-- New menu item -->

<div id="newItem" style="display: none;">
    
    <script type="text/javascript">
    /**
      * Add item to category
      *
      * @author Mateusz Warzyński
      */
    
    $('#add_item_form').submit(function () {
        panthera.jsonPOST({ data: '#add_item_form', messageBox: 'w2ui', success: function (response) {
                if (response.status == "success")
                    navigateTo('?display=menuedit&cat=admin&action=getCategory&category={$cat_type}');
            }
        });
    
        return false;
    
    });
    </script>

    <form id="add_item_form" method="POST" action="?display=menuedit&cat=admin&action=createItem">
        <table class="formTable" style="margin: 0 auto; margin-bottom: 30px;">
            <thead>
                 <tr>
                     <td colspan="2" class="formTableHeader" style="padding-top: 0px; padding-bottom: 30px;">
                         <p style="color: #e5ebef; padding: 0px; margin: 0px; margin-left: 30px;">{function="localize('Create new item', 'menuedit')"}</p>
                     </td>
                 </tr>
            </thead>
            
            <tfoot>
                <tr>
                    <td colspan="3" style="padding-top: 35px;">
                        <input type="button" value="{function="localize('Cancel')"}" onclick="panthera.popup.close()" style="float: left; margin-left: 30px;">
                        <input type="submit" value="{function="localize('Add', 'menuedit')"}" style="float: right; margin-right: 30px;">
                    </td>
                </tr>
            </tfoot>
            <tbody>
                <tr>
                    <th>{function="localize('Title', 'menuedit')"}</th>
                    <th><input type="text" name="item_title" style="width: 99%;"></th>
                </tr>
                <tr>
                    <th>{function="localize('Link', 'menuedit')"}</th>
                    <th><input type="text" name="item_link" style="width: 99%;"></th>
                </tr>
                <tr>
                    <th>{function="localize('Language', 'menuedit')"}</th>
                    <th>
                        <select name="item_language">
                         {loop="$item_language"}
                            <option value="{$key}">{$key}</option>
                         {/loop}
                        </select>
                    </th>
                </tr>
                
                <tr>
                    <th>{function="localize('SEO friendly name', 'menuedit')"} <small>({function="localize('Optional', 'menuedit')"})</small></th>
                    <th><input type="text" name="item_url_id" style="width: 99%;"></th>
                </tr>
                
                <tr>
                    <th>{function="localize('Tooltip', 'menuedit')"} <small>({function="localize('Optional', 'menuedit')"})</small></th>
                    <th><input type="text" name="item_tooltip" style="width: 99%;"></th>
                </tr>
                
                <tr>
                    <th>{function="localize('Icon', 'menuedit')"} <small>({function="localize('Optional', 'menuedit')"})</small></th>
                    <th><input type="text" name="item_icon" style="width: 99%;"></th>
                </tr>
                
                <tr>
                    <th>{function="localize('Attributes', 'menuedit')"} <small>({function="localize('Optional', 'menuedit')"})</small></th>
                    <th><input type="text" name="item_attributes" style="width: 99%;"></th>
                </tr>
            </tbody>
        </table>
        <input type="hidden" id="cat_type" name="cat_type" value="{$cat_type}">
    </form>
</div>

<div id="popupOverlay" style="text-align: center; padding-top: 20px; padding-bottom: 0px;"></div>

<!-- Ajax content -->
<div class="ajax-content" style="text-align: center;">
    <div id="menu_category" style="display: relative;">
      <table style="display: inline-block;" class="categoryTable">
        <thead>
            <tr>
                <th scope="col" class="rounded-company" style="width: 250px;">{function="localize('Title', 'menuedit')"}</th>
                <th>{function="localize('Link', 'menuedit')"}</th>
                <th>{function="localize('Language', 'menuedit')"}</th>
                <th>{function="localize('SEO friendly name', 'menuedit')"}</th>
                <th>{function="localize('Tooltip', 'menuedit')"}</th>
                <th>{function="localize('Icon', 'menuedit')"}</th>
                <th>{function="localize('Attributes', 'menuedit')"}</th>
                <th>{function="localize('Options', 'messages')"}</th>
            </tr>
        </thead>
        
        <tbody>
            {if="count($menus) > 0"}
            {loop="$menus"}
            <tr id="item_{$value.id}">
                <td><a href="{$AJAX_URL}?display=menuedit&cat=admin&action=getItem&item_id={$value.id}" class="ajax_link">{$value.title}</a><input type="hidden" id="sortable_{$value.id}" class="sortable_hidden" value="{$value.id}"></td>
                <td><a href="{$value.link}" target="_blank">{$value.link_original}</a></td>
                <td>{$value.language}</td>
                <td>{$value.url_id}</td>
                <td>{$value.tooltip}</td>
                <td>{$value.icon}</td>
                <td>{$value.attributes}</td>
                <td>
                    <a href="#" onclick="removeItem({$value.id})">
                    <img src="{$PANTHERA_URL}/images/admin/ui/delete.png" style="max-height: 22px;" alt="{function="localize('Delete')"}">
                    </a>
                </td>
            </tr>
            {/loop}
            {else}
            <tr>
                <td colspan="8" style="text-align: center;">{function="localize('No any menu items found', 'menuedit')"}</td>
            </tr>
            {/if}
        </tbody>
      </table>
    </div>
</div>
