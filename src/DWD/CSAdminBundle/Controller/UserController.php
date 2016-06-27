<?php

namespace DWD\CSAdminBundle\Controller;
 
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class UserController
 * @package DWD\CSAdminBundle\Controller
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
        $source          = $this->getRequest()->get('source');
        $searchType      = $this->getRequest()->get('searchType');
        $searchKey       = $this->getRequest()->get('inputValue');
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
            array(
                'url'    => '/user/userdevices',
                'data'   => array(
                    'userId'         => $userId
                ),
                'method' => 'get',
                'key'    => 'userdevices'
            )
        );

        if( $searchType == 'redeemNumber' ){
            $data[]     =  array(
                                'url'    => '/order/orderinfo',
                                'data'   => array(
                                    'redeemNumber'=> $searchKey,
                                    'userId'      => $userId,
                                ),
                                'method' => 'get',
                                'key'    => 'orderinfo',
                           );
        }

        $orderListTypes =  $this->get('dwd.util')->getOrderTableInfo( 0 );

        $data           =  $dataHttp->MutliCall($data); 

        if( empty( $data['user']['data'] ) ){
            return $this->render('DWDCSAdminBundle:Dashboard:index.html.twig', array(
             'errMsg'    => '用户不存在'
          ));
        }
        $userDevicesCount  = count( $data['userdevices']['data']['list'] );

        $needDealOrder     = '';
        if( $searchType == 'redeemNumber' ){

            $orderInfos      = $data['orderinfo']['data'];
            $needDealOrder  = '<table class="table table-striped table-bordered"><tr><th>商品</th><th>门店</th><th>兑换码</th><th>状态</th><th>操作</th></tr>';
            if(!empty($orderInfos)){
              foreach($orderInfos as $orderInfo){
                 $needDealOrder .= "<tr><td>" . $orderInfo['item_name'] . "</td><td>" . $orderInfo['branch_name'] . "</td><td>" . $orderInfo['redeem_number'] . "</td><td>" . $this->get('dwd.util')->getOrderStatusLabel( $orderInfo['status'] ) . "</td><td><a href='#' class='order-correct-btn' data-rel='" . $orderInfo['id'] .  "'>[纠错]</a></td></tr>";
               }
            }
            $needDealOrder .= "</table>";
        }

        return $this->render('DWDCSAdminBundle:User:index.html.twig', array(
            'jsonUserInfo'     => json_encode( $data['user']['data'] ),
            'balancerecords'   => $data['balancerecords']['data']['list'],
            'userinfo'         => $data['user']['data'],
            'needDealOrder'    => $needDealOrder,
            'orderlistTypes'   => $orderListTypes,
            'userId'           => $userId,
            'type'             => $type,
            'source'           => $source,
            'userDevicesCount' => $userDevicesCount
        ));
    }

    private function _getOrderTypeId( $typeCode )
    {
        $orderTypeId             = 2;
        switch ( $typeCode ) {
                case 'waitredeem':
                    $orderTypeId = 2;
                    break;
                case 'refund':
                    $orderTypeId = 6;
                    break;
                case 'expired': 
                    $orderTypeId = 3;
                    break;
                case 'finish': 
                    $orderTypeId = 4;
                    break;
                case 'processing': 
                    $orderTypeId = 11;
                    break;
                default: 
                    break;
            }
        return $orderTypeId;
    }

    private function _getOperation( $operation, $orderId, $offlineEnabled = 0 )
    {
        $opStr               = '';
        foreach ($operation as $operator) {
          switch ( $operator ) {
             case '退款':
                    $opStr  .= "&nbsp;&nbsp;&nbsp;<a href='#' class='order-refund-btn' data-rel='$orderId' data-campaign-branch-enabled-rel='$offlineEnabled'>[退款]</a>";
                    break;
             case '取消':
                  $opStr    .= "&nbsp;&nbsp;&nbsp;<a href='#' class='order-cancel-btn' data-rel='$orderId' data-campaign-branch-enabled-rel='$offlineEnabled'>[取消]</a>";
                  break;
             case '纠错':
                    $opStr  .= "&nbsp;&nbsp;&nbsp;<a href='#' class='order-correct-btn' data-rel='$orderId' data-campaign-branch-enabled-rel='$offlineEnabled'>[纠错]</a>";
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

    //获取订单列表,根据类型来判读
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

        $ordersInfo        = array();
         
        foreach( $data['orderlist']['data']['list'] as $orderInfo )
        {
            $ordersInfo[]        = $orderInfo;
        }

        foreach( $ordersInfo as $key => $orderInfo ){

        $tableInfo               =  $this->get('dwd.util')->getOrderTableInfo( $orderTypeId,$orderInfo['type']);
           $tdValues             = array();
           foreach ($tableInfo['field'] as $field) 
           {  
              $tdValue           = '';
              switch ( $field ) {
                case 'itemName':
                  $tdValue       = $orderInfo['item_name'];
                  break;
                case 'branchName':
                  $tdValue       = $orderInfo['branch_name'];
                  break;
                case 'redeemNumber':
                  $tdValue       = $orderInfo['redeem_number'];
                  break;
                case 'refundTime':
                  $tdValue       = $orderInfo['refunded_at'];
                  break;
                case 'expiredTime':
                  $tdValue       = $orderInfo['expire_date'];
                  break;
                case 'redeemTime':
                  $tdValue       = $orderInfo['redeem_time'];
                  break;
                case 'status':
                  $tdValue       = $this->get('dwd.util')->getOrderStatusLabel($orderInfo['status']);
                  break;
                case 'type':
                  $tdValue       = $this->get('dwd.util')->getOrderTypeLabel($orderInfo['type']); //订单类型
                  break;
                case 'feedback':
                  $tdValue       = $orderInfo['feedback'];
                  break;
                case 'note':
                  $tdValue       = $orderInfo['note'];
                  break;
                default:
                  break;
              }
              if( empty( $tdValue ) ){
                $tdValue         = '';
              }
              $tdValues[]        = $tdValue;
           }

            $data              = array(
                array(
                    'url'    => '/campaignbranch/detail',
                    'data'   => array(
                        'campaignBranchId'    => $orderInfo['campaign_branch_id'],
                    ),
                    'method' => 'get',
                    'key'    => 'detail',
                ),
            );

            $data              = $dataHttp->MutliCall( $data );
            $campaignBranch    = $data['detail']['data'];
           $tdValues[]           = $this->_getOperation( $tableInfo['operation'], $orderInfo['id'], $campaignBranch['enabled'] );
           $orderList['list'][]  = $tdValues;
        }
        return $orderList;
    }

    //获取订单信息,作为未领用的状态
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
        $orderListAll    =  $data['orderinfo']['data'];
        $orderList       =  array(
                                'list'  => array(),
                                'total' => 0,
                            );

        $operation       = array(
                            '纠错','日志','详情'
                           ); 

        foreach( $orderListAll as $orderInfo )
        {
            if( !empty( $orderInfo ) && $userId == $orderInfo['user_id'] ){
                $orderList['list'] []= array(
                    $orderInfo['item_name'],
                    $orderInfo['branch_name'],
                    $orderInfo['redeem_number'],
                    $this->get('dwd.util')->getOrderStatusLabel($orderInfo['status']),
                    $this->get('dwd.util')->getOrderTypeLabel($orderInfo['type']),
                    $orderInfo['redeem_time'],
                    $this->_getOperation( $operation, $orderInfo['id'] ),
                );
                $orderList['total'] += 1;
            }
        }

        return $orderList;
    }

    /**
     * @Route("/user/orderlist", name="dwd_csadmin_user_orderlist_show")
     * @Method("GET")
     *
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
     *
     * @Route("/user/couponActivityDetail",name="dwd_csadmin_coupon_couponactivitydetail")
     * 单个活动的活动码信息。
     */
    public function couponActivityDetailAction()
    {
        $dataHttp            = $this->get('dwd.data.http');
        $couponId            = $this->getRequest()->get('couponId');
        $userId              = $this->getRequest()->get('userId');

        $data                 = array(
            array(
                'url'    => '/user/getPromoActivityInfo',
                'data'   =>  array(
                    'couponId'        => $couponId,
                    'userId'          => $userId,
                ),
                'method' =>  'get',
                'key'    =>  'couponinfo',
            ),
        );

        $data                 = $dataHttp->MutliCall($data);
        $couponinfo           = $data['couponinfo']['data'][0];

        $str               = '<table class="table table-striped table-bordered"><tr></tr>';
        $str              .= "<tr><td>活动id</td><td>"    . $couponinfo['id'] . "</td></tr>";
        $str              .= "<tr><td>活动码名称</td><td>" . $couponinfo['activityname'] . "</td></tr>";
        $str              .= "<tr><td>券码信息</td><td>"   . $couponinfo['couponInfo'] . "</td></tr>";
        $str              .= "<tr><td>券码</td><td>"     . $couponinfo['code'] . "</td></tr>";
        $str              .= "<tr><td>开始时间</td><td>" . $couponinfo['start_date'] . "</td></tr>";
        $str              .= "<tr><td>结束时间</td><td>" . $couponinfo['end_date'] . "</td></tr>";
        $str              .= "<tr><td>使用时间</td><td>" . $couponinfo['used_at'] . "</td></tr>";
        $str              .= "<tr><td>劵码状态</td><td>" . $this->get('dwd.util')->getCouponStatusLabel($couponinfo['status'])."</td></tr>";

        $str              .= "</table>";
        $res               = array(
            'result'  => true,
            'content' => $str,
        );
        $response          = new Response();
        $response->setContent( json_encode( $res ) );
        return $response;
    }

    /**
     *
     * @Route("/user/vendorCouponDetail",name="dwd_csadmin_coupon_vendordetail")
     * 单个商家优惠券活动信息。
     */
    public function vendorCouponDetailAction()
    {
        $dataHttp             = $this->get('dwd.data.http');
        $vendorCouponId       = $this->getRequest()->get('vendorCouponId');

        $data                 = array(
            array(
                'url'    => '/vendorcoupon/Detail',
                'data'   =>  array(
                    'vendorCouponId'    => $vendorCouponId,
                ),
                'method' =>  'get',
                'key'    =>  'couponinfo',
            ),
        );

        $data                 = $dataHttp->MutliCall($data);
        $couponinfo           = $data['couponinfo']['data'];

        $str               = '<table class="table table-striped table-bordered"><tr></tr>';
        $str              .= "<tr><td>活动id</td><td>"    . $couponinfo['id'] . "</td></tr>";
        $str              .= "<tr><td>门店</td><td>"      . $couponinfo['branch_name'] . "</td></tr>";
        $str              .= "<tr><td>优惠券标题</td><td>" . $couponinfo['title'] . "</td></tr>";
        $str              .= "<tr><td>开始时间</td><td>"  . $couponinfo['start_time'] . "</td></tr>";
        $str              .= "<tr><td>结束时间</td><td>"  . $couponinfo['end_time'] . "</td></tr>";
        $str              .= "<tr><td>使用时间</td><td>"  . $couponinfo['used_at'] . "</td></tr>";
        $str              .= "<tr><td>劵码状态</td><td>"  . $this->get('dwd.util')->getVendorCouponStatusLabel($couponinfo['status'])."</td></tr>";

        $str              .= "</table>";
        $res               = array(
            'result'  => true,
            'content' => $str,
        );
        $response          = new Response();
        $response->setContent( json_encode( $res ) );
        return $response;
    }
    /**
     * @Route("/user/promoactivitycoupons", name="dwd_csadmin_user_promocoupon_show")
     * @Method("GET")
     * 用户活动码列表
     */
    public function PromoCouponDataAction()
    {
        $dataHttp             = $this->get('dwd.data.http');
        $sEcho                = $this->getRequest()->get('sEcho');
        $userId               = $this->getRequest()->get('userId');
        $iDisplayStart        = $this->getRequest()->get('iDisplayStart');
        $iDisplayLength       = $this->getRequest()->get('iDisplayLength');
        $sSearch              = $this->getRequest()->get('sSearch', null);

        $data            = array(
            array(
                'url'    => '/user/promoactivitycoupons',
                'data'   => array(
                    'userId'         => $userId,
                    'needPagination' => 1,
                    'pageLimit'      => $iDisplayLength,
                    'pageNum'        => $iDisplayStart / $iDisplayLength + 1,
                ),
                'method' => 'get',
                'key'    => 'couponlist',
            ),
        );
        $returnlist           = $dataHttp->MutliCall($data);

        $total = empty($returnlist['couponlist']['data']['totalCnt']) ? 0:$returnlist['couponlist']['data']['totalCnt'];
        $aaData =[];
        if(!empty($returnlist['couponlist']['data']['list']) ){

            foreach( $returnlist['couponlist']['data']['list'] as $coupon ){
                $aaData[]              = array(
                    $coupon['activityname'],
                    $coupon['couponInfo'],
                    $coupon['code'],
                    $coupon['start_date'],
                    $coupon['end_date'],
                    empty($coupon['used_at'])?'':$coupon['used_at'],
                    $this->get('dwd.util')->getCouponStatusLabel( $coupon['status'] ),
                    "<a href='#' class='order-detail-btn' data-rel='". $coupon['id'] . "'>[详情]</a>",
                );
            }
        }
        $res                       = array(
            "sEcho"                => $sEcho,
            "aaData"               => $aaData,
            "iTotalRecords"        => $total,
            "iTotalDisplayRecords" => $total,
        );
        $response             = new Response();
        $response->setContent( json_encode( $res ) );
        return $response;
    }

    /**
     * @Route("/user/vendorcouponlist", name="dwd_csadmin_user_vendorpromocoupon_show")
     * @Method("GET")
     * 商家优惠券列表
     */
    public function VendorCouponListAction()
    {
        $dataHttp             = $this->get('dwd.data.http');
        $sEcho                = $this->getRequest()->get('sEcho');
        $userId               = $this->getRequest()->get('userId');
        $iDisplayStart        = $this->getRequest()->get('iDisplayStart');
        $iDisplayLength       = $this->getRequest()->get('iDisplayLength');
        $sSearch              = $this->getRequest()->get('sSearch', null);

        $data            = array(
            array(
                'url'    => '/vendorcoupon/vendorcouponlist',
                'data'   => array(
                    'userId'         => $userId,
                    'getAll'         => 1,
                    'needPagination' => 1,
                    'pageLimit'      => $iDisplayLength,
                    'pageNum'        => $iDisplayStart / $iDisplayLength + 1,
                ),
                'method' => 'get',
                'key'    => 'vendorcouponlist',
            ),
        );
        $returnlist           = $dataHttp->MutliCall($data);

        $total = empty($returnlist['vendorcouponlist']['data']['totalCnt']) ?0:$returnlist['vendorcouponlist']['data']['totalCnt'];
        $aaData =[];
        if(!empty($returnlist['vendorcouponlist']['data']['list']) ){

            foreach( $returnlist['vendorcouponlist']['data']['list'] as $coupon ){
                $aaData[]              = array(
                    $coupon['branch_name'],
                    $coupon['title'],
                    $coupon['start_time'],
                    $coupon['end_time'],
                    empty($coupon['used_at'])?'':$coupon['used_at'],
                    $this->get('dwd.util')->getVendorCouponStatusLabel( $coupon['status'] ),
                    "<a href='#' class='order-detail-btn' data-rel='". $coupon['id'] . "'>[详情]</a>",
                );
            }
        }
        $res                       = array(
            "sEcho"                => $sEcho,
            "aaData"               => $aaData,
            "iTotalRecords"        => $total,
            "iTotalDisplayRecords" => $total,
        );
        $response             = new Response();
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
//        var_dump($data);exit;
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
                                               $this->get('dwd.util')->getRecommendRecordNoteLabel($recommendrecord['amount']),
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
     * @Route("/user/noticerecords", name="dwd_csadmin_user_noticerecords_show")
     * @Method("GET")
     * 用户通知记录中心 3.15
     */
    public function  noticeRecordsDataAction()
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
                'url'    => '/user/noticerecords',
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
                $smsrecord['title'],
                $smsrecord['content'],
                $smsrecord['created_at'],
                $smsrecord['end_time'],
            );
        }

        $res             = array(
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

        $sEcho                = $this->getRequest()->get('sEcho');
        $userId               = $this->getRequest()->get('userId');
        $iDisplayStart        = $this->getRequest()->get('iDisplayStart');
        $iDisplayLength       = $this->getRequest()->get('iDisplayLength');   
        $sSearch              = $this->getRequest()->get('sSearch', null);

        $dm                   = $this->get('doctrine_mongodb')->getManager();
        $options              = array(
                                  'limit'  => $iDisplayLength,
                                  'skip'   => $iDisplayStart,
                                );
        $complaintList        = $dm->getRepository('DWDDataBundle:Complaint')->getUserComplaints( $userId, $options );
        $complaintCnt         = $dm->getRepository('DWDDataBundle:Complaint')->getUserCount( $userId );
 

        $aaData                    = array();

        $dataHttp             = $this->get('dwd.data.http');

        foreach( $complaintList as $complaint ){
            $tags                  = array();

            if( isset( $complaint['tags'] ) ){
              foreach ($complaint['tags'] as $tagId) {
                  $tags[]          = $this->get('dwd.util')->getComplaintTag( $tagId );  
              }
            } 

            $branchName = '';
            $itemName   = '';

//            if( isset( $complaint['branchs'] ) ){
//              $branchName = $complaint['branchs'][0]['name'];
//              $itemName   = isset( $complaint['branchs'][0]['itemName'] ) ? $complaint['branchs'][0]['itemName'] : '';
//            } else if( isset( $complaint['orders'] ) ) {
//              $branchName = isset( $complaint['orders'][0]['branchName'] ) ? $complaint['orders'][0]['branchName'] : '';
//              $itemName   = isset( $complaint['orders'][0]['itemName'] ) ? $complaint['orders'][0]['itemName'] : '';
//            }

            if( isset($complaint['complaintInfo']['orderId']) ) {
                $data       = array(
                    array(
                        'url'    => '/order/orderinfo',
                        'data'   =>  array(
                            'orderId'  => $complaint['complaintInfo']['orderId'],
                        ),
                        'method' =>  'get',
                        'key'    =>  'orderinfo',
                    ),
                );


                $data                 = $dataHttp->MutliCall($data);
                $orderinfo            = $data['orderinfo']['data'];
                $branchName           = $orderinfo['branch_name'];
                $itemName             = $orderinfo['item_name'];
            }

            $aaData[]              = array(
                                        $this->get('dwd.util')->getComplaintSourceLabel( $complaint['source'] ),
                                        implode(",", $tags),
                                        $itemName,
                                        $branchName,
                                        date("Y-m-d H:i:s", $complaint['createdAt']),
                                        "<a href='/complaint/edit?id=" . $complaint['_id'] . "' target='_blank' >[详情]</a>",
                                     );
        }

        $res                       = array(
                                        "sEcho"                => $sEcho, 
                                        "aaData"               => $aaData,
                                        "iTotalRecords"        => $complaintCnt,
                                        "iTotalDisplayRecords" => $complaintCnt,
                                     );

        $response             = new Response();
        $response->setContent( json_encode( $res ) );
        return $response;
    }



    /**
     * @Route("/user/modify", name="dwd_csadmin_user_modify")
     * @Method("POST")
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

        $logRecord         = array(
                                'route'    => $this->getRequest()->get('_route'),
                                'res'      => $res['result'],
                                'adminId'  => $this->getUser()->getId(),
                                'ext'      => array( 
                                                'username'      => $userName,
                                                'mobile'        => $mobile,
                                                'userId'        => $userId, 
                                              ),
                             );
        $this->get('dwd.oplogger')->addCommonLog( $logRecord );
 
        $response          = new Response();
        $response->setContent( json_encode( $res ) );
        return $response; 
    }

    /**
     * @Route("/user/resetPwd", name="dwd_csadmin_user_resetPwd")
     * @Method("POST")
     */
    public function resetPwd()
    {
        $dataHttp       = $this->get('dwd.data.http'); 
        $userId         = $this->getRequest()->get('userId');
        $contacterMobile= $this->getRequest()->get('contacter_mobile');

        $randPasswd = rand(100000, 999999);
        $params         = array( 
                              'user_id'          => $userId,
                              'password'         => $randPasswd,
                          );
        $data           = array(
                              array(
                                  'host'   => $this->container->getParameter('iqg_host'),
                                  'url'    => '/api/user/update_password',   // 这个接口 dev环境访问受限，在staging没有问题
                                  'data'   =>  $params,
                                  'method' => 'post',
                                  'key'    => 'resetPwd',
                              ),
                          ); 

        $data              = $dataHttp->MutliCall($data);
        $res               = array();
        $res['result']     = false;
        if( $data['resetPwd']['status']['code'] == 10000 ){//更改成功后，调用短信结构

            $senddata = array(
                array(
                    'url'    => '/sms/send',
                    'data'   => array(
                        'mobile'    => $contacterMobile,
                        'content'   => '亲爱的用户,已为您设置新的密码：' . $randPasswd .',请登陆爱抢购app,及时修改密码' ,
                    ),
                    'method' => 'post',
                    'key'    => 'sendPwd',
                ),
            );
            $sendMsgResult    = $dataHttp->MutliCall($senddata);
            if($sendMsgResult['sendPwd']['errno'] == 0 && $sendMsgResult['sendPwd']['errmsg'] == 'success' ){
                $res['result'] = true;
            }
        }

        $logRecord         = array(
                                'route'    => $this->getRequest()->get('_route'),
                                'res'      => $res['result'],
                                'adminId'  => $this->getUser()->getId(),
                                'ext'      => array( 
                                                'userId'        => $userId,
                                                'password'      => substr_replace( strval( $params['password'] ), '***', 2, 3 ),
                                              ),
                             );

        $this->get('dwd.oplogger')->addCommonLog( $logRecord );
 
        $response          = new Response();
        $response->setContent( json_encode( $res ) );
        return $response;
    }

    /**
     * @Route("/user/resetPinPwd", name="dwd_csadmin_user_resetPinPwd")
     * @Method("POST")
     * 重置pin码（店码,四位数)
     */
    public function resetPinPwd()
    {
        $dataHttp       = $this->get('dwd.data.http');
        $branchId       = $this->getRequest()->get('branchId');
        $mobile         = $this->getRequest()->get('mobile'); //店铺的店码时，需要用到user的brand_admin_bind_mobile字段

        $pin  =  rand(1000, 9999);
        $data = array(
                    array(
                        'url'    => '/branch/update',
                        'data'   => array(
                            'branchId'    => $branchId,
                            'pin'         => $pin,
                        ),
                        'method' => 'post',
                        'key'    => 'resetPwd',
                    ),
         );
        $data              = $dataHttp->MutliCall($data);
        $res             = false;
        if( $data['resetPwd']['errno'] == 0 && $data['resetPwd']['data'] == true ){

            //@todo  调用短信接口
            $senddata = array(
                array(
                    'url'    => '/sms/send',
                    'data'   => array(
                        'mobile'    => $mobile,
                        'content'   => '亲爱的用户,已为您设置新的店码：' . $pin .',请登陆爱抢购app,及时修改密码' ,
                    ),
                    'method' => 'post',
                    'key'    => 'sendPwd',
                ),
            );
            $sendMsgResult    = $dataHttp->MutliCall($senddata);
            if($sendMsgResult['sendPwd']['errno'] == 0 && $sendMsgResult['sendPwd']['errmsg'] == 'success' ){
              $res           = true;
            }
        }

        $logRecord         = array(
            'route'    => $this->getRequest()->get('_route'),
            'res'      => $res,
            'adminId'  => $this->getUser()->getId(),
            'ext'      => array(
                'branchId' => $branchId,
                'pin'      => $pin,
                'sendMsgResult' => $sendMsgResult
             ),
        );
        $this->get('dwd.oplogger')->addCommonLog( $logRecord );

        $response          = new Response();
        $response->setContent( json_encode( $res ) );
        return $response;
    }

    /**
     * @Route("/user/lock", name="dwd_csadmin_user_lock")
     *
     */
    public function lockUser()
    { 
        $dataHttp             = $this->get('dwd.data.http'); 
        $userId               = $this->getRequest()->get('userId');
        $lockDays             = $this->getRequest()->get('lockDays', 30);
        $reasonType           = $this->getRequest()->get('selectlockReason', 2); 

        if( false == empty( $userPassword ) ){
          $params['password'] = $userPassword;
        }

        $type                 = 1;
        $unlockDate           = date( 'Y-m-d H:i:s',  3600 * 24 * $lockDays + time() );

        $data                 = array( 
                                    array(
                                        'url'    => '/user/locked',
                                        'data'   =>  array( 
                                                        'userId'     => $userId,
                                                        'opUserId'   => $this->getUser()->getId(),
                                                        'reasonType' => $reasonType,
                                                        'unlockDate' => $unlockDate,
                                                        'type'       => $type,
                                                        'note'       => '后台封号',
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

        $logRecord       = array(
                              'route'    => $this->getRequest()->get('_route'),
                              'res'      => $res['result'],
                              'adminId'  => $this->getUser()->getId(),
                              'ext'      => array( 
                                              'userId'        => $userId,
                                              'reasonType'    => $reasonType,
                                              'unlockDate'    => $unlockDate, 
                                              'type'          => $type,
                                            ),
                           );
        $this->get('dwd.oplogger')->addCommonLog( $logRecord );
 
        $response          = new Response();
        $response->setContent( json_encode( $res ) );
        return $response; 
    }

    /**
     * @Route("/user/unbinddevice", name="dwd_csadmin_user_unbinddevice")
     *
     */
    public function unbindDevice()
    { 
        $dataHttp             = $this->get('dwd.data.http'); 
        $userId               = $this->getRequest()->get('userId');

        if( false == empty( $userPassword ) ){
          $params['password'] = $userPassword;
        }

        $reasonType           = 2;
        $data                 = array( 
                                    array(
                                        'url'    => '/user/unbinddevice',
                                        'data'   =>  array( 
                                                        'userId'     => $userId,
                                                        'opUserId'   => $this->getUser()->getId(),
                                                        'reasonType' => $reasonType, 
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

        $logRecord         = array(
                              'route'    => $this->getRequest()->get('_route'),
                              'res'      => $res['result'],
                              'adminId'  => $this->getUser()->getId(),
                              'ext'      => array(
                                              'userId'        => $userId,
                                              'reasonType'    => $reasonType,
                                            ),
                             );
        $this->get('dwd.oplogger')->addCommonLog( $logRecord );
 
        $response          = new Response();
        $response->setContent( json_encode( $res ) );
        return $response; 
    }
}
