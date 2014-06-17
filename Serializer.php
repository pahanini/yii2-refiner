<?php
namespace pahanini\refiner;

/**
 * Serializer
 *
 * @author Pavel Tetyaev <pahanini@gmail.com>
 */
class Serializer extends \yii\rest\Serializer
{
    public $node = 'refiners';

    public function serialize($data)
    {
        $result = parent::serialize($data);
        if ($data instanceof DataProvider) {
            $result[$this->node] = $data->refinerSet->getRefinerValues();
        }
        return $result;
    }

} 