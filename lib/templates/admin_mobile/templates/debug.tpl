    <!-- JS code -->
    <script type="text/javascript">
    $(document).ready(function() {
        $('#messagesFilterButton').click(function () {
            manageFilters($('#messagesFilterText').val());
        });
    });

    /**
      * Toggle value of debug
      *
      * @author Mateusz Warzyński
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
      * Add or remove filter
      *
      * @author Damian Kęska
      */

    function manageFilters(filter)
    {
        panthera.jsonPOST({ url: '{$AJAX_URL}?display=debug&cat=admin&action=manageFilterList', data: 'filter='+filter, success: function (response) {
                if(response.status == "success")
                {
                    $('#filterList').html(response.filter);
                }
            }
       });
    }
    </script>
    <!-- End of JS code -->

    <nav class="tab-fixed">
      <ul class="tab-inner">
        <li><a href="#" onclick="navigateTo('?display=dash&cat=admin');">{function="localize('Dash')"}</a></li>
        <li class="active"><a data-ignore="true">{function="localize('Debugging center')"}</a></li>
      </ul>
    </nav>

   <div class="content">
     <div class="slider tab-slider">
        <ul>
            <ul class="list inset">

             <li class="list-divider">{function="localize('Tools', 'debug')"}</li>

             {loop="$tools"}
              <li class="list-item-single-line selectable">
                <a href="{$value.link}" data-ignore="true" data-transition="push">
                    <p style="vertical-align: middle;">{function="localize($value.name, 'debug')"}</p>
                </a>
              </li>
             {/loop}

             <br><br>

             <li class="list-divider">{function="localize('Settings', 'debug')"}</li>
              <li class="list-item-two-lines selectable">
                <a href="" onclick="toggleDebugValue();" data-ignore="true">
                    <h3><span id="debugger_state">{if="$debug == true"} {function="localize('On')"} {else} {function="localize('Off')"} {/if}</span></h3>
                    <p>{function="localize('Debugger state', 'debug')"}</p>
                </a>
              </li>

              <li class="list-item-two-lines selectable">
                <a href="" data-ignore="true">
                        <select id="messagesFilter" style="padding: 0px;">
                            <option value="" {if="$messageFilterType == ''"}selected{/if}>{function="localize('all messages', 'debug')"}</option>
                            <option value="blacklist" {if="$messageFilterType == 'blacklist'"}selected{/if}>{function="localize('blacklist', 'debug')"}</option>
                            <option value="whitelist" {if="$messageFilterType == 'whitelist'"}selected{/if}>{function="localize('whitelist', 'debug')"}</option>
                        </select>
                    <p style="font-size: 12px; color: #bbb;">{function="localize('Messages filter', 'debug')"}</p>
                </a>
              </li>

              <li class="list-item-two-lines">
                <div>
                   <button class="btn-small" style="float: right; height: 53px;" id="messagesFilterButton">{function="localize('Add')"}/{function="localize('Remove')"}</button>
                   <input type="text" placeholder="{function="localize('Filter name (eg. pantheraLocale)', 'debug')"}" onfocus="this.value = '';" class="input-text" id="messagesFilterText" style="border-bottom: 0px; max-width: calc(100% - 162px);">
                </div>
              </li>

              <li class="list-item-two-lines">
                <a href="" data-ignore="true">
                    <h3 id="filterList">{$filterList}</h3>
                    <p>{function="localize('Filter list', 'debug')"}</p>
                </a>
              </li>

              <li class="list-item-two-lines">
                <a href="" data-ignore="true">
                    <h3>
                        {loop="$exampleFilters"}
                            <a onclick="manageFilters('{$value}')" style="cursor: pointer; font-size: 14px;">{$value}</a>
                        {/loop}
                    </h3>
                    <p>{function="localize('Small, incomplete list of example filters', 'debug')"}</p>
                </a>
              </li>

            {if="$debug == true"}
             <br><br>

             <li class="list-divider"><a onclick="$('#current_log').slideToggle();">{function="localize('Current session log', 'debug')"}</a></li>
             <li class="list-item-two-lines" id="current_log" style="display: none;">
                 {loop="$current_log"}
                    <p><span style="color: #bbb;"><b>{$key+1}.</b></span>&nbsp;&nbsp;{$value}</p>
                {/loop}
             </li>

             <br>

             <li class="list-divider"><a onclick="$('#debug_log').slideToggle();">{function="localize('Debug.log content', 'debug')"}</a></li>
             <li class="list-item-two-lines" id="debug_log" style="display: none;">
                 {loop="$debug_log"}
                    <p><span style="color: #bbb;"><b>{$key+1}.</b></span>&nbsp;&nbsp;{$value}</p>
                {/loop}
             </li>

            {/if}

            </ul>
        </ul>
     </div>
   </div>