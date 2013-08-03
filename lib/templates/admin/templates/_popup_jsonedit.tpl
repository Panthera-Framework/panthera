{if="$popup"}
<h2 class="popupHeading">{function="localize('Array editor', 'debug')"}</h2>
{else}
<div class="titlebar" style="margin-bottom: 20px;">{function="localize('Array editor', 'debug')"}{include="_navigation_panel"}</div>
{/if}

<div class="msgSuccess" id="jsonEditBox_success"></div>
<div class="msgError" id="jsonEditBox_failed"></div>

<script type="text/javascript">
jsonEditSpinner = new panthera.ajaxLoader($('#spinnerOverlay'));

function jsonEditSave(responseType)
{
    $('#responseType').val(responseType);

    panthera.jsonPOST({ data: '#jsonEditForm', messageBox: 'jsonEditBox', spinner: jsonEditSpinner, success: function (response) {
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

<table class="gridTable">
    <thead>
        <tr>
            <th scope="col" colspan="5" style="width: 250px;"><b>{function="localize('Enter JSON code or serialized array to convert to PHP array', 'debug')"}</i></th>
        </tr>
    </thead>

    <tfoot>
        <tr>
            <td colspan="5" class="rounded-foot-left">
                <em>Panthera - jsonEdit</em>
            </td>
        </tr>
    </tfoot>

    <tbody>
    <tr>
        <td><textarea name="jsonedit_content" style="width: 98%; height: 300px;">{$code}</textarea></td>
    </tr>
    
    <tr id="jsonedit_result_tr" {if="$popup"}style='display: none;'{/if}>
        <td><b>{function="localize('Result', 'debug')"}</b>: <br><div id="spinnerOverlay" style="position: relative;"><textarea id="jsonedit_result" readonly style="width: 98%; height: 250px;"></textarea></div></td>
    </tr>

    <tr>
        <td>
            <input type="button" value=" {function="localize('Serialize')"} " onclick="jsonEditSave('');" style="float: right; margin-right: 15px;">
            {if="!$popup"}
            <input type="button" value=" {function="localize('print_r')"} " onclick="jsonEditSave('print_r');" style="float: right; margin-right: 15px;"> 
            <input type="button" value=" {function="localize('var_dump')"} " onclick="jsonEditSave('var_dump');" style="float: right; margin-right: 15px;"> 
            <input type="button" value=" {function="localize('var_export')"} " onclick="jsonEditSave('var_export');" style="float: right; margin-right: 15px;"> 
            <input type="button" value=" {function="localize('json')"} " onclick="jsonEditSave('json');" style="float: right; margin-right: 15px;"> 
            {/if}
            <input type="hidden" name="responseType" id="responseType" value=""></td>
    </tr>

    </tbody>
</table>
