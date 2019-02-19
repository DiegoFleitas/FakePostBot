<?php
/**
 * Created by PhpStorm.
 * User: Diego
 * Date: 2/12/2019
 * Time: 8:47 PM
 */

namespace FakepostBot\Tests;

use PHPUnit\Framework\TestCase;

require __DIR__ .'/../vendor/autoload.php';
require_once '../src/resources/secrets.php';

class MixTest extends TestCase
{

    public function testAllFakePost()
    {
        $bot_pool = [
            'StyletransferBot9683',
            'ArtPostBot 1519',
            'Botob 8008',
            'InspiroBot Quotes',
            'CensorBot 1111',
            'EmojiBot 101',
            'CountryBot 0208'
        ];

        $Mimick = new \FakepostBot\MimickBot();

        foreach ($bot_pool as $bot) {
            $result = $Mimick->fakePost($bot);

            $this->assertArrayHasKey('image', $result, $bot.' missing image');
            $this->assertArrayHasKey('title', $result, $bot.' missing title');
            $this->assertArrayHasKey('bot_link', $result, $bot.' missing bot_link');
            $this->assertArrayHasKey('comment', $result, $bot.' missing comment');

            $info = $Mimick->getBotInfo($bot);

            $this->assertArrayHasKey('type', $info, $bot.' missing type');
            $this->assertArrayHasKey('own_title', $info, $bot.' missing own_title');
            $this->assertArrayHasKey('own_comment', $info, $bot.' missing own_comment');
            $this->assertArrayHasKey('needs_base_image', $info, $bot.' missing needs_base_image');

            $this->assertNotEmpty($result['image'], $bot.' missing image');
            $this->assertNotEmpty($result['bot_link'], $bot.' missing bot_link');
            if ($info['own_title']) {
                $this->assertNotEmpty($result['title'], $bot.' missing title');
            }
            if ($info['own_comment']) {
                $this->assertNotEmpty($result['comment'], $bot.' missing comment');
            }
        }
    }

    public function testAllMixFakePost()
    {

//        ini_set('memory_limit', '256M');
        // Do your Intervention/image operations...

        $bot_pool = [
//            'StyletransferBot9683',
            'ArtPostBot 1519',
            'Botob 8008',
            'InspiroBot Quotes',
            'CensorBot 1111',
            'EmojiBot 101',
            'CountryBot 0208'
        ];

        // So tests end up the same but start different every time
//        shuffle($bot_pool);


        $Mimick = new \FakepostBot\MimickBot();

        $counter = 0;
        foreach ($bot_pool as $key => $bot1) {
            $aux = $bot_pool;
            // remove it so it doesn't gets chosen again
            unset($aux[$key]);
            //FIXME iteration not right
            foreach ($aux as $bot2) {
                //backup means save it on test/mixing
                $result = $Mimick->fakePost($bot1, $bot2, true);

                $bot1_info = $Mimick->getBotInfo($bot1);
                $bot2_info = $Mimick->getBotInfo($bot2);

                $MixBot = new \FakepostBot\MixBot();
                $strat = $MixBot->getMixingStrategy($bot1_info, $bot2_info);
                $message = $bot1.' mixed with '.$bot2.' using: '.$strat;

                $this->assertArrayHasKey('image', $result, $message.' missing image');
                $this->assertArrayHasKey('title', $result, $message.' missing title');
                $this->assertArrayHasKey('bot_link', $result, $message.' missing bot_link');
                $this->assertArrayHasKey('comment', $result, $message.' missing comment');

                $counter++;
            }
        }

//        When the order does matter it is a Permutation.
//        n objects taking r samples:
//                        n!
//            P(n,r) = ---------
//                      (n-r)!
        $n = count($bot_pool);
        $r = 2;
        $permutations = gmp_intval(gmp_fact($n)/ gmp_fact($n - $r));

        $this->assertEquals($permutations, $counter);
    }
}
