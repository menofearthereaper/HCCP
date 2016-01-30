<?php
/**
 * Created by PhpStorm.
 * User: Ryan
 * Date: 29/01/2016
 * Time: 7:01 PM
 */

namespace Model;


/**
 * Class Company
 * @package Model
 */
class Company extends Base
{
    /** Originally had a separate model for the data scraped out of prices/upcoming.html Decided it wasnt necessary as
     * from what I can tell there should always be a 1:1 relationship between upcoming IPO records and the
     * /research/upcomingFloatDetail pages, hence I merged the data into one single model class.
     *
     */
    /** @var  $company string */
    protected $company;
    /** @var  $code string */
    protected $proposedCode;
    /** @var  $listingDate string */
    protected $listingDate;
    /** End of params originally inherited from 'upcoming' class */

    /** @var  $contact string */
    protected $contact;
    /** @var  $activities string */
    protected $activities;
    /** @var  $industryGroup string */
    protected $industryGroup;
    /** @var  $issuePrice string */
    protected $issuePrice;
    /** @var  $issueType string - I would consider converting to enum if the issue type was a fixed list of vals */
    protected $issueType;
    /** @var  $securityCode string */
    protected $securityCode;
    /** @var  $capitalToRaise string - I would convert to int if I could be sure the capitol is always in num format */
    protected $capitalToRaise;
    /** @var  $expectedCloseDate string - I would convert to datetime if I could be sure the date was in consistant format */
    protected $expectedCloseDate;
    /** @var  $underwriter string */
    protected $underwriter;


}