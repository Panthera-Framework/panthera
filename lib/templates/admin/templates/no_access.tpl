<script type="text/javascript">
    if (!window.jQuery)
    {
        window.location = '{$PANTHERA_URL}/pa-admin.php';
    }
</script>

{if="$uiNoAccess.loggedIn"}
    {if="!$uiTitlebar.title"}
        {$uiTitlebar.title=localize('No enough permissions', 'login')}
    {/if}
    {include="ui.titlebar"}<br>
    
    <h2 style="margin-left: 25px;">{function="localize('You dont have enough permissions to execute this action', 'login')"}</h2>
    
    <ul>
        <li>{function="localize('Please check account you are using', 'login')"}</li>
        <li>{function="localize('If you think you should be able to access this page please contact Administrator', 'login')"}</li>
    </ul>
    
    <div class="grid-1">
        <br><a href="pa-admin.php">{function="localize('Sign in', 'login')"}</a>
    </div>

{else}
    {if="!$uiTitlebar.title"}
        {$uiTitlebar.title=localize('Authentication required', 'login')}
    {/if}
    {include="ui.titlebar"}<br>
    
    <h2 style="margin-left: 25px;">{function="localize('To continue you must first sign in', 'login')"}.</h2>
    
    <div class="grid-1">
        <br><a href="pa-admin.php">{function="localize('Proceed', 'login')"}</a>
    </div>
{/if}
