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
    <div class="searchBarButtonArea">
        <input type="button" value="{if="$debug == true"}{function="localize('Turn off debugger')"}{else}{function="localize('Turn on debugger')"}{/if}" onclick="toggleDebugValue()" id="buttonToggleDebuggerState">
        <input type="button" value="{function="localize('Options')"}" onclick="panthera.popup.toggle('element:#options')">
    </div>
</div>

<div id="popupOverlay"></div>

<div class="settingsBackground">
    <div id="section">
        <div class="iconViewContainer">
           {loop="$tools"}
            <div class="iconViewItem">
                <a href="{$value.link}" class="ajax_link"> <img src="{$value.icon|pantheraUrl}" style="width: 48px;">
                <p>{function="localize($value.name, 'debug')"} <br><span>{function="localize($value.description, 'debug')"}</span></p></a>
            </div>
           {/loop}
        </div>
    </div>
</div>


