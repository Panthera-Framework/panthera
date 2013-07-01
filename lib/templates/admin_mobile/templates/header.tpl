<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8" />
  <meta name="format-detection" content="telephone=no" />

  <!-- Required meta viewport tag. Do not modify this! -->
  <meta name="viewport" content="user-scalable=no, initial-scale=1, maximum-scale=1, minimum-scale=1, width=device-width" />

  <title>{$site_title}</title>

  <link rel="shortcut icon" href="favicon.png"/>

  <script type="text/javascript" src="http://code.jquery.com/jquery-1.10.0.min.js"></script>
  <script type="text/javascript" src="http://code.jquery.com/jquery-migrate-1.2.1.min.js"></script>
  <script type="text/javascript" src="http://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>

  <script type="text/javascript" src="{$PANTHERA_URL}/js/jquery.form.js"></script>
  <script type="text/javascript" src="{$PANTHERA_URL}/js/admin.js"></script>

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

  <script src="{$PANTHERA_URL}/js/fries/stack.js"></script>
  <script src="{$PANTHERA_URL}/js/fries/action-bars.js"></script>
  <script src="{$PANTHERA_URL}/js/fries/spinners.js"></script>
  <script src="{$PANTHERA_URL}/js/fries/tabs.js"></script>
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

      <ul class="actions pull-right">
        <li><a href="pa-login.php?logout=True" class="action" title="{"Sign out"|localize}" data-transition="push"><i class="icon-fries"></i></a></li>
      </ul>

    </header>
    <!-- End of header -->

    <!-- Bottom -->
    <nav class="action-bar fixed-bottom">
          <ul class="actions flex" data-overflow="false">
            <li><a href="" class="action" title="{"Refresh"|localize}" data-ignore="true"><i class="icon-refresh"></i></a></li>
          </ul>
    </nav>
    <!-- End of bottom -->
