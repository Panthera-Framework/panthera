    {include="header.tpl"}
    
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
        <li><a href="?display=dash">{function="localize('Dash')"}</a></li>
        <li class="active"><a data-ignore="true">{function="localize('Debugging center')"}</a></li>
      </ul>
    </nav>

   <div class="content">

     <!-- Content -->
     <div class="slider tab-slider">
        <ul>
            <ul class="list inset">
             
             <li class="list-divider">{function="localize('Tools', 'debug')"}</li>
             
             {loop="$tools"}
              <li class="list-item-single-line selectable">
                <a href="{$value.link}" data-ignore="true" data-transition="push">
                    <p style="vertical-align: middle;">{function="localize($value.name, 'debug')"}</p>
                </a>
              </li>
             {/loop}
             
             <li class="list-divider">{function="localize('Debugger state', 'debug')"}</li>
              <li class="list-item-single-line selectable">
                <a href="" onclick="toggleDebugValue();" data-ignore="true">
                    <p style="vertical-align: middle;" id="debugger_state">{if="$debug == true"} {function="localize('True')"} {else} {function="localize('False')"} {/if}</p>
                </a>
              </li>
             
            </ul>
        </ul>
     </div>
        
   </div>
   <!-- End of content -->
     
    {include="footer.tpl"}
