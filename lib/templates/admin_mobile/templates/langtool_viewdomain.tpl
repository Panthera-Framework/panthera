{include="header.tpl"}
  <!-- Content -->
    <nav class="tab-fixed">
      <ul class="tab-inner">
        <li><a href="?display=langtool&action=domains&locale={$locale}">{function="localize('Manage domains', 'langtool')"}</a></li>
        <li class="active"><a data-ignore="true">{function="localize('Translates for', 'langtool')"} {$domain}</a></li>
      </ul>
    </nav>
    
    <div class="content inset">
     <div class="slider tab-slider">
        <ul>
            <ul class="list inset">
            
            {loop="$translates"}
              <li class="list-item-two-lines">
                    {loop="$value"}
                      {if="$key == $locale"}
                        <h3>{$value}</h3>
                      {/if}
                    {/loop}
                    <p>{$key}</p>
              </li>
            {/loop}

  <!-- End of content -->
{include="footer.tpl"}