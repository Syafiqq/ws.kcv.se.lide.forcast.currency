<?php
/**
 * This <ws.kcv.se.lide.forcast.currency> project created by :
 * Name         : syafiq
 * Date / Time  : 24 November 2016, 1:12 PM.
 * Email        : syafiq.rezpector@gmail.com
 * Github       : syafiqq
 */

namespace model\method;

use mcordingley\LinearAlgebra\Matrix;
use model\DBModel;

include_once $_SERVER['DOCUMENT_ROOT'] . '/model/DBModel.php';

class MElm extends DBModel
{
    private static $instance;

    private $data;
    private $metadata;
    private $allowLearn;
    private $allowTest;

    protected function __construct()
    {
        parent::__construct();
        $this->data = array();
        $this->metadata = array();
        $this->metadata['type'] = array('training', 'testing');
        $this->metadata['class'] = array('actual', 'expected');
        $this->allowLearn = false;
        $this->allowTest = false;
    }

    /**
     * @return MElm
     */
    public static function getInstance()
    {
        if (self::$instance == null)
        {
            self::$instance = new MElm();
        }
        return self::$instance;
    }

    public static function generateWeight($_tFeature)
    {
        $weight = array();
        for ($ci = -1, $cs_i = $_tFeature; ++$ci < $cs_i;)
        {
            $tmp = array();
            for ($cj = -1, $cs_j = $_tFeature; ++$cj < $cs_j;)
            {
                array_push($tmp, ((float)mt_rand() / (float)mt_getrandmax()) - 0.5);
            }
            array_push($weight, $tmp);
        }

        return $weight;
    }

    public static function generateBias($_tFeature)
    {
        $bias = array();
        for ($ci = -1, $cs_i = $_tFeature; ++$ci < $cs_i;)
        {
            $tmp = array();
            for ($cj = -1, $cs_j = 1; ++$cj < $cs_j;)
            {
                array_push($tmp, ((float)mt_rand() / (float)mt_getrandmax()));
            }
            array_push($bias, $tmp);
        }

        return $bias;
    }

