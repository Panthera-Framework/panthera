<script type="text/javascript">
    {if="$uiTitlebar.backButton"}
    $("#navigationBackBtn").bind('mouseheld', function(e) { createPopup('?display=navigation_history&cat=admin', 1024, 620); });
    {/if}
    $('.ajax_link').click(function (event) { event.preventDefault(); navigateTo(jQuery(this).attr('href')); return false;});
</script>

<div class="titleBar">
    <span class="textTitleBar">{$uiTitlebar.title}
        <span class="titleBarIcons" style="float: right; align: right;">
            {loop="$uiTitlebar.icons.right"}
            {if="$value.link or $value.onclick"}
            <a href="{$value.link}"{if="$value.onclick"} onclick="{$value.onclick}"{/if} class="iconPopupLi" style="align: right;">
                <img src="images/admin/pantheraUI/transparent.png" class="pantheraIcon icon-{$value.image}" alt="Icon">
            </a>
            {else}
            <img src="images/admin/pantheraUI/transparent.png" class="pantheraIcon icon-{$value.image}" alt="Icon" style="align: right;">
            {/if}
            {/loop}
            
            {if="$titleBarInclude"}
                {include="$titleBarInclude"}
            {/if}
            
            {if="$uiTitlebar.backButton"}
            <a href="#back-button" id="navigationBackBtn" onclick="navigateTo('{function="navigation::getBackButton()"}');" class="iconPopupLi" style="align: right;">
                <img src="images/admin/pantheraUI/transparent.png" class="pantheraIcon icon-Back" alt="Icon">
            </a>
            {/if}
        </span>
    </span>
</div>
