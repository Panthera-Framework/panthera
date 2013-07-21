{include="header.tpl"}

    <nav class="tab-fixed">
      <ul class="tab-inner">
        <li><a href="?display=debug">{function="localize('Languages', 'langtool')"}</a></li>
        <li class="active"><a data-ignore="true">{function="localize('Manage domains', 'langtool')"}</a></li>
      </ul>
    </nav>
    
    <div class="content">

     <!-- Content -->
     <div class="slider tab-slider">
        <ul>
          <li id="dash" class="tab-item active">
            <ul class="list inset">
             {loop="$domains"}
              <li class="list-item-single-line selectable">
                <a href="?display=langtool&action=view_domain&locale={$locale}&domain={$value}" data-ignore="true">
                    <img src="{$PANTHERA_URL}/images/admin/flags/{$locale}.png" width="auto" height="38px" style="vertical-align: middle;">
                    <span style="vertical-align: middle;">{$value}</span>
                </a>
              </li>
             {/loop}
            </ul>
        </ul>
     </div>
     <!-- End of content -->
     
{include="footer.tpl"}
