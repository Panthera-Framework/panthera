<script type="text/javascript">
    if (!window.jQuery)
    {
        window.location = '{$PANTHERA_URL}/pa-admin.php';
    }
</script>

<style>
#ajax_content {
    background-color: #56687b;
}

#topContent {
    min-height: 55px;
}

</style>

<div class="settingsBackground" style="color: white;">
    <img src="{$PANTHERA_URL}/images/admin/pantheraUI/logo-big.png" style="margin: 30px; float: left;">
    <span style="float: left;">
        {if="$uiNoAccess.loggedIn"}
            {if="!$uiTitlebar.title"}
            {$uiTitlebar.title=localize('No enough permissions', 'login')}
            {/if}
            
        <h2 style="margin-left: 25px; margin-top: 80px;">{function="localize('You dont have enough permissions to execute this action', 'login')"}</h2>
        
        <ul>
            <li>{function="localize('Please check account you are using', 'login')"}</li>
            <li>{function="localize('If you think you should be able to access this page please contact Administrator', 'login')"}</li>
        </ul>
        {else}
        <h2 style="margin-left: 25px; margin-top: 80px;">{function="localize('To continue you must first sign in', 'login')"}.</h2>
    
        <div class="grid-1">
            <br><a href="pa-admin.php" style="color: white; margin-left: 30px;">{function="localize('Proceed', 'login')"}.</a>
        </div>
        {/if}
    </span>
</div>
