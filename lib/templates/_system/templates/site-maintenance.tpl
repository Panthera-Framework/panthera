<!DOCTYPE html>
<html>
    <head>
        <title>{$title}</title>
        <meta charset="utf-8">
        <META NAME="ROBOTS" CONTENT="NOINDEX">
        <script type="text/javascript" src="{$PANTHERA_URL}/js/panthera.js"></script>
        <?php include getContentDir('templates/error.css.php'); ?>
    </head>

    <body>
        <div id="summary">
            <h1>{$title}</h1>
            
            <ol id="summaryDetails">
                <li>{$message}</li>
            </ol>
        </div>
    </body>
</html>