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
    <div class="searchBarButtonArea">
        <input type="button" value="{function="localize('Refresh')"}" onclick="resetCache();">
    </div>
</div>

<div id="popupOverlay" style="text-align: center; padding-top: 20px; padding-bottom: 0px;"></div>
<!-- Content -->
<div class="ajax-content" style="text-align: center;">
    <div class="tipBlock" style="width: 45%;">
        <div class="tipBlockInside">
            {function="localize('Listed permissions are taken from objective controller\'s variables - $permissions and $actionPermissions', 'acl')"}
        </div>
    </div>
    
    <table style="width: 50%; margin: 0 auto; margin-bottom: 30px;">
        <thead>
            <tr>
                <th>{function="localize('Name')"}</th>
                <th>{function="localize('Title')"}</th>
            </tr>
        </thead>
        <tbody>
            {loop="$permissions"}
            <tr>
                <td>{$key}</td>
                <td>{$value}</td>
            </tr>
            {/loop}
        </tbody>
    </table>
</div>
