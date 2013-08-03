    <nav class="tab-fixed">
      <ul class="tab-inner">
        <li><a href="#" onclick="navigateTo('?display=debug&cat=admin');">{"Debugging center"|localize}</a></li>
        <li class="active"><a data-ignore="true">{"Informations about system"|localize:settings}</a></li>
      </ul>
    </nav>

    <div class="content inset">
      <div class="slider tab-slider">
        <ul>
            <ul class="list inset">

             <li class="list-divider">{"Developer informations"|localize:settings}</li>
            {foreach from=$settings_list key=k item=v}
             <li class="list-item-multi-line selectable">
                    <h3>{$v</h3>
                    <p>{$k}</p>
             </li>
            {/foreach}

            </ul>
        </ul>
     </div>
    </div>