<?php
/**
 * Created by PhpStorm.
 * User: Diego
 * Date: 2/2/2019
 * Time: 3:11 PM
 */

require_once 'Classes\DataLogger.php';

use Intervention\Image\ImageManagerStatic as Image;

class MimickBot extends DataLogger
{
    protected $boot_pool = [
        'StyletransferBot9683', //just use DeepAI API
        'ArtPostBot 1519', //just use Wikiart API
        'Botob 8008', //just use SPB API and my mirror filter
        'InspiroBot Quotes', //just use InspiroBot API
        'CensorBot 1111' //just use SPB API and my red box filter
    ];

    /**
     * @param $bot
     * @return array
     */
    public function mimick($bot)
    {
        $isSuccess = false;
        $comment = '';
        $IMAGE_PATH_NEW = '';


        switch ($bot) {
            case 'StyletransferBot9683':
//                https://findmyfbid.com/
                $comment .= '@[2169855356565545] ';

                $IMAGE_PATH_NEW = 'C:\Users\Diego\PhpstormProjects\FakePostBot\src\Bot\resources\newBot\styletransferbot9683.png';

                // Get two images (has own pool, i'll use SPB)
                // request to DeepAI API
                $ImgFetcher = new ImageFetcher();
                $style = $ImgFetcher->randomStyle();
                $result = $ImgFetcher->deepAiCnnmrf($style['path']);
                $isSuccess = $ImgFetcher->saveImageLocally($result, $IMAGE_PATH_NEW);
                if ($isSuccess) {
                    $comment .= mt_rand(0, 300).' as '.$style['name'];
                }

                break;
            case 'ArtPostBot 1519':
                $comment .= '@[1801596149950905] ';

                $IMAGE_PATH_NEW = 'C:\Users\Diego\PhpstormProjects\FakePostBot\src\Bot\resources\newBot\artpostbot_1519.png';

                // request to WikiArt API
                $ImgFetcher = new ImageFetcher();
                $result = $ImgFetcher->localSourceWikiArt();

                $true_url = $result['image'];
                $isSuccess = $ImgFetcher->saveImageLocally($true_url, $IMAGE_PATH_NEW);
                if ($isSuccess) {
                    $comment .= $result['title'].', '.$result['year'].' - '.$result['author'];
                }

                break;
            case 'Botob 8008':
                $comment .= '@[946056605583121] ';

                $IMAGE_PATH_NEW = 'C:\Users\Diego\PhpstormProjects\FakePostBot\src\Bot\resources\newBot\mirrored.png';
//                $IMAGE_PATH_NEW = 'C:\Users\Diego\PhpstormProjects\FakePostBot\src\Bot\resources\newBot\botob_8008.png';

                // request random source from SPB
                $ImgFetcher = new ImageFetcher();
                $true_url = 'https://www.shitpostbot.com/'.$ImgFetcher->randomSourceSPB();

                // mirror it
                $isSuccess = $ImgFetcher->saveImageLocally($true_url, $IMAGE_PATH_NEW);
                if ($isSuccess) {
                    $ImgTrans = new ImageTransformer();
                    $ImgTrans->mirrorImage($IMAGE_PATH_NEW, false);
                }

                break;
            case 'InspiroBot Quotes':
                $comment .= '@[1041852045984002] ';

                // request random source from InspiroBot API
                $IMAGE_PATH_NEW = 'C:\Users\Diego\PhpstormProjects\FakePostBot\src\Bot\resources\newBot\inspirobot_quotes.png';

                $ImgFetcher = new ImageFetcher();
                $true_url = $ImgFetcher->randomSourceInspiroBot();

                $isSuccess = $ImgFetcher->saveImageLocally($true_url, $IMAGE_PATH_NEW);

                break;
            case 'CensorBot 1111':
                $comment .= '@[227202038206959] ';

                $IMAGE_PATH_NEW = 'C:\Users\Diego\PhpstormProjects\FakePostBot\src\Bot\resources\newBot\censored.png';
//                $IMAGE_PATH_NEW = 'C:\Users\Diego\PhpstormProjects\FakePostBot\src\Bot\resources\newBot\censorbot.png';

                // request random source from SPB
                $ImgFetcher = new ImageFetcher();
                $true_url = 'https://www.shitpostbot.com/'.$ImgFetcher->randomSourceSPB();

                // put randomly sized red box on it
                $isSuccess = $ImgFetcher->saveImageLocally($true_url, $IMAGE_PATH_NEW);
                if ($isSuccess) {
                    $ImgTrans = new ImageTransformer();
                    $ImgTrans->censorImage($IMAGE_PATH_NEW);
                }

                break;
        }
        return [
            'success' => $isSuccess,
            'image'   => $IMAGE_PATH_NEW,
            'title' => $comment
        ];
    }

