<?php
/**
 * This <ws.kcv.se.lide.forcast.currency> project created by :
 * Name         : syafiq
 * Date / Time  : 25 October 2016, 4:01 PM.
 * Email        : syafiq.rezpector@gmail.com
 * Github       : syafiqq
 */

require 'vendor/autoload.php';
include_once 'controller/Dump.php';
include_once 'controller/Currency.php';
include_once 'controller/method/CElm.php';
include_once 'model/logger/Logger.php';
include_once 'model/core/config/error_handler.php';
include_once 'model/core/config/common_function.php';

use model\logger\Logger;
use Pux\Executor;
use Pux\Mux;

$mux = new Mux;
//$mux->get('/dump/carbon', ['controller\Dump', 'carbon']);
//$mux->get('/dump/carbon001', ['controller\Dump', 'carbon_001']);
//$mux->get('/dump/database', ['controller\Dump', 'database']);
$mux->get(
    '/elm/show/:base/:to/:feature/:total/:percentage',
    [
        'controller\method\CElm', 'show'
    ],
    [
        'require' =>
            [
                'base' => '([a-z]+)',
                'to' => '([a-z]+)',
                'feature' => '\d+',
                'total' => '\d+',
                'percentage' => '\d+'
            ],
        'default' =>
            [
                'base' => 'usd',
                'to' => 'idr',
                'feature' => '4',
                'total' => '355',
                'percentage' => '60'
            ]
    ]
);
$mux->get(
    '/elm/test/slide',
    [
        'controller\method\CElm', 'test'
    ]
);
$mux->get(
    '/elm/final/show',
    [
        'controller\method\CElm', 'mfinal'
    ]
);
$mux->get(
    '/elm/final/test/:base/:to/:feature/:total',
    [
        'controller\method\CElm', 'mftest'
    ],
    [
        'require' =>
            [
                'base' => '([a-z]+)',
                'to' => '([a-z]+)',
                'feature' => '\d+',
                'total' => '\d+',
            ],
        'default' =>
            [
                'base' => 'usd',
                'to' => 'idr',
                'feature' => '4',
                'total' => '355',
            ]
    ]
);
//$mux->get('/ws/currency/load', ['controller\Currency', 'load']);
//$mux->post('/ws/currency/add', ['controller\Currency', 'add']);

$logger = Logger::getInstance();
if(isset($_SERVER['PATH_INFO']))
{
    $route = $mux->dispatch($_SERVER['PATH_INFO']);
    if($route != null)
    {
        Executor::execute($route);
    }
}