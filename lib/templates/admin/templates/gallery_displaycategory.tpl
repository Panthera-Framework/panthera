{$site_header}

<script type="text/javascript">
    var uploadProgress = new panthera.ajaxLoader($('#addNewImage'));
    var selected = new Array; // array to store selected ids of gallery items
    
    function removeGalleryCategory(id)
    {
        w2confirm('{function="localize('Are you sure you want delete this category?', 'gallery')"}', function (responseText) {
        
            if (responseText == 'Yes')
            {
                panthera.jsonGET( { url: '{$AJAX_URL}?display=gallery&cat=admin&action=deleteCategory&id='+id, messageBox: 'w2ui', success: function (response) {
                        if (response.status == 'success')
                        {
                            navigateTo('?display=gallery&cat=admin');
                        }
                
                    }
                });
            }
        
        });
    }
    
    function removeGalleryItem(id)
    {
        w2confirm('{function="localize('Are you sure you want delete this item?', 'gallery')"}', function (responseText) {
        
            if (responseText == 'Yes')
            {

                    panthera.jsonGET({ url: '{$AJAX_URL}?display=gallery&cat=admin&action=deleteItem&image_id='+id, success: function (response) {
                            if (response.status == "success")
                            {
                                window.location = '';
                            }

                        }
                    });
            }
        });
    }

    function toggleItemVisibility(id)
    {
        panthera.jsonPOST({ url: '{$AJAX_URL}?display=gallery&cat=admin&action=toggleItemVisibility', data: 'ctgid='+id, success: function (response) {

                    if (response.status == "success")
                    {
                        if (response.visible == 1)
                        {
                            $('#gallery_item_'+id).css("opacity", 1);
                        } else {
                            $('#gallery_item_'+id).css("opacity", 0.5);
                        }
                    }

                }
        });
    }

    function setAsCategoryThumb(id, ctgid)
    {
        panthera.jsonGET({ 'url': '{$AJAX_URL}?display=gallery&cat=admin&action=setCategoryThumb&itid='+id+'&ctgid='+ctgid});
    }

    $(document).ready(function () {
        var multiuploadFiles = new Array();

        panthera.multiuploadArea({ id: '#addNewImage', start: function () {
            uploadProgress.ajaxLoaderInit();

        }, callback: function (content, fileName, fileNum, fileCount) {
                panthera.jsonPOST({ url: '?display=upload&cat=admin&action=handle_file&directory=gallery&popup=true', isUploading: true, async: false, data: { 'image': content, 'fileName': fileName}, success: function (response) {
                        if (response.status == "success")
                        {
                            multiuploadFiles.push(response.upload_id);
                        }

                    }
                });

                // finished
                if (fileNum == fileCount)
                {
                    panthera.jsonPOST({ url: '?display=gallery&cat=admin&action=addUploads&gid={$category_id}', isUploading: true, data: { 'ids': JSON.stringify(multiuploadFiles) }});
                    uploadProgress.stop();
                    navigateTo(window.location);
                }
            }
        });
        
        var saveCategoryDetailsDiv = new panthera.ajaxLoader($('#saveCategoryDetailsDiv'));
        
        /**
          * Save category details
          *
          * @author Damian Kęska
          */
        
        $('#saveCategoryDetails').submit(function () {
            panthera.jsonPOST({ data: '#saveCategoryDetails', messageBox: 'w2ui', spinner: saveCategoryDetailsDiv, success: function (response) {
                
                    // refresh the page
                    if (response.status == "success")
                        setTimeout("navigateTo('?display=gallery&cat=admin&action=displayCategory&unique="+response.unique+"&language="+response.language+"');", 800);

                } 
            });
            return false;
        });
        
        /*$('#removeImage').bind('drop', function (e) {
                console.log("Drop event");
        });
        
        $('.draggableGalleryItem').draggable({ addClasses: false, drag: function (event, ui) {
                if (panthera.inDropRange('#removeImage', 'cursor', event))
                {
                    console.log('In drop range of trash');
                }
        
            } 
        });*/
        
        panthera.forms.checkboxToggleLayer({ input: '#all_langs_checkbox', layer: '#language_input', reversed: true });

    });
    
    /**
      * Transform array to string ([0, 1] -> "0,1")
      *
      * @author Mateusz Warzyński
      */
    
    function transformArrayToString(array) {
        var returnString = array[0];
        
        for(var i=1; i<array.length; i++) {
            returnString = returnString+','+array[i];
        }
        
        return returnString;
    }
    
    /**
      * Remove value from array
      *
      * @author Mateusz Warzyński
      */
    
    function removeFromArrayByValue(array, value) {
        for(var i=0; i<array.length; i++) {
    
            if(array[i] == value) {
                array.splice(i, 1);
                break;
            }
    
        }
    }
    
