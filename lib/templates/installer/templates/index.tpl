{include="buttons"}

<script type="text/javascript">
function selectTimezone ()
{
    var timezone = $("#timezone").val();
    
    navigateTo('?_timezone='+timezone);
}

function selectLanguage() {
    var language = $("#language").val();
    
    window.location = '?_locale='+language;
}
</script>

<div class="header">
        <h1>{function="localize('Welcome, please choose your localization', 'installer')"}</h1>
        <div style="margin-left: 5px;"><span>{function="localize('It\'s very important to correctly set timezone, it responsible for clock in your application', 'installer')"}.</span></div>
</div>

<div class="content">
   <div id="fields" style="margin-top: 70px;">
    <table style="border: 0;">
      <tbody>
          {if="$languages"}
          <tr>
              <td><p><b>{function="localize('Language', 'installer')"}:</p></b></td>
              <td style="padding-left: 10px;">
                  <select id="language" onchange="selectLanguage();">
                  	<option value=""></option>
                            {loop="$languages"}
                                <option {if="$currentLocale == $key"} selected {/if}>{$key|ucfirst}</option>
                            {/loop}
                   </select>
              </td>
          </tr>
          {/if}
          
          <tr><td>&nbsp;</td></tr>
          
          {if="$timezones"}
          <tr>
              <td><p><b>{function="localize('Timezone', 'installer')"}:</p></b></td>
              <td style="padding-left: 10px;">
                  <p><select id="timezone" onchange="selectTimezone();">
                        {loop="$timezones"}
                            <option {if="$timezone == $key"} selected {/if}>{$key}</option>
                        {/loop}
                  </select>&nbsp;<small>({$timezone}, <i>{$currentTime}</i>)</small></p>
              </td>
          </tr>
          {/if}
      </tbody>
     </table>
   </div>
</div>

