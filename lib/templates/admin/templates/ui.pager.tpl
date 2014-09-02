{$pager=$uiPagers[$uiPagerName]}

    {function="localize('Pages')"}: {if="$pager.backBtn"}<a href="{$pager.backBtn.link}"{if="$pager.backBtn.onclick"} onclick="{$pager.backBtn.onclick}"{/if}><<</a>{/if}

    {loop="$pager['links']"}
        &nbsp;
        <a href="{$value.link}" class="pagerLink" {if="$value.active"}style="color: blue;"{/if} onclick="$('.pagerLink').css({'color': '#404c5a'}); $(this).css({'color': '#343e4a'}); {if="$value.onclick"}{$value.onclick}{/if}"><b>{$value.id}</b></a>
        &nbsp;{if="!isset($value.last)"}|{/if}
    {/loop}

    {if="$pager.nextBtn"}&nbsp;<a href="{$pager.nextBtn.link}"{if="$pager.nextBtn.onclick"} onclick="{$pager.nextBtn.onclick}"{/if}>>></a>{/if}{function="localize('Total')"}: {$pager.total},&nbsp; {function="localize('Total pages')"}: {$pager.pages}
