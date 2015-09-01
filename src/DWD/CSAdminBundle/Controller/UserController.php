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
        $userId          = $this->getRequest()->get('userId');
        $dataHttp        = $this->get('dwd.data.http');

        if( false == is_numeric( $userId ) ){
            return $this->render('DWDCsAdminBundle:Dashboard:index.html.twig', array(
             'errMsg'    => '用户id不合法'
          ));
        }

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

        $orderListTypes = array(
                            'wait-redeem' => '未领用',
                            'refund'      => '退款',
                            'expired'     => '过期',
                            'finish'      => '完成',
                            'processing'  => '需处理',
                          );

        $data           = $dataHttp->MutliCall($data); 

        if( empty( $data['user']['data'] ) ){
            return $this->render('DWDCsAdminBundle:Dashboard:index.html.twig', array(
             'errMsg'    => '用户不存在'
          ));
        }
        
        return $this->render('DWDCsAdminBundle:User:index.html.twig', array(
            'balancerecords'   => $data['balancerecords']['data']['list'],
            'userinfo'         => $data['user']['data'],
            'orderlistTypes'   => $orderListTypes,
            'userId'           => $userId,
        ));
    }

    //获取订单列表
    private function _getOrderList( $userId, $orderType, $limitStart, $pageLimit )
    {
        $dataHttp            = $this->get('dwd.data.http');
        $orderTypeId         = 2;
        $pageNum             = $limitStart / $pageLimit + 1;

        switch ( $orderType ) {
            case 'wait-redeem':
                $orderTypeId = 2;
                break;
            case 'refund':
                $orderTypeId = 3;
                break;
            case 'expired': 
                $orderTypeId = 4;
                break;
            case 'finish': 
                $orderTypeId = 5;
                break;
            case 'processing': 
                $orderTypeId = 6;
                break;
            default: 
                break;
        }

        $internalApiHost = 'http://127.0.0.1';//'http://10.0.0.10:12306';
        $data            = array(
            array(
                'url'    => $internalApiHost.'/user/orderlist',
                'data'   => array(
                    'userId'         => $userId,
                    'needPagination' => 1, 
                    'type'           => $orderTypeId,
                    'pageNum'        => $pageNum,
                    'pageLimit'      => $pageLimit,
                ),
                'method' => 'get',
                'key'    => 'orderlist',
            )
        );
    
        $data             = $dataHttp->MutliCall($data);
        $orderList        = array(
                                'list'         => array(),
                                'total'        => $data['orderlist']['data']['totalCnt'], 
                            );
         
        foreach( $data['orderlist']['data']['list'] as $orderInfo )
        {

            $orderList['list'][] = array(
                                       $orderInfo['id'],
                                       $orderInfo['campaign_branch_id'],
                                       $orderInfo['user_id'],
                                       $orderInfo['price'],
                                       $this->get('dwd.util')->getOrderStatusLabel($orderInfo['status']),
                                       $this->get('dwd.util')->getOrderTypeLabel($orderInfo['type']),
                                       $orderInfo['trade_number'],
                                       $orderInfo['created_at'],
                                       $orderInfo['updated_at'],
                                    );  
        }

        return $orderList;
    }

    //获取订单信息
    private function _getOrderInfo( $orderId )
    { 
        $dataHttp        = $this->get('dwd.data.http');
        $internalApiHost =  'http://127.0.0.1';//'http://10.0.0.10:12306';
        $data            =  array(
            array(
                'url'    => $internalApiHost.'/order/orderinfo',
                'data'   => array(
                    'orderId'      => $orderId, 
                ),
                'method' => 'get',
                'key'    => 'orderinfo',
            )
        );
        $data            =  $dataHttp->MutliCall($data);
        $orderInfo       =  $data['orderinfo']['data'];
        $orderList       =  array(
                                'list'  => array(),
                                'total' => 0,
                            );
        if( !empty( $orderInfo )  ){ 
            $orderList       =  array(
                                'list' => 
                                    array(
                                        array(
                                         $orderInfo['id'],
                                         $orderInfo['campaign_branch_id'],
                                         $orderInfo['user_id'],
                                         $orderInfo['price'],
                                         $this->get('dwd.util')->getOrderStatusLabel($orderInfo['status']),
                                         $this->get('dwd.util')->getOrderTypeLabel($orderInfo['type']),
                                         $orderInfo['trade_number'],
                                         $orderInfo['created_at'],
                                         $orderInfo['updated_at'],
                                       ),    
                                    ), 
                                "total" => 1,
                            );
        } 

        return $orderList;
    }

    /**
     * @Route("/user/orderlist", name="dwd_csadmin_user_orderlist_show")
     * @Method("GET")
     */
    public function OrderListDataAction()
    {
        $iDisplayStart   = $this->getRequest()->get('iDisplayStart');
        $iDisplayLength  = $this->getRequest()->get('iDisplayLength'); 
        $sEcho           = $this->getRequest()->get('sEcho');
        $sSearch         = $this->getRequest()->get('sSearch', null);
        $userId          = $this->getRequest()->get('userId');
        $orderType       = $this->getRequest()->get('type', 'redeem');
        $orderList       = array();

        if( empty( $sSearch ) ){
            $orderList   = self::_getOrderList( $userId, $orderType, $iDisplayStart, $iDisplayLength);
        } else {
            $orderList   = self::_getOrderInfo( $sSearch );
        }
        $res             = array
                           (
                                "sEcho"                => $sEcho, 
                                "aaData"               => $orderList['list'],
                                "iTotalRecords"        => $orderList['total'],
                                "iTotalDisplayRecords" => $orderList['total'],
                           );
        exit(json_encode( $res ));
        //return $this->render('DWDCsAdminBundle:Dashboard:show.html.twig', array(
         //   'product'      => $product,
      //  ));
    }

    /**
     * @Route("/user/coinrecords", name="dwd_csadmin_user_coinrecords_show")
     * @Method("GET")
     */
    public function CoinRecordsDataAction()
    {
        $dataHttp        = $this->get('dwd.data.http');
        $internalApiHost = 'http://127.0.0.1';
        $iDisplayStart   = $this->getRequest()->get('iDisplayStart');
        $iDisplayLength  = $this->getRequest()->get('iDisplayLength'); 
        $sEcho           = $this->getRequest()->get('sEcho');
        $sSearch         = $this->getRequest()->get('sSearch', null);
        $userId          = $this->getRequest()->get('userId');
        $orderType       = $this->getRequest()->get('type', 'redeem');
        $orderList       = array();
        $data            = array( 
                                array(
                                    'url'    => $internalApiHost.'/user/coinrecords',
                                    'data'   => array(
                                        'userId'         => $userId,
                                        'needPagination' => 1,
                                        'pageLimit'      => $iDisplayLength,
                                        'pageNum'        => $iDisplayStart / $iDisplayLength + 1,
                                    ), 
                                    'method' => 'get',
                                    'key'    => 'coinrecords',
                                ),  
                            ); 

        $data           = $dataHttp->MutliCall($data);
        $coinrecords    = array(
                             'list'         => array(),
                             'total'        => $data['coinrecords']['data']['totalCnt'], 
                          );
        foreach( $data['coinrecords']['data']['list'] as $coinrecord )
        {

            $coinrecords['list'][] = array(
                                         $coinrecord['user_id'],
                                         $coinrecord['type'],
                                         $coinrecord['amount'],
                                         $coinrecord['current_balance'],
                                         $coinrecord['created_at'],  
                                    );  
        }

        $res             = array
                           (
                                "sEcho"                => $sEcho, 
                                "aaData"               => $coinrecords['list'],
                                "iTotalRecords"        => $data['coinrecords']['data']['totalCnt'],
                                "iTotalDisplayRecords" => $data['coinrecords']['data']['totalCnt'],
                           );
        exit(json_encode( $res )); 
    }

    /**
     * @Route("/user/balancerecords", name="dwd_csadmin_user_balancerecords_show")
     * @Method("GET")
     */
    public function BalanceRecordsDataAction()
    {
        $dataHttp        = $this->get('dwd.data.http');
        $internalApiHost = 'http://127.0.0.1';
        $iDisplayStart   = $this->getRequest()->get('iDisplayStart');
        $iDisplayLength  = $this->getRequest()->get('iDisplayLength'); 
        $sEcho           = $this->getRequest()->get('sEcho');
        $sSearch         = $this->getRequest()->get('sSearch', null);
        $userId          = $this->getRequest()->get('userId');
        $orderType       = $this->getRequest()->get('type', 'redeem');
        $orderList       = array();
        $data            = array( 
                                array(
                                    'url'    => $internalApiHost.'/user/balancerecords',
                                    'data'   => array(
                                        'userId'         => $userId,
                                        'needPagination' => 1,
                                        'pageLimit'      => $iDisplayLength,
                                        'pageNum'        => $iDisplayStart / $iDisplayLength + 1,
                                    ), 
                                    'method' => 'get',
                                    'key'    => 'balancerecords',
                                ),  
                            ); 

        $data           = $dataHttp->MutliCall($data);
        $balancerecords = array(
                             'list'         => array(),
                             'total'        => $data['balancerecords']['data']['totalCnt'], 
                          );
        foreach( $data['balancerecords']['data']['list'] as $balancerecord )
        {

            $balancerecords['list'][] = array(
                                         $balancerecord['user_id'],
                                         $balancerecord['type'],
                                         $balancerecord['amount'],
                                         $balancerecord['current_balance'],
                                         $balancerecord['created_at'],  
                                    );  
        }

        $res             = array
                           (
                                "sEcho"                => $sEcho, 
                                "aaData"               => $balancerecords['list'],
                                "iTotalRecords"        => $data['balancerecords']['data']['totalCnt'],
                                "iTotalDisplayRecords" => $data['balancerecords']['data']['totalCnt'],
                           );
        exit(json_encode( $res )); 
    }

    /**
     * @Route("/user/lockedrecords", name="dwd_csadmin_user_lockedrecords_show")
     * @Method("GET")
     */
    public function LockedRecordsDataAction()
    {
        $dataHttp        = $this->get('dwd.data.http');
        $internalApiHost = 'http://127.0.0.1';
        $iDisplayStart   = $this->getRequest()->get('iDisplayStart');
        $iDisplayLength  = $this->getRequest()->get('iDisplayLength'); 
        $sEcho           = $this->getRequest()->get('sEcho');
        $sSearch         = $this->getRequest()->get('sSearch', null);
        $userId          = $this->getRequest()->get('userId');
        $orderType       = $this->getRequest()->get('type', 'redeem');
        $orderList       = array();
        $data            = array( 
                                array(
                                    'url'    => $internalApiHost.'/user/lockedrecords',
                                    'data'   => array(
                                        'userId'         => $userId,
                                        'needPagination' => 1,
                                        'pageLimit'      => $iDisplayLength,
                                        'pageNum'        => $iDisplayStart / $iDisplayLength + 1,
                                    ), 
                                    'method' => 'get',
                                    'key'    => 'lockedrecords',
                                ),  
                            ); 

        $data           = $dataHttp->MutliCall($data);
        $lockedrecords  = array(
                             'list'         => array(),
                             'total'        => $data['lockedrecords']['data']['totalCnt'], 
                          );
        foreach( $data['lockedrecords']['data']['list'] as $lockedrecord )
        { 
            $lockedrecords['list'][] = array(
                                         $lockedrecord['user_id'],
                                         $lockedrecord['operator_user_id'],
                                         $lockedrecord['type'],
                                         $lockedrecord['lock_date'],
                                         $lockedrecord['note'],  
                                    );  
        }

        $res             = array
                           (
                                "sEcho"                => $sEcho, 
                                "aaData"               => $lockedrecords['list'],
                                "iTotalRecords"        => $data['lockedrecords']['data']['totalCnt'],
                                "iTotalDisplayRecords" => $data['lockedrecords']['data']['totalCnt'],
                           );
        exit(json_encode( $res )); 
    }

    /**
     * @Route("/user/recommendrecords", name="dwd_csadmin_user_recommendrecords_show")
     * @Method("GET")
     */
    public function RecommendRecordsDataAction()
    {
        $dataHttp        = $this->get('dwd.data.http');
        $internalApiHost = 'http://127.0.0.1';
        $iDisplayStart   = $this->getRequest()->get('iDisplayStart');
        $iDisplayLength  = $this->getRequest()->get('iDisplayLength'); 
        $sEcho           = $this->getRequest()->get('sEcho');
        $sSearch         = $this->getRequest()->get('sSearch', null);
        $userId          = $this->getRequest()->get('userId');
        $orderType       = $this->getRequest()->get('type', 'redeem');
        $orderList       = array();
        $data            = array( 
                                array(
                                    'url'    => $internalApiHost.'/user/recommendrecords',
                                    'data'   => array(
                                        'userId'         => $userId,
                                        'needPagination' => 1,
                                        'pageLimit'      => $iDisplayLength,
                                        'pageNum'        => $iDisplayStart / $iDisplayLength + 1,
                                    ), 
                                    'method' => 'get',
                                    'key'    => 'recommendrecords',
                                ),  
                            ); 

        $data              = $dataHttp->MutliCall($data);
        $recommendrecords  = array(
                               'list'         => array(),
                               'total'        => $data['recommendrecords']['data']['totalCnt'], 
                             );
        foreach( $data['recommendrecords']['data']['list'] as $recommendrecord )
        { 
            $recommendrecords['list'][] = array(
                                         $recommendrecord['user_id'],
                                         $recommendrecord['recommend_user_id'],
                                         $recommendrecord['created_at'],
                                    );  
        }

        $res             = array
                           (
                                "sEcho"                => $sEcho, 
                                "aaData"               => $recommendrecords['list'],
                                "iTotalRecords"        => $data['recommendrecords']['data']['totalCnt'],
                                "iTotalDisplayRecords" => $data['recommendrecords']['data']['totalCnt'],
                           );
        exit(json_encode( $res )); 
    }

    /**
     * @Route("/user/smsrecords", name="dwd_csadmin_user_smsrecords_show")
     * @Method("GET")
     */
    public function SMSRecordsDataAction()
    {
        $dataHttp        = $this->get('dwd.data.http');
        $internalApiHost = 'http://127.0.0.1';
        $iDisplayStart   = $this->getRequest()->get('iDisplayStart');
        $iDisplayLength  = $this->getRequest()->get('iDisplayLength'); 
        $sEcho           = $this->getRequest()->get('sEcho');
        $sSearch         = $this->getRequest()->get('sSearch', null);
        $userId          = $this->getRequest()->get('userId');
        $orderType       = $this->getRequest()->get('type', 'redeem');
        $orderList       = array();
        $data            = array( 
                                array(
                                    'url'    => $internalApiHost.'/user/smsrecords',
                                    'data'   => array(
                                        'userId'         => $userId,
                                        'needPagination' => 1,
                                        'pageLimit'      => $iDisplayLength,
                                        'pageNum'        => $iDisplayStart / $iDisplayLength + 1,
                                    ), 
                                    'method' => 'get',
                                    'key'    => 'smsrecords',
                                ),  
                            ); 

        $data              = $dataHttp->MutliCall($data);
        $smsrecords        = array(
                               'list'         => array(),
                               'total'        => $data['smsrecords']['data']['totalCnt'], 
                             );
        foreach( $data['smsrecords']['data']['list'] as $smsrecord )
        { 
            $smsrecords['list'][] = array(
                                         $smsrecord['content'],
                                         $smsrecord['type'],
                                         $smsrecord['mobile'], 
                                         $smsrecord['create_at'],
                                    );  
        }

        $res             = array
                           (
                                "sEcho"                => $sEcho, 
                                "aaData"               => $smsrecords['list'],
                                "iTotalRecords"        => $data['smsrecords']['data']['totalCnt'],
                                "iTotalDisplayRecords" => $data['smsrecords']['data']['totalCnt'],
                           );
        exit(json_encode( $res )); 
    }

    /**
     * @Route("/user/complaintrecords", name="dwd_csadmin_user_complaintrecords_show")
     * @Method("GET")
     */
    public function ComplaintRecordsDataAction()
    {
        $dataHttp        = $this->get('dwd.data.http');
        $internalApiHost = 'http://127.0.0.1';
        $iDisplayStart   = $this->getRequest()->get('iDisplayStart');
        $iDisplayLength  = $this->getRequest()->get('iDisplayLength'); 
        $sEcho           = $this->getRequest()->get('sEcho');
        $sSearch         = $this->getRequest()->get('sSearch', null);
        $userId          = $this->getRequest()->get('userId');
        $orderType       = $this->getRequest()->get('type', 'redeem');
        $orderList       = array();
        $data            = array( 
                                array(
                                    'url'    => $internalApiHost.'/user/complaints',
                                    'data'   => array(
                                        'userId'         => $userId,
                                        'needPagination' => 1,
                                        'pageLimit'      => $iDisplayLength,
                                        'pageNum'        => $iDisplayStart / $iDisplayLength + 1,
                                    ), 
                                    'method' => 'get',
                                    'key'    => 'complaintrecords',
                                ),  
                            ); 

        $data              = $dataHttp->MutliCall($data);

        $complaintrecords  = array(
                               'list'         => array(),
                               'total'        => $data['complaintrecords']['data']['totalCnt'], 
                             );
        foreach( $data['complaintrecords']['data']['list'] as $complaintrecord )
        { 
            $complaintrecords['list'][] = array(
                                         $complaintrecord['item_id'],
                                         $complaintrecord['order_id'],
                                         $complaintrecord['user_id'], 
                                         $complaintrecord['saler_id'],
                                         $complaintrecord['type_id'], 
                                         $complaintrecord['status'],
                                         $complaintrecord['description'], 
                                         $complaintrecord['from_id'],
                                         $complaintrecord['category_id'],
                                         $complaintrecord['mobile'],
                                         $complaintrecord['created_at'],
                                         $complaintrecord['updated_at'],
                                         $complaintrecord['resolved_at'],
                                    );
        }
 
        $res             = array
                           (
                                "sEcho"                => $sEcho, 
                                "aaData"               => $complaintrecords['list'],
                                "iTotalRecords"        => $data['complaintrecords']['data']['totalCnt'],
                                "iTotalDisplayRecords" => $data['complaintrecords']['data']['totalCnt'],
                           );
        exit(json_encode( $res )); 
    }
}