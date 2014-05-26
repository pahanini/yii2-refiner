<?php
namespace pahanini\refiner;

use Yii;
use yii\db\Exception;
use yii\db\Expression;

/**
 * SphinxJsonInt refiner
 * @author Pavel Tetyaev <pahanini@gmail.com>
 */
class SphinxJsonInt extends SphinxJsonAttr
{
    public function __construct($config = [])
    {
        if (!array_key_exists('all', $config)) {
            $config['all'] = [$this, 'all'];
        }
        if (!array_key_exists('active', $config)) {
            $config['active'] = [$this, 'active'];
        }
        if (!array_key_exists('on', $config)) {
            $config['on'] = ['_id' => '_id'];
        }
        parent::__construct($config);
    }

    public function active(\yii\sphinx\Query $query)
    {
        return $this->query($query);
    }

    public function all(\yii\sphinx\Query $query)
    {
        return $this->query($query);
    }

    public function query(\yii\sphinx\Query $query)
    {
        $tmp = $this->attrName . '.' . $this->name;
        return $query
            ->select([new Expression($tmp . ' as _id'), 'COUNT(*) as count'])
            ->andWhere(new Expression("$tmp IS NOT NULL"))
            ->groupBy([new Expression($tmp)]);
    }

    public function refine(\yii\sphinx\Query $query, $params)
    {
        if (!is_array($params)) {
            $params = [$params];
        }
        $tmp = $this->attrName . '.' . $this->name;
        $query->andWhere(new Expression("$tmp IN (" . join(',', $params) . ")"));
        return $query;
    }
}