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

      <script type="text/javascript">
            $(document).ready(function() {
                    {if="isset($navigateTo)"}
                        navigateTo('{$AJAX_URL}?{$navigateTo}');
                    {else}
                        navigateTo('{$AJAX_URL}?display=dash&cat=admin');
                    {/if}
            });
      </script>

    </head>

 <body ontouchstart="">
  <!-- Page -->
   <div class="page">

    <!-- Header -->
    <header class="action-bar fixed-top">

      <a href="?display=dash" class="app-icon action up" data-ignore="true">
        <i class="chevron"></i>
      </a>

      <h1 class="title" style="color: #33b5e5;">&nbsp;&nbsp;&nbsp;Panthera Mobile</h1>

     {if="$user->login != ''"}
      <div style="float: right; margin-right: 7px; margin-top: 9px;">
        <a href="pa-login.php?logout=True">
            <div class="login"><a href="pa-login.php?logout=True" />{function="localize('Logout')"}</a></div>
        </a>
      </div>

      <div id="flags" style="float: right; margin-right: 30px; margin-top: 12px;">
          {loop="$flags"}
              <a href="?display=dash&_locale={$value}"><img src="{$PANTHERA_URL}/images/admin/flags/{$value}.png" style="height: 24px;"></a>&nbsp;&nbsp;
          {/loop}
      </div>
     {/if}

    </header>
   <!-- End of header -->

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

   <!-- Content -->
    <div id="ajax_content" class="ajax_content"></div>
   <!-- End of content -->

   </div>
  <!-- End of page -->
 </body>

</html>
