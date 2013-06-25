<script>$('.ajax_link').click(function (event) { event.preventDefault(); navigateTo(jQuery(this).attr('href')); return false;});


$(document).ready(function(){
    $('#toolbar').w2toolbar({
	    name: 'toolbar',
	    items: [
		    {if isset($back_btn)}{ type: 'button',  id: 'back',  caption: '{"Back"|localize}' },{/if}
		    { type: 'button',  id: 'full_content',  caption: '{"View full content"|localize}' },
	    ],onClick: function(target, eventData) {
            switch (target)
            {
                case 'back':
                    navigateTo('{$back_btn}');
                break;
                
                case 'full_content':
                    navigateTo('{$AJAX_URL}?display=browsefile&path={$file_path}&back_btn={$back_btn|base64_encode}');
                break;
            }
	    }
    });
});

function onAjaxUnload()
{
    $().w2destroy('toolbar');
}

</script>

        <div class="titlebar">{"File browser"|localize} - {$file_path}</div>
        
          <div id="toolbar" style="height: 30px"></div>

{if $action == "view"}
            <table class="blueLog">
                <tr><td><b>{"Name"|localize}: </b></td><td>{$file_path}</td></tr>
                <tr><td><b>{"Mime-type"|localize:files}: </b></td><td>{$mime} ({$type})</td></tr>
                <tr><td><b>{"Permissions"|localize:files}: </b></td><td>{$perms}</td></tr>
                <tr><td><b>{"Owner"|localize:files}: </b></td><td>uid={$owner}, gid={$group}</td></tr>
                <tr><td><b>{"Size"|localize:files}: </b></td><td>{$size} ({$size_bytes}b)</td></tr>
                <tr><td><b>{"Modification time"|localize:files}: </b></td><td>{$modification_time}</td></tr>
            </table>

          <div class="blueLog">
             {$contents}
          </div>
          
         </div>
        </article>
{else}
        <div class="blueLog">
             {"Error"|localize}: {"file not found"|localize:files}
        </div>
{/if}
