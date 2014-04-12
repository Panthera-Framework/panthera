<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        {$site_header}
        <link rel="icon" type="image/png" href="{$PANTHERA_URL}/images/admin/pantheraUI/favicon.ico" />
        <link rel="stylesheet" type="text/css" href="{$PANTHERA_URL}/css/admin/jquery.dropdown.css">
        <link rel="stylesheet" type="text/css" href="{$PANTHERA_URL}/css/admin/jquery-ui.css">
        <link rel="stylesheet" type="text/css" href="{$PANTHERA_URL}/css/admin/pantheraUI.css" media="screen">
        <script type="text/javascript" src="{$PANTHERA_URL}/js/admin/jquery-1.10.0.min.js"></script>
        <script type="text/javascript" src="{$PANTHERA_URL}/js/admin/jquery-migrate-1.2.1.min.js"></script>
        <script type="text/javascript" src="{$PANTHERA_URL}/js/admin/jquery-ui.min.js"></script>
        <script type="text/javascript" src="{$PANTHERA_URL}/js/admin/panthera.js"></script>
        <script type="text/javascript" src="{$PANTHERA_URL}/js/admin/pantheraUI.js"></script>
        <script type="text/javascript" src="{$PANTHERA_URL}/js/admin/jquery.dropdown.js"></script>
        
        <script type="text/javascript">
        $(document).ready(function() {
            {if="isset($navigateTo)"}
            navigateTo('{$AJAX_URL}?{$navigateTo}');
            {else}
            navigateTo('{$AJAX_URL}?display=dash&cat=admin');
            {/if}
            $('.ajaxLinkMain').click(function (event) { event.preventDefault(); navigateTo(jQuery(this).attr('href')); return false;});
        });
        
        panthera.locale.add({'Yes': '{function="localize('Yes')"}', 'No': '{function="localize('No')"}', 'Close': '{function="localize('Close')"}'});
        </script>
        
        {if="$_tpl_settings['compact']"}
        <style>
            .content {
                width: 100%;
            }
            
            .textTitleBar {
                margin-left: 5px;
            }
        </style>
        {/if}
    </head>
    
    <body>
        <div id="logoBar">
            <div class="centerWithContent pantheraLogo">
                <span><a href="{$PANTHERA_URL}/pa-admin.php">{$siteTitle}</a></span>
                
                <!-- Userbar: menu -->
                
                <div class="userBar">
                    <div class="userMenu">
                        <ul class="dropdown dropdown-horizontal">
		                    <li>
		                        <a href="#" class="dir">{$user->login}
		                            <div class="menuArrowBackground"></div>
		                            <div class="menuArrow"><img src="images/admin/pantheraUI/transparent.png" class="pantheraIcon icon-Arrow menuIcon"></div>
		                        </a>
		                        
			                    <ul style="border: solid 1px #56687b;">
				                    <li><a href="#" onclick="navigateTo('?display=users&cat=admin&action=account');"><i class="icon-user"></i>{function="localize('My profile')"}</a></li>
							        <li><a href="{$PANTHERA_URL}/pa-login.php?logout=True"><i class="icon-remove"></i>{function="localize('Logout')"}</a></li>
			                    </ul>
		                    </li>
	                    </ul>
	                </div>
                </div>
                
                <!-- Userbar: avatar -->
                
                <div class="userAvatar"><img src="{$user->getAvatar()}" style="max-width: 28px; max-height: 28px; background: #56687b; position: absolute; top: 0; bottom: 0; margin: auto;"></div>
                
                <div style="display: inline-block; float: right; font-size: 11px; padding-top: 10px; padding-right: 5px;">
                    <span data-searchbardropdown="#languageDropdown" id="languageDropdownSpan" style="font-size: 11px; cursor: pointer;">
                        <img src="{$PANTHERA_URL}/images/admin/flags/{$language}.png" style="height: 10px; margin: 1px;">
                    </span>
                    
                    <div id="languageDropdown" class="searchBarDropdown searchBarDropdown-tip searchBarDropdown-relative">
                        <ul class="searchBarDropdown-menu">
                            {loop="$flags"}
                                <li>
                                    <a href="?{function="getQueryString('GET', '', array('_', '_locale'))"}&_locale={$value}&cat=admin">
                                        <img src="{$PANTHERA_URL}/images/admin/flags/{$value}.png" style="height: 12px; margin: 1px; vertical-align: middle;"> {$value}
                                    </a>
                                </li>
                            {/loop}
                        </ul>
                    </div>
                </div>
            </div>
            
            <!-- Menubar -->
            
            <div id="menuBarVisibleLayer">
                <div class="centerWithContent" id="menuBar">
                    {loop="$admin_menu"}
                    <span class="menuItem">
                        {if="isset($value.icon)"}
                        <a href="{$value.link}" class="ajaxLinkMain"><img src="images/admin/pantheraUI/transparent.png" class="pantheraIcon icon-{$value.icon} menuIcon" alt="Dash"> 
                        {/if}
                        <span class="menuText">{$value.title}</span>
                        </a>
                    </span>
                    {/loop}
                </div>
            </div>
        </div>
        
        <!-- Content -->
        
        <div id="ajax_content" class="content centerWithContent">
            <div class="settingsBackground" style="color: white; background-color: #56687b; height: 400px;">
                <img src="{$PANTHERA_URL}/images/admin/pantheraUI/logo-big.png" style="margin: 30px; float: left;">
                <span style="float: left;">
                    <h2 style="margin-top: 80px;">{function="slocalize('Panthera Framework is loading admin panel', 'messages')"}...</h2>
                </span>
            </div>
        </div>
        
        <!-- Footer -->
        
        <div id="footer" class="centerWithContent">
            {if="$mobileTemplate == True"}<a href="{$PANTHERA_URL}/pa-admin.php?{function="getQueryString('GET', '', array('_', '__switchdevice'))"}&__switchdevice=mobile">Mobile</a>{/if} | 
            {if="$tabletTemplate == True"}<a href="{$PANTHERA_URL}/pa-admin.php?{function="getQueryString('GET', '', array('_', '__switchdevice'))"}&__switchdevice=tablet">Tablet</a>{/if} | 
            <a href="{$PANTHERA_URL}/pa-admin.php?{function="getQueryString('GET', '', array('_', '__switchdevice'))"}&__switchdevice=desktop"><b>Desktop</b></a>
            <div style="float: right;">
                Powered by <a href="https://github.com/webnull/panthera" target="_blank"><b>Panthera Framework</b></a> {$PANTHERA_VERSION}
            </div>
        </div>
    </body>
</html>

