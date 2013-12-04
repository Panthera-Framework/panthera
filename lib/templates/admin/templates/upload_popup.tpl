<script type="text/javascript">
jQuery.event.props.push('dataTransfer');

var selected = new Array;

function callBack()
{
    callback = eval("{$callback_name}");

    if (typeof callback == 'function' && $("#file_link").val() != '')
    {
        // callback ( link, mime, type, directory, id, description, author )
        callback($('#file_link').val(), $('#file_mime').val(), $('#file_type').val(), $('#file_directory').val(), $('#file_id').val(), $('#file_description').val(), $('#file_author').val(), $('#file_name').val());
    } else {
        w2alert("{function="localize('There is no selected file', 'upload')"}!");
    }
}

var uploadProgress = new panthera.ajaxLoader($('#upload_list_window'));

$(document).ready(function(){
    
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

function changeCategory()
{
    var category = $("#upload_category").val();
    panthera.popup.toggle('?display=upload&cat=admin&directory='+category+'&popup=True');
}

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
                            panthera.popup.toggle('?display=upload&cat=admin&directory={$setCategory}&popup=True;');
                        }
                
                    }
                });
            }
        
    });
}

/**
  * Select file
  *     if callback, set only one file which you may callback to ajaxpage
  *     if !callback, you are able to select more than one file to remove
  *
  * @author Mateusz Warzyński
  */

