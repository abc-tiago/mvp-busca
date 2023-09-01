<?php

namespace App\Libraries;

class ArrayUtils
{
    public const COMPARE_STATUS_DELETE = 'DELETE';
    public const COMPARE_STATUS_INSERT = 'INSERT';
    public const COMPARE_STATUS_UPDATE = 'UPDATE';
    public const COMPARE_STATUS_UPDATE_DIFFS = 'UPDATE_DIFFS';

    public static function columnToIndex($array, $indexes)
    {
        $ret = [];
        if (!empty($array)) {
            if (is_string($indexes)) {
                $indexes = [$indexes];
            }
            if (is_array($array[array_key_first($array)])) {
                switch (count($indexes)) {
                    case 1:
                        foreach ($array as $arr) {
                            $ret[$arr[$indexes[0]]] = $arr;
                        }
                        break;
                    case 2:
                        foreach ($array as $arr) {
                            $ret[$arr[$indexes[0]] . '-' . $arr[$indexes[1]]] = $arr;
                        }
                        break;
                    case 3:
                        foreach ($array as $arr) {
                            $ret[$arr[$indexes[0]] . '-' . $arr[$indexes[1]] . '-' . $arr[$indexes[2]]] = $arr;
                        }
                        break;
                }
            } else {
                switch (count($indexes)) {
                    case 1:
                        foreach ($array as $arr) {
                            $ret[$arr->{$indexes[0]}] = $arr;
                        }
                        break;
                    case 2:
                        foreach ($array as $arr) {
                            $ret[$arr->{$indexes[0]} . '-' . $arr->{$indexes[1]}] = $arr;
                        }
                        break;
                    case 3:
                        foreach ($array as $arr) {
                            $ret[$arr->{$indexes[0]} . '-' . $arr->{$indexes[1]} . '-' . $arr->{$indexes[2]}] = $arr;
                        }
                        break;
                }
            }
        }
        return $ret;

        //     if (!empty($array)) {

        //         if (is_array($array[array_key_first($array)])) {
        //             foreach ($array as $k => $r) {
        //                 $rsp[$r[$index]] = $r;
        //             }
        //         } else {
        //             foreach ($array as $k => $r) {
        //                 $rsp[$r->{$index}] = $r;
        //             }
        //         }
        //         unset($array[$k]);
        //     }
        // } elseif (count($index) == 2) {
        //     if (!empty($array)) {
        //         if (is_array($array[array_key_first($array)])) {
        //             foreach ($array as $k => $r) {
        //                 $rsp[$r[$index[0]]][$r[$index[1]]] = $r;
        //             }
        //         } else {
        //             foreach ($array as $k => $r) {
        //                 $rsp[$r->{$index[0]}][$r->{$index[1]}] = $r;
        //             }
        //         }
        //         unset($array[$k]);
        //     }
        // }

        // return $rsp;
    }

    public static function collapse(&$array, $index)
    {
        $rsp = [];

        if (is_string($index)) {
            if (!empty($array)) {
                if (is_array($array[0])) {
                    foreach ($array as $k => $r) {
                        $rsp[$r[$index]][] = $r;
                    }
                } else {
                    foreach ($array as $k => $r) {
                        $rsp[$r->{$index}][] = $r;
                    }
                }
                unset($array[$k]);
            }
        } elseif (count($index) == 2) {
            if (!empty($array)) {
                if (is_array($array[0])) {
                    foreach ($array as $k => $r) {
                        $rsp[$r[$index[0]]][$r[$index[1]]][] = $r;
                    }
                } else {
                    foreach ($array as $k => $r) {
                        $rsp[$r->{$index[0]}][$r->{$index[1]}][] = $r;
                    }
                }
                unset($array[$k]);
            }
        }

        return $rsp;
    }

