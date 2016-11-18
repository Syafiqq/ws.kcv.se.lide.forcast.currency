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
        $this->metadata['total']['data'] = count($training) + count($testing);
        $this->metadata['total']['training'] = count($training);
        $this->metadata['total']['testing'] = count($testing);

        $this->data['data']['training'] = $training;
        $this->data['data']['testing'] = $testing;

/*        var_export($this->data['data']);
        var_export($this->metadata['total']);
        var_export($this->metadata['type']);*/
    }

    public function process($wjk, $bias, $normalization)
    {
        $this->findMinMax($normalization);
        $this->generateNormalization();
        $this->generateDataMatrix();
        $this->generateExpectedClass();
        $this->generateElmProperties();
        $this->generateInitialInputWeight($wjk);
        $this->generateInputBias($bias);
        $this->calculateELM();
    }

    private function calculateELM()
    {
        $this->generateHInit();
        $this->generateH();
        $this->generateHInvers();
        $this->generateHTopi();
        $this->generateBedaTopi();
        $this->generateYPredict();
        $this->calculateAccuracy();
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

    private function generateNormalization()
    {
        $this->data['normalization'] = $this->data['data'];
        $minmax = &$this->metadata['minmax'];

        foreach ($this->metadata['type'] as $type)
        {
            foreach ($this->data['normalization'][$type] as $k1 => $v1)
            {
                $tmpData = &$this->data['normalization'][$type][$k1]['data'];
                foreach ($tmpData as $k2 => $v2)
                {
                    $tmpData[$k2] = ($v2 - $minmax['min']) / ($minmax['range']);
                }
            }
        }
    }

    private function generateDataMatrix()
    {
        foreach ($this->metadata['type'] as $type)
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
            $this->data['elm']['data'][$type] = new Matrix($tD);
        }
    }

    private function generateExpectedClass()
    {
        $uniqueClass = array();
        $tD = array();
        foreach ($this->data['normalization']['training'] as $k1 => $v1)
        {
            array_push($tD, array());
            $tD1 = &$tD[$k1];
            $class = $this->data['normalization']['training'][$k1]['class']['actual'];
            array_push($tD1, $class);
            array_push($uniqueClass, $class);
        }
        $this->data['elm']['data']['expectedClass'] = new Matrix($tD);
        $this->metadata['class'] = array_values(array_unique($uniqueClass));
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
            $this->data['elm']['weight'] = new Matrix($tD);
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
            $this->data['elm']['weight'] = new Matrix($tD);
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
            $this->data['elm']['bias'] = new Matrix($tD);
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
            $this->data['elm']['bias'] = new Matrix($tD);
        }
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
        $this->data['y_predict'] = &$this->data['elm']['y_predict']->toArray();
    }

    private function calculateAccuracy()
    {
        $mape = 0.0;
        $training = &$this->data['data']['training'];
        $predict = &$this->data['y_predict'];
        $totalData = $this->metadata['total']['training'];
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
            $training[$i]['class']['actual'] = $index;
            $mape += abs(($predict[$i][0] - $training[$i]['class']['actual'])/$training[$i]['class']['actual']);
        }
        $this->data['mape'] = $mape / $totalData * 100.0;
    }
}