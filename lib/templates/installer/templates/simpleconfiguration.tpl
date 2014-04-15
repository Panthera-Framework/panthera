{include="buttons"}
<script type="text/javascript">
customNextBtn = true;

$(document).ready (function () {
    $('#installer-controll-nextBtn').attr('disabled', false);
    $('#installer-controll-checkBtn').hide();
    
    $(document).bind('onNextBtn', function () { 
        panthera.jsonPOST( { data: '#simpleConfigurationSubmit', success: function (response) {
                if (response.status == 'success')
                {
                    navigateTo('?_nextstep=True');
                }        
        
            } 
        });
    });
});
</script>

<div class="header">
    <h1>{function="localize('Basic configuration', 'installer')"}.</h1>
    <div style="margin-left: 5px;"><span>{function="localize('Set default install options', 'installer')"}.</span></div>
</div>


<div class="content" style="margin-left: 0px;">
    <form action="?save=True" method="POST" id="simpleConfigurationSubmit">
        <table class="table" style="width: 80%; margin: 0 auto; margin-top: 50px; margin-bottom: 50px; border: none; border-spacing: 0px;">
            <tbody>
                <!-- system settings -->
                <tr><td colspan="2"><h3>{function="localize('System settings', 'installer')"}</h3></td></tr>
                <tr><td style="width: 70%;">{function="localize('Enable lightweight debugger', 'installer')"}:<br><small><i>{function="localize('Allows you to see what happens inside of system, debug and benchmark your application', 'installer')"}</i></small></td><td><input type="checkbox" name="debug" value="1" checked></td></tr>
                <tr>
                    <td style="width: 70%;">{function="localize('Password hashing method', 'installer')"}:<br><small><i>{function="localize('Strong hashing algorithms are great in cases when site\'s database leaks in to the web, the hakers would have a problem with reading a strongly hashed and salted password', 'installer')"}</i></small></td>
                    <td><select name="hashing_algorithm">
                            <option value="md5">md5 - {function="localize('Faster, but very weak', 'installer')"}</option>
                            <option value="sha512">sha512 - {function="localize('Fast, and provides medium security level', 'installer')"}</option>
                            <option value="blowfish" selected>blowfish - {function="localize('Slower, but provides maximum security', 'installer')"}</option>
                    </td>
                </tr>
                
                <tr>
                    <td style="width: 70%;">{function="localize('Mask PHP version', 'installer')"}:<br><small><i>{function="localize('Force HTTP server to show false informations about PHP version', 'installer')"}</i></small></td>
                    <td><select name="header_maskphp">
                            <option value="1" selected>{function="localize('Yes', 'installer')"}</option>
                            <option value="">{function="localize('No', 'installer')"}</option>
                    </td>
                </tr>
                
                <tr>
                    <td style="width: 70%;">{function="localize('X-Frame', 'installer')"}:<br><small><i>{function="localize('Allow your website to be framed using iframe tag', 'installer')"}</i></small></td>
                    <td>
                        <select name="header_framing">
                            <option value="allowall">{function="localize('Yes', 'installer')"}</option>
                            <option value="sameorigin" selected>{function="localize('Only on same domain', 'installer')"}</option>
                            <option value="deny">{function="localize('No', 'installer')"}</option>
                    </td>
                </tr>
                
                <tr>
                    <td style="width: 70%;">{function="localize('IE XSS-Protection', 'installer')"}:<br><small><i>{function="localize('Tell\'s Internet Explorer to turn on XSS-Protection mechanism', 'installer')"}</i></small></td>
                    <td>
                        <select name="header_xssprot">
                            <option value="1">{function="localize('Yes', 'installer')"}</option>
                            <option value="" selected>{function="localize('No', 'installer')"}</option>
                    </td>
                </tr>
                
                <tr>
                    <td style="width: 70%;">{function="localize('No-sniff header', 'installer')"}:<br><small><i>{function="localize('This can reduce some drive-by-download attacks', 'installer')"}</i></small></td>
                    <td>
                        <select name="header_nosniff">
                            <option value="1">{function="localize('Yes', 'installer')"}</option>
                            <option value="" selected>{function="localize('No', 'installer')"}</option>
                    </td>
                </tr>
                
                <!-- mailing -->
                <tr><td colspan="2"><h3>{function="localize('Mailing', 'installer')"}</h3></td></tr>
                <tr>
                    <td>{function="localize('Method', 'installer')"}:
                        <br><small><i>{function="localize('If your PHP is configured you can use default mail() function, or you can set external SMTP server', 'installer')"}</i></small>
                    </td>
                    <td>
                        <select name="mailing_use_php"><option value="1" selected>Use PHP's mail()</option><option value="">External SMTP server</option></select>
                    </td>
                </tr>
                <tr><td>{function="localize('Server address', 'installer')"}:<br><small><i>{function="localize('Address of a SMTP server', 'installer')"}</i></small></td><td><input type="text" name="mailing_server" placeholder="localhost"></td></tr>
                <tr><td>{function="localize('Server port', 'installer')"}:<br><small><i>{function="localize('Port, eg. 465, 587, 25', 'installer')"}</i></small></td><td><input type="text" name="mailing_server_port" id="mailing_server_port" placeholder="465"></td></tr>
                
                <tr><td>{function="localize('Sender e-mail address', 'installer')"}:<br><small><i>{function="localize('You\'r e-mail address, or eg. noreply@example.com', 'installer')"}</i></small></td><td><input type="text" name="mailing_from" id="mailing_from" placeholder="example@example.com"></td></tr>
                
                <tr><td>{function="localize('Username', 'installer')"}:<br><small><i>{function="localize('Login to SMTP server', 'installer')"}</i></small></td><td><input type="text" name="mailing_user" placeholder="example"></td></tr>
                
                <tr><td>{function="localize('Password', 'installer')"}:<br><small><i>{function="localize('Phrase required to authenticate with server', 'installer')"}</i></small></td><td><input type="text" name="mailing_password" id="mailing_password"></td></tr>
                
                <tr><td>{function="localize('Use SSL', 'installer')"}:<br><small><i>{function="localize('SSL/TLS connection encryption', 'installer')"}</i></small></td>
                    <td><select name="mailing_smtp_ssl"><option value="1" selected>{function="localize('Yes', 'installer')"}</option><option value="">{function="localize('No', 'installer')"}</option></td>
                </tr>
                
                <!-- session and cookies -->
                <tr><td colspan="2"><h3>{function="localize('Session and cookies', 'installer')"}</h3></td></tr>
                <tr><td>{function="localize('Useragent strict check', 'installer')"}:<br><small><i>{function="localize('Don\'t allow copying cookies to another browser/computer (it can be spoofed, but always increasing security a little bit)', 'installer')"}</i></small></td>
                    <td><select name="session_useragent"><option value="1" selected>Yes</option><option value="">No</option></td>
                </tr>
                
                <tr><td>{function="localize('Session life time', 'installer')"}:<br><small><i>{function="localize('Maximum time user can be idle (in seconds)', 'installer')"}</i></small></td>
                    <td><input type="text" name="session_lifetime" id="session_lifetime" placeholder="3600" value="3600"></td>
                </tr>
                
                <tr><td>{function="localize('Encrypt cookies', 'installer')"}:<br><small><i>{function="localize('Cookies can be encrypted with strong algorithm, so the user wont be able to read contents', 'installer')"}</i></small></td>
                    <td><select name="cookie_encrypt"><option value="1" selected>{function="localize('Yes', 'installer')"}</option><option value="">{function="localize('No', 'installer')"}</option></td>
                </tr>
            </tbody>
        
        </table>
    </form>
</div>
