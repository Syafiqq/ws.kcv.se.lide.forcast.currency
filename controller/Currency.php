<?php
/**
 * This <ws.kcv.se.lide.forcast.currency> project created by :
 * Name         : syafiq
 * Date / Time  : 27 October 2016, 9:01 PM.
 * Email        : syafiq.rezpector@gmail.com
 * Github       : syafiqq
 */

namespace controller;
use model\logger\Logger;
use model\Currency as DBCurrency;
use model\Exchange as DBExchange;
use Pux;

include_once $_SERVER['DOCUMENT_ROOT'].'/model/logger/Logger.php';
include_once $_SERVER['DOCUMENT_ROOT'].'/model/Currency.php';
include_once $_SERVER['DOCUMENT_ROOT'].'/model/Exchange.php';
include_once $_SERVER['DOCUMENT_ROOT'].'/model/database/DatabaseManager.php';


class Currency extends Pux\Controller
{
    private $logger;

    public function __construct()
    {
        $this->logger = Logger::getInstance();
    }

    public function load()
    {
        require_once(dirname(__FILE__) . '/../view/currency/load.php');
    }

    public function add()
    {
        if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && (strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'))
        {
            if(isset($_POST['base'])
            && isset($_POST['to'])
            && isset($_POST['date'])
            && isset($_POST['value']))
            {
                $db_cur = DBCurrency::getInstance();
                $_POST['base'] = $db_cur->getCurrencyID($_POST['base']);
                $_POST['to'] = $db_cur->getCurrencyID($_POST['to']);
                if($_POST['base'] > $_POST['to'])
                {
                    $tmpVal = $_POST['base'];
                    $_POST['base'] = $_POST['to'];
                    $_POST['to'] = $tmpVal;
                    $_POST['value'] = 1.0/$_POST['value'];
                    unset($tmpVal);
                }
                unset($db_cur);
                $db_ex = DBExchange::getInstance();
                $db_ex->storeOrUpdateExchange($_POST['base'], $_POST['to'], $_POST['date'], $_POST['value']);
            }
            echo json_encode(array('error' => 0));
        }
        else
        {
            echo json_encode(array('error' => 1));
        }
    }
}