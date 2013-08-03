    <nav class="tab-fixed">
      <ul class="tab-inner">
        <li><a href="#" onclick="navigateTo('?display=langtool&cat=admin&action=domains&locale={$locale}');">{function="localize('Manage domains', 'langtool')"}</a></li>
        <li class="active"><a data-ignore="true">{function="localize('Translates for', 'langtool')"} {$domain}</a></li>
      </ul>
    </nav>

    <div class="content inset">
     <div class="slider tab-slider">
        <ul>
            <ul class="list inset">
               <label>{function="localize('Add new translation', 'langtool')"}</label>
              <form id="add_string" action="?display=langtool&cat=admin&action=view_domain&locale={$locale}&domain={$domain}&subaction=set_string" method="POST">
                  <img src="{$PANTHERA_URL}/images/admin/flags/english.png" height="12px">&nbsp;<input type="text" name="id" class="input-text inline" autocomplete="off" style="max-width: calc(100% - 22px);"><br>
                  <img src="{$PANTHERA_URL}/images/admin/flags/{$locale}.png" height="12px">&nbsp;<input type="text" name="string" class="input-text inline" autocomplete="off" style="max-width: calc(100% - 22px);">
                  <button class="btn-block" onclick="addString();" style="margin-top: 6px;">{function="localize('Add')"}</button>
              </form>

            <br><br>

            {$j=0}
          {loop="$translates"}
            {$j=$j+1}

              {loop="$value"}
                {if="$key == $locale"}
                   {$translate = $value}
                {/if}
              {/loop}
              <input type="text" id="key_{$j}" value="{$key}" style="display: none;">

              <li class="list-item-two-lines" id="li_{$j}">
                 <a href="#" onclick="$('#translates_{$j}').slideToggle();">
                    <h3>
                        <img src="{$PANTHERA_URL}/images/admin/flags/{$locale}.png" height="12px">
                        <input type="text" placeholder="{function="localize('Translate', 'langtool')"}" value='{$translate}' id="string_value_{$j}" class="input-text" style="padding: 0px;">
                    </h3>
                    <p style="margin-top: 9px;">
                        <img src="{$PANTHERA_URL}/images/admin/flags/english.png" height="9px">
                        {$key}
                    </p>
                 </a>
              </li>

              <div id="translates_{$j}" style="display: none;">
                 <a href="#" onclick="$('#translates_{$j}').slideToggle();">
                  {loop="$value"}
                    {if="$key != $locale && $value != ''"}
                        <p style="font-size: 12px; color: #bbb; margin-left: 1px;">&nbsp;<img src="{$PANTHERA_URL}/images/admin/flags/{$key}.png" height="9px">&nbsp;{$value}</p>
                    {/if}
                  {/loop}
                 </a>

                 <div style="margin-top: 15px;">
                  <button class="btn-small" id="button_locale_{$j}" style="max-width: calc(100% - 131px); width: 100%;" onclick="saveString({$j}, '{$locale}', '{$domain}');">{function="localize('Change')"}</button>
                  <button class="btn-small" onclick="removeString('{$j}', '{$locale}', '{$domain}');" style="float: right;">{function="localize('Remove')"}</button>
                 </div>
                 <br>
              </div>
           {/loop}
          </ul>
        </ul>
      </div>
    </div>

   <!-- JS code -->
   <script type="text/javascript">
   /**
      * Translate string
      *
      * @author Mateusz Warzyński
      */

    function saveString(j, locale, domain)
    {
        string = $("#string_value_"+j).val();
        id = $("#key_"+j).val();
        panthera.jsonPOST({ url: "?display=langtool&cat=admin&action=view_domain&locale="+locale+"&domain="+domain+"&subaction=set_string&id="+id+"&string="+string, data: "", success: function (response) {

            // return string from server (just in case)
            if (response.status == "success")
                  $("#translates_"+j).hide();
                  $('#button_locale_'+j).hide();
                  $("#string_translate_"+j).text(response.string);

            }
        });
    }

    /**
      * Add string
      *
      * @author Mateusz Warzyński
      */

    function addString()
    {
        panthera.jsonPOST({ data: '#add_string', success: function (response) {

            // return string from server (just in case)
            if (response.status == "success")
                navigateTo('?display=langtool&cat=admin&action=view_domain&locale={$locale}&domain={$domain}');

            }
        });
    }

    /**
      * Remove string
      *
      * @author Mateusz Warzyński
      */

    function removeString(j, locale, domain)
    {
        id = $("#key_"+j).val();

        panthera.jsonPOST({ url: "?display=langtool&cat=admin&action=view_domain&locale="+locale+"&domain="+domain+"&subaction=remove_string&id="+id, data: "", messageBox: 'userinfoBox', success: function (response) {

            // return string from server (just in case)
            if (response.status == "success")
                $('#li_'+j).slideUp('slow', function () {
                    $('#li_'+j).remove();
                });
                $('#translates_'+j).slideUp('slow', function () {
                    $('#translates_'+j).remove();
                });
                $('#key_'+j).remove();
            }
        });
    }

   </script>
   <!-- End of JS code -->