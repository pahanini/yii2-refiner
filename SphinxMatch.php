<?php
namespace pahanini\refiner;

use Yii;

/**
 * SphinxMatch refiner
 * @author Pavel Tetyaev <pahanini@gmail.com>
 */
class SphinxMatch extends Base
{
    private $_params;

    public function init()
    {
        parent::init();
        $this->refine = [$this, 'refine'];
    }

    public function refine($query, $params)
    {
        $this->_params = $params;
        return $query->match($params);
    }

    public function getValue()
    {
        return $this->_params;
    }
}