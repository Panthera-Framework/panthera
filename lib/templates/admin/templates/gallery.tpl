{$site_header}

<script type="text/javascript">
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
</script>

{include="ui.titlebar"}

<div id="topContent">
    <div class="searchBarButtonArea">
        <input type="button" value="{function="localize('Create new gallery', 'gallery')"}" onclick="panthera.popup.toggle('element:#createGallery')">
    </div>
</div>

<!-- Create new gallery popup -->

<div id="createGallery" style="display: none;">
   <form action="?{function="getQueryString('GET', 'action=createCategory', '_')"}" method="POST" id="newGalleryForm">
    <table class="formTable" style="margin: 0 auto; margin-bottom: 30px;">
        
        <tbody>
          <tr>
            <th>{function="localize('Name', 'gallery')"}:</th>
            <th><input type="text" name="name" style="width: 95%;"></th>
          </tr>
          
          <tr>
            <th>{function="localize('Visibility', 'gallery')"}:</th>
            <th><input type="radio" name="visibility" value="1" checked> {function="localize('Yes')"} <input type="radio" name="visibility" value="0"> {function="localize('No')"}</th>
          </tr>
        </tbody>
        
        <tfoot>
          <tr>
            <td colspan="2" style="padding-top: 35px;">
                <input type="button" value="{function="localize('Cancel')"}" onclick="panthera.popup.close()" style="float: left; margin-left: 30px;">
                <input type="submit" value="{function="localize('Add')"}" style="float: right; margin-right: 30px;">
            </td>
          </tr>
        </tfoot>
        
    </table>
   </form>
   
   <script type="text/javascript">
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
</div>

<!-- Content -->
<div class="ajax-content" style="text-align: center;">
    <table style="display: inline-block;">

        <thead>
            <tr>
                <th>&nbsp;</th>
                <th>{function="localize('Title', 'gallery')"}</th>
                <th>{function="localize('Created', 'gallery')"}</th>
                <th>{function="localize('Language', 'gallery')"}</th>
                <th>&nbsp;</th>
            </tr>
        </thead>

        <tbody>
            {loop="$category_list"}
            <tr id="galleryCategory_row_{$value->id}" style="{if="!$value->visibility"}opacity: 0.5;{/if}">
                
                {if="$value->thumb_url"}
                <td style="padding-right: 0px; padding-left: 10px;">
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
            <tr id="noGalleryCategories" {if="$category_list"}style="display: none;"{/if}>
                <td colspan="5">{function="localize('No gallery categories found, create new one using button below', 'gallery')"}</td>
            </tr>
        </tbody>
    </table>
</div>
