<?php
/**
 * Created by PhpStorm.
 * User: Diego
 * Date: 2/2/2019
 * Time: 7:54 PM
 */

require __DIR__ .'/../vendor/autoload.php';
require_once 'resources/secrets.php';


//$bot = 'Botob 8008';
//$bot = 'InspiroBot Quotes';
//$bot = 'CensorBot 1111';
//$bot = 'ArtPostBot 1519';
$bot = 'StyletransferBot9683';
$MimickBot = new \FakepostBot\MimickBot();
$MimickBot->mimick($bot);
