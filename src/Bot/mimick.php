<?php
/**
 * Created by PhpStorm.
 * User: Diego
 * Date: 2/2/2019
 * Time: 7:54 PM
 */

require_once realpath(__DIR__ . '/../..'). '/vendor/autoload.php';
require_once 'resources\secrets.php';
require_once 'Classes\ImageTransformer.php';
require_once 'Classes\ImageFetcher.php';
require_once 'Classes\FacebookHelper.php';
require_once 'Classes\DataLogger.php';
require_once 'Classes\MimickBot.php';


//$bot = 'Botob 8008';
//$bot = 'InspiroBot Quotes';
//$bot = 'CensorBot 1111';
//$bot = 'ArtPostBot 1519';
$bot = 'StyletransferBot9683';
$MimickBot = new MimickBot();
$MimickBot->mimick($bot);
