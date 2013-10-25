{include="buttons"}

<script type="text/javascript">
customNextBtn = false;
</script>

<div class="header">
        <h1>{function="localize('Job scheduler configuration', 'installer')"}</h1>
        <div style="margin-left: 5px;"><span>{function="localize('Crontab is a very powerful job scheduling module, used to execute huge operations such as massive mailing, template compiling. It\'s very important to configure it properly, because many built-in Panthera Framework features depends on this module', 'installer')"}.</span></div>
</div>

<div class="content" style="margin-left: 0px;">
    <p style="margin-top: 20px;"><b>{function="localize('Crontab syntax entries', 'installer')"}</b></p>
    
    <p><span class="description">{function="localize('Internal crontab module placed inside of Panthera Framework does not have possibility to work as a deamon, so it requires a real Unix-like crontab to work and to execute jobs in correct time', 'installer')"}.</span></p>
    
    <p style="margin-top: 5px; color: #C2C2C2;"><code>
    <i># {function="localize('open crontab joblist using text editor', 'installer')"}<br>
    crontab -e</i>
    </code></p>
    
    <p style="margin-top: 10px;"><span class="description">{function="localize('Please paste one of those commands to you\'r Unix crontab list', 'installer')"}</span></p>
    
    <p style="color: #abc; margin-top: 5px;"><code>
    <i>*/1 * * * * wget --spider -O /dev/null {$crontabUrl} > /dev/null 2> /dev/null</i>
    </code></p>
    
    <p style="color: #C2C2C2; margin-top: 5px;"><code>
    <i>*/1 * * * * curl {$crontabUrl} > /dev/null 2>/dev/null</i>
    </code></p>
    
    <p style="margin-top: 20px;"><b>{function="localize('Secret key', 'installer')"}</b></p>
    
    <p><span class="description">{function="localize('To protect access to crontab module there is a requirement to generate and provide a secret key, it can be called a crontab password. So, your key is', 'installer')"}:</span></p>
    
    <p style="color: #C2C2C2; margin-top: 5px;"><code>
    <i>{$crontabKey}</i><span style="float: right;"><a href="#" onclick="navigateTo('?action=save')">{function="localize('Generate new', 'installer')"}</a></span>
    </code></p>
    
    <p style="margin-top: 20px;"><b>{function="localize('Commands to manually invoke a new thread of crontab from shell', 'installer')"}</b></p>
    
    <p><span class="description">{function="localize('You can use those shell commands to runtime check all cronjobs and execute planned for this moment', 'installer')"}</span></p>
    
    <p style="color: #C2C2C2; margin-top: 5px;"><code>
    <i># {function="localize('using', 'installer')"} wget<br>
    wget --spider -O /dev/null {$crontabUrl} > /dev/null 2> /dev/null<br></i>
    </code></p>
    
    <p style="color: #C2C2C2; margin-top: 5px; margin-bottom: 35px;"><code>
    <i># {function="localize('using', 'installer')"} curl<br>
    curl {$crontabUrl} > /dev/null 2>/dev/null<br></i>
    </code></p>
</div>