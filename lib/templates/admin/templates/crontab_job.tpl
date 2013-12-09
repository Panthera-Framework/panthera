{$site_header}
<script type="text/javascript" src="{$PANTHERA_URL}/js/Chart.min.js"></script>
{include="ui.titlebar"}

<script type="text/javascript">
$(document).ready(function() {
    $('#timing').change(function () {
        t = $('#timing').val().split(' ');
        
        if (t.length > 0)
        {
            $('#time_minute').val(t[0]);
            $('#time_hour').val(t[1]);
            $('#time_day').val(t[2]);
            $('#time_month').val(t[3]);
            $('#time_weekday').val(t[4]);
            $('#time_year').val(t[5]);
        }
    });
    
    $('#saveJobDetailsFrom').submit(function () {
        panthera.jsonPOST({data: '#saveJobDetailsFrom', success: function(response) 
            {
                if (response.status == 'success')
                    panthera.logging.output('Cronjob saved.');
            }
        });
        
        return false;
    });
});
</script>

<form action="?display=crontab&cat=admin&action=saveJobDetails&jobid={$cronjob.id}" method="POST" id="saveJobDetailsFrom">
<div id="topContent">
    <div class="searchBarButtonArea">
        <div style="float: left; display: inline-block; margin-left: 10px;">
            <input type="button" value="{function="localize('Back to jobs list', 'messages')"}" onclick="navigateTo('?display=crontab&cat=admin')">
        </div>
    
        <input type="submit" value="{function="localize('Save', 'crontab')"}">
    </div>
</div>

