<?php
namespace pahanini\refiner;
use Yii;

/**
 * Serializer
 *
 * @author Pavel Tetyaev <pahanini@gmail.com>
 */
class Serializer extends \yii\rest\Serializer
{
    /**
     * Serialized response node to add refiner value. If this set to null no data will be added
     *
     * @var null|string
     */
    public $refinerValuesNodeName = 'refiners';

    /**
     * @var string
     */
    public $refinerValuesNodeParam = 'refiners';

    /**
     * @return bool
     */
    private function isRefinerValuesNodeRequired()
    {
        return $this->refinerValuesNodeName && ($this->refinerValuesNodeParam === null || Yii::$app->getRequest()->get(
                $this->refinerValuesNodeParam
            ));
    }

    protected  function serializeDataProvider($dataProvider)
    {
        $result = parent::serializeDataProvider($dataProvider);
        if ($this->isRefinerValuesNodeRequired()) {
            $result[$this->refinerValuesNodeName] = $dataProvider->refinerSet->getRefinerValues();
        }
        return $result;
    }

}

