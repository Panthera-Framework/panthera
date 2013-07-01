   {include 'header.tpl'}
  
   <nav class="tab-fixed">
      <ul class="tab-inner">
        <li class="active"><a data-ignore="true">{"Dash"|localize}</a></li>
      </ul>
   </nav>

   <div class="content">

     <!-- Content -->
     <div class="slider tab-slider">
        <ul>
          <li id="dash" class="tab-item active">
            <ul class="list inset">
             {foreach from=$dash_menu key=k item=v}
              <li class="list-item-single-line selectable">
                <a href="{$v.link|pantheraUrl}" data-ignore="true">
                    <img src="{$v.icon|pantheraUrl}" width="auto" height="38px" style="vertical-align: middle;">
                    <span style="vertical-align: middle;">{"$v.name"|localize}</span>
                </a>
              </li>
             {/foreach}
            </ul>
        </ul>
     </div>
     <!-- End of content -->

   </div>
   
   {include 'footer.tpl'}
