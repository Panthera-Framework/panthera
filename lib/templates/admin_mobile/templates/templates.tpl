    <nav class="tab-fixed">
      <ul class="tab-inner">
        <li><a href="#" onclick="navigateTo('?display=dash&cat=admin&menu=settings');" data-transition="push">{function="localize('Dash')"}</a></li>
        <li class="active"><a data-ignore="true">{function="localize('Templates management', 'templates')"}</a></li>
      </ul>
    </nav>

    <div class="content inset">
      <div class="slider tab-slider">
        <ul>
            <li id="templates" class="tab-item active">
                <ul class="list inset">
                   <li class="list-divider">{function="localize('Webroot templates', 'templates')"}</li>

                   <li class="list-item-single-line selectable">
                      <a href="#" onclick='webrootMerge();' id="merge_a">
                        {function="slocalize('Tap to update static files', 'templates')"}
                      </a>
                   </li>

                   <li class="list-divider">{function="ucfirst(localize('tools', 'templates'))"}</li>

                   <li class="list-item-single-line selectable">
                      <a href="#" onclick="templateTool('clear_cache');" id="cache_clear">
                        {function="localize('Clear templates cache', 'templates')"}
                      </a>
                   </li>

                   <li class="list-divider">{function="localize('Here are listed all templates, including its files', 'templates')"}</li>
                  {loop="$templates_list"}
                   <li class="list-item-single-line">
                      <div>
                        <span style="color: #bbb;">{$value.place} -></span> {$key}
                      </div>
                   </li>
                  {/loop}

                </ul>
            </li>
        </ul>
      </div>
    </div>

   <!-- JS code -->
    <script type="text/javascript">
   /**
      * Copying static files to application root (template->webrootMerge)
      *
      * @author Damian Kęska
      */

    function webrootMerge()
    {
        panthera.jsonPOST({ url: '?display=templates&cat=admin&action=webrootMerge', async: true, success: function (response) {
            if (response.status == "success") {
                $('#merge_a').slideToggle();
                $('#merge_a').slideToggle();
            }
           }
        });
    }

    /**
      * Execute a tool
      *
      * @param string toolName
      * @return void
      * @author Damian Kęska
      */

    function templateTool(toolName, value)
    {
        if (typeof value === "object")
            value = value.val();

        panthera.jsonPOST({ url: '?display=templates&cat=admin&action=exec&name='+toolName+'&value='+value, async: true, success: function (response) {
               if (response.status == "success") {
                    $('#cache_clear').slideToggle();
                    $('#cache_clear').slideToggle();
               }
           }
        });
    }
    </script>
   <!-- End of JS code -->