<script type="text/javascript">
var uploadProgress = new panthera.ajaxLoader($('#addNewImage'));

    $(document).ready(function () {
        var multiuploadFiles = new Array();

        panthera.multiuploadArea({ id: '#addNewImage', start: function () {
            uploadProgress.ajaxLoaderInit();

        }, callback: function (content, fileName, fileNum, fileCount) {
                panthera.jsonPOST({ url: '?display=avatars&cat=admin&action=uploadAvatar', isUploading: true, async: false, data: { 'image': content, 'fileName': fileName}, success: function (response) {
                        if (response.status == "success")
                        {
                            alert('done');
                        }

                    }
                });
            }
        });
    });
    
</script>

<style type="text/css">
.uploadTable thead tr th {
    font-size: 11px;
}
.uploadTable tbody tr {
    color: black;
}
</style>


<div id="content">
    <div class="uploadBoxCentered" style="min-height: 0px; width: 150px; margin-top: -30px;">
        <div class="addBox" id="addNewImage" ondragover="return false;">
            <a href="#" onclick="navigateTo('?display=gallery&cat=admin&action=addItem&categoryid={$category_id}');"><img src="{$PANTHERA_URL}/images/admin/cross_icon.png" style="position: relative; top: 30px; opacity: 0.8;" title="{function="localize('Drag and drop files to this area to start uploading', 'gallery')"}"></a>
        </div>
    </div>
    
    <div class="uploadBoxCentered">
     {if="count($avatars) < 1"}
        <p style="color: white; text-align: center;">{function="localize('There are no available avatars for you', 'upload')"}.</p>
     
     {else}
     
      <div style="text-align: center;">
        
        {loop="$avatars"}
                <div class="uploadBox" id="avatar_{$value->id}" rel="" style="background-color: #404C5A;" onclick="selectFile({$value->id});">
                  <div class="boxInner" style="position: relative;">
                        <div class="boxImg"><img src="{$value->link}" id="item_img_{$value->id}" style="width: 100%;"></div>
                  </div>
                </div>
        {/loop}
        
      </div>
      
     {/if}
     
    </div>
    <input type="button" value="{function="localize('Close')"}" style="float: right;" onclick="panthera.popup.close();">
   </div>
 </div>
