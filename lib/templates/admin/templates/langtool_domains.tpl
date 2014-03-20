<script type="text/javascript">
/**
  * Translate strings
  *
  * @author Damian Kęska
  */

function saveStrings()
{
    stringsArray = new Array();

    // serialize all forms and put into array
    $('.missingTranslationForm').each(function( index ) {
        if ($('input[name="translation"]', $(this)).val())
        {
            stringsArray.push($(this).serialize());
        }
    });
    
    // serialized array will be encoded into JSON and then to Base64 and send to server
    panthera.jsonPOST({ url: '?display=langtool&cat=admin&action=saveStrings&createMissingDomains=True&missingStrings=True', 'data': 'data='+Base64.encode(JSON.stringify(stringsArray)), messageBox: 'w2ui'});
}

/**
  * Create domain
  *
  * @author Mateusz Warzyński
  */

function createDomain(locale)
{
    var name = $("#domain_name").val();
    panthera.jsonPOST({ url: '{$AJAX_URL}?display=langtool&cat=admin&action=domains&subaction=add_domain&domain_name='+name+'&locale='+locale, data: '', success: function (response) {
            if (response.status == "success")
                navigateTo('?display=langtool&cat=admin&action=domains&locale='+locale);
        }
    });

    return false;
}

/**
  * Rename domain
  *
  * @author Mateusz Warzyński
  */

function renameDomain(name, locale, n)
{
    var newname = $("#domain_new_name_"+n).val();
    panthera.jsonPOST({ url: '{$AJAX_URL}?display=langtool&cat=admin&action=domains&subaction=rename_domain&domain_name='+name+'&locale='+locale+'&new_domain_name='+newname, data: '', success: function (response) {
            if (response.status == "success")
                $("#domain_name_"+n).html('<a href="?display=langtool&cat=admin&action=view_domain&locale='+locale+'&domain='+newname+'.phps">'+newname+'.phps</a>');
                $("#domain_new_name_"+n).val(newname);
        }
    });
}


/**
  * Remove domain
  *
  * @author Mateusz Warzyński
  */

function removeDomain(name, locale, n)
{
    panthera.confirmBox.create("{function="localize('Are you sure?')"}", function (response) {
    
        if (response == 'Yes')
        {
            panthera.jsonPOST({ url: '{$AJAX_URL}?display=langtool&cat=admin&action=domains&subaction=remove_domain&domain_name='+name+'&locale='+locale, data: '', success: function (response) {
                    if (response.status == "success")
                        jQuery('#domain_row_'+n).remove();
                }
            });
        }
    });
}
</script>

{$titleBarInclude='langtool_domains.titlebar'}
{include="ui.titlebar"}

<div id="topContent" style="min-height: 50px;">
    {$uiSearchbarName="uiTop"}
    {include="ui.searchbar"}
    
    <div class="separatorHorizontal"></div>

    <div class="searchBarButtonArea">
        <input type="button" value="{function="localize('Add domain', 'langtool')"}" onclick="panthera.popup.toggle('element:#newDomain')">
        <input type="button" value="{function="localize('Save added strings', 'langtool')"}" onclick="saveStrings()">
    </div>
</div>

<!-- New domain popup -->

<div id="newDomain" style="display: none;">
    
    <script type="text/javascript">
    $(document).ready(function () {
        /**
          * Adding new language
          *
          * @author Damian Kęska
          */
    
        $('#createNewLanguage').submit(function () {
            
            panthera.jsonPOST({ data: '#createNewLanguage', type: 'POST', async: true, success: function (response) {
                    if (response.status == "success")
                        navigateTo('?display=langtool&cat=admin');    
                }
            });
            
            return false;
        })
    });
    </script>
    
    <form action="?display=langtool&cat=admin&action=createNewLanguage" method="POST" id="createNewLanguage">
         
         <table class="formTable" style="margin: 0 auto; margin-bottom: 30px;">
            
            <thead>
                 <tr>
                     <td colspan="2" class="formTableHeader" style="padding-top: 0px; padding-bottom: 30px;">
                         <p style="color: #e5ebef; padding: 0px; margin: 0px; margin-left: 30px;">{function="localize('Create new domain', 'langtool')"}</p>
                     </td>
                 </tr>
            </thead>
             
            <tfoot>
                <tr>
                    <td colspan="3" style="padding-top: 35px;">
                        <input type="button" value="{function="localize('Cancel')"}" onclick="panthera.popup.close()" style="float: left; margin-left: 30px;">
                        <input type="button" value="{function="localize('Add', 'langtool')"}" onclick="createDomain('{$locale}');" style="float: right; margin-right: 30px;">
                    </td>
                </tr>
            </tfoot>

            <tbody>
                <tr>
                    <th>{function="localize('Name', 'langtool')"}</th>
                    <th><input type="text" name="domain_name" id="domain_name"></th>
                </tr>
            </tbody>
         </table>
    </form>
