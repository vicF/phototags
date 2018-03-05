<?php
chdir(dirname(__FILE__));
$configFile = 'config.php';

if (file_exists($configFile)) {
    include $configFile;
} else {
    die("Please rename the config-sample.php file to config.php and add your Flickr API key and secret to it\n");
}