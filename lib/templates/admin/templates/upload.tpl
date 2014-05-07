{$site_header}

<script type="text/javascript">

/**
  * Delete upload category
  *
  * @author Mateusz Warzyński
  */

function removeUploadCategory(id)
{
    w2confirm('{function="localize('Are you sure you want to delete this category?', 'upload')"}', function (responseText) {
        
            if (responseText == 'Yes')
            {
                panthera.jsonGET( { url: '{$AJAX_URL}?display=upload&cat=admin&action=deleteCategory&id='+id, messageBox: 'w2ui', success: function (response) {
                        if (response.status == 'success')
                        {
                            navigateTo('?display=upload&cat=admin');
                        }
                
                    }
                });
            }
        
    });
}

/**
  * Save settings to database
  *
  * @author Mateusz Warzyński
  */

function saveSettings()
{
    var maxFileSize = $("#maxFileSize").val();
    
    panthera.jsonGET( { url: '{$AJAX_URL}?display=upload&cat=admin&action=saveSettings&maxFileSize='+maxFileSize, messageBox: 'w2ui', success: function (response) {
            if (response.status == 'success')
            {
                navigateTo('?display=upload&cat=admin');
            }
        }
    });
}
</script>

{include="ui.titlebar"}

<div id="topContent">
    {$uiSearchbarName="uiTop"}
    {include="ui.searchbar"}

    <div class="searchBarButtonArea">
        <input type="button" value="{function="localize('Create category', 'upload')"}" onclick="panthera.popup.toggle('element:#createCategory');">
        <input type="button" value="{function="localize('File list', 'upload')"}" onclick="panthera.popup.toggle('_ajax.php?display=upload&cat=admin&popup=true');">
    </div>
</div>

<div id="createCategory" style="display: none;">
   <form action="?display=upload&cat=admin&action=addCategory" method="POST" id="newCategoryForm">
    <table class="formTable" style="margin: 0 auto; margin-top: 30px; margin-bottom: 30px;">
        
        <tbody>
          <tr>
            <th>{function="localize('Title', 'upload')"}:</th>
            <th><input type="text" name="title" style="width: 95%;"></th>
          </tr>
          
          <tr>
            <th>
            	{function="localize('Mime', 'upload')"}:<br>
            	<small>({function="localize('Comma separated eg. document, audio, video, archive, application/pdf, binary, image/jpeg, image/png', 'upload')"})</small>
            </th>
            <th><input type="text" name="mime" value="all" style="width: 95%;"></th>
          </tr>
          
          <tr>
            <th>{function="localize('Upload maximum size', 'upload')"}:<br><small>{function="localize('Set maximum size of uploaded files (eg. 2 mb, 1 gb, 100 kilobytes)', 'upload')"}.</small></th>
            <th><input type="text" name="maxfilesize" value="2 mb" style="width: 95%;"></th>
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
                <th>{function="localize('Max file size', 'upload')"}</th>
                <th>{function="localize('Options', 'upload')"}</th>
            </tr>
        </thead>

        <tbody class="hovered">
           {if="count($categories) > 1"}
            {loop="$categories"}
            <tr> 
                <td><a href="#" onclick="panthera.popup.toggle('{$AJAX_URL}?display=upload&cat=admin&directory={$value->name}&action=editCategory');">{$value->getName()}</a></td>
                <td>{$value->created}</td>
                <td>{$value->mime_type}</td>
                <td title="{function="localize('Please note: Max file size depends also on your PHP configuration, all sizes are real including server configuration', 'upload')"}">{if="$value->getMaxFilesize()"}{$value->getMaxFilesize(true)}{else}{function="localize('Unlimited', 'upload')"}{/if}</td>
                <td>
                    <a href="#" onclick="removeUploadCategory('{$value->id}');">
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
    </table> <br/>
    
    <table style="display: inline-block; margin-top: 30px;">
        <thead>
            <tr>
                <th colspan="2">{function="localize('Settings')"}</th>
            </tr>
        </thead>
    
        <tbody>
            <tr>
                <td valign="top">
                    <p>{function="localize('Upload maximum size', 'upload')"}:
                  <br>
                    <small><span style="color: grey;">{function="localize('Set maximum size of uploaded files (eg. 2 mb, 1 gb, 100 kilobytes)', 'upload')"}.</span></small>
                    </p>
                </td>
                <td><input type="text" id="maxFileSize" value="{$fileMaxSize}" style="width: 95%;" onchange="$('#saveButton').slideDown();"></td>
            </tr>
            <tr id="saveButton" style="display: none;"><td colspan="2"><input type="button" value="Save" onclick="saveSettings();" style="float: right;"></td></tr>
        </tbody>
    </table>
</div>