{$site_header}

{include="ui.titlebar"}

<div id="popupOverlay" style="text-align: center; padding-top: 20px; padding-bottom: 0px;"></div>

<!-- Content -->
<div class="ajax-content" style="text-align: center;">
    <table style="display: inline-block;">
            <thead>
                <tr><th colspan="5"><b>{function="localize('Error pages', 'errorpages')"}:</b></th></tr>
             </thead>

            <tfoot style="background: transparent;">
                <tr>
                   <td colspan="5" class="rounded-foot-left">
                    <input type="button" value="{function="localize('Manage permissions', 'messages')"}" onclick="panthera.popup.toggle('?display=acl&amp;cat=admin&amp;popup=true&amp;name=can_test_error_pages', 1300, 550);" style="float: right; margin-right: 7px;">
                   </td>
                </tr>
            </tfoot>

            <tbody>
                {loop="$errorPages"}
                {if="$value.notice == True"}
                <tr><td style="width: 40px;"><b>[{$value.visibility}]</td><td>{$value.name}</td><td colspan="2"><i>{function="localize('Please create a file', 'errorpages')"}: {$value.file}</i></td></tr>
                {else}
                <tr><td style="width: 40px;"><b>[{$value.visibility}]</td><td>{$value.name}</td><td><a href="#" onclick="navigateTo('{$AJAX_URL}?display=browsefile&cat=admin&path={$value.file}&back_btn={"?display=errorpages&cat=admin"|base64_encode}'); return false;">{$value.file}</a></td><td><input type="button" value="{function="localize('Trigger test', 'errorpages')"}" onclick="window.open('{$AJAX_URL}?display=errorpages&cat=admin&show={$value.testname}','error_window','width=1024,height=768'); return false;"></td></tr>
                {/if}
                {/loop}
            </tbody>
    </table>
</div>