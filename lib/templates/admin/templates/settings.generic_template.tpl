{$site_header}

    <script type="text/javascript">
    $(document).ready(function () {
        $('#settingsFormSave').submit(function () {
            panthera.jsonPOST( { data: '#settingsFormSave', spinner: new panthera.ajaxLoader($('#settingsFormSaveDiv')), messageBox: 'w2ui', mce: 'tinymce_all' });
            return false; 
        });
        
        $('#languageSelection').change(function () {
            navigateTo('?{function="getQueryString('GET', '', array('_', 'language'))"}&language='+$('#languageSelection').val());
        });
    });
    
    </script>

        {include="ui.titlebar"}<br>
        
        <form action="?{function="getQueryString('GET', '', '_')"}" method="POST" id="settingsFormSave">
        <div class="grid-1" style="position: relative;" id="settingsFormSaveDiv">
         <table class="gridTable">
            <tbody>
                {if="$uiSettings.languageSelector"}
                <tr>
                    <td><b>{function="localize('Language')"}:</b></td>
                    <td>
                        <select name="language" id="languageSelection">
                            {loop="$uiSettings.languages"}
                            <option value="{$key}"{if="$key == $uiSettings.defaultLanguage"} selected{/if}>{$key}</option>
                            {/loop}
                        </select>
                    </td>
                </tr>
                
                {/if}
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
                            
                                {if="$value.type == 'multipleboolselect'"}
                                    <select name="{$key}[]" id="{$key}" multiple style="width: 95%;">
                                        {loop="$value.value"}
                                            <option value="{$key}"{if="$value"} selected{/if}>{$key}</option>
                                        {/loop}
                                    </select>
                                {else}
                            
                                    <input type="text" name="{$key}" value="{$value.value}" style="width: 95%;">
                                {/if}
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

