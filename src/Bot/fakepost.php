<?php
/**
 * Created by PhpStorm.
 * User: Diego
 * Date: 2/2/2019
 * Time: 12:18 AM
 */

require_once realpath(__DIR__ . '/../..'). '/vendor/autoload.php';
require_once 'resources\secrets.php';
require_once 'Classes\ImageTransformer.php';
require_once 'Classes\ImageFetcher.php';
require_once 'Classes\FacebookHelper.php';
require_once 'Classes\DataLogger.php';
require_once 'Classes\MimickBot.php';

$bot = 'StyletransferBot9683';
//$bot = 'ArtPostBot 1519';
//$bot = 'Botob 8008';
//$bot = 'InspiroBot Quotes';
//$bot = 'CensorBot 1111';


$dt = new DataLogger();
$dt->logdata($bot);

$Mimick = new MimickBot();
$result = $Mimick->fakePost($bot);

// Make post with any random image
if (!empty($result)) {
    $FB_helper = new FacebookHelper();
    $fb = $FB_helper->init($_APP_ID, $_APP_SECRET, $_ACCESS_TOKEN_DEBUG);

    $message = 'posting...';
    $dt->logdata($message);

    $FB_helper->newPost($fb, $result['image'], $result['title']);
}
