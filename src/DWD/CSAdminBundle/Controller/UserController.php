<?php

namespace DWD\CsAdminBundle\Controller;
 
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class UserController
 * @package DWD\CsAdminBundle\Controller
 * @Route("/admin")
 */
class UserController extends Controller
{
    /** 
     *
     * @Route("/user",name="dwd_csadmin_user")
     */
    public function indexAction()
    { 
        $internalApiHost = 'http://10.0.0.10:12306';
        $userId          = 166036;
        $dataHttp        = $this->get('dwd.data.http');

        $data            = array(
            array(
                'url'    => $internalApiHost.'/user/userInfo',
                'data'   => array(
                    'userId'         => $userId,
                ),
                'method' => 'get',
                'key'    => 'user',
            ),
            array(
                'url'    => $internalApiHost.'/user/orderlist',
                'data'   => array(
                    'userId'         => $userId,
                    'needPagination' => 1,
                    'type'           => 1,
                    'pageLimit'      => 10,
                ),
                'method' => 'get',
                'key' => 'orderlist',
            ),
            array(
                'url'    => $internalApiHost.'/user/coinrecords',
                'data'   => array(
                    'userId'         => $userId,
                    'type'           => 1,
                    'needPagination' => 1,
                    'pageLimit'      => 10,
                ),
                'method' => 'get',
                'key'    => 'coinrecords',
            ), 
            array(
                'url'    => $internalApiHost.'/user/balancerecords',
                'data'   => array(
                    'userId'         => $userId,
                    'type'           => 1,
                    'needPagination' => 1,
                    'pageLimit'      => 10,
                ),
                'method' => 'get',
                'key'    => 'balancerecords',
            ), 
        );

        $data = $dataHttp->MutliCall($data);
         
        return $this->render('DWDCsAdminBundle:User:index.html.twig', array(
            'coinrecords'      => $data['coinrecords']['data']['list'],
            'balancerecords'   => $data['coinrecords']['data']['list'],
            'orderlist'        => $data['orderlist']['data']['list'],
            'userinfo'         => $data['user']['data'],
        ));
    }

    /**
     * @Route("/usershow", name="dwd_csadmin_product_show")
     * @Method("GET")
     */
    public function showAction()
    {
        $internalApiHost = 'http://10.0.0.10:12306';
        $userId          = 166036;
        $dataHttp        = $this->get('dwd.data.http');

        $data            = array(
            array(
                'url'    => $internalApiHost.'/user/userInfo',
                'data'   => array(
                    'userId'         => $userId,
                ),
                'method' => 'get',
                'key'    => 'user',
            ),
            array(
                'url'    => $internalApiHost.'/user/orderlist',
                'data'   => array(
                    'userId'         => $userId,
                    'needPagination' => 1,
                    'type'           => 1,
                    'pageLimit'      => 10,
                ),
                'method' => 'get',
                'key' => 'orderlist',
            ),
            array(
                'url'    => $internalApiHost.'/user/coinrecords',
                'data'   => array(
                    'userId'         => $userId,
                    'type'           => 1,
                    'needPagination' => 1,
                    'pageLimit'      => 10,
                ),
                'method' => 'get',
                'key'    => 'coinrecords',
            ), 
            array(
                'url'    => $internalApiHost.'/user/balancerecords',
                'data'   => array(
                    'userId'         => $userId,
                    'type'           => 1,
                    'needPagination' => 1,
                    'pageLimit'      => 10,
                ),
                'method' => 'get',
                'key'    => 'balancerecords',
            ), 
        );

        $data = $dataHttp->MutliCall2($data);
        $res  = $dataHttp->getResponse(); 
        var_dump( $res );
        exit(1);
        //return $this->render('DWDCsAdminBundle:Dashboard:show.html.twig', array(
         //   'product'      => $product,
      //  ));
    }
}