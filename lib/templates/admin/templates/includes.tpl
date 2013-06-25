<script>$('.ajax_link').click(function (event) { event.preventDefault(); navigateTo(jQuery(this).attr('href')); return false;});</script>


        <div class="titlebar">{"Included files"|localize:includes} - {"List of all included files in current code execution"|localize:includes}</div>

        <br>

          <table class="gridTable">

            <thead>
                <tr><th colspan="5"><b>{"Files"|localize:includes}:</b></th></tr>
             </thead>

            <tfoot>
                <tr>
                    <td colspan="2" class="rounded-foot-left"><em>Panthera - {"includes"|localize:includes}
                    <input type="button" value="{"Manage permissions"|localize:messages}" onclick="createPopup('_ajax.php?display=acl&popup=true&name=can_see_debug');" style="float: right;"></em></td>
                </tr>
            </tfoot>

            <tbody>
                {foreach from=$files key=k item=v}
                <tr><td><a href="#" onclick="navigateTo('?display=browsefile&path={$v}&back_btn={"?display=includes"|base64_encode}'); return false;">{$v}</a></td></tr>
                {/foreach}
            </tbody>
           </table>
