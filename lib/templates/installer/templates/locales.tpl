{include="buttons"}

<h1>{function="localize('Languages used in your application')"}</h1>

<span class="description">{function="localize('Panthera is offering native support for multiple languages, default preffered language is English that is translated to other languages using our translation system. New languages and its translations can be created using administration panel.', 'installer')"}</span>

<table class="table" style="width: 40%; margin: 0 auto; margin-top: 30px;">
    <thead>
        <tr><td><b>{function="localize('Language', 'installer')"}</b></td><td><b>{function="localize('Site default', 'installer')"}</b></td><td><b>{function="localize('Enabled', 'installer')"}</b></td></tr>
    </thead>

    <tbody>
        {loop="$locales"}
        <tr>
            <td>{if="$value['icon'] == True"}<img src="{$PANTHERA_URL}/images/admin/flags/{$key}.png"> &nbsp;{/if}{$key|ucfirst}</td>
            <td style="width: 30%;"><input type="radio" style="float: right; margin-right: 10px;" onclick="navigateTo('?setDefaultLanguage={$key}');"{if="$value['default'] == True"} checked{/if}></td>
            <td><input type="checkbox" style="float: right; margin-right: 10px;" onclick="navigateTo('?switchLanguage={$key}');"{if="$value['enabled'] == True"} checked{/if}></td>
        </tr>
        {/loop}
        
        <tr>
            <td><img src="{$PANTHERA_URL}/images/admin/flags/english.png"> &nbsp;English</td>
            <td><input type="radio" style="float: right; margin-right: 10px;" onclick="navigateTo('?setDefaultLanguage=english');"{if="$defaultLocale == 'english'"} checked{/if}></td>
            <td><input type="checkbox" style="float: right; margin-right: 10px;" disabled checked></td>
        </tr>
    </tbody>
</table>
