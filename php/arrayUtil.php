<?php

class arrayUtil
{
    static function array_sort($arr,$keys,$type='asc'){

        $keysvalue = $new_array = array();

        foreach ($arr as $k=>$v){

            $keysvalue[$k] = $v[$keys];

        }

        if($type == 'asc'){

            asort($keysvalue);

        }else{

            arsort($keysvalue);

        }

        reset($keysvalue);

        foreach ($keysvalue as $k=>$v){

            $new_array[$k] = $arr[$k];

        }

        return $new_array;

    }
}