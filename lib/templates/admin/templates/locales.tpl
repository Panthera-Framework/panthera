{$site_header}

{if="$action == ''"}
<script type="text/javascript">
function localeAction(action, id)
{
    if (action == "add")
        id = jQuery('#locales_dir').val();

    panthera.htmlPOST({ url: '{$AJAX_URL}?display=locales&cat=admin&action='+action, data: 'id='+id, success: function () {
        navigateTo(window.location);
    }
    
    });
}
</script>
{/if}

{include="ui.titlebar"}

<div id="topContent">
    <div class="searchBarButtonArea">
        <input type="button" value="{function="localize('Settings')"}" onclick="panthera.popup.toggle('element:#settings')">
        <input type="button" value="{function="localize('Create language', 'locales')"}" onclick="panthera.popup.toggle('element:#createLanguage')">
    </div>
</div>

<!-- Create language popup -->
<div id="createLanguage" style="display: none;">
    <table class="formTable" style="margin: 0 auto; margin-bottom: 30px;">
        
        <thead>
            <tr>
                <td colspan="2" class="formTableHeader" style="padding-top: 0px; padding-bottom: 30px;">
                    <p style="color: #e5ebef; padding: 0px; margin: 0px; margin-left: 30px;">{function="localize('Add new language', 'locales')"}</p>
                </td>
            </tr>
        </thead>
        
        <tfoot>
            <tr>
                <td colspan="3" style="padding-top: 35px;">
                    <input type="button" value="{function="localize('Cancel')"}" onclick="panthera.popup.close()" style="float: left; margin-left: 30px;">
                    <input type="button" value="{function="localize('Add')"}" style="float: right; margin-right: 30px;" onclick="localeAction('add', ''); return false;">
                </td>
            </tr>
        </tfoot>
        
        <tbody>
          <tr>
            <th>{function="localize('Select language to create', 'locales')"}</th>
            <td>
                <select id="locales_dir">
                    {loop="$locales_dir"}
                    <option value="{$value}">{$value}</option>
                    {/loop}
                </select>
            </td>
          </tr>
        </tbody>
        
    </table>
</div>

<div id="settings" style="display: none;">
    <table class="formTable" style="margin: 0 auto; margin-bottom: 30px;">
        
        <tbody>
          <tr>
            <th>{function="localize('Website default language', 'locales')"}:</th>
            <th>{$locale_system_default}</th>
          </tr>
          
          <tr>
            <th>{function="localize('Tools', 'locales')"}:</th>
            <th><input type="button" value="{function="localize('Translations editor', 'langtool')"}" onclick="navigateTo('?display=langtool&cat=admin');"></th>
          </tr>
        </tbody>
        
    </table>
</div>

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
                <tr {if="$value.visibility == False"} style="opacity: 0.5;" {/if}>
                    <td style='width: 30px;'><img {if="$value.flag == True"}src='{$PANTHERA_URL}/images/admin/flags/{$key}.png'{else} src="" {/if}></td>
                    <td style="width: 200px;">{$key}</td>
                    <td>
                        <a href="#delete" onclick="localeAction('delete', '{$key}'); return false;"><img src="{$PANTHERA_URL}/images/admin/ui/delete.png" style="max-height: 22px;" alt="{function="localize('Remove')"}"></a>
                        <a href="#toggle-visibility" onclick="localeAction('toggleVisibility', '{$key}');"><img src="{$PANTHERA_URL}/images/admin/menu/search.png" style="max-height: 22px;" title="{function="localize('Toggle visibility', 'locales')"}"></a>
                        <a href="#default" onclick="localeAction('setAsDefault', '{$key}');"><img src="{$PANTHERA_URL}/images/admin/menu/star.png" style="max-height: 22px;" title="{function="localize('Set as default', 'locales')"}"></a>
                    </td>
            {/loop}

        </tbody>

    </table>
</div>