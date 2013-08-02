<script>$('.ajax_link').click(function (event) { event.preventDefault(); navigateTo(jQuery(this).attr('href')); return false;});

$(document).ready(function(){
    $('#upload_form').submit(function () {
        panthera.htmlPOST( { 'data': '#upload_form', success: function (response) {
                $('#syschecksum_window').html(response);
            } 
        });
    
        return false;
    });
});

</script>
<div id="syschecksum_window">
    <div class="titlebar" style="height: 45px;">{function="localize('Checksum of system files', 'debug')"} - {function="localize('Useful tool showing diffirences between local and remote files', 'debug')"}</div>
    <div class="grid-1">
       <table class="gridTable">
            <thead>
                <tr><th colspan="2"><b>{function="localize('Import/Export data', 'debug')"}</b></th></tr>
             </thead>

          <tbody>
              <tr>
                <td>{function="localize('Import', 'debug')"}:</td>
                <td>
                  <form id="upload_form" action="{$AJAX_URL}?display=syschecksum&cat=admin" method="POST" enctype="multipart/form-data">
                    <input type="file" name="syschecksum"> <input type="submit" value="{function="localize('Import')"}"><br><br>
                    <input type="checkbox" name="show_only_modified" checked="checked" value="1"> {function="localize('Show only modified files', 'debug')"}<br>
                    <input type="radio" name="method" value="sum"> {function="localize('md5 checksum', 'debug')"}<br>
                    <input type="radio" name="method" value="size" checked> {function="localize('file size', 'debug')"}<br>
                    <input type="radio" name="method" value="time"> {function="localize('modification time', 'debug')"}<br>
                  </form>
                </td>
              </tr>
              <tr>
                <td>{function="localize('Export', 'debug')"}:</td>
                <td><input type="button" value="{function="localize('Export current data to file', 'debug')"}" onclick="window.location.href='{$AJAX_URL}?display=syschecksum&cat=admin&export&_bypass_x_requested_with'"></td>
              </tr>
          </tbody>
       </table>

       <br>

       <table class="gridTable">

            <thead>
                <tr>
                    <th colspan="5"><b>{function="localize('Files', 'debug')"}:</b></th>
                </tr>
             </thead>

            <tfoot>
                <tr>
                    <td colspan="5" class="rounded-foot-left"><em>Panthera syschecksum
                    <input type="button" value="{function="localize('Manage permissions', 'messages')"}" onclick="createPopup('_ajax.php?display=acl&cat=admin&popup=true&name=can_manage_debug', 1024, 'upload_popup');" style="float: right;"></em></td>
                </tr>
            </tfoot>

            <tbody>
                {loop="$files"}
                <tr {if="$value.bold == True"}style="background-color: rgb(255, 197, 197);"{/if}><td>{$value.name}</td><td>{$value.sum}</td><td>{$value.size}</td><td>{$value.time}</td><td>{if="isset($value.created)"}{function="localize('Created')"}{else}{if="$value.bold == True"}{function="localize('Modified')"}{/if}{/if}</td></tr>
                {/loop}
            </tbody>
       </table>
    </div>
</div>
