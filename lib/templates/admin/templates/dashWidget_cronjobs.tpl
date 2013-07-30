<div class="grid-2">
           <div class="title-grid">{function="localize('Recent cronjobs', 'dashWidget_cronjobs')"}<span id="widgetRemoveButtons" class="widgetRemoveButtons"><a href="#" onclick="removeWidget('cronjobs')"><img src="{$PANTHERA_URL}/images/admin/list-remove.png" style="height: 15px;"></a></span></div>
           <div class="content-table-grid">
              <table class="insideGridTable">
                {if="count($cronjobsWidgetJobs) > 0"}
                   {loop="$cronjobsWidgetJobs"}
                   <tr>
            	        <td style="width: 75px;">#{$value.count}</td><td>{$value.name} ({$value.timeleft})</td><td>{$value.crontime}</td>
            	   </tr>
                   {/loop}
                {else}
                   <tr>
                        <td colspan="3">{function="localize('No sheduled jobs found', 'dashWidget_cronjobs')"}</td>
                   </tr>
                {/if}
               </table>
                <div class="clear"></div>
           </div>
        </div>
