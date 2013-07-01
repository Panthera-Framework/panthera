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
        <div class="titlebar">{"Locales"|localize:langtool} - {"Translates for"|localize:langtool} {$domain}{include file="_navigation_panel.tpl"}</div><br>

        <div class="msgSuccess" id="userinfoBox_success"></div>
        <div class="msgError" id="userinfoBox_failed"></div>

       <div class="grid-1" id="translate">
          <h1><a onclick="navigateTo('?display=langtool&action=domains&locale={$locale}');" href="#">{"Back"|localize}</a></h1> <br/>
          <div class="title-grid">{"Add new translation"|localize:langtool}</div>
          <div class="content-table-grid">
              <table class="insideGridTable">
               <form id="add_string" action="?display=langtool&action=view_domain&locale={$locale}&domain={$domain}&subaction=set_string" method="POST">
                <tbody>
                    <tr>
                        <td style="width: 40%; border-bottom: 0px;"><img src="{$PANTHERA_URL}/images/admin/flags/english.png">&nbsp;&nbsp;&nbsp;<input type="text" name="id" style="width: 80%;"></td>
                        <td id="string" style="border-bottom: 0px; border-right: 0px;"><img src="{$PANTHERA_URL}/images/admin/flags/{$locale}.png">&nbsp;&nbsp;&nbsp;<input type="text" name="string" style="width: 80%;"> <input type="button" value="{"Add"|localize}" style="margin-left: 10px;" onclick="addString();"></td>
                    </tr>
                </tbody>
                </form>
            </table>
         </div>
       </div>

       {$j=0}
       {foreach from=$translates key=k item=i}
       {$j=$j+1}
       <div class="grid-1" id="translate_{$j}">
          <div class="title-grid"><img src="{$PANTHERA_URL}/images/admin/flags/english.png" style="padding-right: 25px; margin-left: 1px;"> {$k}</div>
          <div class="content-table-grid">
              <table class="insideGridTable">
               <form id="change_string_{$j}" action="?display=langtool&action=view_domain&locale={$locale}&domain={$domain}&subaction=set_string&id={$k}" method="POST">
                <tfoot>
                    <tr>
                        <td colspan="2">
                            <input type="button" value="{"Remove"|localize}" style="float: right;" onclick="removeString('{$j}', '{$locale}', '{$domain}', '{$k}');">
                        </td>
                    </tr>
                </tfoot>
                <tbody>
                {foreach from=$i key=lang item=string}
                  <tr>
                  {if $lang eq $locale}
                        <td style="width: 30px;"><img src="{$PANTHERA_URL}/images/admin/flags/{$locale}.png"></td>
                        <td id="string_{$j}" style="border-right: 0px;"><input type="text" name="string" id="string_value_{$j}" value="{$string}" style="width: 80%;"> <input type="button" value="{"Change"|localize}" style="margin-left: 10px;" onclick="saveString({$j}, '{$lang}', '{$domain}', '{$k}');"> </td>
                  {elseif $string eq ''}
                        <td style="width: 30px;"><img src="{$PANTHERA_URL}/images/admin/flags/{$lang}.png"></td>
                        <td id="td_{$j}_{$lang}"><input type="text" name="string" id="string_{$j}_{$lang}"style="width: 80%;"> <input type="button" value="{"Add"|localize}" style="float: right; margin-right: 6px;" onclick="addOtherString({$j}, '{$lang}', '{$domain}', '{$k}')"> </td>
                  {else}
                        <td style="width: 30px;"><img src="{$PANTHERA_URL}/images/admin/flags/{$lang}.png"></td>
                        <td><a href="?display=langtool&action=view_domain&locale={$lang}&domain={$domain}">{$string}</a></td>
                  {/if}
                  </tr>
                {/foreach}
                </tbody>
                </form>
            </table>
         </div>
       </div>
       {/foreach}
