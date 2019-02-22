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
     * Creates new instance of filter
     *
     * @param string $save_to
     */
    public function __construct($save_to)
    {
        $this->SAVE = $save_to;
    }

    /**
     * Applies filter effects to given image
     *
     * @param  \Intervention\Image\Image $image
     * @return \Intervention\Image\Image
     */
    public function applyFilter(\Intervention\Image\Image $image)
    {

        // trim image (only top and bottom with transparency)
//        $image->trim('transparent', array('top', 'bottom'));

        $states = [
            'alabama',
            'alaska',
            'arizona',
            'arkansas',
            'california',
            'colorado',
            'connecticut',
            'dc',
            'delaware',
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
            'n_carolina',
            'n_dakota',
            'nebraska',
            'nevada',
            'new mexico',
            'new_hampshire',
            'new_jersey',
            'new_york',
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
            'w_virginia',
            'washington',
            'wisconsin',
            'wyoming'
        ];

        foreach ($states as $state) {
            $path2 = 'C:\Users\Diego\PhpstormProjects\FakePostBot\src\resources\us_election\\'.$state.'.png';
            /** @var \Intervention\Image\Image $img2 */
            $img2 = Image::make($path2);
            // 50% chance
            if (mt_rand(0, 1)) {
                // red
                $img2->colorize(100, -100, -100);
            } else {
                // blue
                $img2->colorize(-100, -100, 100);
            }
            $image->insert($img2);
        }

        $path3 = 'C:\Users\Diego\PhpstormProjects\FakePostBot\src\resources\us_election\text.png';
        /** @var \Intervention\Image\Image $img3 */
        $img3 = Image::make($path3);
        $image->insert($img3);

        $image->save($this->SAVE);

        return $image;
    }
}