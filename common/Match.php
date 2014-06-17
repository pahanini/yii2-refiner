<?php
namespace pahanini\refiner\common;

/**
 * Common match refiner
 * @author Pavel Tetyaev <pahanini@gmail.com>
 */
class Match extends Base
{
    public $paramsToArray = true;

    protected $params = [];

    public function init()
    {
        parent::init();
        if (!$this->refine) {
            $this->refine = [$this, 'refine'];
        }
    }

    public function refine($query, $params)
    {
        $this->params = $params;
        return $query;
    }

    public function getValues()
    {
        return $this->params;
    }
}