{$site_header}
{include="ui.titlebar"}

<style>
#ajax_content {
    background-color: #56687b;
}

#topContent {
    min-height: 55px;
}

</style>

{if="$debuggingButtons"}
<script type="text/javascript">
$(document).ready(function() {
    $('#debug_log_trigger').click(function () {
        $('#debug_log_window').slideToggle('slow');
    });

    $('#current_log_trigger').click(function () {
        $('#current_log_window').slideToggle('slow');
    });
    
    panthera.inputTimeout({ element: '#messagesFilter', interval: 600, callback: messagesFilterSave });
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
    panthera.jsonGET({ data: '', url: '?display=debug&cat=admin&action=toggleDebugValue', success: function (response) {
            if (response.state)
            {
                $('#debuggerState').html('{function="localize('On')"}');
                $('#buttonToggleDebuggerState').val('{function="localize('Turn off debugger')"}');
            } else {
                $('#debuggerState').html('{function="localize('Off')"}');
                $('#buttonToggleDebuggerState').val('{function="localize('Turn on debugger')"}');
            }
        }
    });
}

/**
  * Toggle strict debugging
  *
  * @author Damian Kęska
  */

function toggleStrictDebugging()
{
    panthera.jsonGET({ data: '', url: '?display=debug&cat=admin&action=toggleStrictDebugging', success: function (response) {
            if (response.state)
            {
                $('#strictDebugging').html('{function="localize('On')"}');
            } else {
                $('#strictDebugging').html('{function="localize('Off')"}');
            }
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

<!-- Options popup -->
<div style="display: none;" id="options">
        <table class="formTable" style="margin: 0 auto; color: #fff;">
             <thead>
                 <tr>
                    <td colspan="2" class="formTableHeader" style="padding-top: 0px; padding-bottom: 30px;">
                        <p style="color: #e5ebef; padding: 0px; margin: 0px; margin-left: 30px;">{function="localize('Options')"}</p>
                    </td>
                 </tr>
             </thead>
             
              <tbody>
                    <tr>
                        <th>{function="localize('Debugger state', 'debug')"}:</th>
                        <td><a id='debug_value' onclick="toggleDebugValue();"  style="cursor: pointer; color: #fff;"> <span id="debuggerState">{if="$debug == true"}{function="localize('On')"}{else}{function="localize('Off')"}{/if}</span></a></td>
                    </tr>
                    
                    <tr>
                        <th>{function="localize('Strict debugging', 'debug')"}:</th>
                        <td><a id='strict_debug_value' onclick="toggleStrictDebugging();"  style="cursor: pointer; color: #fff;"> <span id="strictDebugging">{if="$strictDebugging == true"}{function="localize('On')"}{else}{function="localize('Off')"}{/if}</span></a></td>
                    </tr>
                    
                    <tr style="background-color: transparent;">
                        <th>{function="localize('Log save handlers', 'debug')"}:</th>
                        <td>{$logHandlers}</td>
                    </tr>
                    
                    <tr>
                        <th>{function="localize('Messages filter', 'debug')"}:</th>
                        <td>
                            <select id="messagesFilter">
                                <option value="" {if="$messageFilterType == ''"}selected{/if}>{function="localize('all messages', 'debug')"}</option>
                                <option value="blacklist" {if="$messageFilterType == 'blacklist'"}selected{/if}>{function="localize('blacklist', 'debug')"}</option>
                                <option value="whitelist" {if="$messageFilterType == 'whitelist'"}selected{/if}>{function="localize('whitelist', 'debug')"}</option>
                            </select>
                        </td>
                    </tr>
                    
                    <tr id="filterTr" style="background-color: transparent;">
                        <th>{function="localize('Filter name (eg. pantheraLocale)', 'debug')"}:</th>
                        <td><input type="text" id="messagesFilterText"> <input type="button" value="{function="localize('Add')"}/{function="localize('Remove')"}" id="messagesFilterButton"></td>
                    </tr>
                    
                    <tr id="filterListTr">
                        <th>{function="localize('Filter list', 'debug')"}:</th>
                        <td id="filterList">{$filterList}</td>
                    </tr>
                    
                    <tr style="background-color: transparent;">
                        <th>{function="localize('Small, incomplete list of example filters', 'debug')"}:</th>
                        <td style="max-width: 400px;">
                            <small>{loop="$exampleFilters"} <a onclick="manageFilters('{$value}')" style="cursor: pointer; color: #fff;">{$value}</a> {/loop}</small>
                        </td>
                    </tr>
              </tbody>
        </table>
        
        <script type="text/javascript">
        $('#messagesFilterButton').click(function () {
            manageFilters($('#messagesFilterText').val());
        });
        
        panthera.inputTimeout({ element: '#messagesFilter', interval: 900, callback: messagesFilterSave });
        </script>
</div>
{/if}

<div id="topContent">
    {$uiSearchbarName="uiTop"}
    {include="ui.searchbar"}
    
    <div class="searchBarButtonArea">
    	{if="$debuggingButtons"}
        <input type="button" value="{if="$debug == true"}{function="localize('Turn off debugger')"}{else}{function="localize('Turn on debugger')"}{/if}" onclick="toggleDebugValue()" title="{function="localize('For root account debug is always turned on', 'debug')"}" id="buttonToggleDebuggerState">
        <input type="button" value="{function="localize('Options')"}" onclick="panthera.popup.toggle('element:#options')">
    	{/if}
    </div>
</div>

<div class="settingsBackground">
    {loop="$items"}
    <div id="section_{$j}">
        <div class="titledSeparator">{function="localize(ucfirst($key), 'settings')"}</div>

        <div class="iconViewContainer">
            {loop="$value"}
            <div class="iconViewItem">
                <a href="{$value.link}" class="ajax_link"><img src="{$value.icon|pantheraUrl}" style="width: 48px;">
                <p>{$value.name} <br><span>{$value.description}</span></p></a>
            </div>
            {/loop}
        </div>
    </div>
    {/loop}
</div>
