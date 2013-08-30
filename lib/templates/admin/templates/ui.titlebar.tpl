<script type="text/javascript">
    {if="$uiTitlebar.backButton"}
    $("#navigationBackBtn").bind('mouseheld', function(e) { createPopup('?display=navigation_history&cat=admin', 1024, 620); });
    {/if}
    $('.ajax_link').click(function (event) { event.preventDefault(); navigateTo(jQuery(this).attr('href')); return false;});
</script>

<div class="titlebar">
    <span class="titleBarIcons">
        {loop="$uiTitlebar.icons.left"}
            {if="$value.link or $value.onclick"}
            <a href="{$value.link}"{if="$value.onclick"} onclick="{$value.onclick}"{/if}>
                <img src="{$value}" style="width: 25px;">
            </a>
            {else}
            <img src="{$value.image}" style="width: 25px;">
            {/if}
        {/loop}
    </span>
    
    {$uiTitlebar.title}
    
    <span style="float: right; margin-right: 10px; margin-top: -5px;">
        {loop="$uiTitlebar.icons.right"}
            {if="$value.link or $value.onclick"}
            <a href="{$value.link}"{if="$value.onclick"} onclick="{$value.onclick}"{/if}>
                <img src="{$value}" style="width: 25px;">
            </a>
            {else}
            <img src="{$value.image}" style="width: 25px;">
            {/if}
        {/loop}
    
        {if="$uiTitlebar.backButton"}
        <a href="#back-button" id="navigationBackBtn" onclick="navigateTo('{function="navigation::getBackButton()"}');">
            <img src="images/admin/tango-icon-theme/Go-previous.svg" style="width: 30px" title="{function="localize('Click and hold to see history')"}">
        </a>
        {/if}
    </span>
</div>