<div class="ajax-content" style="text-align: center;">
    <div style="display: inline-block; margin: 0 auto; margin-top: 25px; width: 60%;">
        <table style="width: 100%;">
            <thead>
                <tr>
                    <th colspan="2">{function="slocalize('Editing crontab job id #%s', 'crontab', $cronjob['id'])"}</th>
                </tr>
            </thead>
            
            <tbody class="hovered">
                <tr>
                    <td>{function="localize('Job name', 'crontab')"}:</td>
                    <td><input type="text" name="jobname" value="{$cronjob.name}"></td>
                </tr>
                
                <tr>
                    <td>{function="localize('Class name', 'crontab')"}:</td>
                    <td><input type="text" name="class" value="{$cronjob.class}"></td>
                </tr>
                
                <tr>
                    <td>{function="localize('Function name', 'crontab')"}:</td>
                    <td><input type="text" name="function" value="{$cronjob.function}"></td>
                </tr>
                
                <tr>
                    <td>{function="localize('Crontab string', 'crontab')"}:</td>
                    <td>{$cronjob.crontab_string}</td>
                </tr>
                
                <tr>
                    <td>{function="localize('Execution count', 'crontab')"}:</td>
                    <td>#{$cronjob.count_executed}</td>
                </tr>
                
                <tr>
                    <td>{function="localize('Count left', 'crontab')"}:</td>
                    <td><input type="text" name="count_left" value="{$cronjob.count_left}"></td>
                </tr>
                
                <tr>
                    <td>{function="localize('Next iteration time', 'crontab')"}:</td>
                    <td>{$cronjob.next_iteration}</td>
                </tr>
                
                <tr>
                    <td>{function="localize('Created', 'crontab')"}:</td>
                    <td>{$cronjob.created}</td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <div style="display: inline-block; margin: 0 auto; margin-top: 25px; width: 60%;">
        <table style="width: 100%;">
            <thead>
                <tr>
                    <th colspan="2">{function="localize('Edit execution time', 'crontab')"}</th>
                </tr>
            </thead>
            
            <tbody class="hovered">
                <tr>
                    <td>&nbsp;</td>
                    <td><select id="timing">
                                <option selected></option>
                                <option value="*/1 * * * * *">{function="localize('every 1 minute', 'crontab')"}</option>
                                <option value="*/5 * * * * *">{function="slocalize('every %s minutes', 'crontab', 5)"}</option>
                                <option value="*/10 * * * * *">{function="slocalize('every %s minutes', 'crontab', 10)"}</option>
                                <option value="*/15 * * * * *">{function="slocalize('every %s minutes', 'crontab', 15)"}</option>
                                <option value="*/30 * * * * *">{function="slocalize('every %s minutes', 'crontab', 30)"}</option>
                                <option value="*/45 * * * * *">{function="slocalize('every %s minutes', 'crontab', 45)"}</option>
                                <option></option>
                                <option value="0 */1 * * * *">{function="localize('every 1 hour', 'crontab')"}</option>
                                <option value="0 */2 * * * *">{function="slocalize('every %s hours', 'crontab', 2)"}</option>
                                <option value="0 */6 * * * *">{function="slocalize('every %s hours', 'crontab', 6)"}</option>
                                <option value="0 */12 * * * *">{function="slocalize('every %s hours', 'crontab', 12)"}</option>
                                <option></option>
                                <option value="30 10 */1 * * *">{function="slocalize('10:30 every day', 'crontab', 2)"}</option>
                                <option value="0 0 */2 * * *">{function="slocalize('every %s days', 'crontab', 2)"}</option>
                                <option value="0 0 */4 * * *">{function="slocalize('every %s days', 'crontab', 4)"}</option>
                                <option value="0 0 */6 * * *">{function="slocalize('every %s days', 'crontab', 6)"}</option>
                                <option></option>
                                <option value="0 0 1 */1 * *">{function="localize('every month', 'crontab')"}</option>
                                <option value="0 0 1 1-6 * *">{function="localize('00:00 every 1st of January to June', 'crontab')"}</option>
                                <option></option>
                                <option value="20 21 * * 1 *">{function="localize('09:20 PM every Monday', 'crontab')"}</option>
                                <option value="00 09 * * 5 *">{function="localize('09:00 AM every Friday', 'crontab')"}</option>
                            </select></td>
                </tr>
            
                <tr>
                    <td>{function="localize('Minute', 'crontab')"}:</td>
                    <td><input type="text" name="minute" id="time_minute" value="{$cronjob.minute}"></td>
                </tr>
                
                <tr>
                    <td>{function="localize('Hour', 'crontab')"}:</td>
                    <td><input type="text" name="hour" id="time_hour" value="{$cronjob.hour}"></td>
                </tr>
                
                <tr>
                    <td>{function="localize('Day', 'crontab')"}:</td>
                    <td><input type="text" name="day" id="time_day" value="{$cronjob.day}"></td>
                </tr>
                
                <tr>
                    <td>{function="localize('Month', 'crontab')"}:</td>
                    <td><input type="text" name="month" id="time_month" value="{$cronjob.month}"></td>
                </tr>
                
                <tr>
                    <td>{function="localize('Weekday', 'crontab')"}:</td>
                    <td><input type="text" name="weekday" id="time_weekday" value="{$cronjob.weekday}"></td>
                </tr>
                
                <tr>
                    <td>{function="localize('Year', 'crontab')"}:</td>
                    <td><input type="text" name="year" id="time_year" value="{$cronjob.year}"></td>
                </tr>
            </tbody>
        </table>
    </div>
    
    {if="count($timing) > 1"}
    <div style="display: inline-block; margin: 0 auto; margin-top: 25px; width: 60%;">
        <table style="width: 100%;">
            <thead>
                <tr>
                    <th>{function="localize('Performance', 'crontab')"}</th>
                </tr>
            </thead>
            
            <tbody>
                <tr style="width: 100%;">
                    <td style="padding: 0px; width: 100%; text-align: center; padding: 10px;">
                        <canvas id="myChart" width="600" height="400"></canvas>
                    </td>
                </tr>
            </tbody>
        </table>
        
        <script type="text/javascript">
        var data = {
	        labels : [{loop="$timing"}"{$key}",{/loop}],
	        datasets : [
		        {
			        fillColor : "rgba(151,187,205,0.5)",
			        strokeColor : "rgba(151,187,205,1)",
			        data : [{loop="$timing"}{$value},{/loop}]
		        }
	        ]
        }
        
        //Get context with jQuery - using jQuery's .get() method.
        var ctx = $("#myChart").get(0).getContext("2d");
        //This will get the first returned node in the jQuery collection.
        var myNewChart = new Chart(ctx).Bar(data);
        </script>
    </div>
    {/if}
    
    <div style="display: inline-block; margin: 0 auto; margin-top: 25px; width: 60%;">
        <table style="width: 100%;">
            <thead>
                <tr>
                    <th>{function="localize('Last execution log', 'crontab')"}</th>
                </tr>
            </thead>
            
            <tbody class="hovered">
                <tr style="width: 100%;">
                    <td style="padding: 0px; width: 100%;"><textarea style="width: 98.5%; height: 600px;">{$cronjob.log}</textarea></td>
                </tr>
            </tbody>
        </table>
    </div>
    </form>
</div>
