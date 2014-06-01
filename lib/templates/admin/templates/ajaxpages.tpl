{$site_header}

{include="ui.titlebar"}
 
<script type="text/javascript">
    function resetCache () {
        panthera.jsonPOST({url: '?display=ajaxpages&cat=admin&action=forceResetCache', success: function (response) {
            if (response.status == 'success')
                navigateTo(window.location.href);
        }});
    }
</script>

<div id="topContent">
    {$uiSearchbarName="uiTop"}
    {include="ui.searchbar"}
    <div class="searchBarButtonArea">
    	<input type="button" value="{function="localize('List of front controllers', 'debug')"}" onclick="navigateTo('?display=frontcontrollers&cat=admin');">
        <input type="button" value="{function="localize('Refresh')"}" onclick="resetCache();">
    </div>
</div>

<div class="ajax-content" style="text-align: center;">
    <div class="tipBlock" style="width: 45%;">
        <div class="tipBlockInside">
            {function="localize('Listed permissions are from $permissions and $actionPermissions variables', 'ajaxpages')"}
        </div>
    </div>
    
    <table style="display: inline-block;">
            <thead>
                <tr>
                    <th>
                        <b>{function="localize('Location', 'ajaxpages')"}</b>
                    </th>
                    
                    <th>
                        <b>{function="localize('Directory', 'ajaxpages')"}</b>
                    </th>
                    
                    <th>
                        <b>{function="localize('Page name', 'ajaxpages')"}</b>
                    </th>
                    
                    <th>
                        <b>{function="localize('Objective interface', 'ajaxpages')"}</b>
                    </th>
                    
                    <th>
                        <b>{function="localize('Permissions', 'ajaxpages')"}</b>
                    </th>
                    
                    <th>
                        <b>{function="localize('Modification time', 'ajaxpages')"}</b>
                    </th>
                    
                    <!--<th>
                        <b>{function="localize('Full path', 'ajaxpages')"}</b>
                    </th>-->
                </tr>
            </thead>
            <tbody class="hovered">
                 {if="$pages"}
                 {loop="$pages"}
                     {if="isset($value.info)"}
                        <tr>
                            <td></td>
                            <td></td>
                            <td colspan="2"><i>-> {$value.info}{if="$value.title"} ({$value.title}){/if}</i></td>
                            <td colspan="3" style="cursor: pointer;">{if="$value.permissionsWarning"}<b title="{function="localize('Warning! No permission check found for this action. This action may be available to all users and guests!', 'ajaxpages')"}" style="color:red;">{/if}{$value.permissions}{if="$value.permissionsWarning"}</b>{/if}</td>
                        </tr>
                     {else}
                        <tr>
                            <td>{$value.location}</td>
                            <td title="{$value.path}">{$value.directory}</td>
                            <td><a href="{$value.link}&cat=admin" class="ajax_link"><b>{$value.name}</b></a>{if="$value.title"} <i title="{function="localize('This is readable from uiTitlebar variable', 'ajaxpages')"}">({$value.title})</i>{/if}</td>
                            <td>{if="$value.objective"}{function="localize('Yes')"}{else}<a style="color: red; cursor: pointer;" title="{function="localize('Structural controllers are unsecure and not flexible, please upgrade', 'ajaxpage')"}">{function="localize('No')"}</a>{/if}</td>
                            <td>{if="$value.permissionsWarning"}<a title="{function="localize('Warning! This controller does not implement any permissions check! Please implement check for global or action permissions.', 'ajaxpages')"}" style="color: red; cursor: pointer;"><b>None</b></a>{else}{if="$value.permissions"}<a style="cursor: pointer;" title="{function="localize('This global permission will overwrite all action permissions', 'ajaxpages')"}"><b>{$value.permissions}</b></a>{/if}{/if}</td>
                            <td>{$value.modtime}</td>
                        </tr>
                     {/if}
                 {/loop}
                 {else}
                    <tr>
                        <td colspan="6" style="text-align: center;">{function="localize('No data to display', 'admin')"}</td>
                    </tr>
                 {/if}
            </tbody>
    </table>
</div>
