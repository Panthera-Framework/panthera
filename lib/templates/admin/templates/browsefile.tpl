<script>$('.ajax_link').click(function (event) { event.preventDefault(); navigateTo(jQuery(this).attr('href')); return false;});
</script>

        <div class="titlebar">{function="localize('File browser')"} - {$file_path}{include="_navigation_panel.tpl"}</div>
        
{if="$action == 'view'"}
            <table class="blueLog">
                <tr><td><b>{function="localize('Name')"}: </b></td><td>{$file_path}</td></tr>
                <tr><td><b>{function="localize('Mime-type', 'files')"}: </b></td><td>{$mime} ({$type})</td></tr>
                <tr><td><b>{function="localize('Permissions', 'files')"}: </b></td><td>{$perms}</td></tr>
                <tr><td><b>{function="localize('Owner', 'files')"}: </b></td><td>uid={$owner}, gid={$group}</td></tr>
                <tr><td><b>{function="localize('Size', 'files')"}: </b></td><td>{$size} ({$size_bytes}b)</td></tr>
                <tr><td><b>{function="localize('Modification time', 'files')"}: </b></td><td>{$modification_time}</td></tr>
            </table>

          <div class="blueLog">
             {$contents}
          </div>
          
         </div>
        </article>
{else}
        <div class="blueLog">
             {function="localize('Error')"}: {function="localize('file not found', 'files')"}
        </div>
{/if}