function selectFile(id)
{
    {if="$callback"}
    
        $('#file_title').attr('value', $('#item_title_' +id).val());
        $('#file_description').attr('value', $('#item_description_' +id).val());
        $('#file_name').attr('value', $('#item_title_' +id).val());
        $('#file_author').attr('value', $('#item_author_' +id).val());
        $('#file_mime').attr('value', $('#item_mime_' +id).val());
        $('#file_link').attr('value', $('#item_link_' +id).val());
        $('#file_directory').attr('value', $('#item_directory_' +id).val());
        $('#file_type').attr('value', $('#item_type_' +id).val());
        $('#file_id').attr('value', $('#item_id_' +id).val());
    
        if (selected[0] != undefined)
            var old_id = selected[0];
        else
            var old_id = -1;
            
        selected = new Array;
        selected.push(id);
    
       {if="$view_type == 'images'"}
        
        $("#file_"+id).css("opacity", "0.5");
        $("#file_"+old_id).css("opacity", "1");
       
       {else}
       
        $("#file_"+id).css("background-color", "rgba(86, 104, 123, 0.70)");
        $("#file_"+old_id).css("background-color", "#ffffff");
       
       {/if} 
        
    {else}
        $("#file_delete").slideDown();
        
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
       
       // 3d4957
       
        if (color != "rgb(61, 73, 87)") {
            $("#file_"+id).css("background-color", "#3d4957");
            $("#file_"+id).css("color", "white");
            $("#file_"+id).attr("first-color", color);
            selected.push(id)
        } else {
            var first_color = $("#file_"+id).attr("first-color");
            $("#file_"+id).css("color", "black");
            $("#file_"+id).css("background-color", first_color);
            removeFromArrayByValue(selected, id);
        }
       {/if}
       
       if (selected.length == 0)
            $("#file_delete").slideUp();
            
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
.uploadTable thead tr th {
    font-size: 11px;
}
.uploadTable tbody tr {
    color: black;
}
</style>


<div id="header" style="display: block; width: 65%; margin: 0 auto; height: 35px; text-align: center; color: white;">
    <div style="position: relative; float: right; margin-top: 14px;">
        {if="$permissions.admin"}
            {if="$seeOtherUsersUploads"}
            <input type="button" value="{function="localize('Hide other users files', 'files')"}" onclick="panthera.popup.create('?display=upload&cat=admin&otherUsers=false&popup=true')">
            {else}
            <input type="button" value="{function="localize('Show other users files', 'files')"}" onclick="panthera.popup.create('?display=upload&cat=admin&otherUsers=true&popup=true')">
            {/if}
        {/if}
    </div>
    <p style="font-size: 12px; float: left; margin-top: 20px;"><b>{function="localize('Category', 'upload')"}:</b>&nbsp;
        {$directory}
        
        <div class="select" style="margin-top: 14px; margin-left: 3px;">
         <select onChange="changeCategory();" id="upload_category">
           {loop="$categories"}
            <option {if="$setCategory == $value.name"} selected {/if}>{$value.name}</option>
           {/loop}
         </select>
        </div>
    </p>
</div>

<div id="content">
   {if="$view_type == 'images'"}
    <div class="uploadBoxCentered">
    
     {if="count($files) < 1"}
        <p style="color: white; text-align: center;">{function="localize('There are no uploaded files', 'upload')"}.</p>
     {else}
      <div style="text-align: center;">
        {$i=0}
        {loop="$files"}
        {$i=$i+1}
                <div class="uploadBox" id="file_{$value.id}" rel="{$key}" style="background-color: #404C5A;" onclick="selectFile({$value.id});">
                  <div class="boxInner" style="position: relative;">
                        <div class="boxImg"><img src="{$value.icon}" id="item_img_{$key}" {if="$value.type == 'image'"} style="width: 100%;" {/if}></div>
                        <div class="titleBox"><b>{$value.name}</b></div>
                        
                        <input type="hidden" id="item_title_{$value.id}" value="{$value.name}">
                        <input type="hidden" id="item_delete_{$value.id}" value="{if="$value.ableToDelete == True"}1{else}0{/if}">
                        <input type="hidden" id="item_description_{$value.id}" value="{$value.description}">
                        <input type="hidden" id="item_id_{$value.id}" value="{$value.id}">
                        <input type="hidden" id="item_author_{$value.id}" value="{$value.author}">
                        <input type="hidden" id="item_mime_{$value.id}" value="{$value.mime}">
                        <input type="hidden" id="item_type_{$value.id}" value="{$value.type}">
                        <input type="hidden" id="item_link_{$value.id}" value="{$value.link}">
                        <input type="hidden" id="item_directory_{$value.id}" value="{$value.directory}">
                  </div>
                </div>
        {/loop}
      </div>
     {/if}
    </div>
   
   {else}

    <div style="text-align: center; margin-top: 20px;">
     <table style="margin-top: 5px; margin-bottom: 30px; display: inline-table; width: 65.3%; margin-left: 29px;" class="uploadTable">
        <thead>
           <tr style="border: 1px solid #4d565c; border-bottom: 0;">
            <th>{function="localize('Icon', 'upload')"}</th>
            <th>{function="localize('Name', 'upload')"}</th>
            <th>{function="localize('Description', 'upload')"}</th>
            <th>{function="localize('Mime type', 'upload')"}</th>
            <th>{function="localize('Author', 'upload')"}</th>
           </tr>
        </thead>
       
        <tbody>
        
        {if="count($files) < 1"}
        
            <tr>
                
                <td colspan="5">{function="localize('There are no uploaded files', 'upload')"}.</td>
            </tr>
        
        {else}
        
            {loop="$files"}
              <tr id="file_{$value.id}" onclick="selectFile({$value.id});">
                
                <td style="padding-top: 4px; padding-right: 6px; padding-left: 6px; width: 30px;">
                    <img src="{$value.icon}" style="max-height: 30px; max-width: 30px;">
                </td>
                
                <td style="width: 200px;">{$value.name}</td>
                <td style="width: 200px;">{$value.description}</td>
                <td style="width: 80px;">{$value.mime}</td>
                <td>{$value.author}</td>
                
                <input type="hidden" id="item_title_{$value.id}" value="{$value.name}">
                <input type="hidden" id="item_delete_{$value.id}" value="{if="$value.ableToDelete == True"}1{else}0{/if}">
                <input type="hidden" id="item_description_{$value.id}" value="{$value.description}">
                <input type="hidden" id="item_id_{$value.id}" value="{$value.id}">
                <input type="hidden" id="item_author_{$value.id}" value="{$value.author}">
                <input type="hidden" id="item_mime_{$value.id}" value="{$value.mime}">
                <input type="hidden" id="item_type_{$value.id}" value="{$value.type}">
                <input type="hidden" id="item_link_{$value.id}" value="{$value.link}">
                <input type="hidden" id="item_directory_{$value.id}" value="{$value.directory}">
                
              </tr>
            {/loop}
        {/if}            
        
        </tbody>
    </table>
   </div>
  {/if}
  
  
  <div style="width: 65%; margin: 0 auto; padding-bottom: 10px;">
    <div style="text-align: center; font-size: 12px; color: white; margin-bottom: -20px;">{$uiPagerName="adminUpload"}{include="ui.pager"}</div>
    <input type="button" value="{function="localize('Close')"}" style="float: right;" onclick="panthera.popup.close();">
    <input type="button" value="{function="localize('Change view', 'upload')"}" style="float: right;" onclick="panthera.popup.create('?display=upload&cat=admin&changeView={$view_change}&directory={$setCategory}&popup=true')">

    <input type="text" id="file_name" style="display: none;">
    <input type="text" id="file_description" style="display: none;">
    <input type="text" id="file_author" style="display: none;">
    <input type="text" id="file_type" style="display: none;">
    <input type="text" id="file_mime" style="display: none;">
    <input type="text" id="file_link" style="display: none;">
    <input type="text" id="file_directory" style="display: none;">
    <input type="text" id="file_id" style="display: none;">
    <input type="text" id="file_k" style="display: none;">
    
    {if="$callback"}
        <input type="button" value="{function="localize('Select this file', 'files')"}" style="float: left; margin-left: 5px;" onclick="callBack();" id="_upl_select_file">
    {else}
      {if="$upload_files == True"}
        <input type='button' value="{function="localize('Add new file', 'files')"}" style="margin-left: 5px; float: left;" onclick="panthera.popup.toggle('?display=upload&cat=admin&action=uploadFileWindow&directory={$setCategory}&popup=True')">
      {/if}
        <input type="button" value="{function="localize('Delete selected files', 'files')"}" style="float: left; margin-left: 5px; display: none;" id="file_delete" onclick="deleteSelectedFiles();">
    {/if}
  </div>
 </div>
