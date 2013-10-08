<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        {$site_header}
        <link rel="stylesheet" type="text/css" href="css/pantheraUI.css">
        <link rel="stylesheet" type="text/css" href="css/jquery.dropdown.css">
        <script type="text/javascript" src="http://code.jquery.com/jquery-1.10.0.min.js"></script>
        <script type="text/javascript" src="http://code.jquery.com/jquery-migrate-1.2.1.min.js"></script>
        <script type="text/javascript" src="{$PANTHERA_URL}/js/panthera.js"></script>
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
    </head>
    
    <body>
        <div id="logoBar">
            <div class="centerWithContent pantheraLogo">
                <span><a href="{$PANTHERA_URL}/pa-admin.php">Panthera</a></span>
                
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
                                        <img src="{$PANTHERA_URL}/images/admin/flags/{$value}.png" style="height: 12px; margin: 1px;"> {$value}
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
            <div class="titleBar">
                <span class="textTitleBar">Dash - Everything is here
                    <span class="titleBarIcons" style="float: right; width: 80%;">
                    
                        <a href="#" class="iconPopupLi" style="float: right;"><img src="images/admin/pantheraUI/transparent.png" class="pantheraIcon icon-Back" alt="Back"></a>
	                    
	                    <ul class="dropdown dropdown-horizontal dropdownFixed" style="float: right;">
		                    <li class="iconPopupLi">
		                        <img src="images/admin/pantheraUI/transparent.png" class="pantheraIcon icon-Language">
		                        
			                    <ul style="border: solid 1px #56687b; width: 150px;">
				                    <li><a href="./">Polski</a></li>
				                    <li><a href="./">English</a></li>
			                    </ul>
		                    </li>
	                    </ul>
                    </span>
                </span>
            </div>
            <div id="topContent">
                <div class="dash">
                    <div class="separator">&nbsp;</div>
                    <div class="dashItem">
                        <a href="#">
                          <img src="images/admin/menu/home.png" class="icon" alt="Avatar">
                          <p>Front page</p>
                        </a>
                    </div>
                    <div class="separator">&nbsp;</div>
                    <div class="dashItem">
                        <a href="#">
                          <img src="images/admin/menu/settings.png" class="icon" alt="Avatar">
                          <p>Settings</p>
                        </a>
                    </div>
                    <div class="separator">&nbsp;</div>
                    <div class="dashItem">
                        <a href="#">
                          <img src="images/admin/menu/developement.png" class="icon" alt="Avatar">
                          <p>Debugging center</p>
                        </a>
                    </div>
                    <div class="separator">&nbsp;</div>
                    <div class="dashItem">
                        <a href="#">
                          <img src="images/admin/menu/users.png" class="icon" alt="Avatar">
                          <p>Users</p>
                        </a>
                    </div>
                    <div class="separator">&nbsp;</div>
                    <div class="dashItem">
                        <a href="#">
                          <img src="images/admin/menu/newsletter.png" class="icon" alt="Avatar">
                          <p>Mailing</p>
                        </a>
                    </div>
                    <div class="separator">&nbsp;</div>
                    <div class="dashItem">
                        <a href="#" onclick="$('#popupOverlay').slideToggle(200);">
                          <img src="images/admin/menu/gallery.png" class="icon" alt="Avatar">
                          <p>Gallery</p>
                        </a>
                    </div>
                    <div class="separator">&nbsp;</div>
                </div>
            </div>
            
         <div id="popupOverlay">
            <div class="titledSeparator">System</div>
            
            <div class="iconViewContainer">
                <div class="iconViewItem">
                    <img src="images/admin/menu/users.png" alt="Users"> <p>Users <br><span>Users management</span></p>
                </div>
                
                <div class="iconViewItem">
                    <img src="images/admin/menu/users.png" alt="Users"> <p>Users <br><span>Users management</span></p>
                </div>
      
                <div class="iconViewItem">
                    <img src="images/admin/menu/users.png" alt="Users"> <p>Users <br><span>Users management</span></p>
                </div>
                
                <div class="iconViewItem">
                    <img src="images/admin/menu/users.png" alt="Users"> <p>Users <br><span>Users management</span></p>
                </div>
                
                <div class="iconViewItem">
                    <img src="images/admin/menu/users.png" alt="Users"> <p>Users <br><span>Users management</span></p>
                </div>
                
                <div class="iconViewItem">
                    <img src="images/admin/menu/users.png" alt="Users"> <p>Users <br><span>Users management</span></p>
                </div>
            </div>
            
            <div class="titledSeparator">System</div>
            
            <div class="popupContainer" style="text-align:center;">
                <table class="pantheraTable centeredObject">
                    <thead>
                        <th colspan="3">Options</th>
                    </thead>
                    
                    <tbody class="hovered">
                        <tr>
                            <td id="dashAvatar"><img src="http://graph.facebook.com/100002391859959/picture?width=30&amp;height=30" style="max-height: 90%; max-width:90%;" alt="Avatar"></td>
                            <td><a href="#asdasd">Damian Kęska</a></td>
                            <td>01 hours 49 minutes 14 seconds ago</td>
                        </tr>
                        
                        <tr>
                            <td id="dashAvatar"><img src="http://graph.facebook.com/100002391859959/picture?width=30&amp;height=30" style="max-height: 90%; max-width:90%;" alt="Avatar"></td>
                            <td>Damian Kęska</td>
                            <td>01 hours 49 minutes 14 seconds ago</td>
                        </tr>
                        <tr>
                            <td id="dashAvatar"><img src="http://graph.facebook.com/100002391859959/picture?width=30&amp;height=30" style="max-height: 90%; max-width:90%;" alt="Avatar"></td>
                            <td>Damian Kęska</td>
                            <td>01 hours 49 minutes 14 seconds ago</td>
                        </tr>
                               
                    </tbody>
                </table>
            </div>
         </div>
            
         <div class="ajax-content">
         
            <table class="pantheraTable">
                <thead>
                    <th colspan="3">Recently logged in users</th>
                </thead>
                
                <tbody class="hovered">
                    <tr>
                        <td id="dashAvatar"><img src="http://graph.facebook.com/100002391859959/picture?width=30&amp;height=30" style="max-height: 90%; max-width:90%;" alt="Avatar"></td>
                        <td><a href="#asdasd">Damian Kęska</a></td>
                        <td>01 hours 49 minutes 14 seconds ago</td>
                    </tr>
                    
                    <tr>
                        <td id="dashAvatar"><img src="http://graph.facebook.com/100002391859959/picture?width=30&amp;height=30" style="max-height: 90%; max-width:90%;" alt="Avatar"></td>
                        <td>Damian Kęska</td>
                        <td>01 hours 49 minutes 14 seconds ago</td>
                    </tr>
                    <tr>
                        <td id="dashAvatar"><img src="http://graph.facebook.com/100002391859959/picture?width=30&amp;height=30" style="max-height: 90%; max-width:90%;" alt="Avatar"></td>
                        <td>Damian Kęska</td>
                        <td>01 hours 49 minutes 14 seconds ago</td>
                    </tr>
                           
                </tbody>
            </table>
            
            <input type="button" value="Save" class="pantheraButton">
            <input type="text" placeholder="Write here some text..." class="pantheraText">
            
            <div class="pantheraCheck">
                <input type="checkbox" value="None" id="pantheraCheck" name="check" />
                <label for="pantheraCheck"></label>
            </div>
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

