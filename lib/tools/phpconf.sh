#!/bin/sh
cd $1
echo '<?php error_reporting(0); include(getcwd(). "/config.php"); print(json_encode($config)); ?>' | php 2> /dev/null
