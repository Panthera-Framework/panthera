    <nav class="tab-fixed">
      <ul class="tab-inner">
        <li><a href="#" onclick="navigateTo('?display=debug&cat=admin');" data-transition="push">{function="localize('Debugging center')"}</a></li>
        <li class="active"><a data-ignore="true">{function="localize('Informations about system', 'settings')"}</a></li>
      </ul>
    </nav>

    <div class="content inset">
      <div class="slider tab-slider">
        <ul>
            <ul class="list inset">

             <li class="list-divider">{function="localize('Developer informations', 'settings')"}</li>
            {loop="$settings_list"}
             <li class="list-item-multi-line selectable">
                    <h3 style="font-size: 14px;">{$value}</h3>
                    <p>{$key}</p>
             </li>
            {/loop}

            <br><br>

             <li class="list-divider">{function="localize('List of defined constants', 'settings')"}</li>
            {loop="$constants"}
             <li class="list-item-multi-line selectable">
                    <h3>{$value}</h3>
                    <p>{$key}</p>
             </li>
            {/loop}

            <br><br>

             <li class="list-divider">{function="localize('List of access controls for current user', 'settings')"}</li>
            {loop="$acl_list"}
             <li class="list-item-multi-line selectable">
                    <h3>{$value}</h3>
                    <p>{$key}</p>
             </li>
            {/loop}

            </ul>
        </ul>
     </div>

    </div>