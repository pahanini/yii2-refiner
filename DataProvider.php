<?php
namespace pahanini\refiner;

use Yii;
use  \yii\data\ActiveDataProvider;

/**
 * Class DataProvider
 *
 * @author Pavel Tetyaev <pahanini@gmail.com>
 * @property \pahanini\refiner\Set $refinerSet
 */
class DataProvider extends ActiveDataProvider
{
    private $_refinerSet;

    public function getRefinerSet()
    {
        if (!$this->_refinerSet instanceof Set) {
            $this->_refinerSet = Yii::createObject($this->_refinerSet);
        }
        return $this->_refinerSet;
    }

    protected function prepareModels()
    {
        $this->getRefinerSet()->applyTo($this->query);
        if ($refinerSet = $this->getRefinerSet()) {
            $refinerSet->applyTo($this->query);
        }
        return parent::prepareModels();
    }

    public function setRefinerSet($val)
    {
        $this->_refinerSet = $val;
    }
}