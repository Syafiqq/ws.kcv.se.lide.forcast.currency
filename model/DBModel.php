<?php
/**
 * This <ws.kcv.se.lide.forcast.currency> project created by :
 * Name         : syafiq
 * Date / Time  : 28 October 2016, 1:19 PM.
 * Email        : syafiq.rezpector@gmail.com
 * Github       : syafiqq
 */

namespace model;
use model\database\DatabaseManager;
use model\logger\Logger;

include_once $_SERVER['DOCUMENT_ROOT'].'/model/logger/Logger.php';
include_once $_SERVER['DOCUMENT_ROOT'].'/model/database/DatabaseManager.php';

class DBModel
{
    protected $logger;
    protected $db;

    protected function __construct()
    {
        $this->logger = Logger::getInstance();
        $this->db = DatabaseManager::getInstance();
    }

    protected function reconnect()
    {
        if(!$this->db->ping())
        {
            $this->db->connect();
        }
    }
}