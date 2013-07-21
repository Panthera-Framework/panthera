<script>$('.ajax_link').click(function (event) { event.preventDefault(); navigateTo(jQuery(this).attr('href')); return false;});</script>

    <div class="titlebar">{function="localize('System error pages', 'errorpages')"} - {function="localize('Test system error pages in one place', 'errorpages')"}{include="_navigation_panel.tpl"}</div>

    <br>
    <div class="msgSuccess" id="userinfoBox_success"></div>
    <div class="msgError" id="userinfoBox_failed"></div>

    <div class="grid-1">

          <table class="gridTable">
            <thead>
                <tr><th colspan="5"><b>{function="localize('Error pages', 'errorpages')"}:</b></th></tr>
             </thead>

            <tfoot>
                <tr>
                    <td colspan="5" class="rounded-foot-left"><em>Panthera - {function="localize('Error pages', 'errorpages')"}
                    <input type="button" value="{function="localize('Manage permissions', 'messages')"}" onclick="createPopup('_ajax.php?display=acl&popup=true&name=can_test_error_pages', 1024, 'upload_popup');" style="float: right; margin-right: 7px;"></em></td>
                </tr>
            </tfoot>

            <tbody>
                {loop="$errorPages"}
                {if="$value.notice == True"}
                <tr><td style="width: 40px;"><b>[{$value.visibility}]</td><td>{$value.name}</td><td colspan="2"><i>{function="localize('Please create a file', 'errorpages')"}: {$value.file}</i></td></tr>
                {else}
                <tr><td style="width: 40px;"><b>[{$value.visibility}]</td><td>{$value.name}</td><td><a href="#" onclick="navigateTo('{$AJAX_URL}?display=browsefile&path={$value.file}&back_btn={"?display=errorpages"|base64_encode}'); return false;">{$value.file}</a></td><td><input type="button" value="{function="localize('Trigger test', 'errorpages')"}" onclick="window.open('{$AJAX_URL}?display=errorpages&show={$value.testname}','error_window','width=1024,height=768'); return false;"></td></tr>
                {/if}
                {/loop}
            </tbody>
          </table>

    </div>
