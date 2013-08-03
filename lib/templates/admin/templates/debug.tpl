<script type="text/javascript">
$('.ajax_link').click(function (event) { event.preventDefault(); navigateTo(jQuery(this).attr('href')); return false;});

$(document).ready(function() {
    $('#debug_log_trigger').click(function () {
        $('#debug_log_window').slideToggle('slow');
    });

    $('#current_log_trigger').click(function () {
        $('#current_log_window').slideToggle('slow');
    });
    
    $('#messagesFilterButton').click(function () {
        manageFilters($('#messagesFilterText').val());
    });
    
    panthera.inputTimeout({ element: '#messagesFilter', interval: 900, callback: messagesFilterSave });
});

var spinner = new panthera.ajaxLoader($('#optionsTable'));

/**
  * Add or remove filter
  *
  * @author Damian Kęska
  */

function manageFilters(filter)
{
    panthera.jsonPOST({ url: '{$AJAX_URL}?display=debug&cat=admin&action=manageFilterList', data: 'filter='+filter, spinner: spinner, success: function (response) {
            if(response.status == "success")
            {
                $('#filterList').html(response.filter);
            }
        }
   });
}

/**
  * Toggle debugger
  *
  * @author Damian Kęska
  */

function toggleDebugValue()
{
    panthera.jsonGET({ data: '', url: '?display=debug&cat=admin&action=toggle_debug_value', success: function (response) {
            if (response.status == "success")
                navigateTo('?display=debug&cat=admin');
        }
    });
}

/**
  * Save messages filter mode
  *
  * @author Damian Kęska
  */

function messagesFilterSave()
{
    panthera.jsonPOST({ url: '{$AJAX_URL}?display=debug&cat=admin&action=setMessagesFilter', data: 'value='+$('#messagesFilter').val(), spinner: spinner});
}

/**
  * Save configuration variable to database
  *
  * @author Mateusz Warzyński
  */

function saveVariable(id, value)
{
    panthera.jsonPOST({ url: '{$AJAX_URL}?display=conftool&cat=admin&action=change', data: 'id='+id+'&value='+value, spinner: spinner});
    return false;

}
</script>

<div class="titlebar">{function="localize('Debugging center')"}{include="_navigation_panel"}</div>

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
                <th colspan="2">{function="localize('Settings')"}</th>
            </tr>

        </thead>
            <tr>
                <td>{function="localize('Debugger state', 'debug')"}</td>
                <td><a id='debug_value' onclick="toggleDebugValue();"  style="cursor: pointer;"> {if="$debug == true"} {function="localize('On')"} {else} {function="localize('Off')"} {/if} </a></td>
            </tr>
            
            <tr>
                <td>{function="localize('Messages filter', 'debug')"}</td>
                <td>
                    <select id="messagesFilter">
                        <option value="" {if="$messageFilterType == ''"}selected{/if}>{function="localize('all messages', 'debug')"}</option>
                        <option value="blacklist" {if="$messageFilterType == 'blacklist'"}selected{/if}>{function="localize('blacklist', 'debug')"}</option>
                        <option value="whitelist" {if="$messageFilterType == 'whitelist'"}selected{/if}>{function="localize('whitelist', 'debug')"}</option>
                    </select>
                </td>
            </tr>
            
            <tr id="filterTr">
                <td>
                    {function="localize('Filter name (eg. pantheraLocale)', 'debug')"}
                </td>
                
                <td>
                    <input type="text" id="messagesFilterText"> <input type="button" value="{function="localize('Add')"}/{function="localize('Remove')"}" id="messagesFilterButton">
                </td>
            </tr>
            
            <tr id="filterListTr">
                <td>
                    {function="localize('Filter list', 'debug')"}
                </td>
                
                <td id="filterList">
                    {$filterList}
                </td>
            </tr>
            
            <tr>
                <td>{function="localize('Small, incomplete list of example filters', 'debug')"}</td>
                <td><small>
                {loop="$exampleFilters"}
                <a onclick="manageFilters('{$value}')" style="cursor: pointer;">{$value}</a>
                {/loop}
            </tr>
        </tbody>
      </table>

   {if="$debug == true"}
   <h1 id="current_log_trigger" style="cursor: hand; cursor: pointer; margin: 15px;">{function="localize('Current session log', 'debug')"}</h1>
   <div id="current_log_window">
     <table class="greenLog">
        {loop="$current_log"}
        <tr>
              <td><strong>{$key+1}.</strong></td>
              <td>{$value}</td>
        </tr>
      {/loop}
     </table>
   </div>

   <h1 id="debug_log_trigger" style="cursor: hand; cursor: pointer; margin: 15px;">{function="localize('Debug.log content', 'debug')"}</h1>
   <div id="debug_log_window">
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

