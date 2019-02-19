<?php
/**
 * Created by PhpStorm.
 * User: Diego
 * Date: 2/2/2019
 * Time: 5:03 PM
 */

namespace FakepostBot;

use Intervention\Image\ImageManagerStatic as Image;

class TransparencyFilter implements \Intervention\Image\Filters\FilterInterface
{

    /**
     * @var string
     */
    private $PATH;
    /**
     * @var string
     */
    private $PATH_SAVE;

    /**
     * Creates new instance of filter
     *
     * @param string $path
     * @param string $to_save
     */
    public function __construct($path, $to_save)
    {
        $this->PATH = $path;
        $this->PATH_SAVE = $to_save;
    }

    /**
     * Applies filter effects to given image
     *
     * @param  \Intervention\Image\Image $img1
     * @return \Intervention\Image\Image
     */
    public function applyFilter(\Intervention\Image\Image $img1)
    {

        $path = $this->PATH;
        $to_save = $this->PATH_SAVE;

        // create Image from file and set transparency to 50%
        /** @var \Intervention\Image\Image $img2 */
        $img2 = Image::make($path);
        $w1 = $img1->getWidth();
        $h1 = $img1->getHeight();
        $w2 = $img2->getWidth();
        $h2 = $img2->getHeight();

        /* @see Note: Performance intensive on larger images. Use with care. */
        $aux = 300;
        if ($w2 > $h2) {
            $img2->resize($aux, null, function ($constraint) {
                /** @var \Intervention\Image\Constraint $constraint */
                $constraint->aspectRatio();
            });
        } else {
            $img2->resize(null, $aux, function ($constraint) {
                /** @var \Intervention\Image\Constraint $constraint */
                $constraint->aspectRatio();
            });
        }
        $img2->opacity(50);
        $img2->resize($w1, $h1)->save();


        $img1->insert($img2, 'center');

        $img1->save($to_save);

        return $img1;
    }
}
