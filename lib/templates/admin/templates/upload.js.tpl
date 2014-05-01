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

$(document).ready(function(){
    
    $('#upload_list_window').bind('dragenter', function() {
        $(this).css( { 'box-shadow' : '10px 10px 5px red;' });
    });
        
    $('#upload_list_window').bind('drop', function(e) {
        var files = e.dataTransfer.files;
        
        $.each(files, function(index, file) {
            var fileReader = new FileReader();
            var fileName = file;
            
            fileReader.onload = (function(file) {
                // upload a single file
                panthera.jsonPOST({ url: '?display=upload&cat=admin&action=popupHandleFile', data: { 'image': this.result, 'fileName': fileName.name}});
                
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
            panthera.jsonPOST({ url: '?display=upload&cat=admin&action=popupHandleFile', isUploading: true, data: { 'image': content, 'fileName': fileName}});
            
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
    panthera.htmlPOST({ url: '?display=upload&cat=admin&popup=true&action=displayList', data: data, 'success': '#upload_list'});
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
                panthera.jsonGET( { url: '{$AJAX_URL}?display=upload&cat=admin&action=popupDelete&id='+ids, messageBox: 'w2ui', success: function (response) {
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