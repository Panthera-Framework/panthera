{if="!$popup"}
{include="ui.titlebar"}
{/if}

<script src="{$PANTHERA_URL}/js/jquery-linedtextarea.js" type="text/javascript"></script>
<link href="{$PANTHERA_URL}/css/jquery-linedtextarea.css" type="text/css" rel="stylesheet">

<script type="text/javascript">
function jsonEditSave(responseType)
{
    $('#responseType').val(responseType);

    panthera.jsonPOST({ data: '#jsonEditForm', success: function (response) {
            if (response.result == "N;")
            {
                $('#jsonEditBox_failed').html('{function="localize('Invalid JSON syntax', 'debug')"}');
                $('#jsonEditBox_failed').slideDown();
                return false;
            }
            
            $('#jsonEditBox_failed').slideUp();
    
            {if="$callback"}
                {$callback}('{$callback_arg}', response.result);
            {/if}
            
            {if="!$popup"}
                $('#jsonedit_result').val(response.result);
            {/if}
                        
            closePopup();
        }
    });
}
</script>

<form action="{$AJAX_URL}?display=_popup_jsonedit&cat=admin" method="POST" id="jsonEditForm">
    {if="!$popup"}
    <div id="topContent" style="min-height: 50px;">
        <div class="searchBarButtonArea">
        
            <input type="button" value=" {function="localize('Serialize')"} " onclick="jsonEditSave('');" style="float: right; margin-right: 15px;">
            <input type="button" value=" {function="localize('print_r')"} " onclick="jsonEditSave('print_r');" style="float: right; margin-right: 15px;"> 
            <input type="button" value=" {function="localize('var_dump')"} " onclick="jsonEditSave('var_dump');" style="float: right; margin-right: 15px;"> 
            <input type="button" value=" {function="localize('var_export')"} " onclick="jsonEditSave('var_export');" style="float: right; margin-right: 15px;"> 
            <input type="button" value=" {function="localize('json')"} " onclick="jsonEditSave('json');" style="float: right; margin-right: 15px;"> 
        </div>
    </div>
    {/if}

    <div class="ajax-content" style="text-align: center;">
        <div style="display: inline-table; margin: 0 auto;">
                <table>
                    <thead>
                        <tr>
                            <th scope="col" colspan="5" style="width: 250px;"><b>{function="localize('Enter JSON code or serialized array to convert to PHP array', 'debug')"}</i></th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr>
                            <td style="padding: 0px; min-width: 750px;"><textarea name="jsonedit_content" id="jsonedit_content" style="width: 100%; height: 300px;">{$code}</textarea></td>
                        </tr>
                        
                        <tr id="jsonedit_result_tr" style='{if="$popup"}display: none;{/if}'>
                            <td style="padding: 0px;"><div id="spinnerOverlay" style="position: relative;"><textarea id="jsonedit_result" readonly style="width: 98%; height: 250px;"></textarea></div></td>
                        </tr>
                    </tbody>
                </table>
                
            <input type="hidden" name="responseType" id="responseType" value="">
            
           {if="$popup"}
            <div style="padding-top: 15px;">
                <div style="display: inline-block; float: left; margin-left: 10px;">
                    <input type="button" value="{function="localize('Close')"}" onclick="panthera.popup.close()">
                </div>
            
                <input type="button" value=" {function="localize('Serialize')"} " onclick="jsonEditSave('');" style="margin-right: 45px; float: right;">
            </div>
           {/if}
        </div>
    </div>
</form>

{if="!$popup"}
<script type="text/javascript">
$("#jsonedit_content").linedtextarea();
</script>
{/if}
