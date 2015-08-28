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
        $internalApiHost = 'http://iqginternalapi.wx.jaeapp.com';//'http://10.0.0.10:12306';
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

        $data         = $dataHttp->MutliCall($data);
        $paginator    = $this->get('knp_paginator');
        $coinrecords  = $paginator->paginate($data['coinrecords']['data']['list']);
        
        return $this->render('DWDCsAdminBundle:User:index.html.twig', array(
            'coinrecords'      => $coinrecords,
            'balancerecords'   => $data['coinrecords']['data']['list'],
            'orderlist'        => $data['orderlist']['data']['list'],
            'userinfo'         => $data['user']['data'],
        ));
    }

    /**
     * @Route("/user/orderlist", name="dwd_csadmin_product_show")
     * @Method("GET")
     */
    public function showAction()
    {
        $internalApiHost = 'http://iqginternalapi.wx.jaeapp.com';//'http://10.0.0.10:12306';
        $iDisplayStart   = $this->getRequest()->get('iDisplayStart');
        $iDisplayLength  = $this->getRequest()->get('iDisplayLength');
        $sEcho           = $this->getRequest()->get('sEcho'); 
        $userId          = 166036;
        $dataHttp        = $this->get('dwd.data.http');

        $data            = array(
            array(
                'url'    => $internalApiHost.'/user/orderlist',
                'data'   => array(
                    'userId'         => $userId,
                    'needPagination' => 1, 
                    'pageNum'        => $iDisplayStart / $iDisplayLength + 1,
                    'pageLimit'      => $iDisplayLength,
                ),
                'method' => 'get',
                'key'    => 'orderlist',
            )
        );

       // $data = $dataHttp->MutliCall2($data);
       // $res  = $dataHttp->getResponse(); 
        $data           = $dataHttp->MutliCall($data);
        $orderList      = array();
        foreach( $data['orderlist']['data']['list'] as $orderInfo )
        {

            $orderList[] = array(
                             $orderInfo['campaign_branch_id'],
                             $orderInfo['user_id'],
                             $orderInfo['price'],
                             $orderInfo['status'],
                             $orderInfo['type'],
                             $orderInfo['trade_number'],
                             $orderInfo['created_at'],
                             $orderInfo['updated_at'],
                           );  
        } 
        $res             = array
                           (
                                "sEcho"                => $sEcho, 
                                "aaData"               => $orderList,
                                "iTotalRecords"        => $data['orderlist']['data']['totalCnt'],
                                "iTotalDisplayRecords" => $data['orderlist']['data']['totalCnt'],
                           );
        exit(json_encode( $res ));
        //return $this->render('DWDCsAdminBundle:Dashboard:show.html.twig', array(
         //   'product'      => $product,
      //  ));
    }
}