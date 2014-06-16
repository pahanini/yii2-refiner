<?php
namespace pahanini\refiner\sphinx;

/**
 * Sphinx Range refiner
 * @author Pavel Tetyaev <pahanini@gmail.com>
 */
class SphinxRange extends \pahanini\refiner\common\Range
{
    public function init()
    {
        parent::init();
        // params converted to in due https://github.com/yiisoft/yii2/issues/3513
        $this->paramType = 'int';
    }
}