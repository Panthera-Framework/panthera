{$site_header}
<script src="{$PANTHERA_URL}/js/admin/raphael-min.js"></script>
<script src="{$PANTHERA_URL}/js/admin/charts.min.js"></script>
<script type="text/javascript">
// Spinners
var spinner = new panthera.ajaxLoader($('#cacheVariables'));
var apc_cache = new panthera.ajaxLoader($('#apc_window'));
var addMemcachedServerSpinner = new panthera.ajaxLoader($('#addMemcachedServerDiv'));
var addRedisServerSpinner = new panthera.ajaxLoader($('#addRedisServerDiv'));

/**
  * Clear variables cache
  *
  * @author Mateusz Warzyński
  */

function clearVariablesCache()
{
        panthera.jsonPOST( { url: '?display=cache&cat=admin&action=clearVariablesCache', data: '', spinner: apc_cache, success: function (response) {
                if (response.status == 'success')
                {
                    $('#cl1').slideUp();
                    $('#cl1').slideDown();
                }
            }
        });

        return false;
}

/**
  * Clear files cache
  *
  * @author Mateusz Warzyński
  */

function clearFilesCache()
{
        panthera.jsonPOST( { url: '?display=cache&cat=admin&action=clearFilesCache', data: '', spinner: apc_cache, success: function (response) {
                if (response.status == 'success')
                {
                    $('#cl2').slideUp();
                    $('#cl2').slideDown();
                }
            }
        });

        return false;
}


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
  * Clear memcached server
  *
  * @author Mateusz Warzyński
  */

