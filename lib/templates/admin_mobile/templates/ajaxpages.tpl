    <nav class="tab-fixed">
      <ul class="tab-inner">
        <li><a href="#" onclick="navigateTo('?display=dash&cat=admin&menu=settings');">{function="localize('Dash')"}</a></li>
        <li class="active"><a data-ignore="true">{function="localize('Index of ajax pages', 'ajaxpages')"}</a></li>
      </ul>
    </nav>

    <div class="content">

     <div class="slider tab-slider">
        <ul>
            <ul class="list inset">
                <li class="list-divider">{function="localize('Pages index', 'ajaxpages')"}:</li>

                {loop="$pages"}
                <li class="list-item-single-line selectable">
                    <a href="{$value.link}" data-ignore="true">
                        <p>{$value.location} / {$value.name}</p>
                    </a>
                </li>
                {/loop}

            </ul>
        </ul>
     </div>
    </div>