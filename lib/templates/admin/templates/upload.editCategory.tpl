<script type="text/javascript">
$(document).ready(function(){
    $('#editForm').submit(function () {
        panthera.jsonPOST({ data: '#editForm', success: function(response) {
                if (response.status == "success")
                {
                    panthera.popup.close();
                    navigateTo(window.location.href);
                }
            } 
        });
        
        return false;
      });
});
</script>

<form action="?display=upload&cat=admin&action=editCategory&popup=true&directory={$category->name}" method="POST" id="editForm">
    <table class="formTable" style="margin: 0 auto; margin-top: 30px; margin-bottom: 30px;" id="upload_box_window">
        <thead>
            <tr>
                <td colspan="2" class="formTableHeader" style="padding-top: 0px; padding-bottom: 30px;">
                    <p style="color: #e5ebef; padding: 0px; margin: 0px; margin-left: 30px;">{function="localize('Editing category', 'upload')"} - {if="$category->exists()"}{$category->getName()}{/if}</p>
                </td>
            </tr>
         </thead>
         
         <tbody>
         	 <tr>
                 <th>{function="localize('Name')"}:</th>
                 <td><input type="text" name="name" style="width: 95%;" value="{$category->getName()}"></td>
             </tr>
         
             {if="$category->exists()"}
             <tr>
             	<th>{function="slocalize('Allowed file types: %s', 'upload', '')"}</th>
             	<td style="color: white;"><input type="text" name="mime" value="{$category->mime_type}"></td>
             </tr>
             
             <tr title="{function="localize('Please note: Max file size depends also on your PHP configuration, all sizes are real including server configuration', 'upload')"}">
             	<th>{function="localize('Max file size', 'upload')"}:</th>
             	<td style="color: white;"><input type="text" name="maxfilesize" value="{$category->getMaxFilesize(true)}"></td>
             </tr>
             {/if}
         </tbody>
         <tfoot>
             <tr>
                 <td colspan="5" style="padding-top: 25px;">
                 	 <input type="hidden" name="formSubmit" value="1">
                     <input type="submit" value="{function="localize('Save')"}" style="float: right; margin-right: 30px;">
                     <input type="button" id="_upl_back_btn" value="{function="localize('Close')"}" style="float: left; margin-left: 30px;" onclick="panthera.popup.close()">
                 </td>
             </tr>
         </tfoot>
    </table>
</form>
