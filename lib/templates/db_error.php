<?php

if (!defined('IN_PANTHERA'))
    exit;
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Error - developer informations</title>
        <meta charset="utf-8">
        <META NAME="ROBOTS" CONTENT="NOINDEX">
        <script type="text/javascript" src="{$PANTHERA_URL}/js/panthera.js"></script>
        <?php include getContentDir('templates/error.css.php'); ?>
    </head>
    <?php
                function printUlArray($array)
                {
                    if (count($array))
                    {
                        foreach ($array as $key => $value)
                        {
                            if (!is_array($value))
                                echo '<ul><b>' .$key. '</b> => ' .$value. '</ul>';
                            else {
                                echo ' {<br>';
                                printUlArray($value);
                                echo '<br> }';
                            }
                        }
                    }
                }
            ?>

    <body>
        <div id="summary">
            <h1>Application database error occured.</h1>

            <ol id="summaryDetails">
                <li><b>Message:</b> <span style="color: #C00;"><?php echo $message; ?></span></li>
                <li><b>Query:</b> <span style="color: #C00;"><?php echo $lastQuery[0]; ?></span></li>
            </ol>
        </div>
        <div class="lighter">
            <h1>Details</h1>

            <div class="inner">
            <p>
                <?php echo $warningMessage; ?><br><br>
                <?php echo $message; ?>
            </p>
            </div>
        </div>

        <div class="footer">Message generated by Panthera Framework <?php echo PANTHERA_VERSION;?></div>
    </body>
</html>