{$site_header}
{include="ui.titlebar"}

<script type="text/javascript">
function manageJob(jobid, action)
{
    panthera.jsonPOST({url: '?display=crontab&cat=admin&action='+action, data: 'jobid='+jobid, success: function (response) {
            if (response.status == 'success')
            {
                //$('#job_id_'+jobid).remove();
                navigateTo(window.location.href);
            }
        }
    });
}
</script>

<div id="topContent">
    {$uiSearchbarName="uiTop"}
    {include="ui.searchbar"}
    
    <div class="separatorHorizontal"></div>
    
    <div class="searchBarButtonArea">
        <input type="button" value="{function="localize('Configure crontab', 'crontab')"}" onclick="panthera.popup.toggle('element:#configureCrontab')">
        <input type="button" value="{function="localize('Add new job', 'crontab')"}" onclick="panthera.popup.toggle('element:#addNewJobPopup')">
    </div>
</div>

<!-- Adding new cronjob -->
<div style="display: none;" id="addNewJobPopup">
    <form action="{$AJAX_URL}?display=crontab&cat=admin&action=postANewJob" method="POST" id="postANewJob">
        <table class="formTable" style="margin: 0 auto; margin-bottom: 25px; margin-top: 25px;">
             <thead>
                 <tr>
                    <td colspan="2" class="formTableHeader" style="padding-top: 0px; padding-bottom: 30px;">
                        <p style="color: #e5ebef; padding: 0px; margin: 0px; margin-left: 30px;">{function="localize('Add new job', 'custompages')"}</p>
                    </td>
                 </tr>
             </thead>
             
              <tbody>
                    <tr>
                        <th>{function="localize('Job name', 'crontab')"}:</th>
                        <td><input type="text" name="jobname"></td>
                    </tr>
                    
                    <tr>
                        <th>{function="localize('Class name', 'crontab')"}:</th>
                        <td><div class="ui-widget"><input type="text" name="class" id="className"></div></td>
                    </tr>
                    
                    <tr>
                        <th>{function="localize('Function name', 'crontab')"}:</th>
                        <td><div class="ui-widget"><input type="text" name="function" id="functionName"></div></td>
                    </tr>
                    
                    <tr>
                        <th colspan="2">&nbsp;</th>
                    </tr>
                    
                    <tr>
                        <th>{function="localize('Time', 'crontab')"}:</th>
                        <td><select id="timing">
                                <option value="*/1 * * * * *">{function="localize('every 1 minute', 'crontab')"}</option>
                                <option selected value="*/5 * * * * *">{function="slocalize('every %s minutes', 'crontab', 5)"}</option>
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
                        <td><input type="text" name="time_minute" id="time_minute" value="*/5"></td>
                    </tr>
                    
                    <tr>
                        <th>{function="localize('Hour', 'crontab')"}:</th>
                        <td><input type="text" name="time_hour" id="time_hour" value="*"></td>
                    </tr>
                    
                    <tr>
                        <th>{function="localize('Day', 'crontab')"}:</th>
                        <td><input type="text" name="time_day" id="time_day" value="*"></td>
                    </tr>
                    
                    <tr>
                        <th>{function="localize('Month', 'crontab')"}:</th>
                        <td><input type="text" name="time_month" id="time_month" value="*"></td>
                    </tr>
                    
                    <tr>
                        <th>{function="localize('Weekday', 'crontab')"}:</th>
                        <td><input type="text" name="time_weekday" id="time_weekday" value="*"></td>
                    </tr>
                    
                    <tr>
                        <th>{function="localize('Year', 'crontab')"}:</th>
                        <td><input type="text" name="time_year" id="time_year" value="*"></td>
                    </tr>
                    
                    <tr>
                        <th colspan="2">&nbsp;</th>
                    </tr>
                    
                    <tr>
                        <th>{function="localize('Data', 'crontab')"}:</th>
                        <td><textarea style="width: 200px; height: 150px;" name="jobdata"></textarea></td>
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
    
    <script type="text/javascript">
    /**
      * Submit add_page form
      *
      * @author Mateusz Warzyński
      */

    $('#postANewJob').submit(function () {
        panthera.jsonPOST({ data: '#postANewJob', mce: 'tinymce_all', success: function (response) {
                if (response.status == "success")
                    navigateTo("?display=crontab&cat=admin");
            }
        });

        return false;

    });
    
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
    
    $( "#className" ).autocomplete({
      source: [
          {loop="$autoloadClasses"}
          "{$key}",
          {/loop}
      ]
    });
    
    $( "#functionName").click(function() {
        $( "#functionName" ).autocomplete({
          source: function (request, uiResponse) {
            //query = request.term;
            
            panthera.jsonPOST({url: '?display=crontab&cat=admin&action=getClassFunctions', data: 'className='+$('#className').val(), success: function (response) 
            {
                if (response.status == 'success')
                    uiResponse(response.result);
            }});
          }
        });
    
    });
    </script>
</div>



