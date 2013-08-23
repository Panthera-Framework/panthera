<script>$('.ajax_link').click(function (event) { event.preventDefault(); navigateTo(jQuery(this).attr('href')); return false;});</script>
<div class="titlebar">{function="localize('Settings', 'settings')"}{include="_navigation_panel"}</div>

{$uiSearchbarName="uiTop"}
{include="ui.searchbar"}
<style>
.hiddenTable tbody {
    display: none;
}
</style>

<div style="text-align: center; margin-top: 30px;">
    <div style="display: inline-block; overflow: hidden; margin: 0 auto; min-height: 500px; width: 60%;">
        {$j=0}
        {loop="$items"}
        {$j=$j+1}
        <table class="gridTable" style="margin-bottom: 20px;" id="section_{$j}">
        <thead>
            <tr style="cursor: pointer;" onclick="$('#section_{$j}').find('tbody').toggle('slow');">
                <th colspan="6">{$key|localize:settings|ucfirst}</th>
            </tr>
        </thead>
        
        <tbody>
            {$i=0}
            {loop="$value"}
            {$i=$i+1}
            {if="$i%2"}
            <tr style="cursor: pointer;">
            {/if}
                <td style="border-right: 0px; width: 20px; padding: 10px;">
                    <a href="{$value.link}" class="ajax_link"><img src="{$value.icon|pantheraUrl}" style="width: 48px;"></a>       
                </td>
                
                <td style="padding-right: 10px;">
                    <a href="{$value.link}" class="ajax_link"><b>{$value.name}</b><br>
                    <small><i>{$value.description}</i></small></a>
                </td>
            {if="$i%1"}
            </tr>
            {/if}
            {/loop}
        </tbody>
        </table>
        {/loop}
    </div>
</div>
