<script>$('.ajax_link').click(function (event) { event.preventDefault(); navigateTo(jQuery(this).attr('href')); return false;});</script>


        <div class="titlebar">{function="localize('Included files', 'includes')"} - {function="localize('List of all included files in current code execution', 'includes')"}</div>

        <br>

          <table class="gridTable">

            <thead>
                <tr><th colspan="5"><b>{function="localize('Files', 'includes')"}:</b></th></tr>
             </thead>

            <tfoot>
                <tr>
                    <td colspan="2" class="rounded-foot-left"><em>Panthera - {function="localize('includes', 'includes')"}
                    <input type="button" value="{function="localize('Manage permissions', 'messages')"}" onclick="createPopup('_ajax.php?display=acl&cat=admin&popup=true&name=can_see_debug');" style="float: right;"></em></td>
                </tr>
            </tfoot>

            <tbody>
                {$back_btn=base64_encode('?display=includes&cat=admin')}
                {loop="$files"}
                <tr><td><a href="#" onclick="navigateTo('?display=browsefile&cat=admin&path={$value}&back_btn={$back_btn}'); return false;">{$value}</a></td></tr>
                {/loop}
            </tbody>
           </table>