/**
  * Select image
  *
  * @author Mateusz Warzyński
  */

function selectImage(id)
{
    if (selected.length == 0) {
        $(".imageOptionsBackground").css('display', 'none');
        $("#addNewImage").css('display', 'none');
        $("#selectedBox").css('display', 'block');
        
        selected.push(id);
        $("#image_"+id).css('-webkit-filter', 'blur(9px) contrast(1.5)');
        $("#image_"+id).css('opacity', '0.5');
        
        return true;
    }
    
    if ($("#image_"+id).css('-webkit-filter') == 'blur(9px) contrast(1.5)' || $("#image_"+id).css('opacity') == '0.5') {
        removeFromArrayByValue(selected, id);
        $("#image_"+id).css('-webkit-filter', 'none');
        $("#image_"+id).css('opacity', '1');
    } else {
        selected.push(id);
        $("#image_"+id).css('-webkit-filter', 'blur(9px) contrast(1.5)');
        $("#image_"+id).css('opacity', '0.5');
    }
    
    if (selected.length == 0) {
        $(".imageOptionsBackground").css('display', 'block');
        $("#addNewImage").css('display', 'block');
        $("#selectedBox").css('display', 'none');
    }
}

/**
  * Delete selected images
  *
  * @author Mateusz Warzyński
  */
 
function deleteSelectedImages()
{
    var ids = transformArrayToString(selected); 
    
    w2confirm('{function="localize('Are you sure you want to delete those images?', 'gallery')"}', function (responseText) {
        
            if (responseText == 'Yes')
            {
                panthera.jsonGET( { url: '{$AJAX_URL}?display=gallery&cat=admin&action=deleteItems&images_ids='+ids, messageBox: 'w2ui', success: function (response) {
                        if (response.status == 'success')
                        {
                            window.location = '';
                        } else {
                            window.location = '';
                            // w2alert(response.number+' {function="localize('image(s) have not been deleted! Refresh page.')"}');
                        }
                
                    }
                });
            }
        
    });
}

/**
  * Toggle visibility of selected images
  *
  * @author Mateusz Warzyński
  */
 
