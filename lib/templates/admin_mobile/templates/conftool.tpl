    <nav class="tab-fixed">
      <ul class="tab-inner">
        <li><a href="#" onclick="navigateTo('?display=dash&cat=admin&menu=settings');" data-transition="push">{function="localize('Dash')"}</a></li>
        <li class="active"><a data-ignore="true">{function="localize('Configuration editor', 'conftool')"}</a></li>
      </ul>
    </nav>

    <div class="content inset">
      <div class="slider tab-slider">
        <ul>
            <li id="conftool" class="tab-item active">
                <ul class="list inset">
                   <li class="list-divider">{function="localize('Key')"} / {function="localize('Value')"}</li>

                  {loop="$a"}
                   <li class="list-item-two-lines">
                      <div>
                        <h3>
                            <button class="btn-small" id="button_{$key}" onclick="saveVariable('{$key}');" style="float: right; display: none; padding: 9px; width: 120px;">{function="localize('Save')"}</button>
                            <input type="text" placeholder="{function="localize('Value')"}" value='{$value[1]}' id="value_{$key}" class="input-text" onfocus="$('#button_{$key}').slideToggle();" style="padding: 0px; max-width: calc(100% - 120px);">
                        </h3>
                        <p style="margin-top: 7px;">
                            ({$value[0]})&nbsp;<b>{if="isset($value[2])"}{$value[2]}{else}{$key}{/if}</b>
                        </p>
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
      * Save variable to database
      *
      * @author Mateusz Warzyński
      */

    function saveVariable(id)
    {
        value = jQuery('#value_'+id).val();

        panthera.jsonPOST({ url: '?display=conftool&cat=admin&action=change', data: 'id='+id+'&value='+value, success: function (response) {
                if (response.status == "success")
                {
                   $('#button_'+id).attr("disabled", "disabled");
                   $('#button_'+id).animate({ height:'toggle'});
                }
            }
        });

        return false;

    }
    </script>
   <!-- End of JS code -->