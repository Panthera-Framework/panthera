   <nav class="tab-fixed">
      <ul class="tab-inner">
        <li><a href="#" onclick="navigateTo('?display=includes&cat=admin');">{function="localize('Includes', 'debug')"}</a></li>
        <li class="active"><a data-ignore="true">{function="localize('File browser')"}</a></li>
      </ul>
    </nav>

    <div class="content inset">
      <div class="slider tab-slider">

        <ul>
            <li id="gallery" class="tab-item active">
                <ul class="list inset">

                   {if="$action == 'view'"}

                    <li class="list-item-two-lines">
                        <div>
                            <h3>{$file_path}</h3>
                            <p>{function="localize('Name')"}</p>
                        </div>
                    </li>

                    <li class="list-item-two-lines">
                        <div>
                            <h3>{$mime} ({$type})</h3>
                            <p>{function="localize('Mime-type', 'files')"}</p>
                        </div>
                    </li>

                    <li class="list-item-two-lines">
                        <div>
                            <h3>{$perms}</h3>
                            <p>{function="localize('Permissions', 'files')"}</p>
                        </div>
                    </li>

                    <li class="list-item-two-lines">
                        <div>
                            <h3>uid={$owner}, gid={$group}</h3>
                            <p>{function="localize('Owner', 'files')"}</p>
                        </div>
                    </li>

                    <li class="list-item-two-lines">
                        <div>
                            <h3>{$size} ({$size_bytes}b)</h3>
                            <p>{function="localize('Size', 'files')"}</p>
                        </div>
                    </li>

                    <li class="list-item-two-lines">
                        <div>
                            <h3>{$modification_time}</h3>
                            <p>{function="localize('Modification time', 'files')"}</p>
                        </div>
                    </li>

                    <br>

                    <label>{function="localize('Content', 'debug')"}</label>
                    <li class="list-item-multi-lines">
                        <div style="background: #EDF6FF; color: black;">
                            {$contents}
                        </div>
                    </li>

                   {else}

                    <li class="list-item-single-line">
                        <div>
                            <h3 style="color: red;">{function="localize('Error')"}: {function="localize('file not found', 'files')"}!</h3>
                        </div>
                    </li>

                   {/if}

             </ul>
        </ul>
     </div>
    </div>
