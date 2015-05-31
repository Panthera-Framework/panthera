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
    $('.translationForm').each(function( index ) {
        stringsArray.push($(this).serialize());
    });
    
    // serialized array will be encoded into JSON and then to Base64 and send to server
    panthera.jsonPOST({ url: '?display=langtool&cat=admin&action=saveStrings', 'data': '&data='+Base64.encode(JSON.stringify(stringsArray)), messageBox: 'w2ui'});
}

/**
  * Remove string
  *
  * @author Mateusz Warzyński
  */

function removeString(j, locale, domain)
{
    string = $('#original_'+j).val();
    
    panthera.confirmBox.create('{function="localize('Are you sure?')"}', function (response) {
        if (response == 'Yes')
        {
            panthera.jsonPOST({ url: "?display=langtool&cat=admin&action=view_domain&locale="+locale+"&domain="+domain+"&subaction=remove_string&id="+string, data: "", messageBox: 'w2ui', success: function (response) {

                // return string from server (just in case)
                if (response.status == "success")
                    $('#translate_'+j).remove();
                }
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
              navigateTo('?display=langtool&cat=admin&action=view_domain&locale={$language}&domain={$domain}');

        }
    });
}
</script>

{$titleBarInclude='langtool_viewdomain.titlebar'}
{include="ui.titlebar"}

<div id="topContent" style="min-height: 50px;">
    <div class="searchBarButtonArea">
        <div style="float: left; display: inline-block; margin-left: 10px;">
            <input type="button" value="{function="slocalize('Back to %s', 'messages', $language)"}" onclick="navigateTo('?display=langtool&cat=admin&action=domains&locale={$currentLanguage}')">
        </div>
    
        <input type="button" value="{function="localize('Add new translation', 'langtool')"}" onclick="panthera.popup.toggle('element:#addNewStringPopup')">
        <input type="button" value="{function="localize('Save')"}" onclick="saveStrings()">
    </div>
</div>





<!-- A template of a table to insert -->
<div id="newTable" style="display: none;">
<table style="width: 100%; margin-bottom: 60px;" id="translate_%randomid%">
        <thead>
            <th colspan="2"><img src="{$PANTHERA_URL}/images/admin/flags/english.png" style="padding-right: 10px; margin-left: 1px;">"%original%" <small><i>(english)</i></small></th>
        </thead>
        
        <tbody>
            <tr style="height: 60px;">
                <td>
                    <form action="#" method="POST" class="">
                        <!-- Original string -->
                        <input type="hidden" name="original" value="%original%" id="original_%randomid%">
                        
                        <!-- Translation language -->
                        <input type="hidden" name="language" value="{$language}">
                        <img src="{$flag}" style="margin-right: 25px;">
                        
                        <input type="hidden" name="domain" value="{$domain}">
                        
                        <!-- translation string --> 
                        <input type="text" name="translation" value="%translation%" style="width: 50%;"> 
                        <input type="button" value="{function="localize('Remove')"}" style="float: right;" onclick="removeString('%randomid%', '{$locale}', '{$domain}');">
                    </form>
                </td>
            </tr>
        </tbody>
    </table>
</div>





<!-- Adding new string popup -->
<div id="addNewStringPopup" style="display: none;">
    <form action="?display=langtool&cat=admin&action=view_domain&locale={$locale}&domain={$domain}&subaction=addNewString" method="POST" id="addNewStringForm">
    <table class="formTable" style="margin: 0 auto;">
            <thead>
                <tr>
                    <th colspan="2">{function="localize('Add new translation', 'langtool')"}</th>
                </tr>
            </thead>
            
            <tbody>
                <tr>
                    <th><img src="{$PANTHERA_URL}/images/admin/flags/english.png"> {function="localize('Original', 'langtool')"} <small><i>(english)</i></small></th>
                    <td><input type="text" name="id" style="width: 80%;"></td>
                </tr>
            
                <tr>
                    <th id="string"><img src="{$flag}"> {$language|ucfirst}</i></small></th>
                    <td><input type="text" name="string" style="width: 80%;"></td>
                </tr>
                
                
            </tbody>
            
            <tfoot style="background-color: transparent;">
                <tr>
                    <td colspan="2"><input type="button" value="{function="localize('Add')"}" style="margin-right: 10px; float: right;" onclick="$('#addNewStringForm').submit();"></td>
                </tr>
            </tfoot>
    </table>
    </form>
    
    <script type="text/javascript">
    /**
      * Add string
      *
      * @author Mateusz Warzyński
      * @author Damian Kęska
      */

    $('#addNewStringForm').submit(function () {
        panthera.jsonPOST({ data: '#addNewStringForm', success: function (response) {
                // return string from server (just in case)
                if (response.status == "success")
                {
                    navigateTo('?display=langtool&cat=admin&action=view_domain&locale={$language}&domain={$domain}');
                }
            }
        });
        
        return false;
    });
    </script>
</div>





<!-- Ajax content -->
<div id="ajax_content" class="ajax-content" style="text-align: center;">
    <div style="display: inline-block; width: 60%; margin-right: 50px; min-width: 800px;">
        <table style="width: 100%;">
            <thead>
                <tr>
                    <th>{function="localize('Translations', 'langtool')"}</th>
                </tr>
            </thead>
            
            <tbody>
                {$j=0}
                {loop="$translates"}
                    {$k=$key}
                    {$j=$j+1}
                    
                    {loop="$value"}
                    {if="$key == $locale"}
                    <tr>
                        <td style="padding-top: 10px; padding-bottom: 10px;" id="translate_{$j}">
                            <form action="#" method="POST" class="translationForm">
                                <input type="hidden" name="domain" value="{$domain}">
                                <input type="hidden" name="language" value="{$language}">
                                <input type="hidden" name="original" value="{$k}" id="original_{$j}">
                                
                                <span style="margin-left: 20px;"><img src="{$PANTHERA_URL}/images/admin/flags/english.png" style="padding-right: 5px; margin-left: 1px;"> {$k|htmlspecialchars}</span>
                                
                                <div style="float: right;">
                                    <img src="{$flag}" style="margin-right: 5px;"> 
                                    <input type="text" name="translation" style="min-width: 300px;" value="{$value}"> 
                                    <input type="button" value="{function="localize('Remove')"}" onclick="removeString('{$j}', '{$locale}', '{$domain}');">
                                </div>
                            </form>
                        </td>
                    </tr>
                    {/if}
                    {/loop}
                {/loop}
            </tbody>
        </table>
    <div id="newTableAppendPoint"></div>
</div>
