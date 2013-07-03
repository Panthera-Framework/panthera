{$site_header}
<script type="text/javascript">
    var uploadProgress = new panthera.ajaxLoader($('#addNewImage'));

    function removeGalleryItem(id)
    {
        $.msgBox({
            title: "{"Are you sure?"|localize:messages}",
            content: "{"Do you really want to delete this item?"|localize}",
            type: "confirm",
            autoClose: true,
            opacity: 0.6,
            buttons: [{ value: "{"Yes"|localize:messages}" }, { value: "{"No"|localize:messages}" }, { value: "{"Cancel"|localize:messages}"}],
            success: function (result) {
                if (result == "{"Yes"|localize:messages}") {

                    panthera.jsonGET({ url: '{$AJAX_URL}?display=gallery&action=delete_item&image_id='+id, success: function (response) {
                            if (response.status == "success")
                            {
                                $('#gallery_item_'+id).remove();
                            }

                        }
                    });
                }
            }
        });


    }

    function toggleItemVisibility(id)
    {
        panthera.jsonGET({ url: '{$AJAX_URL}?display=gallery&action=toggle_item_visibility&itid='+id, success: function (response) {

                    if (response.status == "success")
                    {
                        if (response.visible == 1)
                        {
                            $('#gallery_item_'+id).addClass('galleryItemHidden');
                        } else {
                            $('#gallery_item_'+id).removeClass('galleryItemHidden');
                        }
                    }

                }
        });
    }

    function setAsCategoryThumb(id, ctgid)
    {
        panthera.jsonGET({ 'url': '{$AJAX_URL}?display=gallery&action=set_category_thumb&itid='+id+'&ctgid='+ctgid});
    }

    $(document).ready(function () {
        var multiuploadFiles = new Array();

        panthera.multiuploadArea({ id: '#addNewImage', start: function () {
            uploadProgress.ajaxLoaderInit();

        }, callback: function (content, fileName, fileNum, fileCount) {
                panthera.jsonPOST({ url: '?display=upload&action=handle_file&popup=true', isUploading: true, data: { 'image': content, 'fileName': fileName}, success: function (response) {
                        if (response.status == "success")
                        {
                            multiuploadFiles.push(response.upload_id);
                        }

                    }
                });

                // finished
                if (fileNum == fileCount)
                {
                    panthera.jsonPOST({ url: '?display=gallery&action=adduploads&gid={$category_id}', isUploading: true, data: { 'ids': JSON.stringify(multiuploadFiles) }});
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
            panthera.jsonPOST({ data: '#saveCategoryDetails', messageBox: 'userinfoBox', spinner: saveCategoryDetailsDiv, success: function (response) {
                
                    // refresh the page
                    if (response.status == "success")
                        setTimeout("navigateTo('?display=gallery&action=display_category&unique="+response.unique+"&language="+response.language+"');", 800);

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
</script>

<div class="titlebar">{"Gallery"|localize:messages}: &nbsp;{$category_title} ({$langauge}){include file="_navigation_panel.tpl"}</div>

    <div class="msgSuccess" id="userinfoBox_success"></div>
    <div class="msgError" id="userinfoBox_failed"></div>

    {if !$all_langs}
    <div class="grid-2" style="position: relative;" id="languagesGrid">
          <div class="title-grid">{"Gallery in other languages"|localize:gallery}<span></span></div>
          <div class="content-table-grid">
              <table class="insideGridTable">
                <tfoot>
                    <tr>
                        <td colspan="3"><small>{"Select langauge name to edit or create this gallery in other language"|localize:gallery}</small></td>
                    </tr>
                </tfoot>
            
                <tbody>
                    {foreach from=$languages key=k item=i}
                        <tr>
                            <td style="padding: 10px; border-right: 0px; width: 1%;"><a href="#{$k}" onclick="navigateTo('?display=gallery&action=display_category&unique={$unique}&language={$k}');">{$k}</a></td>
                            <td style="width: 60px; padding: 10px; border-right: 0px;"></td>
                        </tr>
                    {/foreach}
                </tbody>
            </table>
         </div>
    </div>
    {/if}
    
    <!-- settings -->
    <form action="?display=gallery&action=saveCategoryDetails&id={$galleryObject->id}" method="POST" id="saveCategoryDetails">
    <div class="grid-{if $all_langs}1{else}2{/if}" style="position: relative; margin-bottom: 50px;" id="saveCategoryDetailsDiv">
          <div class="title-grid">{"Settings"|localize}<span></span></div>
          <div class="content-table-grid">
              <table class="insideGridTable">
                <tbody>
                        <tr id="title_tr">
                            <td style="width: 120px;">{"Title"|localize:gallery}:</td>
                            <td style="border-right: 0px;"><input type="text" style="width: 98%;" name="title" value="{$galleryObject->title}"></td>
                        </tr>
                        
                        <tr id="created_tr">
                            <td>{"Created"|localize:gallery}:</td>
                            <td style="border-right: 0px;">{$galleryObject->created} ({$galleryObject->author_login})</td>
                        </tr>
                        
                        <tr id="all_langs_tr">
                            <td>{"Make this gallery same for all languages"|localize:gallery}:</td>
                            <td style="border-right: 0px;"><input type="checkbox" name="all_langs" value="1"{if $all_langs} checked{/if} id="all_langs_checkbox"></td>
                        </tr>
                        
                        <tr {if $all_langs}style="display: none;"{/if} id="language_input">
                            <td>{"Save this gallery in"|localize:gallery}:</td>
                            <td style="border-right: 0px;">
                            <select name="language">
                            {foreach from=$languages key=k item=i}
                                <option value="{$k}"{if $galleryObject->language == $k} selected{/if}>{$k}</option>
                            {/foreach}
                            </select>
                            </td>
                        </tr>
                        
                        <tr id="save_tr">
                            <td style="padding: 10px; border-right: 0px; border-bottom: 0px;">&nbsp;</td>
                            <td style="width: 60px; padding: 10px; border-right: 0px; border-bottom: 0px;"><input type="submit" value="{"Save"|localize}" style="float: right;"></td>
                        </tr>
                </tbody>
            </table>
         </div>
    </div>
    </form>
    <!-- end of settings -->

<div class="grid-1" style="width: 100%;">

    {foreach from=$item_list key=k item=i}
    <div class="galleryItem{if $i->visibility eq 1} galleryItemHidden{/if} draggableGalleryItem" id="gallery_item_{$i->id}">
        <div class="galleryImageFrame">
            <div class="paGalleryFrameContent">
                <img src="{$i->getThumbnail(300, True, True)}" class="galleryImage">
            </div>

            <div class="paGalleryFrameOverlay">
                <small>
                    <br>
                    <b>{$i->title}</b>
                    <br><br>
                    <i>{$i->description}</i>
                    <br><br>{"Created"|localize:gallery}: {$i->created}
                </small>

                <br><br>
                {"url"|localize:gallery}: <input type="text" value="{$i->link}" style="width: 200px; height: 20px;">
            </div>
        </div>

        <div class="galleryItemDetails">
            <div style="text-align: center;">
                <a href="#edit" onclick="navigateTo('?display=gallery&action=edit_item_form&itid={$i->id}');"><img src="{$PANTHERA_URL}/images/admin/tango-icon-theme/Text-x-generic_with_pencil.svg" class="galleryIcon" title="{"Edit"|localize:messages}"></a>
                <a href="#delete" onclick="removeGalleryItem({$i->id});"><img src="{$PANTHERA_URL}/images/admin/menu/Actions-process-stop-icon.png" class="galleryIcon" title="{"Delete"|localize:messages}"></a>
                <a href="#toggle-visibility" onclick="toggleItemVisibility({$i->id});"><img src="{$PANTHERA_URL}/images/admin/tango-icon-theme/System-search.svg" class="galleryIcon" title="{"Toggle visibility"|localize:gallery}"></a>
                <a href="#rights" onclick="createPopup('_ajax.php?display=acl&popup=true&name=gallery_manage_img_{$i->id}', 1024, 550);"><img src="{$PANTHERA_URL}/images/admin/menu/users.png" class="galleryIcon" title="{"Manage permissions"|localize:messages}" id="permissionsButton"></a>
                <a href="#thumbnail" onclick="setAsCategoryThumb({$i->id}, {$category_id});"><img src="{$PANTHERA_URL}/images/admin/tango-icon-theme/Image-x-generic.svg" class="galleryIcon" title="{"Set as thumbnail"|localize:gallery}"></a>
            </div>
        </div>
    </div>

    {/foreach}
            <div class="galleryItem" style="height: 100px; width: 100px; position: relative; border-radius: 2px;" id="addNewImage" ondragover="return false;">
                <div class="paGalleryFrameOverlay" style="display: block; border-radius: 2px; opacity: 0.4; -moz-opacity: 0.4; -khtml-opacity: 0.4;">
                    <a href="#" onclick="navigateTo('?display=gallery&action=add_item&ctgid={$category_id}');"><span class="tooltip">{"Drag and drop files to this area to start uploading"|localize:gallery}</span><img src="{$PANTHERA_URL}/images/admin/cross_icon.png" style="position: absolute; top: 30px; left: 30px; opacity: 0.8;"></a>
                </div>
            </div>
            
            <!--<div class="galleryItem" style="height: 100px; width: 100px; position: relative; border-radius: 2px; margin-right: 100px; margin-bottom: 160px;" id="removeImage" ondragover="return false;">
                <div class="paGalleryFrameOverlay" style="display: block; border-radius: 2px; opacity: 0.4; -moz-opacity: 0.4; -khtml-opacity: 0.4;">
                    <a href="#"><span class="tooltip">{"To remove files, drag and drop them here"|localize:gallery}</span><img src="{$PANTHERA_URL}/images/admin/tango-icon-theme/120px-Icon-trash.png" style="width: 70px; position: absolute; top: 17px; left: 15px; opacity: 0.8;"></a>
                </div>
            </div>
            
            <div class="galleryItem" style="height: 200px; width: 300px; position: relative; border-radius: 2px;" id="moveGalleryItem" ondragover="return false;">
                <div style="margin: 5px; text-align: center;"><small><b>{"Move to other category"|localize:gallery}</b></small></div>
                
                <div style="overflow-y: auto; overflow-x: hidden; height: 165px;">
                <table class="gridTable" style="margin-left: -1px; margin-top: 15px; width: 302px;">
                {foreach from=$category_list key=k item=i }
                    <tr>
                        <td style="height: 25px;">{$i->title}</td>
                    </tr>
                {/foreach}
                </table>
                </div>
            </div>-->
</div>
