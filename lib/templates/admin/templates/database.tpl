<script>$('.ajax_link').click(function (event) { event.preventDefault(); navigateTo(jQuery(this).attr('href')); return false;});</script>


    <div class="titlebar">{"Database management"|localize:database}</div>

    <br>
    <div class="msgSuccess" id="userinfoBox_success"></div>
    <div class="msgError" id="userinfoBox_failed"></div>

    <div class="grid-1">
       <table class="gridTable">

            <thead>
                <tr><th colspan="5"><b>{"Connection informations"|localize:database}:</b></th></tr>
             </thead>

            <tfoot>
                <tr>
                    <td colspan="5" class="rounded-foot-left"><em>Panthera - {"Connection informations"|localize:database} <input type="button" value="{"Manage backups"|localize:database}" onclick="navigateTo('?display=sqldump');" style="float: right;">  <input type="button" value="{"Manage permissions"|localize:messages}" onclick="createPopup('_ajax.php?display=acl&popup=true&name=can_manage_databases', 1024, 'upload_popup');" style="float: right; margin-right: 7px;"> <input type="button" value="{"Back"|localize}" onclick="navigateTo('?display=settings&action=system_info');" style="float: right; margin-right: 7px;">
                    </em></td>
                </tr>
            </tfoot>

            <tbody>
                {foreach from=$sql_attributes key=k item=v}
                <tr><td>{$v.name}<td>{$v.value}</td></tr>
                {/foreach}
            </tbody>
       </table>

       <br><br>

       <table class="gridTable">

            <thead>
                <tr><th colspan="2"><b>Panthera - {"database driver configuration"|localize:database}:</b></th></tr>
             </thead>

            <tfoot>
                <tr>
                    <td colspan="2" class="rounded-foot-left"><em>Panthera - {"database driver configuration"|localize:database}</em></td>
                </tr>
            </tfoot>

            <tbody>
                {foreach from=$panthera_attributes key=k item=v}
                <tr><td>{$v.name}<td>{$v.value}</td></tr>
                {/foreach}
            </tbody>
       </table>
    </div>
