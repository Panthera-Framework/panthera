<h2 class="popupHeading">{"Array editor"|localize:debug}</h2>

<div class="msgSuccess" id="jsonEditBox_success"></div>
<div class="msgError" id="jsonEditBox_failed"></div>

<script type="text/javascript">

function jsonEditSave()
{
    panthera.jsonPOST({ data: '#jsonEditForm', messageBox: 'jsonEditBox', success: function (response) {
            {if $callback}
                {$callback}('{$callback_arg}', response.result);
            {/if}
        }
    });
}

</script>

<form action="{$AJAX_URL}?display=_popup_jsonedit" method="POST" id="jsonEditForm">

<table class="gridTable">
    <thead>
        <tr>
            <th scope="col" colspan="5" style="width: 250px;"><b>{"Editing input from serialized array"|localize:debug}</i></th>
        </tr>
    </thead>

    <tfoot>
        <tr>
            <td colspan="5" class="rounded-foot-left">
                <em>Panthera - Access Control Lists</em>
            </td>
        </tr>
    </tfoot>

    <tbody>
    <tr>
        <td><textarea name="jsonedit_content" style="width: 98%; height: 300px;">{$code}</textarea></td>
    </tr>

    <tr>
        <td><input type="button" value="{"Save"|localize}" onclick="jsonEditSave();" style="float: right; margin-right: 15px;"></td>
    </tr>

    </tbody>
</table>
