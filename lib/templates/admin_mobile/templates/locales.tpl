    <nav class="tab-fixed">
      <ul class="tab-inner">
        <li><a href="#" onclick="navigateTo('?display=dash&cat=admin&menu=settings');" data-transition="push">{function="localize('Dash')"}</a></li>
        <li class="active"><a data-ignore="true">{function="localize('Language settings', 'locales')"}</a></li>
      </ul>
    </nav>

    <div class="content inset">
      <div class="slider tab-slider">
        <ul>
            <li id="conftool" class="tab-item active">
                <ul class="list inset">
                   <li class="list-divider">{function="localize('Languages', 'locales')"}</li>

                  {loop="$locales_added"}
                   <li class="list-item-single-lines selectable">
                      {if="$value.visibility == True"}
                            <button class="btn-small" id="button_{$key}" onclick="localeAction('toggle_visibility', '{$key}');" style="float: right; display: none; margin-bottom: 0px;">{function="localize('Hide')"}</button>
                       {else}
                            <button class="btn-small" id="button_{$key}" onclick="localeAction('toggle_visibility', '{$key}');" style="float: right; display: none; margin-bottom: 0px;">{function="localize('Show')"}</button>
                       {/if}
                      <a href="#" onclick="$('#options_{$key}').slideToggle(); $('#button_{$key}').slideToggle();">
                          {if="$value.flag == True"}<img src="{$PANTHERA_URL}/images/admin/flags/{$key}.png" width="auto" height="15px" style="vertical-align: middle;">{/if}
                          <span {if="$value.visibility != True"} style="color: #bbb;" {/if}>{$key}</span>
                      </a>
                   </li>

                   <div id="options_{$key}" style="display: none;">
                       <button class="btn-small" style="float: right;" onclick="localeAction('delete', '{$key}');">{function="localize('Remove')"}</button>
                       <button class="btn-small" style="float: right;" onclick="localeAction('set_as_default', '{$key}');">{function="localize('Set as default')"}</button>
                       <br><br>
                   </div>
                  {/loop}

                   <br>

                   <li class="list-divider">{function="localize('Settings')"}</li>

                   <li class="list-item-two-lines">
                      <div>
                          <h3>{$locale_system_default}</h3>
                          <p>{function="localize('Website default language', 'locales')"}</p>
                      </div>
                   </li>

                   <li class="list-item-two-lines selectable">
                      <a href="#" onclick="navigateTo('?display=langtool&cat=admin');">
                          <h3>{function="localize('Translations editor', 'langtool')"}</h3>
                          <p>{function="localize('Tools', 'locales')"}</p>
                      </a>
                   </li>

                   <br>

                   <li class="list-divider">{function="localize('Loaded language domains', 'locales')"}</li>

                  {loop="$loaded_domains"}
                   <li class="list-item-two-lines">
                      <div>
                          <h3>{$key}</h3>
                          <p>{$value}</p>
                      </div>
                   </li>
                  {/loop}

                  <br>

                  <li class="list-divider">{function="localize('Add new language', 'langtool')"}</li>

                  <div id="newLanguageGrid">
                    <form action="?display=langtool&cat=admin&action=createNewLanguage" method="POST" id="createNewLanguage">
                        <button type="submit" class="btn-small" style="float: right;">{function="localize('Add')"}</button>
                        <input type="text" class="input-text inline" placeholder="{function="localize('Language name', 'langtool')"}" name="languageName" style="max-width: calc(100% - 115px);">
                    </form>
                  </div>

                </ul>
            </li>
        </ul>
      </div>
    </div>

  {if="$action == ''"}
   <!-- JS code -->
    <script type="text/javascript">

        /**
          * Execute an action on locale
          *
          * @author Damian Kęska
          */

    function localeAction(action, id)
    {
        panthera.htmlPOST({ url: '?display=locales&cat=admin&action='+action, data: 'id='+id});
        navigateTo('?display=locales&cat=admin');
    }

    $(document).ready(function () {
        /**
          * Adding new language
          *
          * @author Damian Kęska
          */

        $('#newLanguageGrid').submit(function () {

            panthera.jsonPOST({ data: '#createNewLanguage', async: true, success: function (response) {
                    if (response.status == "success")
                        navigateTo('?display=langtool&cat=admin');
                }
            });

            return false;
        })
    });
    </script>
   <!-- End of JS code -->
  {/if}