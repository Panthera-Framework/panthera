    <nav class="tab-fixed">
      <ul class="tab-inner">
        <li><a onclick="navigateTo('?display=debug&cat=admin');">{function="localize('Debugging center')"}</a></li>
        <li class="active"><a data-ignore="true">{function="localize('Plugins debugger', 'debhook')"}</a></li>
      </ul>
    </nav>

    <div class="content inset">
      <div class="slider tab-slider">

        <ul>
            <li id="gallery" class="tab-item active">
                <ul class="list inset">

                 {if="$action == 'list'"}
                   {loop="$functions"}

                     {if="$value.type == 'method'"}
                          <li class="list-item-two-lines">
                            <a href="#" onclick="navigateTo('?display=browsefile&cat=admin&path={$value.filename}&start={$value.startline}&end={$value.endline}&back_btn={"?display=debhook&cat=admin"|base64_encode}');">
                               <h3 style="color: #A0D5FF;">{$value.name}( {$value.params} )</h3>
                               <p><b>{function="localize('method')"}</b> ({$value.declaration})</p>
                            </a>
                          </li>
                     {elseif="$value.type == 'class'"}
                          <li class="list-item-two-lines">
                            <div>
                               <h3 style="color: #33B5E5;"><b>{$value.name}</b></h3>
                               <p><b>{function="localize('class')"}</b></p>
                            </div>
                          </li>
                     {elseif="$value.type == 'function'"}
                          <li class="list-item-two-lines">
                            <a href="#" onclick="navigateTo('?display=browsefile&cat=admin&path={$value.filename}&start={$value.startline}&end={$value.endline}&back_btn={"?display=debhook&cat=admin"|base64_encode}');">
                               <h3>{$value.name}( {$value.params} )</h3>
                               <p><b>{function="localize('function')"}</b> ({$value.declaration})</p>
                            </a>
                          </li>
                     {/if}

                   {/loop}

                 {else}

                    <li class="list-divider">{function="localize('Hooked functions', 'debhook')"}</li>

                   {$where="?display=debhook&cat=admin"}
                   {loop="$hooks"}
                    <li class="list-item-two-lines">
                     <a href="#" onclick="navigateTo('?display=browsefile&cat=admin&path={$value.filename}&start={$value.startline}&end={$value.endline}&back_btn={$where|base64_encode}');">
                        <h3>{$value.hook} / {$value.function}( {$value.params} )</h3>
                        <p>{$value.declaration}</p>
                     </a>
                    </li>
                   {/loop}

                 {/if}

                </ul>
            </li>
        </ul>
      </div>
    </div>
