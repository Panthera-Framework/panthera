{$site_header}

{if="!$showWidgets"}
<style>
#ajax_content {
    background-color: #56687b;
}

</style>
{/if}

<script type="text/javascript">
var widgetsUnlocked = 0;

/**
  * Toogle lock
  *
  * @author Damian Kęska
  */

function toggleWidgetsLock(lock)
{
    if (widgetsUnlocked == 1 || lock == 1)
    {
        $('#widgetsLockedImg').removeClass('icon-Locked');
        $('#widgetsLockedImg').addClass('icon-Unlocked');
        
        $('#widgetsLockedSpan').html('{function="localize('Unlock widgets', 'dash')"}');
        $('.widgetRemoveButtons').hide();
        $('#newWidgetIcon').hide();
        widgetsUnlocked = 9;
    } else {
        $('#widgetsLockedImg').removeClass('icon-Unlocked');
        $('#widgetsLockedImg').addClass('icon-Locked');
        $('#widgetsLockedSpan').html('{function="localize('Lock widgets', 'dash')"}');
        $('.widgetRemoveButtons').show();
        $('#newWidgetIcon').show();
        widgetsUnlocked = 1;
    }
}
 
/**
  * Remove widget
  *
  * @author Damian Kęska
  */

function removeWidget(widgetName)
{
    navigateTo('?display=dash&cat=admin&widget='+widgetName+'&action=remove');
}

/**
  * Add new widget
  *
  * @author Damian Kęska
  */

$(function(){
	if ({$widgetsUnlocked} == 1)
	    toggleWidgetsLock(0);
	else
	    toggleWidgetsLock(1);
});

</script>
{$titleBarInclude='dash.titlebar'}
{include="ui.titlebar"}

<div id="topContent">
    <div class="dash">
        <div class="separator">&nbsp;</div>
        
        {loop="$dash_menu"}
        <div class="dashItem">
            <a class="{if="$value.linkType == 'ajax'"}ajax_link{/if}"{if="$value.linkType == 'onclick'"} onclick="{$value.link}"{else} href="{$value.link|pantheraUrl}"{/if} style="cursor: pointer;">
                <img src="{$value.icon|pantheraUrl}" alt="Avatar" class="icon">
                <p>{$value.name}</p>
            </a>
        </div>
        <div class="separator">&nbsp;</div>
        {/loop}
    </div>
</div>

{loop="$dash_messages"}
{if="$value.type == 'warning'"}
<div class="msgWarning" style="display: block;">{$value.message}</div>
{/if}

{if="$value.type == 'error'"}
<div class="msgError" style="display: block;">{$value.message}</div>
{/if}

{if="$value.type == 'info'"}
<div class="msgInfo" style="display: block;">{$value.message}</div>
{/if}

{if="$value.type == 'success'"}
<div class="msgSuccess" style="display: block;">{$value.message}</div>
{/if}
{/loop}
            
<div class="ajax-content">
         
{if="isset($showWidgets)"}
    {loop="$dashCustomWidgets"}
        {$value}
    {/loop}
{/if}
</div>
