<?php
namespace pahanini\refiner\db;

/**
 * Db match (like) refiner
 * @author Pavel Tetyaev <pahanini@gmail.com>
 */
class Match extends \pahanini\refiner\common\Match
{
    /**
     * @param \yii\db\QueryInterface $query
     * @param array $params
     * @return \yii\db\QueryInterface
     */
    public function refine($query, $params)
    {
        $where = ['or'];
        $bind = [];
        foreach($params as $key => $val) {
            $name = ":{$this->name}_{$key}";
            $where[] = "{$this->columnName} LIKE $name";
            $bind[$name] = $val . '%';
        }
        $query->andWhere($where, $bind);
        return parent::refine($query, $params);
    }
}