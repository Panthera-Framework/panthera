<?php
if (!defined('IN_PANTHERA'))
    exit;
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Error (403)</title>
        <meta charset="utf-8">
        <META NAME="ROBOTS" CONTENT="NOINDEX">
        <script type="text/javascript" src="{$PANTHERA_URL}/js/panthera.js"></script>
        <?php include getContentDir('templates/error.css.php'); ?>
    </head>

    <body>
        <div id="summary">
            <h1>Forbidden</h1>
            
            <ol id="summaryDetails">
                <li>You don't have access to this resource.</li>
            </ol>
        </div>
    </body>
</html>