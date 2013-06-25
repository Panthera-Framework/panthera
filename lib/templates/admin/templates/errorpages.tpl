<script>$('.ajax_link').click(function (event) { event.preventDefault(); navigateTo(jQuery(this).attr('href')); return false;});

$(document).ready(function(){
    $('#upload_form').ajaxForm({ dataType: 'html',
        success: function(response) {
            $('#syschecksum_window').html(response);
        }
    });
});

</script>

    <div class="titlebar">{"System error pages"|localize:errorpages} - {"Test system error pages in one place"|localize:errorpages}{include file="_navigation_panel.tpl"}</div>

    <br>
    <div class="msgSuccess" id="userinfoBox_success"></div>
    <div class="msgError" id="userinfoBox_failed"></div>

    <div class="grid-1">

          <table class="gridTable">
            <thead>
                <tr><th colspan="5"><b>{"Error pages"|localize:errorpages}:</b></th></tr>
             </thead>

            <tfoot>
                <tr>
                    <td colspan="5" class="rounded-foot-left"><em>Panthera - {"Error pages"|localize:errorpages} <input type="button" value="{"Back"|localize}" onclick="navigateTo('?display=settings&action=system_info');" style="float: right;">
                    <input type="button" value="{"Manage permissions"|localize:messages}" onclick="createPopup('_ajax.php?display=acl&popup=true&name=can_test_error_pages', 1024, 'upload_popup');" style="float: right; margin-right: 7px;"></em></td>
                </tr>
            </tfoot>

            <tbody>
                {foreach from=$errorPages key=k item=v}
                {if $v.notice == True}
                <tr><td style="width: 40px;"><b>[{$v.visibility}]</td><td>{$v.name}</td><td colspan="2"><i>{"Please create a file"|localize:errorpages}: {$v.file}</i></td></tr>
                {else}
                <tr><td style="width: 40px;"><b>[{$v.visibility}]</td><td>{$v.name}</td><td><a href="#" onclick="navigateTo('{$AJAX_URL}?display=browsefile&path={$v.file}&back_btn={"?display=errorpages"|base64_encode}'); return false;">{$v.file}</a></td><td><input type="button" value="{"Trigger test"|localize:errorpages}" onclick="window.open('{$AJAX_URL}?display=errorpages&show={$v.testname}','error_window','width=1024,height=768'); return false;"></td></tr>
                {/if}
                {/foreach}
            </tbody>
          </table>

    </div>
