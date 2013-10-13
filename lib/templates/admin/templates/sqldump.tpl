{$site_header}
{include="ui.titlebar"}

<div id="topContent">
    <div class="searchBarButtonArea">
        <input type="button" value="{function="localize('Create backup', 'database')"}" onclick="makeDump();">
        <!--<input type="button" value="{function="localize('Automatic backup settings', 'database')"}" onclick="panthera.popup.toggle('?display=sqldump&cat=admin&action=settings')">-->
    </div>
</div>

    
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
                    {
                        navigateTo('?{function="getQueryString('GET', '', '_,action')"}');
                    }
                }
            });
            return false;
        }
        
    </script>
    
<div class="ajax-content" style="text-align: center;">
    <form action="?{function="getQueryString('GET', 'action=newCategory', '_')"}" method="POST" id="newCategoryForm">
    <div style="display: inline-block; margin: 0 auto;">
        <table style="margin: 0 auto;">
            <thead>
                <tr>
                    <th colspan="3">{function="localize('Avaliable dumps', 'database')"}:</th>
                </tr>
            </thead>
            
            <tbody>
                {if="!count($dumps)"}
                <tr>
                    <td colspan="5">
                        <p style="text-align: center;">{function="localize('There are no any backups created yet', 'database')"}</p>
                    </td>
                </tr>
                {else}
                {loop="$dumps"}
                <tr>
                    <td><a href="?display=sqldump&cat=admin&get={$value.name}&_bypass_x_requested_with">{$value.name}</a></th>
                    <td>{$value.size}</th>
                    <td>{$value.date}</td>
                </tr>
                {/loop}
                {/if}
            </tbody>
        </table>
        
        <div style="position: relative; text-align: left;" class="pager">{$uiPagerName="adminSQLDumps"}{include="ui.pager"}</div>
    </div>
    </form>
</div>
