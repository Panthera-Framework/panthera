<script>$('.ajax_link').click(function (event) { event.preventDefault(); navigateTo(jQuery(this).attr('href')); return false;});

/**
  * Make dump
  *
  * @author Mateusz Warzyński
  */

function makeDump()
{
    panthera.jsonPOST({ url: '?display=sqldump', data: 'dump=True', messageBox: 'userinfoBox', success: function (response) {
            if (response.status == "success")
                navigateTo('?display=sqldump');
        }
    });
    return false;
}

</script>

    <div class="titlebar">{"Database backup"|localize:database} - {"Backup your database to prevent data loss"|localize:database}{include file="_navigation_panel.tpl"}</div>

    <br>
    <div class="msgSuccess" id="userinfoBox_success"></div>
    <div class="msgError" id="userinfoBox_failed"></div>

    <div class="grid-1">
         <table class="gridTable">

            <thead>
                <tr><th colspan="5"><b>{"Avaliable dumps"|localize:database}:</b></th></tr>
             </thead>

            <tfoot>
                <tr>
                    <td colspan="5" class="rounded-foot-left"><em>Panthera - sqldump <input type="button" value="{"Create backup"|localize:database}" onclick="makeDump();" style="float: right;">  <input type="button" value="{"Manage permissions"|localize:messages}" onclick="createPopup('_ajax.php?display=acl&popup=true&name=can_manage_sql_dumps', 1024, 'upload_popup');" style="float: right;">
                    </em></td>
                </tr>
            </tfoot>

            <tbody>
                {foreach from=$dumps key=k item=v}
                <tr><td><a href="{$AJAX_URL}?display=sqldump&get={$v.name}&_bypass_x_requested_with">{$v.name}</a></td><td>{$v.size}</td><td>{$v.date}</td></tr>
                {/foreach}
            </tbody>
         </table>

    </div>
