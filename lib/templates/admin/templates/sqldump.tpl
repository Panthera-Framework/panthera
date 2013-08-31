<script>$('.ajax_link').click(function (event) { event.preventDefault(); navigateTo(jQuery(this).attr('href')); return false;});

/**
  * Make dump
  *
  * @author Mateusz Warzyński
  */

function makeDump()
{
    panthera.jsonPOST({ url: '?display=sqldump&cat=admin', data: 'dump=True', messageBox: 'w2ui', success: function (response) {
            if (response.status == "success")
                navigateTo('?display=sqldump&cat=admin');
        }
    });
    return false;
}

</script>

	{include="ui.titlebar"}
	
    <div class="grid-1">
         <table class="gridTable">

            <thead>
                <tr><th colspan="5"><b>{function="localize('Avaliable dumps', 'database')"}:</b></th></tr>
             </thead>

            <tfoot>
                <tr>
                    <td colspan="5" class="rounded-foot-left"><em>Panthera - sqldump <input type="button" value="{function="localize('Create backup', 'database')"}" onclick="makeDump();" style="float: right;">  <input type="button" value="{function="localize('Manage permissions', 'messages')"}" onclick="createPopup('_ajax.php?display=acl&cat=admin&popup=true&name=can_manage_sql_dumps', 1024, 'upload_popup');" style="float: right;">
                    </em></td>
                </tr>
            </tfoot>

            <tbody>
                {loop="$dumps"}
                <tr><td><a href="{$AJAX_URL}?display=sqldump&cat=admin&get={$value.name}&_bypass_x_requested_with">{$value.name}</a></td><td>{$value.size}</td><td>{$value.date}</td></tr>
                {/loop}
            </tbody>
         </table>

    </div>
