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
        <h2 style="margin-top: 80px;">{function="slocalize('Error %s', 'messages', 404)"}</h2>
        {function="localize('Page you are looking couldnt be found')"}
    </span>
</div>
