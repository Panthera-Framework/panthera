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
        event.preventDefault();
        panthera.jsonPOST({ data: '#newsletterFooterForm', messageBox: 'w2ui', mce: 'tinymce_all', success: function (response) {
                if (response.status == 'success')
                {
                    navigateTo('{$AJAX_URL}?display=compose_newsletter&cat=admin&nid={$nid}');
                }
            } 
        });
    });
});
</script>

{include="ui.titlebar"}

<div class="grid-1">
        <form id="newsletterFooterForm" action="{$AJAX_URL}?display=compose_newsletter&cat=admin&nid={$nid}&action=editFooter" method="POST">
        <div class="title-grid">{function="localize('Editing newsletter footer', 'newsletter')"}</div>
        <div class="content-gird">
             <table style="border: 0px; width: 100%;">
                 <tr>
                    <td colspan="2">
                        <textarea name="footerContent" id="content_textarea" style="width: 99%; height: 250px;"></textarea><br><br>
                    </td>
                 </tr>
                 
                 <tr>
                    <td colspan="2" style="padding-top: 15px;">
                        <input type="submit" value="{function="localize('Save')"}" style="float: right;">
                    </td>
                 </tr>
             </table>
        </div>
        </form>
</div>
