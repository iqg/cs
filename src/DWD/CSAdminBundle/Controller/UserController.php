<?php

namespace DWD\CsAdminBundle\Controller;
 
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class UserController
 * @package DWD\CsAdminBundle\Controller
 * @Route("/")
 */
class UserController extends Controller
{
    /** 
     *
     * @Route("/user",name="dwd_csadmin_user")
     */
    public function indexAction()
    { 
        $userId          = $this->getRequest()->get('userId');
        $type            = $this->getRequest()->get('type');
        $dataHttp        = $this->get('dwd.data.http');

        if( false == is_numeric( $userId ) ){
            return $this->render('DWDCSAdminBundle:Dashboard:index.html.twig', array(
             'errMsg'    => '用户id不合法'
          ));
        }

        $data            = array(
            array(
                'url'    => '/user/userInfo',
                'data'   => array(
                    'userId'         => $userId,
                ),
                'method' => 'get',
                'key'    => 'user',
            ),
            array(
                'url'    => '/user/balancerecords',
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

        $orderListTypes =  $this->get('dwd.util')->getOrderTableInfo( 0 );

        $data           = $dataHttp->MutliCall($data); 

        if( empty( $data['user']['data'] ) ){
            return $this->render('DWDCSAdminBundle:Dashboard:index.html.twig', array(
             'errMsg'    => '用户不存在'
          ));
        }
      
        return $this->render('DWDCSAdminBundle:User:index.html.twig', array(
            'balancerecords'   => $data['balancerecords']['data']['list'],
            'userinfo'         => $data['user']['data'],
            'orderlistTypes'   => $orderListTypes,
            'userId'           => $userId,
            'type'             => $type,
        ));
    }

    private function _getOrderTypeId( $typeCode )
    {
        $orderTypeId             = 2;
        switch ( $typeCode ) {
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
        return $orderTypeId;
    }

    private function _getOperation( $operation, $orderId )
    {
        $opStr               = '';
        foreach ($operation as $operator) {
          switch ( $operator ) {
             /*case '退款':
                    $opStr  .= "&nbsp;&nbsp;&nbsp;<a href='#' class='order-refund-btn' data-rel='$orderId'>[退款]</a>";
                    break;*/
             case '纠错':
                    $opStr  .= "&nbsp;&nbsp;&nbsp;<a href='#' class='order-correct-btn' data-rel='$orderId'>[纠错]</a>";
                    break;
             case '日志': 
                    $opStr  .= "&nbsp;&nbsp;&nbsp;<a href='#' class='order-log-btn' data-rel='$orderId'>[日志]</a>";
                    break;
             case '详情': 
                   $opStr   .= "&nbsp;&nbsp;&nbsp;<a href='#' class='order-detail-btn' data-rel='$orderId'>[详情]</a>";
                   break;
          }
        }

        return $opStr;
    }

    //获取订单列表
    private function _getOrderList( $userId, $orderType, $limitStart, $pageLimit )
    {
        $dataHttp            = $this->get('dwd.data.http');
        $pageNum             = $limitStart / $pageLimit + 1;
        $orderTypeId         = $this->_getOrderTypeId( $orderType );

        $data                = array(
                                  array(
                                      'url'    => '/user/orderlist',
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
    
        $data              = $dataHttp->MutliCall($data);
        $orderList         = array(
                               'list'         => array(),
                               'total'        => $data['orderlist']['data']['totalCnt'], 
                             );

        $campaignBranchIds = array();
        $ordersInfo        = array();
         
        foreach( $data['orderlist']['data']['list'] as $orderInfo )
        {
            $ordersInfo[]        = $orderInfo;
            $campaignBranchIds[] = $orderInfo['campaign_branch_id'];
        }

        $data                    = array(
                                      array(
                                          'url'    => '/campaignbranch/campaignbranchlist',
                                          'data'   => array(
                                              'campaignBranchIds' => implode(',', $campaignBranchIds),
                                          ),
                                          'method' => 'get',
                                          'key'    => 'campaignbranchlist',
                                      ),
                                      array(
                                          'url'    => '/campaignbranch/branchlist',
                                          'data'   => array(
                                              'campaignBranchIds' => implode(',', $campaignBranchIds),
                                          ),
                                          'method' => 'get',
                                          'key'    => 'branchs',
                                      ),
                                  );
        $data                    =  $dataHttp->MutliCall($data);
        $tableInfo               =  $this->get('dwd.util')->getOrderTableInfo( $orderTypeId );

        foreach( $ordersInfo as $key => $orderInfo ){
           if( false == isset( $data['branchs']['data']['list'][$orderInfo['campaign_branch_id']] ) || 
               false == isset( $data['campaignbranchlist']['data']['list'][$orderInfo['campaign_branch_id']] )  ){
               continue ;
           }

           $branchInfo           = $data['branchs']['data']['list'][$orderInfo['campaign_branch_id']];
           $campaignBranchInfo   = $data['campaignbranchlist']['data']['list'][$orderInfo['campaign_branch_id']];
           $tdValues             = array();
           foreach ($tableInfo['field'] as $field) 
           {  
              $tdValue           = '';
              switch ( $field ) {
                case 'itemName':
                  $tdValue       = $campaignBranchInfo['campaign_id'];
                  break;
                case 'branchName':
                  $tdValue       = $branchInfo['name'];
                  break;
                case 'redeemNumber':
                  $tdValue       = $orderInfo['redeem_number'];
                  break;
                case 'refundTime':
                  $tdValue       = $orderInfo['refunded_at'];
                  break;
                case 'expiredTime':
                  $tdValue       = $orderInfo['expire_time'];
                  break;
                case 'redeemTime':
                  $tdValue       = $orderInfo['redeem_time'];
                  break;
                case 'status':
                  $tdValue       = $this->get('dwd.util')->getOrderStatusLabel($orderInfo['status']);
                  break;
                default:
                  break;
              }
              if( empty( $tdValue ) ){
                $tdValue         = '';
              }
              $tdValues[]        = $tdValue;
           }
           $tdValues[]           = $this->_getOperation( $tableInfo['operation'], $orderInfo['id'] );
           $orderList['list'][]  = $tdValues;
        /*   $orderList['list'][]  = array(
                                         $orderInfo['id'],
                                         $branchInfo['name'],
                                         $campaignBranchInfo['campaign_id'],
                                         $orderInfo['price'],
                                         $this->get('dwd.util')->getOrderStatusLabel($orderInfo['status']),
                                         $this->get('dwd.util')->getOrderTypeLabel($orderInfo['type']),
                                         $orderInfo['trade_number'],
                                         $orderInfo['created_at'],
                                         $orderInfo['updated_at'],
                                    );  */
        }

        return $orderList;
    }

    //获取订单信息
    private function _getOrderInfo( $redeemNumber, $userId )
    { 
        $dataHttp        = $this->get('dwd.data.http');
        $data            =  array(
            array(
                'url'    => '/order/orderinfo',
                'data'   => array(
                    'redeemNumber'      => $redeemNumber, 
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
        if( !empty( $orderInfo ) && $userId == $orderInfo['user_id'] ){ 
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
            $orderList   = self::_getOrderInfo( $sSearch, $userId );
        }
 
        $res             = array
                           (
                                "sEcho"                => $sEcho, 
                                "aaData"               => $orderList['list'],
                                "iTotalRecords"        => $orderList['total'],
                                "iTotalDisplayRecords" => $orderList['total'],
                           );
    
        $response        = new Response();
        $response->setContent( json_encode( $res ) );
        return $response;
        //return $this->render('DWDCSAdminBundle:Dashboard:show.html.twig', array(
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
        $iDisplayStart   = $this->getRequest()->get('iDisplayStart');
        $iDisplayLength  = $this->getRequest()->get('iDisplayLength'); 
        $sEcho           = $this->getRequest()->get('sEcho');
        $sSearch         = $this->getRequest()->get('sSearch', null);
        $userId          = $this->getRequest()->get('userId');
        $orderType       = $this->getRequest()->get('type', 'redeem');
        $orderList       = array();
        $data            = array( 
                                array(
                                    'url'    => '/user/coinrecords',
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
                                          $coinrecord['amount'],
                                          $this->get('dwd.util')->getCoinTypeLabel( $coinrecord['type'] ),
                                          strval( $coinrecord['remark'] ), 
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
        $response        = new Response();
        $response->setContent( json_encode( $res ) );
        return $response;
    }

    /**
     * @Route("/user/balancerecords", name="dwd_csadmin_user_balancerecords_show")
     * @Method("GET")
     */
    public function BalanceRecordsDataAction()
    {
        $dataHttp        = $this->get('dwd.data.http');
        $iDisplayStart   = $this->getRequest()->get('iDisplayStart');
        $iDisplayLength  = $this->getRequest()->get('iDisplayLength'); 
        $sEcho           = $this->getRequest()->get('sEcho');
        $sSearch         = $this->getRequest()->get('sSearch', null);
        $userId          = $this->getRequest()->get('userId');
        $orderType       = $this->getRequest()->get('type', 'redeem');
        $orderList       = array();
        $data            = array( 
                                array(
                                    'url'    => '/user/balancerecords',
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
                                         $this->get('dwd.util')->getBalanceTypeLabel( $balancerecord['type'] ),
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
        $response        = new Response();
        $response->setContent( json_encode( $res ) );
        return $response;
    }

    /**
     * @Route("/user/lockedrecords", name="dwd_csadmin_user_lockedrecords_show")
     * @Method("GET")
     */
    public function LockedRecordsDataAction()
    {
        $dataHttp        = $this->get('dwd.data.http');
        $iDisplayStart   = $this->getRequest()->get('iDisplayStart');
        $iDisplayLength  = $this->getRequest()->get('iDisplayLength'); 
        $sEcho           = $this->getRequest()->get('sEcho');
        $sSearch         = $this->getRequest()->get('sSearch', null);
        $userId          = $this->getRequest()->get('userId');
        $orderType       = $this->getRequest()->get('type', 'redeem');
        $orderList       = array();
        $data            = array( 
                                array(
                                    'url'    => '/user/lockedrecords',
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
                                         $lockedrecord['create_at'], 
                                         $lockedrecord['lock_date'],
                                         $this->get('dwd.util')->getLockReasonTypeLabel($lockedrecord['reason_type']),
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
        $response        = new Response();
        $response->setContent( json_encode( $res ) );
        return $response;
    }

    /**
     * @Route("/user/recommendrecords", name="dwd_csadmin_user_recommendrecords_show")
     * @Method("GET")
     */
    public function RecommendRecordsDataAction()
    {
        $dataHttp        = $this->get('dwd.data.http');
        $iDisplayStart   = $this->getRequest()->get('iDisplayStart');
        $iDisplayLength  = $this->getRequest()->get('iDisplayLength'); 
        $sEcho           = $this->getRequest()->get('sEcho');
        $sSearch         = $this->getRequest()->get('sSearch', null);
        $userId          = $this->getRequest()->get('userId');
        $orderType       = $this->getRequest()->get('type', 'redeem');
        $orderList       = array();
        $data            = array( 
                                array(
                                    'url'    => '/user/recommendrecords',
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

        $data                           = $dataHttp->MutliCall($data);
        $recommendrecords               = array(
                                              'list'         => array(),
                                              'total'        => $data['recommendrecords']['data']['totalCnt'], 
                                          );
        $recommendUserIds               = array();
        foreach( $data['recommendrecords']['data']['list'] as $recommendrecord )
        { 
            $recommendrecords['list'][] = array( 
                                               $recommendrecord['recommend_user_id'],
                                               $recommendrecord['created_at'],
                                          );
            $recommendUserIds[]         = $recommendrecord['recommend_user_id'];
        }
        $iTotalRecords                  = $data['recommendrecords']['data']['totalCnt'];
        $data                           = array( 
                                                array(
                                                    'url'    => '/user/usersinfo',
                                                    'data'   => array(
                                                        'userIds'         => implode(',', $recommendUserIds), 
                                                    ), 
                                                    'method' => 'get',
                                                    'key'    => 'usersInfo',
                                                ),  
                                          ); 
        $data                           = $dataHttp->MutliCall($data);


        foreach ($recommendrecords['list'] as $key => $recommendrecord) {
           $recommendUserId                   = $recommendrecord[0];
           $recommendrecords['list'][$key][0] = $data['usersInfo']['data']['list'][$recommendUserId]['username'];
        }

        $res                            = array
                                          (
                                              "sEcho"                => $sEcho, 
                                              "aaData"               => $recommendrecords['list'],
                                              "iTotalRecords"        => $iTotalRecords,
                                              "iTotalDisplayRecords" => $iTotalRecords,
                                          );
        $response        = new Response();
        $response->setContent( json_encode( $res ) );
        return $response;
    }

    /**
     * @Route("/user/smsrecords", name="dwd_csadmin_user_smsrecords_show")
     * @Method("GET")
     */
    public function SMSRecordsDataAction()
    {
        $dataHttp        = $this->get('dwd.data.http');
        $iDisplayStart   = $this->getRequest()->get('iDisplayStart');
        $iDisplayLength  = $this->getRequest()->get('iDisplayLength'); 
        $sEcho           = $this->getRequest()->get('sEcho');
        $sSearch         = $this->getRequest()->get('sSearch', null);
        $userId          = $this->getRequest()->get('userId');
        $orderType       = $this->getRequest()->get('type', 'redeem');
        $orderList       = array();
        $data            = array( 
                                array(
                                    'url'    => '/user/smsrecords',
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
                                         $this->get('dwd.util')->getSMSTypeLabel( $smsrecord['type'] ),
                                         $smsrecord['content'], 
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
        $response        = new Response();
        $response->setContent( json_encode( $res ) );
        return $response; 
    }

    /**
     * @Route("/user/complaintrecords", name="dwd_csadmin_user_complaintrecords_show")
     * @Method("GET")
     */
    public function ComplaintRecordsDataAction()
    {
        $dataHttp        = $this->get('dwd.data.http');
        $iDisplayStart   = $this->getRequest()->get('iDisplayStart');
        $iDisplayLength  = $this->getRequest()->get('iDisplayLength'); 
        $sEcho           = $this->getRequest()->get('sEcho');
        $sSearch         = $this->getRequest()->get('sSearch', null);
        $userId          = $this->getRequest()->get('userId');
        $orderType       = $this->getRequest()->get('type', 'redeem');
        $orderList       = array();
        $data            = array( 
                                array(
                                    'url'    => '/user/complaints',
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
                                              $complaintrecord['category_id'],
                                              $complaintrecord['user_id'],
                                              $complaintrecord['item_id'],
                                              $complaintrecord['order_id'], 
                                              $complaintrecord['created_at'], 
                                              '[操作]',
                                          );
        }
 
        $res             = array
                           (
                                "sEcho"                => $sEcho, 
                                "aaData"               => $complaintrecords['list'],
                                "iTotalRecords"        => $data['complaintrecords']['data']['totalCnt'],
                                "iTotalDisplayRecords" => $data['complaintrecords']['data']['totalCnt'],
                           );
        $response        = new Response();
        $response->setContent( json_encode( $res ) );
        return $response; 
    }

    /**
     * @Route("/user/modify", name="dwd_csadmin_user_modify_show")
     *
     */
    public function modifyUser()
    {
        $dataHttp       = $this->get('dwd.data.http');
        $userName       = $this->getRequest()->get('userName');
        $userMobile     = $this->getRequest()->get('userMobile'); 
        $userPassword   = $this->getRequest()->get('userPassword'); 
        $userId         = $this->getRequest()->get('userId');

        $params         = array(
                              'username'         => $userName,
                              'mobile'           => $userMobile,
                              'userId'           => $userId,
                          );

        if( false == empty( $userPassword ) ){
            $params['password'] = $userPassword;
        }

        $data              = array( 
                                    array(
                                        'url'    => '/user/update',
                                        'data'   =>  $params,
                                        'method' => 'post',
                                        'key'    => 'update',
                                    ),  
                              ); 

        $data              = $dataHttp->MutliCall($data);
     
        $res               = array();
        $res['result']     = false;
        if( $data['update']['errno'] == 0 ){
            $res['result'] = true;
        } 
 
        $response          = new Response();
        $response->setContent( json_encode( $res ) );
        return $response; 
    }

    /**
     * @Route("/user/lock", name="dwd_csadmin_user_lock_show")
     *
     */
    public function lockUser()
    { 
        $dataHttp             = $this->get('dwd.data.http'); 
        $userId               = $this->getRequest()->get('userId');

        if( false == empty( $userPassword ) ){
          $params['password'] = $userPassword;
        }

        $data                 = array( 
                                    array(
                                        'url'    => '/user/locked',
                                        'data'   =>  array( 
                                                        'userId'     => $userId,
                                                        'opUserId'   => 1,
                                                        'reasonType' => 2,
                                                        'unlcokDate' => date( 'Y-m-d H:i:s',  3600 * 24 * 30 + time() ),
                                                        'type'       => 1,
                                                    ),
                                        'method' => 'post',
                                        'key'    => 'locked',
                                    ),  
                                );

        $data              = $dataHttp->MutliCall($data);
    
        $res               = array();
        $res['result']     = false;
        if( $data['locked']['errno'] == 0 ){
            $res['result'] = true;
        } 
 
        $response          = new Response();
        $response->setContent( json_encode( $res ) );
        return $response; 
    }

    /**
     * @Route("/user/unbinddevice", name="dwd_csadmin_user_unbinddevice_show")
     *
     */
    public function unbindDevice()
    { 
        $dataHttp             = $this->get('dwd.data.http'); 
        $userId               = $this->getRequest()->get('userId');

        if( false == empty( $userPassword ) ){
          $params['password'] = $userPassword;
        }

        $data                 = array( 
                                    array(
                                        'url'    => '/user/unbinddevice',
                                        'data'   =>  array( 
                                                        'userId'     => $userId,
                                                        'opUserId'   => 1,
                                                        'reasonType' => 2, 
                                                     ),
                                        'method' =>  'post',
                                        'key'    =>  'unbinddevice',
                                    ),  
                                );

        $data              = $dataHttp->MutliCall($data);
    
        $res               = array();
        $res['result']     = false;
        if( $data['unbinddevice']['errno'] == 0 ){
            $res['result'] = true;
        } 
 
        $response          = new Response();
        $response->setContent( json_encode( $res ) );
        return $response; 
    }
}