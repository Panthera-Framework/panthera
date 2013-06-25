<script type="text/javascript">
jQuery(document).ready(function($) {
    jQuery('#debug_log_trigger').click(function () {
        jQuery('#debug_log_window').slideToggle('slow');
    });

    jQuery('#current_log_trigger').click(function () {
        jQuery('#current_log_window').slideToggle('slow');
    });
});

function toggleDebugValue()
{
    panthera.jsonGET({ data: '', url: '?display=debug&action=toggle_debug_value', success: function (response) {
            if (response.status == "success")
                navigateTo('?display=debug');
        }
    });
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

    <table class="gridTable">
        <thead>
            <tr>
                <th>{"Key"|localize}</th>
                <th>{"Value"|localize}</th>
            </tr>

        </thead>
            <tr>
                <td>{"Debugger state"|localize:debug}</td>
                <td><a id='debug_value' onclick="toggleDebugValue();"  style="cursor: pointer;"> {if $debug eq true} {"True"|localize} {else} {"False"|localize} {/if} </a></td>
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

