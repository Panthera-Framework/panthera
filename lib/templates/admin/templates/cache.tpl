{$site_header}
<script src="{$PANTHERA_URL}/js/admin/raphael-min.js"></script>
<script src="{$PANTHERA_URL}/js/admin/charts.min.js"></script>
<script type="text/javascript">
$('.ajax_link').click(function (event) { event.preventDefault(); navigateTo(jQuery(this).attr('href')); return false;});

var spinner = new panthera.ajaxLoader($('#cacheVariables'));
var addMemcachedServerSpinner = new panthera.ajaxLoader($('#addMemcachedServerDiv'));

/**
  * Save cache variables to database
  *
  * @author Mateusz Warzyński
  */

function saveCacheVariables()
{
    cache = $('#cache').val();
    varcache = $('#varcache').val();

    panthera.jsonPOST({ url: '?display=cache&cat=admin&action=save', data: 'cache='+cache+'&varcache='+varcache, spinner: spinner, success: function (response) {
          if (response.status == "success")
          {
                   jQuery('#save_button').attr("disabled", "disabled");
                   jQuery('#save_button').animate({ height:'toggle'});
                   setTimeout("jQuery('#save_button').removeAttr('disabled');", 2500);
                   setTimeout("jQuery('#save_button').animate({ height:'toggle' });", 2500);
          } else {

              if (response.message != undefined)
              {
                  w2alert(response.message, '{function="localize('Error', 'localize')"}');
              }
          }
        }
    });
    return false;
}

/**
  * Remove a Memcached server from list
  *
  * @author Damian Kęska
  */

function removeMemcachedServer(server, divid)
{
    w2confirm('{function="localize('Are you sure?')"}', '{function="localize('Confirmation')"}', function (response) {
        if (response != 'Yes')
        {
            return false;
        }

        panthera.jsonPOST( { url: '?display=cache&cat=admin&action=removeMemcachedServer', data: 'server='+server, success: function (response) {
                if (response.status == 'success')
                {
                    $('#'+divid).remove();
                }
            }
        });
    });
}

