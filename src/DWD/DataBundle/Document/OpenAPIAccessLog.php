<?php

namespace DWD\DataBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as Mongo;

/**
 * @Mongo\Document(collection="openapi_access_logs")
 */
class OpenAPIAccessLog
{
    /**
     * @Mongo\Id
     */
    private $id;

    /**
     * @Mongo\String
     */
    private $method;

    /**
     * @Mongo\Hash
     */
    private $request;

    /**
     * @Mongo\Hash
     */
    private $query;

    /**
     * @Mongo\Hash
     */
    private $header;

    /**
     * @Mongo\String
     */
    private $path;

    /**
     * @Mongo\Integer
     */
    private $ResponseStatusCode;

    /**
     * @Mongo\Float
     */
    private $cost;

    /**
     * @Mongo\Integer
     */
    private $request_time;

    /**
     * @Mongo\String
     */
    private $server_ip;

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
     * Set method
     *
     * @param string $method
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
     * @return string $method
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Set request
     *
     * @param hash $request
     * @return self
     */
    public function setRequest($request)
    {
        $this->request = $request;
        return $this;
    }

    /**
     * Get request
     *
     * @return hash $request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Set query
     *
     * @param hash $query
     * @return self
     */
    public function setQuery($query)
    {
        $this->query = $query;
        return $this;
    }

    /**
     * Get query
     *
     * @return hash $query
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * Set header
     *
     * @param hash $header
     * @return self
     */
    public function setHeader($header)
    {
        $this->header = $header;
        return $this;
    }

    /**
     * Get header
     *
     * @return hash $header
     */
    public function getHeader()
    {
        return $this->header;
    }

    /**
     * Set path
     *
     * @param string $path
     * @return self
     */
    public function setPath($path)
    {
        $this->path = $path;
        return $this;
    }

    /**
     * Get path
     *
     * @return string $path
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set responseStatusCode
     *
     * @param integer $responseStatusCode
     * @return self
     */
    public function setResponseStatusCode($responseStatusCode)
    {
        $this->ResponseStatusCode = $responseStatusCode;
        return $this;
    }

    /**
     * Get responseStatusCode
     *
     * @return integer $responseStatusCode
     */
    public function getResponseStatusCode()
    {
        return $this->ResponseStatusCode;
    }

    /**
     * Set cost
     *
     * @param float $cost
     * @return self
     */
    public function setCost($cost)
    {
        $this->cost = $cost;
        return $this;
    }

    /**
     * Get cost
     *
     * @return float $cost
     */
    public function getCost()
    {
        return $this->cost;
    }

    /**
     * Set requestTime
     *
     * @param integer $requestTime
     * @return self
     */
    public function setRequestTime($requestTime)
    {
        $this->request_time = $requestTime;
        return $this;
    }

    /**
     * Get requestTime
     *
     * @return integer $requestTime
     */
    public function getRequestTime()
    {
        return $this->request_time;
    }

    /**
     * Set serverIp
     *
     * @param string $serverIp
     * @return self
     */
    public function setServerIp($serverIp)
    {
        $this->server_ip = $serverIp;
        return $this;
    }

    /**
     * Get serverIp
     *
     * @return string $serverIp
     */
    public function getServerIp()
    {
        return $this->server_ip;
    }
}
