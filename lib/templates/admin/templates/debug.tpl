<script type="text/javascript">
$('.ajax_link').click(function (event) { event.preventDefault(); navigateTo(jQuery(this).attr('href')); return false;});

$(document).ready(function() {
    $('#debug_log_trigger').click(function () {
        $('#debug_log_window').slideToggle('slow');
    });

    $('#current_log_trigger').click(function () {
        $('#current_log_window').slideToggle('slow');
    });
    
    panthera.inputTimeout({ element: '#messagesFilter', interval: 1200, callback: messagesFilterSave });
});

function toggleDebugValue()
{
    panthera.jsonGET({ data: '', url: '?display=debug&action=toggle_debug_value', success: function (response) {
            if (response.status == "success")
                navigateTo('?display=debug');
        }
    });
}

function messagesFilterSave()
{
    saveVariable('debug.msgfilter', $('#messagesFilter').val());
}

/**
  * Save configuration variable to database
  *
  * @author Mateusz Warzyński
  */

function saveVariable(id, value)
{
    spinner = new panthera.ajaxLoader($('#optionsTable'));
    panthera.jsonPOST({ url: '{$AJAX_URL}?display=conftool&action=change', data: 'id='+id+'&value='+value, spinner: spinner});
    return false;

}
</script>

<div class="titlebar">{function="localize('Debugging center')"}{include="_navigation_panel.tpl"}</div>

      <table class="gridTable">
        <tfoot>
            <tr>
                <td colspan="2" class="rounded-foot-left"><em>
                 Panthera - debug
                </em></td>
            </tr>
        </tfoot>

        <br>

        <thead>
            <tr>
                <th colspan="2">{function="localize('Debugging center')"}</th>
            </tr>
        </thead>

        <tbody>
            {loop="$tools"}
            <tr>
                <td colspan="2"><a href="{$value.link}" class="ajax_link">{function="localize($value.name, 'debug')"}</a></td>
            </tr>
            {/loop}
        </tbody>
    </table>

    <br>

    <table class="gridTable" id="optionsTable" style="position: relative;">
        <thead>
            <tr>
                <th>{function="localize('Key')"}</th>
                <th>{function="localize('Value')"}</th>
            </tr>

        </thead>
            <tr>
                <td>{function="localize('Debugger state', 'debug')"}</td>
                <td><a id='debug_value' onclick="toggleDebugValue();"  style="cursor: pointer;"> {if="$debug == true"} {function="localize('On')"} {else} {function="localize('Off')"} {/if} </a></td>
            </tr>
            
            <tr>
                <td>{function="localize('Messages filter', 'debug')"}</td>
                <td><select id="messagesFilter"><option value="">all messages</option><option value="blacklist">blacklist</option><option value="whitelist">whitelist</option></select></td>
            </tr>
        </tbody>
      </table>

   {if="$debug == true"}
   <h1 id="current_log_trigger" style="cursor: hand; cursor: pointer; margin: 15px;">{function="localize('Show current log', 'debug')"}</h1>
   <div id="current_log_window" style="display: none;">
     <table class="greenLog">
        {loop="$current_log"}
        <tr>
              <td><strong>{$key+1}.</strong></td>
              <td>{$value}</td>
        </tr>
      {/loop}
     </table>
   </div>

   <h1 id="debug_log_trigger" style="cursor: hand; cursor: pointer; margin: 15px;">{function="localize('Show debug.log content', 'debug')"}</h1>
   <div id="debug_log_window" style="display: none;">
     <table class="blueLog">
        {loop="$debug_log"}
        <tr>
              <td><strong>{$key+1}.</strong></td>
              <td>{$value}</td>
        </tr>
      {/loop}
     </table>
   </div>
   {/if}

