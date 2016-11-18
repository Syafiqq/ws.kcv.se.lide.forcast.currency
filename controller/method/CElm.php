<?php
/**
 * This <ws.kcv.se.lide.forcast.currency> project created by :
 * Name         : syafiq
 * Date / Time  : 28 October 2016, 12:50 PM.
 * Email        : syafiq.rezpector@gmail.com
 * Github       : syafiqq
 */

namespace controller\method;
use model\logger\Logger;
use model\Currency as DBCurrency;
use model\Exchange as DBExchange;
use model\method\DElm;
use model\method\DElmdebug;
use view\method\VElm;
use Pux;

include_once $_SERVER['DOCUMENT_ROOT'].'/model/logger/Logger.php';
include_once $_SERVER['DOCUMENT_ROOT'].'/model/database/DatabaseManager.php';
include_once $_SERVER['DOCUMENT_ROOT'].'/view/method/VElm.php';
include_once $_SERVER['DOCUMENT_ROOT'].'/model/method/DElm.php';
include_once $_SERVER['DOCUMENT_ROOT'].'/model/method/DElmdebug.php';


class CElm extends Pux\Controller
{
    private $logger;

    public function __construct()
    {
        $this->logger = Logger::getInstance();
    }

    public function show($base = 'usd', $to = 'idr', $feature = 4, $total = 355, $percentage = 60)
    {
        $isFlipped = false;
        $dbCur = DBCurrency::getInstance();
        $base = $dbCur->getCurrencyID($base);
        $to = $dbCur->getCurrencyID($to);
        if(($base != -1) && ($to != -1))
        {
            if($base > $to)
            {
                $tmpVal = $base;
                $base = $to;
                $to = $tmpVal;
                $isFlipped = true;
                unset($tmpVal);
            }
            $dbExc = DBExchange::getInstance();
            $dataTotal = $dbExc->getTotalData($base, $to);
            $total = $dataTotal > $total ? $total : $dataTotal;
            if(($total - $feature) > 1)
            {
                $elmMod = DElm::getInstance();
                $data = $dbExc->getData($base, $to, $total, $isFlipped);
                if($total == count($data))
                {
                    if ($percentage > 0 && $percentage < 100)
                    {
                        //$this->logger->addDebug(var_export($feature, true));
                        //$this->logger->addDebug(var_export($data, true));
                        $elmMod->registerData($feature, $data, $percentage);
                        //$elmMod->process();
                        //(new VElm(array('base' => $base, 'to' => $to, 'feature' => $feature, 'total' => $total, 'data' => $elmMod->getData(), 'metadata' => $elmMod->getMetaData())))->display();
                    }
                }
            }
        }
    }

    public function test()
    {
        $training = array(
            array('data' => array(1, 1, 1), 'class' => array('actual' => 1, 'expected' => 1)),
            array('data' => array(1, 0, 1), 'class' => array('actual' => 1, 'expected' => 1)),
            array('data' => array(1, 1, 0), 'class' => array('actual' => 1, 'expected' => 1)),
            array('data' => array(1, 1, 0), 'class' => array('actual' => 2, 'expected' => 2)),
            array('data' => array(0, 1, 0), 'class' => array('actual' => 2, 'expected' => 2)),
            array('data' => array(0, 0, 0), 'class' => array('actual' => 2, 'expected' => 2)),
            array('data' => array(0, 1, 0), 'class' => array('actual' => 3, 'expected' => 3)),
            array('data' => array(1, 1, 0), 'class' => array('actual' => 3, 'expected' => 3)),
            array('data' => array(0, 0, 0), 'class' => array('actual' => 3, 'expected' => 3))
        );
        $testing = array(
            array('data' => array(1, 0, 1), 'class' => array('actual' => -1, 'expected' => 1)),
            array('data' => array(1, 1, 0), 'class' => array('actual' => -1, 'expected' => 1)),
            array('data' => array(0, 1, 0), 'class' => array('actual' => -1, 'expected' => 3))
        );

        $feature = 3;

        $wjk = array(
            array(-0.4,  0.2,  0.1),
            array(-0.2,  0.0,  0.4),
            array(-0.3,  0.3, -0.1)
        );

        $bias = array(
            array(0.1),
            array(0.4),
            array(0.3)
        );

        $normalization = array('min' => 0, 'max' => 1);

        $elmMod = DElmdebug::getInstance();
        $elmMod->registerData($feature, $training, $testing);
        $elmMod->process($wjk, $bias, $normalization);
    }
}