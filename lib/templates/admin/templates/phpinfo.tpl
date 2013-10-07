{$site_header}
{include="ui.titlebar"}

<style>
#phpinfoContent table {
    width: 80%;
    max-width: 80%;
}
</style>

<!--
<div style="text-align: center;">
<iframe src="?display=phpinfo&cat=admin&action=iframe&_bypass_x_requested_with=True" style="width: 840px; height: 600px;" style="border: 0px;"></iframe>
</div>
-->
<div class="ajax-content" style="text-align: center;">
    <div style="display: inline-block; width: 80%; overflow: auto;" id="phpinfoContent">
    {$phpinfoContent}
    </div>
</div>
