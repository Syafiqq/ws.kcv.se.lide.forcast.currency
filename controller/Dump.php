<?php
/**
 * This <ws.kcv.se.lide.forcast.currency> project created by :
 * Name         : syafiq
 * Date / Time  : 27 October 2016, 12:11 PM.
 * Email        : syafiq.rezpector@gmail.com
 * Github       : syafiqq
 */

namespace controller;
use Carbon\Carbon;
use model\database\DatabaseManager;
use model\logger\Logger;
use Pux;

include_once $_SERVER['DOCUMENT_ROOT'].'/model/logger/Logger.php';
include_once $_SERVER['DOCUMENT_ROOT'].'/model/database/DatabaseManager.php';

class Dump extends Pux\Controller
{
    private $logger;
    private $db;

    public function __construct()
    {
        $this->logger = Logger::getInstance();
        $this->db = DatabaseManager::getInstance();
        $this->logger->addDebug("Dump/__construct");
    }

    public function carbon()
    {
        $this->logger->addDebug("Dump/carbon");

        printf("Right now is %s", Carbon::now()->toDateTimeString());
        printf("Right now in Vancouver is %s", Carbon::now('America/Vancouver'));  //implicit __toString()
        $tomorrow = Carbon::now()->addDay();
        $lastWeek = Carbon::now()->subWeek();
        $nextSummerOlympics = Carbon::createFromDate(2012)->addYears(4);

        $officialDate = Carbon::now()->toRfc2822String();

        $howOldAmI = Carbon::createFromDate(1975, 5, 21)->age;

        $noonTodayLondonTime = Carbon::createFromTime(12, 0, 0, 'Europe/London');

        $worldWillEnd = Carbon::createFromDate(2012, 12, 21, 'GMT');

        // Don't really want to die so mock now
        Carbon::setTestNow(Carbon::createFromDate(2000, 1, 1));

        // comparisons are always done in UTC
        if (Carbon::now()->gte($worldWillEnd)) {
            die();
        }

        // Phew! Return to normal behaviour
        Carbon::setTestNow();

        if (Carbon::now()->isWeekend()) {
            echo 'Party!';
        }
        echo Carbon::now()->subMinutes(2)->diffForHumans(); // '2 minutes ago'

        // ... but also does 'from now', 'after' and 'before'
        // rolling up to seconds, minutes, hours, days, months, years

        $daysSinceEpoch = Carbon::createFromTimestamp(0)->diffInDays();
    }

    public function database()
    {
        if($this->db->ping())
        {
            //echo "Connected";
        }
        else
        {
            echo "Can't connect";
        }
    }

    public function carbon_001()
    {
        $date = Carbon::createFromDate(2016, 10, 26);
        for($i = -1, $is = Carbon::DAYS_PER_WEEK * Carbon::WEEKS_PER_YEAR; ++$i < $is;)
        {
            $date->addDay(-1);
            printf($date->toDateString());
            printf('<br>');
        }
    }
}