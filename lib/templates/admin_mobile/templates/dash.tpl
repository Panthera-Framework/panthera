   <nav class="tab-fixed">
      <ul class="tab-inner">
        <li class="active"><a data-ignore="true">{function="localize('Dash')"}</a></li>
      </ul>
   </nav>

   <div class="content">

     <div class="slider tab-slider">
        <ul>
          <li id="dash" class="tab-item active">
            <ul class="list inset">
             {loop="$dash_menu"}
              <li class="list-item-single-line selectable">
                <a href="{$value.link|pantheraUrl}" data-ignore="true">
                    <img src="{$value.icon|pantheraUrl}" width="auto" height="38px" style="vertical-align: middle;">
                    <span style="vertical-align: middle;">{function="localize($value.name)"}</span>
                </a>
              </li>
             {/loop}
            </ul>
        </ul>
     </div>

   </div>