<script type="text/javascript">

var selected = new Array;

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
       
        if (color == "rgb(255, 255, 255)") {
            $("#file_"+id).css("background-color", "rgba(86, 104, 123, 0.70)");
            selected.push(id)
        } else {
            $("#file_"+id).css("background-color", "#ffffff");
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

{include="ui.titlebar"}

<div id="topContent">
    {$uiSearchbarName="uiTop"}
    {include="ui.searchbar"}

    <div class="searchBarButtonArea">
        <input type="button" value="{function="localize('Create category', 'upload')"}" onclick="panthera.popup.toggle('element:#createCategory')">
    </div>
</div>

<div id="createCategory" style="display: none;">
   <form action="?{function="getQueryString('GET', 'action=createCategory', '_')"}" method="POST" id="newCategoryForm">
    <table class="formTable" style="margin: 0 auto; margin-top: 30px; margin-bottom: 30px;">
        
        <tbody>
          <tr>
            <th>{function="localize('Name', 'upload')"}:</th>
            <th><input type="text" name="name" style="width: 95%;"></th>
          </tr>
          
          <tr>
            <th>{function="localize('Mime', 'upload')"}:</th>
            <th><input type="text" name="mime" value="all" style="width: 95%;"></th>
          </tr>
        </tbody>
        
        <tfoot>
          <tr>
            <td colspan="2" style="padding-top: 35px;">
                <input type="submit" value="{function="localize('Add')"}" style="float: right; margin-right: 30px;">
            </td>
          </tr>
        </tfoot>
        
    </table>
    <input type="text" name="language" value="{$set_locale}" style="display: none;">
   </form>
   
   <script type="text/javascript">
        $('#newCategoryForm').submit(function () {
            panthera.jsonPOST( { data: '#newCategoryForm', messageBox: 'w2ui', success: function (response) {
                    if (response.status == 'success')
                    {
                        navigateTo('?display=upload&cat=admin');
                    }
                } 
            });
            
            return false;
        });
   </script>
</div>


<!-- Content -->
<div class="ajax-content" style="text-align: center;">
    <table style="display: inline-block;">

        <thead>
            <tr>
                <th>{function="localize('Name', 'upload')"}</th>
                <th>{function="localize('Created', 'upload')"}</th>
                <th>{function="localize('Mime', 'upload')"}</th>
                <th>{function="localize('Options', 'upload')"}</th>
            </tr>
        </thead>

        <tbody class="hovered">
           {if="count($categories) > 1"}
            {loop="$categories"}
            <tr> 
                
                <td>{$value.name}</td>
                <td>{$value.created}</td>
                <td>{$value.mime_type}</td>
                <td>
                    <a href="#" onclick="removeUploadCategory('{$value.id}');">
                        <img src="{$PANTHERA_URL}/images/admin/ui/delete.png" style="max-height: 22px;" title="Remove">
                    </a>
                </td>
            </tr>
            {/loop}
           {else}
            <tr id="noGalleryCategories" {if="$category_list"}style="display: none;"{/if}>
                <td colspan="5">{function="localize('No upload categories found, create new one using button upper', 'upload')"}</td>
            </tr>
           {/if}
        </tbody>
    </table>
</div>
