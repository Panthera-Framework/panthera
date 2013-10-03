{if="isset($showWidgets)"}
<!-- add new widget button -->

{if="count($dashAvaliableWidgets)"}
<span data-searchbardropdown="#searchDropdown" id="searchDropdownSpan" style="position: relative; cursor: pointer;">
    <img src="{$PANTHERA_URL}/images/admin/pantheraUI/transparent.png" class="pantheraIcon icon-Add" alt="{function="localize('Add new widget', 'dash')"}">
</span>

<div id="searchDropdown" class="searchBarDropdown searchBarDropdown-tip searchBarDropdown-relative" style="display: block; left: 519.53125px; top: 21px;">
    <ul class="searchBarDropdown-menu">
    {loop="$dashAvaliableWidgets"}{if="!$value and $key"}
        <li><a href="?display=dash&cat=admin&widget={$key}&action=add" class="ajax_link">{$key}</a></li>
    {/if}{/loop}
    </ul>
</div>
{/if}

<!-- lock and unlock widgets button -->
<a href="#" onclick="toggleWidgetsLock();" style="align: right;">
    <img src="{$PANTHERA_URL}/images/admin/pantheraUI/transparent.png" id="widgetsLockedImg" class="pantheraIcon icon-{if="$widgetsUnlocked == 0"}Unlocked{else}Locked{/if}" alt="{if="$widgetsUnlocked == 0"}{function="localize('Unlock widgets', 'dash')"}{else}{function="localize('Lock widgets', 'dash')"}{/if}">
</a>

<!-- permissions popup -->
<a href="#" onclick="createPopup('?display=acl&cat=admin&popup=true&name=can_see_dash');" style="align: right;>
    <img src="{$PANTHERA_URL}/images/admin/pantheraUI/transparent.png" class="pantheraIcon icon-Users" alt="{function="localize('Manage permissions')"}">
</a>
{/if}
