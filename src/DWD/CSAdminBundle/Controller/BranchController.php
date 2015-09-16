<?php

namespace DWD\CsAdminBundle\Controller;
 
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class BranchController
 * @package DWD\CsAdminBundle\Controller
 * @Route("/")
 */
class BranchController extends Controller
{
 
    const WEB_VERIFY    = 1;  //Web验证
    const TEL_VERIFY    = 2;  //电话验证
    const PAPER_VERIFY  = 4;  //纸质验证
    const SECRET_VERIFY = 8;  //密码验证

    private function _getRedeemTypes( $redeemType )
    {
        $redeemTypes    = array();
        $redeemTypeIds  = array();

        if( $redeemType & self::WEB_VERIFY  ){
          $redeemTypes[]   = 'web验证';
          $redeemTypeIds[] = self::WEB_VERIFY;
        }

        if( $redeemType & self::TEL_VERIFY  ){
          $redeemTypes[]   = '电话验证';
          $redeemTypeIds[] = self::WEB_VERIFY;
        }

        if( $redeemType & self::PAPER_VERIFY  ){
          $redeemTypes[]   = '纸质验证';
          $redeemTypeIds[] = self::WEB_VERIFY;
        }

        if( $redeemType & self::SECRET_VERIFY  ){
          $redeemTypes[]   = '密码验证';
          $redeemTypeIds[] = self::WEB_VERIFY;
        }

        return  array(
                  $redeemTypes,
                  $redeemTypeIds,
                );
    }

