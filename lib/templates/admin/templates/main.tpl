<html>
    <head>
	    <meta charset="utf-8">
	    <title>{$site_title}</title>
	    {$site_header}

        <link rel="stylesheet" type="text/css" href="http://fonts.googleapis.com/css?family=Cuprum" />
        <link rel="stylesheet" type="text/css" href="{$PANTHERA_URL}/css/admin.css" />
        <link rel="stylesheet" type="text/css" href="{$PANTHERA_URL}/css/w2ui-1.2.min.css" />
        <link rel="stylesheet" type="text/css" href="http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css">

        <script type="text/javascript" src="http://code.jquery.com/jquery-1.10.0.min.js"></script>
        <script type="text/javascript" src="http://code.jquery.com/jquery-migrate-1.2.1.min.js"></script>
        <script type="text/javascript" src="http://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>

        <script type="text/javascript" src="{$PANTHERA_URL}/js/admin.js"></script>
        <script type="text/javascript" src="{$PANTHERA_URL}/js/tiny_mce/tiny_mce.js"></script>
        <script type="text/javascript" src="{$PANTHERA_URL}/js/w2ui-1.2.min.js"></script>
        <script type="text/javascript" src="{$PANTHERA_URL}/js/jquery.form.js"></script>
        <script type="text/javascript" src="//maps.google.com/maps/api/js?sensor=false"></script>
        <script type="text/javascript" src="{$PANTHERA_URL}/js/map.js"></script>


        <script>
            $(document).ready(function() {
                    {if isset($navigateTo)}
                        navigateTo('{$AJAX_URL}?{$navigateTo}');
                    {else}
                        navigateTo('{$AJAX_URL}?display=dash');
                    {/if}
            });

            $(window).resize(function () {
                $('#menuLayer').height($('#container-main').height());
            });
        </script>
    </head>
<body>

    <header id="siteHeader">
        <input type="button" onclick="navigateTo('pa-login.php?logout=True')" value="{"Logout"|localize}" style="float: right; margin-right: 20px; margin-top: 10px;">
        <div class="siteLogo"><h1><a href="{$PANTHERA_URL}/pa-admin.php">Panthera</a></h1> <span class="userHeader">{slocalize("Welcome %s, what would you like to do?", "messages", "admin")}</span></div>
    </header>


  <section id="contentDiv">
  	<div id="container-main">
		<div id="container">
		<div id="content">

		<div id="ajax_content" class="ajax_content">

        </div>

        		<div class="clear"></div>
			</div>
		</div>

		<aside class="leftBar" style="display:block;">
		    <div id="menuLayer" class="menuLayer">
			 <span class="category upperCategory">{"Admin panel"|localize}</span>
               <ul class="menu">
		            <li class="menuLong" style="display:block;">
			            <ul>
				            {foreach from=$admin_menu key=k item=i}
				            <li class="menuItemLi"><a href="#" onclick="navigateTo('{$i.link}');"><span class="menuItem">{$i.title}</span></a></li>
                            {/foreach}
			            </ul>
		            </li>
	            </ul>
	         <span class="category downCategory">{"Bookmarks"|localize}</span>
	         <ul class="menu" style="display:block;">
		            <li class="menuLong">
			            <ul>
				            <li><a href="#" onclick="navigateTo('?display=settings');"><span class="menuItem">{"Settings"|localize}</span></a></li>
			            </ul>
		            </li>
	            </ul>
	    </aside>
	    </div>




        <footer>
            <div class="footer">
                <p>Powered by Panthera Framework {$PANTHERA_VERSION}</p>
            </div>
      </footer>
    </body>
</html>
