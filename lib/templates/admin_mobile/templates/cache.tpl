    <nav class="tab-fixed">
      <ul class="tab-inner">
        <li><a href="#" onclick="navigateTo('?display=dash&cat=admin&menu=settings');" data-transition="push">{function="localize('Dash')"}</a></li>
        <li class="active"><a data-ignore="true">{function="localize('Cache management', 'cache')"}</a></li>
      </ul>
    </nav>

    <div class="content inset">
      <div class="slider tab-slider">
        <ul>
            <li id="conftool" class="tab-item active">
                <ul class="list inset">
                   <li class="list-divider">{function="localize('Set preffered cache methods in to caching slots', 'cache')"}</li>

                   <li class="list-item-two-lines">
                      <button class="btn-small" style="float: right; display: none;" id="save_button" onclick="saveCacheVariables()">{function="localize('Save')"}</button>
                      <div>
                           <h3><select id="cache" onchange="$('#save_button').slideDown();">
                             {loop="$cache_list"}
                               {if="$value == True"}
                                 <option {if="$cache == $key"} selected {/if}>{$key}</option>
                               {/if}
                             {/loop}
                           </select></h3>
                           <p>cache ({function="localize('Needs to be really fast, huge amounts of data are stored here. Set only in-memory caching methods here - APC, XCache, Memcached', 'cache')"})</p>
                      </div>
                   </li>

                   <li class="list-item-two-lines">
                      <button class="btn-small" style="float: right; display: none;" id="save_button2" onclick="saveCacheVariables()">{function="localize('Save')"}</button>
                      <div>
                           <h3><select id="varcache" onchange="$('#save_button2').slideDown();">
                             {loop="$cache_list"}
                               {if="$value == True"}
                                 <option {if="$varcache == $key"} selected {/if}>{$key}</option>
                               {/if}
                             {/loop}
                           </select></h3>
                           <p>varcache ({function="localize('Used to store simple variables, this can be a database cache, but if any in-memory cache is avaliable, select it', 'cache')"})</p>
                      </div>
                   </li>

                   <li class="list-item-two-lines">
                      <div>
                           <h3>{$sessionHandler}</h3>
                           <p>session.save_handler ({function="localize('User sessions can be stored on harddrive by default, or in memory using memcached or mm. This can be set in PHP configuration.', 'cache')"})</p>
                      </div>
                   </li>

                   <br><br>

                {if="count($memcachedServers) > 0"}
                  {loop="$memcachedServers"}
                   <li class="list-divider">memcached #{$value.num}</li>

                   <li class="list-item-two-lines">
                      <div>
                           <h3>{$key}, pid: {$value.pid}, {$value.threads} {function="localize('threads', 'cache')"}</h3>
                           <p>{function="localize('Host', 'cache')"}</p>
                      </div>
                   </li>

                   <li class="list-item-two-lines">
                      <div>
                           <h3>{$value.uptime}</h3>
                           <p>{function="localize('Uptime', 'cache')"}</p>
                      </div>
                   </li>

                   <li class="list-item-two-lines">
                      <div>
                           <h3>{$value.version}</h3>
                           <p>{function="localize('Version', 'cache')"}</p>
                      </div>
                   </li>

                   <li class="list-item-two-lines">
                      <div>
                           <h3>{$value.memory_used} / {$value.memory_max}</h3>
                           <p>{function="localize('Used memory', 'cache')"}</p>
                      </div>
                   </li>

                   <li class="list-item-two-lines">
                      <div>
                           <h3>{$value.read} {function="localize('read', 'cache')"}, {$value.written} {function="localize('written', 'cache')"}</h3>
                           <p>{function="localize('Transferred', 'cache')"}</p>
                      </div>
                   </li>
                   <button class="btn-block" onclick="clearMemcachedCache({$value.num});" id="button_{$value.num}">{function="localize('Clear cache of this server', 'cache')"}</button><br> <br>
                  {/loop}
                {/if}

                {if="$acp_info != ''"}
                   <li class="list-divider">APC</li>

                   <li class="list-item-two-lines">
                      <div>
                           <h3>{$acp_info.start_time}</h3>
                           <p>{function="localize('Start time', 'cache')"}</p>
                      </div>
                   </li>

                   <li class="list-item-two-lines">
                      <div>
                           <h3>{$acp_info.cached_files}</h3>
                           <p>{function="localize('Cached files', 'cache')"}</p>
                      </div>
                   </li>

                   <li class="list-item-two-lines">
                      <div>
                           <h3>{$acp_info.num_hits} {function="localize('hits', 'cache')"}, {$acp_info.num_misses} {function="localize('misses', 'cache')"}</h3>
                           <p>{function="localize('Usage', 'cache')"}</p>
                      </div>
                   </li>
                   <button class="btn-block" onclick="clearVariablesCache();" id="cl1">{function="localize('Clear variables cache', 'cache')"}</button>
                   <button class="btn-block" onclick="clearFilesCache();" id="cl2">{function="localize('Clear files cache', 'cache')"}</button>
                   <br> <br>
                {/if}

                   <label>{function="localize('Add memcached server', 'cache')"}</label>
                  <form action="?display=cache&cat=admin&action=addMemcachedServer" method="POST" id="addMemcachedServer">
                   <input type="text" class="input-text" name="ip" placeholder="{function="localize('address', 'cache')"}">
                   <input type="text" class="input-text" name="port" placeholder="{function="localize('port', 'cache')"}">
                   <input type="text" class="input-text" name="priority" placeholder="{function="localize('priority', 'cache')"} ({function="localize('optional', 'cache')"})">
                   <button type="submit" class="btn-block">{function="localize('Add')"}</button>
                  </form>

                </ul>
            </li>
        </ul>
      </div>
    </div>

   <!-- JS code -->
    <script type="text/javascript">

    /**
      * Add new Memcached server
      *
      * @author Damian Kęska
      */

    $('#addMemcachedServer').submit(function () {
        panthera.jsonPOST( { data: '#addMemcachedServer', success: function (response) {
                if (response.status == "success")
                {
                    navigateTo('?display=cache&cat=admin');
                }
            }
        });

        return false;

    });

    /**
      * Clear memcached server
      *
      * @author Mateusz Warzyński
      */

    function clearMemcachedCache(id)
    {
        panthera.jsonPOST({ url: '?display=cache&cat=admin&action=clearMemcachedCache&id='+id, data: '', success: function (response) {
              if (response.status == "success") {
                       jQuery('#button_'+id).animate({ height:'toggle'});
                       jQuery('#button_'+id).animate({ height:'toggle'});
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
            panthera.jsonPOST( { url: '?display=cache&cat=admin&action=clearFilesCache', data: '', success: function (response) {
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
      * Clear variables cache
      *
      * @author Mateusz Warzyński
      */

    function clearVariablesCache()
    {
            panthera.jsonPOST( { url: '?display=cache&cat=admin&action=clearVariablesCache', data: '', success: function (response) {
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
      * Save cache variables
      *
      * @author Mateusz Warzyński
      */

    function saveCacheVariables()
    {
        cache = $('#cache').val();
        varcache = $('#varcache').val();

        panthera.jsonPOST({ url: '?display=cache&cat=admin&action=save', data: 'cache='+cache+'&varcache='+varcache, success: function (response) {
              if (response.status == "success")
              {
                       jQuery('#save_button').slideUp();
                       setTimeout("jQuery('#save_button2').slideUp();", 2500);
              }
            }
        });
        return false;
    }

    </script>
   <!-- End of JS code -->