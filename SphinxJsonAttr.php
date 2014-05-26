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

    public function __construct($config = [])
    {
        if (!array_key_exists('refine', $config)) {
            $config['refine'] = [$this, 'refine'];
        }
        parent::__construct($config);
    }

    abstract public function refine(\yii\sphinx\Query $query, $params);
}