<?php
/**
 * Created by PhpStorm.
 * User: Diego
 * Date: 2/9/2019
 * Time: 12:34 PM
 */

require_once  __DIR__.'\DataLogger.php';
require_once __DIR__.'\MimickBot.php';

use Intervention\Image\ImageManagerStatic as Image;
require_once 'MirrorFilter.php';
require_once 'CensorFilter.php';
require_once 'TransparencyFilter.php';


class MixBot extends DataLogger
{

    /**
     * @param array $bot1_info
     * @param array $bot2_info
     * @return string
     */
    public function getMixingStrategy($bot1_info, $bot2_info)
    {
        $mixtype = 'n/a';

//        'type' => 'image',
//        'own_title' => true,
//        'own_comment' => false

        //Decide how to mix:
        if ($bot1_info['type'] == 'text' && $bot2_info['type'] == 'text') {
            $mixtype = 'scramble';
        } elseif ($bot1_info['type'] == 'image' && $bot2_info['type'] == 'image') {
            if ($bot2_info['needs_base_image']) {
                // Just reprocess the images
                $mixtype = 'reprocessing';
            } elseif ($bot1_info['own_title'] || $bot2_info['own_title']) {
                // Make a transparency filter and overlap both images
                $mixtype = 'overlapping + title';
            } else {
                $mixtype = 'overlapping';
            }
        } elseif (($bot1_info['type'] == 'image' && $bot2_info['type'] == 'text') || ($bot1_info['type'] == 'text' && $bot2_info['type'] == 'image')) {
            //Take text from one and image from other
            $mixtype = 'normal';
        }

        return $mixtype;
    }


    /**
     * @desc parameter order is LIFO, meaning bot2 goes over
     * @param string $bot1
     * @param string $bot2
     * @return array
     */
    public function mix($bot1, $bot2)
    {
        $success = false;
        $method = '';
        $image = '';
        $text = '';
        $comments = [];
        $bot_links = [];

        $Mimick = new MimickBot();
        $bot1_info = $Mimick->getBotInfo($bot1);
        $bot2_info = $Mimick->getBotInfo($bot2);

        $strategy = $this->getMixingStrategy($bot1_info, $bot2_info);
        $message = 'strategy: '.$strategy;
        $this->logdata($message);

//        'type' => 'image',
//        'own_title' => true,
//        'own_comment' => false

        switch ($strategy) {
            case 'normal':
                if ($bot1_info['type'] == 'image' && $bot2_info['type'] == 'text') {
                    $method = $bot1.' (image) - '.$bot2.' (text)';
                    $message = 'method: '.$method;
                    $this->logdata($message);

                    $res1 = $Mimick->mimick($bot1);
                    $res2 = $Mimick->mimick($bot2);


                    if ($res1['success'] && $res1['success']) {
                        $image = $res1['image'];
                        $text = $res2['title'];
                    }

                } elseif ($bot1_info['type'] == 'text' && $bot2_info['type'] == 'image') {
                    $method = $bot1.' (text) - '.$bot2.' (image)';
                    $message = 'method: '.$method;
                    $this->logdata($message);

                    $res1 = $Mimick->mimick($bot1);
                    $res2 = $Mimick->mimick($bot2);
                    if ($res1['success'] && $res1['success']) {
                        $image = $res2['image'];
                        $text = $res1['title'];
                        $success = true;
                    }

                } else {
                    $message = 'unexpected bot info';
                    $this->logdata($message, 1);
                }

                if (!empty($image) && !empty($text)) {
                    $success = true;
                }

                break;
            case 'scramble':
                $method = $bot1.' (text) - '.$bot2.' (text)';
                $message = 'method: '.$method;
                $this->logdata($message);

                $res1 = $Mimick->mimick($bot1);
                $res2 = $Mimick->mimick($bot2);

                $text = $this->scrambleText($res1['title'], $res2['title']);

                if (!empty($text)) {
                    $success = true;
                }

                break;
            case 'reprocessing':
                $method = $bot1.' (image) - '.$bot2.' (image)';
                $message = 'method: '.$method;
                $this->logdata($message);

                // Just reprocess the images
                $res1 = $Mimick->mimick($bot1);
                $image_path = $res1['image'];

                if (!empty($image_path)) {
                    $res2 = $Mimick->mimick($bot2, $image_path);
                    $image = $res2['image'];
                    $success = true;
                }

                break;
            case 'overlapping':
                $method = $bot1.' (image) - '.$bot2.' (image)';
                $message = 'method: '.$method;
                $this->logdata($message);

                // getting data
                $res1 = $Mimick->mimick($bot1, '');
                $image_path1 = $res1['image'];

                $res2 = $Mimick->mimick($bot2, '');
                $image_path2 = $res2['image'];

                // Make a transparency filter and overlap both images
                if (!empty($image_path1)) {
                    /** @var \Intervention\Image\Image $img */
                    $img1 = Image::make($image_path1);

                    $path_to_save = 'C:\Users\Diego\PhpstormProjects\FakePostBot\src\Bot\resources\newBot\mixed_'.$strategy.'.png';
                    // apply filter and save result to mixed
                    $img1->filter(new \TransparencyFilter($image_path2, $path_to_save));

                    $image = $path_to_save;
                    $success = true;
                }

                break;
            case 'overlapping + title':
                $method = $bot1.' (image) - '.$bot2.' (image)';
                $message = 'method: '.$method;
                $this->logdata($message);

                // getting data
                $res1 = $Mimick->mimick($bot1, '');
                $image_path1 = $res1['image'];

                $res2 = $Mimick->mimick($bot2, '');
                $image_path2 = $res2['image'];

                // Mix the titles
                $b1HasTitle = !empty($res1['title']);
                $b2HasTitle = !empty($res2['title']);
                if ($b1HasTitle || $b2HasTitle) {
                    if ($b1HasTitle && $b2HasTitle) {
                        $text = $this->scrambleText($res1['title'], $res2['title']);
                    } elseif ($b1HasTitle) {
                        $text = $res1['title'];
                    } else {
                        $text = $res2['title'];
                    }
                }

                // Make a transparency filter and overlap both images
                if (!empty($image_path1) && !empty($text)) {
                    /** @var \Intervention\Image\Image $img */
                    $img1 = Image::make($image_path1);

                    $path_to_save = 'C:\Users\Diego\PhpstormProjects\FakePostBot\src\Bot\resources\newBot\mixed_'.$strategy.'.png';
                    // apply filter and save result to mixed
                    $img1->filter(new \TransparencyFilter($image_path2, $path_to_save));

                    $image = $path_to_save;
                    $success = true;
                }

                break;
            default:
                $message = 'unexpected mixing strategy: '.$strategy;
                $this->logdata($message, 1);
        }

        if ($success) {
            array_push($comments, $res1['comment']);
            array_push($comments, $res2['comment']);
            array_push($bot_links, $res1['bot_link']);
            array_push($bot_links, $res2['bot_link']);
        }


        return [
            'bot_links' => $bot_links,
            'comments'  => $comments,
            'image'     => $image,
            'method'    => $method,
            'success'   => $success,
            'strategy'  => $strategy,
            'text'      => $text
        ];
    }

    public function scrambleText($t1, $t2)
    {
        $mixed = $t1 .' '.$t2;
        //Separate text in tokens and shuffle them
        $tokens = explode(' ', $mixed);
        shuffle($tokens);
        // glue
        $text = implode(' ', $tokens);

        return $text;
    }


}