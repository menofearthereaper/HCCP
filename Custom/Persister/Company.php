<?php
/**
 * Created by PhpStorm.
 * User: Ryan
 * Date: 31/01/2016
 * Time: 7:30 PM
 */

namespace Persister;

/**
 * Class Company - contains all functions for persistent storage of Company data
 * @package Persister
 */
class Company
{
    /** @var \SQLite3 $db */
    private $db;

    public function __construct(\SQLite3 $db)
    {
        $this->db = $db;
    }

    /**
     * Function takes an array of Model\Company objects deletes the current contents of the company table and replaces it
     * with the contents of the modelArray
     * @param \Model\Company[] $modelArray
     */
    public function replaceAll($modelArray)
    {
        // nuke existing records and replace with the new ones
        // yes this is a blunt way of doing it, esp if we have audit trails on the DB or anything like that,
        // but from what I can see audit trails are out of scope, and given I am stuck in windows / xampp hell
        // for the time being, I am not about to burn any more of my time trying to get memcache installed and working
        $this->db->exec('BEGIN');
        $this->deleteAll();
        foreach ($modelArray as $model) {
            $this->save($model);
        }
        $this->db->exec('COMMIT;');

    }

    public function deleteAll()
    {
        $sql = 'DELETE FROM companyDetails';
        $this->db->exec($sql);
    }

    /**
     * @param \Model\Company $model
     */
    public function save($model)
    {

        $sql = "
INSERT INTO companyDetails (company, proposedCode, listingDate, contact, activities, industryGroup, issuePrice, issueType, securityCode, capitalToRaise, expextedCloseDate, underwriter)
VALUES (:company, :proposedCode,:listingDate, :contact, :activities, :industryGroup,  :issuePrice, :issueType,:securityCode, :capitalToRaise, :expectedCloseDate, :underwriter)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':company', $model->company);
        $stmt->bindValue(':proposedCode', $model->proposedCode);
        $stmt->bindValue(':listingDate', $model->listingDate);
        $stmt->bindValue(':contact', $model->contact);
        $stmt->bindValue(':activities', $model->activities);
        $stmt->bindValue(':industryGroup', $model->industryGroup);
        $stmt->bindValue(':securityCode', $model->securityCode);
        $stmt->bindValue(':issuePrice', $model->issuePrice);
        $stmt->bindValue(':issueType', $model->issueType);
        $stmt->bindValue(':capitalToRaise', $model->capitalToRaise);
        $stmt->bindValue(':expectedCloseDate', $model->expectedCloseDate);
        $stmt->bindValue(':underwriter', $model->underwriter);
        $stmt->execute();
    }
}