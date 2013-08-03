{$site_header}
{if="$action == ''"}
<script type="text/javascript">
function localeAction(action, id)
{
    if (action == "add")
        id = jQuery('#locales_dir').val();

    panthera.htmlPOST({ url: '{$AJAX_URL}?display=locales&cat=admin&action='+action, data: 'id='+id, success: '#locale_window' });
}

$(document).ready(function () {
    /**
      * Adding new language
      *
      * @author Damian Kęska
      */

    $('#newLanguageGrid').submit(function () {
        spinner = new panthera.ajaxLoader($('#newLanguageGrid'));
    
        panthera.jsonPOST({ data: '#createNewLanguage', spinner: spinner, async: true, messageBox: 'userinfoBox', success: function (response) {
                if (response.status == "success")
                    navigateTo('?display=langtool&cat=admin');    
            }
        });
        
        return false;
    })
});

</script>
{/if}

<script>$('.ajax_link').click(function (event) { event.preventDefault(); navigateTo(jQuery(this).attr('href')); return false;});</script>
<div id="locale_window">
    <div class="titlebar">{function="localize('Language settings', 'locales')"} - {function="localize('Manage site localization', 'locales')"}{include="_navigation_panel"}</div>
    <div class="msgSuccess" id="userinfoBox_success"></div>
    <div class="msgError" id="userinfoBox_failed"></div>

    <!-- Table #1: languages list -->
    <table class="gridTable">
        <thead>
            <tr><th colspan="3"><b>{function="localize('Languages', 'locales')"}:</b></th></tr>
         </thead>

        <br>

        <tbody>
            {loop="$locales_added"}
                <tr>
                    {if="$value.flag == True"}<td style='width: 30px;'><img src='{$PANTHERA_URL}/images/admin/flags/{$key}.png'></td>{/if}
                    <td>{$key}</td>
                    <td><input type="button" value="{function="localize('Delete')"}" onclick="localeAction('delete', '{$key}'); return false;">

                    {if="$value.visibility == True"}
                    <input type="button" value="{function="localize('Hide')"}" onclick="localeAction('toggle_visibility', '{$key}');">
                    {else}
                    <input type="button" value="{function="localize('Show')"}" onclick="localeAction('toggle_visibility', '{$key}');">
                    {/if}

                <input type="button" value="{function="localize('Set as default')"}" onclick="localeAction('set_as_default', '{$key}');"></td></tr>
            {/loop}

        </tbody>

    </table>

    <br>

    <!-- Table #2: language settongs -->
    <table class="gridTable">
        <thead>
            <tr><th colspan="2"><b>{function="localize('Settings')"}:</b></th></tr>
        </thead>

        <tbody>
            <tr><td>{function="localize('Website default language', 'locales')"}:</td><td>{$locale_system_default}</td></tr>
            <tr>
                <td>{function="localize('Add new language', 'locales')"}:</td><td>
                <select id="locales_dir">
                    {loop="$locales_dir"}
                    <option value="{$value}">{$value}</option>
                    {/loop}
                </select>

                <input type="button" value="{function="localize('Add')"}" onclick="localeAction('add', ''); return false;"></td></tr>
            </tr>
            
            <tr><td>{function="localize('Tools', 'locales')"}:</td><td><input type="button" value="{function="localize('Translations editor', 'langtool')"}" onclick="navigateTo('?display=langtool&cat=admin');"></td></tr>
        </tbody>
        </table>

        <br>

    <table class="gridTable">
        <thead>
            <tr><th colspan="2"><b>{function="localize('Loaded language domains', 'locales')"}:</b></th></tr>
        </thead>
        <tbody>
            {loop="$loaded_domains"}
                <tr><td><b>{$value}</b></td><td>{$key}</td></tr>
            {/loop}
        </tbody>

         <tfoot>
            <tr>
                <td colspan="2" class="rounded-foot-left">
                    <em>Panthera - {function="localize('Languages', 'locales')"}</em>
                </td>
            </tr>
        </tfoot>
    </table>
    
    <div class="grid-2" id="newLanguageGrid" style="position: relative;">
          <div class="title-grid">{function="localize('Add new language', 'langtool')"}<span></span></div>
          <div class="content-table-grid">
              <table class="insideGridTable">
                <tbody>
                    <tr>
                        <form action="?display=langtool&cat=admin&action=createNewLanguage" method="POST" id="createNewLanguage">
                            <td style="border-bottom: 0px;">{function="localize('Language name', 'langtool')"}<br><small>{function="localize('Single word, eg. polski, english, deutsh', 'langtool')"}</small></td>
                            <td style="border-bottom: 0px; border-right: 0px;"><input type="text" name="languageName"> <input type="submit" value=" {function="localize('Add', 'langtool')"} "></td>
                        </form>
                    </tr>
                </tbody>
            </table>
         </div>
       </div>
