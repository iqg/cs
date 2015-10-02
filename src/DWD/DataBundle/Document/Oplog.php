<?php

namespace DWD\DataBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\Document(collection="Oplog", repositoryClass="DWD\DataBundle\Repository\OplogRepository")
 */
class Oplog
{
	/**
     * @MongoDB\Id
     */
    protected $id;

    /**
     * @MongoDB\Int
     */
    protected $adminId;

    /**
     * @MongoDB\String
     */
    protected $route;

    /**
     * @MongoDB\Bool
     */
    protected $res;

    /**
     * @MongoDB\Int
     */
    protected $cratedAt;

    /**
     * @MongoDB\String
     */
    protected $ext;

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
     * Set adminId
     *
     * @param int $adminId
     * @return self
     */
    public function setAdminId($adminId)
    {
        $this->adminId = $adminId;
        return $this;
    }

    /**
     * Get adminId
     *
     * @return int $adminId
     */
    public function getAdminId()
    {
        return $this->adminId;
    }

    /**
     * Set route
     *
     * @param string $route
     * @return self
     */
    public function setRoute($route)
    {
        $this->route = $route;
        return $this;
    }

    /**
     * Get route
     *
     * @return string $route
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * Set res
     *
     * @param bool $res
     * @return self
     */
    public function setRes($res)
    {
        $this->res = $res;
        return $this;
    }

    /**
     * Get res
     *
     * @return bool $res
     */
    public function getRes()
    {
        return $this->res;
    }

    /**
     * Set cratedAt
     *
     * @param int $cratedAt
     * @return self
     */
    public function setCratedAt($cratedAt)
    {
        $this->cratedAt = $cratedAt;
        return $this;
    }

    /**
     * Get cratedAt
     *
     * @return int $cratedAt
     */
    public function getCratedAt()
    {
        return $this->cratedAt;
    }

    /**
     * Set ext
     *
     * @param string $ext
     * @return self
     */
    public function setExt($ext)
    {
        $this->ext = $ext;
        return $this;
    }

    /**
     * Get ext
     *
     * @return string $ext
     */
    public function getExt()
    {
        return $this->ext;
    }
}
