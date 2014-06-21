{$site_header}
{$uiTitlebar['title']=localize('Integration with external development tools', 'debug')}
{include="ui.titlebar"}

<div id="topContent">
    <div class="searchBarButtonArea">
    	<div class="searchBarButtonAreaLeft">
        	<input type="button" value="{function="localize('Back')"}" onclick="navigateTo('?display=debug&cat=admin')">
    	</div>
    </div>
</div>

<div class="popupOverlay">
    <div class="instructionBox">
    	<!-- PHP WEB SERVER -->
    
    	<h2>{function="localize('Using a PHP built-in web server for development', 'debug')"}</h2>
    	
    	<div class="instructionBoxDescription">
    		{function="localize('To use built-in PHP web server with Panthera based application you have to navigate to your app root directory and then run a web server pointing to route.php', 'debug')"}
	    	<code><pre>cd {$_SERVER.DOCUMENT_ROOT}
php -S {$_SERVER.SERVER_NAME}:8080 route.php</pre>
	    	</code>
	    	
	    	<p>
	    		{function="slocalize('Now navigate to http://%s:8080 in your web browser and enjoy using pure PHP web server without configuring Apache or any other production servers', 'debug', $_SERVER.SERVER_NAME)"}
	    	</p>
    	</div>
    	
    	<div style="margin-top: 50px;"></div>
    	
    	
    	
    	<!-- PHPSH -->
    	<h2>{function="localize('PHP interactive shell - phpsh', 'debug')"}</h2>
    	
    	<div class="instructionBoxDescription">
    		{function="localize('phpsh is an interactive shell for php that features readline history, tab completion, quick access to documentation. Usage:', 'debug')"}
	    	<code><pre>cd {$_SERVER.DOCUMENT_ROOT}
phpsh _phpsh.php</pre>
	    	</code>
    	</div>
    	
    	<div style="margin-top: 50px;"></div>
    </div>
</div>