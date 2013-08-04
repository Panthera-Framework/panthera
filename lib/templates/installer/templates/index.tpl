<script type="text/javascript">
function selectTimezone (timezone, time)
{
    $('#timezone').html(timezone);
    navigateTo('?_timezone='+timezone);
}

function selectLanguage (language, iconAvaliable)
{
    icon = '';
    
    if (iconAvaliable)
    {
        icon = '<img src="{$PANTHERA_URL}/images/admin/flags/'+language+'.png">';
    }

    $('#language').html(icon+' '+ucfirst(language));
    navigateTo('?_locale='+language);
}
</script>

<h1>{function="localize('Welcome, please choose your localization', 'installer')"}</h1>

<div style="margin-top: 50px; margin-left: 35px;">

<div id="languageDropdown" class="dropdown dropdown-tip">
    <ul class="dropdown-menu">
        {loop="$languages"}
            <li><a href="#{$key|ucfirst}" onclick="selectLanguage('{$key}', {$value});"><img src="{$PANTHERA_URL}/images/admin/flags/{$key}.png"> {$key|ucfirst}</a></li>
        {/loop}
    </ul>
</div>

<div id="timezoneDropdown" class="dropdown dropdown-tip dropdown-scroll">
    <ul class="dropdown-menu">
        {loop="$timezones"}
            <li><a href="#{$key}" onclick="selectTimezone('{$key}', '{$value}');">{$key} ({$value})</a></li>
        {/loop}
    </ul>
</div>

<table class="table" style="width: 50%;">
    <tbody>
        <tr>
            <td style="width: 15%;">{function="localize('Language', 'installer')"}:</td>
            <td style="width: 85%;">
                <span class="selectBox" data-dropdown="#languageDropdown" id="language">{if="isset($currentLocaleFlag)"}<img src="{$PANTHERA_URL}/images/admin/flags/{$currentLocale}.png">{/if} {$currentLocale|ucfirst}</span>
            </td>
        </tr>
        <tr>
            <td>{function="localize('Timezone', 'installer')"}:</td>
            <td><span class="selectBox" data-dropdown="#timezoneDropdown" id="timezone">{$timezone}</span></td>
        </tr>
    </tbody>
</table>
</div>