$(document).ready(function () {
    panthera.inputTimeout({ element: '#cache', interval: 1200, callback: saveCacheVariables });
    panthera.inputTimeout({ element: '#varcache', interval: 1200, callback: saveCacheVariables });

    {if="count($memcachedServers) > 1"}
    var bars = new Charts.BarChart('memcachedChart', {
      show_grid: true,
      label_max: false,
      label_min: false,
      x_label_color: "#333333",
      bar_width: 30,
      rounding: 3,
    });

    {loop="$memcachedServers"}
    if ({$value.num} % 2)
        color = '#53ba03';
    else
        color = '#00aadd';

    bars.add({
      label: "#{$value.num}",
      value: {$value.load_percent},
      options: {
        bar_color: color
      }
    });
    {/loop}

    bars.draw();
    {/if}

    /**
      * Add new Memcached server
      *
      * @author Damian Kęska
      */

    $('#addMemcachedServer').submit(function () {
        panthera.jsonPOST( { data: '#addMemcachedServer', spinner: addMemcachedServerSpinner, success: function (response) {
                if (response.status == "success")
                {
                    navigateTo('?display=cache&cat=admin');
                } else {
                    if (response.message != undefined)
                    {
                        w2alert(response.message, '{function="localize('Error')"}');
                    }
                }

            }
        });

        return false;

    });

});
</script>

    <div class="titlebar">{function="localize('Cache management', 'cache')"} {include="_navigation_panel.tpl"}</div><br>

    <div class="grid-1" style="position: relative;" id="cacheVariables">
         <table class="gridTable">

            <thead>
                <tr>
                    <th colspan="2" style="width: 250px;">{function="localize('Set preffered cache methods in to caching slots', 'cache')"}</th>
                </tr>
            </thead>

            <tbody>
                <tr>
                    <td><b>cache</b><br><small>({function="localize('Needs to be really fast, huge amounts of data are stored here. Set only in-memory caching methods here - APC, XCache, Memcached', 'cache')"})</small></td>
                    <td>
                       <select id="cache">
                         {loop="$cache_list"}
                           {if="$value == True"}
                             <option {if="$cache == $key"} selected {/if}>{$key}</option>
                           {/if}
                         {/loop}
                       </select>
                    </td>
                </tr>

                <tr>
                    <td><b>varCache</b><br><small>({function="localize('Used to store simple variables, this can be a database cache, but if any in-memory cache is avaliable, select it', 'cache')"})</small></td>
                    <td>
                        <select id="varcache">
                          {loop="$cache_list"}
                           {if="$value == True"}
                             <option {if="$varcache == $key"} selected {/if}>{$key}</option>
                           {/if}
                         {/loop}
                       </select>
                    </td>
                </tr>

                <tr>
                    <td><b>session.save_handler</b><br><small>({function="localize('User sessions can be stored on harddrive by default, or in memory using memcached or mm. This can be set in PHP configuration.', 'cache')"})</small></td>
                    <td>
                        {$sessionHandler}
                    </td>
                </tr>
            </tbody>
         </table>
      </div>

    {if="$memcacheAvaliable == True"}
    <div class="grid-2" style="position: relative;" id="addMemcachedServerDiv">
    <table class="gridTable">
        <thead>
            <tr>
                <th colspan="2" style="width: 250px;">{function="localize('Add memcached server', 'cache')"} </th>
            </tr>
        </thead>

        <tbody>
            <tr>
                <td style="text-align: center;">
                    <form action="?display=cache&cat=admin&action=addMemcachedServer" method="POST" id="addMemcachedServer">
                    <input type="text" name="ip" placeholder="{function="localize('address', 'cache')"}"> <input type="text" name="port" placeholder="{function="localize('port', 'cache')"}"> <input type="text" name="priority" placeholder="{function="localize('priority', 'cache')"} ({function="localize('optional', 'cache')"})"> <input type="submit" value="{function="localize('Add')"}">
                    </form>
                </td>
            </tr>
        </tbody>
    </table>
    </div>
    {/if}

    <!-- separator -->
    <div style="height: 1px; margin-top: 100px;"></div>

    {if="count($memcachedServers) > 0"}
    {if="count($memcachedServers) > 1"}
    <!-- charts -->
    <div class="grid-2" style="width: 45%; margin-left: 40px; height: 230px;">
         <div class="title-grid">{function="ucfirst(localize('Memcached statistics', 'cache'))"}<span></span></div>
         <div class="content-table-grid">
            <div style="padding: 20px; border: 10px; width: 480px; margin: 0 auto; text-align: center; overflow: auto;">
                <div id='memcachedChart' style='width: 480px; height: 188px; margin-bottom: 10px;'></div>
                <small>{function="localize('Server load', 'cache')"}</small>
            </div>
         </div>
    </div>
    {/if}

    <!-- list of servers -->
    {loop="$memcachedServers"}
    <div class="grid-2" style="width: 46%;" id="server_{$value.num}">
         <table class="gridTable">

            <thead>
                <tr>
                    <th colspan="2">
                        <a href="#" onclick="createPopup('_ajax.php?display=cache&cat=admin&popup=memcached&server={$key}', 1000, 720);">memcached #{$value.num}</a>
                        <span class="widgetRemoveButtons" style="float: right;">
                            <a href="#" onclick="removeMemcachedServer('{$key}', 'server_{$value.num}')">
                                <img src="{$PANTHERA_URL}/images/admin/list-remove.png" style="height: 15px;">
                            </a>
                        </span>
                    </th>
                </tr>
            </thead>

            <tbody>
                <tr>
                    <td>{function="localize('Host', 'cache')"}:</td>
                    <td><a href="#" onclick="createPopup('_ajax.php?display=cache&cat=admin&popup=stats&server={$key}', 1000, 720);">{$key}, pid: {$value.pid}, {$value.threads} {function="localize('threads', 'cache')"}</a></td>
                </tr>

                <tr>
                    <td>{function="localize('Uptime', 'cache')"}:</td>
                    <td>{$value.uptime}</td>
                </tr>

                <tr>
                    <td>{function="localize('Version', 'cache')"}:</td>
                    <td>{$value.version}</td>
                </tr>

                <tr>
                    <td>{function="localize('Used memory', 'cache')"}:</td>
                    <td>{$value.memory_used} / {$value.memory_max}</td>
                </tr>

                <tr>
                    <td>{function="localize('Transferred', 'cache')"}:</td>
                    <td>{$value.read} {function="localize('read', 'cache')"}, {$value.written} {function="localize('written', 'cache')"}</td>
                </tr>

                <tr>
                    <td>{function="localize('Items', 'cache')"}:</td>
                    <td>{$value.items_current} {function="localize('current', 'cache')"}, {$value.items_total} {function="localize('total', 'cache')"}</td>
                </tr>

                <tr>
                    <td>{function="localize('Requests', 'cache')"}:</td>
                    <td>{if="isset($value.readWarning)"}<b style="color: red;">{/if}{$value.get} {function="localize('get', 'cache')"}, {$value.set} {function="localize('set', 'cache')"}{if="isset($value.readWarning)"} (!)</b>{/if}</td>
                </tr>

                <tr>
                    <td>{function="localize('Connections', 'cache')"}:</td>
                    <td>{$value.connections_current} {function="localize('current', 'cache')"}, {$value.connections_total} {function="localize('total', 'cache')"}</td>
                </tr>

                <tr>
                    <td>{function="localize('Server load', 'cache')"}:</td>
                    <td>{$value.load_percent}%</td>
                </tr>
            </tbody>
         </table>
      </div>
      {/loop}
      {/if}

     {if="$acp_info != ''"}
      <div class="grid-2">
         <table class="gridTable">

            <thead>
                <tr>
                    <th colspan="2">
                        <a href="#" onclick="createPopup('_ajax.php?display=cache&cat=admin&popup=apc', 1400, 800);">APC</a>
                    </th>
                </tr>
            </thead>

            <tbody>
                <tr>
                    <td>{function="localize('Start time', 'cache')"}:</td>
                    <td>{$acp_info.start_time}</td>
                </tr>

                <tr>
                    <td>{function="localize('Cached files', 'cache')"}:</td>
                    <td>{$acp_info.cached_files} {function="localize('files', 'cache')"}</td>
                </tr>

                <tr>
                    <td>{function="localize('Usage', 'cache')"}:</td>
                    <td>{$acp_info.num_hits} {function="localize('hits', 'cache')"}, {$acp_info.num_misses} {function="localize('misses', 'cache')"}</td>
                </tr>
            </tbody>

         </table>
      </div>
     {/if}