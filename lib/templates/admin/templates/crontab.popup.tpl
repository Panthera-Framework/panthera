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
                {
                    panthera.logging.output('Cronjob saved.');
                    panthera.popup.close();
                }
            }
        });
        
        return false;
    });
});
</script>

<form action="?display=crontab&cat=admin&action=saveJobDetails&jobid={$cronjob.id}" method="POST" id="saveJobDetailsFrom">
<table class="formTable" style="margin: 0 auto; margin-bottom: 30px;">
            <thead>
                <tr>
                    <th colspan="2">{function="slocalize('Editing crontab job id #%s', 'crontab', $cronjob['id'])"}</th>
                </tr>
            </thead>
            
            <tbody>
                {if="$cronjob.name"}
                <tr>
                    <th>{function="localize('Job name', 'crontab')"}:</th>
                    <td><input type="text" name="jobname" value="{$cronjob.name}"></td>
                </tr>
                {/if}
                
                {if="$cronjob.class"}
                <tr>
                    <th>{function="localize('Class name', 'crontab')"}:</th>
                    <td><input type="text" name="class" value="{$cronjob.class}"></td>
                </tr>
                {/if}
                
                {if="$cronjob.function"}
                <tr>
                    <th>{function="localize('Function name', 'crontab')"}:</th>
                    <td><input type="text" name="function" value="{$cronjob.function}"></td>
                </tr>
                {/if}
                
                <tr>
                    <th>&nbsp;</th>
                    <td>
                        <select id="timing">
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
                        </select>
                    </td>
                </tr>
            
                <tr>
                    <th>{function="localize('Minute', 'crontab')"}:</th>
                    <td><input type="text" name="minute" id="time_minute" value="{$cronjob.minute}"></td>
                </tr>
                
                <tr>
                    <th>{function="localize('Hour', 'crontab')"}:</th>
                    <td><input type="text" name="hour" id="time_hour" value="{$cronjob.hour}"></td>
                </tr>
                
                <tr>
                    <th>{function="localize('Day', 'crontab')"}:</th>
                    <td><input type="text" name="day" id="time_day" value="{$cronjob.day}"></td>
                </tr>
                
                <tr>
                    <th>{function="localize('Month', 'crontab')"}:</th>
                    <td><input type="text" name="month" id="time_month" value="{$cronjob.month}"></td>
                </tr>
                
                <tr>
                    <th>{function="localize('Weekday', 'crontab')"}:</th>
                    <td><input type="text" name="weekday" id="time_weekday" value="{$cronjob.weekday}"></td>
                </tr>
                
                <tr>
                    <th>{function="localize('Year', 'crontab')"}:</th>
                    <td><input type="text" name="year" id="time_year" value="{$cronjob.year}"></td>
                </tr>
            </tbody>
            
            <tfoot>
                <tr>
                    <td colspan="3" style="padding-top: 35px;">
                        <input type="button" value="{function="localize('Cancel')"}" onclick="panthera.popup.close()" style="float: left; margin-left: 30px;">
                        <input type="submit" value="{function="localize('Save')"}" style="float: right; margin-right: 30px;">
                    </td>
                </tr>
            </tfoot>
        </table>
</form>
