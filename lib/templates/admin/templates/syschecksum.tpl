<script>$('.ajax_link').click(function (event) { event.preventDefault(); navigateTo(jQuery(this).attr('href')); return false;});

$(document).ready(function(){
    $('#upload_form').ajaxForm({ dataType: 'html',
        success: function(response) {
            $('#syschecksum_window').html(response);
        }
    });
});

</script>

    <div class="titlebar" style="height: 45px;">{"Checksum of system files"|localize:debug} - {"Useful tool showing diffirences between local and remote files"|localize:debug}</div>

    <div class="grid-1">
       <table class="gridTable">
            <thead>
                <tr><th colspan="2"><b>{"Import/Export data"|localize:debug}</b></th></tr>
             </thead>

          <tbody>
              <tr>
                <td>{"Import"|localize:debug}:</td>
                <td>
                  <form id="upload_form" action="{$AJAX_URL}?display=syschecksum" method="POST">
                    <input type="file" name="syschecksum"> <input type="submit" value="{"Import"|localize}"><br><br>
                    <input type="checkbox" name="show_only_modified" checked="checked" value="1"> {"Show only modified files"|localize:debug}<br>
                    <input type="radio" name="method" value="sum"> {"md5 checksum"|localize:debug}<br>
                    <input type="radio" name="method" value="size" checked> {"file size"|localize:debug}<br>
                    <input type="radio" name="method" value="time"> {"modification time"|localize:debug}<br>
                  </form>
                </td>
              </tr>
              <tr>
                <td>{"Export"|localize:debug}:</td>
                <td><input type="button" value="{"Export current data to file"|localize:debug}" onclick="window.location.href='{$AJAX_URL}?display=syschecksum&export&_bypass_x_requested_with'"></td>
              </tr>
          </tbody>
       </table>

       <br>

       <table class="gridTable">

            <thead>
                <tr>
                    <th colspan="3"><b>{"Files"|localize:debug}:</b></th>
                </tr>
             </thead>

            <tfoot>
                <tr>
                    <td colspan="3" class="rounded-foot-left"><em>Panthera syschecksum <input type="button" value="{"Back"|localize}" onclick="navigateTo('?display=settings&action=system_info');" style="float: right;">
                    <input type="button" value="{"Manage permissions"|localize:messages}" onclick="createPopup('_ajax.php?display=acl&popup=true&name=can_manage_debug', 1024, 'upload_popup');" style="float: right;"></em></td>
                </tr>
            </tfoot>

            <tbody>
                {foreach from=$files key=k item=v}
                <tr {if $v.bold == True}style="background-color: rgb(255, 197, 197);"{/if}><td>{$v.name}</td><td>{$v.sum}</td><td>{$v.size}</td><td>{$v.time}</td><td>{if isset($v.created)}{"Created"|localize}{else}{if $v.bold == True}{"Modified"|localize}{/if}{/if}</td></tr>
                {/foreach}
            </tbody>
       </table>

    </div>
