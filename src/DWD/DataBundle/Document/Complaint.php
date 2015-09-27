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
     * @MongoDB\Int
     */
    protected $userId;

    /**
     * @MongoDB\Int
     */
    protected $branchId;

    /**
     * @MongoDB\Collection
     */
    protected $campaigns;

    /**
     * @MongoDB\Collection
     */
    protected $branchs;

    /**
     * @MongoDB\Collection
     */
    protected $users;

    /**
     * @MongoDB\Collection
     */
    protected $orders;

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
    protected $createdAt;


    /**
     * @MongoDB\Int
     */
    protected $resolvedAt;


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
     * Set campaigns
     *
     * @param collection $campaigns
     * @return self
     */
    public function setCampaigns($campaigns)
    {
        $this->campaigns = $campaigns;
        return $this;
    }

    /**
     * Get campaigns
     *
     * @return collection $campaigns
     */
    public function getCampaigns()
    {
        return $this->campaigns;
    }

    /**
     * Set branchs
     *
     * @param collection $branchs
     * @return self
     */
    public function setBranchs($branchs)
    {
        $this->branchs = $branchs;
        return $this;
    }

    /**
     * Get branchs
     *
     * @return collection $branchs
     */
    public function getBranchs()
    {
        return $this->branchs;
    }

    /**
     * Set users
     *
     * @param collection $users
     * @return self
     */
    public function setUsers($users)
    {
        $this->users = $users;
        return $this;
    }

    /**
     * Get users
     *
     * @return collection $users
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * Set orders
     *
     * @param collection $orders
     * @return self
     */
    public function setOrders($orders)
    {
        $this->orders = $orders;
        return $this;
    }

    /**
     * Get orders
     *
     * @return collection $orders
     */
    public function getOrders()
    {
        return $this->orders;
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
}
