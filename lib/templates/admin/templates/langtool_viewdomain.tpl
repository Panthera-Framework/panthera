<script>
/**
  * Translate string
  *
  * @author Mateusz Warzyński
  */

function saveString(j, locale, domain, id)
{
    string = $("#string_value_"+j).val();
    panthera.jsonPOST({ url: "?display=langtool&cat=admin&action=view_domain&locale="+locale+"&domain="+domain+"&subaction=set_string&id="+id+"&string="+string, data: "", success: function (response) {

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

function addString(backURL)
{
    panthera.jsonPOST({ data: '#add_string', success: function (response) {

        // return string from server (just in case)
        if (response.status == "success")
            navigateTo(backURL);

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
    string = $('#id_'+j).val();
    panthera.jsonPOST({ url: "?display=langtool&cat=admin&action=view_domain&locale="+locale+"&domain="+domain+"&subaction=remove_string&id="+string, data: "", messageBox: 'w2ui', success: function (response) {

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
    panthera.jsonPOST({ url: "?display=langtool&cat=admin&action=view_domain&locale="+locale+"&domain="+domain+"&subaction=set_string&id="+id+"&string="+string, data: "", success: function (response) {

        // return string from server (just in case)
        if (response.status == "success")
              $("#td_"+j+"_"+locale).text(response.string);

        }
    });
}
</script>

{include="ui.titlebar"}

<!-- Ajax content -->

<div class="ajax-content" style="text-align: center;">

       <table>
          <form id="add_string" action="?display=langtool&cat=admin&action=view_domain&locale={$locale}&domain={$domain}&subaction=set_string" method="POST">
              <thead>
                 <tr>
                     <th colspan="2">{function="localize('Add new translation', 'langtool')"}</th>
                 </tr>
              </thead>
              
              <tfoot style="background-color: transparent;">
                  <tr>
                      <td colspan="2"><input type="button" value="{function="localize('Add')"}" style="margin-right: 10px; float: right;" onclick="addString('?display=langtool&cat=admin&action=view_domain&locale={$locale}&domain={$domain}');"></td>
                  </tr>
              </tfoot>
              
              <tbody>
                 <tr>
                     <td><img src="{$PANTHERA_URL}/images/admin/flags/english.png">&nbsp;&nbsp;&nbsp;<input type="text" name="id" style="width: 80%;"></td>
                     <td id="string"><img src="{$flag}">&nbsp;&nbsp;&nbsp;<input type="text" name="string" style="width: 80%;"></td>
                 </tr>
              </tbody>
          </form>
       </table>
       
       
       {$j=0}
       {loop="$translates"}
       {$j=$j+1}
       {$k=$key}
          
       <input type="text" id="id_{$j}" value="{$key}" style="display: none;">
       
       <table style="margin-top: 25px;" id="translate_{$j}">
          <form id="change_string_{$j}" action="?display=langtool&cat=admin&action=view_domain&locale={$locale}&domain={$domain}&subaction=set_string&id={$k}" method="POST">
          
            <thead>
                <tr>
                    <th colspan="2"><img src="{$PANTHERA_URL}/images/admin/flags/english.png" style="padding-right: 25px; margin-left: 1px;">{$k}</th>
                </tr>
            </thead>
            
            <tfoot style="background-color: transparent;">
                <tr>
                   <td colspan="2">
                      <input type="button" value="{function="localize('Remove')"}" style="float: right;" onclick="removeString('{$j}', '{$locale}', '{$domain}');">
                   </td>
                </tr>
            </tfoot>
            
            <tbody class="hovered">
             {loop="$value"}
                <tr>
               {if="$key == $locale"}
                    <td><img src="{$flag}"></td>
                    <td id="string_{$j}"><input type="text" name="string" id="string_value_{$j}" value="{$value}"> <input type="button" value="{function="localize('Change')"}" onclick="saveString({$j}, '{$key}', '{$domain}', '{$k}');"> </td>
               {elseif="$value == ''"}
                    <td><img src="{$PANTHERA_URL}/images/admin/flags/{$lang}.png"></td>
                    <td id="td_{$j}_{$lang}"><input type="text" name="string" id="string_{$j}_{$lang}"> <input type="button" value="{function="localize('Add')"}"onclick="addOtherString({$j}, '{$key}', '{$domain}', '{$k}')"> </td>
               {else}
                    <td><img src="{$flag}"></td>
                    <td><a href="?display=langtool&cat=admin&action=view_domain&locale={$key}&domain={$domain}">{$value}</a></td>
               {/if}
                </tr>
             {/loop}
            </tbody>
         </form>
      </table>
      {/loop}
</div>