    private function _getOperation( $operation, $orderId )
    {
        $opStr               = '';
        foreach ($operation as $operator) {
          switch ( $operator ) {
            /*  case '退款':
                    $opStr  .= "&nbsp;&nbsp;&nbsp;<a href='#' class='order-refund-btn' data-rel='$orderId'>[退款]</a>";
                    break; */
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

    /** 
     *
     * @Route("/branch",name="dwd_csadmin_branch")
     */
    public function indexAction()
    { 
        $branchId        = $this->getRequest()->get("branchId");
        $dataHttp        = $this->get('dwd.data.http');

        $requests        = array(
            array(
                'url'    => '/branch/branchinfo',
                'data'   => array(
                    'branchId'      => $branchId,
                ),
                'method' => 'get',
                'key'    => 'branch',
            ), 
        ); 

        $data           = $dataHttp->MutliCall($requests);
        $branchInfo     = $data['branch']['data'];
        if( empty( $branchInfo ) ){
            return $this->render('DWDCSAdminBundle:Dashboard:index.html.twig', array(
             'errMsg'    => '商户不存在'
          ));
        }
        
        $requests        = array(
            array(
                'url'    => '/brand/brandinfo',
                'data'   => array(
                    'brandId'      => $branchInfo['brand_id'],
                ),
                'method' => 'get',
                'key'    => 'brand',
            ), 
            array(
                'url'    => '/zone/zoneinfo',
                'data'   => array(
                    'zoneId'       => $branchInfo['zone_id'],
                ),
                'method' => 'get',
                'key'    => 'zone',
            ), 
            array(
                'url'    => '/saler/salerinfo',
                'data'   => array(
                    'salerId'      => $branchInfo['saler_id'],
                ),
                'method' => 'get',
                'key'    => 'saler',
            ),
        ); 

        $data                        = $dataHttp->MutliCall($requests);
        $brandInfo                   = $data['brand']['data'];
        $zoneInfo                    = $data['zone']['data'];
        $salerInfo                   = $data['saler']['data'];
 
        $branchInfo['brandName']     = $brandInfo['name'];
        $branchInfo['brandTel']      = $brandInfo['tel'];
        $branchInfo['zoneName']      = $zoneInfo['name'];
        $branchInfo['salerName']     = $salerInfo['name'];
        $redeemTypes                 = self::_getRedeemTypes( $branchInfo['redeem_type'] );
        $branchInfo['redeemTypes']   = implode( ", ", $redeemTypes[0]); //self::_getRedeemTypes( $branchInfo['redeem_type'] );
        $branchInfo['redeemTypeIds'] = $redeemTypes[1];
        $orderListTypes              =  $this->get('dwd.util')->getOrderTableInfo( 0 );

        return $this->render('DWDCSAdminBundle:Branch:index.html.twig', array( 
            'branchinfo'             => $branchInfo, 
            'orderlistTypes'         => $orderListTypes,
            'branchId'               => $branchId,
        ));
    } 

    //获取订单列表
    private function _getOrderList( $branchId, $orderType, $limitStart, $pageLimit )
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

        $data            = array(
            array(
                'url'    => '/branch/orderlist',
                'data'   => array(
                    'branchId'       => $branchId,
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
        $data                    = $dataHttp->MutliCall($data);
        $tableInfo               =  $this->get('dwd.util')->getOrderTableInfo( $orderTypeId );

        foreach( $ordersInfo as $key => $orderInfo ){
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
        }

        return $orderList;
    }

    //获取订单信息
    private function _getOrderInfo( $redeemNumber, $branchId )
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
                                ),
                                array(
                                    'url'    => '/branch/branchinfo',
                                    'data'   => array(
                                        'redeemNumber'      => $redeemNumber, 
                                    ),
                                    'method' => 'get',
                                    'key'    => 'branchinfo',
                                )
                            );
        $data            =  $dataHttp->MutliCall($data);
        $orderInfo       =  $data['orderinfo']['data'];
        $branchInfo      =  $data['branchinfo']['data']; 
        $orderList       =  array(
                                'list'  => array(),
                                'total' => 0,
                            );
       
        if( !empty( $orderInfo ) &&  $branchInfo['id'] ==  $branchId ){ 
            $orderList       =  array(
                                'list' => 
                                    array(
                                        array(
                                         $orderInfo['id'],
                                         $orderInfo['campaign_branch_id'],
                                         $orderInfo['user_id'],
                                         $orderInfo['price'],
                                         $this->get('dwd.util')->getOrderStatusLabel( $orderInfo['status'] ),
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
     * @Route("/branch/orderlist", name="dwd_csadmin_branch_orderlist_show")
     * @Method("GET")
     */
    public function OrderListDataAction()
    {
        $iDisplayStart   = $this->getRequest()->get('iDisplayStart');
        $iDisplayLength  = $this->getRequest()->get('iDisplayLength'); 
        $sEcho           = $this->getRequest()->get('sEcho');
        $sSearch         = $this->getRequest()->get('sSearch', null);
        $branchId        = $this->getRequest()->get('branchId');
        $orderType       = $this->getRequest()->get('type', 'redeem');
        $orderList       = array();

        if( empty( $sSearch ) ){
            $orderList   = self::_getOrderList( $branchId, $orderType, $iDisplayStart, $iDisplayLength);
        } else {
            $orderList   = self::_getOrderInfo( $sSearch, $branchId );
        }
        $res             = array
                           (
                                "sEcho"                => $sEcho, 
                                "aaData"               => $orderList['list'],
                                "iTotalRecords"        => $orderList['total'],
                                "iTotalDisplayRecords" => $orderList['total'],
                           );
        exit(json_encode( $res ));
        //return $this->render('DWDCSAdminBundle:Dashboard:show.html.twig', array(
         //   'product'      => $product,
      //  ));
    }
 
    /**
     * @Route("/branch/complaintrecords", name="dwd_csadmin_branch_complaintrecords_show")
     * @Method("GET")
     */
    public function ComplaintRecordsDataAction()
    {
        $dataHttp        = $this->get('dwd.data.http');
        $iDisplayStart   = $this->getRequest()->get('iDisplayStart');
        $iDisplayLength  = $this->getRequest()->get('iDisplayLength'); 
        $sEcho           = $this->getRequest()->get('sEcho');
        $sSearch         = $this->getRequest()->get('sSearch', null);
        $branchId        = $this->getRequest()->get('branchId');
        $orderType       = $this->getRequest()->get('type', 'redeem');
        $orderList       = array();
        $data            = array( 
                                array(
                                    'url'    => '/branch/complaints',
                                    'data'   => array(
                                        'branchId'       => $branchId,
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