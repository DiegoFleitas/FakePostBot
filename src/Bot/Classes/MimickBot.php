<?php
/**
 * Created by PhpStorm.
 * User: Diego
 * Date: 2/2/2019
 * Time: 3:11 PM
 */

require_once 'Classes\DataLogger.php';

use Intervention\Image\ImageManagerStatic as Image;
use Stringy\Stringy as S;

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
        $link = '';

        switch ($bot) {
            case 'StyletransferBot9683':
                $link = 'https://www.facebook.com/Styletransferbot9683-2169855356565545/';

                $IMAGE_PATH_NEW = 'C:\Users\Diego\PhpstormProjects\FakePostBot\src\Bot\resources\newBot\styletransferbot9683.png';

                // Get two images (has own pool, i'll use SPB)
                // request to DeepAI API
                $ImgFetcher = new ImageFetcher();
                $style = $ImgFetcher->randomStyle();

                $message = 'style data '.join(' - ', $style);
                $this->logdata($message);

                $result = $ImgFetcher->deepAiCnnmrf($style['path']);
                $isSuccess = $ImgFetcher->saveImageLocally($result, $IMAGE_PATH_NEW);
                if ($isSuccess) {
                    $comment .= mt_rand(0, 300).' as '.$style['name'];
                }

                break;
            case 'ArtPostBot 1519':
                $link = 'https://www.facebook.com/ArtpostBot/';

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
                $link = 'https://www.facebook.com/botob8008/';

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
                $link = 'https://www.facebook.com/InspiroBotQuotesIGot/';

                // request random source from InspiroBot API
                $IMAGE_PATH_NEW = 'C:\Users\Diego\PhpstormProjects\FakePostBot\src\Bot\resources\newBot\inspirobot_quotes.png';

                $ImgFetcher = new ImageFetcher();
                $true_url = $ImgFetcher->randomSourceInspiroBot();

                $isSuccess = $ImgFetcher->saveImageLocally($true_url, $IMAGE_PATH_NEW);

                break;
            case 'CensorBot 1111':

                $link = 'https://www.facebook.com/CensorBot-1111-227202038206959/';

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
            case 'EmojiBot 101':
                $link = 'https://www.facebook.com/emojibot101/';

                $emojis = '';
                $names = '';

                $n = mt_rand(1, 3);
                for($i = 0; $i < $n; $i++) {
                    $isSuccess = true;
                    $aux = $this->getRandomEmoji();
                    $emojis .= $aux['emoji_code'];
                    $names .= $aux['emoji_name'].' ';
                }

                $comment = $emojis ."\n". $names;

                break;
        }
        return [
            'success'  => $isSuccess,
            'image'    => $IMAGE_PATH_NEW,
            'title'    => $comment,
            'bot_link' => $link
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
                'image'    => $data['image'],
                'title'    => $data['title'],
                'bot_link' => $data['bot_link']
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

    public function getRandomEmoji(){

        //<editor-fold desc="emoji array">
        $emojis = array(
            0 => array(
                'U+1F9EE',
                '⊛ abacus'
            ),
            1 => array(
                'U+1F9A1',
                '⊛ badger'
            ),
            2 => array(
                'U+1F96F',
                '⊛ bagel'
            ),
            3 => array(
                'U+1F9B2',
                '⊛ bald'
            ),
            4 => array(
                'U+1F9FA',
                '⊛ basket'
            ),
            5 => array(
                'U+1F9B4',
                '⊛ bone'
            ),
            6 => array(
                'U+1F9F1',
                '⊛ brick'
            ),
            7 => array(
                'U+1F9F9',
                '⊛ broom'
            ),
            8 => array(
                'U+265F',
                '⊛ chess pawn'
            ),
            9 => array(
                'U+1F976',
                '⊛ cold face'
            ),
            10 => array(
                'U+1F9ED',
                '⊛ compass'
            ),
            11 => array(
                'U+1F9C1',
                '⊛ cupcake'
            ),
            12 => array(
                'U+1F9B1',
                '⊛ curly hair'
            ),
            13 => array(
                'U+1F9EC',
                '⊛ dna'
            ),
            14 => array(
                'U+1F9EF',
                '⊛ fire extinguisher'
            ),
            15 => array(
                'U+1F9E8',
                '⊛ firecracker'
            ),
            16 => array(
                'U+1F97F',
                '⊛ flat shoe'
            ),
            17 => array(
                'U+1F94F',
                '⊛ flying disc'
            ),
            18 => array(
                'U+1F9B6',
                '⊛ foot'
            ),
            19 => array(
                'U+1F97D',
                '⊛ goggles'
            ),
            20 => array(
                'U+1F97E',
                '⊛ hiking boot'
            ),
            21 => array(
                'U+1F99B',
                '⊛ hippopotamus'
            ),
            22 => array(
                'U+1F975',
                '⊛ hot face'
            ),
            23 => array(
                'U+267E',
                '⊛ infinity'
            ),
            24 => array(
                'U+1F998',
                '⊛ kangaroo'
            ),
            25 => array(
                'U+1F97C',
                '⊛ lab coat'
            ),
            26 => array(
                'U+1F94D',
                '⊛ lacrosse'
            ),
            27 => array(
                'U+1F96C',
                '⊛ leafy green'
            ),
            28 => array(
                'U+1F9B5',
                '⊛ leg'
            ),
            29 => array(
                'U+1F999',
                '⊛ llama'
            ),
            30 => array(
                'U+1F99E',
                '⊛ lobster'
            ),
            31 => array(
                'U+1F9F4',
                '⊛ lotion bottle'
            ),
            32 => array(
                'U+1F9F3',
                '⊛ luggage'
            ),
            33 => array(
                'U+1F9F2',
                '⊛ magnet'
            ),
            34 => array(
                'U+1F9B8 U+200D U+2642 U+FE0F',
                '⊛ man superhero'
            ),
            35 => array(
                'U+1F9B9 U+200D U+2642 U+FE0F',
                '⊛ man supervillain'
            ),
            36 => array(
                'U+1F468 U+200D U+1F9B2',
                '⊛ man: bald'
            ),
            37 => array(
                'U+1F468 U+200D U+1F9B1',
                '⊛ man: curly hair'
            ),
            38 => array(
                'U+1F468 U+200D U+1F9B0',
                '⊛ man: red hair'
            ),
            39 => array(
                'U+1F468 U+200D U+1F9B3',
                '⊛ man: white hair'
            ),
            40 => array(
                'U+1F96D',
                '⊛ mango'
            ),
            41 => array(
                'U+1F9A0',
                '⊛ microbe'
            ),
            42 => array(
                'U+1F96E',
                '⊛ moon cake'
            ),
            43 => array(
                'U+1F99F',
                '⊛ mosquito'
            ),
            44 => array(
                'U+1F9FF',
                '⊛ nazar amulet'
            ),
            45 => array(
                'U+1F99C',
                '⊛ parrot'
            ),
            46 => array(
                'U+1F973',
                '⊛ partying face'
            ),
            47 => array(
                'U+1F99A',
                '⊛ peacock'
            ),
            48 => array(
                'U+1F9EB',
                '⊛ petri dish'
            ),
            49 => array(
                'U+1F3F4 U+200D U+2620 U+FE0F',
                '⊛ pirate flag'
            ),
            50 => array(
                'U+1F97A',
                '⊛ pleading face'
            ),
            51 => array(
                'U+1F9E9',
                '⊛ puzzle piece'
            ),
            52 => array(
                'U+1F99D',
                '⊛ raccoon'
            ),
            53 => array(
                'U+1F9FE',
                '⊛ receipt'
            ),
            54 => array(
                'U+1F9E7',
                '⊛ red envelope'
            ),
            55 => array(
                'U+1F9B0',
                '⊛ red hair'
            ),
            56 => array(
                'U+1F9FB',
                '⊛ roll of paper'
            ),
            57 => array(
                'U+1F9F7',
                '⊛ safety pin'
            ),
            58 => array(
                'U+1F9C2',
                '⊛ salt'
            ),
            59 => array(
                'U+1F6F9',
                '⊛ skateboard'
            ),
            60 => array(
                'U+1F970',
                '⊛ smiling face with hearts'
            ),
            61 => array(
                'U+1F9FC',
                '⊛ soap'
            ),
            62 => array(
                'U+1F94E',
                '⊛ softball'
            ),
            63 => array(
                'U+1F9FD',
                '⊛ sponge'
            ),
            64 => array(
                'U+1F9B8',
                '⊛ superhero'
            ),
            65 => array(
                'U+1F9B9',
                '⊛ supervillain'
            ),
            66 => array(
                'U+1F9A2',
                '⊛ swan'
            ),
            67 => array(
                'U+1F9F8',
                '⊛ teddy bear'
            ),
            68 => array(
                'U+1F9EA',
                '⊛ test tube'
            ),
            69 => array(
                'U+1F9F5',
                '⊛ thread'
            ),
            70 => array(
                'U+1F9F0',
                '⊛ toolbox'
            ),
            71 => array(
                'U+1F9B7',
                '⊛ tooth'
            ),
            72 => array(
                'U+1F9B3',
                '⊛ white hair'
            ),
            73 => array(
                'U+1F9B8 U+200D U+2640 U+FE0F',
                '⊛ woman superhero'
            ),
            74 => array(
                'U+1F9B9 U+200D U+2640 U+FE0F',
                '⊛ woman supervillain'
            ),
            75 => array(
                'U+1F469 U+200D U+1F9B2',
                '⊛ woman: bald'
            ),
            76 => array(
                'U+1F469 U+200D U+1F9B1',
                '⊛ woman: curly hair'
            ),
            77 => array(
                'U+1F469 U+200D U+1F9B0',
                '⊛ woman: red hair'
            ),
            78 => array(
                'U+1F469 U+200D U+1F9B3',
                '⊛ woman: white hair'
            ),
            79 => array(
                'U+1F974',
                '⊛ woozy face'
            ),
            80 => array(
                'U+1F9F6',
                '⊛ yarn'
            ),
            81 => array(
                'U+1F947',
                '1st place medal'
            ),
            82 => array(
                'U+1F948',
                '2nd place medal'
            ),
            83 => array(
                'U+1F949',
                '3rd place medal'
            ),
            84 => array(
                'U+1F170',
                'A button (blood type)'
            ),
            85 => array(
                'U+1F18E',
                'AB button (blood type)'
            ),
            86 => array(
                'U+1F39F',
                'admission tickets'
            ),
            87 => array(
                'U+1F6A1',
                'aerial tramway'
            ),
            88 => array(
                'U+2708',
                'airplane'
            ),
            89 => array(
                'U+1F6EC',
                'airplane arrival'
            ),
            90 => array(
                'U+1F6EB',
                'airplane departure'
            ),
            91 => array(
                'U+23F0',
                'alarm clock'
            ),
            92 => array(
                'U+2697',
                'alembic'
            ),
            93 => array(
                'U+1F47D',
                'alien'
            ),
            94 => array(
                'U+1F47E',
                'alien monster'
            ),
            95 => array(
                'U+1F691',
                'ambulance'
            ),
            96 => array(
                'U+1F3C8',
                'american football'
            ),
            97 => array(
                'U+1F3FA',
                'amphora'
            ),
            98 => array(
                'U+2693',
                'anchor'
            ),
            99 => array(
                'U+1F4A2',
                'anger symbol'
            ),
            100 => array(
                'U+1F620',
                'angry face'
            ),
            101 => array(
                'U+1F47F',
                'angry face with horns'
            ),
            102 => array(
                'U+1F627',
                'anguished face'
            ),
            103 => array(
                'U+1F41C',
                'ant'
            ),
            104 => array(
                'U+1F4F6',
                'antenna bars'
            ),
            105 => array(
                'U+1F630',
                'anxious face with sweat'
            ),
            106 => array(
                'U+2652',
                'Aquarius'
            ),
            107 => array(
                'U+2648',
                'Aries'
            ),
            108 => array(
                'U+1F69B',
                'articulated lorry'
            ),
            109 => array(
                'U+1F3A8',
                'artist palette'
            ),
            110 => array(
                'U+1F632',
                'astonished face'
            ),
            111 => array(
                'U+1F3E7',
                'ATM sign'
            ),
            112 => array(
                'U+269B',
                'atom symbol'
            ),
            113 => array(
                'U+1F697',
                'automobile'
            ),
            114 => array(
                'U+1F951',
                'avocado'
            ),
            115 => array(
                'U+1F171',
                'B button (blood type)'
            ),
            116 => array(
                'U+1F476',
                'baby'
            ),
            117 => array(
                'U+1F47C',
                'baby angel'
            ),
            118 => array(
                'U+1F37C',
                'baby bottle'
            ),
            119 => array(
                'U+1F424',
                'baby chick'
            ),
            120 => array(
                'U+1F6BC',
                'baby symbol'
            ),
            121 => array(
                'U+1F519',
                'BACK arrow'
            ),
            122 => array(
                'U+1F447',
                'backhand index pointing down'
            ),
            123 => array(
                'U+1F448',
                'backhand index pointing left'
            ),
            124 => array(
                'U+1F449',
                'backhand index pointing right'
            ),
            125 => array(
                'U+1F446',
                'backhand index pointing up'
            ),
            126 => array(
                'U+1F392',
                'backpack'
            ),
            127 => array(
                'U+1F953',
                'bacon'
            ),
            128 => array(
                'U+1F3F8',
                'badminton'
            ),
            129 => array(
                'U+1F6C4',
                'baggage claim'
            ),
            130 => array(
                'U+1F956',
                'baguette bread'
            ),
            131 => array(
                'U+2696',
                'balance scale'
            ),
            132 => array(
                'U+1F388',
                'balloon'
            ),
            133 => array(
                'U+1F5F3',
                'ballot box with ballot'
            ),
            134 => array(
                'U+1F34C',
                'banana'
            ),
            135 => array(
                'U+1F3E6',
                'bank'
            ),
            136 => array(
                'U+1F4CA',
                'bar chart'
            ),
            137 => array(
                'U+1F488',
                'barber pole'
            ),
            138 => array(
                'U+26BE',
                'baseball'
            ),
            139 => array(
                'U+1F3C0',
                'basketball'
            ),
            140 => array(
                'U+1F987',
                'bat'
            ),
            141 => array(
                'U+1F6C1',
                'bathtub'
            ),
            142 => array(
                'U+1F50B',
                'battery'
            ),
            143 => array(
                'U+1F3D6',
                'beach with umbrella'
            ),
            144 => array(
                'U+1F601',
                'beaming face with smiling eyes'
            ),
            145 => array(
                'U+1F43B',
                'bear'
            ),
            146 => array(
                'U+1F493',
                'beating heart'
            ),
            147 => array(
                'U+1F6CF',
                'bed'
            ),
            148 => array(
                'U+1F37A',
                'beer mug'
            ),
            149 => array(
                'U+1F514',
                'bell'
            ),
            150 => array(
                'U+1F515',
                'bell with slash'
            ),
            151 => array(
                'U+1F6CE',
                'bellhop bell'
            ),
            152 => array(
                'U+1F371',
                'bento box'
            ),
            153 => array(
                'U+1F6B2',
                'bicycle'
            ),
            154 => array(
                'U+1F459',
                'bikini'
            ),
            155 => array(
                'U+1F9E2',
                'billed cap'
            ),
            156 => array(
                'U+2623',
                'biohazard'
            ),
            157 => array(
                'U+1F426',
                'bird'
            ),
            158 => array(
                'U+1F382',
                'birthday cake'
            ),
            159 => array(
                'U+26AB',
                'black circle'
            ),
            160 => array(
                'U+1F3F4',
                'black flag'
            ),
            161 => array(
                'U+1F5A4',
                'black heart'
            ),
            162 => array(
                'U+2B1B',
                'black large square'
            ),
            163 => array(
                'U+25FC',
                'black medium square'
            ),
            164 => array(
                'U+25FE',
                'black medium-small square'
            ),
            165 => array(
                'U+2712',
                'black nib'
            ),
            166 => array(
                'U+25AA',
                'black small square'
            ),
            167 => array(
                'U+1F532',
                'black square button'
            ),
            168 => array(
                'U+1F33C',
                'blossom'
            ),
            169 => array(
                'U+1F421',
                'blowfish'
            ),
            170 => array(
                'U+1F4D8',
                'blue book'
            ),
            171 => array(
                'U+1F535',
                'blue circle'
            ),
            172 => array(
                'U+1F499',
                'blue heart'
            ),
            173 => array(
                'U+1F417',
                'boar'
            ),
            174 => array(
                'U+1F4A3',
                'bomb'
            ),
            175 => array(
                'U+1F516',
                'bookmark'
            ),
            176 => array(
                'U+1F4D1',
                'bookmark tabs'
            ),
            177 => array(
                'U+1F4DA',
                'books'
            ),
            178 => array(
                'U+1F37E',
                'bottle with popping cork'
            ),
            179 => array(
                'U+1F490',
                'bouquet'
            ),
            180 => array(
                'U+1F3F9',
                'bow and arrow'
            ),
            181 => array(
                'U+1F963',
                'bowl with spoon'
            ),
            182 => array(
                'U+1F3B3',
                'bowling'
            ),
            183 => array(
                'U+1F94A',
                'boxing glove'
            ),
            184 => array(
                'U+1F466',
                'boy'
            ),
            185 => array(
                'U+1F9E0',
                'brain'
            ),
            186 => array(
                'U+1F35E',
                'bread'
            ),
            187 => array(
                'U+1F931',
                'breast-feeding'
            ),
            188 => array(
                'U+1F470',
                'bride with veil'
            ),
            189 => array(
                'U+1F309',
                'bridge at night'
            ),
            190 => array(
                'U+1F4BC',
                'briefcase'
            ),
            191 => array(
                'U+1F506',
                'bright button'
            ),
            192 => array(
                'U+1F966',
                'broccoli'
            ),
            193 => array(
                'U+1F494',
                'broken heart'
            ),
            194 => array(
                'U+1F41B',
                'bug'
            ),
            195 => array(
                'U+1F3D7',
                'building construction'
            ),
            196 => array(
                'U+1F685',
                'bullet train'
            ),
            197 => array(
                'U+1F32F',
                'burrito'
            ),
            198 => array(
                'U+1F68C',
                'bus'
            ),
            199 => array(
                'U+1F68F',
                'bus stop'
            ),
            200 => array(
                'U+1F464',
                'bust in silhouette'
            ),
            201 => array(
                'U+1F465',
                'busts in silhouette'
            ),
            202 => array(
                'U+1F98B',
                'butterfly'
            ),
            203 => array(
                'U+1F335',
                'cactus'
            ),
            204 => array(
                'U+1F4C5',
                'calendar'
            ),
            205 => array(
                'U+1F919',
                'call me hand'
            ),
            206 => array(
                'U+1F42A',
                'camel'
            ),
            207 => array(
                'U+1F4F7',
                'camera'
            ),
            208 => array(
                'U+1F4F8',
                'camera with flash'
            ),
            209 => array(
                'U+1F3D5',
                'camping'
            ),
            210 => array(
                'U+264B',
                'Cancer'
            ),
            211 => array(
                'U+1F56F',
                'candle'
            ),
            212 => array(
                'U+1F36C',
                'candy'
            ),
            213 => array(
                'U+1F96B',
                'canned food'
            ),
            214 => array(
                'U+1F6F6',
                'canoe'
            ),
            215 => array(
                'U+2651',
                'Capricorn'
            ),
            216 => array(
                'U+1F5C3',
                'card file box'
            ),
            217 => array(
                'U+1F4C7',
                'card index'
            ),
            218 => array(
                'U+1F5C2',
                'card index dividers'
            ),
            219 => array(
                'U+1F3A0',
                'carousel horse'
            ),
            220 => array(
                'U+1F38F',
                'carp streamer'
            ),
            221 => array(
                'U+1F955',
                'carrot'
            ),
            222 => array(
                'U+1F3F0',
                'castle'
            ),
            223 => array(
                'U+1F408',
                'cat'
            ),
            224 => array(
                'U+1F431',
                'cat face'
            ),
            225 => array(
                'U+1F639',
                'cat with tears of joy'
            ),
            226 => array(
                'U+1F63C',
                'cat with wry smile'
            ),
            227 => array(
                'U+26D3',
                'chains'
            ),
            228 => array(
                'U+1F4C9',
                'chart decreasing'
            ),
            229 => array(
                'U+1F4C8',
                'chart increasing'
            ),
            230 => array(
                'U+1F4B9',
                'chart increasing with yen'
            ),
            231 => array(
                'U+2611',
                'check box with check'
            ),
            232 => array(
                'U+2714',
                'check mark'
            ),
            233 => array(
                'U+2705',
                'check mark button'
            ),
            234 => array(
                'U+1F9C0',
                'cheese wedge'
            ),
            235 => array(
                'U+1F3C1',
                'chequered flag'
            ),
            236 => array(
                'U+1F352',
                'cherries'
            ),
            237 => array(
                'U+1F338',
                'cherry blossom'
            ),
            238 => array(
                'U+1F330',
                'chestnut'
            ),
            239 => array(
                'U+1F414',
                'chicken'
            ),
            240 => array(
                'U+1F9D2',
                'child'
            ),
            241 => array(
                'U+1F6B8',
                'children crossing'
            ),
            242 => array(
                'U+1F43F',
                'chipmunk'
            ),
            243 => array(
                'U+1F36B',
                'chocolate bar'
            ),
            244 => array(
                'U+1F962',
                'chopsticks'
            ),
            245 => array(
                'U+1F384',
                'Christmas tree'
            ),
            246 => array(
                'U+26EA',
                'church'
            ),
            247 => array(
                'U+1F6AC',
                'cigarette'
            ),
            248 => array(
                'U+1F3A6',
                'cinema'
            ),
            249 => array(
                'U+24C2',
                'circled M'
            ),
            250 => array(
                'U+1F3AA',
                'circus tent'
            ),
            251 => array(
                'U+1F3D9',
                'cityscape'
            ),
            252 => array(
                'U+1F306',
                'cityscape at dusk'
            ),
            253 => array(
                'U+1F191',
                'CL button'
            ),
            254 => array(
                'U+1F5DC',
                'clamp'
            ),
            255 => array(
                'U+1F3AC',
                'clapper board'
            ),
            256 => array(
                'U+1F44F',
                'clapping hands'
            ),
            257 => array(
                'U+1F3DB',
                'classical building'
            ),
            258 => array(
                'U+1F37B',
                'clinking beer mugs'
            ),
            259 => array(
                'U+1F942',
                'clinking glasses'
            ),
            260 => array(
                'U+1F4CB',
                'clipboard'
            ),
            261 => array(
                'U+1F503',
                'clockwise vertical arrows'
            ),
            262 => array(
                'U+1F4D5',
                'closed book'
            ),
            263 => array(
                'U+1F4EA',
                'closed mailbox with lowered flag'
            ),
            264 => array(
                'U+1F4EB',
                'closed mailbox with raised flag'
            ),
            265 => array(
                'U+1F302',
                'closed umbrella'
            ),
            266 => array(
                'U+2601',
                'cloud'
            ),
            267 => array(
                'U+1F329',
                'cloud with lightning'
            ),
            268 => array(
                'U+26C8',
                'cloud with lightning and rain'
            ),
            269 => array(
                'U+1F327',
                'cloud with rain'
            ),
            270 => array(
                'U+1F328',
                'cloud with snow'
            ),
            271 => array(
                'U+1F921',
                'clown face'
            ),
            272 => array(
                'U+2663',
                'club suit'
            ),
            273 => array(
                'U+1F45D',
                'clutch bag'
            ),
            274 => array(
                'U+1F9E5',
                'coat'
            ),
            275 => array(
                'U+1F378',
                'cocktail glass'
            ),
            276 => array(
                'U+1F965',
                'coconut'
            ),
            277 => array(
                'U+26B0',
                'coffin'
            ),
            278 => array(
                'U+1F4A5',
                'collision'
            ),
            279 => array(
                'U+2604',
                'comet'
            ),
            280 => array(
                'U+1F4BD',
                'computer disk'
            ),
            281 => array(
                'U+1F5B1',
                'computer mouse'
            ),
            282 => array(
                'U+1F38A',
                'confetti ball'
            ),
            283 => array(
                'U+1F616',
                'confounded face'
            ),
            284 => array(
                'U+1F615',
                'confused face'
            ),
            285 => array(
                'U+1F6A7',
                'construction'
            ),
            286 => array(
                'U+1F477',
                'construction worker'
            ),
            287 => array(
                'U+1F39B',
                'control knobs'
            ),
            288 => array(
                'U+1F3EA',
                'convenience store'
            ),
            289 => array(
                'U+1F35A',
                'cooked rice'
            ),
            290 => array(
                'U+1F36A',
                'cookie'
            ),
            291 => array(
                'U+1F373',
                'cooking'
            ),
            292 => array(
                'U+1F192',
                'COOL button'
            ),
            293 => array(
                'U+00A9',
                'copyright'
            ),
            294 => array(
                'U+1F6CB',
                'couch and lamp'
            ),
            295 => array(
                'U+1F504',
                'counterclockwise arrows button'
            ),
            296 => array(
                'U+1F491',
                'couple with heart'
            ),
            297 => array(
                'U+1F468 U+200D U+2764 U+FE0F U+200D U+1F468',
                'couple with heart: man, man'
            ),
            298 => array(
                'U+1F469 U+200D U+2764 U+FE0F U+200D U+1F468',
                'couple with heart: woman, man'
            ),
            299 => array(
                'U+1F469 U+200D U+2764 U+FE0F U+200D U+1F469',
                'couple with heart: woman, woman'
            ),
            300 => array(
                'U+1F404',
                'cow'
            ),
            301 => array(
                'U+1F42E',
                'cow face'
            ),
            302 => array(
                'U+1F920',
                'cowboy hat face'
            ),
            303 => array(
                'U+1F980',
                'crab'
            ),
            304 => array(
                'U+1F58D',
                'crayon'
            ),
            305 => array(
                'U+1F4B3',
                'credit card'
            ),
            306 => array(
                'U+1F319',
                'crescent moon'
            ),
            307 => array(
                'U+1F997',
                'cricket'
            ),
            308 => array(
                'U+1F3CF',
                'cricket game'
            ),
            309 => array(
                'U+1F40A',
                'crocodile'
            ),
            310 => array(
                'U+1F950',
                'croissant'
            ),
            311 => array(
                'U+274C',
                'cross mark'
            ),
            312 => array(
                'U+274E',
                'cross mark button'
            ),
            313 => array(
                'U+1F91E',
                'crossed fingers'
            ),
            314 => array(
                'U+1F38C',
                'crossed flags'
            ),
            315 => array(
                'U+2694',
                'crossed swords'
            ),
            316 => array(
                'U+1F451',
                'crown'
            ),
            317 => array(
                'U+1F63F',
                'crying cat'
            ),
            318 => array(
                'U+1F622',
                'crying face'
            ),
            319 => array(
                'U+1F52E',
                'crystal ball'
            ),
            320 => array(
                'U+1F952',
                'cucumber'
            ),
            321 => array(
                'U+1F964',
                'cup with straw'
            ),
            322 => array(
                'U+1F94C',
                'curling stone'
            ),
            323 => array(
                'U+27B0',
                'curly loop'
            ),
            324 => array(
                'U+1F4B1',
                'currency exchange'
            ),
            325 => array(
                'U+1F35B',
                'curry rice'
            ),
            326 => array(
                'U+1F36E',
                'custard'
            ),
            327 => array(
                'U+1F6C3',
                'customs'
            ),
            328 => array(
                'U+1F969',
                'cut of meat'
            ),
            329 => array(
                'U+1F300',
                'cyclone'
            ),
            330 => array(
                'U+1F5E1',
                'dagger'
            ),
            331 => array(
                'U+1F361',
                'dango'
            ),
            332 => array(
                'U+1F4A8',
                'dashing away'
            ),
            333 => array(
                'U+1F333',
                'deciduous tree'
            ),
            334 => array(
                'U+1F98C',
                'deer'
            ),
            335 => array(
                'U+1F69A',
                'delivery truck'
            ),
            336 => array(
                'U+1F3EC',
                'department store'
            ),
            337 => array(
                'U+1F3DA',
                'derelict house'
            ),
            338 => array(
                'U+1F3DC',
                'desert'
            ),
            339 => array(
                'U+1F3DD',
                'desert island'
            ),
            340 => array(
                'U+1F5A5',
                'desktop computer'
            ),
            341 => array(
                'U+1F575',
                'detective'
            ),
            342 => array(
                'U+2666',
                'diamond suit'
            ),
            343 => array(
                'U+1F4A0',
                'diamond with a dot'
            ),
            344 => array(
                'U+1F505',
                'dim button'
            ),
            345 => array(
                'U+1F3AF',
                'direct hit'
            ),
            346 => array(
                'U+1F61E',
                'disappointed face'
            ),
            347 => array(
                'U+2797',
                'division sign'
            ),
            348 => array(
                'U+1F4AB',
                'dizzy'
            ),
            349 => array(
                'U+1F635',
                'dizzy face'
            ),
            350 => array(
                'U+1F415',
                'dog'
            ),
            351 => array(
                'U+1F436',
                'dog face'
            ),
            352 => array(
                'U+1F4B5',
                'dollar banknote'
            ),
            353 => array(
                'U+1F42C',
                'dolphin'
            ),
            354 => array(
                'U+1F6AA',
                'door'
            ),
            355 => array(
                'U+1F52F',
                'dotted six-pointed star'
            ),
            356 => array(
                'U+27BF',
                'double curly loop'
            ),
            357 => array(
                'U+203C',
                'double exclamation mark'
            ),
            358 => array(
                'U+1F369',
                'doughnut'
            ),
            359 => array(
                'U+1F54A',
                'dove'
            ),
            360 => array(
                'U+2B07',
                'down arrow'
            ),
            361 => array(
                'U+2199',
                'down-left arrow'
            ),
            362 => array(
                'U+2198',
                'down-right arrow'
            ),
            363 => array(
                'U+1F613',
                'downcast face with sweat'
            ),
            364 => array(
                'U+1F53D',
                'downwards button'
            ),
            365 => array(
                'U+1F409',
                'dragon'
            ),
            366 => array(
                'U+1F432',
                'dragon face'
            ),
            367 => array(
                'U+1F457',
                'dress'
            ),
            368 => array(
                'U+1F924',
                'drooling face'
            ),
            369 => array(
                'U+1F4A7',
                'droplet'
            ),
            370 => array(
                'U+1F941',
                'drum'
            ),
            371 => array(
                'U+1F986',
                'duck'
            ),
            372 => array(
                'U+1F95F',
                'dumpling'
            ),
            373 => array(
                'U+1F4C0',
                'dvd'
            ),
            374 => array(
                'U+1F4E7',
                'e-mail'
            ),
            375 => array(
                'U+1F985',
                'eagle'
            ),
            376 => array(
                'U+1F442',
                'ear'
            ),
            377 => array(
                'U+1F33D',
                'ear of corn'
            ),
            378 => array(
                'U+1F95A',
                'egg'
            ),
            379 => array(
                'U+1F346',
                'eggplant'
            ),
            380 => array(
                'U+1F557',
                'eight o’clock'
            ),
            381 => array(
                'U+2734',
                'eight-pointed star'
            ),
            382 => array(
                'U+2733',
                'eight-spoked asterisk'
            ),
            383 => array(
                'U+1F563',
                'eight-thirty'
            ),
            384 => array(
                'U+23CF',
                'eject button'
            ),
            385 => array(
                'U+1F50C',
                'electric plug'
            ),
            386 => array(
                'U+1F418',
                'elephant'
            ),
            387 => array(
                'U+1F55A',
                'eleven o’clock'
            ),
            388 => array(
                'U+1F566',
                'eleven-thirty'
            ),
            389 => array(
                'U+1F9DD',
                'elf'
            ),
            390 => array(
                'U+1F51A',
                'END arrow'
            ),
            391 => array(
                'U+2709',
                'envelope'
            ),
            392 => array(
                'U+1F4E9',
                'envelope with arrow'
            ),
            393 => array(
                'U+1F4B6',
                'euro banknote'
            ),
            394 => array(
                'U+1F332',
                'evergreen tree'
            ),
            395 => array(
                'U+1F411',
                'ewe'
            ),
            396 => array(
                'U+2757',
                'exclamation mark'
            ),
            397 => array(
                'U+2049',
                'exclamation question mark'
            ),
            398 => array(
                'U+1F92F',
                'exploding head'
            ),
            399 => array(
                'U+1F611',
                'expressionless face'
            ),
            400 => array(
                'U+1F441',
                'eye'
            ),
            401 => array(
                'U+1F441 U+FE0F U+200D U+1F5E8 U+FE0F',
                'eye in speech bubble'
            ),
            402 => array(
                'U+1F440',
                'eyes'
            ),
            403 => array(
                'U+1F618',
                'face blowing a kiss'
            ),
            404 => array(
                'U+1F60B',
                'face savoring food'
            ),
            405 => array(
                'U+1F631',
                'face screaming in fear'
            ),
            406 => array(
                'U+1F92E',
                'face vomiting'
            ),
            407 => array(
                'U+1F92D',
                'face with hand over mouth'
            ),
            408 => array(
                'U+1F915',
                'face with head-bandage'
            ),
            409 => array(
                'U+1F637',
                'face with medical mask'
            ),
            410 => array(
                'U+1F9D0',
                'face with monocle'
            ),
            411 => array(
                'U+1F62E',
                'face with open mouth'
            ),
            412 => array(
                'U+1F928',
                'face with raised eyebrow'
            ),
            413 => array(
                'U+1F644',
                'face with rolling eyes'
            ),
            414 => array(
                'U+1F624',
                'face with steam from nose'
            ),
            415 => array(
                'U+1F92C',
                'face with symbols on mouth'
            ),
            416 => array(
                'U+1F602',
                'face with tears of joy'
            ),
            417 => array(
                'U+1F912',
                'face with thermometer'
            ),
            418 => array(
                'U+1F61B',
                'face with tongue'
            ),
            419 => array(
                'U+1F636',
                'face without mouth'
            ),
            420 => array(
                'U+1F3ED',
                'factory'
            ),
            421 => array(
                'U+1F9DA',
                'fairy'
            ),
            422 => array(
                'U+1F342',
                'fallen leaf'
            ),
            423 => array(
                'U+1F46A',
                'family'
            ),
            424 => array(
                'U+1F468 U+200D U+1F466',
                'family: man, boy'
            ),
            425 => array(
                'U+1F468 U+200D U+1F466 U+200D U+1F466',
                'family: man, boy, boy'
            ),
            426 => array(
                'U+1F468 U+200D U+1F467',
                'family: man, girl'
            ),
            427 => array(
                'U+1F468 U+200D U+1F467 U+200D U+1F466',
                'family: man, girl, boy'
            ),
            428 => array(
                'U+1F468 U+200D U+1F467 U+200D U+1F467',
                'family: man, girl, girl'
            ),
            429 => array(
                'U+1F468 U+200D U+1F468 U+200D U+1F466',
                'family: man, man, boy'
            ),
            430 => array(
                'U+1F468 U+200D U+1F468 U+200D U+1F466 U+200D U+1F466',
                'family: man, man, boy, boy'
            ),
            431 => array(
                'U+1F468 U+200D U+1F468 U+200D U+1F467',
                'family: man, man, girl'
            ),
            432 => array(
                'U+1F468 U+200D U+1F468 U+200D U+1F467 U+200D U+1F466',
                'family: man, man, girl, boy'
            ),
            433 => array(
                'U+1F468 U+200D U+1F468 U+200D U+1F467 U+200D U+1F467',
                'family: man, man, girl, girl'
            ),
            434 => array(
                'U+1F468 U+200D U+1F469 U+200D U+1F466',
                'family: man, woman, boy'
            ),
            435 => array(
                'U+1F468 U+200D U+1F469 U+200D U+1F466 U+200D U+1F466',
                'family: man, woman, boy, boy'
            ),
            436 => array(
                'U+1F468 U+200D U+1F469 U+200D U+1F467',
                'family: man, woman, girl'
            ),
            437 => array(
                'U+1F468 U+200D U+1F469 U+200D U+1F467 U+200D U+1F466',
                'family: man, woman, girl, boy'
            ),
            438 => array(
                'U+1F468 U+200D U+1F469 U+200D U+1F467 U+200D U+1F467',
                'family: man, woman, girl, girl'
            ),
            439 => array(
                'U+1F469 U+200D U+1F466',
                'family: woman, boy'
            ),
            440 => array(
                'U+1F469 U+200D U+1F466 U+200D U+1F466',
                'family: woman, boy, boy'
            ),
            441 => array(
                'U+1F469 U+200D U+1F467',
                'family: woman, girl'
            ),
            442 => array(
                'U+1F469 U+200D U+1F467 U+200D U+1F466',
                'family: woman, girl, boy'
            ),
            443 => array(
                'U+1F469 U+200D U+1F467 U+200D U+1F467',
                'family: woman, girl, girl'
            ),
            444 => array(
                'U+1F469 U+200D U+1F469 U+200D U+1F466',
                'family: woman, woman, boy'
            ),
            445 => array(
                'U+1F469 U+200D U+1F469 U+200D U+1F466 U+200D U+1F466',
                'family: woman, woman, boy, boy'
            ),
            446 => array(
                'U+1F469 U+200D U+1F469 U+200D U+1F467',
                'family: woman, woman, girl'
            ),
            447 => array(
                'U+1F469 U+200D U+1F469 U+200D U+1F467 U+200D U+1F466',
                'family: woman, woman, girl, boy'
            ),
            448 => array(
                'U+1F469 U+200D U+1F469 U+200D U+1F467 U+200D U+1F467',
                'family: woman, woman, girl, girl'
            ),
            449 => array(
                'U+23EC',
                'fast down button'
            ),
            450 => array(
                'U+23EA',
                'fast reverse button'
            ),
            451 => array(
                'U+23EB',
                'fast up button'
            ),
            452 => array(
                'U+23E9',
                'fast-forward button'
            ),
            453 => array(
                'U+1F4E0',
                'fax machine'
            ),
            454 => array(
                'U+1F628',
                'fearful face'
            ),
            455 => array(
                'U+2640',
                'female sign'
            ),
            456 => array(
                'U+1F3A1',
                'ferris wheel'
            ),
            457 => array(
                'U+26F4',
                'ferry'
            ),
            458 => array(
                'U+1F3D1',
                'field hockey'
            ),
            459 => array(
                'U+1F5C4',
                'file cabinet'
            ),
            460 => array(
                'U+1F4C1',
                'file folder'
            ),
            461 => array(
                'U+1F39E',
                'film frames'
            ),
            462 => array(
                'U+1F4FD',
                'film projector'
            ),
            463 => array(
                'U+1F525',
                'fire'
            ),
            464 => array(
                'U+1F692',
                'fire engine'
            ),
            465 => array(
                'U+1F386',
                'fireworks'
            ),
            466 => array(
                'U+1F313',
                'first quarter moon'
            ),
            467 => array(
                'U+1F31B',
                'first quarter moon face'
            ),
            468 => array(
                'U+1F41F',
                'fish'
            ),
            469 => array(
                'U+1F365',
                'fish cake with swirl'
            ),
            470 => array(
                'U+1F3A3',
                'fishing pole'
            ),
            471 => array(
                'U+1F554',
                'five o’clock'
            ),
            472 => array(
                'U+1F560',
                'five-thirty'
            ),
            473 => array(
                'U+26F3',
                'flag in hole'
            ),
            474 => array(
                'U+1F1E6 U+1F1EB',
                'flag: Afghanistan'
            ),
            475 => array(
                'U+1F1E6 U+1F1FD',
                'flag: Åland Islands'
            ),
            476 => array(
                'U+1F1E6 U+1F1F1',
                'flag: Albania'
            ),
            477 => array(
                'U+1F1E9 U+1F1FF',
                'flag: Algeria'
            ),
            478 => array(
                'U+1F1E6 U+1F1F8',
                'flag: American Samoa'
            ),
            479 => array(
                'U+1F1E6 U+1F1E9',
                'flag: Andorra'
            ),
            480 => array(
                'U+1F1E6 U+1F1F4',
                'flag: Angola'
            ),
            481 => array(
                'U+1F1E6 U+1F1EE',
                'flag: Anguilla'
            ),
            482 => array(
                'U+1F1E6 U+1F1F6',
                'flag: Antarctica'
            ),
            483 => array(
                'U+1F1E6 U+1F1EC',
                'flag: Antigua & Barbuda'
            ),
            484 => array(
                'U+1F1E6 U+1F1F7',
                'flag: Argentina'
            ),
            485 => array(
                'U+1F1E6 U+1F1F2',
                'flag: Armenia'
            ),
            486 => array(
                'U+1F1E6 U+1F1FC',
                'flag: Aruba'
            ),
            487 => array(
                'U+1F1E6 U+1F1E8',
                'flag: Ascension Island'
            ),
            488 => array(
                'U+1F1E6 U+1F1FA',
                'flag: Australia'
            ),
            489 => array(
                'U+1F1E6 U+1F1F9',
                'flag: Austria'
            ),
            490 => array(
                'U+1F1E6 U+1F1FF',
                'flag: Azerbaijan'
            ),
            491 => array(
                'U+1F1E7 U+1F1F8',
                'flag: Bahamas'
            ),
            492 => array(
                'U+1F1E7 U+1F1ED',
                'flag: Bahrain'
            ),
            493 => array(
                'U+1F1E7 U+1F1E9',
                'flag: Bangladesh'
            ),
            494 => array(
                'U+1F1E7 U+1F1E7',
                'flag: Barbados'
            ),
            495 => array(
                'U+1F1E7 U+1F1FE',
                'flag: Belarus'
            ),
            496 => array(
                'U+1F1E7 U+1F1EA',
                'flag: Belgium'
            ),
            497 => array(
                'U+1F1E7 U+1F1FF',
                'flag: Belize'
            ),
            498 => array(
                'U+1F1E7 U+1F1EF',
                'flag: Benin'
            ),
            499 => array(
                'U+1F1E7 U+1F1F2',
                'flag: Bermuda'
            ),
            500 => array(
                'U+1F1E7 U+1F1F9',
                'flag: Bhutan'
            ),
            501 => array(
                'U+1F1E7 U+1F1F4',
                'flag: Bolivia'
            ),
            502 => array(
                'U+1F1E7 U+1F1E6',
                'flag: Bosnia & Herzegovina'
            ),
            503 => array(
                'U+1F1E7 U+1F1FC',
                'flag: Botswana'
            ),
            504 => array(
                'U+1F1E7 U+1F1FB',
                'flag: Bouvet Island'
            ),
            505 => array(
                'U+1F1E7 U+1F1F7',
                'flag: Brazil'
            ),
            506 => array(
                'U+1F1EE U+1F1F4',
                'flag: British Indian Ocean Territory'
            ),
            507 => array(
                'U+1F1FB U+1F1EC',
                'flag: British Virgin Islands'
            ),
            508 => array(
                'U+1F1E7 U+1F1F3',
                'flag: Brunei'
            ),
            509 => array(
                'U+1F1E7 U+1F1EC',
                'flag: Bulgaria'
            ),
            510 => array(
                'U+1F1E7 U+1F1EB',
                'flag: Burkina Faso'
            ),
            511 => array(
                'U+1F1E7 U+1F1EE',
                'flag: Burundi'
            ),
            512 => array(
                'U+1F1F0 U+1F1ED',
                'flag: Cambodia'
            ),
            513 => array(
                'U+1F1E8 U+1F1F2',
                'flag: Cameroon'
            ),
            514 => array(
                'U+1F1E8 U+1F1E6',
                'flag: Canada'
            ),
            515 => array(
                'U+1F1EE U+1F1E8',
                'flag: Canary Islands'
            ),
            516 => array(
                'U+1F1E8 U+1F1FB',
                'flag: Cape Verde'
            ),
            517 => array(
                'U+1F1E7 U+1F1F6',
                'flag: Caribbean Netherlands'
            ),
            518 => array(
                'U+1F1F0 U+1F1FE',
                'flag: Cayman Islands'
            ),
            519 => array(
                'U+1F1E8 U+1F1EB',
                'flag: Central African Republic'
            ),
            520 => array(
                'U+1F1EA U+1F1E6',
                'flag: Ceuta & Melilla'
            ),
            521 => array(
                'U+1F1F9 U+1F1E9',
                'flag: Chad'
            ),
            522 => array(
                'U+1F1E8 U+1F1F1',
                'flag: Chile'
            ),
            523 => array(
                'U+1F1E8 U+1F1F3',
                'flag: China'
            ),
            524 => array(
                'U+1F1E8 U+1F1FD',
                'flag: Christmas Island'
            ),
            525 => array(
                'U+1F1E8 U+1F1F5',
                'flag: Clipperton Island'
            ),
            526 => array(
                'U+1F1E8 U+1F1E8',
                'flag: Cocos (Keeling) Islands'
            ),
            527 => array(
                'U+1F1E8 U+1F1F4',
                'flag: Colombia'
            ),
            528 => array(
                'U+1F1F0 U+1F1F2',
                'flag: Comoros'
            ),
            529 => array(
                'U+1F1E8 U+1F1EC',
                'flag: Congo - Brazzaville'
            ),
            530 => array(
                'U+1F1E8 U+1F1E9',
                'flag: Congo - Kinshasa'
            ),
            531 => array(
                'U+1F1E8 U+1F1F0',
                'flag: Cook Islands'
            ),
            532 => array(
                'U+1F1E8 U+1F1F7',
                'flag: Costa Rica'
            ),
            533 => array(
                'U+1F1E8 U+1F1EE',
                'flag: Côte d’Ivoire'
            ),
            534 => array(
                'U+1F1ED U+1F1F7',
                'flag: Croatia'
            ),
            535 => array(
                'U+1F1E8 U+1F1FA',
                'flag: Cuba'
            ),
            536 => array(
                'U+1F1E8 U+1F1FC',
                'flag: Curaçao'
            ),
            537 => array(
                'U+1F1E8 U+1F1FE',
                'flag: Cyprus'
            ),
            538 => array(
                'U+1F1E8 U+1F1FF',
                'flag: Czechia'
            ),
            539 => array(
                'U+1F1E9 U+1F1F0',
                'flag: Denmark'
            ),
            540 => array(
                'U+1F1E9 U+1F1EC',
                'flag: Diego Garcia'
            ),
            541 => array(
                'U+1F1E9 U+1F1EF',
                'flag: Djibouti'
            ),
            542 => array(
                'U+1F1E9 U+1F1F2',
                'flag: Dominica'
            ),
            543 => array(
                'U+1F1E9 U+1F1F4',
                'flag: Dominican Republic'
            ),
            544 => array(
                'U+1F1EA U+1F1E8',
                'flag: Ecuador'
            ),
            545 => array(
                'U+1F1EA U+1F1EC',
                'flag: Egypt'
            ),
            546 => array(
                'U+1F1F8 U+1F1FB',
                'flag: El Salvador'
            ),
            547 => array(
                'U+1F3F4 U+E0067 U+E0062 U+E0065 U+E006E U+E0067 U+E007F',
                'flag: England'
            ),
            548 => array(
                'U+1F1EC U+1F1F6',
                'flag: Equatorial Guinea'
            ),
            549 => array(
                'U+1F1EA U+1F1F7',
                'flag: Eritrea'
            ),
            550 => array(
                'U+1F1EA U+1F1EA',
                'flag: Estonia'
            ),
            551 => array(
                'U+1F1F8 U+1F1FF',
                'flag: Eswatini'
            ),
            552 => array(
                'U+1F1EA U+1F1F9',
                'flag: Ethiopia'
            ),
            553 => array(
                'U+1F1EA U+1F1FA',
                'flag: European Union'
            ),
            554 => array(
                'U+1F1EB U+1F1F0',
                'flag: Falkland Islands'
            ),
            555 => array(
                'U+1F1EB U+1F1F4',
                'flag: Faroe Islands'
            ),
            556 => array(
                'U+1F1EB U+1F1EF',
                'flag: Fiji'
            ),
            557 => array(
                'U+1F1EB U+1F1EE',
                'flag: Finland'
            ),
            558 => array(
                'U+1F1EB U+1F1F7',
                'flag: France'
            ),
            559 => array(
                'U+1F1EC U+1F1EB',
                'flag: French Guiana'
            ),
            560 => array(
                'U+1F1F5 U+1F1EB',
                'flag: French Polynesia'
            ),
            561 => array(
                'U+1F1F9 U+1F1EB',
                'flag: French Southern Territories'
            ),
            562 => array(
                'U+1F1EC U+1F1E6',
                'flag: Gabon'
            ),
            563 => array(
                'U+1F1EC U+1F1F2',
                'flag: Gambia'
            ),
            564 => array(
                'U+1F1EC U+1F1EA',
                'flag: Georgia'
            ),
            565 => array(
                'U+1F1E9 U+1F1EA',
                'flag: Germany'
            ),
            566 => array(
                'U+1F1EC U+1F1ED',
                'flag: Ghana'
            ),
            567 => array(
                'U+1F1EC U+1F1EE',
                'flag: Gibraltar'
            ),
            568 => array(
                'U+1F1EC U+1F1F7',
                'flag: Greece'
            ),
            569 => array(
                'U+1F1EC U+1F1F1',
                'flag: Greenland'
            ),
            570 => array(
                'U+1F1EC U+1F1E9',
                'flag: Grenada'
            ),
            571 => array(
                'U+1F1EC U+1F1F5',
                'flag: Guadeloupe'
            ),
            572 => array(
                'U+1F1EC U+1F1FA',
                'flag: Guam'
            ),
            573 => array(
                'U+1F1EC U+1F1F9',
                'flag: Guatemala'
            ),
            574 => array(
                'U+1F1EC U+1F1EC',
                'flag: Guernsey'
            ),
            575 => array(
                'U+1F1EC U+1F1F3',
                'flag: Guinea'
            ),
            576 => array(
                'U+1F1EC U+1F1FC',
                'flag: Guinea-Bissau'
            ),
            577 => array(
                'U+1F1EC U+1F1FE',
                'flag: Guyana'
            ),
            578 => array(
                'U+1F1ED U+1F1F9',
                'flag: Haiti'
            ),
            579 => array(
                'U+1F1ED U+1F1F2',
                'flag: Heard & McDonald Islands'
            ),
            580 => array(
                'U+1F1ED U+1F1F3',
                'flag: Honduras'
            ),
            581 => array(
                'U+1F1ED U+1F1F0',
                'flag: Hong Kong SAR China'
            ),
            582 => array(
                'U+1F1ED U+1F1FA',
                'flag: Hungary'
            ),
            583 => array(
                'U+1F1EE U+1F1F8',
                'flag: Iceland'
            ),
            584 => array(
                'U+1F1EE U+1F1F3',
                'flag: India'
            ),
            585 => array(
                'U+1F1EE U+1F1E9',
                'flag: Indonesia'
            ),
            586 => array(
                'U+1F1EE U+1F1F7',
                'flag: Iran'
            ),
            587 => array(
                'U+1F1EE U+1F1F6',
                'flag: Iraq'
            ),
            588 => array(
                'U+1F1EE U+1F1EA',
                'flag: Ireland'
            ),
            589 => array(
                'U+1F1EE U+1F1F2',
                'flag: Isle of Man'
            ),
            590 => array(
                'U+1F1EE U+1F1F1',
                'flag: Israel'
            ),
            591 => array(
                'U+1F1EE U+1F1F9',
                'flag: Italy'
            ),
            592 => array(
                'U+1F1EF U+1F1F2',
                'flag: Jamaica'
            ),
            593 => array(
                'U+1F1EF U+1F1F5',
                'flag: Japan'
            ),
            594 => array(
                'U+1F1EF U+1F1EA',
                'flag: Jersey'
            ),
            595 => array(
                'U+1F1EF U+1F1F4',
                'flag: Jordan'
            ),
            596 => array(
                'U+1F1F0 U+1F1FF',
                'flag: Kazakhstan'
            ),
            597 => array(
                'U+1F1F0 U+1F1EA',
                'flag: Kenya'
            ),
            598 => array(
                'U+1F1F0 U+1F1EE',
                'flag: Kiribati'
            ),
            599 => array(
                'U+1F1FD U+1F1F0',
                'flag: Kosovo'
            ),
            600 => array(
                'U+1F1F0 U+1F1FC',
                'flag: Kuwait'
            ),
            601 => array(
                'U+1F1F0 U+1F1EC',
                'flag: Kyrgyzstan'
            ),
            602 => array(
                'U+1F1F1 U+1F1E6',
                'flag: Laos'
            ),
            603 => array(
                'U+1F1F1 U+1F1FB',
                'flag: Latvia'
            ),
            604 => array(
                'U+1F1F1 U+1F1E7',
                'flag: Lebanon'
            ),
            605 => array(
                'U+1F1F1 U+1F1F8',
                'flag: Lesotho'
            ),
            606 => array(
                'U+1F1F1 U+1F1F7',
                'flag: Liberia'
            ),
            607 => array(
                'U+1F1F1 U+1F1FE',
                'flag: Libya'
            ),
            608 => array(
                'U+1F1F1 U+1F1EE',
                'flag: Liechtenstein'
            ),
            609 => array(
                'U+1F1F1 U+1F1F9',
                'flag: Lithuania'
            ),
            610 => array(
                'U+1F1F1 U+1F1FA',
                'flag: Luxembourg'
            ),
            611 => array(
                'U+1F1F2 U+1F1F4',
                'flag: Macao SAR China'
            ),
            612 => array(
                'U+1F1F2 U+1F1F0',
                'flag: Macedonia'
            ),
            613 => array(
                'U+1F1F2 U+1F1EC',
                'flag: Madagascar'
            ),
            614 => array(
                'U+1F1F2 U+1F1FC',
                'flag: Malawi'
            ),
            615 => array(
                'U+1F1F2 U+1F1FE',
                'flag: Malaysia'
            ),
            616 => array(
                'U+1F1F2 U+1F1FB',
                'flag: Maldives'
            ),
            617 => array(
                'U+1F1F2 U+1F1F1',
                'flag: Mali'
            ),
            618 => array(
                'U+1F1F2 U+1F1F9',
                'flag: Malta'
            ),
            619 => array(
                'U+1F1F2 U+1F1ED',
                'flag: Marshall Islands'
            ),
            620 => array(
                'U+1F1F2 U+1F1F6',
                'flag: Martinique'
            ),
            621 => array(
                'U+1F1F2 U+1F1F7',
                'flag: Mauritania'
            ),
            622 => array(
                'U+1F1F2 U+1F1FA',
                'flag: Mauritius'
            ),
            623 => array(
                'U+1F1FE U+1F1F9',
                'flag: Mayotte'
            ),
            624 => array(
                'U+1F1F2 U+1F1FD',
                'flag: Mexico'
            ),
            625 => array(
                'U+1F1EB U+1F1F2',
                'flag: Micronesia'
            ),
            626 => array(
                'U+1F1F2 U+1F1E9',
                'flag: Moldova'
            ),
            627 => array(
                'U+1F1F2 U+1F1E8',
                'flag: Monaco'
            ),
            628 => array(
                'U+1F1F2 U+1F1F3',
                'flag: Mongolia'
            ),
            629 => array(
                'U+1F1F2 U+1F1EA',
                'flag: Montenegro'
            ),
            630 => array(
                'U+1F1F2 U+1F1F8',
                'flag: Montserrat'
            ),
            631 => array(
                'U+1F1F2 U+1F1E6',
                'flag: Morocco'
            ),
            632 => array(
                'U+1F1F2 U+1F1FF',
                'flag: Mozambique'
            ),
            633 => array(
                'U+1F1F2 U+1F1F2',
                'flag: Myanmar (Burma)'
            ),
            634 => array(
                'U+1F1F3 U+1F1E6',
                'flag: Namibia'
            ),
            635 => array(
                'U+1F1F3 U+1F1F7',
                'flag: Nauru'
            ),
            636 => array(
                'U+1F1F3 U+1F1F5',
                'flag: Nepal'
            ),
            637 => array(
                'U+1F1F3 U+1F1F1',
                'flag: Netherlands'
            ),
            638 => array(
                'U+1F1F3 U+1F1E8',
                'flag: New Caledonia'
            ),
            639 => array(
                'U+1F1F3 U+1F1FF',
                'flag: New Zealand'
            ),
            640 => array(
                'U+1F1F3 U+1F1EE',
                'flag: Nicaragua'
            ),
            641 => array(
                'U+1F1F3 U+1F1EA',
                'flag: Niger'
            ),
            642 => array(
                'U+1F1F3 U+1F1EC',
                'flag: Nigeria'
            ),
            643 => array(
                'U+1F1F3 U+1F1FA',
                'flag: Niue'
            ),
            644 => array(
                'U+1F1F3 U+1F1EB',
                'flag: Norfolk Island'
            ),
            645 => array(
                'U+1F1F0 U+1F1F5',
                'flag: North Korea'
            ),
            646 => array(
                'U+1F1F2 U+1F1F5',
                'flag: Northern Mariana Islands'
            ),
            647 => array(
                'U+1F1F3 U+1F1F4',
                'flag: Norway'
            ),
            648 => array(
                'U+1F1F4 U+1F1F2',
                'flag: Oman'
            ),
            649 => array(
                'U+1F1F5 U+1F1F0',
                'flag: Pakistan'
            ),
            650 => array(
                'U+1F1F5 U+1F1FC',
                'flag: Palau'
            ),
            651 => array(
                'U+1F1F5 U+1F1F8',
                'flag: Palestinian Territories'
            ),
            652 => array(
                'U+1F1F5 U+1F1E6',
                'flag: Panama'
            ),
            653 => array(
                'U+1F1F5 U+1F1EC',
                'flag: Papua New Guinea'
            ),
            654 => array(
                'U+1F1F5 U+1F1FE',
                'flag: Paraguay'
            ),
            655 => array(
                'U+1F1F5 U+1F1EA',
                'flag: Peru'
            ),
            656 => array(
                'U+1F1F5 U+1F1ED',
                'flag: Philippines'
            ),
            657 => array(
                'U+1F1F5 U+1F1F3',
                'flag: Pitcairn Islands'
            ),
            658 => array(
                'U+1F1F5 U+1F1F1',
                'flag: Poland'
            ),
            659 => array(
                'U+1F1F5 U+1F1F9',
                'flag: Portugal'
            ),
            660 => array(
                'U+1F1F5 U+1F1F7',
                'flag: Puerto Rico'
            ),
            661 => array(
                'U+1F1F6 U+1F1E6',
                'flag: Qatar'
            ),
            662 => array(
                'U+1F1F7 U+1F1EA',
                'flag: Réunion'
            ),
            663 => array(
                'U+1F1F7 U+1F1F4',
                'flag: Romania'
            ),
            664 => array(
                'U+1F1F7 U+1F1FA',
                'flag: Russia'
            ),
            665 => array(
                'U+1F1F7 U+1F1FC',
                'flag: Rwanda'
            ),
            666 => array(
                'U+1F1FC U+1F1F8',
                'flag: Samoa'
            ),
            667 => array(
                'U+1F1F8 U+1F1F2',
                'flag: San Marino'
            ),
            668 => array(
                'U+1F1F8 U+1F1F9',
                'flag: São Tomé & Príncipe'
            ),
            669 => array(
                'U+1F1F8 U+1F1E6',
                'flag: Saudi Arabia'
            ),
            670 => array(
                'U+1F3F4 U+E0067 U+E0062 U+E0073 U+E0063 U+E0074 U+E007F',
                'flag: Scotland'
            ),
            671 => array(
                'U+1F1F8 U+1F1F3',
                'flag: Senegal'
            ),
            672 => array(
                'U+1F1F7 U+1F1F8',
                'flag: Serbia'
            ),
            673 => array(
                'U+1F1F8 U+1F1E8',
                'flag: Seychelles'
            ),
            674 => array(
                'U+1F1F8 U+1F1F1',
                'flag: Sierra Leone'
            ),
            675 => array(
                'U+1F1F8 U+1F1EC',
                'flag: Singapore'
            ),
            676 => array(
                'U+1F1F8 U+1F1FD',
                'flag: Sint Maarten'
            ),
            677 => array(
                'U+1F1F8 U+1F1F0',
                'flag: Slovakia'
            ),
            678 => array(
                'U+1F1F8 U+1F1EE',
                'flag: Slovenia'
            ),
            679 => array(
                'U+1F1F8 U+1F1E7',
                'flag: Solomon Islands'
            ),
            680 => array(
                'U+1F1F8 U+1F1F4',
                'flag: Somalia'
            ),
            681 => array(
                'U+1F1FF U+1F1E6',
                'flag: South Africa'
            ),
            682 => array(
                'U+1F1EC U+1F1F8',
                'flag: South Georgia & South Sandwich Islands'
            ),
            683 => array(
                'U+1F1F0 U+1F1F7',
                'flag: South Korea'
            ),
            684 => array(
                'U+1F1F8 U+1F1F8',
                'flag: South Sudan'
            ),
            685 => array(
                'U+1F1EA U+1F1F8',
                'flag: Spain'
            ),
            686 => array(
                'U+1F1F1 U+1F1F0',
                'flag: Sri Lanka'
            ),
            687 => array(
                'U+1F1E7 U+1F1F1',
                'flag: St. Barthélemy'
            ),
            688 => array(
                'U+1F1F8 U+1F1ED',
                'flag: St. Helena'
            ),
            689 => array(
                'U+1F1F0 U+1F1F3',
                'flag: St. Kitts & Nevis'
            ),
            690 => array(
                'U+1F1F1 U+1F1E8',
                'flag: St. Lucia'
            ),
            691 => array(
                'U+1F1F2 U+1F1EB',
                'flag: St. Martin'
            ),
            692 => array(
                'U+1F1F5 U+1F1F2',
                'flag: St. Pierre & Miquelon'
            ),
            693 => array(
                'U+1F1FB U+1F1E8',
                'flag: St. Vincent & Grenadines'
            ),
            694 => array(
                'U+1F1F8 U+1F1E9',
                'flag: Sudan'
            ),
            695 => array(
                'U+1F1F8 U+1F1F7',
                'flag: Suriname'
            ),
            696 => array(
                'U+1F1F8 U+1F1EF',
                'flag: Svalbard & Jan Mayen'
            ),
            697 => array(
                'U+1F1F8 U+1F1EA',
                'flag: Sweden'
            ),
            698 => array(
                'U+1F1E8 U+1F1ED',
                'flag: Switzerland'
            ),
            699 => array(
                'U+1F1F8 U+1F1FE',
                'flag: Syria'
            ),
            700 => array(
                'U+1F1F9 U+1F1FC',
                'flag: Taiwan'
            ),
            701 => array(
                'U+1F1F9 U+1F1EF',
                'flag: Tajikistan'
            ),
            702 => array(
                'U+1F1F9 U+1F1FF',
                'flag: Tanzania'
            ),
            703 => array(
                'U+1F1F9 U+1F1ED',
                'flag: Thailand'
            ),
            704 => array(
                'U+1F1F9 U+1F1F1',
                'flag: Timor-Leste'
            ),
            705 => array(
                'U+1F1F9 U+1F1EC',
                'flag: Togo'
            ),
            706 => array(
                'U+1F1F9 U+1F1F0',
                'flag: Tokelau'
            ),
            707 => array(
                'U+1F1F9 U+1F1F4',
                'flag: Tonga'
            ),
            708 => array(
                'U+1F1F9 U+1F1F9',
                'flag: Trinidad & Tobago'
            ),
            709 => array(
                'U+1F1F9 U+1F1E6',
                'flag: Tristan da Cunha'
            ),
            710 => array(
                'U+1F1F9 U+1F1F3',
                'flag: Tunisia'
            ),
            711 => array(
                'U+1F1F9 U+1F1F7',
                'flag: Turkey'
            ),
            712 => array(
                'U+1F1F9 U+1F1F2',
                'flag: Turkmenistan'
            ),
            713 => array(
                'U+1F1F9 U+1F1E8',
                'flag: Turks & Caicos Islands'
            ),
            714 => array(
                'U+1F1F9 U+1F1FB',
                'flag: Tuvalu'
            ),
            715 => array(
                'U+1F1FA U+1F1F2',
                'flag: U.S. Outlying Islands'
            ),
            716 => array(
                'U+1F1FB U+1F1EE',
                'flag: U.S. Virgin Islands'
            ),
            717 => array(
                'U+1F1FA U+1F1EC',
                'flag: Uganda'
            ),
            718 => array(
                'U+1F1FA U+1F1E6',
                'flag: Ukraine'
            ),
            719 => array(
                'U+1F1E6 U+1F1EA',
                'flag: United Arab Emirates'
            ),
            720 => array(
                'U+1F1EC U+1F1E7',
                'flag: United Kingdom'
            ),
            721 => array(
                'U+1F1FA U+1F1F3',
                'flag: United Nations'
            ),
            722 => array(
                'U+1F1FA U+1F1F8',
                'flag: United States'
            ),
            723 => array(
                'U+1F1FA U+1F1FE',
                'flag: Uruguay'
            ),
            724 => array(
                'U+1F1FA U+1F1FF',
                'flag: Uzbekistan'
            ),
            725 => array(
                'U+1F1FB U+1F1FA',
                'flag: Vanuatu'
            ),
            726 => array(
                'U+1F1FB U+1F1E6',
                'flag: Vatican City'
            ),
            727 => array(
                'U+1F1FB U+1F1EA',
                'flag: Venezuela'
            ),
            728 => array(
                'U+1F1FB U+1F1F3',
                'flag: Vietnam'
            ),
            729 => array(
                'U+1F3F4 U+E0067 U+E0062 U+E0077 U+E006C U+E0073 U+E007F',
                'flag: Wales'
            ),
            730 => array(
                'U+1F1FC U+1F1EB',
                'flag: Wallis & Futuna'
            ),
            731 => array(
                'U+1F1EA U+1F1ED',
                'flag: Western Sahara'
            ),
            732 => array(
                'U+1F1FE U+1F1EA',
                'flag: Yemen'
            ),
            733 => array(
                'U+1F1FF U+1F1F2',
                'flag: Zambia'
            ),
            734 => array(
                'U+1F1FF U+1F1FC',
                'flag: Zimbabwe'
            ),
            735 => array(
                'U+1F526',
                'flashlight'
            ),
            736 => array(
                'U+269C',
                'fleur-de-lis'
            ),
            737 => array(
                'U+1F4AA',
                'flexed biceps'
            ),
            738 => array(
                'U+1F4BE',
                'floppy disk'
            ),
            739 => array(
                'U+1F3B4',
                'flower playing cards'
            ),
            740 => array(
                'U+1F633',
                'flushed face'
            ),
            741 => array(
                'U+1F6F8',
                'flying saucer'
            ),
            742 => array(
                'U+1F32B',
                'fog'
            ),
            743 => array(
                'U+1F301',
                'foggy'
            ),
            744 => array(
                'U+1F64F',
                'folded hands'
            ),
            745 => array(
                'U+1F463',
                'footprints'
            ),
            746 => array(
                'U+1F374',
                'fork and knife'
            ),
            747 => array(
                'U+1F37D',
                'fork and knife with plate'
            ),
            748 => array(
                'U+1F960',
                'fortune cookie'
            ),
            749 => array(
                'U+26F2',
                'fountain'
            ),
            750 => array(
                'U+1F58B',
                'fountain pen'
            ),
            751 => array(
                'U+1F340',
                'four leaf clover'
            ),
            752 => array(
                'U+1F553',
                'four o’clock'
            ),
            753 => array(
                'U+1F55F',
                'four-thirty'
            ),
            754 => array(
                'U+1F98A',
                'fox'
            ),
            755 => array(
                'U+1F5BC',
                'framed picture'
            ),
            756 => array(
                'U+1F193',
                'FREE button'
            ),
            757 => array(
                'U+1F35F',
                'french fries'
            ),
            758 => array(
                'U+1F364',
                'fried shrimp'
            ),
            759 => array(
                'U+1F438',
                'frog'
            ),
            760 => array(
                'U+1F425',
                'front-facing baby chick'
            ),
            761 => array(
                'U+2639',
                'frowning face'
            ),
            762 => array(
                'U+1F626',
                'frowning face with open mouth'
            ),
            763 => array(
                'U+26FD',
                'fuel pump'
            ),
            764 => array(
                'U+1F315',
                'full moon'
            ),
            765 => array(
                'U+1F31D',
                'full moon face'
            ),
            766 => array(
                'U+26B1',
                'funeral urn'
            ),
            767 => array(
                'U+1F3B2',
                'game die'
            ),
            768 => array(
                'U+2699',
                'gear'
            ),
            769 => array(
                'U+1F48E',
                'gem stone'
            ),
            770 => array(
                'U+264A',
                'Gemini'
            ),
            771 => array(
                'U+1F9DE',
                'genie'
            ),
            772 => array(
                'U+1F47B',
                'ghost'
            ),
            773 => array(
                'U+1F992',
                'giraffe'
            ),
            774 => array(
                'U+1F467',
                'girl'
            ),
            775 => array(
                'U+1F95B',
                'glass of milk'
            ),
            776 => array(
                'U+1F453',
                'glasses'
            ),
            777 => array(
                'U+1F30E',
                'globe showing Americas'
            ),
            778 => array(
                'U+1F30F',
                'globe showing Asia-Australia'
            ),
            779 => array(
                'U+1F30D',
                'globe showing Europe-Africa'
            ),
            780 => array(
                'U+1F310',
                'globe with meridians'
            ),
            781 => array(
                'U+1F9E4',
                'gloves'
            ),
            782 => array(
                'U+1F31F',
                'glowing star'
            ),
            783 => array(
                'U+1F945',
                'goal net'
            ),
            784 => array(
                'U+1F410',
                'goat'
            ),
            785 => array(
                'U+1F47A',
                'goblin'
            ),
            786 => array(
                'U+1F98D',
                'gorilla'
            ),
            787 => array(
                'U+1F393',
                'graduation cap'
            ),
            788 => array(
                'U+1F347',
                'grapes'
            ),
            789 => array(
                'U+1F34F',
                'green apple'
            ),
            790 => array(
                'U+1F4D7',
                'green book'
            ),
            791 => array(
                'U+1F49A',
                'green heart'
            ),
            792 => array(
                'U+1F957',
                'green salad'
            ),
            793 => array(
                'U+1F62C',
                'grimacing face'
            ),
            794 => array(
                'U+1F63A',
                'grinning cat'
            ),
            795 => array(
                'U+1F638',
                'grinning cat with smiling eyes'
            ),
            796 => array(
                'U+1F600',
                'grinning face'
            ),
            797 => array(
                'U+1F603',
                'grinning face with big eyes'
            ),
            798 => array(
                'U+1F604',
                'grinning face with smiling eyes'
            ),
            799 => array(
                'U+1F605',
                'grinning face with sweat'
            ),
            800 => array(
                'U+1F606',
                'grinning squinting face'
            ),
            801 => array(
                'U+1F497',
                'growing heart'
            ),
            802 => array(
                'U+1F482',
                'guard'
            ),
            803 => array(
                'U+1F3B8',
                'guitar'
            ),
            804 => array(
                'U+1F354',
                'hamburger'
            ),
            805 => array(
                'U+1F528',
                'hammer'
            ),
            806 => array(
                'U+2692',
                'hammer and pick'
            ),
            807 => array(
                'U+1F6E0',
                'hammer and wrench'
            ),
            808 => array(
                'U+1F439',
                'hamster'
            ),
            809 => array(
                'U+1F590',
                'hand with fingers splayed'
            ),
            810 => array(
                'U+1F45C',
                'handbag'
            ),
            811 => array(
                'U+1F91D',
                'handshake'
            ),
            812 => array(
                'U+1F423',
                'hatching chick'
            ),
            813 => array(
                'U+1F3A7',
                'headphone'
            ),
            814 => array(
                'U+1F649',
                'hear-no-evil monkey'
            ),
            815 => array(
                'U+1F49F',
                'heart decoration'
            ),
            816 => array(
                'U+2763',
                'heart exclamation'
            ),
            817 => array(
                'U+2665',
                'heart suit'
            ),
            818 => array(
                'U+1F498',
                'heart with arrow'
            ),
            819 => array(
                'U+1F49D',
                'heart with ribbon'
            ),
            820 => array(
                'U+1F4B2',
                'heavy dollar sign'
            ),
            821 => array(
                'U+1F994',
                'hedgehog'
            ),
            822 => array(
                'U+1F681',
                'helicopter'
            ),
            823 => array(
                'U+1F33F',
                'herb'
            ),
            824 => array(
                'U+1F33A',
                'hibiscus'
            ),
            825 => array(
                'U+26A1',
                'high voltage'
            ),
            826 => array(
                'U+1F460',
                'high-heeled shoe'
            ),
            827 => array(
                'U+1F684',
                'high-speed train'
            ),
            828 => array(
                'U+1F573',
                'hole'
            ),
            829 => array(
                'U+2B55',
                'hollow red circle'
            ),
            830 => array(
                'U+1F36F',
                'honey pot'
            ),
            831 => array(
                'U+1F41D',
                'honeybee'
            ),
            832 => array(
                'U+1F6A5',
                'horizontal traffic light'
            ),
            833 => array(
                'U+1F40E',
                'horse'
            ),
            834 => array(
                'U+1F434',
                'horse face'
            ),
            835 => array(
                'U+1F3C7',
                'horse racing'
            ),
            836 => array(
                'U+1F3E5',
                'hospital'
            ),
            837 => array(
                'U+2615',
                'hot beverage'
            ),
            838 => array(
                'U+1F32D',
                'hot dog'
            ),
            839 => array(
                'U+1F336',
                'hot pepper'
            ),
            840 => array(
                'U+2668',
                'hot springs'
            ),
            841 => array(
                'U+1F3E8',
                'hotel'
            ),
            842 => array(
                'U+231B',
                'hourglass done'
            ),
            843 => array(
                'U+23F3',
                'hourglass not done'
            ),
            844 => array(
                'U+1F3E0',
                'house'
            ),
            845 => array(
                'U+1F3E1',
                'house with garden'
            ),
            846 => array(
                'U+1F3D8',
                'houses'
            ),
            847 => array(
                'U+1F917',
                'hugging face'
            ),
            848 => array(
                'U+1F4AF',
                'hundred points'
            ),
            849 => array(
                'U+1F62F',
                'hushed face'
            ),
            850 => array(
                'U+1F368',
                'ice cream'
            ),
            851 => array(
                'U+1F3D2',
                'ice hockey'
            ),
            852 => array(
                'U+26F8',
                'ice skate'
            ),
            853 => array(
                'U+1F194',
                'ID button'
            ),
            854 => array(
                'U+1F4E5',
                'inbox tray'
            ),
            855 => array(
                'U+1F4E8',
                'incoming envelope'
            ),
            856 => array(
                'U+261D',
                'index pointing up'
            ),
            857 => array(
                'U+2139',
                'information'
            ),
            858 => array(
                'U+1F524',
                'input latin letters'
            ),
            859 => array(
                'U+1F521',
                'input latin lowercase'
            ),
            860 => array(
                'U+1F520',
                'input latin uppercase'
            ),
            861 => array(
                'U+1F522',
                'input numbers'
            ),
            862 => array(
                'U+1F523',
                'input symbols'
            ),
            863 => array(
                'U+1F383',
                'jack-o-lantern'
            ),
            864 => array(
                'U+1F251',
                'Japanese “acceptable” button'
            ),
            865 => array(
                'U+1F238',
                'Japanese “application” button'
            ),
            866 => array(
                'U+1F250',
                'Japanese “bargain” button'
            ),
            867 => array(
                'U+3297',
                'Japanese “congratulations” button'
            ),
            868 => array(
                'U+1F239',
                'Japanese “discount” button'
            ),
            869 => array(
                'U+1F21A',
                'Japanese “free of charge” button'
            ),
            870 => array(
                'U+1F201',
                'Japanese “here” button'
            ),
            871 => array(
                'U+1F237',
                'Japanese “monthly amount” button'
            ),
            872 => array(
                'U+1F235',
                'Japanese “no vacancy” button'
            ),
            873 => array(
                'U+1F236',
                'Japanese “not free of charge” button'
            ),
            874 => array(
                'U+1F23A',
                'Japanese “open for business” button'
            ),
            875 => array(
                'U+1F234',
                'Japanese “passing grade” button'
            ),
            876 => array(
                'U+1F232',
                'Japanese “prohibited” button'
            ),
            877 => array(
                'U+1F22F',
                'Japanese “reserved” button'
            ),
            878 => array(
                'U+3299',
                'Japanese “secret” button'
            ),
            879 => array(
                'U+1F202',
                'Japanese “service charge” button'
            ),
            880 => array(
                'U+1F233',
                'Japanese “vacancy” button'
            ),
            881 => array(
                'U+1F3EF',
                'Japanese castle'
            ),
            882 => array(
                'U+1F38E',
                'Japanese dolls'
            ),
            883 => array(
                'U+1F3E3',
                'Japanese post office'
            ),
            884 => array(
                'U+1F530',
                'Japanese symbol for beginner'
            ),
            885 => array(
                'U+1F456',
                'jeans'
            ),
            886 => array(
                'U+1F0CF',
                'joker'
            ),
            887 => array(
                'U+1F579',
                'joystick'
            ),
            888 => array(
                'U+1F54B',
                'kaaba'
            ),
            889 => array(
                'U+1F511',
                'key'
            ),
            890 => array(
                'U+2328',
                'keyboard'
            ),
            891 => array(
                'U+002A U+FE0F U+20E3',
                'keycap: *'
            ),
            892 => array(
                'U+0023 U+FE0F U+20E3',
                'keycap: #'
            ),
            893 => array(
                'U+0030 U+FE0F U+20E3',
                'keycap: 0'
            ),
            894 => array(
                'U+0031 U+FE0F U+20E3',
                'keycap: 1'
            ),
            895 => array(
                'U+1F51F',
                'keycap: 10'
            ),
            896 => array(
                'U+0032 U+FE0F U+20E3',
                'keycap: 2'
            ),
            897 => array(
                'U+0033 U+FE0F U+20E3',
                'keycap: 3'
            ),
            898 => array(
                'U+0034 U+FE0F U+20E3',
                'keycap: 4'
            ),
            899 => array(
                'U+0035 U+FE0F U+20E3',
                'keycap: 5'
            ),
            900 => array(
                'U+0036 U+FE0F U+20E3',
                'keycap: 6'
            ),
            901 => array(
                'U+0037 U+FE0F U+20E3',
                'keycap: 7'
            ),
            902 => array(
                'U+0038 U+FE0F U+20E3',
                'keycap: 8'
            ),
            903 => array(
                'U+0039 U+FE0F U+20E3',
                'keycap: 9'
            ),
            904 => array(
                'U+1F6F4',
                'kick scooter'
            ),
            905 => array(
                'U+1F458',
                'kimono'
            ),
            906 => array(
                'U+1F48F',
                'kiss'
            ),
            907 => array(
                'U+1F48B',
                'kiss mark'
            ),
            908 => array(
                'U+1F468 U+200D U+2764 U+FE0F U+200D U+1F48B U+200D U+1F468',
                'kiss: man, man'
            ),
            909 => array(
                'U+1F469 U+200D U+2764 U+FE0F U+200D U+1F48B U+200D U+1F468',
                'kiss: woman, man'
            ),
            910 => array(
                'U+1F469 U+200D U+2764 U+FE0F U+200D U+1F48B U+200D U+1F469',
                'kiss: woman, woman'
            ),
            911 => array(
                'U+1F63D',
                'kissing cat'
            ),
            912 => array(
                'U+1F617',
                'kissing face'
            ),
            913 => array(
                'U+1F61A',
                'kissing face with closed eyes'
            ),
            914 => array(
                'U+1F619',
                'kissing face with smiling eyes'
            ),
            915 => array(
                'U+1F52A',
                'kitchen knife'
            ),
            916 => array(
                'U+1F95D',
                'kiwi fruit'
            ),
            917 => array(
                'U+1F428',
                'koala'
            ),
            918 => array(
                'U+1F3F7',
                'label'
            ),
            919 => array(
                'U+1F41E',
                'lady beetle'
            ),
            920 => array(
                'U+1F4BB',
                'laptop computer'
            ),
            921 => array(
                'U+1F537',
                'large blue diamond'
            ),
            922 => array(
                'U+1F536',
                'large orange diamond'
            ),
            923 => array(
                'U+1F317',
                'last quarter moon'
            ),
            924 => array(
                'U+1F31C',
                'last quarter moon face'
            ),
            925 => array(
                'U+23EE',
                'last track button'
            ),
            926 => array(
                'U+271D',
                'latin cross'
            ),
            927 => array(
                'U+1F343',
                'leaf fluttering in wind'
            ),
            928 => array(
                'U+1F4D2',
                'ledger'
            ),
            929 => array(
                'U+2B05',
                'left arrow'
            ),
            930 => array(
                'U+21AA',
                'left arrow curving right'
            ),
            931 => array(
                'U+1F6C5',
                'left luggage'
            ),
            932 => array(
                'U+1F5E8',
                'left speech bubble'
            ),
            933 => array(
                'U+1F91B',
                'left-facing fist'
            ),
            934 => array(
                'U+2194',
                'left-right arrow'
            ),
            935 => array(
                'U+1F34B',
                'lemon'
            ),
            936 => array(
                'U+264C',
                'Leo'
            ),
            937 => array(
                'U+1F406',
                'leopard'
            ),
            938 => array(
                'U+1F39A',
                'level slider'
            ),
            939 => array(
                'U+264E',
                'Libra'
            ),
            940 => array(
                'U+1F4A1',
                'light bulb'
            ),
            941 => array(
                'U+1F688',
                'light rail'
            ),
            942 => array(
                'U+1F517',
                'link'
            ),
            943 => array(
                'U+1F587',
                'linked paperclips'
            ),
            944 => array(
                'U+1F981',
                'lion'
            ),
            945 => array(
                'U+1F484',
                'lipstick'
            ),
            946 => array(
                'U+1F6AE',
                'litter in bin sign'
            ),
            947 => array(
                'U+1F98E',
                'lizard'
            ),
            948 => array(
                'U+1F512',
                'locked'
            ),
            949 => array(
                'U+1F510',
                'locked with key'
            ),
            950 => array(
                'U+1F50F',
                'locked with pen'
            ),
            951 => array(
                'U+1F682',
                'locomotive'
            ),
            952 => array(
                'U+1F36D',
                'lollipop'
            ),
            953 => array(
                'U+1F62D',
                'loudly crying face'
            ),
            954 => array(
                'U+1F4E2',
                'loudspeaker'
            ),
            955 => array(
                'U+1F3E9',
                'love hotel'
            ),
            956 => array(
                'U+1F48C',
                'love letter'
            ),
            957 => array(
                'U+1F91F',
                'love-you gesture'
            ),
            958 => array(
                'U+1F925',
                'lying face'
            ),
            959 => array(
                'U+1F9D9',
                'mage'
            ),
            960 => array(
                'U+1F50D',
                'magnifying glass tilted left'
            ),
            961 => array(
                'U+1F50E',
                'magnifying glass tilted right'
            ),
            962 => array(
                'U+1F004',
                'mahjong red dragon'
            ),
            963 => array(
                'U+2642',
                'male sign'
            ),
            964 => array(
                'U+1F468',
                'man'
            ),
            965 => array(
                'U+1F468 U+200D U+1F3A8',
                'man artist'
            ),
            966 => array(
                'U+1F468 U+200D U+1F680',
                'man astronaut'
            ),
            967 => array(
                'U+1F6B4 U+200D U+2642 U+FE0F',
                'man biking'
            ),
            968 => array(
                'U+26F9 U+FE0F U+200D U+2642 U+FE0F',
                'man bouncing ball'
            ),
            969 => array(
                'U+1F647 U+200D U+2642 U+FE0F',
                'man bowing'
            ),
            970 => array(
                'U+1F938 U+200D U+2642 U+FE0F',
                'man cartwheeling'
            ),
            971 => array(
                'U+1F9D7 U+200D U+2642 U+FE0F',
                'man climbing'
            ),
            972 => array(
                'U+1F477 U+200D U+2642 U+FE0F',
                'man construction worker'
            ),
            973 => array(
                'U+1F468 U+200D U+1F373',
                'man cook'
            ),
            974 => array(
                'U+1F57A',
                'man dancing'
            ),
            975 => array(
                'U+1F575 U+FE0F U+200D U+2642 U+FE0F',
                'man detective'
            ),
            976 => array(
                'U+1F9DD U+200D U+2642 U+FE0F',
                'man elf'
            ),
            977 => array(
                'U+1F926 U+200D U+2642 U+FE0F',
                'man facepalming'
            ),
            978 => array(
                'U+1F468 U+200D U+1F3ED',
                'man factory worker'
            ),
            979 => array(
                'U+1F9DA U+200D U+2642 U+FE0F',
                'man fairy'
            ),
            980 => array(
                'U+1F468 U+200D U+1F33E',
                'man farmer'
            ),
            981 => array(
                'U+1F468 U+200D U+1F692',
                'man firefighter'
            ),
            982 => array(
                'U+1F64D U+200D U+2642 U+FE0F',
                'man frowning'
            ),
            983 => array(
                'U+1F9DE U+200D U+2642 U+FE0F',
                'man genie'
            ),
            984 => array(
                'U+1F645 U+200D U+2642 U+FE0F',
                'man gesturing NO'
            ),
            985 => array(
                'U+1F646 U+200D U+2642 U+FE0F',
                'man gesturing OK'
            ),
            986 => array(
                'U+1F487 U+200D U+2642 U+FE0F',
                'man getting haircut'
            ),
            987 => array(
                'U+1F486 U+200D U+2642 U+FE0F',
                'man getting massage'
            ),
            988 => array(
                'U+1F3CC U+FE0F U+200D U+2642 U+FE0F',
                'man golfing'
            ),
            989 => array(
                'U+1F482 U+200D U+2642 U+FE0F',
                'man guard'
            ),
            990 => array(
                'U+1F468 U+200D U+2695 U+FE0F',
                'man health worker'
            ),
            991 => array(
                'U+1F9D8 U+200D U+2642 U+FE0F',
                'man in lotus position'
            ),
            992 => array(
                'U+1F9D6 U+200D U+2642 U+FE0F',
                'man in steamy room'
            ),
            993 => array(
                'U+1F574',
                'man in suit levitating'
            ),
            994 => array(
                'U+1F935',
                'man in tuxedo'
            ),
            995 => array(
                'U+1F468 U+200D U+2696 U+FE0F',
                'man judge'
            ),
            996 => array(
                'U+1F939 U+200D U+2642 U+FE0F',
                'man juggling'
            ),
            997 => array(
                'U+1F3CB U+FE0F U+200D U+2642 U+FE0F',
                'man lifting weights'
            ),
            998 => array(
                'U+1F9D9 U+200D U+2642 U+FE0F',
                'man mage'
            ),
            999 => array(
                'U+1F468 U+200D U+1F527',
                'man mechanic'
            ),
            1000 => array(
                'U+1F6B5 U+200D U+2642 U+FE0F',
                'man mountain biking'
            ),
            1001 => array(
                'U+1F468 U+200D U+1F4BC',
                'man office worker'
            ),
            1002 => array(
                'U+1F468 U+200D U+2708 U+FE0F',
                'man pilot'
            ),
            1003 => array(
                'U+1F93E U+200D U+2642 U+FE0F',
                'man playing handball'
            ),
            1004 => array(
                'U+1F93D U+200D U+2642 U+FE0F',
                'man playing water polo'
            ),
            1005 => array(
                'U+1F46E U+200D U+2642 U+FE0F',
                'man police officer'
            ),
            1006 => array(
                'U+1F64E U+200D U+2642 U+FE0F',
                'man pouting'
            ),
            1007 => array(
                'U+1F64B U+200D U+2642 U+FE0F',
                'man raising hand'
            ),
            1008 => array(
                'U+1F6A3 U+200D U+2642 U+FE0F',
                'man rowing boat'
            ),
            1009 => array(
                'U+1F3C3 U+200D U+2642 U+FE0F',
                'man running'
            ),
            1010 => array(
                'U+1F468 U+200D U+1F52C',
                'man scientist'
            ),
            1011 => array(
                'U+1F937 U+200D U+2642 U+FE0F',
                'man shrugging'
            ),
            1012 => array(
                'U+1F468 U+200D U+1F3A4',
                'man singer'
            ),
            1013 => array(
                'U+1F468 U+200D U+1F393',
                'man student'
            ),
            1014 => array(
                'U+1F3C4 U+200D U+2642 U+FE0F',
                'man surfing'
            ),
            1015 => array(
                'U+1F3CA U+200D U+2642 U+FE0F',
                'man swimming'
            ),
            1016 => array(
                'U+1F468 U+200D U+1F3EB',
                'man teacher'
            ),
            1017 => array(
                'U+1F468 U+200D U+1F4BB',
                'man technologist'
            ),
            1018 => array(
                'U+1F481 U+200D U+2642 U+FE0F',
                'man tipping hand'
            ),
            1019 => array(
                'U+1F9DB U+200D U+2642 U+FE0F',
                'man vampire'
            ),
            1020 => array(
                'U+1F6B6 U+200D U+2642 U+FE0F',
                'man walking'
            ),
            1021 => array(
                'U+1F473 U+200D U+2642 U+FE0F',
                'man wearing turban'
            ),
            1022 => array(
                'U+1F472',
                'man with Chinese cap'
            ),
            1023 => array(
                'U+1F9DF U+200D U+2642 U+FE0F',
                'man zombie'
            ),
            1024 => array(
                'U+1F9D4',
                'man: beard'
            ),
            1025 => array(
                'U+1F471 U+200D U+2642 U+FE0F',
                'man: blond hair'
            ),
            1026 => array(
                'U+1F45E',
                'man’s shoe'
            ),
            1027 => array(
                'U+1F570',
                'mantelpiece clock'
            ),
            1028 => array(
                'U+1F5FE',
                'map of Japan'
            ),
            1029 => array(
                'U+1F341',
                'maple leaf'
            ),
            1030 => array(
                'U+1F94B',
                'martial arts uniform'
            ),
            1031 => array(
                'U+1F356',
                'meat on bone'
            ),
            1032 => array(
                'U+2695',
                'medical symbol'
            ),
            1033 => array(
                'U+1F4E3',
                'megaphone'
            ),
            1034 => array(
                'U+1F348',
                'melon'
            ),
            1035 => array(
                'U+1F4DD',
                'memo'
            ),
            1036 => array(
                'U+1F46C',
                'men holding hands'
            ),
            1037 => array(
                'U+1F46F U+200D U+2642 U+FE0F',
                'men with bunny ears'
            ),
            1038 => array(
                'U+1F93C U+200D U+2642 U+FE0F',
                'men wrestling'
            ),
            1039 => array(
                'U+1F6B9',
                'men’s room'
            ),
            1040 => array(
                'U+1F54E',
                'menorah'
            ),
            1041 => array(
                'U+1F9DC U+200D U+2640 U+FE0F',
                'mermaid'
            ),
            1042 => array(
                'U+1F9DC U+200D U+2642 U+FE0F',
                'merman'
            ),
            1043 => array(
                'U+1F9DC',
                'merperson'
            ),
            1044 => array(
                'U+1F687',
                'metro'
            ),
            1045 => array(
                'U+1F3A4',
                'microphone'
            ),
            1046 => array(
                'U+1F52C',
                'microscope'
            ),
            1047 => array(
                'U+1F595',
                'middle finger'
            ),
            1048 => array(
                'U+1F396',
                'military medal'
            ),
            1049 => array(
                'U+1F30C',
                'milky way'
            ),
            1050 => array(
                'U+1F690',
                'minibus'
            ),
            1051 => array(
                'U+2796',
                'minus sign'
            ),
            1052 => array(
                'U+1F5FF',
                'moai'
            ),
            1053 => array(
                'U+1F4F1',
                'mobile phone'
            ),
            1054 => array(
                'U+1F4F4',
                'mobile phone off'
            ),
            1055 => array(
                'U+1F4F2',
                'mobile phone with arrow'
            ),
            1056 => array(
                'U+1F4B0',
                'money bag'
            ),
            1057 => array(
                'U+1F4B8',
                'money with wings'
            ),
            1058 => array(
                'U+1F911',
                'money-mouth face'
            ),
            1059 => array(
                'U+1F412',
                'monkey'
            ),
            1060 => array(
                'U+1F435',
                'monkey face'
            ),
            1061 => array(
                'U+1F69D',
                'monorail'
            ),
            1062 => array(
                'U+1F391',
                'moon viewing ceremony'
            ),
            1063 => array(
                'U+1F54C',
                'mosque'
            ),
            1064 => array(
                'U+1F6E5',
                'motor boat'
            ),
            1065 => array(
                'U+1F6F5',
                'motor scooter'
            ),
            1066 => array(
                'U+1F3CD',
                'motorcycle'
            ),
            1067 => array(
                'U+1F6E3',
                'motorway'
            ),
            1068 => array(
                'U+1F5FB',
                'mount fuji'
            ),
            1069 => array(
                'U+26F0',
                'mountain'
            ),
            1070 => array(
                'U+1F6A0',
                'mountain cableway'
            ),
            1071 => array(
                'U+1F69E',
                'mountain railway'
            ),
            1072 => array(
                'U+1F401',
                'mouse'
            ),
            1073 => array(
                'U+1F42D',
                'mouse face'
            ),
            1074 => array(
                'U+1F444',
                'mouth'
            ),
            1075 => array(
                'U+1F3A5',
                'movie camera'
            ),
            1076 => array(
                'U+1F936',
                'Mrs. Claus'
            ),
            1077 => array(
                'U+2716',
                'multiplication sign'
            ),
            1078 => array(
                'U+1F344',
                'mushroom'
            ),
            1079 => array(
                'U+1F3B9',
                'musical keyboard'
            ),
            1080 => array(
                'U+1F3B5',
                'musical note'
            ),
            1081 => array(
                'U+1F3B6',
                'musical notes'
            ),
            1082 => array(
                'U+1F3BC',
                'musical score'
            ),
            1083 => array(
                'U+1F507',
                'muted speaker'
            ),
            1084 => array(
                'U+1F485',
                'nail polish'
            ),
            1085 => array(
                'U+1F4DB',
                'name badge'
            ),
            1086 => array(
                'U+1F3DE',
                'national park'
            ),
            1087 => array(
                'U+1F922',
                'nauseated face'
            ),
            1088 => array(
                'U+1F454',
                'necktie'
            ),
            1089 => array(
                'U+1F913',
                'nerd face'
            ),
            1090 => array(
                'U+1F610',
                'neutral face'
            ),
            1091 => array(
                'U+1F195',
                'NEW button'
            ),
            1092 => array(
                'U+1F311',
                'new moon'
            ),
            1093 => array(
                'U+1F31A',
                'new moon face'
            ),
            1094 => array(
                'U+1F4F0',
                'newspaper'
            ),
            1095 => array(
                'U+23ED',
                'next track button'
            ),
            1096 => array(
                'U+1F196',
                'NG button'
            ),
            1097 => array(
                'U+1F303',
                'night with stars'
            ),
            1098 => array(
                'U+1F558',
                'nine o’clock'
            ),
            1099 => array(
                'U+1F564',
                'nine-thirty'
            ),
            1100 => array(
                'U+1F6B3',
                'no bicycles'
            ),
            1101 => array(
                'U+26D4',
                'no entry'
            ),
            1102 => array(
                'U+1F6AF',
                'no littering'
            ),
            1103 => array(
                'U+1F4F5',
                'no mobile phones'
            ),
            1104 => array(
                'U+1F51E',
                'no one under eighteen'
            ),
            1105 => array(
                'U+1F6B7',
                'no pedestrians'
            ),
            1106 => array(
                'U+1F6AD',
                'no smoking'
            ),
            1107 => array(
                'U+1F6B1',
                'non-potable water'
            ),
            1108 => array(
                'U+1F443',
                'nose'
            ),
            1109 => array(
                'U+1F4D3',
                'notebook'
            ),
            1110 => array(
                'U+1F4D4',
                'notebook with decorative cover'
            ),
            1111 => array(
                'U+1F529',
                'nut and bolt'
            ),
            1112 => array(
                'U+1F17E',
                'O button (blood type)'
            ),
            1113 => array(
                'U+1F419',
                'octopus'
            ),
            1114 => array(
                'U+1F362',
                'oden'
            ),
            1115 => array(
                'U+1F3E2',
                'office building'
            ),
            1116 => array(
                'U+1F479',
                'ogre'
            ),
            1117 => array(
                'U+1F6E2',
                'oil drum'
            ),
            1118 => array(
                'U+1F197',
                'OK button'
            ),
            1119 => array(
                'U+1F44C',
                'OK hand'
            ),
            1120 => array(
                'U+1F5DD',
                'old key'
            ),
            1121 => array(
                'U+1F474',
                'old man'
            ),
            1122 => array(
                'U+1F475',
                'old woman'
            ),
            1123 => array(
                'U+1F9D3',
                'older person'
            ),
            1124 => array(
                'U+1F549',
                'om'
            ),
            1125 => array(
                'U+1F51B',
                'ON! arrow'
            ),
            1126 => array(
                'U+1F698',
                'oncoming automobile'
            ),
            1127 => array(
                'U+1F68D',
                'oncoming bus'
            ),
            1128 => array(
                'U+1F44A',
                'oncoming fist'
            ),
            1129 => array(
                'U+1F694',
                'oncoming police car'
            ),
            1130 => array(
                'U+1F696',
                'oncoming taxi'
            ),
            1131 => array(
                'U+1F550',
                'one o’clock'
            ),
            1132 => array(
                'U+1F55C',
                'one-thirty'
            ),
            1133 => array(
                'U+1F4D6',
                'open book'
            ),
            1134 => array(
                'U+1F4C2',
                'open file folder'
            ),
            1135 => array(
                'U+1F450',
                'open hands'
            ),
            1136 => array(
                'U+1F4ED',
                'open mailbox with lowered flag'
            ),
            1137 => array(
                'U+1F4EC',
                'open mailbox with raised flag'
            ),
            1138 => array(
                'U+26CE',
                'Ophiuchus'
            ),
            1139 => array(
                'U+1F4BF',
                'optical disk'
            ),
            1140 => array(
                'U+1F4D9',
                'orange book'
            ),
            1141 => array(
                'U+1F9E1',
                'orange heart'
            ),
            1142 => array(
                'U+2626',
                'orthodox cross'
            ),
            1143 => array(
                'U+1F4E4',
                'outbox tray'
            ),
            1144 => array(
                'U+1F989',
                'owl'
            ),
            1145 => array(
                'U+1F402',
                'ox'
            ),
            1146 => array(
                'U+1F17F',
                'P button'
            ),
            1147 => array(
                'U+1F4E6',
                'package'
            ),
            1148 => array(
                'U+1F4C4',
                'page facing up'
            ),
            1149 => array(
                'U+1F4C3',
                'page with curl'
            ),
            1150 => array(
                'U+1F4DF',
                'pager'
            ),
            1151 => array(
                'U+1F58C',
                'paintbrush'
            ),
            1152 => array(
                'U+1F334',
                'palm tree'
            ),
            1153 => array(
                'U+1F932',
                'palms up together'
            ),
            1154 => array(
                'U+1F95E',
                'pancakes'
            ),
            1155 => array(
                'U+1F43C',
                'panda'
            ),
            1156 => array(
                'U+1F4CE',
                'paperclip'
            ),
            1157 => array(
                'U+303D',
                'part alternation mark'
            ),
            1158 => array(
                'U+1F389',
                'party popper'
            ),
            1159 => array(
                'U+1F6F3',
                'passenger ship'
            ),
            1160 => array(
                'U+1F6C2',
                'passport control'
            ),
            1161 => array(
                'U+23F8',
                'pause button'
            ),
            1162 => array(
                'U+1F43E',
                'paw prints'
            ),
            1163 => array(
                'U+262E',
                'peace symbol'
            ),
            1164 => array(
                'U+1F351',
                'peach'
            ),
            1165 => array(
                'U+1F95C',
                'peanuts'
            ),
            1166 => array(
                'U+1F350',
                'pear'
            ),
            1167 => array(
                'U+1F58A',
                'pen'
            ),
            1168 => array(
                'U+270F',
                'pencil'
            ),
            1169 => array(
                'U+1F427',
                'penguin'
            ),
            1170 => array(
                'U+1F614',
                'pensive face'
            ),
            1171 => array(
                'U+1F46F',
                'people with bunny ears'
            ),
            1172 => array(
                'U+1F93C',
                'people wrestling'
            ),
            1173 => array(
                'U+1F3AD',
                'performing arts'
            ),
            1174 => array(
                'U+1F623',
                'persevering face'
            ),
            1175 => array(
                'U+1F9D1',
                'person'
            ),
            1176 => array(
                'U+1F6B4',
                'person biking'
            ),
            1177 => array(
                'U+26F9',
                'person bouncing ball'
            ),
            1178 => array(
                'U+1F647',
                'person bowing'
            ),
            1179 => array(
                'U+1F938',
                'person cartwheeling'
            ),
            1180 => array(
                'U+1F9D7',
                'person climbing'
            ),
            1181 => array(
                'U+1F926',
                'person facepalming'
            ),
            1182 => array(
                'U+1F93A',
                'person fencing'
            ),
            1183 => array(
                'U+1F64D',
                'person frowning'
            ),
            1184 => array(
                'U+1F645',
                'person gesturing NO'
            ),
            1185 => array(
                'U+1F646',
                'person gesturing OK'
            ),
            1186 => array(
                'U+1F487',
                'person getting haircut'
            ),
            1187 => array(
                'U+1F486',
                'person getting massage'
            ),
            1188 => array(
                'U+1F3CC',
                'person golfing'
            ),
            1189 => array(
                'U+1F6CC',
                'person in bed'
            ),
            1190 => array(
                'U+1F9D8',
                'person in lotus position'
            ),
            1191 => array(
                'U+1F9D6',
                'person in steamy room'
            ),
            1192 => array(
                'U+1F939',
                'person juggling'
            ),
            1193 => array(
                'U+1F3CB',
                'person lifting weights'
            ),
            1194 => array(
                'U+1F6B5',
                'person mountain biking'
            ),
            1195 => array(
                'U+1F93E',
                'person playing handball'
            ),
            1196 => array(
                'U+1F93D',
                'person playing water polo'
            ),
            1197 => array(
                'U+1F64E',
                'person pouting'
            ),
            1198 => array(
                'U+1F64B',
                'person raising hand'
            ),
            1199 => array(
                'U+1F6A3',
                'person rowing boat'
            ),
            1200 => array(
                'U+1F3C3',
                'person running'
            ),
            1201 => array(
                'U+1F937',
                'person shrugging'
            ),
            1202 => array(
                'U+1F3C4',
                'person surfing'
            ),
            1203 => array(
                'U+1F3CA',
                'person swimming'
            ),
            1204 => array(
                'U+1F6C0',
                'person taking bath'
            ),
            1205 => array(
                'U+1F481',
                'person tipping hand'
            ),
            1206 => array(
                'U+1F6B6',
                'person walking'
            ),
            1207 => array(
                'U+1F473',
                'person wearing turban'
            ),
            1208 => array(
                'U+1F471',
                'person: blond hair'
            ),
            1209 => array(
                'U+26CF',
                'pick'
            ),
            1210 => array(
                'U+1F967',
                'pie'
            ),
            1211 => array(
                'U+1F416',
                'pig'
            ),
            1212 => array(
                'U+1F437',
                'pig face'
            ),
            1213 => array(
                'U+1F43D',
                'pig nose'
            ),
            1214 => array(
                'U+1F4A9',
                'pile of poo'
            ),
            1215 => array(
                'U+1F48A',
                'pill'
            ),
            1216 => array(
                'U+1F38D',
                'pine decoration'
            ),
            1217 => array(
                'U+1F34D',
                'pineapple'
            ),
            1218 => array(
                'U+1F3D3',
                'ping pong'
            ),
            1219 => array(
                'U+2653',
                'Pisces'
            ),
            1220 => array(
                'U+1F52B',
                'pistol'
            ),
            1221 => array(
                'U+1F355',
                'pizza'
            ),
            1222 => array(
                'U+1F6D0',
                'place of worship'
            ),
            1223 => array(
                'U+25B6',
                'play button'
            ),
            1224 => array(
                'U+23EF',
                'play or pause button'
            ),
            1225 => array(
                'U+2795',
                'plus sign'
            ),
            1226 => array(
                'U+1F693',
                'police car'
            ),
            1227 => array(
                'U+1F6A8',
                'police car light'
            ),
            1228 => array(
                'U+1F46E',
                'police officer'
            ),
            1229 => array(
                'U+1F429',
                'poodle'
            ),
            1230 => array(
                'U+1F3B1',
                'pool 8 ball'
            ),
            1231 => array(
                'U+1F37F',
                'popcorn'
            ),
            1232 => array(
                'U+1F3E4',
                'post office'
            ),
            1233 => array(
                'U+1F4EF',
                'postal horn'
            ),
            1234 => array(
                'U+1F4EE',
                'postbox'
            ),
            1235 => array(
                'U+1F372',
                'pot of food'
            ),
            1236 => array(
                'U+1F6B0',
                'potable water'
            ),
            1237 => array(
                'U+1F954',
                'potato'
            ),
            1238 => array(
                'U+1F357',
                'poultry leg'
            ),
            1239 => array(
                'U+1F4B7',
                'pound banknote'
            ),
            1240 => array(
                'U+1F63E',
                'pouting cat'
            ),
            1241 => array(
                'U+1F621',
                'pouting face'
            ),
            1242 => array(
                'U+1F4FF',
                'prayer beads'
            ),
            1243 => array(
                'U+1F930',
                'pregnant woman'
            ),
            1244 => array(
                'U+1F968',
                'pretzel'
            ),
            1245 => array(
                'U+1F934',
                'prince'
            ),
            1246 => array(
                'U+1F478',
                'princess'
            ),
            1247 => array(
                'U+1F5A8',
                'printer'
            ),
            1248 => array(
                'U+1F6AB',
                'prohibited'
            ),
            1249 => array(
                'U+1F49C',
                'purple heart'
            ),
            1250 => array(
                'U+1F45B',
                'purse'
            ),
            1251 => array(
                'U+1F4CC',
                'pushpin'
            ),
            1252 => array(
                'U+2753',
                'question mark'
            ),
            1253 => array(
                'U+1F407',
                'rabbit'
            ),
            1254 => array(
                'U+1F430',
                'rabbit face'
            ),
            1255 => array(
                'U+1F3CE',
                'racing car'
            ),
            1256 => array(
                'U+1F4FB',
                'radio'
            ),
            1257 => array(
                'U+1F518',
                'radio button'
            ),
            1258 => array(
                'U+2622',
                'radioactive'
            ),
            1259 => array(
                'U+1F683',
                'railway car'
            ),
            1260 => array(
                'U+1F6E4',
                'railway track'
            ),
            1261 => array(
                'U+1F308',
                'rainbow'
            ),
            1262 => array(
                'U+1F3F3 U+FE0F U+200D U+1F308',
                'rainbow flag'
            ),
            1263 => array(
                'U+1F91A',
                'raised back of hand'
            ),
            1264 => array(
                'U+270A',
                'raised fist'
            ),
            1265 => array(
                'U+270B',
                'raised hand'
            ),
            1266 => array(
                'U+1F64C',
                'raising hands'
            ),
            1267 => array(
                'U+1F40F',
                'ram'
            ),
            1268 => array(
                'U+1F400',
                'rat'
            ),
            1269 => array(
                'U+23FA',
                'record button'
            ),
            1270 => array(
                'U+267B',
                'recycling symbol'
            ),
            1271 => array(
                'U+1F34E',
                'red apple'
            ),
            1272 => array(
                'U+1F534',
                'red circle'
            ),
            1273 => array(
                'U+2764',
                'red heart'
            ),
            1274 => array(
                'U+1F3EE',
                'red paper lantern'
            ),
            1275 => array(
                'U+1F53B',
                'red triangle pointed down'
            ),
            1276 => array(
                'U+1F53A',
                'red triangle pointed up'
            ),
            1277 => array(
                'U+00AE',
                'registered'
            ),
            1278 => array(
                'U+1F60C',
                'relieved face'
            ),
            1279 => array(
                'U+1F397',
                'reminder ribbon'
            ),
            1280 => array(
                'U+1F501',
                'repeat button'
            ),
            1281 => array(
                'U+1F502',
                'repeat single button'
            ),
            1282 => array(
                'U+26D1',
                'rescue worker’s helmet'
            ),
            1283 => array(
                'U+1F6BB',
                'restroom'
            ),
            1284 => array(
                'U+25C0',
                'reverse button'
            ),
            1285 => array(
                'U+1F49E',
                'revolving hearts'
            ),
            1286 => array(
                'U+1F98F',
                'rhinoceros'
            ),
            1287 => array(
                'U+1F380',
                'ribbon'
            ),
            1288 => array(
                'U+1F359',
                'rice ball'
            ),
            1289 => array(
                'U+1F358',
                'rice cracker'
            ),
            1290 => array(
                'U+1F5EF',
                'right anger bubble'
            ),
            1291 => array(
                'U+27A1',
                'right arrow'
            ),
            1292 => array(
                'U+2935',
                'right arrow curving down'
            ),
            1293 => array(
                'U+21A9',
                'right arrow curving left'
            ),
            1294 => array(
                'U+2934',
                'right arrow curving up'
            ),
            1295 => array(
                'U+1F91C',
                'right-facing fist'
            ),
            1296 => array(
                'U+1F48D',
                'ring'
            ),
            1297 => array(
                'U+1F360',
                'roasted sweet potato'
            ),
            1298 => array(
                'U+1F916',
                'robot'
            ),
            1299 => array(
                'U+1F680',
                'rocket'
            ),
            1300 => array(
                'U+1F5DE',
                'rolled-up newspaper'
            ),
            1301 => array(
                'U+1F3A2',
                'roller coaster'
            ),
            1302 => array(
                'U+1F923',
                'rolling on the floor laughing'
            ),
            1303 => array(
                'U+1F413',
                'rooster'
            ),
            1304 => array(
                'U+1F339',
                'rose'
            ),
            1305 => array(
                'U+1F3F5',
                'rosette'
            ),
            1306 => array(
                'U+1F4CD',
                'round pushpin'
            ),
            1307 => array(
                'U+1F3C9',
                'rugby football'
            ),
            1308 => array(
                'U+1F3BD',
                'running shirt'
            ),
            1309 => array(
                'U+1F45F',
                'running shoe'
            ),
            1310 => array(
                'U+1F625',
                'sad but relieved face'
            ),
            1311 => array(
                'U+2650',
                'Sagittarius'
            ),
            1312 => array(
                'U+26F5',
                'sailboat'
            ),
            1313 => array(
                'U+1F376',
                'sake'
            ),
            1314 => array(
                'U+1F96A',
                'sandwich'
            ),
            1315 => array(
                'U+1F385',
                'Santa Claus'
            ),
            1316 => array(
                'U+1F6F0',
                'satellite'
            ),
            1317 => array(
                'U+1F4E1',
                'satellite antenna'
            ),
            1318 => array(
                'U+1F995',
                'sauropod'
            ),
            1319 => array(
                'U+1F3B7',
                'saxophone'
            ),
            1320 => array(
                'U+1F9E3',
                'scarf'
            ),
            1321 => array(
                'U+1F3EB',
                'school'
            ),
            1322 => array(
                'U+2702',
                'scissors'
            ),
            1323 => array(
                'U+264F',
                'Scorpio'
            ),
            1324 => array(
                'U+1F982',
                'scorpion'
            ),
            1325 => array(
                'U+1F4DC',
                'scroll'
            ),
            1326 => array(
                'U+1F4BA',
                'seat'
            ),
            1327 => array(
                'U+1F648',
                'see-no-evil monkey'
            ),
            1328 => array(
                'U+1F331',
                'seedling'
            ),
            1329 => array(
                'U+1F933',
                'selfie'
            ),
            1330 => array(
                'U+1F556',
                'seven o’clock'
            ),
            1331 => array(
                'U+1F562',
                'seven-thirty'
            ),
            1332 => array(
                'U+1F958',
                'shallow pan of food'
            ),
            1333 => array(
                'U+2618',
                'shamrock'
            ),
            1334 => array(
                'U+1F988',
                'shark'
            ),
            1335 => array(
                'U+1F367',
                'shaved ice'
            ),
            1336 => array(
                'U+1F33E',
                'sheaf of rice'
            ),
            1337 => array(
                'U+1F6E1',
                'shield'
            ),
            1338 => array(
                'U+26E9',
                'shinto shrine'
            ),
            1339 => array(
                'U+1F6A2',
                'ship'
            ),
            1340 => array(
                'U+1F320',
                'shooting star'
            ),
            1341 => array(
                'U+1F6CD',
                'shopping bags'
            ),
            1342 => array(
                'U+1F6D2',
                'shopping cart'
            ),
            1343 => array(
                'U+1F370',
                'shortcake'
            ),
            1344 => array(
                'U+1F6BF',
                'shower'
            ),
            1345 => array(
                'U+1F990',
                'shrimp'
            ),
            1346 => array(
                'U+1F500',
                'shuffle tracks button'
            ),
            1347 => array(
                'U+1F92B',
                'shushing face'
            ),
            1348 => array(
                'U+1F918',
                'sign of the horns'
            ),
            1349 => array(
                'U+1F555',
                'six o’clock'
            ),
            1350 => array(
                'U+1F561',
                'six-thirty'
            ),
            1351 => array(
                'U+26F7',
                'skier'
            ),
            1352 => array(
                'U+1F3BF',
                'skis'
            ),
            1353 => array(
                'U+1F480',
                'skull'
            ),
            1354 => array(
                'U+2620',
                'skull and crossbones'
            ),
            1355 => array(
                'U+1F6F7',
                'sled'
            ),
            1356 => array(
                'U+1F634',
                'sleeping face'
            ),
            1357 => array(
                'U+1F62A',
                'sleepy face'
            ),
            1358 => array(
                'U+1F641',
                'slightly frowning face'
            ),
            1359 => array(
                'U+1F642',
                'slightly smiling face'
            ),
            1360 => array(
                'U+1F3B0',
                'slot machine'
            ),
            1361 => array(
                'U+1F6E9',
                'small airplane'
            ),
            1362 => array(
                'U+1F539',
                'small blue diamond'
            ),
            1363 => array(
                'U+1F538',
                'small orange diamond'
            ),
            1364 => array(
                'U+1F63B',
                'smiling cat with heart-eyes'
            ),
            1365 => array(
                'U+263A',
                'smiling face'
            ),
            1366 => array(
                'U+1F607',
                'smiling face with halo'
            ),
            1367 => array(
                'U+1F60D',
                'smiling face with heart-eyes'
            ),
            1368 => array(
                'U+1F608',
                'smiling face with horns'
            ),
            1369 => array(
                'U+1F60A',
                'smiling face with smiling eyes'
            ),
            1370 => array(
                'U+1F60E',
                'smiling face with sunglasses'
            ),
            1371 => array(
                'U+1F60F',
                'smirking face'
            ),
            1372 => array(
                'U+1F40C',
                'snail'
            ),
            1373 => array(
                'U+1F40D',
                'snake'
            ),
            1374 => array(
                'U+1F927',
                'sneezing face'
            ),
            1375 => array(
                'U+1F3D4',
                'snow-capped mountain'
            ),
            1376 => array(
                'U+1F3C2',
                'snowboarder'
            ),
            1377 => array(
                'U+2744',
                'snowflake'
            ),
            1378 => array(
                'U+2603',
                'snowman'
            ),
            1379 => array(
                'U+26C4',
                'snowman without snow'
            ),
            1380 => array(
                'U+26BD',
                'soccer ball'
            ),
            1381 => array(
                'U+1F9E6',
                'socks'
            ),
            1382 => array(
                'U+1F366',
                'soft ice cream'
            ),
            1383 => array(
                'U+1F51C',
                'SOON arrow'
            ),
            1384 => array(
                'U+1F198',
                'SOS button'
            ),
            1385 => array(
                'U+2660',
                'spade suit'
            ),
            1386 => array(
                'U+1F35D',
                'spaghetti'
            ),
            1387 => array(
                'U+2747',
                'sparkle'
            ),
            1388 => array(
                'U+1F387',
                'sparkler'
            ),
            1389 => array(
                'U+2728',
                'sparkles'
            ),
            1390 => array(
                'U+1F496',
                'sparkling heart'
            ),
            1391 => array(
                'U+1F64A',
                'speak-no-evil monkey'
            ),
            1392 => array(
                'U+1F50A',
                'speaker high volume'
            ),
            1393 => array(
                'U+1F508',
                'speaker low volume'
            ),
            1394 => array(
                'U+1F509',
                'speaker medium volume'
            ),
            1395 => array(
                'U+1F5E3',
                'speaking head'
            ),
            1396 => array(
                'U+1F4AC',
                'speech balloon'
            ),
            1397 => array(
                'U+1F6A4',
                'speedboat'
            ),
            1398 => array(
                'U+1F577',
                'spider'
            ),
            1399 => array(
                'U+1F578',
                'spider web'
            ),
            1400 => array(
                'U+1F5D3',
                'spiral calendar'
            ),
            1401 => array(
                'U+1F5D2',
                'spiral notepad'
            ),
            1402 => array(
                'U+1F41A',
                'spiral shell'
            ),
            1403 => array(
                'U+1F944',
                'spoon'
            ),
            1404 => array(
                'U+1F699',
                'sport utility vehicle'
            ),
            1405 => array(
                'U+1F3C5',
                'sports medal'
            ),
            1406 => array(
                'U+1F433',
                'spouting whale'
            ),
            1407 => array(
                'U+1F991',
                'squid'
            ),
            1408 => array(
                'U+1F61D',
                'squinting face with tongue'
            ),
            1409 => array(
                'U+1F3DF',
                'stadium'
            ),
            1410 => array(
                'U+2B50',
                'star'
            ),
            1411 => array(
                'U+262A',
                'star and crescent'
            ),
            1412 => array(
                'U+2721',
                'star of David'
            ),
            1413 => array(
                'U+1F929',
                'star-struck'
            ),
            1414 => array(
                'U+1F689',
                'station'
            ),
            1415 => array(
                'U+1F5FD',
                'Statue of Liberty'
            ),
            1416 => array(
                'U+1F35C',
                'steaming bowl'
            ),
            1417 => array(
                'U+23F9',
                'stop button'
            ),
            1418 => array(
                'U+1F6D1',
                'stop sign'
            ),
            1419 => array(
                'U+23F1',
                'stopwatch'
            ),
            1420 => array(
                'U+1F4CF',
                'straight ruler'
            ),
            1421 => array(
                'U+1F353',
                'strawberry'
            ),
            1422 => array(
                'U+1F399',
                'studio microphone'
            ),
            1423 => array(
                'U+1F959',
                'stuffed flatbread'
            ),
            1424 => array(
                'U+2600',
                'sun'
            ),
            1425 => array(
                'U+26C5',
                'sun behind cloud'
            ),
            1426 => array(
                'U+1F325',
                'sun behind large cloud'
            ),
            1427 => array(
                'U+1F326',
                'sun behind rain cloud'
            ),
            1428 => array(
                'U+1F324',
                'sun behind small cloud'
            ),
            1429 => array(
                'U+1F31E',
                'sun with face'
            ),
            1430 => array(
                'U+1F33B',
                'sunflower'
            ),
            1431 => array(
                'U+1F576',
                'sunglasses'
            ),
            1432 => array(
                'U+1F305',
                'sunrise'
            ),
            1433 => array(
                'U+1F304',
                'sunrise over mountains'
            ),
            1434 => array(
                'U+1F307',
                'sunset'
            ),
            1435 => array(
                'U+1F363',
                'sushi'
            ),
            1436 => array(
                'U+1F69F',
                'suspension railway'
            ),
            1437 => array(
                'U+1F4A6',
                'sweat droplets'
            ),
            1438 => array(
                'U+1F54D',
                'synagogue'
            ),
            1439 => array(
                'U+1F489',
                'syringe'
            ),
            1440 => array(
                'U+1F996',
                'T-Rex'
            ),
            1441 => array(
                'U+1F455',
                't-shirt'
            ),
            1442 => array(
                'U+1F32E',
                'taco'
            ),
            1443 => array(
                'U+1F961',
                'takeout box'
            ),
            1444 => array(
                'U+1F38B',
                'tanabata tree'
            ),
            1445 => array(
                'U+1F34A',
                'tangerine'
            ),
            1446 => array(
                'U+2649',
                'Taurus'
            ),
            1447 => array(
                'U+1F695',
                'taxi'
            ),
            1448 => array(
                'U+1F375',
                'teacup without handle'
            ),
            1449 => array(
                'U+1F4C6',
                'tear-off calendar'
            ),
            1450 => array(
                'U+260E',
                'telephone'
            ),
            1451 => array(
                'U+1F4DE',
                'telephone receiver'
            ),
            1452 => array(
                'U+1F52D',
                'telescope'
            ),
            1453 => array(
                'U+1F4FA',
                'television'
            ),
            1454 => array(
                'U+1F559',
                'ten o’clock'
            ),
            1455 => array(
                'U+1F565',
                'ten-thirty'
            ),
            1456 => array(
                'U+1F3BE',
                'tennis'
            ),
            1457 => array(
                'U+26FA',
                'tent'
            ),
            1458 => array(
                'U+1F321',
                'thermometer'
            ),
            1459 => array(
                'U+1F914',
                'thinking face'
            ),
            1460 => array(
                'U+1F4AD',
                'thought balloon'
            ),
            1461 => array(
                'U+1F552',
                'three o’clock'
            ),
            1462 => array(
                'U+1F55E',
                'three-thirty'
            ),
            1463 => array(
                'U+1F44E',
                'thumbs down'
            ),
            1464 => array(
                'U+1F44D',
                'thumbs up'
            ),
            1465 => array(
                'U+1F3AB',
                'ticket'
            ),
            1466 => array(
                'U+1F405',
                'tiger'
            ),
            1467 => array(
                'U+1F42F',
                'tiger face'
            ),
            1468 => array(
                'U+23F2',
                'timer clock'
            ),
            1469 => array(
                'U+1F62B',
                'tired face'
            ),
            1470 => array(
                'U+1F6BD',
                'toilet'
            ),
            1471 => array(
                'U+1F5FC',
                'Tokyo tower'
            ),
            1472 => array(
                'U+1F345',
                'tomato'
            ),
            1473 => array(
                'U+1F445',
                'tongue'
            ),
            1474 => array(
                'U+1F51D',
                'TOP arrow'
            ),
            1475 => array(
                'U+1F3A9',
                'top hat'
            ),
            1476 => array(
                'U+1F32A',
                'tornado'
            ),
            1477 => array(
                'U+1F5B2',
                'trackball'
            ),
            1478 => array(
                'U+1F69C',
                'tractor'
            ),
            1479 => array(
                'U+2122',
                'trade mark'
            ),
            1480 => array(
                'U+1F686',
                'train'
            ),
            1481 => array(
                'U+1F68A',
                'tram'
            ),
            1482 => array(
                'U+1F68B',
                'tram car'
            ),
            1483 => array(
                'U+1F6A9',
                'triangular flag'
            ),
            1484 => array(
                'U+1F4D0',
                'triangular ruler'
            ),
            1485 => array(
                'U+1F531',
                'trident emblem'
            ),
            1486 => array(
                'U+1F68E',
                'trolleybus'
            ),
            1487 => array(
                'U+1F3C6',
                'trophy'
            ),
            1488 => array(
                'U+1F379',
                'tropical drink'
            ),
            1489 => array(
                'U+1F420',
                'tropical fish'
            ),
            1490 => array(
                'U+1F3BA',
                'trumpet'
            ),
            1491 => array(
                'U+1F337',
                'tulip'
            ),
            1492 => array(
                'U+1F943',
                'tumbler glass'
            ),
            1493 => array(
                'U+1F983',
                'turkey'
            ),
            1494 => array(
                'U+1F422',
                'turtle'
            ),
            1495 => array(
                'U+1F55B',
                'twelve o’clock'
            ),
            1496 => array(
                'U+1F567',
                'twelve-thirty'
            ),
            1497 => array(
                'U+1F495',
                'two hearts'
            ),
            1498 => array(
                'U+1F551',
                'two o’clock'
            ),
            1499 => array(
                'U+1F42B',
                'two-hump camel'
            ),
            1500 => array(
                'U+1F55D',
                'two-thirty'
            ),
            1501 => array(
                'U+2602',
                'umbrella'
            ),
            1502 => array(
                'U+26F1',
                'umbrella on ground'
            ),
            1503 => array(
                'U+2614',
                'umbrella with rain drops'
            ),
            1504 => array(
                'U+1F612',
                'unamused face'
            ),
            1505 => array(
                'U+1F984',
                'unicorn'
            ),
            1506 => array(
                'U+1F513',
                'unlocked'
            ),
            1507 => array(
                'U+2B06',
                'up arrow'
            ),
            1508 => array(
                'U+2195',
                'up-down arrow'
            ),
            1509 => array(
                'U+2196',
                'up-left arrow'
            ),
            1510 => array(
                'U+2197',
                'up-right arrow'
            ),
            1511 => array(
                'U+1F199',
                'UP! button'
            ),
            1512 => array(
                'U+1F643',
                'upside-down face'
            ),
            1513 => array(
                'U+1F53C',
                'upwards button'
            ),
            1514 => array(
                'U+1F9DB',
                'vampire'
            ),
            1515 => array(
                'U+1F6A6',
                'vertical traffic light'
            ),
            1516 => array(
                'U+1F4F3',
                'vibration mode'
            ),
            1517 => array(
                'U+270C',
                'victory hand'
            ),
            1518 => array(
                'U+1F4F9',
                'video camera'
            ),
            1519 => array(
                'U+1F3AE',
                'video game'
            ),
            1520 => array(
                'U+1F4FC',
                'videocassette'
            ),
            1521 => array(
                'U+1F3BB',
                'violin'
            ),
            1522 => array(
                'U+264D',
                'Virgo'
            ),
            1523 => array(
                'U+1F30B',
                'volcano'
            ),
            1524 => array(
                'U+1F3D0',
                'volleyball'
            ),
            1525 => array(
                'U+1F19A',
                'VS button'
            ),
            1526 => array(
                'U+1F596',
                'vulcan salute'
            ),
            1527 => array(
                'U+1F318',
                'waning crescent moon'
            ),
            1528 => array(
                'U+1F316',
                'waning gibbous moon'
            ),
            1529 => array(
                'U+26A0',
                'warning'
            ),
            1530 => array(
                'U+1F5D1',
                'wastebasket'
            ),
            1531 => array(
                'U+231A',
                'watch'
            ),
            1532 => array(
                'U+1F403',
                'water buffalo'
            ),
            1533 => array(
                'U+1F6BE',
                'water closet'
            ),
            1534 => array(
                'U+1F30A',
                'water wave'
            ),
            1535 => array(
                'U+1F349',
                'watermelon'
            ),
            1536 => array(
                'U+1F44B',
                'waving hand'
            ),
            1537 => array(
                'U+3030',
                'wavy dash'
            ),
            1538 => array(
                'U+1F312',
                'waxing crescent moon'
            ),
            1539 => array(
                'U+1F314',
                'waxing gibbous moon'
            ),
            1540 => array(
                'U+1F640',
                'weary cat'
            ),
            1541 => array(
                'U+1F629',
                'weary face'
            ),
            1542 => array(
                'U+1F492',
                'wedding'
            ),
            1543 => array(
                'U+1F40B',
                'whale'
            ),
            1544 => array(
                'U+2638',
                'wheel of dharma'
            ),
            1545 => array(
                'U+267F',
                'wheelchair symbol'
            ),
            1546 => array(
                'U+26AA',
                'white circle'
            ),
            1547 => array(
                'U+2755',
                'white exclamation mark'
            ),
            1548 => array(
                'U+1F3F3',
                'white flag'
            ),
            1549 => array(
                'U+1F4AE',
                'white flower'
            ),
            1550 => array(
                'U+2B1C',
                'white large square'
            ),
            1551 => array(
                'U+25FB',
                'white medium square'
            ),
            1552 => array(
                'U+25FD',
                'white medium-small square'
            ),
            1553 => array(
                'U+2754',
                'white question mark'
            ),
            1554 => array(
                'U+25AB',
                'white small square'
            ),
            1555 => array(
                'U+1F533',
                'white square button'
            ),
            1556 => array(
                'U+1F940',
                'wilted flower'
            ),
            1557 => array(
                'U+1F390',
                'wind chime'
            ),
            1558 => array(
                'U+1F32C',
                'wind face'
            ),
            1559 => array(
                'U+1F377',
                'wine glass'
            ),
            1560 => array(
                'U+1F609',
                'winking face'
            ),
            1561 => array(
                'U+1F61C',
                'winking face with tongue'
            ),
            1562 => array(
                'U+1F43A',
                'wolf'
            ),
            1563 => array(
                'U+1F469',
                'woman'
            ),
            1564 => array(
                'U+1F46B',
                'woman and man holding hands'
            ),
            1565 => array(
                'U+1F469 U+200D U+1F3A8',
                'woman artist'
            ),
            1566 => array(
                'U+1F469 U+200D U+1F680',
                'woman astronaut'
            ),
            1567 => array(
                'U+1F6B4 U+200D U+2640 U+FE0F',
                'woman biking'
            ),
            1568 => array(
                'U+26F9 U+FE0F U+200D U+2640 U+FE0F',
                'woman bouncing ball'
            ),
            1569 => array(
                'U+1F647 U+200D U+2640 U+FE0F',
                'woman bowing'
            ),
            1570 => array(
                'U+1F938 U+200D U+2640 U+FE0F',
                'woman cartwheeling'
            ),
            1571 => array(
                'U+1F9D7 U+200D U+2640 U+FE0F',
                'woman climbing'
            ),
            1572 => array(
                'U+1F477 U+200D U+2640 U+FE0F',
                'woman construction worker'
            ),
            1573 => array(
                'U+1F469 U+200D U+1F373',
                'woman cook'
            ),
            1574 => array(
                'U+1F483',
                'woman dancing'
            ),
            1575 => array(
                'U+1F575 U+FE0F U+200D U+2640 U+FE0F',
                'woman detective'
            ),
            1576 => array(
                'U+1F9DD U+200D U+2640 U+FE0F',
                'woman elf'
            ),
            1577 => array(
                'U+1F926 U+200D U+2640 U+FE0F',
                'woman facepalming'
            ),
            1578 => array(
                'U+1F469 U+200D U+1F3ED',
                'woman factory worker'
            ),
            1579 => array(
                'U+1F9DA U+200D U+2640 U+FE0F',
                'woman fairy'
            ),
            1580 => array(
                'U+1F469 U+200D U+1F33E',
                'woman farmer'
            ),
            1581 => array(
                'U+1F469 U+200D U+1F692',
                'woman firefighter'
            ),
            1582 => array(
                'U+1F64D U+200D U+2640 U+FE0F',
                'woman frowning'
            ),
            1583 => array(
                'U+1F9DE U+200D U+2640 U+FE0F',
                'woman genie'
            ),
            1584 => array(
                'U+1F645 U+200D U+2640 U+FE0F',
                'woman gesturing NO'
            ),
            1585 => array(
                'U+1F646 U+200D U+2640 U+FE0F',
                'woman gesturing OK'
            ),
            1586 => array(
                'U+1F487 U+200D U+2640 U+FE0F',
                'woman getting haircut'
            ),
            1587 => array(
                'U+1F486 U+200D U+2640 U+FE0F',
                'woman getting massage'
            ),
            1588 => array(
                'U+1F3CC U+FE0F U+200D U+2640 U+FE0F',
                'woman golfing'
            ),
            1589 => array(
                'U+1F482 U+200D U+2640 U+FE0F',
                'woman guard'
            ),
            1590 => array(
                'U+1F469 U+200D U+2695 U+FE0F',
                'woman health worker'
            ),
            1591 => array(
                'U+1F9D8 U+200D U+2640 U+FE0F',
                'woman in lotus position'
            ),
            1592 => array(
                'U+1F9D6 U+200D U+2640 U+FE0F',
                'woman in steamy room'
            ),
            1593 => array(
                'U+1F469 U+200D U+2696 U+FE0F',
                'woman judge'
            ),
            1594 => array(
                'U+1F939 U+200D U+2640 U+FE0F',
                'woman juggling'
            ),
            1595 => array(
                'U+1F3CB U+FE0F U+200D U+2640 U+FE0F',
                'woman lifting weights'
            ),
            1596 => array(
                'U+1F9D9 U+200D U+2640 U+FE0F',
                'woman mage'
            ),
            1597 => array(
                'U+1F469 U+200D U+1F527',
                'woman mechanic'
            ),
            1598 => array(
                'U+1F6B5 U+200D U+2640 U+FE0F',
                'woman mountain biking'
            ),
            1599 => array(
                'U+1F469 U+200D U+1F4BC',
                'woman office worker'
            ),
            1600 => array(
                'U+1F469 U+200D U+2708 U+FE0F',
                'woman pilot'
            ),
            1601 => array(
                'U+1F93E U+200D U+2640 U+FE0F',
                'woman playing handball'
            ),
            1602 => array(
                'U+1F93D U+200D U+2640 U+FE0F',
                'woman playing water polo'
            ),
            1603 => array(
                'U+1F46E U+200D U+2640 U+FE0F',
                'woman police officer'
            ),
            1604 => array(
                'U+1F64E U+200D U+2640 U+FE0F',
                'woman pouting'
            ),
            1605 => array(
                'U+1F64B U+200D U+2640 U+FE0F',
                'woman raising hand'
            ),
            1606 => array(
                'U+1F6A3 U+200D U+2640 U+FE0F',
                'woman rowing boat'
            ),
            1607 => array(
                'U+1F3C3 U+200D U+2640 U+FE0F',
                'woman running'
            ),
            1608 => array(
                'U+1F469 U+200D U+1F52C',
                'woman scientist'
            ),
            1609 => array(
                'U+1F937 U+200D U+2640 U+FE0F',
                'woman shrugging'
            ),
            1610 => array(
                'U+1F469 U+200D U+1F3A4',
                'woman singer'
            ),
            1611 => array(
                'U+1F469 U+200D U+1F393',
                'woman student'
            ),
            1612 => array(
                'U+1F3C4 U+200D U+2640 U+FE0F',
                'woman surfing'
            ),
            1613 => array(
                'U+1F3CA U+200D U+2640 U+FE0F',
                'woman swimming'
            ),
            1614 => array(
                'U+1F469 U+200D U+1F3EB',
                'woman teacher'
            ),
            1615 => array(
                'U+1F469 U+200D U+1F4BB',
                'woman technologist'
            ),
            1616 => array(
                'U+1F481 U+200D U+2640 U+FE0F',
                'woman tipping hand'
            ),
            1617 => array(
                'U+1F9DB U+200D U+2640 U+FE0F',
                'woman vampire'
            ),
            1618 => array(
                'U+1F6B6 U+200D U+2640 U+FE0F',
                'woman walking'
            ),
            1619 => array(
                'U+1F473 U+200D U+2640 U+FE0F',
                'woman wearing turban'
            ),
            1620 => array(
                'U+1F9D5',
                'woman with headscarf'
            ),
            1621 => array(
                'U+1F9DF U+200D U+2640 U+FE0F',
                'woman zombie'
            ),
            1622 => array(
                'U+1F471 U+200D U+2640 U+FE0F',
                'woman: blond hair'
            ),
            1623 => array(
                'U+1F462',
                'woman’s boot'
            ),
            1624 => array(
                'U+1F45A',
                'woman’s clothes'
            ),
            1625 => array(
                'U+1F452',
                'woman’s hat'
            ),
            1626 => array(
                'U+1F461',
                'woman’s sandal'
            ),
            1627 => array(
                'U+1F46D',
                'women holding hands'
            ),
            1628 => array(
                'U+1F46F U+200D U+2640 U+FE0F',
                'women with bunny ears'
            ),
            1629 => array(
                'U+1F93C U+200D U+2640 U+FE0F',
                'women wrestling'
            ),
            1630 => array(
                'U+1F6BA',
                'women’s room'
            ),
            1631 => array(
                'U+1F5FA',
                'world map'
            ),
            1632 => array(
                'U+1F61F',
                'worried face'
            ),
            1633 => array(
                'U+1F381',
                'wrapped gift'
            ),
            1634 => array(
                'U+1F527',
                'wrench'
            ),
            1635 => array(
                'U+270D',
                'writing hand'
            ),
            1636 => array(
                'U+1F49B',
                'yellow heart'
            ),
            1637 => array(
                'U+1F4B4',
                'yen banknote'
            ),
            1638 => array(
                'U+262F',
                'yin yang'
            ),
            1639 => array(
                'U+1F92A',
                'zany face'
            ),
            1640 => array(
                'U+1F993',
                'zebra'
            ),
            1641 => array(
                'U+1F910',
                'zipper-mouth face'
            ),
            1642 => array(
                'U+1F9DF',
                'zombie'
            ),
            1643 => array(
                'U+1F4A4',
                'zzz'
            ),
        );
        //</editor-fold>

        $rnd_index = mt_rand(0, count($emojis) - 1);

        $name = $emojis[$rnd_index][1];
        $aux = $emojis[$rnd_index][0];

        $code = str_replace('U+', '0x', $aux);

        return [
            'emoji_code' => $this->codeToSymbol($code),
            'emoji_name' => $name
        ];
    }

    function codeToSymbol($em) {
        if($em > 0x10000) {
            $first = (($em - 0x10000) >> 10) + 0xD800;
            $second = (($em - 0x10000) % 0x400) + 0xDC00;
            return json_decode('"' . sprintf("\\u%X\\u%X", $first, $second) . '"');
        } else {
            return json_decode('"' . sprintf("\\u%X", $em) . '"');
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