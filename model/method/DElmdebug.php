<?php
/**
 * This <ws.kcv.se.lide.forcast.currency> project created by :
 * Name         : syafiq
 * Date / Time  : 18 November 2016, 7:05 AM.
 * Email        : syafiq.rezpector@gmail.com
 * Github       : syafiqq
 */

namespace model\method;

use mcordingley\LinearAlgebra\Matrix;
use model\DBModel;

include_once $_SERVER['DOCUMENT_ROOT'] . '/model/DBModel.php';

class DElmdebug extends DBModel
{
    private static $instance;

    private $data;
    private $metadata;

    protected function __construct()
    {
        parent::__construct();
        $this->data = array();
        $this->metadata = array();
        $this->metadata['type'] = array('training', 'testing');
    }

    /**
     * @return DElmdebug
     */
    public static function getInstance()
    {
        if (self::$instance == null)
        {
            self::$instance = new DElmdebug();
        }
        return self::$instance;
    }


    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    public function getMetaData()
    {
        return $this->metadata;
    }

    public function registerData($feature, $training, $testing)
    {
        $this->data['raw'] = array('training' => $training, 'testing' => $testing);

        $this->metadata['total']['parameter'] = $feature;
        $this->metadata['total']['training'] = count($training);
        $this->metadata['total']['testing'] = count($testing);

        $this->data['data']['training'] = $training;
        $this->data['data']['testing'] = $testing;

        $this->generateClass();
    }

    public function processLearning($feature, $training, $wjk = array(), $bias = array(), $normalization = array())
    {
        $this->metadata['total']['parameter'] = $feature;
        $this->metadata['total']['training'] = count($training);

        $this->data['data']['training'] = $training;

        $this->generateClass();
        $this->findMinMax($normalization);
        $this->generateNormalization('training');
        $this->process($wjk, $bias);
        $this->generateDataMatrix('training');
        $this->generateExpectedClass('training');
        $this->generateHInit('training');
        $this->generateH('training');
        $this->learn();
        $this->generateYPredict('training');
        $this->calculateAccuracy('training');
    }

    public function processTesting($testing)
    {
        $this->metadata['total']['testing'] = count($testing);
        $this->data['data']['testing'] = $testing;
        $this->generateNormalization('testing');
        $this->generateDataMatrix('testing');
        $this->generateExpectedClass('testing');
        $this->generateHInit('testing');
        $this->generateH('testing');
        $this->generateYPredict('testing');
        $this->calculateAccuracy('testing');
    }

    private function process($wjk, $bias)
    {
        $this->generateElmProperties();
        $this->generateInitialInputWeight($wjk);
        $this->generateInputBias($bias);
    }
    
    private function learn()
    {
        $this->generateHInvers();
        $this->generateHTopi();
        $this->generateBedaTopi();
    }

    public function getMape()
    {
        return $this->data['data']['mape'];
    }

    private function generateClass()
    {
        $uniqueClass = array();
        foreach ($this->data['data']['training'] as $k1 => $v1)
        {
            array_push($uniqueClass, $this->data['data']['training'][$k1]['class']['actual']);
        }
        $this->metadata['class'] = array_values(array_unique($uniqueClass));
    }

    private function findMinMax($normalization = array())
    {
        if(empty($normalization))
        {
            $this->metadata['minmax'] = array('min' => PHP_INT_MAX, 'max' => PHP_INT_MIN, 'range' => (float)0);

            foreach ($this->data['raw'] as $v1)
            {
                if ($v1['value'] > $this->metadata['minmax']['max'])
                {
                    $this->metadata['minmax']['max'] = $v1['value'];
                }
                else if ($v1['value'] < $this->metadata['minmax']['min'])
                {
                    $this->metadata['minmax']['min'] = $v1['value'];
                }
            }

            $this->metadata['minmax']['range'] = $this->metadata['minmax']['max'] - $this->metadata['minmax']['min'];
        }
        else
        {
            $this->metadata['minmax']['min'] = $normalization['min'];
            $this->metadata['minmax']['max'] = $normalization['max'];
            $this->metadata['minmax']['range'] = $this->metadata['minmax']['max'] - $this->metadata['minmax']['min'];
        }
    }

    private function generateNormalization($type)
    {
        $this->data['normalization'][$type] = $this->data['data'][$type];
        $minmax = &$this->metadata['minmax'];

        foreach ($this->data['normalization'][$type] as $k1 => $v1)
        {
            $tmpData = &$this->data['normalization'][$type][$k1]['data'];
            foreach ($tmpData as $k2 => $v2)
            {
                $tmpData[$k2] = ($v2 - $minmax['min']) / ($minmax['range']);
            }
        }
    }
    
    private function generateElmProperties()
    {
        $this->metadata['total']['layer']['input'] = $this->metadata['total']['parameter'];
        $this->metadata['total']['layer']['hidden'] = $this->metadata['total']['parameter'];
        $this->metadata['total']['layer']['output'] = 1;
    }

