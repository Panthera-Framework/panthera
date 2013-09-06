<div class="grid-2">
           <div class="title-grid"><a href="#" onclick="navigateTo('?display=cache&cat=admin')">{function="localize('Memcached', 'dash')"}</a><span id="widgetRemoveButtons" class="widgetRemoveButtons"><a href="#" onclick="removeWidget('memcached')"><img src="{$PANTHERA_URL}/images/admin/list-remove.png" style="height: 15px;"></a></span></div>
           <div class="content-table-grid">
              <table class="insideGridTable">
              	
              	{if="count($memcachedServers) > 0"}
              	 {loop="$memcachedServers"}
                   <tr>
            	        <td>memcached #{$value.num}</td><td>{$value.memory_usage}</td>
            	   </tr>
            	 {/loop}
            	{else}
            	   <tr>
                        <td colspan="2" style="text-align: center;">{function="localize('There are no memcached servers.', 'dash')"}</td>
                   </tr>
            	{/if}
            	 
               </table>
                <div class="clear"></div>
           </div>
        </div>
