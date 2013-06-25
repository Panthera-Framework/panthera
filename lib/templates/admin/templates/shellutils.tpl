{if $action eq ''}
<script>

/**
  * Execute command from shell
  *
  * @author Mateusz Warzyński
  */

function executeShellCommand()
{
    command = jQuery('#command_selection').val();

    panthera.jsonPOST({ url: '{$AJAX_URL}?display=shellutils&exec='+command, data: '', messageBox: 'userinfoBox'});
    return false;

}

</script>
{/if}

<style>
#command_output_window {
      width: 92%;
      background-color: rgb(237, 246, 255);
      padding: 5px;
      border: 1px solid #d4d4d4;
      font-size: 110%;
      font-family: "courier new";
      padding: 20px;
      margin: 20px;
}
</style>

<script>$('.ajax_link').click(function (event) { event.preventDefault(); navigateTo(jQuery(this).attr('href')); return false;});</script>

    <div class="titlebar">{"Shell utils"|localize:debug} - {"Developer tools"|localize:debug}</div>

    <br>
    <div class="msgSuccess" id="userinfoBox_success"></div>
    <div class="msgError" id="userinfoBox_failed"></div>

    <div class="grid-1">

      <table class="gridTable">

        <thead>
            <tr><th colspan="4"><b>{"Execute command"|localize:debug}</b></th></tr>
         </thead>

        <tfoot>
            <tr>
                <td colspan="4" class="rounded-foot-left"><em>Panthera - {"shellutils"|localize:debug} <input type="button" value="{"Back"|localize}" onclick="navigateTo('{navigation::getBackButton()}');" style="float: right;"></em></td>
            </tr>
        </tfoot>

        <tbody>
            <tr><td style="width: 100px;">{"Server command"|localize:debug}:</td><td style="width: 80px;">
            <select id="command_selection">
            {foreach from=$commands key=k item=v}
                <option value="{$k}">{$k}</option>
            {/foreach}
            </select>
            </td> <td><input type="button" value="{"Execute"|localize:debug}" onclick="executeShellCommand();"></td></tr>
        </tbody>
      </table>

      <div id="command_output_window" style="display: none;"></div>

    </div>
