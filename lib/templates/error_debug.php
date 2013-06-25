<!DOCTYPE html>
<html>
    <head>
	    <meta charset="utf-8">
	    <title>Error - developer informations</title>

        <style>
            body {
                background: -webkit-gradient(linear, left top, left bottom, from(rgb(0, 0, 0)), to(rgb(111, 111, 111))); /* Chromium/Chrome/Safari - all webkit based browsers */
                background: -moz-linear-gradient(top,  rgb(0, 0, 0),  rgb(111, 111, 111)); /* Firefox - all gecko based browsers. */
                filter: progid:DXImageTransform.Microsoft.gradient(startColorstr=rgb(0, 0, 0), endColorstr=rgb(111, 111, 111)); /* Internet Explorer */
                background-image: -o-linear-gradient(rgb(0, 0, 0),rgb(111, 111, 111)); /* Opera */
                background-repeat:repeat-x, repeat-y;

                /*height: 1024px;*/
            }

            .info {
                color: #00529B;
                background: #BDE5F8;
            }

            .warning, .error, .info, .success {
                border: 1px solid;
                margin: 15px 0px;
                padding:15px 20px 15px 55px;
                width: 500px;	
                font: bold 12px verdana;
                -moz-box-shadow: 0 0 5px #888;
                -webkit-box-shadow: 0 0 5px#888;
                box-shadow: 0 0 5px #888;
                text-shadow: 2px 2px 2px #ccc;
                -webkit-border-radius: 15px;
                -moz-border-radius: 15px;
                border-radius: 15px;
                width: 92%;
            }

            .warning {
                color: #9F6000;
                background: #FEEFB3;
            }

            .error {
                color: rgb(27, 22, 2);
                background: #FFBABA;
            }

            .success {
                color: #4F8A10;
                background: #DFF2BF;
            }

            .msg {
                margin: 100px;
            }

            .content {
                background: rgb(255, 236, 236);
                margin: 0px auto;
                width: 960px;
                -moz-border-radius: 10px;
                -webkit-border-radius: 10px;
                border-radius: 10px;
                -moz-box-shadow: 0px 0px 10px #422A20;
                -webkit-box-shadow: 0px 0px 10px #422A20;
                box-shadow: 0px 0px 10px #422A20;
                padding: 30px 40px;
                padding-top: 5px;
                color: black;
            }

            .err_header {
                margin-bottom: 5px;
                color: black;
                font-size: 35px;
            }

            .class_name {
                color: rgb(58, 1, 1);
            }

            .func_name {
                color: rgb(32, 32, 209)
            }
        </style>

    </head>
    <body>
        <br><br>
        <div class="content">
            <h2 class="err_header">Panthera</h2>
            <p>Unexpected errors occured in PHP code execution, please take a look at those informations.</p>
            <br>

            <?php
                if ($errno == E_USER_ERROR)
                    $class = "error"; 
                else
                    $class = "warning";
            ?>

            <div class="<?php echo $class;?>">
                Message:<br>
                <?php echo $details['message']; ?>
            </div>

            <div class="warning">
                Stack trace:<br>
                <?php
                    foreach ($stack as $key => $trace)
                    {
                        $function = '<span class="func_name">' .$trace['function']. '</span>';

                        if (isset($trace['class']))
                            $function = '<span class="class_name">' .$trace['class']. '</span> -> <span class="func_name">' .$trace['function']. ' ( ' .implode(', ', $trace['args']). ' )</span>';

                        echo $key. ' => ' .$function. ' ( ' .implode(', ', $trace['args']). ' )<br>';
                    }
                ?>
            </div>

            <?php
                function printUlArray($array)
                {
                    if (count($array))
                    {
                        foreach ($array as $key => $value)
                        {
                            if (!is_array($value))
                                echo '<ul>' .$key. ' => ' .$value. '</ul>';
                            else
                                printUlArray($value);
                        }
                    }
                }
            ?>

            <div class="success">
                Debugger:<br>
                <?php echo str_replace("\n", "\n<br>", $panthera->logging->getOutput()); ?>
            </div>

            <div class="info">
                Informations:<br><br>
                &nbsp;GET:
                <?php printUlArray($_GET); ?><br>
                <br>&nbsp;POST:
                <?php printUlArray($_POST); ?><br>
                <br>&nbsp;SERVER:
                <?php printUlArray($_SERVER); ?>
                <br><br>Registered hooks:<br><?php printUlArray($panthera->getAllHooks()); ?>
            </div>

            <p style="float: right;"><small>Message generated by Panthera Framework (<?php echo PANTHERA_VERSION;?>)</small></p>
            <br>
        </div>
    </body>
</html>
