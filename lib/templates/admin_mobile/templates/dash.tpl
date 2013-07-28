{include="header.tpl"}
  
   <nav class="tab-fixed">
      <ul class="tab-inner">
        <li class="active"><a data-ignore="true">{function="localize('Dash')"}</a></li>
      </ul>
   </nav>

   <div class="content">

     <!-- Content -->
     <div class="slider tab-slider">
        <ul>
          <li id="dash" class="tab-item active">
            <ul class="list inset">
                
              <li class="list-divider">Menu</li>
              
              {loop="$admin_menu"}
               <li class="list-item-single-line selectable">
                <a href="{$value.link}" data-ignore="true">
                    {if="$value.icon != ''"}
                        <img src="{$value.icon|pantheraUrl}" width="auto" height="38px" style="vertical-align: middle;">
                        <span style="vertical-align: middle;">{$value.title}</span>
                    {else}
                        <span style="vertical-align: middle; margin-left: 41px;">{$value.title}</span>                   
                    {/if}
                </a>
               </li>
              {/loop}
              
              <br>
              
              <li class="list-divider">Dash</li>
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
     <!-- End of content -->

   </div>
   
   {include="footer.tpl"}
