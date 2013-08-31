<!-- Powered by Panthera Framework {$PANTHERA_VERSION} -->
<!--      http://github.com/webnull/panthera           -->
<html>
    <head>

      <meta charset="utf-8" />
      <meta name="format-detection" content="telephone=no" />

      <!-- Required meta viewport tag. Do not modify this! -->
      <meta name="viewport" content="user-scalable=no, initial-scale=1, maximum-scale=1, minimum-scale=1, width=device-width" />

      <title>{$site_title}</title>

      {$site_header}

     <!-- Include jquery -->
      <script type="text/javascript" src="http://code.jquery.com/jquery-1.10.0.min.js"></script>
      <script type="text/javascript" src="http://code.jquery.com/jquery-migrate-1.2.1.min.js"></script>
      <script type="text/javascript" src="http://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>

     <!-- Include panthera scripts -->
      <script type="text/javascript" src="{$PANTHERA_URL}/js/panthera.js"></script>
      <script type="text/javascript" src="{$PANTHERA_URL}/js/tiny_mce/tiny_mce.js"></script>
      <script type="text/javascript" src="{$PANTHERA_URL}/js/jquery.form.js"></script>
      <script type="text/javascript" src="{$PANTHERA_URL}/js/admin.js"></script>

     <!-- Include fries styles -->
      <link rel="stylesheet" href="{$PANTHERA_URL}/css/fries/base.css">
      <link rel="stylesheet" href="{$PANTHERA_URL}/css/fries/action-bars.css">
      <link rel="stylesheet" href="{$PANTHERA_URL}/css/fries/chevrons.css">
      <link rel="stylesheet" href="{$PANTHERA_URL}/css/fries/tabs.css">
      <link rel="stylesheet" href="{$PANTHERA_URL}/css/fries/content.css">
      <link rel="stylesheet" href="{$PANTHERA_URL}/css/fries/buttons.css">
      <link rel="stylesheet" href="{$PANTHERA_URL}/css/fries/forms.css">
      <link rel="stylesheet" href="{$PANTHERA_URL}/css/fries/lists.css">
      <link rel="stylesheet" href="{$PANTHERA_URL}/css/fries/spinners.css">
      <link rel="stylesheet" href="{$PANTHERA_URL}/css/fries/icomoon.css">
      <link rel="stylesheet" href="{$PANTHERA_URL}/css/fries/stack.css">
      <link rel="stylesheet" href="{$PANTHERA_URL}/css/fries/sliders.css">

     <!-- Include fries scripts -->
      <script src="{$PANTHERA_URL}/js/fries/stack.js"></script>
      <script src="{$PANTHERA_URL}/js/fries/action-bars.js"></script>
      <script src="{$PANTHERA_URL}/js/fries/spinners.js"></script>
      <script src="{$PANTHERA_URL}/js/fries/tabs.js"></script>
      
      <!-- Include our styles -->
      <link rel="stylesheet" href="{$PANTHERA_URL}/css/admin_mobile.css">

      <script type="text/javascript">
            $(document).ready(function() {
                    {if="isset($navigateTo)"}
                        navigateTo('{$AJAX_URL}?{$navigateTo}');
                    {else}
                        navigateTo('{$AJAX_URL}?display=dash&cat=admin&cat=admin');
                    {/if}
            });
      </script>

    </head>

 <body ontouchstart="">
  <!-- Page -->
   <div class="page">

    <!-- Header -->
    <header class="action-bar fixed-top">

      <h2 class="title" style="margin-left: 20px;">Panthera Mobile <span class="subtitle"><a href="{$PANTHERA_URL}/pa-admin.php?display=dash&cat=admin&cat=admin&__switchdevice=desktop">Desktop</a></span></h2>

     {if="$user->login != ''"}
      <div style="float: right; margin-right: 7px; margin-top: 7px;">
        <a href="pa-login.php?logout=True">
            <div class="login"><h2 class="title"><a href="pa-login.php?logout=True" />{function="localize('Logout')"}</a></h2></div>
        </a>
      </div>

      <div id="flags" style="float: right; margin-right: 30px; margin-top: 13px;">
          {loop="$flags"}
              <a href="#{$value}" onclick="navigateTo('?display=dash&cat=admin&_locale={$value}');"><img src="{$PANTHERA_URL}/images/admin/flags/{$value}.png" style="height: 15px;"></a>&nbsp;&nbsp;
          {/loop}
      </div>
     {/if}

    </header>
   <!-- End of header -->

   <!-- Content -->
    <div id="ajax_content" class="ajax_content"></div>
   <!-- End of content -->

   <!-- Bottom -->
    <nav class="action-bar fixed-bottom">
          <ul class="actions flex">
            {loop="$admin_menu"}
              {if="$value.icon != ''"}
                <li>
                    <a href="{$value.link}" data-ignore="true">
                            <img src="{$value.icon|pantheraUrl}" width="auto" height="38px" style="vertical-align: middle; margin-top: 5px;" alt="{$value.title}">
                    </a>
                </li>
              {/if}
            {/loop}
          </ul>
    </nav>
   <!-- End of bottom -->

   </div>
  <!-- End of page -->
 </body>

</html>
