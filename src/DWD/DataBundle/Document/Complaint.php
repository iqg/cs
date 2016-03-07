<?php

namespace DWD\DataBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\Document(collection="Complaint", repositoryClass="DWD\DataBundle\Repository\ComplaintRepository")
 */
class Complaint
{
    /**
     * @MongoDB\Id
     */
    protected $id;

    /**
     * @MongoDB\Int
     */
    protected $source;

    /**
     * @MongoDB\String
     */
    protected $type;

    /**
     * @MongoDB\Collection
     */
    protected $tags;

    /**
     * @MongoDB\String
     */
    protected $mobile;

    /**
     * @MongoDB\Int
     */
    protected $method;

    /**
     * @MongoDB\Int
     */
    protected $platform;

    /**
     * @MongoDB\String
     */
    protected $op;

    /**
     * @MongoDB\Int
     */
    protected $branchId; 

    /**
     * @MongoDB\Int
     */
    protected $userId;

    /**
     * @MongoDB\Hash
     */
    protected $complaintInfo;

    /**
     * @MongoDB\Int
     */
    protected $status;

    /**
     * @MongoDB\String
     */
    protected $note;

    /**
     * @MongoDB\Int
     */
    protected $salerId;

    /**
     * @MongoDB\Int
     */
    protected $labelType;
    /**
     * @MongoDB\Int
     */
    protected $zoneId;

    /**
     * @MongoDB\Int
     */
    protected $createdAt;

    /**
     * @MongoDB\Int
     */
    protected $resolvedAt;

    /**
     * @MongoDB\Collection
     */
    protected $oplog;

    /**
     * Get id
     *
     * @return id $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set source
     *
     * @param int $source
     * @return self
     */
    public function setSource($source)
    {
        $this->source = $source;
        return $this;
    }

    /**
     * Get source
     *
     * @return int $source
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * Set type
     *
     * @param string $type
     * @return self
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Get type
     *
     * @return string $type
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set tags
     *
     * @param collection $tags
     * @return self
     */
    public function setTags($tags)
    {
        $this->tags = $tags;
        return $this;
    }

    /**
     * Get tags
     *
     * @return collection $tags
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * Set mobile
     *
     * @param string $mobile
     * @return self
     */
    public function setMobile($mobile)
    {
        $this->mobile = $mobile;
        return $this;
    }

    /**
     * Get mobile
     *
     * @return string $mobile
     */
    public function getMobile()
    {
        return $this->mobile;
    }

    /**
     * Set method
     *
     * @param int $method
     * @return self
     */
    public function setMethod($method)
    {
        $this->method = $method;
        return $this;
    }

    /**
     * Get method
     *
     * @return int $method
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Set salerId
     *
     * @param int $salerId
     * @return self
     */
    public function setSalerId($salerId)
    {
        $this->salerId = $salerId;
        return $this;
    }

    /**
     * Get salerId
     *
     * @return int $salerId
     */
    public function getSalerId()
    {
        return $this->salerId;
    }

    /**
     * Set labelType
     *
     * @param int $labelType
     * @return self
     */
    public function setLabelType($labelType)
    {
        $this->labelType = $labelType;
        return $this;
    }

    /**
     * Get labelType
     *
     * @return int $labelType
     */
    public function getLabelType()
    {
        return $this->labelType;
    }

    /**
     * Set method
     *
     * @param int $method
     * @return self
     */
    public function setZoneId($zoneId)
    {
        $this->zoneId = $zoneId;
        return $this;
    }

    /**
     * Get zoneId
     *
     * @return int $zoneId
     */
    public function getZoneId()
    {
        return $this->zoneId;
    }

    /**
     * Set platform
     *
     * @param int $platform
     * @return self
     */
    public function setPlatform($platform)
    {
        $this->platform = $platform;
        return $this;
    }

    /**
     * Get platform
     *
     * @return int $platform
     */
    public function getPlatform()
    {
        return $this->platform;
    }

    /**
     * Set op
     *
     * @param string $op
     * @return self
     */
    public function setOp($op)
    {
        $this->op = $op;
        return $this;
    }

    /**
     * Get op
     *
     * @return string $op
     */
    public function getOp()
    {
        return $this->op;
    }

    /**
     * Set branchId
     *
     * @param int $branchId
     * @return self
     */
    public function setBranchId($branchId)
    {
        $this->branchId = $branchId;
        return $this;
    }

    /**
     * Get branchId
     *
     * @return int $branchId
     */
    public function getBranchId()
    {
        return $this->branchId;
    }

    /**
     * Set userId
     *
     * @param int $userId
     * @return self
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
        return $this;
    }

    /**
     * Get userId
     *
     * @return int $userId
     */
    public function getUserId()
    {
        return $this->userId;
    }
 
    /**
     * Set status
     *
     * @param int $status
     * @return self
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * Get status
     *
     * @return int $status
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set note
     *
     * @param string $note
     * @return self
     */
    public function setNote($note)
    {
        $this->note = $note;
        return $this;
    }

    /**
     * Get note
     *
     * @return string $note
     */
    public function getNote()
    {
        return $this->note;
    }

    /**
     * Set createdAt
     *
     * @param int $createdAt
     * @return self
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * Get createdAt
     *
     * @return int $createdAt
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set resolvedAt
     *
     * @param int $resolvedAt
     * @return self
     */
    public function setResolvedAt($resolvedAt)
    {
        $this->resolvedAt = $resolvedAt;
        return $this;
    }

    /**
     * Get resolvedAt
     *
     * @return int $resolvedAt
     */
    public function getResolvedAt()
    {
        return $this->resolvedAt;
    }

    /**
     * Set oplog
     *
     * @param collection $oplog
     * @return self
     */
    public function setOplog($oplog)
    {
        $this->oplog = $oplog;
        return $this;
    }

    /**
     * Get oplog
     *
     * @return collection $oplog
     */
    public function getOplog()
    {
        return $this->oplog;
    }

    /**
     * Set complaintInfo
     *
     * @param hash $complaintInfo
     * @return self
     */
    public function setComplaintInfo($complaintInfo)
    {
        $this->complaintInfo = $complaintInfo;
        return $this;
    }

    /**
     * Get complaintInfo
     *
     * @return hash $complaintInfo
     */
    public function getComplaintInfo()
    {
        return $this->complaintInfo;
    }
}
