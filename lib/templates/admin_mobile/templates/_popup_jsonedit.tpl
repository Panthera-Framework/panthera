   <nav class="tab-fixed">
      <ul class="tab-inner">
        <li><a href="#" onclick="navigateTo('?display=debug&cat=admin');">{function="localize('Debugging center')"}</a></li>
        <li class="active"><a data-ignore="true">{function="localize('Array editor', 'debug')"}</a></li>
      </ul>
    </nav>

    <div class="content inset">
      <div class="slider tab-slider">

        <ul>
            <li id="json_edit" class="tab-item active">
             <ul class="list inset">
              <form action="{$AJAX_URL}?display=_popup_jsonedit&cat=admin" method="POST" id="jsonEditForm">
               <label>{function="localize('Enter JSON code or serialized array to convert to PHP array', 'debug')"}</label>
               <textarea name="jsonedit_content" style="width: 98%; height: 200px;">{$code}</textarea>

               <br>
               <label>{function="localize('Result', 'debug')"}</label>
               <textarea id="jsonedit_result" readonly style="width: 98%; height: 180px;"></textarea>

                <input type="button" class="block-btn" value=" {function="localize('Serialize')"} " onclick="jsonEditSave('');" style="float: right; margin-right: 15px;">
               {if="!$popup"}
                <input type="button" class="block-btn" value=" {function="localize('print_r')"} " onclick="jsonEditSave('print_r');" style="float: right; margin-right: 15px; width: 156px;">
                <input type="button" class="block-btn" value=" {function="localize('var_dump')"} " onclick="jsonEditSave('var_dump');" style="float: right; margin-right: 15px; width: 156px;">
                <input type="button" class="block-btn" value=" {function="localize('var_export')"} " onclick="jsonEditSave('var_export');" style="float: right; margin-right: 15px; width: 160px;">
                <input type="button" class="block-btn" value=" {function="localize('json')"} " onclick="jsonEditSave('json');" style="float: right; margin-right: 15px; width: 156px;">
               {/if}
               <input type="hidden" name="responseType" id="responseType" value="">

              </form>
             </ul>
            </li>
        </ul>
     </div>
    </div>

   <!-- JS code -->
    <script type="text/javascript">
    jsonEditSpinner = new panthera.ajaxLoader($('#spinnerOverlay'));

    function jsonEditSave(responseType)
    {
        $('#responseType').val(responseType);

        panthera.jsonPOST({ data: '#jsonEditForm', messageBox: 'jsonEditBox', success: function (response) {
                if (response.result == "N;")
                {
                    $('#jsonEditBox_failed').html('{function="localize('Invalid JSON syntax', 'debug')"}');
                    $('#jsonEditBox_failed').slideDown();
                    return false;
                }

                $('#jsonEditBox_failed').slideUp();

                {if="$callback"}
                    {$callback}('{$callback_arg}', response.result);
                {/if}

                {if="!$popup"}
                    $('#jsonedit_result').val(response.result);
                {/if}

                closePopup();
            }
        });
    }

    </script>
   <!-- End of JS code -->