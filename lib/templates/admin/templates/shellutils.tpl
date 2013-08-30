{if="$action == ''"}
<script>

/**
  * Execute command from shell
  *
  * @author Mateusz Warzyński
  */

function executeShellCommand()
{
    command = jQuery('#command_selection').val();

    panthera.jsonPOST({ url: '{$AJAX_URL}?display=shellutils&cat=admin&exec='+command, data: '', messageBox: 'w2ui'});
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

	{include="ui.titlebar"}

    <div class="grid-1">

      <table class="gridTable">

        <thead>
            <tr><th colspan="4"><b>{function="localize('Execute command', 'debug')"}</b></th></tr>
         </thead>

        <tfoot>
            <tr>
                <td colspan="4" class="rounded-foot-left"><em>Panthera - {function="localize('shellutils', 'debug')"}</em></td>
            </tr>
        </tfoot>

        <tbody>
            <tr><td style="width: 100px;">{function="localize('Server command', 'debug')"}:</td><td style="width: 80px;">
            <select id="command_selection">
            {loop="$commands"}
                <option value="{$key}">{$key}</option>
            {/loop}
            </select>
            </td> <td><input type="button" value="{function="localize('Execute', 'debug')"}" onclick="executeShellCommand();"></td></tr>
        </tbody>
      </table>

      <div id="command_output_window" style="display: none;"></div>

    </div>
