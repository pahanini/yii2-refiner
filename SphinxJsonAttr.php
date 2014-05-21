<?php
namespace pahanini\refiner;

use Yii;
use yii\db\Expression;

/**
 * SphinxJsonAttr refiner
 * @author Pavel Tetyaev <pahanini@gmail.com>
 */
abstract class SphinxJsonAttr extends Base
{
    public $attrName = 'attr';

    public function init()
    {
        parent::init();
        $this->refine = [$this, 'refine'];
    }

    abstract public function refine(\yii\sphinx\Query $query, $params);
}