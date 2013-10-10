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
    
        panthera.jsonPOST({ data: '#createNewLanguage', spinner: spinner, async: true, messageBox: 'w2ui', success: function (response) {
                if (response.status == "success")
                    navigateTo('?display=langtool&cat=admin');    
            }
        });
        
        return false;
    })
});

</script>
{/if}

{include="ui.titlebar"}

<div id="popupOverlay" style="text-align: center; padding-top: 20px; padding-bottom: 0px;"></div>

<!-- Content -->
<div class="ajax-content" style="text-align: center;">
    <table style="display: inline-block;">
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
</div>