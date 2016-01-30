<?php
/**
 * Created by PhpStorm.
 * User: Ryan
 * Date: 24/01/2016
 * Time: 11:03 AM
 */
include 'Custom\Resources\config.php';
use Utils\Container as Container;
use Controller\Upcoming as Upcoming;

// create autoloader - Because I couldnt get virtual machines talking to my home network i had to use xampp on windows YUCK!
// Changing the WINDOWS_FILEPATHS var should get the autoloader working un unix environments but I havent had a chance to test it
spl_autoload_register(function ($class) {
    if (WINDOWS_FILEPATHS) {
        $path = 'Custom\\' . $class . '.php';
        if (file_exists($path)) {
            require_once($path);
        }
    } else {
        // blow up the class path
        $parts = explode('\\', $class);
        // replace the \ with / in class path and glue it back together
        $path = "Custom/" . implode('/', $parts) . '.php';
        // profit!
        if (file_exists($path)) {
            require_once($path);
        }
    }
});
// add in the composer autoloader.
require_once 'Vendor/autoload.php';
try {
    $controller = new Upcoming(new Container());
    $controller->update();
} catch (Exception $e) {
    \Utils\Logger::getInstance()->write(ERROR, $e->getMessage(), $e->getTrace());
}



//$ipo = file_get_contents('http://www.asx.com.au/asx/research/upcomingFloatDetail.do?asxCode=ABT');
//$dom = new DOMDocument();
//$propertyNames = ['contact', 'activities', 'industryGroup','issuePrice','issueType','securityCode','capitalToRaise','expectedCloseDate','underwriter'];
//if (!empty($ipo)) {
//    libxml_use_internal_errors(true);
//    @$dom->loadHTML($ipo);
//    $dom->preserveWhiteSpace = false;
//    //  echo print_r($dom->getElementsByTagName('table'),false);
//    $tables = $dom->getElementsByTagName('table');
//
//    $rows = $tables->item(0)->getElementsByTagName('tr');
//    foreach ($rows as $i => $row) {
//        $cols = $row->getElementsByTagName('td');
//        $dataArr[$propertyNames[$i]] = $cols[1]->nodeValue;
//    }
//    echo print_r($dataArr, true);
//}


//$ipo = file_get_contents('http://www.asx.com.au/prices/upcoming.htm');
//preg_match_all ('#<tr[^>]*>(.*?)</tr>#s',$ipo,$matches);
//$dataArr = [];
//foreach($matches[0] as $i => $row){
//    if($i){
//        preg_match_all('#<td>(.*?)</td>#',$row,$td);
//        preg_match('#<a[^>]*>(.*?)</a>#s',$td[1][0],$company);
//
//        $dataArr[] = ['company'=>$company[1],'code'=>$td[1][1],'date'=>$td[1][2]];
//    }
//}
//echo print_r($dataArr,true);

//
//$ipo = file_get_contents('http://www.asx.com.au/prices/upcoming.htm');
//
//$ipo_doc = new DOMDocument();
//if(!empty($ipo)){
//    libxml_use_internal_errors(true);
//    $ipo_doc->loadHTML($ipo);
//    // remove any errors for dodgy html
//    $ipo_xpath = new DOMXPath($ipo_doc);
//    // fetch the content table
//    //*[@id="content"]/table
//    $tableRows = $ipo_xpath->query('//*[@id="content"]/table/tbody/tr');
//
//    foreach($tableRows as $row){
//        echo $row;
//    }
//}



