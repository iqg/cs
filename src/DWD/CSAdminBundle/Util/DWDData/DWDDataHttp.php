<?php
/**
 * Created by PhpStorm.
 * User: caowei
 * Date: 8/24/15
 * Time: 17:48
 */

namespace DWD\CSAdminBundle\Util\DWDData;

use Symfony\Component\DependencyInjection\ContainerInterface as Container;
use \Curl\Curl;
use \Curl\MultiCurl;

class DWDDataHttp
{

    private $_responses  = array();

    const  API_SERVER    = 'http://127.0.0.1/';

    public function __construct(Container $container)
    { 
    }

    static function callback($data, $delay) {
        usleep($delay);
        return $data;
    }

    static function PackageGetRequest( &$ch, $request ){
        $path            =  http_build_query( $request['data'] );
        $url             =  isset( $request['host'] ) ? $request['host'] : SELF::API_SERVER;
        $request['url'] .= '?' . $path;
        curl_setopt($ch, CURLOPT_URL, $url . $request['url']);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_NOSIGNAL, true);
    }

    static function PackagePostRequest( &$ch, $request ){
        $url             =  isset( $request['host'] ) ? $request['host'] : SELF::API_SERVER;
        curl_setopt($ch, CURLOPT_URL, $url . $request['url']);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_NOSIGNAL, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request['data']);
    }

    public function processMutliReseout( $instance )
    {
        $this->_responses[$instance->id] = $instance->response;
    }

    public function getResponse()
    {
        ksort( $this->_responses );
        return $this->_responses;
    }

    public function MutliCall2( $requests )
    {
        $multi_curl      = new MultiCurl();
        $multi_curl->success( function($instance) {
           $key          = $instance->id - 1;
           $this->_responses[$key] = $instance->response;
        } );

        foreach( $requests as $request ){ 

            switch ( $request['method'] ) {
                case 'get':
                    $multi_curl->addGet( $request['url'], $request['data'] );
                    break;
                case 'post':
                    $multi_curl->addPost( $request['url'], $request['data'] );
                    break;
                default: break;
            } 
        } 

        $multi_curl->start();
        return true;
    }

    function MutliCall($requests, $delay = 0) {

        $queue                   = curl_multi_init();
        $map                     = array();

        foreach ($requests as $reqId => $request) {

            if( false == isset( $request['data'] ) || false == is_array( $request['data'] ) ){
                $request['data'] = array();
            }
            $ch                  = curl_init();
            switch ( $request['method'] ) {
                case 'get':
                    self::PackageGetRequest( $ch, $request );
                    break;
                case 'post':
                    self::PackagePostRequest( $ch, $request );
                    break;
                default: break;
            }
            self::PackageGetRequest( $ch, $request );
            curl_multi_add_handle($queue, $ch);
            $map[(string) $ch] = $request['key'];
        }

        $responses        = array();

        do {
            while (($code = curl_multi_exec($queue, $active)) == CURLM_CALL_MULTI_PERFORM) ;

            if ($code != CURLM_OK) { break; }

            // a request was just completed -- find out which one
            while ($done  = curl_multi_info_read($queue)) {

                // get the info and content returned on the request
                $info     = curl_getinfo($done['handle']);
                $error    = curl_error($done['handle']);
                $results  = curl_multi_getcontent($done['handle']);

                if( empty( $error ) ){
                    $responses[$map[(string) $done['handle']]] = json_decode( $results, true );
                } else {
                    $responses[$map[(string) $done['handle']]] = compact('info', 'error', 'results');
                }
                // remove the curl handle that just completed
                curl_multi_remove_handle($queue, $done['handle']);
                curl_close($done['handle']);
            }

            // Block for data in / output; error handling is done by curl_multi_exec
            if ($active > 0) {
                curl_multi_select($queue, 0.5);
            }

        } while ($active);

        curl_multi_close($queue);
//        ksort( $responses );

        return $responses;
    }
}