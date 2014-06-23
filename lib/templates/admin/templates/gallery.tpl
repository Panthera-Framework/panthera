{$site_header}

<script type="text/javascript">
    
    function toggleGalleriesVisibility(array)
    {
        panthera.jsonGET( { url: '{$AJAX_URL}?display=gallery&cat=admin&action=toggleGalleryVisibility&categoryid='+array, messageBox: 'w2ui', success: function (response) {
                if (response.status == 'success')
                {
                    navigateTo('?display=gallery&cat=admin&language={$current_lang}');
                }
            }
        });
    }
    
    function removeGalleryCategories(array)
    {
        w2confirm('{function="localize('Are you sure you want delete that categories? <br> Please, have another think coming. Later will be too late.', 'gallery')"}', function (responseText) {
        
            if (responseText == 'Yes')
            {
                panthera.jsonGET( { url: '{$AJAX_URL}?display=gallery&cat=admin&action=deleteCategory&categoryid='+array, messageBox: 'w2ui', success: function (response) {
                        if (response.status == 'success')
                        {
                            navigateTo('?display=gallery&cat=admin&language={$current_lang}');
                        }
                
                    }
                });
            }
        
        });
    }

</script>

{include="ui.titlebar"}

<div id="topContent">
    {$uiSearchbarName="uiTop"}
    {include="ui.searchbar"}

    <div class="searchBarButtonArea">
        <span data-searchbardropdown="#searchDropdown" id="searchDropdownSpan" style="position: relative; cursor: pointer;">
             <input type="button" value="{function="localize('Switch language', 'custompages')"}">
        </span>
        
        <div id="searchDropdown" class="searchBarDropdown searchBarDropdown-tip searchBarDropdown-relative">
            <ul class="searchBarDropdown-menu">
            {loop="$languages"}
                <li style="text-align: left;">
                    <a href="" style="cursor: pointer;" onclick="navigateTo('?display=gallery&cat=admin&language={$key}');">
                        <img src="{$PANTHERA_URL}/images/admin/flags/{$key}.png" style="height: 12px; margin: 1px; vertical-align: middle;"> {$key}
                    </a>
                </li>
            {/loop}
            </ul>
        </div>
        
        <input type="button" value="{function="localize('Create new gallery', 'gallery')"}" onclick="panthera.popup.toggle('element:#createGallery')">

    </div>
</div>

<!-- Create new gallery popup -->

<div id="createGallery" style="display: none;">
   <form action="?{function="Tools::getQueryString('GET', 'action=createCategory', '_')"}" method="POST" id="newGalleryForm">
    <table class="formTable" style="margin: 0 auto; margin-bottom: 30px;">
        
        <thead>
            <tr>
                <td colspan="2" class="formTableHeader" style="padding-top: 0px; padding-bottom: 30px;">
                    <p style="color: #e5ebef; padding: 30px; margin: 0px; margin-left: 15px;">{function="localize('Create new gallery', 'gallery')"}</p>
                </td>
            </tr>
        </thead>
        
        <tbody>
          
          <tr>
            <th>{function="localize('Name', 'gallery')"}:</th>
            <th><input type="text" name="name" style="width: 95%;"></th>
          </tr>
          
          <tr>
            <th>{function="localize('Visibility', 'gallery')"}:</th>
            <th><input type="radio" name="visibility" value="1" checked> {function="localize('Yes')"} <input type="radio" name="visibility" value="0"> {function="localize('No')"}</th>
          </tr>
          
          <tr>
            <th>{function="localize('Language', 'gallery')"}:</th>
            <th>
                <div class="select" style="margin-top: 14px; margin-left: 3px;">
                 <select name="language">
                   {loop="$languages"}
                    <option {if="$key == $current_lang"} selected {/if}>{$key}</option>
                   {/loop}
                 </select>
                </div>
            </th>
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
    <input type="text" name="language" value="{$current_lang}" style="display: none;">
   </form>
   
   <script type="text/javascript">
        $('#newGalleryForm').submit(function () {
            panthera.jsonPOST( { data: '#newGalleryForm', messageBox: 'w2ui', success: function (response) {
                    if (response.status == 'success')
                    {
                        navigateTo('?display=gallery&cat=admin&filter={$category_filter}&language={$current_lang}');
                    }
                } 
            });
            
            return false;
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

        <tbody class="hovered">
            {loop="$category_list"}
            <tr id="galleryCategory_row_{$value->id}" style="height: 59px; {if="!$value->visibility"}opacity: 0.5;{/if}"> 
                
                {if="$value->thumb_url"}
                <td style="padding-top: 4px; padding-right: 10px; padding-left: 10px;">
                    <a href="?display=gallery&cat=admin&action=displayCategory&unique={$value->unique}&language={$value->language}" class='ajax_link' id='gallery_title_{$value->id}'>
                    <img src="{$value->thumb_url|pantheraUrl}" style="max-width: 50px; height: 50px;">
                    </a>
                </td>
                {/if}
                
                <td {if="!$value->thumb_url"}colspan="2"{/if}>
                <a href="?display=gallery&cat=admin&action=displayCategory&unique={$value->unique}&language={$value->language}" class='ajax_link' id='gallery_title_{$value->id}'>
                {$value->title}
                </a>
                </td>
                <td>{$value->created} {function="localize('by')"} {$value->author_login}</td>
                <td>{$value->language}</td>
                
                <td>
                    <a href="#" onclick="toggleGalleriesVisibility('{$value->id}');">
                    <img src="{$PANTHERA_URL}/images/admin/tango-icon-theme/System-search.svg" style="max-height: 22px;" id="hide_btn_{$value->id}" title="{function="localize('Show or hide', 'messages')"}">
                    </a>
                    <a href="#" onclick="removeGalleryCategories('{$value->id}');">
                    <img src="{$PANTHERA_URL}/images/admin/ui/delete.png" style="max-height: 22px;" title="Remove">
                    </a>
                </td>
            </tr>
            {/loop}
            <tr id="noGalleryCategories" {if="$category_list"}style="display: none;"{/if}>
                <td colspan="5">{function="localize('No gallery categories found, create new one using button below', 'gallery')"}</td>
            </tr>
        </tbody>
    </table>
    
    <div style="margin-top: 10px; margin-left: 8px; color: #404c5a; font-size: 12px;">{$uiPagerName="adminGalleryCategories"}{include="ui.pager"}</div>
</div>
