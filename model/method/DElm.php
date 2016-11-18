<?php
/**
 * This <ws.kcv.se.lide.forcast.currency> project created by :
 * Name         : syafiq
 * Date / Time  : 02 November 2016, 1:01 PM.
 * Email        : syafiq.rezpector@gmail.com
 * Github       : syafiqq
 */

namespace model\method;

use mcordingley\LinearAlgebra\Matrix;
use model\DBModel;

include_once $_SERVER['DOCUMENT_ROOT'] . '/model/DBModel.php';

class DElm extends DBModel
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
     * @return DElm
     */
    public static function getInstance()
    {
        if (self::$instance == null)
        {
            self::$instance = new DElm();
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

    public function registerData($param, $data, $percentage)
    {
        $this->data['raw'] = $data;

        $this->metadata['total']['parameter'] = $param;
        $this->metadata['total']['data'] = count($data) - $this->metadata['total']['parameter'];
        $this->metadata['total']['training'] = (int)ceil($percentage * $this->metadata['total']['data'] / 100.0);
        if ($this->metadata['total']['training'] == 0)
        {
            $this->metadata['total']['training'] = 1;
            $this->metadata['total']['testing'] = $this->metadata['total']['data'] - $this->metadata['total']['training'];
        }
        else if ($this->metadata['total']['training'] == $this->metadata['total']['data'])
        {
            $this->metadata['total']['training'] -= 1;
            $this->metadata['total']['testing'] = 1;
        }
        else
        {
            $this->metadata['total']['testing'] = $this->metadata['total']['data'] - $this->metadata['total']['training'];
        }

        $i = -1;
        $is = 0;
        foreach ($this->metadata['type'] as $type)
        {
            for ($is += $this->metadata['total'][$type], $js = $this->metadata['total']['parameter']; ++$i < $is;)
            {
                $feature = array();
                for ($j = -1; ++$j < $js;)
                {
                    array_push($feature, $data[$i + ($js - $j)]['value']);
                }
                array_push($currency, array('data' => $feature, 'class' => $data[$i]['value']));
            }
            --$i;
            $this->data['data'][$type] = $currency;
            $currency = array();
        }

        var_export($this->data['data']);
        var_export($this->metadata['total']);
        var_export($this->metadata['type']);
    }

    public function process()
    {
        $this->findMinMax();
        $this->generateMinMax();
        $this->generateDataMatrix();
        $this->generateExpectedClass();
        $this->generateElmProperties();
        $this->generateInitialInputWeight();
        $this->generateInputBias();
        $this->generateHInit();
        $this->generateH();
        $this->generateHInvers();
        $this->generateHTopi();
        $this->generateBedaTopi();
        $this->generateYPredict();
    }

    private function findMinMax()
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

    private function generateMinMax()
    {
        $this->data['minmax'] = $this->data['data'];
        $minmax = &$this->metadata['minmax'];

        foreach ($this->metadata['type'] as $type)
        {
            foreach ($this->data['minmax'][$type] as $k1 => $v1)
            {
                foreach ($this->data['minmax'][$type][$k1] as $k3 => $v3)
                {
                    $tmpData = &$this->data['minmax'][$type][$k1][$k3];
                    if (is_array($tmpData))
                    {
                        foreach ($tmpData as $k2 => $v2)
                        {
                            $tmpData[$k2] = ($v2 - $minmax['min']) / ($minmax['range']);
                        }
                    }
                    else
                    {
                        $tmpData = ($tmpData - $minmax['min']) / ($minmax['range']);
                    }
                }
            }
        }
    }


    private function generateDataMatrix()
    {
        foreach ($this->metadata['type'] as $type)
        {
            $tD = array();
            foreach ($this->data['minmax'][$type] as $k1 => $v1)
            {
                array_push($tD, array());
                $tD1 = &$tD[$k1];
                foreach ($this->data['minmax'][$type][$k1]['data'] as $v3)
                {
                    array_push($tD1, $v3);
                }
            }
            $this->data['elm']['data'][$type] = new Matrix($tD);
        }
    }


    private function generateExpectedClass()
    {
        $tD = array();
        foreach ($this->data['minmax']['training'] as $k1 => $v1)
        {
            array_push($tD, array());
            $tD1 = &$tD[$k1];
            array_push($tD1, $this->data['minmax']['training'][$k1]['class']);
        }
        $this->data['elm']['data']['expectedClass'] = new Matrix($tD);
    }

    private function generateElmProperties()
    {
        $this->metadata['total']['layer']['input'] = $this->metadata['total']['parameter'];
        $this->metadata['total']['layer']['hidden'] = $this->metadata['total']['parameter'];
        $this->metadata['total']['layer']['output'] = 1;
    }

    private function generateInitialInputWeight()
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
        $this->data['elm']['weight'] = new Matrix($tD);
    }

    private function generateInputBias()
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
        $this->data['elm']['bias'] = new Matrix($tD);
    }

    private function generateHInit()
    {
        $this->data['elm']['h_init'] = ($this->data['elm']['data']['training'])->multiplyMatrix(($this->data['elm']['weight'])->transpose());
    }

    private function generateH()
    {
        $this->data['elm']['h'] = ($this->data['elm']['h_init'])->map(function ($element)
        {
            return (1.0 / (1.0 + exp(-$element)));
        });
    }

    private function generateHInvers()
    {
        $this->data['elm']['h_inverse'] = ((($this->data['elm']['h'])->transpose())->multiplyMatrix($this->data['elm']['h']))->inverse();
    }

    private function generateHTopi()
    {
        $this->data['elm']['h_topi'] = ($this->data['elm']['h_inverse'])->multiplyMatrix(($this->data['elm']['h'])->transpose());
    }

    private function generateBedaTopi()
    {
        $this->data['elm']['beda_topi'] = ($this->data['elm']['h_topi'])->multiplyMatrix($this->data['elm']['data']['expectedClass']);
    }

    private function generateYPredict()
    {
        $this->data['elm']['y_predict'] = ($this->data['elm']['h'])->multiplyMatrix($this->data['elm']['beda_topi']);
    }

}