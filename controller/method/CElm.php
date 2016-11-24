<?php
/**
 * This <ws.kcv.se.lide.forcast.currency> project created by :
 * Name         : syafiq
 * Date / Time  : 28 October 2016, 12:50 PM.
 * Email        : syafiq.rezpector@gmail.com
 * Github       : syafiqq
 */

namespace controller\method;
use model\Currency as DBCurrency;
use model\Exchange as DBExchange;
use model\logger\Logger;
use model\method\DElm_tmp;
use model\method\MElm;
use Pux;

include_once $_SERVER['DOCUMENT_ROOT'].'/model/logger/Logger.php';
include_once $_SERVER['DOCUMENT_ROOT'].'/model/database/DatabaseManager.php';
include_once $_SERVER['DOCUMENT_ROOT'].'/model/method/DElm_tmp.php';
include_once $_SERVER['DOCUMENT_ROOT'].'/model/method/MElm.php';
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
                $elmMod = DElm_tmp::getInstance();
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

    /*    public function test()
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

            $elmMod->processLearning($feature, $training, $wjk, $bias, $normalization);
            $elmMod->processTesting($testing);
            var_dump($elmMod->getMape());

        }

        public function mfinal()
        {
            $elm = MElm::getInstance();
            $feature = 3;
            $weight = MElm::generateWeight($feature);
            $bias = MElm::generateBias($feature);
            $minMax = MElm::generateNormalizationBound(array(0, 1), 0);

            $training = array(
                array('data' => array(1, 1, 1), 'class' => array('expected' => 1)),
                array('data' => array(1, 0, 1), 'class' => array('expected' => 1)),
                array('data' => array(1, 1, 0), 'class' => array('expected' => 1)),
                array('data' => array(1, 1, 0), 'class' => array('expected' => 2)),
                array('data' => array(0, 1, 0), 'class' => array('expected' => 2)),
                array('data' => array(0, 0, 0), 'class' => array('expected' => 2)),
                array('data' => array(0, 1, 0), 'class' => array('expected' => 3)),
                array('data' => array(1, 1, 0), 'class' => array('expected' => 3)),
                array('data' => array(0, 0, 0), 'class' => array('expected' => 3))
            );
            $testing[0] = array(
                array('data' => array(1, 0, 1), 'class' => array('expected' => 1)),
                array('data' => array(1, 1, 0), 'class' => array('expected' => 1)),
                array('data' => array(0, 1, 0), 'class' => array('expected' => 3))
            );
            $testing[1] = array(
                array('data' => array(1, 1, 1), 'class' => array('expected' => 1)),
                array('data' => array(1, 0, 1), 'class' => array('expected' => 1)),
                array('data' => array(1, 1, 0), 'class' => array('expected' => 1)),
                array('data' => array(1, 1, 0), 'class' => array('expected' => 2)),
                array('data' => array(0, 1, 0), 'class' => array('expected' => 2)),
                array('data' => array(0, 0, 0), 'class' => array('expected' => 2)),
                array('data' => array(0, 1, 0), 'class' => array('expected' => 3)),
                array('data' => array(1, 1, 0), 'class' => array('expected' => 3)),
                array('data' => array(0, 0, 0), 'class' => array('expected' => 3))
            );

            $elm->registerMetadata($feature, $weight, $bias, $minMax, true, 'mape');
            $elm->learn($training);
            $elm->testForAccuracy($testing[0]);
            print_r($testing[0]);
            //print_r($elm->getData());
        }*/

    public function mftest($base = 'usd', $to = 'idr', $feature = 4, $total = 355)
    {
        $isFlipped = false;
        $dbCur = DBCurrency::getInstance();
        $base = $dbCur->getCurrencyID($base);
        $to = $dbCur->getCurrencyID($to);
        if (($base != -1) && ($to != -1))
        {
            if ($base > $to)
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
            if (($total - $feature) > 1)
            {
                $data = $dbExc->getData($base, $to, $total, $isFlipped);
                $training = $dbExc->formatData($data, $feature);
                //print_r($training);

                $elm = MElm::getInstance();
                $weight = MElm::generateWeight($feature);
                $bias = MElm::generateBias($feature);
                $minMax = MElm::generateNormalizationBound($data, 100);
                $elm->registerMetadata($feature, $weight, $bias, $minMax, true, 'mape');
                $elm->learn($training);
                //$elm->testForAccuracy($training);

                $testing = array();
                $sub_testing['data'] = array();
                for ($cf = -1, $cs_f = $feature; ++$cf < $cs_f;)
                {
                    array_push($sub_testing['data'], $data[$cs_f - $cf - 1]);
                }
                array_push($testing, $sub_testing);


                for ($ct = -1, $cs_t = 20; ++$ct < $cs_t;)
                {
                    $elm->test($testing);
                    print_r($testing);
                    foreach ($testing as $tk => $tkv)
                    {
                        for ($cf = -1, $cs_f = $feature - 1; ++$cf < $cs_f;)
                        {
                            $testing[$tk]['data'][$cf] = $testing[$tk]['data'][$cf + 1];
                        }
                        $testing[$tk]['data'][$feature - 1] = round($testing[$tk]['class']['actual'], 0);
                    }
                }
            }
            else
            {
                die("Insufficient data");
            }
        }
    }
}