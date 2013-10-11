<table class="dashWidget" style="padding-top: 30px;">
    <thead>
        <tr>
            <th colspan="2">
                <a href="#" onclick="navigateTo('?display=cache&cat=admin')">{function="localize('Memcached', 'dash')"}</a>
                <span id="widgetRemoveButtons" class="widgetRemoveButtons">
                    <a href="#" onclick="removeWidget('memcached')">
                        <img src="{$PANTHERA_URL}/images/admin/list-remove.png" style="height: 15px; float: right; margin-right: 5px;">
                    </a>
                </span>
            </th>
        </tr>
    </thead>
                
    <tbody class="hovered">
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
    </tbody>
</table>
