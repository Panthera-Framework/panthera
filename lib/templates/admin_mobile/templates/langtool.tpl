{include="header.tpl"}

    <nav class="tab-fixed">
      <ul class="tab-inner">
        <li><a href="?display=debug">{function="localize('Debugging center')"}</a></li>
        <li class="active"><a data-ignore="true">{function="localize('Languages', 'langtool')"}</a></li>
      </ul>
    </nav>
    
    <div class="content">

     <!-- Content -->
     <div class="slider tab-slider">
        <ul>
          <li id="dash" class="tab-item active">
            <ul class="list inset">
             {loop="$locales"}
              <li class="list-item-single-line selectable">
                <a href="?display=langtool&action=domains&locale={$key}" data-ignore="true">
                    <img src="{$PANTHERA_URL}/images/admin/flags/{$key}.png" width="auto" height="38px" style="vertical-align: middle;">
                    <span style="vertical-align: middle;">{$key}</span>
                </a>
              </li>
             {/loop}
            </ul>
        </ul>
     </div>
     <!-- End of content -->
     
{include="footer.tpl"}