function clearMemcachedCache(id)
{
    panthera.jsonPOST({ url: '?display=cache&cat=admin&action=clearMemcachedCache&id='+id, data: '', success: function (response) {
        if (response.status == "success") 
        {
            $('#button_'+id).animate({ height:'toggle'});
            $('#button_'+id).animate({ height:'toggle'});
            navigateTo('?display=cache&cat=admin');
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

function clearXCache (cacheID)
{
    panthera.jsonPOST( { url: '?display=cache&cat=admin&action=clearXCache', data: 'cacheID='+cacheID, spinner: new panthera.ajaxLoader($('#xcacheWindow_'+cacheID)), success: function (response) {
            if (response.status == "success")
            {
                window.setTimeout("navigateTo('?display=cache&cat=admin')", 800);
            }    
        }
    });
}

function removeRedisServer(address)
{
    panthera.jsonPOST( { url: '?display=cache&cat=admin&action=removeRedisServer', data: 'address='+address, spinner: addRedisServerSpinner, success: function (response) {
            if (response.status == "success")
            {
                window.setTimeout("navigateTo('?display=cache&cat=admin')", 800);
            }    
        }
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

    $('#addRedisServer').submit(function () {
        panthera.jsonPOST( { data: '#addRedisServer', spinner: addRedisServerSpinner, success: function (response) {
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

{include="ui.titlebar"}

<div id="topContent">
    <div class="searchBarButtonArea">
        {if="$memcacheAvaliable == True"}<input type="button" value="{function="localize('Add memcached server', 'cache')"}" onclick="panthera.popup.toggle('element:#addMemcachedServerDiv')">{/if}
        {if="isset($redisInfo)"}<input type="button" value="{function="localize('Add Redis cache', 'cache')"}" onclick="panthera.popup.toggle('element:#addRedisServerDiv')">{/if}
    </div>
</div>

<!-- Content -->
<div class="ajax-content" style="text-align: center;">
    <table style="display: inline-block;">
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
            <tr>
                <td><b>session.serialize_handler</b><br><small>({function="localize('User session storage mechanism, by default PHP is using its own serialize method, but there are also faster methods like igbinary', 'cache')"})</small></td>
                <td>
                    {$sessionSerializer}
                </td>
            </tr>
            {if="isset($memcachedSerializer)"}
            <tr>
                <td><b>memcached.serializer</b><br><small>({function="localize('Memcached is storing data in special containers using a serializer mechanism, by default its using PHP serializer, but there are also faster methods like igbinary', 'cache')"})</small></td>
                <td>
                    {$memcachedSerializer}
                </td>
            </tr>
            {/if}
            {if="isset($memcachedCompression)"}
            <tr>
                <td><b>memcached.compression_type</b><br><small>({function="localize('Compression used to store Memcached data, zlib provides better compression but fastlz better performance', 'cache')"})</small></td>
                <td>
                    {$memcachedCompression}
                </td>
            </tr>
            {/if}
            <tr>
                <td colspan="2"><a href="?display=settings.cachetime&cat=admin" class="ajax_link"><b>{function="localize('Cache life time settings', 'cache')"}</b></a><br><small>({function="localize('Set life time of cached entries per module', 'cache')"})</small></td>
            </tr>
        </tbody>
    </table>
    
    

  <!-- Adding new Memcached server -->
  <div id="addMemcachedServerDiv" style="display: none;">
  {if="$memcacheAvaliable == True"}
   <div style="text-align: center;">
        <form action="?display=cache&cat=admin&action=addMemcachedServer" method="POST" id="addMemcachedServer">
            <table class="formTable" style="display: inline-block;">
                <thead>
                    <tr>
                        <th colspan="2" style="width: 250px;">
                            <p style="color: #e5ebef; padding: 0px; margin: 0px; margin-left: 30px;">{function="localize('Add memcached server', 'cache')"}</p>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th>{function="ucfirst(localize('address', 'cache'))"}</th>
                        <td><input type="text" name="ip" placeholder="{function="localize('address', 'cache')"}" style="width: 110px;"></td>
                    </tr>
                    <tr>
                        <th>{function="ucfirst(localize('port', 'cache'))"}</th>
                        <td><input type="text" name="port" placeholder="{function="localize('port', 'cache')"}" style="width: 60px;"></td>
                    </tr>
                    <tr>
                        <th>{function="ucfirst(localize('priority', 'cache'))"} ({function="localize('optional', 'cache')"})</th>
                        <td><input type="number" name="priority" placeholder="{function="localize('priority', 'cache')"} ({function="localize('optional', 'cache')"})" style="width: 90px;"></td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="2" style="padding-top: 35px;">
                            <input type="button" value="{function="localize('Cancel')"}" onclick="panthera.popup.close()" style="float: left; margin-left: 30px;">
                            <input type="submit" value="{function="localize('Add')"}" style="float: right; margin-right: 30px;">
                        </td>
                    </tr>
                </tfoot>
            </table>
        </form>
    </div>
   
   <script type="text/javascript">
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
    </script>
  {/if}
  </div>
    
  {if="isset($redisInfo)"}
   <div style="display: none;" id="addRedisServerDiv">
    <form action="?display=cache&cat=admin&action=addRedisServer" method="POST" id="addRedisServer">
    <table style="display: inline-block;">
        <thead class="formTable">
            <tr>
                <th colspan="2" style="width: 250px;">
                    <p style="color: #e5ebef; padding: 0px; margin: 0px; margin-left: 30px;">{function="localize('Add Redis cache', 'cache')"}</p>
                </th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="2" style="text-align: center;">
                    
                        
                        <input type="submit" value="{function="localize('Add')"}">
                    </form>
                </td>
                
            </tr>
            
            
            {loop="$redisServers"}
            <tr>
                <td style="text-align: center;">{if="$value['socket'] != False"}{$value.socket}{else}{$value.host}:{$value.port} {if="$value['persistent'] == True"}({function="localize('persistent connection', 'cache')"}){/if}{/if}</td>
                <td><a href="#" onclick="removeRedisServer('{$value.host}:{$value.port}')">{function="localize('Remove')"}</a></td>
            </tr>
            {/loop}
        </tbody>
        
        <tbody>
            <tr>
                <th>{function="ucfirst(localize('address', 'cache'))"}</th>
                <td><input type="text" name="ip" placeholder="{function="localize('address', 'cache')"}" style="width: 110px;"></td>
            </tr>
            <tr>
                <th>{function="ucfirst(localize('port', 'cache'))"}</th>
                <td><input type="text" name="port" placeholder="{function="localize('port', 'cache')"}" style="width: 60px;"></td>
            </tr>
            <tr>
                <th>{function="localize('Persistent connection', 'cache')"}</th>
                <td>
                    <select name="persistent">
                        <option value="1">{function="localize('Persistent connection', 'cache')"}</option>
                        <option value="">{function="localize('Normal connection', 'cache')"}</option>
                    </select>
                </td>
            </tr>
        </tbody>
    </table>
    </form>
   </div><br>
  {/if}

    <!-- separator -->
    <div style="height: 1px; margin-top: 30px;"></div>

  {if="count($memcachedServers) > 0"}
  {if="count($memcachedServers) > 1"}
    <!-- charts -->
    <table style="display: inline-block; margin-bottom: 60px;">
        <thead>
            <tr><th>{function="ucfirst(localize('Memcached statistics', 'cache'))"} - {function="localize('Server load', 'cache')"}</th></tr>
        </thead>
        
        <tbody>
            <tr>
                <td><div id='memcachedChart' style='width: 480px; height: 188px; margin-bottom: 10px;'></div></td>
            </tr>
        </tbody>
    </table><br>
  {/if}
    
  {if="isset($redisInfo)"}
    <div style="height: 1px; margin-bottom: 30px;"></div>
    <div id="redisWindow">
        <table>
            <thead>
                <tr>
                    <th colspan="2">
                        {function="localize('Redis server', 'cache')"}
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{function="localize('Host', 'cache')"}:</td>
                    <td>{$redisInfo.hosts}</td>
                </tr>
                <tr>
                    <td>{function="localize('Uptime', 'cache')"}:</td>
                    <td>{$redisInfo.uptime}</td>
                </tr>
                <tr>
                    <td>{function="localize('Used memory', 'cache')"}:</td>
                    <td>{$redisInfo.usedMemory}</td>
                </tr>
                <tr>
                    <td>{function="localize('Elements', 'cache')"}:</td>
                    <td>{$redisInfo.hits} {function="localize('hits', 'cache')"}, {$redisInfo.misses} {function="localize('misses', 'cache')"}, {$redisInfo.expiredKeys} {function="localize('expired', 'cache')"}</td>
                </tr>
                <tr>
                    <td>{function="localize('Role', 'cache')"}:</td>
                    <td>{$redisInfo.role}, {$redisInfo.slaves} {function="localize('connected slaves', 'cache')"}</td>
                </tr>
                <tr>
                    <td>{function="localize('Version', 'cache')"}:</td>
                    <td>Redis {$redisInfo.version}</td>
                </tr>
                <tr>
                    <td>{function="localize('CPU usage', 'cache')"}:</td>
                    <td>{$redisInfo.cpu}</td>
                </tr>
                <tr>
                    <td>{function="localize('Operating system', 'cache')"}:</td>
                    <td>{$redisInfo.os} @ {$redisInfo.arch} bit</td>
                </tr>
            </tbody>
        </table>
    </div>
  {/if}

    <!-- list of servers -->
  {loop="$memcachedServers"}
    <div style="margin-bottom: 30px; display: inline-block;" id="server_{$value.num}">
        <table>
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
                {if="count($memcachedServers) > 1"}
                <tr>
                    <td>{function="localize('Server load', 'cache')"}:</td>
                    <td>{$value.load_percent}%</td>
                </tr>
                {/if}
            </tbody>
        </table>
        <input type="button" value="{function="localize('Clear cache of this server', 'cache')"}" onclick="clearMemcachedCache({$value.num});" style="float: right; margin-right: 31px; margin-top: 10px;" id="button_{$value.num}">
    </div>
   {/loop}
  {/if}

  {if="$acp_info != ''"}
    <div id="apc_window" style="display: inline-block; margin-bottom: 15px;">
        <table>
            <thead>
                <tr>
                    <th colspan="2">
                        <a href="#" onclick="createPopup('_ajax.php?display=cache&cat=admin&popup=apc', 1400, 800);">APC</a>
                    </th>
                </tr>
            </thead>
            <tfoot style="background: transparent;">
                <tr>
                    <td colspan="2" class="rounded-foot-left">
                        <em>
                        <input type="button" value="{function="localize('Clear variables cache', 'cache')"}" onclick="clearVariablesCache();" style="float: right; margin-right: 7px;" id="cl1">
                        <input type="button" value="{function="localize('Clear files cache', 'cache')"}" onclick="clearFilesCache();" style="float: right; margin-right: 7px;" id="cl2">
                        </em>
                    </td>
                </tr>
            </tfoot>
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
     
 {if="isset($xcacheInfo)"}
   {loop="$xcacheInfo"}
    <div style="display: inline-block;" id="xcacheWindow_{$key}">
        <table>
            <thead>
                <tr>
                    <th colspan="2">
                        XCache #{$key}
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{function="localize('Items', 'cache')"}:</td>
                    <td>{$value.cached} / {$value.slots}, {$value.deleted} {function="localize('deleted', 'cache')"}</td>
                </tr>
                <tr>
                    <td>{function="localize('Used memory', 'cache')"}:</td>
                    <td>{$value.used} / {$value.size}</td>
                </tr>
                <tr>
                    <td>{function="localize('Errors', 'cache')"}:</td>
                    <td>{$value.errors}</td>
                </tr>
                <tr>
                    <td>{function="localize('Requests', 'cache')"}:</td>
                    <td>{$value.hits} {function="localize('hits', 'cache')"}, {$value.misses} {function="localize('misses', 'cache')"}</td>
                </tr>
                <tr>
                    <td colspan="2">
                        {function="localize('Hourly usage', 'cache')"}:
                        <div style="text-align: center; width: 800px; margin: 0 auto;">
                            <div id='xcacheBar_{$key}' style='width: 900px; height: 188px; margin-bottom: 10px;'></div>
                            <script type="text/javascript">
                                var xcacheBar_{$key} = new Charts.BarChart('xcacheBar_{$key}', {
                                  show_grid: true,
                                  label_max: false,
                                  label_min: false,
                                  x_label_color: "#333333",
                                  bar_width: 14,
                                  rounding: 3,
                                });
                                
                                {$xkey=$key}
                                
                                {loop="$value['hourlyStats']"}
                                color = rgbToHex(0, 170*{$key}, 221*{$key})
                                
                                xcacheBar_{$xkey}.add({
                                  label: "{$key}",
                                  value: {$value},
                                  options: {
                                    bar_color: color
                                  }
                                });
                                {/loop}
                                
                                xcacheBar_{$key}.draw();
                            </script>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
        <input type="button" value="{function="localize('Clear cache', 'cache')"}" onclick="clearXCache('{$key}');" style="float: right; margin-right: 31px; margin-top: 10px;">
    </div><br>
  {/loop}
 {/if}
</div>
