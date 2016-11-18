<?php
/**
 * This <ws.kcv.se.lide.forcast.currency> project created by :
 * Name         : syafiq
 * Date / Time  : 27 October 2016, 12:06 PM.
 * Email        : syafiq.rezpector@gmail.com
 * Github       : syafiqq
 */

namespace model\logger;

require $_SERVER['DOCUMENT_ROOT'].'/vendor/autoload.php'; // use PCRE patterns you need Pux\PatternCompiler class.
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger as MonologLogger;

class Logger
{
    private static $instance;
    private $logger;

    /**
     * Logger constructor.
     */
    private function __construct()
    {
        $dateFormat = "Y-m-d H:i:s";
        $output = "[%datetime%] %channel%.%level_name%: %message%\n";
        $formatter = new LineFormatter($output, $dateFormat);

        $this->logger = new MonologLogger('debug');

        $stream = new StreamHandler($_SERVER['DOCUMENT_ROOT'] . '/logs/apps/app.log', MonologLogger::DEBUG);
        $stream->setFormatter($formatter);
        $this->logger->pushHandler($stream);
        //$this->logger->addInfo('Logger.__construct');
    }

    /**
     * @return MonologLogger
     */
    public function getLogger()
    {
        //$this->logger->addInfo('Logger.getLogger');

        return $this->logger;
    }

    /**
     * @return MonologLogger
     */
    public static function getInstance()
    {
        if(self::$instance == null)
        {
            self::$instance = new Logger();
        }
        return self::$instance->getLogger();
    }
}