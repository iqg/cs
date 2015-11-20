<?php

namespace DWD\CSAdminBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use DWD\DataBundle\Document\Store;
use Overtrue\Pinyin\Pinyin;

/**
 * Class DashboardController
 * @package DWD\CSAdminBundle\Controller
 * @Route("/")
 */
class DashboardController extends Controller
{
    /**
     *
     * @Route("/",name="dwd_csadmin_dashboard")
     */
    public function indexAction(Request $request)
    { 
      	$errMsg          = $this->getRequest()->get('errMsg', ""); 
        $dataHttp        = $this->get('dwd.data.http');  
        $data            = array(
                               array(
                                   'url'    => '/zone/zonelist',
                                   'data'   => array(
                                       'active'    => 1,
                                   ),
                                   'method' => 'get',
                                   'key'    => 'zonelist',
                               ),
                               array(
                                   'url'    => '/saler/salerlist',
                                   'data'   => array(
                                       'active'    => 1,
                                   ),
                                   'method' => 'get',
                                   'key'    => 'salerlist',
                               ),  
                           );
        $response        = $dataHttp->MutliCall( $data );   

        return $this->render('DWDCSAdminBundle:Dashboard:index.html.twig', array(
          	'errMsg'     => $errMsg,
            'zoneList'   => $response['zonelist']['data']['list'],
            'salerlist'  => $response['salerlist']['data']['list'],
        ));
    }
 
}