    private function generateInitialInputWeight($wjk = array())
    {
        if(empty($wjk))
        {
            $tD = array();
            for ($i = -1, $is = $this->metadata['total']['layer']['hidden'], $js = $this->metadata['total']['layer']['input']; ++$i < $is;)
            {
                array_push($tD, array());
                $tD1 = &$tD[$i];
                for ($j = -1; ++$j < $js;)
                {
                    array_push($tD1, ((float)mt_rand() / (float)mt_getrandmax()) - 0.5);
                }
            }
            $this->data['elm']['metadata']['weight'] = new Matrix($tD);
        }
        else
        {
            $tD = array();
            for ($i = -1, $is = $this->metadata['total']['layer']['hidden'], $js = $this->metadata['total']['layer']['input']; ++$i < $is;)
            {
                array_push($tD, array());
                $tD1 = &$tD[$i];
                for ($j = -1; ++$j < $js;)
                {
                    array_push($tD1, $wjk[$i][$j]);
                }
            }
            $this->data['elm']['metadata']['weight'] = new Matrix($tD);
        }
    }

    private function generateInputBias($bias = array())
    {
        if(empty($bias))
        {
            $tD = array();
            for ($i = -1, $is = $this->metadata['total']['layer']['hidden'], $js = 1; ++$i < $is;)
            {
                array_push($tD, array());
                $tD1 = &$tD[$i];
                for ($j = -1; ++$j < $js;)
                {
                    array_push($tD1, ((float)mt_rand() / (float)mt_getrandmax()));
                }
            }
            $this->data['elm']['metadata']['bias'] = new Matrix($tD);
        }
        else
        {
            $tD = array();
            for ($i = -1, $is = $this->metadata['total']['layer']['hidden'], $js = 1; ++$i < $is;)
            {
                array_push($tD, array());
                $tD1 = &$tD[$i];
                for ($j = -1; ++$j < $js;)
                {
                    array_push($tD1, $bias[$i][$j]);
                }
            }
            $this->data['elm']['metadata']['bias'] = new Matrix($tD);
        }
    }

    private function generateDataMatrix($type)
    {
        $tD = array();
        foreach ($this->data['normalization'][$type] as $k1 => $v1)
        {
            array_push($tD, array());
            $tD1 = &$tD[$k1];
            foreach ($this->data['normalization'][$type][$k1]['data'] as $v3)
            {
                array_push($tD1, $v3);
            }
        }
        $this->data['elm']['data'][$type]['data'] = new Matrix($tD);
    }

    private function generateExpectedClass($type)
    {
        $tD = array();
        foreach ($this->data['normalization'][$type] as $k1 => $v1)
        {
            array_push($tD, array());
            $tD1 = &$tD[$k1];
            $class = $this->data['normalization'][$type][$k1]['class']['actual'];
            array_push($tD1, $class);
        }
        $this->data['elm']['data'][$type]['class']['expected'] = new Matrix($tD);
    }
    
    private function generateHInit($type)
    {
        $this->data['elm']['data'][$type]['h_init'] = ($this->data['elm']['data'][$type]['data'])->multiplyMatrix(($this->data['elm']['metadata']['weight'])->transpose());
    }

    private function generateH($type)
    {
        $this->data['elm']['data'][$type]['h'] = ($this->data['elm']['data'][$type]['h_init'])->map(function ($element)
        {
            return (1.0 / (1.0 + exp(-$element)));
        });
    }

    private function generateHInvers()
    {
        $this->data['elm']['metadata']['h_inverse'] = ((($this->data['elm']['data']['training']['h'])->transpose())->multiplyMatrix($this->data['elm']['data']['training']['h']))->inverse();
    }

    private function generateHTopi()
    {
        $this->data['elm']['metadata']['h_topi'] = ($this->data['elm']['metadata']['h_inverse'])->multiplyMatrix(($this->data['elm']['data']['training']['h'])->transpose());
    }

    private function generateBedaTopi()
    {
        $this->data['elm']['metadata']['beda_topi'] = ($this->data['elm']['metadata']['h_topi'])->multiplyMatrix($this->data['elm']['data']['training']['class']['expected']);
    }

    private function generateYPredict($type)
    {
        $this->data['elm']['metadata'][$type]['y_predict'] = ($this->data['elm']['data'][$type]['h'])->multiplyMatrix($this->data['elm']['metadata']['beda_topi']);
        $this->metadata['y_predict'][$type] = &$this->data['elm']['metadata'][$type]['y_predict']->toArray();
    }

    private function calculateAccuracy($type)
    {
        $mape = 0.0;
        $data = &$this->data['data'][$type];
        $predict = &$this->metadata['y_predict'][$type];
        $totalData = $this->metadata['total'][$type];
        for($i = -1; ++$i < $totalData;)
        {
            $value = PHP_INT_MAX;
            $index = -1;
            foreach ($this->metadata['class'] as $class)
            {
                $tmpValue = abs($predict[$i][0] - $class);

                if($tmpValue < $value)
                {
                    $index = $class;
                    $value = $tmpValue;
                }
            }
            $data[$i]['class']['actual'] = $index;
            $mape += abs(($predict[$i][0] - $data[$i]['class']['expected'])/$data[$i]['class']['expected']);
        }
        $this->data['data']['mape'][$type] = $mape / $totalData * 100.0;
    }
}