<?php
/**
 * Created by PhpStorm.
 * User: Diego
 * Date: 2/22/2019
 * Time: 12:56 AM
 */

namespace FakepostBot;

use Intervention\Image\ImageManagerStatic as Image;

class UsBotFilter implements \Intervention\Image\Filters\FilterInterface
{

    /**
     *
     * @var string
     */
    private $SAVE;

    /**
     *
     * @var array
     */
    private $POLL;

    /**
     *
     * @var string
     */
    private $CANDIDATE1;

    /**
     *
     * @var string
     */
    private $CANDIDATE2;

    /**
     *
     * @var string
     */
    private $YEAR;

    /**
     * Creates new instance of filter
     *
     * @param string $save_to
     * @param array $data
     */
    public function __construct($save_to, $data)
    {
        $this->SAVE = $save_to;
        $this->POLL = $data['result'];
        $this->CANDIDATE1 = $data['candidate_dem'];
        $this->CANDIDATE2 = $data['candidate_rep'];
        $this->YEAR = $data['year'];
    }

    /**
     * Applies filter effects to given image
     *
     * @param  \Intervention\Image\Image $image
     * @return \Intervention\Image\Image
     */
    public function applyFilter(\Intervention\Image\Image $image)
    {

        $states = [
            'alabama',
            'alaska',
            'arizona',
            'arkansas',
            'california',
            'colorado',
            'connecticut',
            'delaware',
            'dc',
            'florida',
            'georgia',
            'hawaii',
            'idaho',
            'illinois',
            'indiana',
            'iowa',
            'kansas',
            'kentucky',
            'lousiana',
            'maine',
            'maryland',
            'massachussetts',
            'michigan',
            'minnesota',
            'mississippi',
            'missouri',
            'montana',
            'nebraska',
            'nevada',
            'new_hampshire',
            'new_jersey',
            'new mexico',
            'new_york',
            'n_carolina',
            'n_dakota',
            'ohio',
            'oklahoma',
            'oregon',
            'pennsylvania',
            'rhode_i',
            's_carolina',
            's_dakota',
            'tenesse',
            'texas',
            'utah',
            'vermont',
            'virginia',
            'washington',
            'w_virginia',
            'wisconsin',
            'wyoming'
        ];

        $blues = [];
        $reds = [];
        foreach ($this->POLL as $key => $state_res) {
            $path2 = 'C:\Users\Diego\PhpstormProjects\FakePostBot\src\resources\us_election\\'.$key.'.png';
            /** @var \Intervention\Image\Image $img2 */
            $img2 = Image::make($path2);

            if ($state_res == 'red') {
                // red #c84a4a
                array_push($reds, $key);
                $img2->colorize(100, -70, -70);
            } else {
                // blue #4a4ac8
                $img2->colorize(-70, -70, 100);
                array_push($blues, $key);
            }
            $image->insert($img2);
        }

        $barpath = 'C:\Users\Diego\PhpstormProjects\FakePostBot\src\resources\us_election\bar.png';
        /** @var \Intervention\Image\Image $bar */
        $bar = Image::make($barpath);
        $image->insert($bar);

        $offset = 11;
        $end = 702;
        $actual = $end - $offset;
        $blue_percentage = floor(count($blues) /  count($states)* 100);
        $blue_part = floor($blue_percentage * $actual / 100);

        // draw filled blue rectangle, starts fron beginning
        /** @var \Intervention\Image\Image $image */
        $image->rectangle($offset, 13, $blue_part, 33, function ($draw) {
            /** @var \Intervention\Image\Imagick\Shapes\RectangleShape $draw */
            $draw->background('#4c4cff');
        });
        // draw filled red rectangle, starts where blue ends
        /** @var \Intervention\Image\Image $image */
        $image->rectangle($blue_part, 38, $end, 58, function ($draw) {
            /** @var \Intervention\Image\Imagick\Shapes\RectangleShape $draw */
            $draw->background('#ff4c4c');
        });

        // Candidate 1
        $text = $this->CANDIDATE1;
        $image->text($text, 715, 30, function ($font) {
            /** @var \Intervention\Image\Imagick\Font $font */
            $font->file('C:\Users\Diego\PhpstormProjects\FakePostBot\src\resources\fonts\lucida');
            $font->size(20);
            $font->color('#000000');
        });
        // Candidate 2
        $text2 = $this->CANDIDATE2;
        $image->text($text2, 715, 55, function ($font) {
            /** @var \Intervention\Image\Imagick\Font $font */
            $font->file('C:\Users\Diego\PhpstormProjects\FakePostBot\src\resources\fonts\lucida');
            $font->size(20);
            $font->color('#000000');
        });

        $yearpath = 'C:\Users\Diego\PhpstormProjects\FakePostBot\src\resources\us_election\year.png';
        /** @var \Intervention\Image\Image $year */
        $year_template = Image::make($yearpath);
        $image->insert($year_template);

        $year = $this->YEAR;
        $image->text($year, 770, 675, function ($font) {
            /** @var \Intervention\Image\Imagick\Font $font */
            $font->file('C:\Users\Diego\PhpstormProjects\FakePostBot\src\resources\fonts\impact.ttf');
            $font->size(70);
            $font->color('#000000');
//            $font->align('right');
//            $font->valign('bottom');
        });

        $path3 = 'C:\Users\Diego\PhpstormProjects\FakePostBot\src\resources\us_election\text.png';
        /** @var \Intervention\Image\Image $img3 */
        $img3 = Image::make($path3);
        $image->insert($img3);

        $image->save($this->SAVE);

        return $image;
    }
}