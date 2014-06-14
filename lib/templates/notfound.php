<?php
if (!defined('IN_PANTHERA'))
    exit;
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Error (404)</title>
        <meta charset="utf-8">
        <META NAME="ROBOTS" CONTENT="NOINDEX">
        <script type="text/javascript" src="{$PANTHERA_URL}/js/panthera.js"></script>
        <?php include getContentDir('templates/error.css.php'); ?>
    </head>

    <body>
        <div id="summary">
            <h1>Not found</h1>

            <ol id="summaryDetails">
                <li>Requested document cannot be found on our servers.</li>
                <li>If you found a broken link on this page please report it to site administrator.</li>
            </ol>
        </div>
    </body>
</html>