{"files"|localizeDomain}
{if $action == "display_list"}
<script type="text/javascript">
initUploadBox();
</script>
{foreach from=$files key=k item=i}
            <div class="uploadBox" id="box_{$k}" rel="{$k}">
              <div class="boxInner">
                <div class="boxImg"><img src="{$i.icon}" id="box_img_{$k}"></div>
                <div class="titleBox" id="box_title_{$k}">{$i.name}</div>
                <input type="hidden" id="box_delete_{$k}" value="{if $i.ableToDelete == True}1{else}0{/if}">
                <input type="hidden" id="box_description_{$k}" value="{$i.description}">
                <input type="hidden" id="box_id_{$k}" value="{$i.id}">
                <input type="hidden" id="box_author_{$k}" value="{$i.author}">
                <input type="hidden" id="box_mime_{$k}" value="{$i.mime}">
                <input type="hidden" id="box_type_{$k}" value="{$i.type}">
                <input type="hidden" id="box_link_{$k}" value="{$i.link}">
                <input type="hidden" id="box_directory_{$k}" value="{$i.directory}">
              </div>
            </div>
        {/foreach}
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
        $('#file_link').val($('#box_link_' +id).val());
        $('#file_directory').html($('#box_directory_' +id).val());
        $('#file_type').html($('#box_type_' +id).val());
        $('#file_id').val($('#box_id_' +id).val());
        $('#file_k').val(id);
        $('#file_informations_window').show();

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
                panthera.jsonPOST({ url: '?display=upload&action=handle_file&popup=true', spinner: uploadProgress, data: { 'image': this.result, 'fileName': fileName.name}});
                
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
            panthera.jsonPOST({ url: '?display=upload&action=handle_file&popup=true', isUploading: true, spinner: uploadProgress, data: { 'image': content, 'fileName': fileName}});
            
            // finished
            if (fileNum == fileCount)
                getUploadsPage('page=0');
        }
    });
    
    


    $('#file_delete').click(function () {
        id = $('#file_id').val();
        k = $('#file_k').val();
        
        panthera.jsonPOST({ url: '{$AJAX_URL}?display=upload&popup=true&action=delete', data: 'id='+id, spinner: uploadProgress, success:
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


    $('#_upl_add_file').click(function () {
        $('#upload_list_window').slideUp();
        $('#upload_box_window').slideDown();
        
        jQuery.event.props.push('dataTransfer');
    });

    $('#_upl_back_btn').click(function () {
        $('#upload_list_window').slideDown();
        $('#upload_box_window').slideUp();
    });

    // AJAX UPLOAD FORM
    $('#upload_form').submit(function () {
        panthera.jsonPOST({ data: '#upload_form', async: true,
            before: function () {
                $('#upload_box_window').css({ opacity: 1 });
            
            }, progress: function (precent, start, end) {
                $('#upload_box_window').css({ opacity: (1-(precent/100)) });
            },
     
            success: function(response) {
                if (response.status == "success")
                {
                    $('#upload_error').hide();
                    $('#upload_box_window').slideUp(function() { $('#upload_box_window').css({ opacity: 1 }); }); // restore opacity after hiding element
                    $('#upload_list_window').slideDown();
                    getUploadsPage('page=0');
                } else {
                    $('#upload_box_window').css({ opacity: 1 });
                    $('#upload_error').html(response.message);
                    $('#upload_error').slideDown();
                }
            } 
        });
        
        return false;
      });
    });

function getUploadsPage(data)
{
    panthera.htmlPOST({ url: '{$AJAX_URL}?display=upload&popup=true&action=display_list', data: data, spinner: uploadProgress, 'success': '#upload_list'});
}
</script>

<style>
    .wrap {
        float: left;
        overflow: hidden;
        margin: 10px;
        margin-right: 0px;

        -moz-box-shadow: 0 0 3px rgba(0, 0, 0, 0.5);
        -ms-box-shadow: 0 0 3px rgba(0, 0, 0, 0.5);
        -o-box-shadow: 0 0 3px rgba(0, 0, 0, 0.5);
        -webkit-box-shadow: 0 0 3px rgba(0, 0, 0, 0.5);
        border-collapse: collapse;
        border: solid rgba(0, 0, 0, 0.5) 1px;
        background: rgba(214, 214, 214, 0.5);

        margin-left: 20px;
        margin-bottom: 25px;
        width: 70%;
        height: 350px;
    }

    .optionsBox {
        padding: 5px;
        padding-top: 10px;
        padding-bottom: 0px;
        padding-right: 10px;
        margin: 20px;
        margin-top: 0px;
        margin-right: 10px;
        height: 40px;

        -moz-box-shadow: 0 0 3px rgba(0, 0, 0, 0.5);
        -ms-box-shadow: 0 0 3px rgba(0, 0, 0, 0.5);
        -o-box-shadow: 0 0 3px rgba(0, 0, 0, 0.5);
        -webkit-box-shadow: 0 0 3px rgba(0, 0, 0, 0.5);
        border-collapse: collapse;
        border: solid rgba(0, 0, 0, 0.5) 1px;
        background: rgba(190, 190, 190, 0.5);
    }

    .uploadToolbox {
        float: right;
        margin: 10px;
        -moz-box-shadow: 0 0 3px rgba(0, 0, 0, 0.5);
        -ms-box-shadow: 0 0 3px rgba(0, 0, 0, 0.5);
        -o-box-shadow: 0 0 3px rgba(0, 0, 0, 0.5);
        -webkit-box-shadow: 0 0 3px rgba(0, 0, 0, 0.5);
        border-collapse: collapse;
        border: solid rgba(0, 0, 0, 0.5) 1px;
        background: rgba(214, 214, 214, 0.5);

        width: 25%;
        height: 350px;
    }

    .uploadToolBoxInner {
        padding-left: 20px;
        padding-top: 10px;
    }

    .uploadBox {
       float: left;
       position: relative;
       width: 140px;
       padding-bottom: 140px;
    }

    .boxInner {
       position: absolute;
       left: 10px;
       right: 10px;
       top: 10px;
       bottom: 10px;
       overflow: hidden;
       border: solid rgba(0, 0, 0, 0.5) 1px;
       background: rgba(173, 216, 253, 0.78);

        -moz-box-shadow: 0 0 3px rgba(0, 0, 0, 0.5);
        -ms-box-shadow: 0 0 3px rgba(0, 0, 0, 0.5);
        -o-box-shadow: 0 0 3px rgba(0, 0, 0, 0.5);
        -webkit-box-shadow: 0 0 3px rgba(0, 0, 0, 0.5);

    }
    .boxInner img {
       width-min: 60px;
       height-min: 60px;
       width-max: 100px;
       height-max: 100px;
       cursor: pointer;
    }

    .boxImg {
       vertical-align: middle;
       align: top;
       width-max: 100px;
       height-max: 100px;
    }

    .boxInner .titleBox {
       position: absolute;
       bottom: 0;
       left: 0;
       right: 0;
       /*margin-bottom: -50px;*/
       background: #000;
       background: rgba(0, 0, 0, 0.5);
       color: #FFF;
       padding: 6px;
       text-align: center;
       -webkit-transition: all 0.3s ease-out;
       -moz-transition: all 0.3s ease-out;
       -o-transition: all 0.3s ease-out;
       transition: all 0.3s ease-out;
    }
    body.no-touch .boxInner:hover .titleBox, body.touch .boxInner.touchFocus .titleBox {
       margin-bottom: 0;
    }
</style>
    <h2 class="popupHeading">{"Files upload"|localize} - {"Add files from your computer"|localize}</h2>

    <div class="msgError" id="uploadBox_failed"></div>
    <div class="msgSuccess" id="uploadBox_success"></div>

    <div id="upload_list_window" ondragover="return false;">
        <div class="wrap" id="upload_list">
        {foreach from=$files key=k item=i}
            <div class="uploadBox" id="box_{$k}" rel="{$k}">
              <div class="boxInner">
                <div class="boxImg"><img src="{$i.icon}" id="box_img_{$k}"></div>
                <div class="titleBox" id="box_title_{$k}">{$i.name}</div>
                <input type="hidden" id="box_delete_{$k}" value="{if $i.ableToDelete == True}1{else}0{/if}">
                <input type="hidden" id="box_description_{$k}" value="{$i.description}">
                <input type="hidden" id="box_id_{$k}" value="{$i.id}">
                <input type="hidden" id="box_author_{$k}" value="{$i.author}">
                <input type="hidden" id="box_mime_{$k}" value="{$i.mime}">
                <input type="hidden" id="box_type_{$k}" value="{$i.type}">
                <input type="hidden" id="box_link_{$k}" value="{$i.link}">
                <input type="hidden" id="box_directory_{$k}" value="{$i.directory}">
              </div>
            </div>
        {/foreach}
        
      </div>

      <div class="uploadToolbox" id="file_informations_window" style="display: none;">
        <div class="uploadToolBoxInner">
            <h3 id="file_name">test.odf</h3>
            <b>{"Author"|localize}:</b> <i id="file_author">admin</i><br>
            <b>{"Type"|localize}:</b> <i id="file_type">document</i><br>
            <b>{"Mime-type"|localize}:</b> <i id="file_mime">document/odf</i><br>
            <b>{"Directory"|localize}:</b> <i id="file_directory">default</i><br>
            <b>{"Description"|localize}:</b> <i id="file_description">This is just a test. File was added from phpMyAdmin but displayed here.</i><br><br>
            <b>{"Link"|localize}:</b> <input type="text" id="file_link" value=""><br>
            <input type="hidden" id="file_id"><input type="hidden" id="file_k">
            <b>{"Options"|localize}:</b> <input type="button" value="{"Select"|localize}" onclick="selectFile();"> <input type="button" value="{"Remove"|localize}" id="file_delete" disabled>
        </div>
      </div>

      <div style="clear:both;"></div>

      <div class="optionsBox">&lsaquo;
                        {foreach from=$pager key=page item=active}
                            {if $active == true}
                            <a href="#" onclick="getUploadsPage('page={$page}'); return false;"><b>{$page+1}</b></a>
                            {else}
                            <a href="#" onclick="getUploadsPage('page={$page}'); return false;">{$page+1}</a>
                            {/if}
                        {/foreach}&rsaquo; {if $upload_files == True}<input type="button" value="{"Add new file"|localize}" style="float: right;" id="_upl_add_file">{/if}</div>
  </div>

  <!-- UPLOAD BOX -->
  <div id="upload_box_window" style="display: none;">
        <div class="msgError" id="uploadSingleFile_failed"></div>
        <div class="msgSuccess" id="uploadSingleFile_success"></div>

        <form action="?display=upload&action=handle_file&popup=true" method="POST" enctype="multipart/form-data" id="upload_form">
            <table class="gridTable">
                <thead>
                    <tr><th colspan="5">{"Upload a new file"|localize}</th></tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{"Select a file"|localize}</td><td><input type="file" name="input_file"> <input type="hidden" name="MAX_FILE_SIZE" value="{$max_file_size}" /></td>
                    </tr>

                    <tr>
                        <td>{"Description"|localize}</td><td><input type="text" name="input_description" style="width: 95%;"></td>
                    </tr>
                </tbody>

                <tfoot>
                    <tr>
                        <td colspan="5" class="rounded-foot-left"><em>Panthera - upload <input type="submit" value="{"Send"|localize}" style="float: right;"> <input type="button" id="_upl_back_btn" value="{"Back"|localize}" style="float: right;"></em></td>
                    </tr>
                </tfoot>
            </table>
        </form>

        <br><br><br>
  </div>

  <!-- END OF UPLOAD BOX -->
    </div>
{/if}
