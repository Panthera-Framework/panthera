<?php
if (!defined('IN_PANTHERA'))
    exit;
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Error (500)</title>
        <meta charset="utf-8">
        <META NAME="ROBOTS" CONTENT="NOINDEX">
        <script type="text/javascript" src="{$PANTHERA_URL}/admin/js/panthera.js"></script>
        <?php include getContentDir('templates/error.css.php'); ?>
    </head>

    <body>
        <div id="summary">
            <h1>Service unavaliable, we are sorry (500)</h1>

            <ol id="summaryDetails">
                <li>Our service is currently unavaliable, please try again later.<br/>This error has been automatically reported to site administrators.</li>
            </ol>
        </div>
    </body>
</html>