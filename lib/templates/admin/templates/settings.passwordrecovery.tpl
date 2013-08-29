{$site_header}

    <script type="text/javascript">
    /**
      * Init MCE editor
      *
      * @author Damian Kęska
      */

    function initEditor()
    {
        mceSetContent('recovery_mail_content', htmlspecialchars_decode("{$variables['recovery_mail_content']}"));
    }
    
    {$mce_init = "init_instance_callback: initEditor,"}
    {include="mce"}

    mceInit('recovery_mail_content');
    
    $(document).ready(function () {
        $('#recovery_passwd_length_range').change(function () {
            $('#recovery_passwd_length').html($('#recovery_passwd_length_range').val());
        });
        
        $('#recovery_key_length_range').change(function () {
            $('#recovery_key_length').html($('#recovery_key_length_range').val());
        });
        
        $('#settingsFormSave').submit(function () {
            panthera.jsonPOST( { data: '#settingsFormSave', spinner: new panthera.ajaxLoader($('#settingsFormSaveDiv')), messageBox: 'w2ui' });
            return false; 
        });
    });
    
    </script>

        <div class="titlebar">{function="localize('Password recovery settings', 'passwordrecovery')"}{include="_navigation_panel"}</div><br>
        
        <form action="?{function="getQueryString('GET', '', '_')"}" method="POST" id="settingsFormSave">
        <div class="grid-1" style="position: relative;" id="settingsFormSaveDiv">
         <table class="gridTable">
            <thead>
                <tr><th colspan="2">{function="localize('Settings', 'password recovery')"}</th></tr>
            </thead>
         

            <tbody>
                <tr>
                    <td>Tytuł wiadomości e-mail: </td><td><input type="text" name="recovery.mail.title" value="{$variables['recovery_mail_title']}" style="width: 95%;"></td>
                </tr>
                
                <tr>
                    <td>Długość nowo wygenerowanego hasła:</td>
                    <td><input type="range" min="6" max="32" name="recovery.passwd.length" id="recovery_passwd_length_range" value="{$variables['recovery_passwd_length']}"> <span id="recovery_passwd_length" style="font-size: 14px; margin-left: 10px;">{$variables['recovery_passwd_length']}</span></td>
                </tr>
                
                <tr>
                    <td>Długość identyfikatora przywracania hasła:</td>
                    <td><input type="range" min="6" max="32" name="recovery.key.length" id="recovery_key_length_range" value="{$variables['recovery_key_length']}"> <span id="recovery_key_length" style="font-size: 14px; margin-left: 10px;">{$variables['recovery_key_length']}</span></td>
                </tr>
                
                <tr>
                    <td valign="top">Treść wiadomości:</td><td><textarea name="recovery.mail.content" id="recovery_mail_content" style="width: 95%; height: 250px;">{$variables['recovery_mail_content']}</textarea></td>
                </tr>
            </tbody>
            
            <tfoot>
                <tr>
                    <td colspan="2">
                        <span style="float: right;">
                            <input type="submit" value="{function="localize('Save', 'messages')"}">
                        </span>
                    </td>
                </tr>
            </tfoot>
           </table>
           </form>
      </div>
</article>

