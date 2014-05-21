<?php
namespace pahanini\refiner;

use Yii;
use yii\db\Expression;

/**
 * SphinxJsonBool refiner
 * @author Pavel Tetyaev <pahanini@gmail.com>
 */
class SphinxJsonBool extends SphinxJsonAttr
{
    public function init()
    {
        parent::init();
        $this->all = null;
        $this->active = [$this, 'active'];
    }

    public function refine(\yii\sphinx\Query $query, $params)
    {
        if ($params) {
            $query->andWhere("{$this->attrName}.{$this->name} = 1");
        }
        return $query;
    }

    public function active(\yii\sphinx\Query $query)
    {
        return $query->select('COUNT(*) as c')->andWhere("{$this->attrName}.{$this->name} = 1");
    }

    public function merge($all, $active)
    {
        return isset($active[0]['c']) ? (int)$active[0]['c'] : 0;
    }
}