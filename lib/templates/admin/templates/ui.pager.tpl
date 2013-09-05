{$pager=$uiPagers[$uiPagerName]}

{function="localize('Pages')"}:

{if="$pager.backBtn"}<a href="{$pager.backBtn.link}"{if="$pager.backBtn.onclick"} onclick="{$pager.backBtn.onclick}"{/if}><<</a>{/if}

{loop="$pager['links']"}
    &nbsp;
    <a href="{$value.link}" class="pagerLink" {if="$value.active"}style="color: blue;"{/if} onclick="$('.pagerLink').css({'color': 'black'}); $(this).css({'color': 'blue'}); {if="$value.onclick"}{$value.onclick}{/if}"><b>{$value.id}</b></a>
    &nbsp;{if="!isset($value.last)"}|{/if}
{/loop}

&nbsp;{if="$pager.nextBtn"}<a href="{$pager.nextBtn.link}"{if="$pager.nextBtn.onclick"} onclick="{$pager.nextBtn.onclick}"{/if}>>></a>{/if}
