<?php
namespace pahanini\refiner\sphinx;

use Yii;

/**
 * Sphinx match refiner
 * @author Pavel Tetyaev <pahanini@gmail.com>
 */
class Match extends \pahanini\refiner\common\Match
{
    public function init()
    {
        parent::init();
        if (!$this->refine) {
            $this->refine = [$this, 'refine'];
        }
    }

    public function refine($query, $params)
    {
        $query->match($params);
        return parent::refine($query, $params);
    }
}