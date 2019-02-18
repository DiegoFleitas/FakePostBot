<?php
/**
 * Created by PhpStorm.
 * User: Diego
 * Date: 2/2/2019
 * Time: 5:03 PM
 */

class MirrorFilter implements Intervention\Image\Filters\FilterInterface
{

    /**
     * If need to be fixed
     *
     * @var bool
     */
    private $FIX;
    /**
     *
     * @var string
     */
    private $SAVE;

    /**
     * Creates new instance of filter
     *
     * @param string $save_to
     * @param bool $fix
     */
    public function __construct($save_to, $fix)
    {
        $this->SAVE = $save_to;
        $this->FIX = $fix;
    }

    /**
     * Applies filter effects to given image
     *
     * @param  Intervention\Image\Image $image
     * @return Intervention\Image\Image
     */
    public function applyFilter(\Intervention\Image\Image $image)
    {
        // backup
        $image->backup();

        // trim image (only top and bottom with transparency)
        $image->trim('transparent', array('top', 'bottom'));

        //flip horizontally
        $image->flip('h');

        //cortarla a la mitad
        $w = $image->getWidth()/2;
        $h = $image->getHeight();
        $image->crop(floor($w), $h, 0, 0);

        $mirrored = 'C:\Users\Diego\PhpstormProjects\FakePostBot\src\Bot\resources\newBot\half.png';
        $image->save($mirrored);

        // reset image (return to backup state)
        $image->reset();

        //TODO: report this bug? Y coordinates won't work on these but will work using top-left https://github.com/Intervention/image/issues
//        $image->insert($mirrored, 'left', 0, 100);
//        $image->insert($mirrored, 'center', 0, 64);
        if ($this->FIX) {
            $image->insert($mirrored, 'top-left', 0, 64);
        } else {
            $image->insert($mirrored, 'left');
        }

        $image->save($this->SAVE);

        return $image;
    }
}
