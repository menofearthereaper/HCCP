<?php
/**
 * Created by PhpStorm.
 * User: Ryan
 * Date: 30/01/2016
 * Time: 2:03 PM
 */

/**
 * Typically for a quick prototype I would structure defines for multiple environments like this. When resources
 * are available I would use Vagrant/puppet for clean build/tear down of environments.
 */
if (!defined('ENVIRONMENT')) {
    DEFINE('ENVIRONMENT', 'dev');
}
switch (ENVIRONMENT) {
    case 'live':
        // Live DB
        DEFINE('DB_HOST', "");
        DEFINE('DB_USER', "");
        DEFINE('DB_PASS', '');
        DEFINE('LOG_DETAIL', 0);
        DEFINE('LOG_ECHO', 0);
        break;
    case 'test':
        DEFINE('DB_HOST', "");
        DEFINE('DB_USER', "");
        DEFINE('DB_PASS', '');
        DEFINE('LOG_DETAIL', 1);
        DEFINE('LOG_ECHO', 0);
        break;
    case 'dev':
        DEFINE('DB_HOST', "192.168.1.166");
        DEFINE('DB_USER', "root");
        DEFINE('DB_PASS', 'root');
        DEFINE('LOG_DETAIL', 1); //true for loging full stack traces and print_f's of variables 0 for simple logging
        DEFINE('LOG_ECHO', 1); // echo logs back to user???
        break;
}


// define the base url for the asx website
define('ASX_BASE_URL', 'http://www.asx.com.au');

/**
 * Logger settings
 */
define('LOG', 'Logs/log.log');
define('ERROR', 'Logs/error.log');


/**
 * Asana creds
 */
define('ASANA_WORKSPACE', '18930037370237');
define('ASANA_ASIGNEE', '81762596593527');
define('ASANA_TOKEN', '0/359ebb4e817ae1512f8bb194ea51e450 ');
define('ASANA_PROJECT_ID', '81762813064377');

