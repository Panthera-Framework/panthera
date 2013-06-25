
{if $action eq ''}
<script type="text/javascript">
function localeAction(action, id)
{
    if (action == "add")
        id = jQuery('#locales_dir').val();

    panthera.htmlPOST({ url: '{$AJAX_URL}?display=locales&action='+action, data: 'id='+id, success: '#locale_window' });
}

function executeShellCommand(command)
{
    panthera.jsonPOST({ url: '{$AJAX_URL}?display=shellutils&exec='+command, success: '#command_output_window'});
    $('#command_output_window').slideDown();
}

</script>
{/if}

<script>$('.ajax_link').click(function (event) { event.preventDefault(); navigateTo(jQuery(this).attr('href')); return false;});</script>
<div id="locale_window">
    <div class="titlebar">{"Language settings"|localize:locales} - {"Manage site localization"|localize:locales}</div>

    <!-- Table #1: languages list -->
    <table class="gridTable">
        <thead>
            <tr><th colspan="3"><b>{"Languages"|localize:locales}:</b></th></tr>
         </thead>

        <br>

        <tbody>
            {foreach from=$locales_added key=k item=v}
                <tr>
                    {if $v.flag == True}<td style="width: 30px;"><img src="{$PANTHERA_URL}/images/admin/flags/{$k}.png"></td>{/if}
                    <td>{$k}</td>
                    <td><input type="button" value="{"Delete"|localize}" onclick="localeAction('delete', '{$k}'); return false;">

                    {if $v.visibility == True}
                    <input type="button" value="{"Hide"|localize}" onclick="localeAction('toggle_visibility', '{$k}');">
                    {else}
                    <input type="button" value="{"Show"|localize}" onclick="localeAction('toggle_visibility', '{$k}');">
                    {/if}

                <input type="button" value="{"Set as default"|localize}" onclick="localeAction('set_as_default', '{$k}');"></td></tr>
            {/foreach}

        </tbody>

    </table>

    <br>

    <!-- Table #2: language settongs -->
    <table class="gridTable">
        <thead>
            <tr><th colspan="2"><b>{"Settings"|localize}:</b></th></tr>
        </thead>

        <tbody>
            <tr><td>{"Website default language"|localize:locales}:</td><td>{$locale_system_default}</td></tr>
            <tr>
                <td>{"Add new language"|localize:locales}:</td><td>
                <select id="locales_dir">
                    {foreach from=$locales_dir key=k item=v}
                    <option value="{$v}">{$v}</option>
                    {/foreach}
                </select>

                <input type="button" value="{"Add"|localize}" onclick="localeAction('add', ''); return false;"></td></tr>
            </tr>
        </tbody>
        </table>

        <br>

    <table class="gridTable">
        <thead>
            <tr><th colspan="2"><b>{"Loaded language domains"|localize:locales}:</b></th></tr>
        </thead>
        <tbody>
            {foreach from=$loaded_domains key=k item=v}
                <tr><td><b>{$v}</b></td><td>{$k}</td></tr>
            {/foreach}
        </tbody>

         <tfoot>
            <tr>
                <td colspan="2" class="rounded-foot-left">
                    <em>Panthera - {"Languages"|localize:locales}
                        <input type="button" value="{"Compile locales"|localize:locales}" onclick="executeShellCommand('build-locales.py');" style="float: right;">&nbsp;&nbsp;
                    </em>
                </td>
            </tr>
        </tfoot>
    </table>

       <div id="command_output_window" class="blueLog" style="display: none;"></div>
