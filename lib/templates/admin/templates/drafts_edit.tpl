{$site_header}
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

jQuery(document).ready(function() {
    /**
      * Init MCE Editor
      *
      * @author Damian Kęska
      */
    
    mceInit('content_textarea');
    
    $('#saveDraftForm').submit(function () {
        panthera.jsonPOST({data: '#saveDraftForm', mce: 'tinymce_all', success: function (response) {
                if (response.status == 'success')
                {
                    navigateTo('?display=editor_drafts&cat=admin');
                }
            }
        })               

        return false;
    });
});
</script>

{function="uiMce::display()"}

{include="ui.titlebar"}

<form action="?display=editor_drafts&cat=admin&id={$draftID}&action=saveDraft" method="POST" id="saveDraftForm">
<div class="grid-1">
    <table class="gridTable">
        <thead>
            <tr>
                <th>{function="slocalize('Editing message draft created by %s', 'editordrafts', $author)"}</th>
            </tr>
        </thead>
        
        <tbody>
            <tr>
                <td><textarea name="content" id="content_textarea" style="width: 99%; min-height: 450px;">{$content}</textarea></td>
            </tr>
        </tbody>
        
        <tfoot>
            <tr>
                <td><input type="submit" value="{function="localize('Save')"}" style="float: right;"></td>
            </tr>
        </tfoot>
    </table>
</div>
</form>
