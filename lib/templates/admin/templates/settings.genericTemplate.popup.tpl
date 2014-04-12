<script type="text/javascript">
    function initEditor()
    {
        {loop="$variables"}
            {if="$value.type == 'wysiwyg'"}
                mceSetContent('{$key}', htmlspecialchars_decode("{$value.value}"));
            {/if}
        {/loop}
    }
</script>

{function="uiMce::display()"}
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
    
    {loop="$variables"}
    {if="$value.type == 'wysiwyg'"}mceInit('{$key}');{/if}
    {/loop}
    
</script>

<form action="?{function="getQueryString('GET', '', '_')"}" method="POST" id="settingsFormSave">

<table style="margin: 0 auto; margin-top: 50px; margin-bottom: 50px;" class="formTable">
    <thead>
        <tr>
            <th colspan="2" class="formTableHeader" style="padding-top: 0px; padding-bottom: 30px;">
                <p style="color: #e5ebef; padding: 0px; margin: 0px; margin-left: 30px;">{$uiTitlebar.title}</p>
            </th>
        </tr>
    </thead>

    <tbody>
        {if="$uiSettings.languageSelector"}
        <tr>
            <td>{function="localize('Language')"}</td>
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
        {if="!$value.hide"}
        <tr>
            <th valign="top"{if="$value.separator"} colspan="2"{/if} style="{if="!$value.separator"}padding-left: 25px; height: 100%; max-width: 500px;{/if}">
                <p>{$value.label}:
              {if="$value.description"}<br>
                <small><span style="color: grey;">{$value.description}</span></small>
              {/if}
                </p>
            </th>
            
            {if="!$value.separator"}
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
                {elseif="$value.type == 'wysiwyg'"}
                <textarea id="{$key}" name="{$key}" style="width: 95%; min-height: 200px;">{$value.value}</textarea>
                {elseif="is_int($value.value)"}
                <input type="number" name="{$key}" value="{$value.value}" style="width: 95%;">
                {else}
                <input type="text" name="{$key}" value="{$value.value}" style="width: 95%;">
                {/if}
                {/if}
                {/if}
            </td>
            {/if}
        </tr>
        {/if}
      {/loop}
    </tbody>
</table>
</form>
