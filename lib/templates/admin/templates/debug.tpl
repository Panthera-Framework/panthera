<script type="text/javascript">
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

<div class="titlebar">{"Debugging center"|localize}{include file="_navigation_panel.tpl"}</div>

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
                <th colspan="2">{"Debugging center"|localize}</th>
            </tr>
        </thead>

        <tbody>
            {foreach from=$tools item=i key=k}
            <tr>
                <td colspan="2"><a href="{$i.link}" class="ajax_link">{"$i.name"|localize:debug}</a></td>
            </tr>
            {/foreach}
        </tbody>
    </table>

    <br>

    <table class="gridTable" id="optionsTable" style="position: relative;">
        <thead>
            <tr>
                <th>{"Key"|localize}</th>
                <th>{"Value"|localize}</th>
            </tr>

        </thead>
            <tr>
                <td>{"Debugger state"|localize:debug}</td>
                <td><a id='debug_value' onclick="toggleDebugValue();"  style="cursor: pointer;"> {if $debug eq true} {"On"|localize} {else} {"Off"|localize} {/if} </a></td>
            </tr>
            
            <tr>
                <td>{"Messages filter"|localize:debug}</td>
                <td><select id="messagesFilter"><option value="">all messages</option><option value="blacklist">blacklist</option><option value="whitelist">whitelist</option></select></td>
            </tr>
        </tbody>
      </table>

   {if $debug eq true}
   <h1 id="current_log_trigger" style="cursor: hand; cursor: pointer; margin: 15px;">{"Show current log"|localize:debug}</h1>
   <div id="current_log_window" style="display: none;">
     <table class="greenLog">
        {foreach from=$current_log item=i key=k}
        <tr>
              <td><strong>{$k+1}.</strong></td>
              <td>{$i}</td>
        </tr>
      {/foreach}
     </table>
   </div>

   <h1 id="debug_log_trigger" style="cursor: hand; cursor: pointer; margin: 15px;">{"Show debug.log content"|localize:debug}</h1>
   <div id="debug_log_window" style="display: none;">
     <table class="blueLog">
        {foreach from=$debug_log item=i key=k}
        <tr>
              <td><strong>{$k+1}.</strong></td>
              <td>{$i}</td>
        </tr>
      {/foreach}
     </table>
   </div>
   {/if}

