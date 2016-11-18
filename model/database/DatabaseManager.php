<?php

/**
 * This <sekcv-stroke.esy.es> project created by :
 * Name         : Muhammad Syafiq
 * Date / Time  : 29 September 2016, 4:04 PM.
 * Email        : syafiq.rezpector@gmail.com
 * Github       : syafiqq
 */
namespace model\database;
require_once 'DatabaseProperties.php';

use MysqliDb;

class DatabaseManager
{
    private static $instance;

    private $db;
    /**
     * DatabaseManager constructor.
     */
    private function __construct()
    {
        global $config;
        try
        {
            $this->db = new MysqliDb($config['database']);
        }
        catch (\Exception $ignored)
        {
            sprintf('Database error');
        }
    }

    /**
     * @return MysqliDb
     */
    public static function getInstance()
    {
        if(self::$instance == null)
        {
            self::$instance = new DatabaseManager();
        }
        return self::$instance->db;
    }
}