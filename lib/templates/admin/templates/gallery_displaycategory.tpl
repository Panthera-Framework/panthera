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
                panthera.jsonPOST({ url: '?display=upload&action=handle_file&popup=true', data: { 'image': content, 'fileName': fileName}, success: function (response) {
                        if (response.status == "success")
                        {
                            multiuploadFiles.push(response.upload_id);
                        }

                    }
                });

                // finished
                if (fileNum == fileCount)
                {
                    panthera.jsonPOST({ url: '?display=gallery&action=adduploads&gid={$category_id}', data: { 'ids': JSON.stringify(multiuploadFiles) }});
                    uploadProgress.stop();
                    navigateTo(window.location);
                }
            }
        });

    });
</script>

<div class="titlebar">{"Gallery"|localize:messages}: &nbsp;{$category_title}{include file="_navigation_panel.tpl"}</div>
<div class="grid-1">

    {foreach from=$item_list key=k item=i}
    <div class="galleryItem{if $i->visibility eq 1} galleryItemHidden{/if}" id="gallery_item_{$i->id}">
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
                <a href="#rights" onclick="createPopup('_ajax.php?display=acl&popup=true&name=gallery_manage_img_{$i->id}', 1024, 'upload_popup');"><img src="{$PANTHERA_URL}/images/admin/menu/users.png" class="galleryIcon" title="{"Manage permissions"|localize:messages}"></a>
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
</div>

