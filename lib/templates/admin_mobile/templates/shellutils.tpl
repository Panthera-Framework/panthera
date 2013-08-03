    <nav class="tab-fixed">
      <ul class="tab-inner">
        <li><a href="#" onclick="navigateTo('?display=debug&cat=admin');">{function="localize('Debugging center')"}</a></li>
        <li class="active"><a data-ignore="true">{function="localize('Shell utils', 'debug')"}</a></li>
      </ul>
    </nav>

    <div class="content inset">
      <div class="slider tab-slider">

        <ul>
            <li id="debug" class="tab-item active">
               <ul class="list inset">

                    <li class="list-divider">{function="localize('Execute command', 'debug')"}</li>

                    <select id="command_selection">
                   {loop="$commands"}
                        <option value="{$key}">{$key}</option>
                   {/loop}
                    </select>

                    <input type="button" class="btn-block" value="{function="localize('Execute', 'debug')"}" onclick="executeShellCommand();"><br><br>

                    <div style="background: #EDF6FF; color: black;">
                       <div class="msgSuccess" id="userinfoBox_success" style="background: #000; color: #bbb; font-size: 14px;"></div>
                       <div class="msgError" id="userinfoBox_failed"></div>
                    </div>
               </ul>
            </li>
        </ul>
     </div>
    </div>

    {if="$action == ''"}
    <!-- JS code -->
    <script>

    /**
      * Execute command from shell
      *
      * @author Mateusz Warzyński
      */

    function executeShellCommand()
    {
        command = jQuery('#command_selection').val();

        panthera.jsonPOST({ url: '{$AJAX_URL}?display=shellutils&cat=admin&exec='+command, data: '', messageBox: 'userinfoBox'});
        return false;

    }

    </script>
    <!-- End of JS code -->
    {/if}