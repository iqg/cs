<?php
/**
 * Created by PhpStorm.
 * User: jokeikusunoki
 * Date: 15/9/2
 * Time: 下午2:32
 */

namespace DWD\CSAdminBundle\Utils;

class DwdAPI
{
    /**
     * Set up the API root URL
     */
    private $host = "http://staging.iqianggou.com/";

    /**
     * @var $http_client \Guzzle\Service\Client
     */
    private $http_client;

    /**
     * @param $guzzle
     */
    public function __construct($guzzle)
    {
        $this->http_client = $guzzle;
    }

    public function error($error = NULL){
        if (is_null($error))
            return $this->error;

        $this->error = $error;
        return false;
    }

    public function login_brandadmin($params = array())
    {
        if (!isset($params['username']) || !isset($params['password']) ) {
            return $this->error( 'params is empty' );
        }

        $uri = 'api/brandadmin/login';
        $request = $this->http_client->post( $this->host . $uri,
            array('content-type' => 'application/json'),
            array('account' => $params['username'], 'password' => $params['password'])
        );
        $response = $this->http_client->send($request)->json();

        if(!is_array($response) || !$response)
            return $this->error("request failed");

        return $response;
    }
}