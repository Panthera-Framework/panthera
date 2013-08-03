    <nav class="tab-fixed">
      <ul class="tab-inner">
        <li><a href="#" onclick="navigateTo('?display=dash&cat=admin&menu=settings');" data-transition="push">{function="localize('Dash')"}</a></li>
        <li class="active"><a data-ignore="true">{function="localize('Plugins', 'plugins')"}</a></li>
      </ul>
    </nav>

    <div class="content inset">
      <div class="slider tab-slider">
        <ul>
            <li id="gallery" class="tab-item active">
                <ul class="list inset">
                   <li class="list-divider">{function="localize('Manage plugins', 'plugins')"}</li>

                  {loop="$plugins"}
                   <li class="list-item-two-lines selectable">
                      <a href="#" onclick="togglePlugin('{$value.name}', {if="$value.enabled == True"}0{else}1{/if});" >
                        <img src="{$PANTHERA_URL}/images/plugin-{if="$value.enabled == 1"}enabled{else}disabled{/if}.png" width="20px" height="20px" style="vertical-align: middle;">
                        {$value.title} {if="$value.version != 'unknown'"}{$value.version}{/if}
                        <p>({$value.author}) {$value.description}</p>
                      </a>
                   </li>

                  {if="$value.configuration != ''"}
                   <button onclick="navigateTo('{$value.configuration}');" class="btn-small" style="float: right;">{function="localize('Settings')"}</button>
                   <br><br>
                  {/if}
                  {/loop}

                </ul>
            </li>
        </ul>
      </div>
    </div>

   <!-- JS code -->
    <script type="text/javascript">
     /**
       * Toggle plugin
       *
       * @author Mateusz Warzyński
       */

     function togglePlugin(name, value)
     {
        panthera.jsonPOST({ url: '?display=plugins&cat=admin&action=toggle&plugin='+name+'&value='+value, messageBox: 'userinfoBox', success: function (response) {
                if (response.status == "success")
                    navigateTo("?display=plugins&cat=admin");
            }
        });

        return false;
     }

    </script>
   <!-- End of JS code -->