{if $popup}
<h2 class="popupHeading">{"Array editor"|localize:debug}</h2>
{else}
<div class="titlebar" style="margin-bottom: 20px;">{"Array editor"|localize:debug}{include file="_navigation_panel.tpl"}</div>
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
                $('#jsonEditBox_failed').html('{"Invalid JSON syntax"|localize:debug}');
                $('#jsonEditBox_failed').slideDown();
                return false;
            }
            
            $('#jsonEditBox_failed').slideUp();
    
            {if $callback}
                {$callback}('{$callback_arg}', response.result);
            {/if}
            
            {if !$popup}
                $('#jsonedit_result').val(response.result);
            {/if}
                        
            closePopup();
        }
    });
}

</script>

<form action="{$AJAX_URL}?display=_popup_jsonedit" method="POST" id="jsonEditForm">

<table class="gridTable">
    <thead>
        <tr>
            <th scope="col" colspan="5" style="width: 250px;"><b>{"Enter JSON code to convert to PHP array"|localize:debug}</i></th>
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
    
    <tr id="jsonedit_result_tr" {if $popup}style="display: none;"{/if}>
        <td><b>{"Result"|localize:debug}</b>: <br><div id="spinnerOverlay" style="position: relative;"><textarea id="jsonedit_result" readonly style="width: 98%; height: 250px;"></textarea></div></td>
    </tr>

    <tr>
        <td>
            <input type="button" value=" {"Serialize"|localize} " onclick="jsonEditSave('');" style="float: right; margin-right: 15px;">
            {if !$popup}
            <input type="button" value=" {"print_r"|localize} " onclick="jsonEditSave('print_r');" style="float: right; margin-right: 15px;"> 
            <input type="button" value=" {"var_dump"|localize} " onclick="jsonEditSave('var_dump');" style="float: right; margin-right: 15px;"> 
            {/if}
            <input type="hidden" name="responseType" id="responseType" value=""></td>
    </tr>

    </tbody>
</table>
