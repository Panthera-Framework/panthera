<script type="text/javascript">
jQuery.event.props.push('dataTransfer');

var selected = new Array;

function callBack()
{
    callback = eval("{$callback_name}");

    if (typeof callback == 'function')
    {
        // callback ( link, mime, type, directory, id, description, author )
        callback($('#file_link').attr("href"), $('#file_mime').html(), $('#file_type').html(), $('#file_directory').html(), $('#file_id').val(), $('#file_description').html(), $('#file_author').html());
    }
}

function initUploadBox() {
    $('.uploadBox').click(function () {
        item = jQuery('#'+this.id);
        id = item.attr('rel');
        
        $('#file_description').html(jQuery('#box_description_' +id).val());
        $('#file_name').html($('#box_title_' +id).html());
        $('#file_author').html($('#box_author_' +id).val());
        $('#file_mime').html($('#box_mime_' +id).val());
        $('#file_link').attr('href', $('#box_link_' +id).val());
        $('#file_directory').html($('#box_directory_' +id).val());
        $('#file_type').html($('#box_type_' +id).val());
        $('#file_id').val($('#box_id_' +id).val());
        $('#file_k').val(id);
        $('#file_informations_window').show();
        $('#_upl_select_file').show();
        $('#file_delete').show();

        if ($('#box_delete_'+id).val() == "1")
            $('#file_delete').removeAttr('disabled');
        else
            $('#file_delete').attr('disabled', 'disabled');

    });
}

var uploadProgress = new panthera.ajaxLoader($('#upload_list_window'));

$(document).ready(function(){
    initUploadBox();
    
    $('#upload_list_window').bind('dragenter', function() {
        $(this).css( { 'box-shadow' : '10px 10px 5px red;' });
    });
        
    $('#upload_list_window').bind('drop', function(e) {
        var files = e.dataTransfer.files;
        
        uploadProgress = new panthera.ajaxLoader($('#upload_list_window'));
        
        $.each(files, function(index, file) {
            var fileReader = new FileReader();
            var fileName = file;
            
            fileReader.onload = (function(file) {
                // upload a single file
                panthera.jsonPOST({ url: '?display=upload&cat=admin&action=handle_file&popup=true', spinner: uploadProgress, data: { 'image': this.result, 'fileName': fileName.name}});
                
            });
            
            fileReader.readAsDataURL(file);
            
            if (panthera.logging )
            
            // finished
            if (index == (files.length-1))
                getUploadsPage('page=0');
        });
        
        
        
        return false;
    });
    
    /**
      * Upload multiple files
      *
      * @author Damian Kęska
      */
    
    panthera.multiuploadArea({ id: '#upload_list_window', callback: function (content, fileName, fileNum, fileCount) {
            panthera.jsonPOST({ url: '?display=upload&cat=admin&action=handle_file&popup=true', isUploading: true, spinner: uploadProgress, data: { 'image': content, 'fileName': fileName}});
            
            // finished
            if (fileNum == fileCount)
                getUploadsPage('page=0');
        }
    });
});

function getUploadsPage(data)
{
    panthera.htmlPOST({ url: '?display=upload&cat=admin&popup=true&action=display_list', data: data, spinner: uploadProgress, 'success': '#upload_list'});
}

/**
  * Delete selected files
  *
  * @author Mateusz Warzyński
  */
 
function deleteSelectedFiles()
{
    var ids = transformArrayToString(selected); 
    
    w2confirm('{function="localize('Are you sure you want to delete those files?', 'upload')"}', function (responseText) {
        
            if (responseText == 'Yes')
            {
                panthera.jsonGET( { url: '{$AJAX_URL}?display=upload&cat=admin&action=delete&id='+ids+'&popup=true', messageBox: 'w2ui', success: function (response) {
                        if (response.status == 'success')
                        {
                            panthera.popup.toggle('?display=upload&cat=admin&popup=True;');
                        }
                
                    }
                });
            }
        
    });
}

/**
  * Select file (change background-color or opacity and add id to global array 'selected')
  *
  * @author Mateusz Warzyński
  */

