<script type="text/javascript">
$('.ajax_link').click(function (event) { event.preventDefault(); navigateTo(jQuery(this).attr('href')); return false;});

/**
  * Toggle plugin
  *
  * @author Mateusz Warzyński
  */

function togglePlugin(name, value)
{
    panthera.jsonPOST({ url: '?display=plugins&action=toggle&plugin='+name+'&value='+value, messageBox: 'userinfoBox', success: function (response) {
            if (response.status == "success")
                navigateTo("?display=plugins");
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

	 	<div class="titlebar">{"Plugins"|localize:plugins} - {"Manage plugins"|localize:plugins}</div><br>

        <div class="msgSuccess" id="userinfoBox_success"></div>
        <div class="msgError" id="userinfoBox_failed"></div>

        <div class="grid-1">
          <table class="gridTable">
            <thead>
            	<tr>
                	<th colspan="3">{"plugin"|localize:plugins|ucfirst}</th>
                	<th style="width: 40%;">{"Description"|localize:plugins}</th>
                	<th>{"author"|localize:plugins|ucfirst}</th>
                	<th>{"version"|localize:plugins|ucfirst}</th>
            </thead>
                <tfoot>
            	<tr>
                	<td class="rounded-foot-left" colspan="6"><em>Panthera - {"Plugins"|localize:plugins}</em></td>
                </tr>
            </tfoot>
            <tbody>
              {foreach from=$plugins key=k item=plugin}
                <tr>
                    <td style="width: 1%; border-right: 0px;">
                    {if $plugin.configuration != ''}<a href="{$plugin.configuration}" class="ajax_link"><img src="{$PANTHERA_URL}/images/admin/menu/settings.png" style="width: 16px;"></a>{else}{/if}</td>
                    <td style="width: 1%; border-right: 0px;"><a href="#" onclick="togglePlugin('{$plugin.name}', {if $plugin.enabled == True}0{else}1{/if});">
                    {if $plugin.enabled eq 1} <img src="{$PANTHERA_URL}/images/plugin-enabled.png"> {else} <img src="{$PANTHERA_URL}/images/plugin-disabled.png"> {/if}</a></td>
                	<td><a href="#" onclick="togglePlugin('{$plugin.name}', {if $plugin.enabled == True}0{else}1{/if});">{$plugin.title} <span class="tooltip">{"directory"|localize|ucfirst}: {$plugin.path}<br>{"version"|localize|ucfirst}: {$plugin.version}<br>{"author"|localize|ucfirst}: {$plugin.author}<br>{"Description"|localize}: {$plugin.description}</span></a></td>
                	<td>{$plugin.description}</td>
                	<td>{$plugin.author}</td>
                	<td>{$plugin.version}</td>
                </tr>
              {/foreach}
            </tbody>
          </table>
        </div>

        <div style="height: 100px;"></div> <!-- clean space -->
