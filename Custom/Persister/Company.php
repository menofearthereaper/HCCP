<?php
/**
 * Created by PhpStorm.
 * User: Ryan
 * Date: 31/01/2016
 * Time: 7:30 PM
 */

namespace Persister;

use Utils\Logger;

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

    /**
     * Function deletes a record from the companyDetails table with a given id
     * @param $id
     */
    public function delete($code)
    {
        $this->db->exec('BEGIN');
        $sql = 'DELETE FROM companyDetails WHERE proposedCode = :code';
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':code', $code);
        $this->db->exec('COMMIT;');
    }

    /**
     * Function fetches all rows out of the DB and returns an array of Model/Company objects
     * @return \Model\Company[]
     */
    public function getAll()
    {
        $retArr = array();
        $sql = 'SELECT * FROM companyDetails';
        $rs = $this->db->query($sql);
        while ($row = $rs->fetchArray()) {
            $model = new \Model\Company($row);
            $retArr[$row['proposedCode']] = $model;
        }
        return $retArr;
    }

    /**
     * Function deletes all records in the companyDetails table
     */
    private function deleteAll()
    {
        $this->db->exec('BEGIN');
        $sql = 'DELETE FROM companyDetails';
        $this->db->exec($sql);
        $this->db->exec('COMMIT');
    }

    /**
     * @param \Model\Company $model
     */
    public function save($model)
    {
        $this->db->exec('BEGIN');
        $sql = "
INSERT INTO companyDetails (company, proposedCode, listingDate, contact, activities, industryGroup, issuePrice, issueType, securityCode, capitalToRaise, expectedCloseDate, underwriter)
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
        $this->db->exec('COMMIT');
    }

    public function update($model)
    {

        $this->db->exec('BEGIN');
        $sql = "
UPDATE companyDetails
SET company = :company, listingDate = :listingDate, contact = :contact, activities = :activities, capitalToRaise = :capitalToRaise,
industryGroup = :industryGroup, issuePrice = :issuePrice, issueType = :issueType, securityCode = :securityCode,
expectedCloseDate = :expectedCloseDate, underwriter = :underwriter
WHERE proposedCode = :proposedCode";
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
        $this->db->exec('COMMIT');
    }

    public function addComment($code, $comment)
    {
        $this->db->exec('BEGIN');
        $sql = 'UPDATE companyDetails SET comment=:comment WHERE proposedCode=:code';
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':comment', trim($comment));
        $stmt->bindValue(':code', trim($code));
        $result = $stmt->execute();
        var_dump($result->numColumns());
        $this->db->exec('COMMIT');
    }
}