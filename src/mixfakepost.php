<?php
/**
 * Created by PhpStorm.
 * User: Diego
 * Date: 2/2/2019
 * Time: 12:18 AM
 */

require __DIR__ .'/../vendor/autoload.php';
require_once 'resources/secrets.php';

$bot_pool = [
    'StyletransferBot9683',
//    'ArtPostBot 1519', // thank admin
//    'Botob 8008',
//    'InspiroBot Quotes',
//    'CensorBot 1111',
//    'EmojiBot 101',
//    'CountryBot 0208',
    'US Election Bot 1776'
];

//TODO US Election Bot 1776
//TODO ToolpostBot -- thank admin
//TODO Millennialbot 2019
//TODO CommentBot2005 -- it's Max's bot
//TODO ClickBot 2000

$bot = $bot_pool[array_rand($bot_pool)];
$bot = 'US Election Bot 1776';
//$bot = 'StyletransferBot9683';
//$bot = 'ArtPostBot 1519';
//$bot = 'Botob 8008';
//$bot = 'InspiroBot Quotes';
//$bot = 'CensorBot 1111';
//$bot = 'EmojiBot 101';
//$bot = 'CountryBot 0208';
// remove it so it doesn't gets chosen again
$key = array_search($bot, $bot_pool);
unset($bot_pool[$key]);
$bot2 = $bot_pool[array_rand($bot_pool)];
//$bot2 = 'StyletransferBot9683';
//$bot2 = 'ArtPostBot 1519';
//$bot2 = 'Botob 8008';
//$bot2 = 'InspiroBot Quotes';
//$bot2 = 'CensorBot 1111';
//$bot2 = 'EmojiBot 101';
//$bot2 = 'CountryBot 0208';


$dt = new FakepostBot\DataLogger();
$dt->logdata($bot);

$Mimick = new FakepostBot\MimickBot();
$result = $Mimick->fakePost($bot, $bot2, true);

// Make post with any random image
if (!empty($result)) {
    $FB_helper = new FakepostBot\FacebookHelper();
    $fb = $FB_helper->init($_APP_ID, $_APP_SECRET, $_ACCESS_TOKEN_DEBUG);

    $message = 'posting...';
    $dt->logdata($message);

    $FB_helper->newPost($fb, $result['image'], $result['title'], $result['bot_link'], $result['comment']);
}