</div>
    
<div id="popupOverlay" style="text-align: center; padding-top: 20px; padding-bottom: 0px;"></div>


<!-- Ajax content -->

<div class="ajax-content" style="text-align: center;">

      <table style="display: inline-block; margin-bottom: 30px; max-width: 40%;">
            <thead>
                <tr>
                    <th>{function="localize('Locale', 'langtool')"}</th>
                    <th>{function="localize('Domain', 'langtool')"}</th>
                    <th>{function="localize('Options')"}</th>
                </tr>
            </thead>

            <tbody class="hovered">
              {if="count($domains) > 0"}
              {$j=0}
              {loop="$domains"}
              {$j=$j+1}
                <tr id="domain_row_{$j}">
                    <td><img src="{$flag}"></td>
                    <td id="domain_name_{$j}" style="width: 400px;"><a href="#" onclick="navigateTo('?display=langtool&cat=admin&action=view_domain&locale={$locale}&domain={$value}');">{$value}</a></td>
                    <td>
                        <a href="#" onclick="removeDomain('{$value}', '{$locale}', '{$j}');">
                            <img src="{$PANTHERA_URL}/images/admin/ui/delete.png" style="max-height: 22px;" alt="{function="localize('Remove')"}">
                        </a>
                    </td>
                </tr>
                
                <script type="text/javascript">
                    $(document).ready(function () { 
                        panthera.inputTimeout({ element: '#domain_new_name_{$j}', interval: 1200, callback: function () { renameDomain('{$value}', '{$locale}', '{$j}'); }});
                    });
                </script>
              {/loop}
              {else}
              <tr><td colspan="3" style="text-align: center;">{function="localize('Cannot find any domains for this locale, please use button below to create a new domain', 'langtool')"}</td></tr>
              {/if}
            </tbody>
     </table>
     
     {if="count($missingTranslations)"}
     
     <div style="display: inline-block; width: 50%; margin-right: 50px;">
        <table style="display: inline-block;">
            <thead>
                <tr>
                    <th>{function="localize('Missing translations in this language', 'langtool')"}</th>
                </tr>
            </thead>
            
            <tbody class="hovered">
                {loop="$missingTranslations"}
                    {$domain=$key}
                    {loop="$value"}
                <tr>
                    <td style="padding-top: 10px; padding-bottom: 10px;">
                        <form action="#" method="POST" class="missingTranslationForm">
                            <small style="float: left;"><i>{$value.domain}</i><br>{$value.file|basename}:{$value.line}</small>
                            <input type="hidden" name="domain" value="{$value.domain}">
                            <input type="hidden" name="language" value="{$locale}">
                            <input type="hidden" name="original" value="{$key|base64_encode}">
                            <input type="hidden" name="originalEncoding" value="base64">
                            <span style="margin-left: 20px;">{$key|htmlspecialchars}</span>
                            
                            <input type="text" name="translation" style="float: right; width: 40%;">
                        </form>
                    </td>
                    
                </tr>
                    {/loop}
                {/loop}
            </tbody>
        </table>
        
        <div style="position: relative; text-align: left; width: 50%; margin-right: 50px; margin-left: 50px;" class="pager">{$uiPagerName="adminMissingTranslations"}{include="ui.pager"}</div>
    </div>
    {/if}
</div>
