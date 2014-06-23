<?php
namespace pahanini\refiner;

class Helper
{
    /**
     * Expands nested arrays values
     *
     * @param array $array
     * @param array $columns list of columns to expand
     * @return array;
     */
    public static function expand($array, $columns)
    {
        foreach ($array as $key => $value) {
            foreach ($columns as $rule) {
                if (array_key_exists($rule, $value) && is_array($value[$rule])) {
                    $array[$key] = array_merge($array[$key], $value[$rule]);
                    unset($array[$key][$rule]);
                }
            }
        }
        return $array;
    }


    /**
     * Merges two arrays using specified $on values
     *
     * @param array $array1
     * @param array $array2
     * @param array $on
     * @return array
     */
    public static function merge($array1, $array2, $on)
    {
        list($on1, $on2) = each($on);
        $array2 = \yii\helpers\ArrayHelper::index($array2, $on2);
        foreach ($array1 as $k1 => $v1) {
            if (!array_key_exists($on1, $v1)) {
                continue;
            }
            $k2 = $v1[$on1];
            if (isset($array2[$k2])) {
                $v2 = $array2[$k2];
                $array1[$k1] = array_merge($v1, $v2);
                unset($array2[$k2]);
            }
        }
        foreach ($array2 as $v2) {
            $array1[] = $v2;
        }
        return $array1;
    }


    /**
     * Renames or deleted values of array
     */
    public static function rename($array, $rules)
    {
        foreach ($array as $key => $value) {
            foreach ($rules as $rKey => $rValue) {
                if (array_key_exists($rKey, $value)) {
                    if ($rValue !== false) {
                        $array[$key][$rValue] = $value[$rKey];
                    }
                    unset($array[$key][$rKey]);
                }
            }
        }
        return $array;
    }
}