<script type="text/javascript">
$(document).ready(function () {
    panthera.multiuploadArea({ id: '#dragDropHere,#resultWindow', callback: function (content, fileName, fileNum, fileCount) {
            panthera.jsonPOST({ url: '?display=mergephps&action=upload', data: { 'file': content, 'fileName': fileName}, success: function (response) {
                    if (response.html)
                    {
                        $('#resultGrid').val(response.html);
                        regenerateFileList(response.files);
                    }
                }
            });
        }
    });
});

/**
  * Regenerate file list
  *
  * @param json files
  * @return void
  * @author Damian Kęska
  */

function regenerateFileList(files)
{
    $('#filesList').html('');
    
    for (k in files)
    {
        $('#filesList').append('<tr id="file_'+k+'"><td style="border-right: 0px;">'+k+'</td><td style="border-right: 0px;"><a href="#" onclick="removePHPSFile(\''+k+'\');"><img src="{$PANTHERA_URL}/images/admin/menu/Actions-process-stop-icon.png" style="width: 20px; float: right; margin-right: 5px;"></a></td></tr>');
    }
}

/**
  * Remove PHPS/JSON file from memory
  *
  * @param string id
  * @return void
  * @author Damian Kęska
  */

function removePHPSFile(id)
{
    panthera.jsonPOST({ url: '?display=mergephps&action=removeFile', data: { 'fileName': id }, success: function (response) {
            if (response.files)
            {
                $('#resultGrid').val(response.html);
                regenerateFileList(response.files);
            }
        }
    });
}

/**
  * Change output type
  *
  * @param string name JSON or Serialized array
  * @return void
  * @author Damian Kęska
  */

function changeOutputType(name)
{
    panthera.jsonPOST({ url: '?display=mergephps&action=outputType', data: { 'type': name }, success: function (response) {
            if (response.files)
            {
                $('#resultGrid').val(response.html);
                regenerateFileList(response.files);
            }
        }
    });
}
</script>

{if $popup == True}
<h2 class="popupHeading">{"Merge serialized arrays and json files"|localize:debug}</h2>
{else}
<div class="titlebar">{"Merge serialized arrays and json files"|localize:debug}{include file="_navigation_panel.tpl"}</div>
{/if}

        <div class="msgError" id="messageBox_failed"></div>
        <div class="msgSuccess" id="messageBox_success"></div>
   
        <!-- webrootMerge -->
   
        <div class="grid-2" style="position: relative;" id="resultWindow" ondragover="return false;">
           <div class="title-grid">{"Result"|localize:debug}<a href="#" onclick="changeOutputType('json');" style="float: right;"><img src="{$PANTHERA_URL}/images/admin/mimes/javascript.png" style="width: 30px;"></a> <a href="#" onclick="changeOutputType('serialize');" style="float: right;"><img src="{$PANTHERA_URL}/images/admin/mimes/php.png" style="width: 30px;"></a></div>
           <div class="content-gird">
               <textarea style="width: 100%; height: 100%; min-height: 600px;" id="resultGrid">{if strlen($result) > 0}{$result}
               {else}
                 {"Please upload some files to get result"|localize:debug}
               {/if}
               </textarea>
           </div>
        </div>
        
        <div class="grid-2" style="position: relative;" id="dragDropHere" ondragover="return false;">
           <div class="title-grid">{"Files"|localize:debug}<span></span></div>
           <div class="content-table-grid">
              <table class="insideGridTable">
                <tfoot>
                    <tr>
                        <td colspan="3"><small>{"Drag and drop files here"|localize:debug}</small></td>
                    </tr>
                </tfoot>
            
                <tbody id="filesList">
                    {foreach from=$files key=k item=i}
                    <tr id="file_{$k}">
                        <td style="border-right: 0px;">{$k}</td><td style="border-right: 0px;"><a href="#" onclick="removePHPSFile('{$k}');"><img src="{$PANTHERA_URL}/images/admin/menu/Actions-process-stop-icon.png" style="width: 20px; float: right; margin-right: 5px;"></a></td>
                    </tr>
                    {/foreach}
                </tbody>
            </table>
         </div>
        </div>
