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

    panthera.jsonPOST({ url: '?display=shellutils&cat=admin&exec='+command, data: '', messageBox: 'undefined', success: function(response) {
        if (response.status == 'success')
        {
            $("#output").html(response.message);
        }
    }});
    return false;

}

</script>
{/if}

<style>

.terminal .terminal-output .format, .terminal .cmd .format,
.terminal .cmd .prompt, .terminal .cmd .prompt div, .terminal .terminal-output div div{
    display: inline-block;
}
.terminal {
    padding: 10px;
    position: relative;
    width: 600px;
    height: 400px;
    margin-top: 20px;
    display: inline-block;
    overflow: scroll;
}
.cmd {
    padding: 0;
    margin: 0;
    height: 1.3em;
    /*margin-top: 3px; */
}

.terminal {
    font-family: FreeMono, monospace;
    color: #aaa;
    background-color: #000;
    font-size: 12px;
    line-height: 14px;
}

#output {
    float: left;
    text-align: left;
}
</style>

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
      
      <div class="terminal" style="height: 400px; width: 100%;">
          <div class="cmd"><p id="output">Panthera Framework $</p></div>
      </div>
</div>
