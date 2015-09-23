<?php

namespace DWD\CSAdminBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use DWD\DataBundle\Document\Store;
/**
 * Class ZoneController
 * @package DWD\CSAdminBundle\Controller
 * @Route("/")
 */
class ZoneController extends Controller
{
    
    /** 
     * 如果是混合的关键字，全部转化成拼音
     * @Route("/zone/zonelist", name="dwd_csadmin_zonelist")
     */
    public function ZoneListAction(Request $request)
    {
        $dataHttp       = $this->get('dwd.data.http');  
        $data           = array(
                              array(
                                  'url'    => '/zone/zonelist',
                                  'data'   => array(
                                      'active'    => 1,
                                  ),
                                  'method' => 'get',
                                  'key'    => 'zonelist',
                              ),  
                          );
      
        $response       = $dataHttp->MutliCall( $data );  

        $response       = new Response();
        $response->setContent(json_encode($response['zonelist']['data']['list']));
        return $response;
    }
 
    

}