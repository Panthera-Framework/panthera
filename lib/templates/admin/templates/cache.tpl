{$site_header}
<script src="{$PANTHERA_URL}/js/admin/raphael-min.js"></script>
<script src="{$PANTHERA_URL}/js/admin/charts.min.js"></script>
<script type="text/javascript">
$('.ajax_link').click(function (event) { event.preventDefault(); navigateTo(jQuery(this).attr('href')); return false;});

var spinner = new panthera.ajaxLoader($('#cacheVariables'));

/**
  * Save cache variables to database
  *
  * @author Mateusz Warzyński
  */
    
function saveCacheVariables()
{
    cache = $('#cache').val();
    varcache = $('#varcache').val();
    
    panthera.jsonPOST({ url: '?display=cache&action=save', data: 'cache='+cache+'&varcache='+varcache, spinner: spinner, success: function (response) {
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
    bars.add({
      label: "#{$value.num}",
      value: {$value.load_percent}
    });
    {/loop}

    bars.draw();
    {/if}

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
            </tbody>
         </table>
      </div>
      
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
    <div class="grid-2" style="width: 46%;">
         <table class="gridTable">

            <thead>
                <tr>
                    <th colspan="2"><a href="#" onclick="createPopup('_ajax.php?display=cache&popup=stats&server={$key}', 1000, 720);">memcached #{$value.num}</a></th>
                </tr>
            </thead>

            <tbody>
                <tr>
                    <td>{function="localize('Host', 'cache')"}:</td>
                    <td><a href="#" onclick="createPopup('_ajax.php?display=cache&popup=stats&server={$key}', 1000, 720);">{$key}, pid: {$value.pid}, {$value.threads} {function="localize('threads', 'cache')"}</a></td>
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
