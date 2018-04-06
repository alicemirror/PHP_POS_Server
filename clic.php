<?php

    function getPath($path) {
        switch ($path) {            
            case 'product_icons':
                $ret = '/var/www/pos/product_icons/';
                break;
            default:
                break;
         }
       
        return $ret;
    }