function selectFile(id)
{
    $("#file_delete").show();
    
   {if="$view_type == 'images'"}
    
    var opacity = $("#file_"+id).css("opacity");
    
    if (opacity == "1") {
        $("#file_"+id).css("opacity", "0.5");
        selected.push(id)
    } else {
        $("#file_"+id).css("opacity", "1");
        removeFromArrayByValue(selected, id);
    }
   
   {else}
   
    var color = $("#file_"+id).css("background-color");
   
    if (color == "rgb(255, 255, 255)") {
        $("#file_"+id).css("background-color", "rgba(86, 104, 123, 0.70)");
        selected.push(id)
    } else {
        $("#file_"+id).css("background-color", "#ffffff");
        removeFromArrayByValue(selected, id);
    }
    
   {/if}
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
</script>

<style type="text/css">
table tbody tr td {
    font-size: 11px;
}
</style>


<div id="header" style="display: block; text-align: center; color: white;">
    <div style="position: absolute; top: 10px; right: 20px; margin-top: 0;">
        <input type="button" value="{function="localize('Change view', 'upload')"}" onclick="panthera.popup.create('?display=upload&cat=admin&changeView={$view_change}&popup=true')" />
    </div>
    <p style="font-size: 22px;">{function="localize('Category', 'upload')"}:&nbsp;{$directory}</p>
</div>

<div id="content">
   {if="$view_type == 'images'"}
    <div class="uploadBoxCentered">
    
    {$i=0}
    {loop="$files"}
    {$i=$i+1}
            <div class="uploadBox" id="file_{$value.id}" rel="{$key}" style="background-color: #404C5A;" onclick="selectFile({$value.id});">
              <div class="boxInner" style="position: relative;">
                    <div class="boxImg"><img src="{$value.icon}" id="box_img_{$key}"></div>
                    <div class="titleBox" id="box_title_{$key}">{$value.name}</div>
                    <input type="hidden" id="box_delete_{$key}" value="{if="$value.ableToDelete == True"}1{else}0{/if}">
                    <input type="hidden" id="box_description_{$key}" value="{$value.description}">
                    <input type="hidden" id="box_id_{$key}" value="{$value.id}">
                    <input type="hidden" id="box_author_{$key}" value="{$value.author}">
                    <input type="hidden" id="box_mime_{$key}" value="{$value.mime}">
                    <input type="hidden" id="box_type_{$key}" value="{$value.type}">
                    <input type="hidden" id="box_link_{$key}" value="{$value.link}">
                    <input type="hidden" id="box_directory_{$key}" value="{$value.directory}">
              </div>
            </div>
    {/loop}
    </div>
   
   {else}
    <div style="text-align: center;">
     <table style="margin-top: 5px; margin-bottom: 12px; display: inline-table;">
        <thead>
            <th>{function="localize('Icon', 'upload')"}</th>
            <th>{function="localize('Name', 'upload')"}</th>
            <th>{function="localize('Description', 'upload')"}</th>
            <th>{function="localize('Mime type', 'upload')"}</th>
            <th>{function="localize('Author', 'upload')"}</th>
        </thead>
       
        <tbody>
        
        {$i=0}
        {loop="$files"}
        {$i=$i+1}
          <tr id="file_{$value.id}" onclick="selectFile({$value.id});" style="background-color: #ffffff">
            
            <td style="padding-top: 4px; padding-right: 6px; padding-left: 6px;">
                <img src="{$value.icon}" style="max-height: 30px; max-width: 30px;">
            </td>
            
            <td>{$value.name}</td>
            <td>{$value.description}</td>
            <td>{$value.mime}</td>
            <td>{$value.author}</td>
          </tr>
        {/loop}            
        
        </tbody>
    </table>
   </div>
  {/if}
  
  <div style="width: 65%; margin: 0 auto; padding-bottom: 10px;">
    <div style="display: inline-block; font-size: 12px; color: white;">{$uiPagerName="adminUpload"}{include="ui.pager"}</div>
    
    <input type="button" value="{function="localize('Close')"}" style="float: right;" onclick="panthera.popup.close();">
    
    {if="$permissions.admin"}
    {if="$seeOtherUsersUploads"}
    <input type="button" value="{function="localize('Hide other users files', 'files')"}" onclick="panthera.popup.create('?display=upload&cat=admin&otherUsers=false&popup=true')">
    {else}
    <input type="button" value="{function="localize('Show other users files', 'files')"}" onclick="panthera.popup.create('?display=upload&cat=admin&otherUsers=true&popup=true')">
    {/if}
    {/if}
    
    {if="$upload_files == True"}<input type='button' value="{function="localize('Add new file', 'files')"}" style="margin-right: 5px; float: right;" onclick="panthera.popup.toggle('?display=upload&cat=admin&action=uploadFileWindow&popup=True')">{/if}
    <input type="button" value="{function="localize('Select this file', 'files')"}" style="float: right; margin-right: 5px; display: none;" onclick="callBack();" id="_upl_select_file">
    <input type="button" value="{function="localize('Delete selected files', 'files')"}" style="float: right; margin-right: 5px; display: none;" id="file_delete" onclick="deleteSelectedFiles();">
  </div>
 </div>