    public static function compareByIndex($new, $old, $keys)
    {
        $new = self::columnToIndex($new, $keys);
        $old = self::columnToIndex($old, $keys);
        $ret = [
            self::COMPARE_STATUS_DELETE => [],
            self::COMPARE_STATUS_INSERT => [],
            self::COMPARE_STATUS_UPDATE => [],
            self::COMPARE_STATUS_UPDATE_DIFFS => [],
        ];

        foreach ($new as $new_key => $new_value) {
            if (!empty($old[$new_key])) {
                $diffs = [];
                foreach ($old[$new_key] as $columnName => $columnValue) {
                    if (isset($new[$new_key]->{$columnName})
                        && $new[$new_key]->{$columnName} != $old[$new_key]->{$columnName}) {
                        $diffs[$columnName] = (object) [
                            'new' => $new[$new_key]->{$columnName},
                            'old' => $old[$new_key]->{$columnName},
                        ];
                    }
                }
                if (!empty($diffs)) {
                    $ret[self::COMPARE_STATUS_UPDATE][$new_key] = $new_value;
                    $ret[self::COMPARE_STATUS_UPDATE_DIFFS][$new_key] = $diffs;
                }
            } else {
                $ret[self::COMPARE_STATUS_INSERT][$new_key] = $new_value;
            }
        }

        foreach ($old as $old_key => $old_value) {
            if (empty($new[$old_key])) {
                $ret[self::COMPARE_STATUS_DELETE][$old_key] = $old_value;
            }
        }

        return $ret;
    }

    public static function simpleCompareInsersect(&$new, &$old, $index)
    {
        $diff = [];

        // dump('$new: ' . count($new) . ' $old: ' . count($old));
        if (!empty($new) || !empty($old)) {
            $new = self::columnToIndex($new, $index);
            $old = self::columnToIndex($old, $index);

            $indexes_intersect = array_intersect(
                array_column($new, $index),
                array_column($old, $index)
            );
            if (!empty($indexes_intersect)) {
                $columns_new = array_keys((array) $new[array_key_first($new)]);
                $columns_old = array_keys((array) $old[array_key_first($old)]);

                if (array_intersect($columns_new, $columns_old) == $columns_new) {

                    // $indexes_intersect = array_intersect(
                    //     array_column($new, $index),
                    //     array_column($old, $index)
                    // );
                    foreach ($indexes_intersect as $index) {
                        foreach ($columns_new as $column) {
                            if ($new[$index]->{$column} != $old[$index]->{$column}) {
                                dump($index . '(' . $column . ') => erp:' . $new[$index]->{$column} . ' - local:' . $old[$index]->{$column});
                                $diff[$index] = $new[$index];
                                unset($new[$index]);
                                unset($old[$index]);
                                break;
                            }
                        }
                    }
                } else {
                    throw new \Exception('Different number os columns');
                }
            }
        }

        return $diff;
    }

    public static function simpleCompareAdded(&$new, &$old, $index)
    {
        $diff = [];

        dump('$new: ' . count($new) . ' $old: ' . count($old));
        // if (!empty($new) || !empty($old)) {
        $new = self::columnToIndex($new, $index);
        $old = self::columnToIndex($old, $index);

        $new_indexes = array_column($new, $index);
        $old_indexes = array_column($old, $index);

        $new_items = array_diff($new_indexes, $old_indexes);

        foreach ($new_items as $index) {
            // dump($index);
            $diff[$index] = $new[$index];
            unset($new[$index]);
        }
        // }

        return $diff;
    }

    public static function filterArrayAll(&$array, $index, $value)
    {
        if (empty($array)) {
            return [];
        }

        return array_filter($array, function($v, $k) use($index, $value) {

            if(is_array($v)){
                $i = $v[$index];
            } else {
                $i = $v->$index;
            }

            return $i == $value;
        }, ARRAY_FILTER_USE_BOTH);
    }

    public static function convertToArray($result) {
        return json_decode(json_encode($result), true);
    }

    public static function objectToArray($result) {
        return json_decode(json_encode($result), FALSE);
    }

    public static function arrayToObject($result) {
        return json_decode(json_encode($result), FALSE);
    }

