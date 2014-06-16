<?php
namespace pahanini\refiner\common;

/**
 * Common match refiner
 * @author Pavel Tetyaev <pahanini@gmail.com>
 */
class Match extends Base
{
    public $paramsToArray = true;

    public function init()
    {
        parent::init();
        if (!$this->refine) {
            $this->refine = [$this, 'refine'];
        }
    }
}