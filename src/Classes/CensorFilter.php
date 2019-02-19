<?php
/**
 * Created by PhpStorm.
 * User: Diego
 * Date: 2/2/2019
 * Time: 5:03 PM
 */

namespace FakepostBot;

class CensorFilter implements \Intervention\Image\Filters\FilterInterface
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
     * @param  Intervention\Image\Image $image
     * @return Intervention\Image\Image
     */
    public function applyFilter(\Intervention\Image\Image $image)
    {

        // trim image (only top and bottom with transparency)
        $image->trim('transparent', array('top', 'bottom'));

        $width = $image->getWidth();
        $height = $image->getHeight();

        // random
        $posx1 = mt_rand(0, $width);
        $posy1 = mt_rand(0, $height);
        $posx2 = mt_rand(0, $width);
        $posy2 = mt_rand(0, $height);

        // draw filled red rectangle
        /** @var Intervention\Image\Image $image */
        $image->rectangle($posx1, $posy1, $posx2, $posy2, function ($draw) {
            /** @var \Intervention\Image\Imagick\Shapes\RectangleShape $draw */
            $draw->background('#ff0000');
        });

        $image->save($this->SAVE);

        return $image;
    }

}