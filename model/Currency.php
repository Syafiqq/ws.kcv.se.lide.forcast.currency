<?php
/**
 * This <ws.kcv.se.lide.forcast.currency> project created by :
 * Name         : syafiq
 * Date / Time  : 28 October 2016, 4:44 AM.
 * Email        : syafiq.rezpector@gmail.com
 * Github       : syafiqq
 */

namespace model;

include_once $_SERVER['DOCUMENT_ROOT'].'/model/DBModel.php';

class Currency extends DBModel
{
    private static $instance;

    protected function __construct()
    {
        parent::__construct();
    }

    /**
     * @return Currency
     */
    public static function getInstance()
    {
        if(self::$instance == null)
        {
            self::$instance = new Currency();
        }
        return self::$instance;
    }

    public function getCurrencyID($code = null)
    {
        $this->reconnect();
        $currencyID = -1;
        $this->db->where ('currency.code', $code);
        $currency = $this->db->getOne ('currency', array('currency.id'));
        if($currency != null)
        {
            if(is_array($currency) && $this->db->count > 0)
            {
                $currencyID = $currency['id'];
            }
        }
        return $currencyID;
    }
}