    /**
     * @param array $data
     * @param $bias
     * @return array
     */
    public static function generateNormalizationBound($data, $bias)
    {
        $minMax = array('min' => PHP_INT_MAX, 'max' => PHP_INT_MIN, 'range' => 0.0);

        foreach ($data as $datum)
        {
            if ($datum > $minMax['max'])
            {
                $minMax['max'] = $datum;
            }
            if ($datum < $minMax['min'])
            {
                $minMax['min'] = $datum;
            }
        }

        $minMax['max'] += $bias;
        $minMax['min'] -= $bias;

        $minMax['range'] = $minMax['max'] - $minMax['min'];
        return $minMax;
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

    /**
     * @param int $_tFeature
     * @param array $_weight
     * @param array $_bias
     * @param array $_normBound
     * @param bool $isBiasEnabled
     * @param string $accuracyType
     */
    public function registerMetadata($_tFeature, $_weight, $_bias, $_normBound, $isBiasEnabled, $accuracyType)
    {
        $this->metadata['total']['parameter'] = $_tFeature;
        $this->metadata['minmax'] = array();
        $this->metadata['property']['bias'] = $isBiasEnabled;
        $this->metadata['property']['accuracy'] = strtolower($accuracyType);
        $this->metadata['elm']['weight'] = array();
        $this->metadata['elm']['bias'] = array();

        $weight = &$this->metadata['elm']['weight'];
        for ($ci = -1, $cs_i = $_tFeature; ++$ci < $cs_i;)
        {
            $tmp = array();
            for ($cj = -1, $cs_j = $_tFeature; ++$cj < $cs_j;)
            {
                array_push($tmp, $_weight[$ci][$cj]);
            }
            array_push($weight, $tmp);
        }

        $bias = &$this->metadata['elm']['bias'];
        for ($ci = -1, $cs_i = $_tFeature; ++$ci < $cs_i;)
        {
            $tmp = array();
            for ($cj = -1, $cs_j = 1; ++$cj < $cs_j;)
            {
                array_push($tmp, $_bias[$ci][$cj]);
            }
            array_push($bias, $tmp);
        }

        $minMax = &$this->metadata['minmax'];

        $minMax['min'] = $_normBound['min'];
        $minMax['max'] = $_normBound['max'];
        $minMax['range'] = $_normBound['range'];

        $this->allowLearn = true;
    }

    /**
     * @param array $training
     */
    public function learn($training)
    {
        if ($this->allowLearn)
        {
            $this->metadata['total']['training'] = count($training);
            $this->data['data']['training'] = &$training;

            $this->generateNormalization('training');
            $this->initializeELMComponent();
            $this->calculateELM('training');
            $this->calculateOutputWeight();
            $this->calculateOutput('training');
            $this->calculateAccuracy('training');

            $this->allowLearn = false;
            $this->allowTest = true;
        }
        else
        {
            die("Please initialize/reassign the metadata first");
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

    private function initializeELMComponent()
    {
        $this->generateElmProperties();
        $this->generateInitialInputWeight();
        $this->generateInputBias();
    }

    private function generateElmProperties()
    {
        $this->metadata['total']['layer']['input'] = $this->metadata['total']['parameter'];
        $this->metadata['total']['layer']['hidden'] = $this->metadata['total']['parameter'];
        $this->metadata['total']['layer']['output'] = 1;
    }

    private function generateInitialInputWeight()
    {
        $this->data['elm']['metadata']['weight'] = new Matrix($this->metadata['elm']['weight']);
    }

    private function generateInputBias()
    {
        $this->data['elm']['metadata']['bias'] = new Matrix($this->metadata['elm']['bias']);
    }

    private function calculateELM($type)
    {
        $this->generateDataMatrix($type);
        $this->generateExpectedClass($type);
        $this->generateHInit($type);
        $this->generateH($type);
    }

    private function generateDataMatrix($type)
    {
        $row = array();
        foreach ($this->data['normalization'][$type] as $k1 => $v1)
        {
            array_push($row, array());
            $column = &$row[$k1];
            foreach ($this->data['normalization'][$type][$k1]['data'] as $v2)
            {
                array_push($column, $v2);
            }
        }
        $this->data['elm']['data'][$type]['data'] = new Matrix($row);
    }

    private function generateExpectedClass($type)
    {
        $row = array();
        foreach ($this->data['normalization'][$type] as $k1 => $v1)
        {
            array_push($row, array());
            $column = &$row[$k1];
            $class = $this->data['normalization'][$type][$k1]['class']['expected'];
            array_push($column, $class);
        }
        $this->data['elm']['data'][$type]['class']['expected'] = new Matrix($row);
    }

    private function generateHInit($type)
    {
        $this->data['elm']['data'][$type]['h_init'] = ($this->data['elm']['data'][$type]['data'])->multiplyMatrix(($this->data['elm']['metadata']['weight'])->transpose());
    }

    private function generateH($type)
    {
        if ($this->metadata['property']['bias'])
        {
            $this->data['elm']['data'][$type]['h'] = ($this->data['elm']['data'][$type]['h_init'])->map(function ($element, $row, $column)
            {
                return (1.0 / (1.0 + exp(-($element + $this->metadata['elm']['bias'][$column][0]))));
            });
        }
        else
        {
            $this->data['elm']['data'][$type]['h'] = ($this->data['elm']['data'][$type]['h_init'])->map(function ($element)
            {
                return (1.0 / (1.0 + exp(-$element)));
            });
        }
    }

    private function calculateOutputWeight()
    {
        $this->generateHInverse();
        $this->generateMPGIM();
        $this->generateOutputWeight();
    }

    private function generateHInverse()
    {
        $this->data['elm']['metadata']['h_inverse'] = ((($this->data['elm']['data']['training']['h'])->transpose())->multiplyMatrix($this->data['elm']['data']['training']['h']))->inverse();
    }

    private function generateMPGIM()
    {
        $this->data['elm']['metadata']['h_topi'] = ($this->data['elm']['metadata']['h_inverse'])->multiplyMatrix(($this->data['elm']['data']['training']['h'])->transpose());
    }

    private function generateOutputWeight()
    {
        $this->data['elm']['metadata']['beta_topi'] = ($this->data['elm']['metadata']['h_topi'])->multiplyMatrix($this->data['elm']['data']['training']['class']['expected']);
    }

    private function calculateOutput($type)
    {
        $this->data['elm']['metadata'][$type]['y_predict'] = ($this->data['elm']['data'][$type]['h'])->multiplyMatrix($this->data['elm']['metadata']['beta_topi']);
        $this->metadata['y_predict'][$type] = &$this->data['elm']['metadata'][$type]['y_predict']->toArray();

        $this->assignActualClass($type);
    }

    private function assignActualClass($type)
    {
        $predict = &$this->metadata['y_predict'][$type];
        foreach ($this->data['data'][$type] as $k1 => $v1)
        {
            $this->data['data'][$type][$k1]['class']['actual'] = $predict[$k1][0];
        }
    }

    private function calculateAccuracy($type)
    {
        switch ($this->metadata['property']['accuracy'])
        {
            case 'mape';
            {
                $this->calculateMAPEAccuracy($type);
            }
        }
    }

    private function calculateMAPEAccuracy($type)
    {
        $mape = 0.0;
        $data = &$this->data['normalization'][$type];
        $predict = &$this->metadata['y_predict'][$type];
        $totalData = $this->metadata['total'][$type];
        for ($i = -1; ++$i < $totalData;)
        {
            $y_topi = $predict[$i][0];
            $y = $data[$i]['class']['expected'];
            $mape += abs(($y_topi - $y) / $y);
        }
        $this->data['data']['mape'][$type] = $mape / $totalData * 100.0;
    }

    public function testForAccuracy(&$testing)
    {
        if ($this->allowTest)
        {
            $this->test($testing);
            $this->calculateAccuracy('testing');
            foreach ($testing as $key => $value)
            {
                $testing['data'][$key] = $value;
                unset($testing[$key]);
            }
            $testing['accuracy'] = $this->data['data']['mape']['testing'];
        }
        else
        {
            die("Please train your data first");
        }
    }

    public function test(&$testing)
    {
        if ($this->allowTest)
        {
            $this->metadata['total']['testing'] = count($testing);
            $this->data['data']['testing'] = &$testing;

            $this->generateNormalization('testing');
            $this->calculateELM('testing');
            $this->calculateOutput('testing');
        }
        else
        {
            die("Please train your data first");
        }
    }
}