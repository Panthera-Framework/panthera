{$site_header}

    <script type="text/javascript">
    $(document).ready(function () {
        $('#settingsFormSave').submit(function () {
            panthera.jsonPOST( { data: '#settingsFormSave', spinner: new panthera.ajaxLoader($('#settingsFormSaveDiv')), messageBox: 'w2ui', mce: 'tinymce_all' });
            return false; 
        });
    });
    
    </script>

        {include="ui.titlebar"}<br>
        
        <form action="?{function="getQueryString('GET', '', '_')"}" method="POST" id="settingsFormSave">
        <div class="grid-1" style="position: relative;" id="settingsFormSaveDiv">
         <table class="gridTable">
            <tbody>
                {loop="$variables"}
                <tr>
                    <td valign="top"><b>{$value.label}:</b> {if="$value.description"}<br><small>{$value.description}</small>{/if}</td>
                    <td>
                        {if="is_array($value.validator)"}
                        <select name="{$key}">
                            {$v=$value.value}
                            {loop="$value.validator"}
                            <option value="{$key}"{if="$v == $key"} selected{/if}>{$value}</option>
                            {/loop}
                        </select>
                        {else}
                            {if="is_bool($value.value)"}
                            <input type="radio" name="{$key}" value="1"{if="$value.value"} checked{/if}> {function="localize('True')"} &nbsp;<input type="radio" name="{$key}" value="0"{if="!$value.value"} checked{/if}> {function="localize('False')"}
                            {else}
                            <input type="text" value="{$value.value}">
                            {/if}
                        {/if}
                    
                </tr>
                {/loop}
            </tbody>
            
            <tfoot>
                <tr>
                    <td colspan="2">
                        <span style="float: right;">
                            <input type="submit" value="{function="localize('Save')"}">
                        </span>
                    </td>
                </tr>
            </tfoot>
           </table>
           </form>
      </div>
</article>

