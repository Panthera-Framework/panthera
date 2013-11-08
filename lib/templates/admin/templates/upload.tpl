{if="$action == 'display_list'"}
<script type="text/javascript">
initUploadBox();
</script>
{loop="$files"}
            <div class="uploadBox" id="box_{$key}" rel="{$key}">
              <div class="boxInner">
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
{else}
<script type="text/javascript">
jQuery.event.props.push('dataTransfer');

function selectFile()
{
    callback = eval("{$callback_name}");

    if (typeof callback == 'function')
    {
        // callback ( link, mime, type, directory, id, description, author )
        callback($('#file_link').val(), $('#file_mime').html(), $('#file_type').html(), $('#file_directory').html(), $('#file_id').val(), $('#file_description').html(), $('#file_author').html());
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
        
   /* $('#upload_list_window').bind('drop', function(e) {
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
    });*/
    
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
    
    


    $('#file_delete').click(function () {
        id = $('#file_id').val();
        k = $('#file_k').val();
        
        panthera.jsonPOST({ url: '?display=upload&cat=admin&popup=true&action=delete', data: 'id='+id, spinner: uploadProgress, success:
            function (response) {
                  if (response.status == "success")
                  {
                      $('#box_'+k).remove();
                      $('#upload_error').hide();
                  } else {
                      $('#upload_error').html(response.message);
                      $('#upload_error').slideDown();
                  }
            }
        });
    });
    });

function getUploadsPage(data)
{
    panthera.htmlPOST({ url: '?display=upload&cat=admin&popup=true&action=display_list', data: data, spinner: uploadProgress, 'success': '#upload_list'});
}
</script>

  <div class="uploadBoxCentered">
    {$i=0}
    {loop="$files"}
    {$i=$i+1}
            <div class="uploadBox" id="box_{$key}" rel="{$key}" style="background-color: #404C5A;">
              <div class="boxInner" style="position: relative;">
                    <div class="boxImg"><img src="{$value.icon}" id="box_img_{$key}"></div>
                    <div class="titleBox" id="box_title_{$key}"><a href="{$value.link}" target="blank" style="color: white;">{$value.name}</a></div>
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
     
     <div style="position: absolute; bottom: 0; width: 99.9%; background: #343E4A; margin: 1px; height: 55px; color: white; font-size: 12px; display: none;" id="file_informations_window">
        <div style="padding-left: 10px; padding-top: 10px;">
            <input type="hidden" id="file_id"><input type="hidden" id="file_k">
            <b><span id="file_name">test.odf</span></b>, <b>{function="localize('Author')"}:</b> <i id="file_author">admin</i>, <b>{function="localize('Type')"}:</b> <i id="file_type">document</i> (<i id="file_mime">document/odf</i>), <b>{function="localize('Directory')"}:</b> <i id="file_directory">default</i><br><b>{function="localize('Link')"}:</b> <a id="file_link" style="color: white;" target="_blank">here</a>
            <br><i id="file_description"></i>
        </div>
     </div>
  </div>
  
  <div style="width: 65%; margin: 0 auto;">
    <div style="display: inline-block; font-size: 12px; color: white;">{$uiPagerName="adminUpload"}{include="ui.pager"}</div>
    
    <input type="button" value="{function="localize('Close')"}" style="float: right; margin-right: 5px;" onclick="panthera.popup.close();">
    
    {if="$permissions.admin"}
    {if="$seeOtherUsersUploads"}
    <input type="button" value="{function="localize('Hide other users files', 'files')"}" onclick="panthera.popup.create('?display=upload&cat=admin&otherUsers=false&popup=true')">
    {else}
    <input type="button" value="{function="localize('Show other users files', 'files')"}" onclick="panthera.popup.create('?display=upload&cat=admin&otherUsers=true&popup=true')">
    {/if}
    {/if}
    
    {if="$upload_files == True"}<input type='button' value="{function="localize('Add new file', 'files')"}" style="float: right;" onclick="panthera.popup.toggle('?display=upload&cat=admin&action=uploadFileWindow&popup=True')">{/if}
    <input type="button" value="{function="localize('Select this file', 'files')"}" style="float: right; margin-right: 5px; display: none;" onclick="selectFile();" id="_upl_select_file">
    <input type="button" value="{function="localize('Delete selected files', 'files')"}" style="float: right; margin-right: 5px; display: none;" id="file_delete">
  </div>
 </div>
{/if}
