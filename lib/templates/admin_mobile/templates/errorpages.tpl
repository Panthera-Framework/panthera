  <nav class="tab-fixed">
      <ul class="tab-inner">
        <li><a href="#" onclick="navigateTo('?display=dash&cat=admin&menu=settings');">{function="localize('Dash')"}</a></li>
        <li class="active"><a data-ignore="true">{function="localize('Errorpages')"}</a></li>
      </ul>
  </nav>

  <div class="content">

     <!-- Content -->
     <div class="slider tab-slider">
        <ul>
            <ul class="list inset">
              {loop="$errorPages"}
                   <li class="list-divider">[{$value.visibility}]</li>
               {if="$value.notice == True"}
                    <li class="list-item-two-lines">
                        <div>
                            <h3><b>{$value.name}</b></h3>
                            <p>{function="localize('Please create a file', 'errorpages')"}: {$value.file}</p>
                        </div>
                    </li>
                {else}
                    <li class="list-item-two-lines">
                        <div>
                            <button class="btn-small" style="float: right; display: none;" onclick="window.open('{$AJAX_URL}?display=errorpages&cat=admin&show={$value.testname}','error_window','width=1024,height=768'); return false;">{function="localize('Trigger test', 'errorpages')"}</button>
                            <h3><b>{$value.name}</b></h3>
                            <p>{$value.file}</p>
                        </div>
                    </li>
                {/if}
                <br>
              {/loop}

           </ul>
        </ul>
     </div>
    </div>