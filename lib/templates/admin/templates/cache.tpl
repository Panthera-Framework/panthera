{$site_header}
<script>
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
          }
        }
    });
    return false;
}

</script>

    <div class="titlebar">{function="localize('Cache management', 'cache')"} {include="_navigation_panel.tpl"}</div><br>
    
    <div class="grid-1" style="position: relative;" id="cacheVariables">
         <table class="gridTable">

            <thead>
                <tr>
                    <th scope="col" class="rounded-company" style="width: 250px;">{function="localize('Key')"}</th>
                    <th colspan="2">{function="localize('Value')"}</th>
                </tr>
            </thead>

            <tfoot>
                <tr>
                    <td colspan="2" class="rounded-foot-left"><em>Panthera - {function="localize('Cache management', 'cache')"} <input type="button" value="{function="localize('Save')"}" onclick="saveCacheVariables();" id="save_button" style="float: right; margin-right: 7px;"></em></td>
                </tr>
            </tfoot>
            
            <tbody>
                <tr>
                    <td>cache</td>
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
                    <td>varCache</td>
                    <td>
                        <select id="varcache">
                          {loop="$cache_list"}
                             <option {if="$cache == $key"} selected {/if}>{$key}</option>
                          {/loop}
                       </select>
                    </td>
                </tr>
            </tbody>
         </table>
      </div>
      
    <div class="grid-1">
         <table class="gridTable">

            <thead>
                <tr>
                    <th scope="col" class="rounded-company">{function="localize('Server', 'cache')"}</th>
                    <th colspan="2">{function="localize('Port', 'cache')"}</th>
                </tr>
            </thead>

            <tfoot>
                <tr>
                    <td colspan="2" class="rounded-foot-left"><em>Panthera - {function="localize('Server list', 'cache')"} </em></td>
                </tr>
            </tfoot>
            
            <tbody>
              {loop="$servers"}
                <tr>
                    <td><a href="#" onclick="createPopup('_ajax.php?display=cache&popup=stats&server={$value.host}&port={$value.port}', 1000, 'server_stats');">{$value.host}</a></td>
                    <td>{$value.port}</td>
                </tr>
              {/loop}
            </tbody>
         </table>
      </div>