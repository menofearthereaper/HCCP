<?php
/**
 * Created by PhpStorm.
 * User: Ryan
 * Date: 30/01/2016
 * Time: 3:56 PM
 */

namespace Utils;

/**
 * Class Logger - it erm.... logs things
 * Singleton.
 */
class Logger
{
    /**
     * @var null instance of self
     */
    private static $instance = null;

    /**
     * make constructer private to prevent multiple instantiation
     */
    private function __construct()
    {
    }

    /**
     * @param $file - log file we are writing to
     * @param $logMessage - message to be logged
     * @param mixed $data - The offending data that has triggered the log to be written or FALSE
     */
    function write($file, $logMessage, $data = false)
    {
        $log = self::createLogMessage($logMessage, $data);
        // echo log out so we can see it on command line / html page
        if (LOG_ECHO) {
            // drop in some extra spacing between log lines, just makes it easier to read whats being dumped
            // yeah its hacky but given I dont have Xdebug working on this windows environ, I needed something quick and
            // dirty to help debug and dump data where I could see it
            echo $log . '<br><br>';
        }
        self::writeLog($file, $log);
    }

    /**
     * Function pulls together various details and creates a log message
     * @param $message - original log message without additional details
     * @param mixed $data - The offending data that has triggered the log to be written or FALSE
     * @return string
     */
    private function createLogMessage($message, $data = false)
    {
        // get message
        $logMessage = ' - MESSAGE: ' . $message;
        if ($data && LOG_DETAIL) {
            if (LOG_DETAIL) {
                $logMessage = $logMessage . " - DATA : " . print_r($data, true);
            }
        }

        // get a timestamp and glue it all together
        $log = date(DATE_RFC2822) . $logMessage . " \n ";
        return $log;
    }


    /**
     * Function writes to file - same as file_put_contents with FILE_APPEND flag set  except that it will create the
     * file if it doesnt exist already.
     * @param $logFile - file to write to
     * @param $log - text to write
     */
    private function writeLog($logFile, $log)
    {
        $filePointer = fopen($logFile, "a+");
        fwrite($filePointer, $log);
        fclose($filePointer);
    }

    /**
     * Function returns the current instance of logger, if no instance exists it creates one
     * @return Logger
     */
    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new logger;
        }
        return self::$instance;
    }
}