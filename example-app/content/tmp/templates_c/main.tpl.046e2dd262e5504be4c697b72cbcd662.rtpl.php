<?php if(!class_exists('Rain\Tpl')){exit;}?><html>
    <head>
	    <meta charset="utf-8">
	    <title><?php echo $site_title; ?></title>
	    <?php echo $site_header; ?>


        <link rel="stylesheet" type="text/css" href="<?php echo $PANTHERA_URL; ?>/css/admin.css" />
        <link rel="stylesheet" type="text/css" href="<?php echo $PANTHERA_URL; ?>/css/w2ui-1.2.min.css" />
        <link rel="stylesheet" type="text/css" href="http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css">
        <link rel="stylesheet" type="text/css" href="<?php echo $PANTHERA_URL; ?>/css/msgBoxLight.css">

        <script type="text/javascript" src="http://code.jquery.com/jquery-1.10.0.min.js"></script>
        <script type="text/javascript" src="http://code.jquery.com/jquery-migrate-1.2.1.min.js"></script>
        <script type="text/javascript" src="http://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>

        <script type="text/javascript" src="<?php echo $PANTHERA_URL; ?>/js/admin.js"></script>
        <script type="text/javascript" src="<?php echo $PANTHERA_URL; ?>/js/tiny_mce/tiny_mce.js"></script>
        <script type="text/javascript" src="<?php echo $PANTHERA_URL; ?>/js/w2ui-1.2.min.js"></script>
        <script type="text/javascript" src="<?php echo $PANTHERA_URL; ?>/js/jquery.form.js"></script> <!-- deprecated -->
        <script type="text/javascript" src="<?php echo $PANTHERA_URL; ?>/js/jquery.tinycarousel.js"></script>
        <script type="text/javascript" src="<?php echo $PANTHERA_URL; ?>/js/jquery.msgBox.js"></script>
        <script type="text/javascript">
            $(document).ready(function() {
                    <?php if( isset($navigateTo) ){ ?>

                        navigateTo('<?php echo $AJAX_URL; ?>?<?php echo $navigateTo; ?>');
                    <?php }else{ ?>

                        navigateTo('<?php echo $AJAX_URL; ?>?display=dash');
                    <?php } ?>

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
        <div class="siteLogo"><h1><a href="<?php echo $PANTHERA_URL; ?>/pa-admin.php">Panthera</a></h1> 
        <span class="userHeader" style="width: 80%; height: 60px;">
            <div class="wrapper-demo">
					<div id="dd" class="wrapper-dropdown-5" tabindex="1"><?php echo $user->login; ?>

						<ul class="dropdown">
							<li><a href="#" onclick="navigateTo('?display=settings&action=my_account');"><i class="icon-user"></i><?php echo localize('My profile'); ?></a></li>
							<li><a href="<?php echo $PANTHERA_URL; ?>/pa-login.php?logout=True"><i class="icon-remove"></i><?php echo localize('Logout'); ?></a></li>
						</ul>
					</div>
		    ​</div>
		    
		    <div class="flagsInHeader">
            <?php $counter1=-1;  if( isset($flags) && ( is_array($flags) || $flags instanceof Traversable ) && sizeof($flags) ) foreach( $flags as $key1 => $value1 ){ $counter1++; ?>

                <a href="?display=dash&_locale=<?php echo $value1; ?>"><img src="<?php echo $PANTHERA_URL; ?>/images/admin/flags/<?php echo $value1; ?>.png" style="height: 12px; margin: 1px;"></a>
            <?php } ?>

            </div>
        </span></div>
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
		    <div id="menuLayer" class="menuLayer" style="background-color:#4d565c;">
			 <span class="category upperCategory"><?php echo localize('Admin panel'); ?></span>
               <ul class="menu">
		            <li class="menuLong" style="display:block;">
			            <ul>
			                <?php $counter1=-1;  if( isset($admin_menu) && ( is_array($admin_menu) || $admin_menu instanceof Traversable ) && sizeof($admin_menu) ) foreach( $admin_menu as $key1 => $value1 ){ $counter1++; ?>

				            <li class="menuItemLi"><a href="#" onclick="navigateTo('<?php echo $value1["link"]; ?>');"><span class="menuItem"><?php echo $value1["title"]; ?></span></a></li>
                            <?php } ?>

			            </ul>
		            </li>
	            </ul>
	         <!--<span class="category downCategory">{"Bookmarks"|localize}</span>
	         <ul class="menu" style="display:block;">
		            <li class="menuLong">
			            <ul>
				            <li><a href="#" onclick="navigateTo('?display=settings');"><span class="menuItem">{"Settings"|localize}</span></a></li>
			            </ul>
		            </li>
	            </ul>-->
	    </aside>
	    </div>

        <footer>
            <div class="footer">
                <p>Powered by <a href="http://github.com/webnull/panthera" target="_blank">Panthera Framework</a> <?php echo $PANTHERA_VERSION; ?></p>
            </div>
      </footer>
    </body>
</html>