    public static function groupByIndexArray(&$array, $index) {
        $arr = [];
        foreach ($array as $item) {
            $arr1 = [];
            foreach($index as $row) {
                if(isset($item->$row)) {
                    $arr1[$row] = $item->$row;
                }
            }
            $arr[] = $arr1;
        }

        return self::arrayToObject($arr);
    }

    public static function group_by($data, $key) {
        $result = array();
        // dd($data);
        foreach($data as $val) {
            if(!is_array($val)) {
                $val = self::convertToArray($val);
            }

            if(array_key_exists($key, $val)){
                $result[$val[$key]][] = self::arrayToObject($val);
            } else {
                $result[""][] = $val;
            }
        }
        return $result;
    }

    public static function group_by2($data, $key) {
        /* esse pega dois niveis de array */
        $result = array();
        // dd($data);
        foreach($data as $val1) {
            if(!is_array($val1)) {
                $val1 = self::convertToArray($val1);
            }

            foreach($val1 as $val) {
                $val = self::convertToArray($val);
                if(array_key_exists($key, $val)){
                    $result[$val[$key]][] = $val;
                    // $result[$val[$key]][] = self::arrayToObject($val);
                } else {
                    $result[""][] = $val;
                }
            }
        }
        return $result;
    }

    public static function arrayLike(array $arr, string $index, string $key = null): array
    {
        return array_filter($arr, function(mixed $item) use ($index, $key): bool {
            return 1 === preg_match(sprintf('/^%s$/i', preg_replace('/(^%)|(%$)/', '.*', $index)), $key ? $item->$key : $item);
        });
    }

    public static function arraySort($arr, string $col, string $type = 'ASC')
    {
        $sort = array();
        foreach ($arr as $i => $obj) {
            $sort[$i] = $obj->{$col};
        }

        $type = $type == 'ASC' ? SORT_ASC : SORT_DESC;
        array_multisort($sort, $type, $arr);
        return $arr;
    }


    public static function arraySort2($array, $on, $order=SORT_ASC)
    {
        $new_array = array();
        $sortable_array = array();

        if (count($array) > 0) {
            foreach ($array as $k => $v) {
                if (is_array($v)) {
                    foreach ($v as $k2 => $v2) {
                        if ($k2 == $on) {
                            $sortable_array[$k] = $v2;
                        }
                    }
                } else {
                    $sortable_array[$k] = $v;
                }
            }

            switch ($order) {
                case SORT_ASC:
                    asort($sortable_array);
                break;
                case SORT_DESC:
                    arsort($sortable_array);
                break;
            }

            foreach ($sortable_array as $k => $v) {
                $new_array[$k] = $array[$k];
            }
        }

        return $new_array;
    }

    public static function arrayToColumnValueAll(&$array, $index, $value)
    {
        $rsp = [];
        if (!empty($array)) {
            $handleArray = array_filter($array, function($item) use ($index, $value) {
                if(is_array($item)){
                    $i = $item[$index];
                } else {
                    $i = $item->$index;
                }
                return $i == $value;
            });
            return current(array_values($handleArray));
        }
        return $rsp;
    }

    public static function arrayToIndex(array $array, string $index) {

        if(empty($array)){
            return [];
        }

        $arr = [];
        foreach($array as $item) {
            if(is_array($item)){
                $arr[] = $item[$index];
            } else {
                $arr[] = $item->$index;
            }
        }

        return $arr;
    }


    public static function allArrayAllColumnValue(&$array, $index, $value)
    {
        $rsp = [];
        if (!empty($array) && !empty($index)) {

            $handleArray = array_filter($array, function($item) use ($index, $value) {

                // maximo dois items
                $index1 = (string)$index[0];
                $index2 = (string)$index[1];
                if(is_array($item)){
                    $i1 = $item[$index1];
                    $i2 = $item[$index2];
                } else {
                    $i1 = $item->$index1;
                    $i2 = $item->$index2;
                }

                return $i1 <= $value  && $i2 >= $value;
            });

            return current(array_values($handleArray));
        }

        return $rsp;
    }


}
