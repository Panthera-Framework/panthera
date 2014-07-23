{$site_header}

<style>
.formTable tbody td {
    padding-right: 0px;
}

.tableRightColumn {
    padding-right: 90px;
}
</style>

<div style="margin-top: 25px;">
<form action="?display=editor.drafts&cat=admin&id={$draftID}&action=saveDraft" method="POST" id="saveDraftForm">
    <table class="formTable" style="margin: 0 auto;">
        <thead>
            <tr>
                <td class="formTableHeader" style="padding-top: 0px; padding-bottom: 30px;">
                    <p style="color: #e5ebef; padding: 0px; margin: 0px; margin-left: 30px;">{function="slocalize('Editing message draft created by %s', 'editordrafts', $author)"}</p>
                </td>
            </tr>
        </thead>
        
        <tbody>
            <tr style="background-color: transparent;">
                <th style="padding-left: 0px;"><textarea name="content" id="content_textarea" style="width: 99%; min-height: 450px;">{$content}</textarea></th>
            </tr>
        </tbody>
        
        <tfoot>
            <tr>
                <td colspan="2" style="padding-top: 35px;">
                    <input type="button" value="{function="localize('Cancel')"}" onclick="panthera.popup.create('?display=editor.drafts&cat=admin&callback={$callback}')" style="float: left; margin-left: 30px;">
                    <input type="submit" value="{function="localize('Save')"}" style="float: right;">
                </td>
            </tr>
        </tfoot>
    </table>
</form>
</div>

<script type="text/javascript">
/**
 * Init MCE editor
 *
 * @author Damian Kęska
 */

function initEditor()
{
    mceSetContent('content_textarea', htmlspecialchars_decode("{$content}"));
}

    /**
     * Init MCE Editor
     *
     * @author Damian Kęska
     */
    
    $('#saveDraftForm').submit(function () {
        panthera.jsonPOST({data: '#saveDraftForm', mce: 'tinymce_all', success: function (response) {
                if (response.status == 'success')
                    panthera.popup.create('?display=editor.drafts&cat=admin&popup=true&callback={$callback}');
            }
        })               

        return false;
    });
</script>

{function="uiMce::display()"}

<script type="text/javascript">
mceInit('content_textarea');
</script>