    public function fakePost($bot)
    {

        $message = 'mimicking '.$bot.'...';
        $this->logdata($message);

        $data = $this->mimick($bot);

        if ($data['success']) {
            $message = 'mimicking successful.';
            $this->logdata($message);

            return [
                'image' => $data['image'],
                'title' => $data['title']
            ];
        } else {
            $message = 'mimicking failed.';
            $this->logdata($message, 1);
        }
    }

    /**
     * @param $bot
     * @deprecated
     */
    public function simulatePostView($bot)
    {
        $message = 'simulating '.$bot.'...';
        $this->logdata($message);

        $mimick_data = $this->getMimickData($bot);
        $data = $this->mimick($bot);

        if ($data['success']) {
            $message = 'simulating successful.';
            $this->logdata($message);

            $dimension = $mimick_data['dimension'];
            $frame = $mimick_data['frame_path'];
            $photo = $mimick_data['new_path'];

            // configure with favored image driver (gd by default)
            Image::configure(array('driver' => 'imagick'));

            /** @var \Intervention\Image\Image $img_frame */
            $img_frame = Image::make($frame);

            //image to be pasted
            /** @var \Intervention\Image\Image $img_photo */
            $img_photo = Image::make($photo);

            //Width 501px height 670px
            if ($img_photo->getWidth() != $dimension['width'] || $img_photo->getHeight() != $dimension['height']) {
                $message = 'resizing photo.';
                $this->logdata($message);

                $newpath = 'C:\Users\Diego\PhpstormProjects\FakePostBot\src\Bot\resources\newBot\resized.png';
                $photo = $newpath;
                // resize image to fixed size
                $img_photo->resize($dimension['width'], $dimension['height']);
                $img_photo->save($newpath);
            }

            // paste another image
            $img_frame->insert($photo, 'center', 0, 90);

            $new_path = 'C:\Users\Diego\PhpstormProjects\FakePostBot\src\Bot\resources\newBot\fakepost.png';
            $img_frame->save($new_path);

        } else {
            $message = 'simulating failed.';
            $this->logdata($message, 1);
        }

    }

    /**
     * @param $bot
     * @return array
     * @deprecated
     */
    public function getMimickData($bot)
    {
        switch ($bot) {
            default:
            case 'StyletransferBot9683':
                $IMAGE_PATH_NEW = 'C:\Users\Diego\PhpstormProjects\FakePostBot\src\Bot\resources\newBot\styletransferbot9683.png';
                $frame_path = 'C:\Users\Diego\PhpstormProjects\FakePostBot\src\Bot\resources\frames\frame3.png';
                //Width 501px height 670px
                $dimension['width'] = 501;
                $dimension['height'] = 507;

                break;
            case 'ArtPostBot 1519':
                $IMAGE_PATH_NEW = 'C:\Users\Diego\PhpstormProjects\FakePostBot\src\Bot\resources\newBot\artpostbot_1519.png';
                $frame_path = 'C:\Users\Diego\PhpstormProjects\FakePostBot\src\Bot\resources\frames\frame.png';
                $dimension['width'] = 501;
                $dimension['height'] = 507;

                break;
            case 'Botob 8008':
//        $IMAGE_PATH_NEW = 'C:\Users\Diego\PhpstormProjects\FakePostBot\src\Bot\resources\newBot\botob_8008.png';
                $IMAGE_PATH_NEW = 'C:\Users\Diego\PhpstormProjects\FakePostBot\src\Bot\resources\newBot\mirrored.png';
                $frame_path = 'C:\Users\Diego\PhpstormProjects\FakePostBot\src\Bot\resources\frames\frame2.png';
                $dimension['width'] = 501;
                $dimension['height'] = 507;


                break;
            case 'InspiroBot Quotes':
                $IMAGE_PATH_NEW = 'C:\Users\Diego\PhpstormProjects\FakePostBot\src\Bot\resources\newBot\inspirobot_quotes.png';
                $frame_path = 'C:\Users\Diego\PhpstormProjects\FakePostBot\src\Bot\resources\frames\frame4.png';
                $dimension['width'] = 501;
                $dimension['height'] = 507;

                break;
            case 'CensorBot 1111':
                $IMAGE_PATH_NEW = 'C:\Users\Diego\PhpstormProjects\FakePostBot\src\Bot\resources\newBot\censored.png';
                $frame_path = 'C:\Users\Diego\PhpstormProjects\FakePostBot\src\Bot\resources\frames\frame5.png';
                $dimension['width'] = 501;
                $dimension['height'] = 507;

                break;
        }

        return [
            'image'      => $IMAGE_PATH_NEW,
            'frame'      => $frame_path,
            'dimension'  => $dimension
        ];
    }
}