<!-- Configure crontab -->
<div style="display: none;" id="configureCrontab">
    <div style="margin-left: 100px; margin-right: 100px; color: white;">
            <h1>{function="localize('Job scheduler configuration', 'installer')"}</h1>
            <div style="margin-left: 5px;"><span>{function="localize('Crontab is a very powerful job scheduling module, used to execute huge operations such as massive mailing, template compiling. It\'s very important to configure it properly, because many built-in Panthera Framework features depends on this module', 'installer')"}.</span></div>

        <p style="margin-top: 20px;"><b>{function="localize('Crontab syntax entries', 'installer')"}</b></p>
        
        <p><span class="description">{function="localize('Internal crontab module placed inside of Panthera Framework does not have possibility to work as a daemon, so it requires a real Unix-like crontab to work and to execute jobs in correct time', 'installer')"}.</span></p>
        
        <p style="margin-top: 5px; color: #C2C2C2;"><code>
        <i># {function="localize('open crontab joblist using text editor', 'installer')"}<br>
        crontab -e</i>
        </code></p>
        
        <p style="margin-top: 10px;"><span class="description">{function="localize('Please paste one of those commands to you\'r Unix crontab list', 'installer')"}</span></p>
        
        <p style="color: #abc; margin-top: 5px;"><code>
        <i>*/1 * * * * wget --spider -O /dev/null {$crontabUrl} > /dev/null 2> /dev/null</i>
        </code></p>
        
        <p style="color: #C2C2C2; margin-top: 5px;"><code>
        <i>*/1 * * * * curl {$crontabUrl} > /dev/null 2>/dev/null</i>
        </code></p>
        
        <p style="margin-top: 20px;"><b>{function="localize('Secret key', 'installer')"}</b></p>
        
        <p><span class="description">{function="localize('To protect access to crontab module there is a requirement to generate and provide a secret key, it can be called a crontab password. So, your key is', 'installer')"}:</span></p>
        
        <p style="color: #C2C2C2; margin-top: 5px;"><code>
        <i>{$crontabKey}</i><span style="float: right;"><a href="#" onclick="navigateTo('?display=crontab&cat=admin&action=save')">{function="localize('Generate new', 'installer')"}</a></span>
        </code></p>
        
        <p style="margin-top: 20px;"><b>{function="localize('Commands to manually invoke a new thread of crontab from shell', 'installer')"}</b></p>
        
        <p><span class="description">{function="localize('You can use those shell commands to runtime check all cronjobs and execute planned for this moment', 'installer')"}</span></p>
        
        <p style="color: #C2C2C2; margin-top: 5px;"><code>
        <i># {function="localize('using', 'installer')"} wget<br>
        wget --spider -O /dev/null {$crontabUrl} > /dev/null 2> /dev/null<br></i>
        </code></p>
        
        <p style="color: #C2C2C2; margin-top: 5px; margin-bottom: 35px;"><code>
        <i># {function="localize('using', 'installer')"} curl<br>
        curl {$crontabUrl} > /dev/null 2>/dev/null<br></i>
        </code></p>
    </div>
</div>




<div class="ajax-content" style="text-align: center;">
    <div style="display: inline-block; margin: 0 auto;">
        <table>
            <thead>
                <tr>
                    <th>{function="localize('id', 'crontab')"}</th>
                    <th>{function="localize('Job name', 'crontab')"}</th>
                    <th>{function="localize('Crontab string', 'crontab')"}</th>
                    <th>{function="localize('Execution count', 'crontab')"}</th>
                    <th>{function="localize('Count left', 'crontab')"}</th>
                    <th>{function="localize('Next iteration time', 'crontab')"}</th>
                    <th>{function="localize('Created', 'crontab')"}</th>
                    <th>{function="localize('Enabled', 'crontab')"}</th>
                    <th>{function="localize('Options', 'custompages')"}</th>
                </tr>
            </thead>
            
            <tbody class="hovered">
                {if="count($cronjobs) > 0"}
                {loop="$cronjobs"}
                <tr id="job_id_{$value.id}" style="{if="!$value.enabled"}opacity: 0.4;{/if}">
                    <td>{$value.id}</td>
                    <td><a href="?display=crontab&cat=admin&action=jobDetails&jobid={$value.id}" class="ajax_link">{$value.name}</a></td>
                    <td>{$value.crontab_string}</td>
                    <td>#{$value.count_executed}</td>
                    <td>{$value.count_left}</td>
                    <td>{$value.next_iteration}</td>
                    <td>{$value.created}</td>
                    <td>{if="$value.enabled"}{function="localize('Yes')"}{else}{function="localize('No')"}{/if}</td>
                    <td>
                        <a href="#" onclick="manageJob({$value.id}, 'removeJob')">
                            <img src="{$PANTHERA_URL}/images/admin/ui/delete.png" style="max-height: 22px;" alt="{function="localize('Remove', 'messages')"}">
                        </a>
                        
                        <a href="#" onclick="manageJob({$value.id}, 'toggleEnabled')">
                            <img src="{$PANTHERA_URL}/images/admin/tango-icon-theme/System-search.svg" style="max-height: 22px;" alt="{function="localize('Remove', 'messages')"}">
                        </a>
                    </td>
                </tr>
                {/loop}
                {else}
                <tr>
                    <td colspan="7" style="text-align: center;">{function="localize('No any jobs found', 'crontab')"}</td>
                </tr>
                {/if}
            </tbody>
        </table>
        
        <div style="position: relative; text-align: left;" class="pager">{$uiPagerName="adminCronjobs"}{include="ui.pager"}</div>
    </div>
</div>
