    {include 'header.tpl'}

    <nav class="tab-fixed">
      <ul class="tab-inner">
        <li><a href="?display=debug" data-transition="push">{"Debugging center"|localize}</a></li>
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
                    <h3 style="font-size: 14px;">{$v}</h3>
                    <p>{$k}</p>
             </li>
            {/foreach}

            <br><br>

             <li class="list-divider">{"List of defined constants"|localize:settings}</li>
            {foreach from=$constants key=k item=v}
             <li class="list-item-multi-line selectable">
                    <h3>{$v}</h3>
                    <p>{$k}</p>
             </li>
            {/foreach}

            <br><br>

             <li class="list-divider">{"List of access controls for current user"|localize:settings}</li>
            {foreach from=$acl_list key=k item=v}
             <li class="list-item-multi-line selectable">
                    <h3>{$v}</h3>
                    <p>{$k}</p>
             </li>
            {/foreach}

            </ul>
        </ul>
     </div>

    </div>

    {include 'footer.tpl'}
