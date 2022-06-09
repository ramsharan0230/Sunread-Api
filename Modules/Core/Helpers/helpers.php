<?php


use Modules\Core\Services\Core;

if (! function_exists('array_permutation')) {

    function array_permutation($input)
    {
        $results = [];

        foreach ($input as $key => $values) {
            if (empty($values)) {
                continue;
            }

            if (empty($results)) {
                foreach ($values as $value) {
                    $results[] = [$key => $value];
                }
            } else {
                $append = [];

                foreach ($results as &$result) {
                    $result[$key] = array_shift($values);

                    $copy = $result;

                    foreach ($values as $item) {
                        $copy[$key] = $item;
                        $append[] = $copy;
                    }

                    array_unshift($values, $result[$key]);
                }

                $results = array_merge($results, $append);
            }
        }

        return $results;
    }

    if (! function_exists('core')) {
        function core()
        {
            return app()->make(Core::class);
        }
    }

    if (! function_exists('setDotToArray')) {
        function setDotToArray($dot_syntax, &$main_array, $value, $separator = ".") 
        {
            $keys = explode($separator, $dot_syntax);
            foreach ($keys as $key) $main_array = &$main_array[$key];
            $main_array = $value;
        }
    }

    if (! function_exists('getDotToArray')) {
        function getDotToArray($dot_syntax, &$main_array, $separator = ".") 
        {
            $keys = explode($separator, $dot_syntax);
            foreach ($keys as $key) $main_array = &$main_array[$key];
            return $main_array;
        }
    }

    if (! function_exists('is_json')) {
        function is_json($string) 
        {
            json_decode($string);
            return (json_last_error() == JSON_ERROR_NONE);
        }
    } 

    if (! function_exists('decodeJsonNumeric')) {
        function decodeJsonNumeric($val) 
        {
            return is_numeric($val) ? json_decode($val) : $val;
        }
    }
    
    if (! function_exists("array_not_unique") ) {
        function array_not_unique(array $array): array
        {
            $duplicate_array = array_diff_key( $array , array_unique( $array ) );
            $unique_array = [];
            foreach ($array as $key => $value) {
                if ( in_array($value, $duplicate_array)) {
                    $duplicate_array[$key] = $value;
                }
                else {
                    $unique_array[$key] = $value;
                } 

            }

            return ["unique_array" => $unique_array, "duplicate_array" => $duplicate_array];
        }
    }

    if (! function_exists("array_min") ) {
        function array_min(array $array): array
        {
            $min_value = min($array);
            $min_value_with_key = [];
            foreach ( $array as $key => $value ) {
                if ($min_value == $value) $min_value_with_key[$key] = $value;
            }

            return $min_value_with_key;
        }
    }
}
?>
