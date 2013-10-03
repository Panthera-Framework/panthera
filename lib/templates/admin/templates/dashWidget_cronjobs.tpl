<table class="dashWidget">
    <thead>
        <th colspan="3">
            {function="localize('Recent cronjobs', 'dash')"}<span id="widgetRemoveButtons" class="widgetRemoveButtons"><a href="#" onclick="removeWidget('cronjobs')"><img src="{$PANTHERA_URL}/images/admin/list-remove.png" style="height: 15px; float: right; margin-right: 5px;"></a></span>
        </th>
    </thead>
                
    <tbody class="hovered">
        {if="count($cronjobsWidgetJobs) > 0"}
            {loop="$cronjobsWidgetJobs"}
            <tr>
                <td style="width: 60px;">#{$value.count}</td><td>{$value.name} ({$value.timeleft})</td><td>{$value.crontime}</td>
             </tr>
            {/loop}
        {else}
            <tr>
               <td colspan="3" style="text-align: center;">{function="localize('There are no sheduled tasks, is the crontab module enabled?', 'dash')"}</td>
            </tr>
         {/if}
    </tbody>
</table>
