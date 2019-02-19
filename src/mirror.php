<?php
/**
 * Created by PhpStorm.
 * User: Diego
 * Date: 2/2/2019
 * Time: 5:39 PM
 */

require __DIR__ .'/../vendor/autoload.php';
require_once 'resources/secrets.php';

$option = '2';

//Width 501px height 670px
$image_path = 'resources/newBot/image'.$option.'.png';

$ImgTrans = new FakepostBot\ImageTransformer();
$ImgTrans->mirrorImage($image_path, false);
