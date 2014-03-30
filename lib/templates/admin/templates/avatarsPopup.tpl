<script type="text/javascript">
var selected = new Array;

var uploadProgress = new panthera.ajaxLoader($('#addNewAvatar'));

    $(document).ready(function () {
        var multiuploadFiles = new Array();

        panthera.multiuploadArea({ id: '#addNewAvatar', start: function () {
            uploadProgress.ajaxLoaderInit();

        }, callback: function (content, fileName, fileNum, fileCount) {
                panthera.jsonPOST({ url: '?display=avatars&cat=admin&action=uploadAvatar', isUploading: true, async: false, data: { 'image': content, 'fileName': fileName}, success: function (response) {
                        if (response.status == "success")
                        {
                            panthera.popup.reload(2)
                        } else {
                            w2alert(response.message);
                        }

                    }
                });
            }
        });
    });


function selectFile(id)
{
    if (selected.length == 0)
    {
        selected.push(id);
        $('#avatarImage'+id).css('opacity', '0.8');
        $('#avatarImage'+id).css('-webkit-filter', 'blur(5px)');
        
        $('#selectButton').css('display', 'block');
    } else {
        removeFromArrayByValue(selected, id);
        $('#avatarImage'+id).css('opacity', '1');
        $("#avatarImage"+id).css('-webkit-filter', 'none');
    }
    
    if (selected.length == 0)
    {
        $('#selectButton').css('display', 'none');
    }
}


/**
 * Remove value from array
 *
 * @author Mateusz Warzyński
 */
    
function removeFromArrayByValue(array, value)
{
    for(var i=0; i<array.length; i++) {
    
        if(array[i] == value) {
            array.splice(i, 1);
            break;
        }
    
    }
}

function callBack()
{
    id = selected[0];
    
    callback = eval("{$callback_name}");

    if (typeof callback == 'function' && $("#avatarLink"+id).val() != '')
        callback($('#avatarLink'+id).val(), id);
    else
        w2alert("{function="localize('There is no selected file', 'upload')"}!");
        
    panthera.popup.close('avatarPopup');
}

</script>

<style type="text/css">
.uploadTable thead tr th {
    font-size: 11px;
}
.uploadTable tbody tr {
    color: black;
}
</style>


<div id="content" style="margin-top: 40px;">
    
    <div class="uploadBoxCentered" style="min-height: 0px; width: 150px; margin-top: -15px;">
        <div id="addNewAvatar" ondragover="return false;" style="text-align: center; height: 100px;">
            <a href="#" ><img src="{$PANTHERA_URL}/images/admin/cross_icon.png" style="position: relative; top: 30px; opacity: 0.8;" title="{function="localize('Drag and drop avatar to this area to start uploading', 'avatars')"}"></a>
        </div>
    </div>
    
    <div class="uploadBoxCentered">
        
     {if="count($avatars) < 1"}
        
        <p style="color: white; text-align: center;">{function="localize('There are no available avatars for you', 'upload')"}.</p>
     
     {else}
     
      <div style="margin: 30px; margin-top: 20px;">
        
        {loop="$avatars"}
                <div id="avatar{$value->id}" rel="" style="background-color: #404C5A; display: inline-block; position: relative;" onclick="selectFile({$value->id});">
                  <div class="boxInner" style="position: relative;">
                        <div style="vertical-align: middle;">
                            <img src="{$value->getLink()}" id="avatarImage{$value->id}" style="width: {$dimensions[1]}; height: {$dimensions[2]}; -webkit-transition-duration: 300ms;">
                            
                            <input type="hidden" id="avatarLink{$value->id}" value="{$value->getLink()}">
                        </div>
                  </div>
                </div>
        {/loop}
        
      </div>
      
     {/if}
     
    </div>
    
    <div style="width: 65%; margin: 0 auto; padding-bottom: 10px;">
        <input type="button" value="{function="localize('Close')"}" style="float: right;" onclick="panthera.popup.close('avatarPopup');">
        <input type="button" value="{function="localize('Select avatar', 'avatar')"}" style="float: left; display: none;" id="selectButton" onclick="callBack();">
    </div>
    
    
   </div>
 </div>
