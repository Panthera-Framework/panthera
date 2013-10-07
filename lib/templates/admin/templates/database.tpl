{$site_header}
{include="ui.titlebar"}

<div id="topContent">
    <div class="searchBarButtonArea">
        <input type="button" value="{function="localize('Manage backups', 'database')"}" onclick="panthera.popup.toggle('element:#sqlDump')">
        <input type="button" value="{function="localize('Manage permissions', 'messages')"}" onclick="panthera.popup.toggle('?display=acl&cat=admin&popup=true&name=can_manage_databases');">
    </div>
</div>

<div id="popupOverlay" style="text-align: center; padding-top: 20px; padding-bottom: 0px;"></div>
<!-- Content -->
<div class="ajax-content" style="text-align: center;">

    <table style="width: 80%; margin: 0 auto; margin-bottom: 30px;">
        <thead>
            <tr>
                <th colspan="5"><b>{function="localize('Connection informations', 'database')"}:</b></th>
            </tr>
        </thead>
        <tbody>
            {loop="$sql_attributes"}
            <tr>
                <td>{$value.name}
                <td>{$value.value}</td>
            </tr>
            {/loop}
        </tbody>
    </table>
    
    <table style="margin: 0 auto; width: 80%;">
        <thead>
            <tr>
                <th colspan="2"><b>Panthera - {function="localize('database driver configuration', 'database')"}:</b></th>
            </tr>
        </thead>
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

<!-- Sqldump -->
<div id="sqlDump" style="display: none;">
    <form action="?{function="getQueryString('GET', 'action=newCategory', '_')"}" method="POST" id="newCategoryForm">
    <table class="formTable" style="margin: 0 auto; margin-bottom: 30px;">
        <thead>
            <tr>
                <td colspan="2" class="formTableHeader" style="padding-top: 0px; padding-bottom: 30px;">
                    <p style="color: #e5ebef; padding: 0px; margin: 0px; margin-left: 30px;">{function="localize('Avaliable dumps', 'database')"}:</p>
                </td>
            </tr>
        </thead>
        <tfoot>
            <tr>
                <td colspan="2" style="padding-top: 35px;">
                    <input type="button" value="{function="localize('Create backup', 'database')"}" onclick="makeDump();" style="float: right; margin-right: 30px;">
                    <input type="button" value="{function="localize('Cancel')"}" onclick="panthera.popup.close()" style="float: left; margin-left: 30px;">
                </td>
            </tr>
        </tfoot>
        <tbody>
            {if="count($dumps) < 1"}
            <tr>
                <th colspan="5">
                    <p style="text-align: center;">{function="localize('Sorry, you have not any backups', 'database')"}!</p>
                </th>
            </tr>
            {else}
            {loop="$dumps"}
            <tr>
                <th><a href="{$AJAX_URL}?display=sqldump&cat=admin&get={$value.name}&_bypass_x_requested_with">{$value.name}</a></th>
                <th>{$value.size}</th>
                <th>{$value.date}</th>
            </tr>
            {/loop}
            {/if}
        </tbody>
    </table>
    </form>
    
    <script type="text/javascript">
        /**
          * Make dump
          *
          * @author Mateusz Warzyński
          */
        
        function makeDump()
        {
            panthera.jsonPOST({ url: '?display=sqldump&cat=admin', data: 'dump=True', messageBox: 'w2ui', success: function (response) {
                    if (response.status == "success")
                        panthera.popup.close();
                }
            });
            return false;
        }
        
    </script>
</div>
