<?php
/**
 * Created by PhpStorm.
 * User: Ryan
 * Date: 29/01/2016
 * Time: 8:04 PM
 */

namespace Controller;

use Utils\Container as Container;
use Model\Company as Model;
use DOMDocument;
use Utils\Logger;

class Upcoming
{
    /** @var \SQLite3 $db */
    protected $db;
    /** @var \Connection\AsyncCurl $asyncConn */
    protected $asyncConn;

    public function __construct(Container $container)
    {
        $this->db = $container->getDb();
        $this->asyncConn = $container->getAsyncCurl(ASX_BASE_URL);
    }

    /**
     * Function retrieves upcoming IPOs
     */
    public function update()
    {
        /** @var Model[] $companyData */
        $companyData = $this->scrapeData();
        $persister = new \Persister\Company($this->db);
        echo print_r($companyData, false);
        $persister->replaceAll($companyData);

    }

    /**
     * Function scrapes the upcoming ipo's page, for each ipo it scrapes the company data page, and converts the scraped
     * data into an array of model objects.
     *
     *
     * Ideally i would want to map the property names against element id's or something more concrete than the order in
     * which they appear in the table, but given the table structure and the probability of the table labels changing
     * being about the same as their position moving I will simply stick with relying on the position of the table rows
     * to match the property name.
     *
     * In a production system i would want some form of email notification or message sent to monitoring system (nagios?)
     * if the table structure changes (number of rows change and/or labels change) anything to prompt someone to eyeball
     * the page and make sure there hasnt been some change at the other end which breaks this script
     *
     *
     * @return Model[]
     * @throws \Exception
     * @throws \LogicException
     */
    private function scrapeData()
    {
        $dataArr = [];
        $ipo = file_get_contents(ASX_BASE_URL . '/prices/upcoming.htm');
        $propertyNames = ['company', 'proposedCode', 'listingDate'];
        // sanity check we have rows - technically no rows shouldnt be an exception it should just mean that there are
        // no new IPO's on the radar, so we are just protecting the foreach loop here.
        if ($rows = $this->getTableRows($ipo)) {
            /**
             * @var int $rowIndex - This is used as the key value in the asyncConn pool as well as the array of data scraped
             *      from the upcoming.html page, using this same key value allows the data to be merged back together
             * @var  \DOMElement $row
             */
            foreach ($rows as $rowIndex => $row) {
                // fetch columns out of the table
                $cols = $row->getElementsByTagName('td');
                /**
                 * @var \DOMNodeList $cols - IF it is null then somethings gone BOOM as we should always have columns in
                 * the row data
                 */
                if ($cols) {
                    /**
                     * @var int $i
                     * @var \DOMElement $node
                     */
                    foreach ($cols as $i => $node) {
                        // the first column of the table contains a href to fetch the company details for the ipo so shove
                        // it into asyncConn pool to fetch the html.
                        if ($i == 0) {
                            // fetch the href out of the first column and use it to scrape the company details
                            if ($companyDetailsUrl = $node->getElementsByTagName('a')[0]->getAttribute('href')) {
                                // add request to pool, index on $rowIndex for merge on unwind, dont need additional options
                                // so use an empty array
                                $this->asyncConn->addPromise($rowIndex, $companyDetailsUrl, []);

                            } else {
                                // if we cant fetch the href we are in trouble so log it and throw exe
                                $message = 'Could not find href for company details page for proposed asx code:' . $this->stripAndTrim($node->ownerDocument->saveXML($node));
                                // take the contents of the node, convert it back into a string and throw it at the logger
                                Logger::getInstance()->write(ERROR, $message, $node->ownerDocument->saveXML($node));
                                throw new \Exception($message);
                            }
                        }
                        // trim and strip out excessive whitespace from data
                        $rowVals[$propertyNames[$i]] = $this->stripAndTrim($node->nodeValue);
                    }
                    if (isset($rowVals)) {
                        $dataArr[$rowIndex] = $rowVals;
                    }
                    unset($rowVals);
                } else {
                    throw new \LogicException('Table row has no columns');
                }
            }
        }
        $completeDataArr = $this->unWrapAndMerge($dataArr);
        return $completeDataArr;
    }

