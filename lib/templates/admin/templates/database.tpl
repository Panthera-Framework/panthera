	{include="ui.titlebar"}

    <div class="grid-1">
       <table class="gridTable">

            <thead>
                <tr><th colspan="5"><b>{function="localize('Connection informations', 'database')"}:</b></th></tr>
             </thead>

            <tfoot>
                <tr>
                    <td colspan="5" class="rounded-foot-left"><em>Panthera - {function="localize('Connection informations', 'database')"} <input type="button" value="{function="localize('Manage backups', 'database')"}" onclick="navigateTo('?display=sqldump&cat=admin');" style="float: right;">  <input type="button" value="{function="localize('Manage permissions', 'messages')"}" onclick="createPopup('_ajax.php?display=acl&cat=admin&popup=true&name=can_manage_databases', 1024, 'upload_popup');" style="float: right; margin-right: 7px;">
                    </em></td>
                </tr>
            </tfoot>

            <tbody>
                {loop="$sql_attributes"}
                <tr><td>{$value.name}<td>{$value.value}</td></tr>
                {/loop}
            </tbody>
       </table>

       <br><br>

       <table class="gridTable">

            <thead>
                <tr><th colspan="2"><b>Panthera - {function="localize('database driver configuration', 'database')"}:</b></th></tr>
             </thead>

            <tfoot>
                <tr>
                    <td colspan="2" class="rounded-foot-left"><em>Panthera - {function="localize('database driver configuration', 'database')"}</em></td>
                </tr>
            </tfoot>

            <tbody>
               {loop="$panthera_attributes"}
                <tr>
                	<td>{$value.name}</td>
                  
                  {if="$value.type == 'bool'"}
                   
                   {if="$value.value == true"}
                  	<td>{function="localize('True')"}</td>
                   {else}
                   	<td>{function="localize('False')"}</td>
                   {/if}
                  
                  {else}
                	<td>{$value.value}</td>
                  {/if}
                </tr>
               {/loop}
            </tbody>
       </table>
    </div>
