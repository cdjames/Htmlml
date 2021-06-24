<?php

namespace Helpers\Strings {
    function remove_values($expr, $string) {
        return preg_replace($expr, '', $string);
    }

    function add_values($expr, $add, $string, $before = true) {
        /* test for valid $expr */
        if (preg_match('/\(.*\)/', $expr) === 0) {
            throw new \Exception('need at least one group');
        }
        
        $repl = ($before == true) ? $add.'$1' : '$1'.$add;
        $new_string = preg_replace($expr, $repl, $string);
        
        /* test for errors */
        if ($new_string === null) {
            throw new \Exception('regex error');
        }

        // if ($new_string === $string) {
        //     throw new \Exception('no matches found');
        // }

        return $new_string;
    }

    function add_dashes($string, $expr) {
        try {
            return add_values('/(\.mp3)$/', '---', $string);
            // return preg_replace('/(\.mp3)$/', '---$1', $string);
        } catch (\Exception $e) {
            throw $e;
        }
    }
}

namespace Helpers\Files {

    function pathjoin(Array $elements = null)
    {
        if ($elements == null || sizeof($elements) < 2 ) {
            throw new \Exception('invalid array or less than 2 items');
        }
        
        $path = implode(DIRECTORY_SEPARATOR, $elements);

        return $path;
    }
}

?>