<?php

namespace DWD\DataBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\Document
 */
class Store
{
    /**
     * @MongoDB\Id
     */
    protected $id;

    /**
     * @MongoDB\String
     */
    protected $branch_id;

    /**
     * @MongoDB\String
     */
    protected $name;

    /**
     * @MongoDB\Float
     */
    protected $zone_id;

    /**
     * @MongoDB\String
     */
    protected $address;

    /**
     * @MongoDB\Float
     */
    protected $lng;

    /**
     * @MongoDB\Float;
     */
    protected $lat;

    /**
     * @MongoDB\Float;
     */
    protected $enabled;

    /**
     * @MongoDB\Collection
     */
    protected $pinyin = array();

    /**
     * Get branchId
     *
     * @return string $branchId
     */
    public function getBranchId()
    {
        return $this->branch_id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return self
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get name
     *
     * @return string $name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set pinyin
     *
     * @param hash $pinyin
     * @return self
     */
    public function setPinyin($pinyin)
    {
        $this->pinyin = $pinyin;
        return $this;
    }

    /**
     * Get pinyin
     *
     * @return hash $pinyin
     */
    public function getPinyin()
    {
        return $this->pinyin;
    }

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
     * Set branchId
     *
     * @param string $branchId
     * @return self
     */
    public function setBranchId($branchId)
    {
        $this->branch_id = $branchId;
        return $this;
    }

    /**
     * Set zoneId
     *
     * @param float $zoneId
     * @return self
     */
    public function setZoneId($zoneId)
    {
        $this->zone_id = $zoneId;
        return $this;
    }

    /**
     * Get zoneId
     *
     * @return float $zoneId
     */
    public function getZoneId()
    {
        return $this->zone_id;
    }

    /**
     * Set address
     *
     * @param string $address
     * @return self
     */
    public function setAddress($address)
    {
        $this->address = $address;
        return $this;
    }

    /**
     * Get address
     *
     * @return string $address
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set lng
     *
     * @param float $lng
     * @return self
     */
    public function setLng($lng)
    {
        $this->lng = $lng;
        return $this;
    }

    /**
     * Get lng
     *
     * @return float $lng
     */
    public function getLng()
    {
        return $this->lng;
    }

    /**
     * Set lat
     *
     * @param float $lat
     * @return self
     */
    public function setLat($lat)
    {
        $this->lat = $lat;
        return $this;
    }

    /**
     * Get lat
     *
     * @return float $lat
     */
    public function getLat()
    {
        return $this->lat;
    }

    /**
     * Set enabled
     *
     * @param float $enabled
     * @return self
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
        return $this;
    }

    /**
     * Get enabled
     *
     * @return float $enabled
     */
    public function getEnabled()
    {
        return $this->enabled;
    }
}