    /**
     * Function unwraps the curl responses in the async data pool, merges it into the array of partial company records
     * and returns the resulting completed array of company data.
     * @param $dataArr - array of partial company data pulled from the upcoming ipo page
     * @return array - array of completed company data records
     * @throws \Exception
     */
    private function unWrapAndMerge($dataArr)
    {
        // send Async curl now that promises have all been made
        $responseArr = $this->asyncConn->unwrap();
        /**
         * @var int $index
         * @var \GuzzleHttp\Psr7\Response $response
         */
        foreach ($responseArr as $index => $response) {
            // fetch the raw html out of response body
            $html = $response->getBody();
            // If we have no data log it and trigger an error. no need to thropw exception and quit processing, just
            // skip that record from the record set and keep trucking.
            if ($html) {
                $companyData = $this->scrapeCompanyData($html);
                $dataArr[$index] = new Model(array_merge($companyData, $dataArr[$index]));
            } else {
                // something didnt come back in the async curl, fetch what we can out of the response and log it
                $message = 'Error retrieving company data. Will drop the current entry and continue processing. Check error log for details.';
                $logData = [
                    'proposed code' => $dataArr[$index]['proposedCode'],
                    'status' => $response->getStatusCode(),
                    'reason' => $response->getReasonPhrase(),
                    'headers' => $response->getHeaders()
                ];
                Logger::getInstance()->write(ERROR, $message, $logData);
                // TODO set an error handler to suppress unwanted details from being exposed to user. for now I am going to keep default behavior
                trigger_error($message, E_USER_NOTICE);
            }
        }
        return $dataArr;
    }

    /**
     * Function trims and strips excessive whitespace out of a string - quick and dirty method of cleaning up the
     * company details information which appears to have whitespace up the wazoo
     * @param $str
     * @return mixed
     */
    private function stripAndTrim($str)
    {
        // TODO - fix this up and make less hacky - possibly implement htmlPurifier or something like that?
        // ditch any 2 or more consecutive spaces and replace with single space.
        // trim any leading or trailing whitespace.
        // strip any html tags that may be in there.
        return preg_replace('/\s\s+/', ' ', trim(strip_tags($str)));
    }


    /**
     * Function takes the html fetched from the asx pages and fetches the table rows out of the first table found
     * This would need a ton of hardening
     * @param $html
     * @return \DOMNodeList
     * @throws \LogicException - if the html is empty
     * @throws \Exception - if there is html but something is awry with it
     */
    private function getTableRows($html)
    {
        $dom = new DOMDocument();
        if (!empty($html)) {
            libxml_use_internal_errors(true);
            @$dom->loadHTML($html);
            $dom->preserveWhiteSpace = false;
            /** @var \DOMElement $table */
            if ($table = $dom->getElementsByTagName('table')->item(0)) {
                return $table->getElementsByTagName('tr');
            } else {
                // if item(0) is null throw exception
                $message = 'Could not locate a table in html';
                // TODO - remove this Logger entry once things are stable - leave the exception tho!
                // lets log the HTML for now so we can track why things went snap! all current tables have rows so to
                // trip this exception something had to have gone "bad-a-boom"
                Logger::getInstance()->write(ERROR, $message, $html);
                throw new \Exception($message);
            }
        } else {
            throw new \LogicException('html should not be empty.');
        }
    }


    /**
     * Function takes the raw html from the company data page and scrapes the company info out of it
     * returning an array of [name => value] pairs where name are the model property names.
     *
     * Ideally i would want to map the property names against element id's or something more concrete than the order in
     * which they appear in the table, but given the table structure and the probability of the table labels changing
     * being about the same as their position moving I will simply stick with relying on the position of the table rows
     * to match the property name.
     *
     * In a production system i would want some form of email notification or message sent to monitoring system (nagios?)
     * if the table structure changes (number of rows change and/or labels change) anything to prompt someone to eyeball
     * the page and make sure there hasnt been some change at the other end which breaks this script
     *
     * @param $html - HTML string
     * @return array
     * @throws \Exception
     */
    private function scrapeCompanyData($html)
    {
        // init $dataArray to empty []
        $dataArr = [];
        $propertyNames = [
            'contact',
            'activities',
            'industryGroup',
            'issuePrice',
            'issueType',
            'securityCode',
            'capitalToRaise',
            'expectedCloseDate',
            'underwriter'
        ];
        /** @var \DOMNodeList $rows */
        if ($rows = $this->getTableRows($html)) {
            /** @var  \DOMElement $row */
            foreach ($rows as $i => $row) {
                $cols = $row->getElementsByTagName('td');
                // table columns in this table are in the format [ label , value ] we only need the values
                $dataArr[$propertyNames[$i]] = $this->stripAndTrim($cols[1]->nodeValue);
            }
        } else {
            // if getTableRows has returned a null something has gone snap as the company data table should always be populated
            $message = 'The company details table exists but is empty';
            throw new \Exception($message);
        }

        return $dataArr;
    }
}
