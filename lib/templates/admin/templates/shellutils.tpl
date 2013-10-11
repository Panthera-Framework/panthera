{$site_header}

{if="$action == ''"}
<script>

/**
  * Execute command from shell
  *
  * @author Mateusz Warzyński
  */

function executeShellCommand()
{
    command = $('#command_selection').val();

    panthera.jsonPOST({ url: '{$AJAX_URL}?display=shellutils&cat=admin&exec='+command, data: '', messageBox: 'w2ui'});
    return false;

}

</script>
{/if}

{include="ui.titlebar"}

<!-- Content -->
<div class="ajax-content" style="text-align: center;">
    <table style="display: inline-block;">

        <thead>
            <tr><th colspan="4"><b>{function="localize('Execute command', 'debug')"}</b></th></tr>
         </thead>

        <tbody>
            <tr>
                <td style="width: 120px;">{function="localize('Server command', 'debug')"}:</td>
                <td style="min-width: 80px;">
                    <select id="command_selection">
                    {loop="$commands"}
                        <option value="{$key}">{$key}</option>
                    {/loop}
                    </select>
                </td> 
                <td>
                    <input type="button" value="{function="localize('Execute', 'debug')"}" onclick="executeShellCommand();">
                </td>
            </tr>
        </tbody>
      </table>
</div>
