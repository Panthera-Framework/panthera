{$site_header}
<script type="text/javascript">
    $('.ajax_link').click(function (event) { event.preventDefault(); navigateTo(jQuery(this).attr('href')); return false;});
    
    function toggleGalleryVisibility(id)
    {
        panthera.jsonGET( { url: '{$AJAX_URL}?display=gallery&cat=admin&action=toggleGalleryVisibility&ctgid='+id, messageBox: 'w2ui', success: function (response) {
                if (response.status == 'success')
                {
                    if (response.visible)
                    {
                        $('#galleryCategory_row_'+id).css({'opacity': '1'});
                    } else {
                        $('#galleryCategory_row_'+id).css({'opacity': '0.5'});
                    }
                }
            }
        });
    }
    
    function removeGalleryCategory(id)
    {
        w2confirm('{function="localize('Are you sure you want delete this gallery?', 'gallery')"}', function (responseText) {
        
            if (responseText == 'Yes')
            {
                panthera.jsonGET( { url: '{$AJAX_URL}?display=gallery&cat=admin&action=deleteCategory&id='+id, messageBox: 'w2ui', success: function (response) {
                        if (response.status == 'success')
                        {
                            navigateTo('?display=gallery&cat=admin&filter={$category_filter}');
                        }
                
                    }
                });
            }
        
        });
    }
    
    $(document).ready(function () {
        $('#newGalleryForm').submit(function () {
            panthera.jsonPOST( { data: '#newGalleryForm', messageBox: 'w2ui', success: function (response) {
                    if (response.status == 'success')
                    {
                        navigateTo('?display=gallery&cat=admin&filter={$category_filter}');
                    }
                } 
            });
            
            return false;
        });
    });
    
</script>

{include="ui.titlebar"}
{$uiSearchbarName="uiTop"}
{include="ui.searchbar"}

<div class="grid-1">
    <div id="all_categories_window">
        <table class="gridTable">
            <thead>
                <tr>
                    <th>&nbsp;</th>
                    <th>{function="localize('Title', 'gallery')"}</th>
                    <th>{function="localize('Created', 'gallery')"}</th>
                    <th>{function="localize('Language', 'gallery')"}</th>
                    <th>&nbsp;</th>
                </tr>
            </thead>
            
            <tfoot>
                <tr>
                    <td colspan="8">
                        {$uiPagerName="galleryCategories"}{include="ui.pager"}
                    </td>
                </tr>
            </tfoot>
            
            <tbody>
                {loop="$category_list"}
                <tr id="galleryCategory_row_{$value->id}" style="{if="!$value->visibility"}opacity: 0.5;{/if}">
                    {if="$value->thumb_url"}
                    <td style="width: 60px;">
                        <a href="?display=gallery&cat=admin&action=displayCategory&unique={$value->unique}{if="$category_filter_complete"}&filter={$category_filter_complete}{/if}" class='ajax_link' id='gallery_title_{$value->id}'>
                        <img src="{$value->thumb_url|pantheraUrl}" style="width: 50px; height: 50px;">
                        </a>
                    </td>
                    
                    {/if}
                    <td {if="!$value->thumb_url"}colspan="2"{/if}>
                    <a href="?display=gallery&cat=admin&action=displayCategory&unique={$value->unique}{if="$category_filter_complete"}&filter={$category_filter_complete}{/if}" class='ajax_link' id='gallery_title_{$value->id}'>
                    {$value->title}
                    </a>
                    </td>
                    
                    <td>{$value->created} {function="localize('by')"} {$value->author_login}</td>
                    
                    <td>{$value->language}</td>
                    
                    <td>
                        <a href="#" onclick="toggleGalleryVisibility({$value->id});">
                        <img src="{$PANTHERA_URL}/images/admin/tango-icon-theme/System-search.svg" style="max-height: 22px;" id="hide_btn_{$value->id}" title="{function="localize('Show or hide', 'messages')"}">
                        </a>
                        <a href="#" onclick="removeGalleryCategory({$value->id});">
                        <img src="{$PANTHERA_URL}/images/admin/ui/delete.png" style="max-height: 22px;" title="Remove">
                        </a>
                        <a href="#" onclick="createPopup('_ajax.php?display=acl&cat=admin&popup=true&name=can_manage_gallery_{$value->id}', 1024, 'upload_popup');">
                        <img src="{$PANTHERA_URL}/images/admin/menu/users.png" style="max-height: 22px;" title="{function="localize('Manage permissions', 'messages')"}">
                        </a>
                    </td>
                </tr>
                {/loop}
            </tbody>
        </table>
    </div>
    
    <form action="?{function="getQueryString('GET', 'action=createCategory', '_')"}" method="POST" id="newGalleryForm">
    <table class="gridTable" style="margin-top: 30px; width: 40%;">
        <thead>
            <tr>
                <th colspan="2">{function="localize('Create new gallery', 'gallery')"}</th>
            </tr>
        </thead>
        
        <tbody>
            <tr>
                <td>{function="localize('Name', 'gallery')"}:</td>
                <td><input type="text" name="name" style="width: 95%;">
            </tr>
            
            <tr>
                <td>{function="localize('Visibility', 'gallery')"}:</td>
                <td><input type="radio" name="visibility" value="1" checked> {function="localize('Yes')"} <input type="radio" name="visibility" value="0"> {function="localize('No')"}</td>
            </tr>
            
            <tr>
                <td colspan="2" style="text-align: right;">
                    <input type="submit" value=" {function="localize('Create', 'gallery')"} " style="margin-right: 15px;">
                </td>
            </tr>
    </table>
    </form>
</div>
