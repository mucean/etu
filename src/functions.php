<?php

if (!function_exists('getallheaders')) {
    function getallheaders(array &$servers)
    {
        $headers = [];
        foreach ($servers as $name => $value) {
            if (strpos($name, 'HTTP_') === 0) {
                $headers[str_replace(
                    ' ',
                    '-',
                    ucwords(strtolower(str_replace('_', ' ', substr($name, 5))))
                )] = $value;
            }
        }
        return $headers;
    }
}