function toggleItemVisibilitySelected()
{
    var ids = transformArrayToString(selected); 
    
    w2confirm('{function="localize('Are you sure you want to toggle visibility those images?', 'gallery')"}', function (responseText) {
        
            if (responseText == 'Yes')
            {
                panthera.jsonGET( { url: '{$AJAX_URL}?display=gallery&cat=admin&action=toggleItemsVisibility&ids='+ids, messageBox: 'w2ui', success: function (response) {
                        if (response.status == 'success') {
                            window.location = '';
                        } else {
                            window.location = '';
                            // w2alert(response.number+' {function="localize('image(s) have not been toggled! Refresh page.')"}');
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
    
    <div class="separatorHorizontal"></div>
    
    <div class="searchBarButtonArea">
        <input type="button" value="{function="localize('Back')"}" onclick="navigateTo('?display=gallery&cat=admin&language={$current_lang}');" style="float: left; margin-left: 10px;">
       {if="!$all_langs"} <input type="button" value="{function="localize('Other languages', 'gallery')"}" onclick="panthera.popup.toggle('element:#languagePopup')"> {/if}
        <input type="button" value="{function="localize('Settings')"}" onclick="panthera.popup.toggle('element:#settingsPopup')">
        <input type="button" value="{function="localize('Delete category', 'gallery')"}" onclick="removeGalleryCategory({$category_id});">
    </div>
</div>

{if="!$all_langs"}
<!-- Language popup -->
<div id="languagePopup" style="display: none;">
    <table class="formTable" style="margin: 0 auto; margin-bottom: 30px;">
        
        <thead>
            <tr>
                <td colspan="2" class="formTableHeader" style="padding-top: 0px; padding-bottom: 30px;">
                    <p style="color: #e5ebef; padding: 0px; margin: 0px; margin-left: 15px; font-size: 14px;">{function="localize('Select language name to edit or create this gallery in other language', 'gallery')"}</p>
                </td>
            </tr>
        </thead>
        
        <tbody>
           {loop="$languages"}
            <tr>
                <th style="padding: 4px; padding-left: 90px;"><a href="#{$key}" onclick="navigateTo('?display=gallery&cat=admin&action=displayCategory&unique={$unique}&language={$key}');">{$key}</a></th>
                <th style="padding: 4px; padding-left: 90px;"></th>
            </tr>
           {/loop}
        </tbody>
        
        <tfoot>
          <tr>
            <td colspan="2" style="padding-top: 35px;">
                <input type="button" value="{function="localize('Cancel')"}" onclick="panthera.popup.close()" style="float: right; margin-right: 180px;">
            </td>
          </tr>
        </tfoot>
        
    </table>
</div>
{/if}

<!-- Settings popup -->
<div id="settingsPopup" style="display: none;">
   <form action="?display=gallery&cat=admin&action=saveCategoryDetails&id={$galleryObject->id}" method="POST" id="saveCategoryDetails">
    <table class="formTable" style="margin: 0 auto; margin-bottom: 30px;">
        
        <tbody>
            <tr id="title_tr">
                <th>{function="localize('Title', 'gallery')"}:</th>
                <th><input type="text" style="width: 95%;" name="title" value="{$galleryObject->title}"></th>
            </tr>
            
            <tr id="visibility_tr">
                <th>{function="localize('Visibility', 'gallery')"}:</th>
                <th>
                    <select name="visibility">
                        <option value="1" {if="$category_visibility == 1"} selected {/if}>{function="localize('visible', 'gallery')"}</option>
                        <option value="0" {if="$category_visibility == 0"} selected {/if}>{function="localize('invisible', 'gallery')"}</option>
                    </select>
                </th>
            </tr>
            
            <tr id="created_tr">
                <th>{function="localize('Created', 'gallery')"}:</th>
                <th>{$galleryObject->created} ({$galleryObject->author_login})</th>
            </tr>
            
            <tr id="all_langs_tr">
                <th>{function="localize('Make this gallery same for all languages', 'gallery')"}:</th>
                <th><input type="checkbox" name="all_langs" value="1"{if="$all_langs"} checked{/if} id="all_langs_checkbox"></th>
            </tr>
            
            <tr {if="$all_langs"}style='display: none;'{/if} id="language_input">
                <th>{function="localize('Save this gallery in', 'gallery')"}:</th>
                <th>
                    <select name="language">
                    {loop="$languages"}
                    <option value="{$key}"{if="$category_language == $key"} selected{/if}>{$key}</option>
                    {/loop}
                    </select>
                </th>
            </tr>
        </tbody>
        
        <tfoot>
          <tr>
            <td colspan="2" style="padding-top: 35px;">
                <input type="button" value="{function="localize('Cancel')"}" onclick="panthera.popup.close()" style="float: left; margin-left: 30px;">
                <input type="submit" value="{function="localize('Save')"}" style="float: right; margin-right: 30px;">
            </td>
          </tr>
        </tfoot>
        
    </table>
   </form>
   
   <script type="text/javascript"> // saveCategoryDetails
    var saveCategoryDetailsDiv = new panthera.ajaxLoader($('#saveCategoryDetailsDiv'));
    
      /**
        * Save category details
        *
        * @author Damian Kęska
        */
   
    $('#saveCategoryDetails').submit(function () {
        panthera.jsonPOST({ data: '#saveCategoryDetails', messageBox: 'w2ui', spinner: saveCategoryDetailsDiv, success: function (response) {
            // refresh the page
            if (response.status == "success")
                setTimeout("navigateTo('?display=gallery&cat=admin&action=displayCategory&unique="+response.unique+"&language="+response.language+"');", 800);
            } 
        });
        return false;
    });
   </script>
</div>

<div id="popupOverlay" style="text-align: center; padding-top: 20px; padding-bottom: 0px;"></div>

<!-- Content -->

<div class="ajax-content" style="text-align: center; background-color: #56687b;">
    
    <div class="uploadBoxCentered" style="min-height: 0px; width: 150px; margin-top: -30px;">
      <div class="addBox" id="addNewImage" ondragover="return false;">
            <a href="#" onclick="navigateTo('?display=gallery&cat=admin&action=addItem&ctgid={$category_id}');"><img src="{$PANTHERA_URL}/images/admin/cross_icon.png" style="position: relative; top: 30px; opacity: 0.8;" title="{function="localize('Drag and drop files to this area to start uploading', 'gallery')"}"></a>
      </div>
      
      <div id="selectedBox" class="selectedBox">
          <a style="cursor: pointer;" onclick="deleteSelectedImages();"><img src="{$PANTHERA_URL}/images/admin/menu/Actions-process-stop-icon.png" title="{function="localize('Delete', 'messages')"}"></a>
          &nbsp;&nbsp;&nbsp;&nbsp;
          <a style="cursor: pointer;" onclick="toggleItemVisibilitySelected();"><img src="{$PANTHERA_URL}/images/admin/menu/search.png" title="{function="localize('Toggle visibility', 'gallery')"}"></a>
      </div>
  </div> 
    
    <div class="galleryBox uploadBoxCentered">
       {loop="$item_list"}
        <div class="imageBox {if="$value->visibility == 0"}galleryItemHidden{/if}" id="gallery_item_{$value->id}">
           
           <div class="image" onMouseOver="$('#options_{$value->id}').css('opacity', '1.0');" onMouseOut="$('#options_{$value->id}').css('opacity', '0');" style="overflow: hidden;">
              <a alt="{function="localize('Click to select', 'gallery')"}" style="cursor: pointer;" onclick="selectImage({$value->id});">
                <img src="{$value->getThumbnail(300, True, True)}" id="image_{$value->id}" class="imagePicture">
              </a>
            
            <div id="options_{$value->id}" class="imageOptions">
                <div class="imageOptionsBackground">
                    <p id="imageTitle">{$value->getTitle(20)}</p>
                    <p>
                        <a onclick="navigateTo('?display=gallery&cat=admin&action=editItemForm&itid={$value->id}&page={$page}');"><img src="{$PANTHERA_URL}/images/admin/menu/mce.png" class="galleryIcon" title="{function="localize('Edit', 'messages')"}"></a>
                        <a onclick="removeGalleryItem({$value->id});"><img src="{$PANTHERA_URL}/images/admin/menu/Actions-process-stop-icon.png" class="galleryIcon" title="{function="localize('Delete', 'messages')"}"></a>
                        <a onclick="toggleItemVisibility('{$value->id}');"><img src="{$PANTHERA_URL}/images/admin/menu/search.png" class="galleryIcon" title="{function="localize('Toggle visibility', 'gallery')"}"></a>
                        <a onclick="createPopup('_ajax.php?display=acl&cat=admin&popup=true&name=can_manage_gimage_{$value->id}', 1024, 550);"><img src="{$PANTHERA_URL}/images/admin/menu/users.png" class="galleryIcon" title="{function="localize('Manage permissions', 'messages')"}" id="permissionsButton"></a>
                        <a onclick="setAsCategoryThumb({$value->id}, {$category_id});"><img src="{$PANTHERA_URL}/images/admin/menu/star.png" class="galleryIcon" title="{function="localize('Set as thumbnail', 'gallery')"}"></a>
                    </p>
                </div>
            </div>
           </div>
        </div>
       {/loop}
    </div>
    
    <div style="width: 65%; margin: 0 auto;">
        <style> a { color: white; } </style>
       <div style="display: inline-block; font-size: 12px; color: white;">{$uiPagerName="adminGalleryItems"}{include="ui.pager"}</div>
    </div>
    
</div>