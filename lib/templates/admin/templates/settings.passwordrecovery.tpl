{$site_header}

<script type="text/javascript">
/**
  * Init MCE editor
  *
  * @author Damian Kęska
  */

function initEditor()
{
    mceSetContent('recovery_mail_content', htmlspecialchars_decode("{function="stripNewLines(htmlspecialchars($variables['recovery_-_mail_-_content']['value'], ENT_QUOTES))"}"));
}
</script>

{function="uiMce::display()"}
    <script type="text/javascript">
    mceInit('recovery_mail_content');
    
    $(document).ready(function () {
        $('#recovery_passwd_length_range').change(function () {
            $('#recovery_passwd_length').html($('#recovery_passwd_length_range').val());
        });
        
        $('#recovery_key_length_range').change(function () {
            $('#recovery_key_length').html($('#recovery_key_length_range').val());
        });
        
        $('#settingsFormSave').submit(function () {
            panthera.jsonPOST( { data: '#settingsFormSave', spinner: new panthera.ajaxLoader($('#settingsFormSaveDiv')), messageBox: 'w2ui', mce: 'tinymce_all' });
            return false; 
        });

        $('#languageSelection').change(function () {
            navigateTo('?{function="getQueryString('GET', '', array('_', 'language'))"}&language='+$('#languageSelection').val());
        });
    });
    
    </script>

{include="ui.titlebar"}

<form action="?{function="getQueryString('GET', '', '_')"}" method="POST" id="settingsFormSave">

<div id="topContent">
    <div class="searchBarButtonArea">
        <input type="submit" value="{function="localize('Save')"}">
    </div>
</div>

<table style="margin: 0 auto; margin-top: 50px; margin-bottom: 50px;">
    <thead>
        <tr>
            <th colspan="2">{$uiTitlebar.title}</th>
        </tr>
    </thead>

    <tbody>
                <tr>
                    <td>{function="localize('Language')"}: </td>
                    <td>
                        <select name="language" id="languageSelection">
                            {loop="$languages"}
                            <option value="{$key}"{if="$key == $activeLanguage"} selected{/if}>{$key}</option>
                            {/loop}
                        </select>
                    </td>
                </tr>
            
                <tr>
                    <td>{$variables['recovery_-_mail_-_title']['label']}: </td>
                    <td>
                        <input type="text" name="recovery_-_mail_-_title" value="{$variables['recovery_-_mail_-_title']['value']}" style="width: 95%;">
                    </td>
                </tr>
                
                <tr>
                    <td>{$variables['recovery_-_passwd_-_length']['label']}:</td>
                    <td><input type="range" min="6" max="32" name="recovery_-_passwd_-_length" id="recovery_passwd_length_range" value="{$variables['recovery_-_passwd_-_length']['value']}"> 
                        <span id="recovery_passwd_length" style="font-size: 14px; margin-left: 10px;">{$variables['recovery_-_passwd_-_length']['value']}</span>
                    </td>
                </tr>
                
                <tr>
                    <td>{$variables['recovery_-_key_-_length']['label']}:</td>
                    <td>
                        <input type="range" min="6" max="32" name="recovery_-_key_-_length" id="recovery_key_length_range" value="{$variables['recovery_-_key_-_length']['value']}"> 
                        <span id="recovery_key_length" style="font-size: 14px; margin-left: 10px;">{$variables['recovery_-_key_-_length']['value']}</span>
                    </td>
                </tr>
                
                <tr>
                    <td valign="top">{$variables['recovery_-_mail_-_content']['label']}:</td>
                    <td>
                        <b>{function="localize('Avaliable tags', 'passwordrecovery')"}:</b> {&#36;recovery_key}, {&#36;recovery_passwd}, {&#36;PANTHERA_URL}, {&#36;userName}, {&#36;userID}<br><br>
                        <textarea name="recovery_-_mail_-_content" id="recovery_mail_content" style="width: 95%; height: 250px;">{$variables['recovery_-_mail_-_content']['value']|stripNewLines|htmlspecialchars}</textarea>
                    </td>
                </tr>
            </tbody>
</table>
</form>
