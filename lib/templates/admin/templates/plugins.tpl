<script type="text/javascript">
/**
  * Toggle plugin
  *
  * @author Mateusz Warzyński
  */

function togglePlugin(name, value)
{
    panthera.jsonPOST({ url: '?display=plugins&cat=admin&action=toggle&plugin='+name+'&value='+value, messageBox: 'w2ui', success: function (response) {
            if (response.status == "success")
                navigateTo("?display=plugins&cat=admin");
        }
    });

    return false;
}

</script>

<style>
#container {
    background: url("images/admin/menu/Apps-preferences-plugin-icon.png") no-repeat transparent;
    background-size: 80px;
    background-position: 92% 88%;
}
</style>

	 	{include="ui.titlebar"}

        <div class="grid-1">
          <table class="gridTable">
            <thead>
            	<tr>
                	<th colspan="3">{function="ucfirst(localize('plugin', 'plugins'))"}</th>
                	<th style="width: 40%;">{function="localize('Description', 'plugins')"}</th>
                	<th>{function="ucfirst(localize('author', 'plugins'))"}</th>
                	<th>{function="ucfirst(localize('version', 'plugins'))"}</th>
            </thead>
                <tfoot>
            	<tr>
                	<td class="rounded-foot-left" colspan="6"><em>Panthera - {function="localize('Plugins', 'plugins')"}</em></td>
                </tr>
            </tfoot>
            <tbody>
              {loop="$plugins"}
                <tr>
                    <td style="width: 1%; border-right: 0px;">
                    {if="$value.configuration != ''"}<a href='{$value.configuration}' class='ajax_link'><img src='{$PANTHERA_URL}/images/admin/menu/settings.png' style='width: 16px;'></a>{/if}</td>
                    <td style="width: 1%; border-right: 0px;"><a href="#" onclick="togglePlugin('{$value.name}', {if="$value.enabled == True"}0{else}1{/if});">
                    {if="$value.enabled == 1"} <img src='{$PANTHERA_URL}/images/plugin-enabled.png'> {else} <img src='{$PANTHERA_URL}/images/plugin-disabled.png'> {/if}</a></td>
                	<td><a href="#" onclick="togglePlugin('{$value.name}', {if="$value.enabled == True"}0{else}1{/if});">{$value.title} <span class='tooltip'>{function="ucfirst(localize('directory'))"}: {$value.path}<br>{function="ucfirst(localize('version'))"}: {$value.version}<br>{function="ucfirst(localize('author'))"}: {$value.author}<br>{function="localize('Description')"}: {$value.description}</span></a></td>
                	<td>{$value.description}</td>
                	<td>{$value.author}</td>
                	<td>{$value.version}</td>
                </tr>
              {/loop}
            </tbody>
          </table>
        </div>

        <div style="height: 100px;"></div> <!-- clean space -->
