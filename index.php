<?php
/**
 * @Author: anchen
 * @Date:   2017-10-12 14:17:40
 * @Last Modified by:   anchen
 * @Last Modified time: 2017-10-12 15:45:03
 */

require_once __DIR__ . '/vendor' . '/autoload.php';


// print_r(spl_autoload_functions());



if(class_exists('Sawyes\Log\LoggerHelper')) {
// if(class_exists('Sawyes\ServiceProvider')) {
    Sawyes\Log\LoggerHelper::write('test', [], 'test');
    // echo "okok\r\n";
}
else {
    echo "nono\r\n";
}