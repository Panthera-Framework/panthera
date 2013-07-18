<script>
/**
  * Translate string
  *
  * @author Mateusz Warzyński
  */

function saveString(j, locale, domain, id)
{
    string = $("#string_value_"+j).val();
    panthera.jsonPOST({ url: "?display=langtool&action=view_domain&locale="+locale+"&domain="+domain+"&subaction=set_string&id="+id+"&string="+string, data: "", success: function (response) {

        // return string from server (just in case)
        if (response.status == "success")
              $("#td_"+j+"_"+locale).text(response.string);

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
    panthera.jsonPOST({ data: '#add_string', messageBox: 'userinfoBox', success: function (response) {

        // return string from server (just in case)
        if (response.status == "success")
            navigateTo('#');

        }
    });
}

/**
  * Remove string
  *
  * @author Mateusz Warzyński
  */

function removeString(j, locale, domain, id)
{
    panthera.jsonPOST({ url: "?display=langtool&action=view_domain&locale="+locale+"&domain="+domain+"&subaction=remove_string&id="+id, data: "", messageBox: 'userinfoBox', success: function (response) {

        // return string from server (just in case)
        if (response.status == "success")
            $('#translate_'+j).slideUp('slow', function () {
                $('#translate_'+j).remove();
            });
        }
    });
}

/**
  * Add translation to other locale
  *
  * @author Mateusz Warzyński
  */

function addOtherString(j, locale, domain, id)
{
    string = $('#string_'+j+'_'+locale).val();
    panthera.jsonPOST({ url: "?display=langtool&action=view_domain&locale="+locale+"&domain="+domain+"&subaction=set_string&id="+id+"&string="+string, data: "", success: function (response) {

        // return string from server (just in case)
        if (response.status == "success")
              $("#td_"+j+"_"+locale).text(response.string);

        }
    });
}
</script>
        <div class="titlebar">{function="localize('Locales', 'langtool')"} - {function="localize('Translates for', 'langtool')"} {$domain}{include="_navigation_panel.tpl"}</div><br>

        <div class="msgSuccess" id="userinfoBox_success"></div>
        <div class="msgError" id="userinfoBox_failed"></div>

       <div class="grid-1" id="translate">
          <h1><a onclick="navigateTo('?display=langtool&action=domains&locale={$locale}');" href="#">{function="localize('Back')"}</a></h1> <br/>
          <div class="title-grid">{function="localize('Add new translation', 'langtool')"}</div>
          <div class="content-table-grid">
              <table class="insideGridTable">
               <form id="add_string" action="?display=langtool&action=view_domain&locale={$locale}&domain={$domain}&subaction=set_string" method="POST">
                <tbody>
                    <tr>
                        <td style="width: 40%; border-bottom: 0px;"><img src="{$PANTHERA_URL}/images/admin/flags/english.png">&nbsp;&nbsp;&nbsp;<input type="text" name="id" style="width: 80%;"></td>
                        <td id="string" style="border-bottom: 0px; border-right: 0px;"><img src="{$PANTHERA_URL}/images/admin/flags/{$locale}.png">&nbsp;&nbsp;&nbsp;<input type="text" name="string" style="width: 80%;"> <input type="button" value="{function="localize('Add')"}" style="margin-left: 10px;" onclick="addString();"></td>
                    </tr>
                </tbody>
                </form>
            </table>
         </div>
       </div>

       {$j=0}
       {loop="$translates"}
       {$j=$j+1}
       <div class="grid-1" id="translate_{$j}">
          <div class="title-grid"><img src="{$PANTHERA_URL}/images/admin/flags/english.png" style="padding-right: 25px; margin-left: 1px;"> {$key}</div>
          <div class="content-table-grid">
              <table class="insideGridTable">
               <form id="change_string_{$j}" action="?display=langtool&action=view_domain&locale={$locale}&domain={$domain}&subaction=set_string&id={$k}" method="POST">
                <tfoot>
                    <tr>
                        <td colspan="2">
                            <input type="button" value="{function="localize('Remove')"}" style="float: right;" onclick="removeString('{$j}', '{$locale}', '{$domain}', '{$k}');">
                        </td>
                    </tr>
                </tfoot>
                <tbody>
                {$k=$key}
                {loop="$value"}
                  <tr>
                  {if="$key == $locale"}
                        <td style="width: 30px;"><img src="{$PANTHERA_URL}/images/admin/flags/{$locale}.png"></td>
                        <td id="string_{$j}" style="border-right: 0px;"><input type="text" name="string" id="string_value_{$j}" value="{$value}" style="width: 80%;"> <input type="button" value="{function="localize('Change')"}" style="margin-left: 10px;" onclick="saveString({$j}, '{$key}', '{$domain}', '{$k}');"> </td>
                  {elseif="$value == ''"}
                        <td style="width: 30px;"><img src="{$PANTHERA_URL}/images/admin/flags/{$lang}.png"></td>
                        <td id="td_{$j}_{$lang}"><input type="text" name="string" id="string_{$j}_{$lang}"style="width: 80%;"> <input type="button" value="{function="localize('Add')"}" style="float: right; margin-right: 6px;" onclick="addOtherString({$j}, '{$key}', '{$domain}', '{$k}')"> </td>
                  {else}
                        <td style="width: 30px;"><img src="{$PANTHERA_URL}/images/admin/flags/{$key}.png"></td>
                        <td><a href="?display=langtool&action=view_domain&locale={$key}&domain={$domain}">{$value}</a></td>
                  {/if}
                  </tr>
                {/loop}
                </tbody>
                </form>
            </table>
         </div>
       </div>
       {/loop}
