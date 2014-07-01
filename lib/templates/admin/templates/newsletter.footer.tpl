<script type="text/javascript">
function initEditor () 
{
    mceSetContent('content_textarea', htmlspecialchars_decode("{$mailFooter}"));
}
</script>

{function="uiMce::display()"}
<script type="text/javascript">
jQuery(document).ready(function($) {
    //{include file="mce.tpl"}
    mceInit('content_textarea');
    
    $('#newsletterFooterForm').submit(function(event){
        panthera.jsonPOST({ data: '#newsletterFooterForm', mce: 'tinymce_all', success: function (response) {
                if (response.status == 'success')
                    navigateTo('{$AJAX_URL}?display=newsletter.compose&cat=admin&nid={$nid}');
            } 
        });
        
        return false;
    });
});
</script>

<div style="margin-top: 25px;">
<form id="newsletterFooterForm" action="{$AJAX_URL}?display=newsletter.compose&cat=admin&nid={$nid}&action=editFooter" method="POST">
    <table class="formTable" style="margin: 0 auto;">
        <thead>
            <tr>
                <td class="formTableHeader" style="padding-top: 0px; padding-bottom: 30px;">
                    <p style="color: #e5ebef; padding: 0px; margin: 0px; margin-left: 30px;">{function="localize('Editing newsletter footer', 'newsletter')"}</p>
                </td>
            </tr>
        </thead>
        
        <tbody>
            <tr style="background-color: transparent;">
                <th style="padding-left: 0px;"><textarea name="footerContent" id="content_textarea" style="height: 250px;"></textarea></th>
            </tr>
        </tbody>
        
        <tfoot>
            <tr>
                <td colspan="2" style="padding-top: 35px;">
                    <input type="button" value="{function="localize('Cancel')"}" onclick="panthera.popup.close()" style="float: left; margin-left: 30px;">
                    <input type="submit" value="{function="localize('Save changes', 'newsletter')"}" style="float: right; margin-right: 30px;">
                </td>
            </tr>
        </tfoot>
    </table>
</form>
</div>
