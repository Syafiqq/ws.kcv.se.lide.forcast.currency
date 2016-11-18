<?php
/**
 * This <ws.kcv.se.lide.forcast.currency> project created by :
 * Name         : syafiq
 * Date / Time  : 28 October 2016, 5:58 AM.
 * Email        : syafiq.rezpector@gmail.com
 * Github       : syafiqq
 */

namespace model;

include_once $_SERVER['DOCUMENT_ROOT'].'/model/DBModel.php';

class Exchange extends DBModel
{
    private static $instance;

    protected function __construct()
    {
        parent::__construct();
    }

    /**
     * @return Exchange
     */
    public static function getInstance()
    {
        if(self::$instance == null)
        {
            self::$instance = new Exchange();
        }
        return self::$instance;
    }

    public function storeExchangeData($base, $to, $date, $value)
    {
        $this->reconnect();
        if ($this->db->insert ('exchange', array(
            'base' => $base,
            'to' => $to,
            'date' => $date,
            'value' => $value,
        )))
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    public function storeOrUpdateExchange($base, $to, $date, $value)
    {
        $this->reconnect();
        return $this->db->rawQuery("call store_or_update_exchange(?, ?, ?, ?)", array($base, $to, $date, $value));
    }

    public function getTotalData($base, $to)
    {
        $this->reconnect();
        $exchangeTotal = -1;
        $this->db->where ('`exchange`.`base`', $base);
        $this->db->where ('`exchange`.`to`', $to);
        $resultSet = $this->db->getOne ('`exchange`', array('count(`exchange`.`base`) as \'count\''));
        if($resultSet != null)
        {
            if(is_array($resultSet) && $this->db->count > 0)
            {
                $exchangeTotal = $resultSet['count'];
            }
        }
        return $exchangeTotal;
    }

    public function getData($base, $to, $total, $isFlipped)
    {
        $this->reconnect();
        $data = array();
        $db = &$this->db;
        $db->where ('`exchange`.`base`', $base);
        $db->where ('`exchange`.`to`', $to);
        $db->orderBy('`exchange`.`date`','desc');
        $resultSet = $db->get ('`exchange`', $total, array(($isFlipped ? '1.0/' : ''). '`value` as \'value\''));
        if($resultSet != null)
        {
            if($db->count > 0)
            {
                $data =  $resultSet;
            }
        }
        return $data;
    }
}