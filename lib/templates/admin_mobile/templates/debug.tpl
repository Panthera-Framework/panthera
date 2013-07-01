    {include 'header.tpl'}
    
    <script type="text/javascript">
    function toggleDebugValue()
    {
        panthera.jsonGET({ data: '', url: '?display=debug&action=toggle_debug_value', success: function (response) {
                if (response.status == "success")
                    navigateTo('?display=debug');
            }
        });
    }
    </script>
    
    <nav class="tab-fixed">
      <ul class="tab-inner">
        <li><a href="?display=dash">{"Dash"|localize}</a></li>
        <li class="active"><a data-ignore="true">{"Debugging center"|localize}</a></li>
      </ul>
    </nav>

   <div class="content">

     <!-- Content -->
     <div class="slider tab-slider">
        <ul>
            <ul class="list inset">
             
             <li class="list-divider">{"Tools"|localize:debug}</li>
             
             {foreach from=$tools item=i key=k}
              <li class="list-item-single-line selectable">
                <a href="{$i.link}" data-ignore="true" data-transition="push">
                    <p style="vertical-align: middle;">{"$i.name"|localize:debug}</p>
                </a>
              </li>
             {/foreach}
             
             <li class="list-divider">{"Debugger state"|localize:debug}</li>
              <li class="list-item-single-line selectable">
                <a href="" onclick="toggleDebugValue();" data-ignore="true">
                    <p style="vertical-align: middle;" id="debugger_state">{if $debug eq true} {"True"|localize} {else} {"False"|localize} {/if}</p>
                </a>
              </li>
             
            </ul>
        </ul>
     </div>
        
   </div>
   <!-- End of content -->
     
    {include 'footer.tpl'}
