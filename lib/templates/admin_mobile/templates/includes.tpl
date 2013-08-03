    <nav class="tab-fixed">
      <ul class="tab-inner">
        <li><a href="?display=debug&cat=admin">{function="localize('Debugging center')"}</a></li>
        <li class="active"><a data-ignore="true">{function="localize('Includes')"}</a></li>
      </ul>
    </nav>

    <div class="content">

     <!-- Content -->
     <div class="slider tab-slider">
        <ul>
            <ul class="list inset">

             <li class="list-divider">{function="localize('Files', 'includes')"}:</li>

             {loop="$files"}
              <li class="list-item-single-line selectable">
                <a onclick="window.location = '?display=browsefile&cat=admin&path={$value}'" data-ignore="true" data-transition="push">
                    <p style="vertical-align: middle;">{$value}</p>
                </a>
              </li>
             {/loop}

            </ul>
        </ul>
     </div>
    </div>