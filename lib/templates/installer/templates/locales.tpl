{include="buttons"}

<div class="header">
        <h1>{function="localize('Languages used in your application', 'installer')"}</h1>
        <div style="margin-left: 5px;"><span>{function="localize('Panthera is offering native support for multiple languages, default preffered language is English that is translated to other languages using our translation system. New languages and its translations can be created using administration panel.', 'installer')"}</span></div>
</div>

<style>
.greenColumn {
    background: #e7ffd4;
    font-color: black;
    border-bottom: 5px;
}
</style>

<div class="content" style="margin-left: 0px; padding: 0;">
    <table class="table" style="width: 40%; margin: 0 auto; margin-top: 30px;">
        <thead>
            <tr>
                <td>{function="localize('Language', 'installer')"}</td>
                <td>{function="localize('Site default', 'installer')"}</td>
                <td>{function="localize('Enabled', 'installer')"}</td>
            </tr>
        </thead>
    
        <tbody>
            {loop="$locales"}
            <tr>
                <td class="greenColumn" style="color: black; border: 0px;">{if="$value['icon'] == True"}<img src="{$PANTHERA_URL}/images/admin/flags/{$key}.png"> &nbsp;{/if}{$key|ucfirst}</td>
                <td class="greenColumn" style="width: 30%; border: 0px;"><input type="radio" name="language" style="float: right; margin-right: 10px;" onclick="navigateTo('?setDefaultLanguage={$key}');"{if="$value['default'] == True"} checked{/if}></td>
                <td class="greenColumn" style="color: black; border: 0px;"><input type="checkbox" style="float: right; margin-right: 10px;" onclick="navigateTo('?switchLanguage={$key}');"{if="$value['enabled'] == True"} checked{/if}></td>
            </tr>
            {/loop}
            
            <tr>
                <td class="greenColumn" style="color: black; border: 0px; background: #ffffca;"><img src="{$PANTHERA_URL}/images/admin/flags/english.png"> &nbsp;English</td>
                <td class="greenColumn" style="color: black; border: 0px; background: #ffffca;"><input type="radio" name="language" style="float: right; margin-right: 10px;" onclick="navigateTo('?setDefaultLanguage=english');"{if="$defaultLocale == 'english'"} checked{/if}></td>
                <td class="greenColumn" style="color: black; border: 0px; background: #ffffca;"><input type="checkbox" style="float: right; margin-right: 10px;" disabled checked></td>
            </tr>
        </tbody>
    </table>
</div>
