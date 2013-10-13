{$site_header}
{include="ui.titlebar"}

<style>
#ajax_content {
    background-color: #56687b;
}

#topContent {
    min-height: 55px;
}

</style>

<div id="topContent">
    {$uiSearchbarName="uiTop"}
    {include="ui.searchbar"}
</div>

<div id="popupOverlay"></div>

<div class="settingsBackground">
    {loop="$items"}
    <div id="section_{$j}">
        <div class="titledSeparator">{function="localize(ucfirst($key), 'settings')"}</div>

        <div class="iconViewContainer">
            {loop="$value"}
            <div class="iconViewItem">
                <a href="{$value.link}" class="ajax_link"><img src="{$value.icon|pantheraUrl}" style="width: 48px;">
                <p>{$value.name} <br><span>{$value.description}</span></p></a>
            </div>
            {/loop}
        </div>
    </div>
    {/loop}
</div>
