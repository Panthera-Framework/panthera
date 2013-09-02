<html>
    <head>
	    <meta charset="utf-8">
	    {$site_header}

        <link rel="stylesheet" type="text/css" href="{$PANTHERA_URL}/css/admin.css" />
        <link rel="stylesheet" type="text/css" href="{$PANTHERA_URL}/css/w2ui-1.2.min.css" />
        <link rel="stylesheet" type="text/css" href="http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css">
        <link rel="stylesheet" type="text/css" href="{$PANTHERA_URL}/css/msgBoxLight.css">

        <script type="text/javascript" src="http://code.jquery.com/jquery-1.10.0.min.js"></script>
        <script type="text/javascript" src="http://code.jquery.com/jquery-migrate-1.2.1.min.js"></script>
        <script type="text/javascript" src="http://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>

        <script type="text/javascript" src="{$PANTHERA_URL}/js/panthera.js"></script>
        <!--<script type="text/javascript" src="{$PANTHERA_URL}/js/tiny_mce/tiny_mce.js"></script>-->
        <script type="text/javascript" src="{$PANTHERA_URL}/js/w2ui-1.2.min.js"></script>
        <script type="text/javascript" src="{$PANTHERA_URL}/js/jquery.tinycarousel.js"></script>
        <script type="text/javascript" src="{$PANTHERA_URL}/js/jquery.msgBox.js"></script>
        
        <link rel='stylesheet' href='{$PANTHERA_URL}/css/admin/jquery.dropdown.css' type='text/css' media='all' />
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

            function DropDown(el) {
				    this.dd = el;
				    this.initEvents();
			}
			
			    DropDown.prototype = {
				    initEvents : function() {
					    var obj = this;

					    obj.dd.on('click', function(event){
						    $(this).toggleClass('active');
						    event.stopPropagation();
					    });	
				    }
			    }

			    $(function() {
				    var dd = new DropDown( $('#dd') );

				    $(document).click(function() {
					    // all dropdowns
					    $('.wrapper-dropdown-5').removeClass('active');
				    });

			    });
        </script>
    </head>
<body>

    <header id="siteHeader">
    	<img src="{$PANTHERA_URL}/images/admin/logo.svg" class="logo"></img>
        <div class="siteLogo">
        	<h1><a href="{$PANTHERA_URL}/pa-admin.php">Panthera</a></h1> 
           <span class="userHeader" style="width: 80%; height: 60px;">
            <div class="wrapper-demo">
					<div id="dd" class="wrapper-dropdown-5" tabindex="1">{$user->login}
						<ul class="dropdownBlue">
							<li><a href="#" onclick="navigateTo('?display=users&cat=admin&action=account');"><i class="icon-user"></i>{function="localize('My profile')"}</a></li>
							<li><a href="{$PANTHERA_URL}/pa-login.php?logout=True"><i class="icon-remove"></i>{function="localize('Logout')"}</a></li>
						</ul>
					</div>
		    ​</div>
		    
		    <div class="flagsInHeader">
            {loop="$flags"}
                <a href="?{function="getQueryString('GET', '', array('_', '_locale'))"}&_locale={$value}"><img src="{$PANTHERA_URL}/images/admin/flags/{$value}.png" style="height: 12px; margin: 1px;"></a>
            {/loop}
            </div>
           </span>
       </div>
    </header>


  <section id="contentDiv" style="position: relative;">
  	<div id="container-main">
		<div id="container">
		<div id="content" style="position: relative;">

		<div id="ajax_content" class="ajax_content" style="position: relative;">

        </div>

        		<div class="clear"></div>
			</div>
		</div>

		<aside class="leftBar" style="display:block;">
		    <div id="menuLayer" class="menuLayer" style="background-color:#4d565c;">
			 <span class="category upperCategory">{function="localize('Admin panel')"}</span>
               <ul class="menu">
		            <li class="menuLong" style="display:block;">
			            <ul>
			                {loop="$admin_menu"}
				            <li class="menuItemLi">
				                <a href="{$value.link}" class="ajaxLinkMain">
				                    {if="isset($value.icon)"}
				                    <span style="position: absolute; top: 7px; width: 50px; height: 28px;"><img src="{$value.icon|pantheraUrl}" style="width: 28px;"></span>
				                    {/if}
				                    <span class="menuItem" {if="isset($value.icon)"}style="padding-left: 25px;"{/if}>{$value.title}</span></a></li>
                            {/loop}
			            </ul>
		            </li>
	            </ul>
	         <!--<span class="category downCategory">{"Bookmarks"|localize}</span>
	         <ul class="menu" style="display:block;">
		            <li class="menuLong">
			            <ul>
				            <li><a href="#" onclick="navigateTo('?display=settings&cat=admin');"><span class="menuItem">{"Settings"|localize}</span></a></li>
			            </ul>
		            </li>
	            </ul>-->
	    </aside>
	    </div>

        <footer>
            <div class="footer">
                <p style="float: left; margin-left: 275px;">{if="$mobileTemplate == True"}<a href="{$PANTHERA_URL}/pa-admin.php?{function="getQueryString('GET', '', array('_', '__switchdevice'))"}&__switchdevice=mobile">Mobile</a>{/if} | {if="$tabletTemplate == True"}<a href="{$PANTHERA_URL}/pa-admin.php?{function="getQueryString('GET', '', array('_', '__switchdevice'))"}&__switchdevice=tablet">Tablet</a>{/if} | <a href="{$PANTHERA_URL}/pa-admin.php?{function="getQueryString('GET', '', array('_', '__switchdevice'))"}&__switchdevice=desktop">Desktop</a></p>
            
                <p>Powered by <a href="http://github.com/webnull/panthera" target="_blank">Panthera Framework</a> {$PANTHERA_VERSION}</p>
            </div>
      </footer>
    </body>
</html>
