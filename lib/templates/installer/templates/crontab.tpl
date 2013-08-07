{include="buttons"}

<h1>{function="localize('Job scheduler configuration', 'installer')"}</h1>

<span class="description">{function="localize('Crontab is a very powerful job scheduling module, used to execute huge operations such as massive mailing, template compiling. It\'s very important to configure it properly, because many built-in Panthera Framework features depends on this module', 'installer')"}.</span>

<h2 style="margin-top: 20px;">{function="localize('Crontab syntax entries', 'installer')"}</h2>

<span class="description">{function="localize('Internal crontab module placed inside of Panthera Framework does not have possibility to work as a deamon, so it requires a real Unix-like crontab to work and to execute jobs in correct time', 'installer')"}.</span>

<code>
crontab -e # open crontab joblist using text editor
</code>

<span class="description">{function="localize('Please paste one of those commands to you\'r Unix crontab list', 'installer')"}</span>

<code>
*/1 * * * * wget --spider -O /dev/null {$crontabUrl} > /dev/null 2> /dev/null
</code>

<code>
*/1 * * * * curl {$crontabUrl} > /dev/null 2>/dev/null
</code>

<h2 style="margin-top: 20px;">{function="localize('Secret key', 'installer')"}</h2>

<span class="description">{function="localize('To protect access to crontab module there is a requirement to generate and provide a secret key, it can be called a crontab password. So, your key is', 'installer')"}:</span>

<code>
{$crontabKey}<span style="float: right;"><a href="#" onclick="navigateTo('?action=save')">{function="localize('Generate new', 'installer')"}</a></span>
</code>

<h2 style="margin-top: 20px;">{function="localize('Commands to manually invoke a new thread of crontab from shell', 'installer')"}</h2>

<span class="description">{function="localize('You can use those shell commands to runtime check all cronjobs and execute planned for this moment', 'installer')"}</span>

<code>
# {function="localize('using', 'installer')"} wget<br>
wget --spider -O /dev/null {$crontabUrl} > /dev/null 2> /dev/null<br>
</code>

<code>
# {function="localize('using', 'installer')"} curl<br>
curl {$crontabUrl} > /dev/null 2>/dev/null<br>
</code>
