<?php
namespace pahanini\refiner\sphinx;

use Yii;

/**
 * Sphinx match refiner
 * @author Pavel Tetyaev <pahanini@gmail.com>
 */
class Match extends \pahanini\refiner\common\Match
{
    private $_params;

    public function init()
    {
        parent::init();
        if (!$this->refine) {
            $this->refine = [$this, 'refine'];
        }
    }

    public function refine($query, $params)
    {
        $this->_params = $params;
        return $query->match($params);
    }

    public function getValues($query)
    {
        return $this->_params;
    }
}